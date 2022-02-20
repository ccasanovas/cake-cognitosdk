<?php
namespace Ccasanovas\CognitoSDK\Controller\Api;

use Ccasanovas\CognitoSDK\Controller\AppController as BaseController;

class AppController extends BaseController
{
	public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Auth');

        $this->loadComponent('Ccasanovas/ApiGatewaySDK.ApiRequest');

        $this->Auth->config('authenticate', [
            'Ccasanovas/CognitoSDK.AwsCognitoJwt' => [
            	'userModel' => 'Ccasanovas/CognitoSDK.ApiUsers'
            ]
        ]);

        $this->Auth->config('storage', 'Memory');
        $this->Auth->config('unauthorizedRedirect', false);
        $this->Auth->config('loginAction', false);
        $this->Auth->config('loginRedirect', false);
    }

}
