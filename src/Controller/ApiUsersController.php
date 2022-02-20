<?php
namespace Ccasanovas\CognitoSDK\Controller;

use Ccasanovas\CognitoSDK\Controller\AppController;
use Ccasanovas\CognitoSDK\Model\Table\ApiUsersTable;
use Ccasanovas\CognitoSDK\Controller\Traits\BaseCrudTrait;
use Ccasanovas\CognitoSDK\Controller\Traits\ImportApiUsersTrait;
use Ccasanovas\CognitoSDK\Controller\Traits\AwsCognitoTrait;
use Muffin\Footprint\Auth\FootprintAwareTrait;

class ApiUsersController extends AppController
{

    use FootprintAwareTrait;
    use BaseCrudTrait;
    use ImportApiUsersTrait;
    use AwsCognitoTrait;

    public $paginate = [
        'limit' => 100,
        'order' => ['ApiUsers.aws_cognito_username' => 'asc'],
    ];

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Search.Prg', [
            'actions' => ['index']
        ]);
    }
}
