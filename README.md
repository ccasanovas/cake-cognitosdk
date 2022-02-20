## ApiGatewaySDK plugin para CakePHP

Este plugin contiene utilidades para crear y deployar más fácilmente aplicaciones para AWS ApiGateway.

----------

## Changelog

Fecha: 2022-08-31

* Cambios codigo deprecado para CakePHP 3.8.* actualización a 4.3.*

Fecha: 2019-04-15

* Agregada opción al ApiRequestComponent para habilitar la personalización de los Content-Types permitidos

Fecha: 2018-10-10

* Reescrito todo el README para denotar los cambios más recientes
* El plugin ya no es compatible con las versiones viejas (anteriores a tag v0.1.0)

----------

## Documentación

### Funcionalidades

- Incluye el componente `ApiRequestComponent` para tus API Controllers, con las siguientes funcionalidades:
    + asegura que las requests esten formateadas correctamente en JSON
    + verifica que la request provenga del ApiGateway configurado
    + habilita CORS para el APIGateway
    + formatea las variables de paginación correctamente si se usa el PagintorComponent en el controller para servir los datos
    + agrega los Link Headers (https://tools.ietf.org/html/rfc5988) en case de que la response tenga paginación
- Incluye el trait `FlattenedFieldsTrait` para las modelos usados en las API que facilita la creación de métodos y acciones compatibles con el API.
    + Éste Trait simplifica el mapeo entre las tablas locales y la definición de la API.
    + Esto permite separar completamente la definición del API de la estructura interna de las tablas
    + También se encarga de formatear los errores de validación para que coincidan con la estructura definida para la API.
- Incluye la excepción `UnprocessableEntityException`, una excepción para manejar los errores de formato y validación de datos recibidos en la API.
- Incluye el exception renderer `ApiExceptionRenderer` para poder procesar las excepciones que dispare la API de manera correcta


### Instalación

Para obtener el plugin con **composer** se requiere agregar a `composer.json` lo siguiente:

1. Al objeto `"require"`, agregar el plugin: `"ccasanovas/cake-apigateway": "dev-master"`
2. Al arreglo de `"repositories"` agregar el objeto: ```{"type": "vcs", "url": "git@bitbucket.org:ccasanovas/cake-apigateway.git"}```
3. correr `composer update`

NOTA: asegurarse de tener los permisos de acceso/deploy correctos en el repositorio.

Una vez instalado el plugin en el repositorio se puede:

- agregar a los controllers de su API el component: `$this->loadComponent('Ccasanovas/ApiGatewaySDK.ApiRequest');`
- agregar a las tablas que lo requieran el trait de FlattenedFields: `use Ccasanovas\ApiGatewaySDK\Traits\FlattenedFieldsTrait;`
- configurar la aplicación para que use el exception renderer en `app.php` setteando `Error.exceptionRenderer' a  `Ccasanovas\ApiGatewaySDK\Error\ApiExceptionRenderer`


### Estructura

#### Ccasanovas\ApiGatewaySDK\Controller\Component\ApiRequestComponent

- *public* **$_apiRoute** = null
    + Variable interna usada para la generación de los headers Link. Para más información vea `getLinkHeaders`
- *public* **beforeFilter**(Event $event)
    + En este callback, el componente se encarga de:
        * comprobar que el header `X-Amzn-Apigateway-Api-Id` está presente y coincide con la configuración
        * asegurarse que el header `Content-Type` sea `application/json` para las requests de tipo PUT, POST y PATCH
        * asegurarse que el header `Accept` sea `application/json`
- *public* **beforeRender**(Event $event)
    + En este callback, el componente se encarga de:
        * Configurar la Response con los headers necesarios para CORS
        * Settear las variables del Paginator, si es que fue usado, en el array `paging` de la Response.
        * Settear los headers `Link` para la paginación, de existir.
        * Asegurarse que todas las viewVars estén serializadas.
    + También se ocupa de limpiar el viewVar `_apiRoute` antes de serializar la respuesta.

#### Ccasanovas\ApiGatewaySDK\Error\UnprocessableEntityException

- *public* **__construct**($message = null, $code = 422)
    + Esta excepción se usa para mostrar los errores de validación en la requests de la API.
    + utiliza el error code 422, que equivale la la HTTP Exception `422 Unprocessable Entity`
    + permite settear los errores de validación dentro del $message, pasandolo como array:
        ```php
        throw new UnprocessableEntityException([
            'message' => 'Error de validación!',
            'errors' => $entity->getErrors()
        ])
        ```


#### Ccasanovas\ApiGatewaySDK\Error\ApiExceptionRenderer

- *public* **UnprocessableEntity**($exception)
    + Este exception renderer agrega soporte para las excepciones de tipo `UnprocessableEntityException`
    + Se encarga de settear las variables correspondientes en la Response
    + En caso de haber errores de validación, los settea en la variable 'errors' del objeto de respuesta:
        ```php
        throw new UnprocessableEntityException([
            'message' => __('Data Validation Failed'),
            'errors' => $entity->getErrors()
        ]);
        ```
        ```json
        {
          "message": "Data Validation Failed",
          "errors": {
            "email": [{
              "code": "Empty",
              "message": "This field cannot be left empty"
            }]
          },
          "url": "\/api\/v1\/me\/email",
          "code": 422
        }
        ```
- *protected* **formatErrors**($entity_errors)
    + este método es usado para formatear los errores de validación en un formato de API más generico y menos Cake-like
    + Por cada campo hay un arreglo que contiene los errores. Cada error consiste de un arreglo con su `code` y su `message`. Convirtiéndo los errores de validación de Cake al estándar de API:
        ```php
        $errors = [
            'email' => [
                '_empty' => 'This field cannot be left empty',
                'email'  => 'This field must be a valid email address'
            ]
        ];

        pr($this->formatErrors($errors));
        ```
        ```php
        //resultado:
        [
            'email' => [
                [
                    'code' => 'Empty',
                    'message' => 'This field cannot be left empty'
                ],
                [
                    'code' => 'Email',
                    'message' => 'This field must be a valid email address'
                ],
            ]
        ]

        ```

#### Ccasanovas\ApiGatewaySDK\Traits\FlattenedFieldsTrait

- *public* **flattenedFieldsMaps**()
    + Devuelve un array con los mapas de campos para los metodos que usan este Trait. **Es importante sobreescribir este método en la tabla para que devuelva los mapas necesarios para satisfacer la estructura del API**:
    + Para poder usar `getFlattenedEntity` y `setFlattenedEntity`, el array debe definir los mapas: 'get' y 'set', respectivamente.
    + Los mapas se definen como arrays asociativos (`clave => valor`) donde la **clave** corresponde al nombre del campo resultante, y el **valor** corresponde al nombre del campo en los datos ingresados. Por ejemplo:
        ```php
        [
             'get' => [
                'identificador' => 'id',
                'nombre' => 'name'
             ],
             'set' => [
                'id' => 'identificador',
                'name' => 'nombre'
            ]
        ]
        ```
    + Vea ` mapFlattenedFields` para más información
- *protected* **getFlattenedFieldsMap**($map_name, $flip = false)
    + Este método se usa internamente para obtener los mapas desde `flattenedFieldsMaps`.
    + También permite invertir el mapa de ser necesario.
- *protected* **mapFlattenedFields**($entity, $map, $callback)
    + Este método procesa una entidad o arreglo `$entity` con el mapa $map, pasando cada entrada individualmente a $callback para ser procesado.
    + La forma más común de usar esto es con `Hash::get`, por ejemplo:
        ```php
        //dentro de la Table
        $entity = $this->get($id);
        $map    = $this->getFlattenedFieldsMap('get');
        $result = $this->mapFlattenedFields($entity, $map, function($field, $entity){
            return Hash::get($entity, $field);
        });
        ```
    + Al usar `Hash::get` se nos permite definir campos en un mapa con notación de punto, por ejemplo:
        ```php
        [
            'index' => [
                'id'                => 'id',
                'title'             => 'title',
                'category'          => 'news_category.name',
                'thumbnail'         => 'news_images.0.thumbnail',
                'created_at'        => 'created_at',
                'created_by'        => 'creator.full_name',
                'modified_at'       => 'modified_at',
                'modified_by'       => 'modifier.full_name',
            ]
        ]
        ```
    + Otra cualidad del método `mapFlattenedFields` es que permite mapear los campos de las entidades asociadas, por ejemplo, este mapa es para una entidad que está asociada con `BankAccounts` mediante `hasMany` y quiere exponerlas en el alias `cuentas`:
        ```php
        [
            'get' => [
                'id' => 'id',
                'titulo' => 'title',
                'cuentas' => [
                    'entities' => 'bank_accounts',
                    'map' => [
                        'banco'  => 'bank.name',
                        'tipo'   => 'bank_account_type.title',
                        'numero' => 'account_number',
                        'cbu'    => 'account_cbu',
                    ]
                ]
            ]
        ]
        ```
- *protected* **getFlattenedEntity**($where = [], $contain = [])
    + Este método es un wrapper básico que demuestra el uso de los mapas para una entidad singular.
    + Úselo para casos simples o como guía para crear sus propios métodos complejos.
    + Él método usa el mapa `get` definido en `flattenedFieldsMaps`
    + El método está definido como protected porque se espera que lo uses en tus métodos en lugar de llamarlo directamente del controller.
    + Los parámetros son transparentes y equivalentes a los usados para crear queries en CakePHP 3.
    + El parámetro `$where` define las condiciones de búsqueda de la query, y será pasado al find sin modificaciones.
    + El parámetro `$contain` define las asociaciones de la query.
    + Un ejemplo de uso básico sería:
        ```php
        //dentro de la Table
        $entity = $this->get($id);
        $mapped_entity = $this->getFlattenedEntity(['id' => $id]);
        ```
- *protected* **setFlattenedEntity**(Callable $method, $error_map = null)
    + A diferencia de `getFlattenedEntity`, este método no es un wrapper de guardado básico, si no que está hecho para que lo uses en combinación con tus propios métodos de guardado.
    + Esto se debe a que usualmente, la simplificación de datos en la definición de la API lleva a que el proceso de guardado sea más complejo, y muy variado como para ser facilmente encapsulado en éste plugin.
    + Esta flexibilidad permite por ejemplo guardar información en varias tablas a la vez usando un solo API endpoint, encerrando todas las operaciones en una transaction dentro de setFlattenedEntity.
    + Este método ejecuta el Callable $method proporcionado dentro de un try/catch que se encarga de que los errores de validación o guardado conserven el mismo formato de mapeado.
    + Es importante que el Callable use `saveOrFail` en lugar de `save`, ya que el catch se basa en la excepcion de guardado emitida por el saveOrFail para formatear correctamente los errores de validación.
    + Al momento de mapear los errores de validación, el método utilizará el mapa proporcionado en $error_map, o de lo contrario dará vuelta el mapa 'set'.
    + Un ejemplo de una implementación típica es:
        ```php
        public function setPaymentNotification($flattened_data, $api_user_id)
        {
            //obtiene un error map custom1
            $error_map = $this->getFlattenedFieldsMap('errors');

            //llama al método setFlattenedEntity
            return $this->setFlattenedEntity(function() use ($flattened_data, $api_user_id){

                /*  encierra el proceso en una transacción para poder guardar en varias tablas y
                    si alguna falla, revertir todos los cambios.
                */
                return $this->getConnection()->transactional(
                function ($connection) use ($flattened_data, $api_user_id){

                    //mapeamos con set
                    $fields_map = $this->getFlattenedFieldsMap('set');
                    $data = $this->mapFlattenedFields($flattened_data, $fields_map,
                    function($field, $payment_notification){
                        return Hash::get($payment_notification, $field);
                    });

                    //creamos entidad
                    $payment_notification = $this->newEntity($data, [
                        'associated' => ['FunctionalUnits']
                    ]);

                    /*  acá también se podrían guardar datos en otras tablas,
                        por ejemplo tablas asociadas a esta entidad.
                    */

                    //setteamos algunos campos manualmente
                    $payment_notification->api_user_id = $api_user_id;

                    /*  finalmente usamos saveOrFail en todas nuestras operaciones para que
                        el catch ataje las excepciones si el guardado falla.
                    */
                    return $this->saveOrFail($payment_notification, [
                        'associated' => ['FunctionalUnits']
                    ]);

                });
            }, $error_map);
        }
         ```

### Variables de configuracion

Las variables de configuración se guardan en el arreglo de configuración de la aplicación al igual que el resto de las configuraciones (`config/app.php` por defecto).

Las configuraciones disponibles son:

```php
'ApiGatewaySDK' => [
    /*
    Esto es para que las peticiones requieren el api_id que agrega APIGateway a las requests.
    Al setearlo como requerido, las peticiones que no vengan a través de APIGateway no serán aceptadas.
    */
    'api_id'                => 'rlrr9c1dk8',
    'require_api_id_header' => true,
]
```
