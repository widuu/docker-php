<?php

namespace widuu\Docker\Lib;

use widuu\Docker\Factory\RequestInterface;
use widuu\Docker\Factory\ResponseInterface;
use widuu\Docker\Factory\SocketInterface;

class Request implements RequestInterface
{

	/**
	 * 请求 version,默认使用 1.0
	 */

	private $protocol = '1.0';
	
	/**
	 * @var 请求方法
	 */

	private $method = 'GET';

	/**
	 * @var header 请求head头数组
	 */

	private $header = [];

	/**
	 * @var body 请求信息的 body
	 */

	private $body = null;

	/**
	 * @var url 地址
	 */

	private $uri = null;

	/**
	 * 实列化 Request 类
	 *
	 * @param string $method  请求方法
	 * @param string $uri     请求url
	 * @param array  $headers 请求头部数组
	 * @param mix    $body    可以为空，字符串和Resource
	 * @param string $version HTTP的协议版本
	 */
	
	public function __construct( $method, $uri, array $headers = [], $body = null, $version='1.0' )
	{
		$this->method = strtoupper($method);
		$this->uri    = $uri;
		$this->setHeader($headers);

		if( $body != null || !empty($body) ){
			$this->body = $body;
		}
	}

	/**
	 * 返回 HTTP 版本信息 1.0||1.1 
	 *
	 * @return string
	 */

	public function getProtocolVersion()
	{
		return $this->protocol;
	}

	/**
	 * 设置 HTTP 版本信息 1.0||1.1 
	 *
	 * @param  string 版本信息
	 * @return void
	 */

	public function setProtocolVersion($version)
	{
		$this->protocol = $version;
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
		foreach ($header as $name => $value) {
			$this->header[$name] = $value;
		}
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
		if( isset($this->header[$name]) && !empty($this->header[$name]) ){
			unset($this->header[$name]);
		}
		return true;
	}

	/**
	 * 获取 Body 信息
	 * 
	 * @return
	 */

	public function getBody()
	{
		return $this->body;
	}


	/**
	 * 设置请求方法
	 *
	 * @param 
	 */

	public function setMethod($method)
	{
		$this->method = strtoupper($method);
	}

	/**
	 * 获取当前的请求方法
	 *
	 * @return string
	 */

	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * 设置请求的url
	 * 
	 * @param string
	 */

	public function setUri($uri)
	{
		$this->uri = $uri;
	}

	/**
	 * 获取请求的url
	 * 
	 * @return string
	 */

	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * 获取拼接好的 Request 信息
	 */

	public function getRequestHeader()
	{
		$separator = "\r\n";

		$requestHeader = vsprintf('%s %s HTTP/%s',[
			strtoupper($this->method),
			$this->uri,
			$this->protocol	
		]).$separator;

		if( count($this->header) != 0 ){
			foreach ($this->header as $name => $value) {
				$requestHeader .= $name.':'.$value.$separator;
			}
		}

		$requestHeader .= $separator;

		return $requestHeader;
	}
	
	/**
	 * 往 Socket 中写入头部信息
	 * 
	 * @param  resource $socket 当前的连接的Socket信息
	 */

	public function writeHeader($socket)
	{
		if( false == fwrite($socket, $this->getRequestHeader()) ){
			return false;
		}
		return true;
	}
	
	/**
	 * 往 Socket 写入body
	 * 
	 * @param  resource $socket 当前的连接的Socket信息
	 * @return bool 
	 */

	public function writeBody($socket)
	{
		if( empty($this->body) || $this->body == null ) return true;
		// 如果 body 存在就读取将 body 转换成 stream 流
		$stream = new Stream($this->body);
		// 如果流文件可读，先seek(0)
		if( $stream->isReadable() && $stream->rewind() ){
			return $this->writeRequestBody($socket,$stream);
		}
		return false;
	}
	
	/**
	 * 往 Socket 中写入头部信息
	 * 
	 * @param  resource $socket 当前的连接的Socket信息
	 * @return bool
	 */

	protected function writeRequestBody($socket, $stream, $bufferSize = 4096)
    {	
    	$result = false;
    	// 如果文件是大字符串或者是文件流使用分片写入socket
        while ( !$stream->isEof() ) {
        	// 分片读取
            $buffer = $stream->read($bufferSize);
            // 分片写入
            $writeStatus = fwrite($socket, $buffer);
            $result = $writeStatus === false ? false : true;
        }
        // 关闭流文件
        $stream->close();
        return $result;
    }

	/**
	 * 向服务器发送Request请求
	 * 
	 * @param  resource        $socket 当前的连接的Socket信息
	 * @param  responseObject  widuu\Docker\Factory\ResponseInterface
	 * @return responseObject  widuu\Docker\Factory\ResponseInterface
	 */
	
	public function sendRequest(SocketInterface $socket,ResponseInterface $response)
	{	
		$currentSocket = null;

		$currentSocket = $socket->getSocket();
		
		// 如果当前的 Socket 未空 
		if( $currentSocket == null ) throw new \Exception("Socket Connect Error");
		

		$status = $this->writeHeader($currentSocket) && $this->writeBody($currentSocket);
		
		if( !$status ){
			$socket->close();
			throw new \Exception("Socket Request Client Write Error");
		}

		return  $response->readResponse( $currentSocket,$socket );
	}

}