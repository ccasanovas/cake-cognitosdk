<?php
namespace Ccasanovas\ApiGatewaySDK\Error;

use Cake\Error\ExceptionRenderer;
use Cake\Utility\Inflector;

class ApiExceptionRenderer extends ExceptionRenderer
{
    public function UnprocessableEntity($exception)
    {
        $message = $this->_message($exception, $exception->getCode());
        $url = $this->controller->request->getRequestTarget();
        $this->controller->response->header($exception->responseHeader());
        $this->controller->response->statusCode($exception->getCode());
        $viewVars = [
            'message'    => $message,
            'errors'     => $this->formatErrors($exception->getErrors()),
            'url'        => h($url),
            'error'      => $exception,
            'code'       => $exception->getCode(),
            '_serialize' => ['message', 'errors', 'url', 'code']
        ];
        $this->controller->set($viewVars);
        return $this->_outputMessage('error400');
    }

    protected function formatErrors($entity_errors)
    {
        $formatted_errors = [];
        foreach ($entity_errors as $field => $errors) {
            foreach ($errors as $code => $message) {
                $formatted_errors[$field][] = [
                    'code'    => Inflector::camelize(trim($code, '_')),
                    'message' => $message
                ];
            }
        }
        return array_filter($formatted_errors);
    }

}
