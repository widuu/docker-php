<?php

namespace widuu\Docker\Lib;


class Response
{
	private $socket  = null ;

	private $context = '';

	private $request = null;

	private $header  = [];

	private $http_status;

	private $http_code;

	private $http_version;

	private $body;


	public function getPackage( $package_name  )
	{
		Header( "Content-type:  application/octet-stream "); 
		Header( "Accept-Ranges: ".strlen($this->body)."bytes "); 
		Header( "Content-Disposition:attachment;filename={$package_name}"); 
		echo $this->body;
	}

	public function getBody()
	{
		return json_decode($this->body,true);
	}

	public function getCode()
	{
		return $this->http_code;
	}

	public function getHeader()
	{
		return $this->header;
	}

	public function resolveRawData( $raw_data )
	{
		list($header, $body) = explode("\r\n\r\n", $raw_data,2);
		$this->body = $body;
		$header_array = explode( "\r" , $header );
 		list( $http_version , $http_code , $http_status ) = explode( " "  , $header_array[0] );

 		$this->http_code 	= $http_code;
 		$this->http_status  = $http_status;
 		$this->http_version = $http_version;

 		unset($header_array[0]);

 		foreach ( $header_array as $v ) {
 			list($type,$value) = explode(':', $v);
 			$name = trim( strtolower ( str_replace('-','_', $type ) ) );
 			$this->header[$name] = trim($value);
 		}

 		return $this;
	}
}