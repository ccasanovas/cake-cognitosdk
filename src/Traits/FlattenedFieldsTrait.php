<?php
namespace Ccasanovas\ApiGatewaySDK\Traits;

use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\ORM\Exception\PersistenceFailedException;
use Ccasanovas\ApiGatewaySDK\Error\UnprocessableEntityException;
use Error;

trait FlattenedFieldsTrait
{
    /*
    Description:
    ------------
    this Trait helps streamline the creation of API compatible methods and actions.
    this helps by making it easy to map the way the requests should handle the table fields (and its associated tables' fields), to the way the internal tables handle those fields.
    For instance, you can use this to map this structure:
    user => [
        name,
        address => [
            street,
            number
        ],
        phone => [
            number
        ],
        dni => [
            number
        ]
    ]
    to this request:
    user => [
        name,
        address_street,
        address_number,
        phone_number,
        dni_number
    ]

    this is useful to make the API definition separate from the internal table structure.
    When used, this trait also handles validation errors, such that the error structure matches the api definition and the user of the api is never aware of the internal data structure.

    How to use:
    -----------
    1. use trait in table
    2. redefine flattenedFieldsMaps in your table
    3. create methods to easily set(save) and get(find) entities in your table.
        you can use getFlattenedEntity and setFlattenedEntity inside those methods for this purpose.
        Preferably use standard names like setFlattened{EntityName} and getFlattened{EntityName}
    4. call these new methods from your controller to get very thin api controllers
    */

    public function flattenedFieldsMaps()
    {
        /* override this function in table with corresponding fields */
        $maps = [
            'set' => [
                //'resulting entity' => 'input entity'
                'id' => 'id',
            ],
            'get' => [
                //'resulting entity' => 'input entity'
                'created' => 'created',
                'modified' => 'modified',
            ]
        ];
        return $maps;
    }

    protected function getFlattenedFieldsMap($map_name, $flip = false)
    {
        $maps = $this->flattenedFieldsMaps();

        if(!in_array($map_name, array_keys($maps))){
            throw new Error('Requested map for getFlattenedFieldsMap is invalid');
        }

        if(!$flip) return $maps[$map_name];

        $flipped = [];

        foreach ($maps[$map_name] as $key => $value) {
            if(is_scalar($value)){
                $flipped[$value] = $key;
                continue;
            }

            if(empty($value['entities'])){
                throw new Error('Cannot flip array fields map because the "entities" key is not set');
            }

            if(empty($value['map'])){
                throw new Error('Cannot flip array fields map because the "map" key is not set');
            }

            $flipped[ $value['entities'] ] = [
                'entities' => $key,
                'map' => array_flip($value['map'])
            ];
        }

        return $flipped;
	}

	protected function mapFlattenedFields($entity, $map, $callback)
    {
        $mapped = [];
        foreach ($map as $output => $input) {

            if(is_array($input)){
                $value = [];

                if(empty($input['entities'])){
                    throw new Error('Cannot map fields because the "entities" key is not set');
                }

                if(empty($input['map'])){
                    throw new Error('Cannot map fields because the "map" key is not set');
                }

                $entities = Hash::get($entity, $input['entities']);

                if(!is_array($entities)){
                    $value = $callback($input['entities'], $entity);
                }else{
                    foreach ($entities as $array_value) {
                        $value[] = $this->mapFlattenedFields($array_value, $input['map'], $callback);
                    }
                }

            }else{
                $value = $callback($input, $entity);
            }

            $mapped = Hash::insert($mapped, $output, $value);
        }

        return $mapped;
    }

    protected function getFlattenedEntity($where = [], $contain = [])
    {
        $entity = $this->find()
            ->where($where)
            ->contain($contain)
        ->firstOrFail();

        $map = $this->getFlattenedFieldsMap('get');

        return $this->mapFlattenedFields($entity, $map, function($field, $entity){
            return Hash::get($entity, $field);
        });
    }

    protected function setFlattenedEntity(Callable $method, $error_map = null)
    {
        try {
            return $method();
        } catch (PersistenceFailedException $e) {
            $entity = $e->getEntity();
            $entity_errors = $entity->getErrors();

            if(!empty($entity_errors)){
                $map = $error_map ?: $this->getFlattenedFieldsMap('set', true);
                $errors = $this->mapFlattenedFields($entity, $map, function($field, $entity) use (&$entity_errors){
                    if(!Hash::check($entity_errors, $field)) return null;

                    $field_errors = Hash::get($entity_errors, $field);
                    $entity_errors = Hash::remove($entity_errors, $field);

                    return $field_errors;
                });

                $errors = Hash::filter($errors);
            }

            throw new UnprocessableEntityException([
                'message' => __d('api', 'Data Validation Failed'),
                'errors'  => $errors,
                //'errors' => $entity_errors, //debug
            ]);
        }

    }
}
