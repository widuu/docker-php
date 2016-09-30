<?php

namespace widuu\Docker\Lib;

use widuu\Docker\Factory\ResponseInterface;
use widuu\Docker\Factory\SocketInterface;

class Response implements ResponseInterface
{	
	/**
	 * @var Response Header头信息
	 */

	private $header = [];

	/**
	 * @var 返回状态码说明
	 */

	private $statusInfo;

	/**
	 * @var 返回请求的状态码
	 */

	private $statusCode;

	/**
	 * @var 返回的 http 版本信息
	 */

	private $protocolVersion;

	/**
	 * @var 当前的socket
	 */

	private $socket;

	/**
	 * 读取 socket 信息 并设置头部信息，
	 *
	 * @param  resource      当前连接的Socket，两种连接模式http和unix可以同时连接
	 * @param  SocketObejct  widuu\Docker\Factory\SocketInterface
	 * @return widuu\Docker\Factory\ResponseInterface
	 */

	public function readResponse( $currentSocket )
	{
		if( !is_resource($currentSocket) ){
			$this->close();
			throw new \Exception("The First Param Must Be Socket Resource");
		}

		$this->socket = $currentSocket;
		
		if( !$socket->isReadable() ){
			$this->close();
			throw new \Exception("Reponse Socket Cann't Write");
		}

		$metaData = stream_get_meta_data($currentSocket);

		if( $metaData['timed_out'] ){
			$this->close();
			throw new \Exception("Reponse Pipe TimeOut");
		}

		$this->readHeader($currentSocket);
		return $this;
	}

	/**
	 * 读取socket里边的header头部信息
	 *
	 * @param  resource      当前连接的Socket，两种连接模式http和unix可以同时连接
	 * @return void
	 */

	public function readHeader($socket)
	{
		while ( !feof($socket) ) {
			$header = fgets($socket);
            if (rtrim($header) === '') {
                break;
            }
            $headers[] = trim($header);
        }

        list( $httpVersion, $statusCode, $statusInfo ) = explode(' ',$headers[0]);
        
        $this->statusCode  = $statusCode;
        $this->statusInfo  = $statusInfo;
        $this->protocolVersion = trim($httpVersion,'HTTP/');

		array_shift($headers);
		$headerArray = [];
		array_map(function($header) use (&$headerArray){
			list($name,$value)  = explode(':', $header);
			$headerArray[$name] = $value;
		},$headers);
	}

	/**
	 * 获取 HTTP 返回状态码
	 *
	 * @return int
	 */

	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/** 
     * 设置 HTTP 返回状态码和请求信息
     *
     * @param int     状态码
     * @param string  状态信息
     */

    public function setStatusCode($statusCode,$statusInfo = '')
    {
    	$this->statusCode = $statusCode;
    	$this->statusInfo = $statusInfo;
    }

    /**
	 * 返回 HTTP 版本信息 1.0||1.1 
	 *
	 * @return string
	 */

	public function getProtocolVersion()
	{
		return $this->protocolVersion;
	}

	/**
	 * 设置 HTTP 版本信息 1.0||1.1 
	 *
	 * @param  string 版本信息
	 * @return void
	 */

	public function setProtocolVersion($version)
	{
		$this->protocolVersion = $version;
	}

	/**
	 * 返回 HTTP 头部信息 
	 *
	 * @return array  
	 */

	public function getHeaders()
	{
		return $this->header;
	}

	/**
	 * 设置 HTTP 头部信息
	 * 
	 * @param  string[]  name => value
	 * @return void
	 */

	public function setHeader(array $header)
	{
		$this->header = $header;
	}

	/**
	 * 获取某个头部的信息
	 * 
	 * @param  string  例如 HOST
	 * @return string|bool 
	 */

	public function getHeader($name)
	{
		if( isset($this->header[$name]) && !empty($this->header[$name]) ){
			return $this->header[$name];
		}
		return false;
	}

	/**
	 * 删除某个头部信息
	 *
	 * @param string  例如 HOST
	 */

	public function removeHeader($name)
	{
		if( isset($this->header[$name]) ) unset($this->header[$name]);
	}

	/**
	 * 获取 Body 信息
	 * 
	 * @return
	 */

	public function getBody()
	{
		return $this->socket;
	}
	
	/**
	 * 关闭Socket
	 */
	
	public function close()
	{
		if( $this->socket && is_resource($this->socket) ){
			fclose($this->socket);
		} 
	}
	
	public function __destruct()
	{
		$this->close();
	}	
}