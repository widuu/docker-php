<?php

namespace widuu\Docker;

use widuu\Docker\Lib\Response;
use widuu\Docker\Lib\Request;
use widuu\Docker\Lib\Socket;
use widuu\Docker\Lib\Log;

class Dispather
{
	/**
	 * Request Object
	 */

	private $request  = null;

	/**
	 * Response Object
	 */

	private $response = null;

	/**
	 * Socket Object
	 */

	private $socket = null; 

	/**
	 * Debug Info
	 */

	private $debug  = false;

	/**
	 * Log File Path
	 */

	private $log_file;


	public function __construct(Socket $socket)
	{
		$this->socket   = $socket;
		$this->request  = new Request();
		$this->response = new Response();
	}

	public function clearHeader( $flag = true ){
		$this->request->clearHeader($flag);
	}

	public function setDefault($type = '', $options = [] )
	{
		$type = 'set'.ucfirst(strtolower($type));
		$this->request->$type($options);
		if( $this->debug ){
			Log::write($this->log_file,$type,[
				'options' => var_export($options,true)
			]);
		}
	}

	public function setOption($path , $query = [] , $params =[], $flag = true)
	{
		$this->request->setOption($path , $query , $params, $flag );
		if( $this->debug ){
			Log::write($this->log_file,'setOption',[
				'path'	 => $path.'?'.http_build_query($query),
				'params' => var_export($params,true)
			]);
		}
		return $this;
	}

	public function setFileStream($filepath)
	{
		if( !file_exists($filepath) || !is_writable($filepath) ){
			throw new \Exception("File Not Exists Or File Not Writeable", 404);
		}

		$file_stream = '';

		$fp = fopen($filepath, 'rb');
		while( !feof($fp) ){
		    $file_stream .= fgets($fp,200);
		}

		fclose($fp);

		$this->request->setHeader([
			'Content-Type'   => 'application/json',
			'Content-Length' =>  strlen($file_stream),
			'Connection'     => 'close'
		]);

		$this->request->setBody($file_stream);
	}

	public function setHeader($params)
	{
		$this->request->setHeader($params);
	}

	public function getContext()
	{
		$this->socket->connect()->writeStream($this->request);
		if( $this->debug ){
			Log::write($this->log_file,'请求数据',[
				'data' => $this->request->getBody()
			]);
		}
		return $this->socket->read();
	}

	public function getBody( $raw_data = '' )
	{
		return $this->getResolveRawData()->getBody();
	}

	public function getCode( $raw_data = '' )
	{
		return $this->getResolveRawData( $raw_data = '' )->getCode();
	}

	public function getPackage( $package_name = '' )
	{
		if( empty($package_name) ) $package_name  = 'docker.tar.gz';
		$this->getResolveRawData()->getPackage($package_name);
	}

	public function getHeader( $name = '' )
	{
		$name = str_replace( '-' , '_' , strtolower($name) );
		$header = $this->getResolveRawData()->getHeader();
		if( !empty($name) && isset($header[$name]) ){
			return $header[$name];
		}
		return $header;
	}

	private function getResolveRawData( $raw_data = '' )
	{
		if( empty($raw_data) ){
			if( empty($this->socket->getContext) ){
				$raw_data = $this->getContext();
			}else{
				$raw_data = $this->socket->getContext();
			}
		}
		
		if( $this->debug ){
			if( strlen($raw_data) > 102400 ){
				$raw_data_log = 'STREAM FILE';
			}else{
				$raw_data_log = $raw_data;
			}
			Log::write($this->log_file,'接收数据',[
				'data' => $raw_data_log
			]);
		}
		return $this->response->resolveRawData($raw_data);
	}

	public function setDebug($debug,$file_path)
	{

		if( $this->debug ) return true;
		
		if( empty($file_path) ) $file_path = __DIR__;

		if( $debug ){
			$this->debug 	= true;
			$this->log_file = rtrim(realpath($file_path),'/').'/'.'Debug.log';
		}
	}
}