<?php

namespace Docker\Lib;


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

	public function __construct( $request )
	{
		if( !$request ) throw new Exception("Error No Request Object", 404);

		$this->request = $request;

		$this->request->connect();
	}

	public function getContext()
	{

		$this->socket = $this->request->getObject();

		if( !$this->socket ) throw new \Exception("Error No Stream Socket Object", 404);

		$this->request->write();
		$this->context = stream_get_contents($this->socket);
		$this->request->close();
		$this->setHeader();
		dump($this->context);
		return $this->context;
	}

	public function getTar( $tar_name = '' )
	{
		$this->getContext();
		Header( "Content-type:  application/octet-stream "); 
		Header( "Accept-Ranges: ".strlen($this->body)."bytes "); 
		Header( "Content-Disposition:attachment;filename={$tar_name}"); 
		echo $this->body;
		exit();
	}

	public function getResult()
	{
		$this->getContext();
		$result = json_decode($this->body,true);
		dump($result);
	}

	public function getStatus()
	{
		$this->getContext();
		return $this->http_code;
	}

	public function setHeader()
	{
		list($header, $body) = explode("\r\n\r\n", $this->context,2);
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

	}
}