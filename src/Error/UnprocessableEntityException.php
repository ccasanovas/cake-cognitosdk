<?php
namespace Ccasanovas\ApiGatewaySDK\Error;

use Cake\Network\Exception\HttpException;


class UnprocessableEntityException extends HttpException
{
	protected $_errors = [];

    public function __construct($message = null, $code = 422)
    {
    	if(is_array($message)){
    		if(isset($message['errors'])){
	    		$this->_errors = $message['errors'];
	    		$this->_attributes['errors'] = $this->_errors;
	    	}
	    	if(isset($message['message'])){
	    		$message = $message['message'];
	    	}
    	}

        parent::__construct($message, $code);
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
