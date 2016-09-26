<?php

namespace widuu\Docker\Lib;

class Socket
{
	/**
	 * unix Socket Path
	 */

	private $socket_path;
	
	/**
	 * timeout
	 */

	private $timeout;

	/**
	 * Socket Object
	 */

	private $socket = [];

	/**
	 * response Info
	 */

	private $context;


	public function __construct( $config ){

		if(empty($config['socket_path'])) throw new \Exception('No Setting Socket Path');

		$socket_path  = str_replace( '\\' , '/', $config['socket_path']);

		$this->socket_path  = 'unix:///'.trim( trim( $socket_path , 'unix:' ) , '/' );
		
		$timeout = ini_get('default_socket_timeout');

		if( !empty( $timeout ) ){
			$timeout = $config['timeout'];
		}

		$this->timeout = $timeout;
	}

	public function connect()
	{
		$this->socket = @stream_socket_client( $this->socket_path, $errno, $errstr, $this->timeout,  STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT );
		
		if( $errno != 0 ){
			throw new \Exception('Socket Error Info:'.$errstr);
		}

		return $this;
	}

	public function writeStream( Request $request )
	{
		$stream_data = $request->getBody();
		if( !$stream_data ) throw new Exception("Write Stream Empty", 400);

		if( $this->write($this->socket,$stream_data) == false ){
			throw new Exception("Write Stream Error", 400);
		}
	}

	public function write( $socket,$stream_data )
	{	
		if( strlen($stream_data) == 0 ){
			return false;
		}

		$result = @fwrite($socket, $stream_data);
        if ($result !== 0) {
            return $result;
        }

        $read = [];
        $write = [$socket];
        $except = [];

        @stream_select($read, $write, $except, 0);
        if (!$write) {
            return 0;
        }
        
        $result = @fwrite($socket, $stream_data);
        if ($result !== 0) {
            return $result;
        }

        return false;
	}

	public function read()
	{
		$meta_data = stream_get_meta_data($this->socket);
		if( isset($meta_data['timeout']) && $meta_data['timeout'] ){
			throw new Exception("Socket Read timeout", 1);
			$this->close();
		}
		$this->context = stream_get_contents($this->socket);
		$this->close();
		return $this->context;
	}

	public function getContext()
	{
		return $this->context;
	}

	public function close()
	{
		if( $this->socket ){ 
			fclose($this->socket);
		}
	}


}