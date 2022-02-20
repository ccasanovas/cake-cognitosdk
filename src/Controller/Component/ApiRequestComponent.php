<?php
namespace Ccasanovas\ApiGatewaySDK\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\Routing\Router;
use Cake\Core\Configure;
use Cake\Network\Exception\BadRequestException;

class ApiRequestComponent extends Component
{

    protected $_defaultConfig = [
        'allowedContentTypes' => ['application/json']
    ];

    public $_apiRoute = null;

	public function beforeFilter(Event $event)
	{
        $controller = $event->getSubject();

		$controller->viewBuilder()->setClassName('Json');

		if(Configure::check('ApiGatewaySDK.require_api_id_header')
        && Configure::read('ApiGatewaySDK.require_api_id_header')){

            if(!Configure::check('ApiGatewaySDK.api_id')){
                throw new Exception('The AWS APIGateway api_id is not properly configured');
            }

            $config_apigateway_id = Configure::read('ApiGatewaySDK.api_id');
            $header_apigateway_id = $controller->request->getHeaderLine('X-Amzn-Apigateway-Api-Id');
            if($header_apigateway_id !== $config_apigateway_id){
                throw new BadRequestException('The APIGateway header is missing or incorrect');
            }
        }

        $allowedContentTypes = $this->getConfig('allowedContentTypes');
        if($controller->request->is(['put', 'post', 'patch'])
        && $allowedContentTypes
        && !in_array($controller->request->getHeaderLine('Content-Type'), $allowedContentTypes)){
            throw new BadRequestException(sprintf(
                __d('ApiGatewaySDK', '%s requests only allow the following Content-Type headers: %s'),
                $controller->request->getMethod(),
                implode(', ', $allowedContentTypes)
            ));
        }

        if($controller->request->getHeaderLine('Accept') !== 'application/json'){
            throw new BadRequestException(
                __d('ApiGatewaySDK', 'API requests require Header "Accept: application/json"')
            );
        }

	}


	public function beforeRender(Event $event)
    {
        $controller = $event->getSubject();

        $this->_apiRoute = $controller->viewVars['_apiRoute'] ?? null;
        unset($controller->viewVars['_apiRoute']);

        /* CORS Headers */
        $event->getSubject()->response->cors($controller->request)
            ->allowOrigin(['*'])
            ->allowMethods(['*'])
            ->allowHeaders([
                'x-xsrf-token',
                'Origin',
                'Content-Type',
                'X-Auth-Token',
                'X-Amz-Date',
                'Authorization',
                'X-Api-Key',
                'X-Amz-Security-Token'
            ])
            ->allowCredentials()
            ->exposeHeaders(['Link'])
            ->maxAge(300)
            ->build();

        /* Paging */
        $paging = $controller->request->getParam('paging', false);
        if($paging && !empty($paging[$controller->modelClass])){
            $controller->set([
                'paging'    => [
                    'page'      => $paging[$controller->modelClass]['page'],
                    'current'   => $paging[$controller->modelClass]['current'],
                    'count'     => $paging[$controller->modelClass]['count'],
                    'perPage'   => $paging[$controller->modelClass]['perPage'],
                    'prevPage'  => $paging[$controller->modelClass]['prevPage'],
                    'nextPage'  => $paging[$controller->modelClass]['nextPage'],
                    'pageCount' => $paging[$controller->modelClass]['pageCount'],
                    'sort'      => $paging[$controller->modelClass]['sort'],
                    'direction' => $paging[$controller->modelClass]['direction'],
                ]
            ]);

            /* Link Headers */
            $link_headers = $this->getLinkHeaders($paging[$controller->modelClass], $controller);
            if(!empty($link_headers)){
                $this->response = $this->response->withHeader('Link', $link_headers);
            }
        }

        /* Serialize */
        $controller->set('_serialize', true);
    }

    protected function getLinkHeaders($paging, $controller)
    {
        /* abstract https://tools.ietf.org/html/rfc5988 */

        if(!$paging || $paging['pageCount'] === 1) return '';

        $getUrlWithPage = function($page) use ($controller){
            $route = $this->_apiRoute ?? Router::parseRequest($controller->request);
            $route['?']['page'] = $page;
            return Router::reverse($route, true);
        };
        $link_headers = [];

        if($paging['page'] > 1){
            $link_headers['first'] = $getUrlWithPage(1);
            $link_headers['prev'] = $getUrlWithPage($paging['page'] - 1);
        }

        if($paging['page'] < $paging['pageCount']){
            $link_headers['next'] = $getUrlWithPage($paging['page'] + 1);
            $link_headers['last'] = $getUrlWithPage($paging['pageCount']);
        }

        $link_headers = array_map(function($url, $key){
            return "<$url>; rel=\"$key\"";
        }, $link_headers, array_keys($link_headers));

        return implode(', ', $link_headers);
    }

}
