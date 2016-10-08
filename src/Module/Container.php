<?php

namespace widuu\Docker\Module;

use widuu\Docker\Lib\Query;
use widuu\Docker\Lib\Request;

class Container extends Module
{
	private static $errorInfo = [
		200 => 'no error',
		400 => 'bad parameter',
		500 => 'server error'
	];

	public function list( $params=[] )
	{
		$query = new Query('/containers/json');
		$query->setDefault('all', false);
		$query->setDefault('limit', null);
		$query->setDefault('since', null);
		$query->setDefault('before', null);
		$query->setDefault('size', false);
		$query->setDefault('filters', null);
		$request  = new Request('GET',$query->buildQuery());
		$response = $request->sendRequest($this->socket,$this->response);
		$statusCode = $response->getStatusCode(true);
		var_dump(self::$errorInfo[$statusCode]);
		
		//var_dump($response->getBody());
	}

	public function test()
	{
		$query = new Query('/containers/f38084700134/json');
		$query->setDefault('all', false);
		$query->setDefault('limit', null);
		$query->setDefault('since', null);
		$query->setDefault('before', null);
		$query->setDefault('size', false);
		$query->setDefault('filters', null);
		$request  = new Request('GET',$query->buildQuery());
		$response = $request->sendRequest($this->socket,$this->response);
		var_dump($response->getBody());
	
	}
}