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
     * 关闭 Socket 连接
     *
     * @return bool
     */

	public function close();

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