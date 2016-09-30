<?php

namespace widuu\Docker\Factory;

interface SocketInterface
{

	/**
     * 写入数据
     *
     * @return bool
     */

	public function writeReauest(RequestInterface $request);

	/**
     * 读取数据
     *
     * @return bool
     */

	public function readResponse(ResponseInterface $response);

	/**
     * 获取数据内容
     *
     * @return bool
     */

	public function getContents();


	/**
     * 关闭 Socket 连接
     *
     * @return bool
     */

	public function close($id);

	/**
     * 返回是否读取到结尾
     *
     * @return bool
     */

	public function eof();

	/**
     * 返回是否可读
     *
     * @return bool
     */

	public function isReadable();

	/**
	 * 返回是否可写
	 * 
	 * @return bool
	 */

	public function isWriteable();
}