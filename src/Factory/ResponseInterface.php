<?php

namespace widuu\Docker\Factory;

interface ResponseInterface extends HttpInterface
{
	
	/**
	 * 获取 HTTP 返回状态码
	 *
	 * @return int
	 */

	public function getStatusCode();

	/** 
     * 设置 HTTP 返回状态码和请求信息
     *
     * @param int     状态码
     * @param string  状态信息
     */

    public function setStatusCode($code,$statusInfo = '');

    /** 
     * 读取数据信息
     *
     */

    public function readResponse( $currentSocket, SocketInterface $socket );
}