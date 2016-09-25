<?php

namespace Docker\Lib;


class Request
{
	private $socket;

	private $method = 'GET';

	private $header = [];

	private $target;

	private $socket_path;

	private $timeout;

	private $body;

	private $raw_data;

	public function __construct( $socket_path , $timeout='' )
	{
		
		if( empty($socket_path) ) throw new \Exception('Now Setting Socket Path');

		$socket_path  = str_replace( '\\' , '/', $socket_path);

		$this->socket_path  = 'unix:///'.trim( trim( $socket_path , 'unix:' ) , '/' );
		
		$timeout = ini_get('default_socket_timeout');

		if( !empty( $timeout ) ){
			$timeout = $timeout;
		}

		$this->timeout = $timeout;
		
	}

	public function connect(){
		$this->socket = @stream_socket_client( $this->socket_path, $errno, $errstr, $this->timeout,  STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT );
		
		if( $errno != 0 ){
			throw new \Exception('Socket Error Info:'.$errstr);
		}
	}


	public function setRequestHeader()
	{
		$message = vsprintf('%s %s HTTP/1.0', [
            strtoupper($this->method),
            $this->target,
        ])."\r\n";

        foreach ($this->getHeaders() as $name => $values) {
            $message .= $name.': '.$values."\r\n";
        }

        $message .= "\r\n";

        return $message;
	}

	public function write($data ="")
	{
		$stream_data = $this->setRequestHeader();
		fwrite($this->socket, $stream_data);
		if( $this->method == 'POST' && !empty( $this->raw_data ) ){
			fwrite($this->socket, $this->raw_data);
		}
		
	}

	public function setTarget( $path,$query = [] )
	{
		if( $this->method != 'POST' ){
			$this->target = trim( $path ,'?' );

			if( count($query) != 0 ){
				$query_string = http_build_query($query);
				$this->target .= '?'.$query_string;
			}
		}else if( $this->method == 'PUT' ){

		}else{
			$this->target = trim( $path ,'?' );
			$data = json_encode($query,JSON_UNESCAPED_SLASHES);
			$this->setHeader('Content-Length',strlen($data));
			$this->raw_data = str_replace('[]', '{}', $data);
		}
		
	}

	public function setMethod( $method = '' )
	{
		$this->method = $method;
	}

	public function getHeaders(){
		
		if( empty($this->header['Host']) ){
			$this->header['Host'] = 'localhost';
		}
		
		if( empty($this->header['Accept']) ){
			$this->header['Accept'] = ' */*';
		}

		return $this->header;
	}

	public function setDefault( $name,$value )
	{
		if( isset($this->{$name}) ){
			$this->{$name} = $value;
		}
	}

	public function setHeader( $name,$value )
	{

		$this->header[$name] = $value;
	}

	public function getObject()
	{
		if( $this->socket ){
			return $this->socket;
		}
		return false;
	}

	public function close()
	{
		fclose($this->socket);
	}
}