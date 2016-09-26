<?php

namespace Docker\Lib;


class Request
{
	private $method = 'GET';

	private $body;

	private $stream_data;

	private $url_path;

	private $http_version = '1.0';

	private $header = [
		'Host'   => 'localhost',
		'Accept' => '*/*'
	];

	public function setOption( $path , $query = [] , $params =[], $flag = true )
	{
		$this->url_path = trim( $path ,'?' );

		if( count($query) > 0  ){
			$query_string = http_build_query($query);
			$this->url_path .= '?'.$query_string;
		}

		if( count($params) > 0 ){
			if( $flag ){
				$body = str_replace('[]', '{}',json_encode($params,JSON_UNESCAPED_SLASHES));

				$this->setHeader([
					'Content-Type'   => 'application/json',
					'Content-Length' =>  strlen($body),
					'Connection'     => 'close'
				]);
				
				$this->body = $body;
			}
		}
	}

	public function getBody()
	{
		$request_header = $this->getHeaders();
		if( !empty($this->body) ){
			return $request_header.$this->body;
		}
		return $request_header;
	}

	public function getHeaders()
	{
		$request_header = vsprintf('%s %s HTTP/%s', [
        	strtoupper($this->method),
       		$this->url_path,
        	$this->http_version
    	])."\r\n";

    	foreach ($this->header as $name => $value) {
    		$request_header .= $name.': '.$value."\r\n";
    	}

    	$request_header .= "\r\n";

    	return $request_header;
	}


	public function setMethod( $method )
	{
		$method_array = [];
		if( !is_array($method) ){
			$method_array[]  = $method;
		}else{
			$method_array[0] = $method[0]; 
		}
		$this->method = $method_array[0];
	}


	public function setHeader( $options = [] )
	{
		if( count($options) > 0 ){
			foreach ($options as $name => $value) {
				$this->header[$name] = $value;
			}
		}
	}

	public function setBody( $body ){
		$this->body = $body;
	}

	public function clearHeader( $flag = true )
	{
		$this->header = [];
		if( $flag ){
			$this->header = [
				'Host'   => 'localhost',
				'Accept' => '*/*'
			];
		}
	}

}