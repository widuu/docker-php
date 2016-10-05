<?php

namespace widuu\Docker\Factory;

interface RequestInterface extends HttpInterface
{

	/**
	 * 设置请求方法
	 *
	 * @param 
	 */

	public function setMethod($method);

	/**
	 * 获取当前的请求方法
	 *
	 * @return string
	 */

	public function getMethod();

	/**
	 * 设置请求的url
	 * 
	 * @param string
	 */

	public function setUri($uri);

	/**
	 * 获取请求的url
	 * 
	 * @return string
	 */

	public function getUri();

	/**
	 * 向服务发送 resquest 请求
	 * 
	 * @return string
	 */

	public function sendRequest( SocketInterface $socket, ResponseInterface $response ); 

}