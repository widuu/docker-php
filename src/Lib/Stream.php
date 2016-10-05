<?php

namespace widuu\Docker\Lib;

/**
 * stream 数据的读写类
 */

class Stream
{	
	/**
	 * @var 当前流
	 */

	private $stream;

	/**
	 * @var 是否可读
	 */

	private $readable;

	/**
	 * @var 是否可写
	 */

	private $writeable;

	/**
	 * @var 是否可移动
	 */

	private $seekable;

	/**
	 * @var 读写模式用于判断
	 */

    private static $readWrite = [
        'read' => [
            'r'   => true, 'w+'  => true, 'r+'  => true, 'x+'  => true, 'c+' => true,
            'rb'  => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt'  => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+'  => true
        ],
        'write' => [
            'w'   => true, 'w+'  => true, 'rw'  => true, 'r+'  => true, 'x+' => true,
            'c+'  => true, 'wb'  => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a'   => true, 'a+'  => true
        ]
    ];


    /**
     * 实例化stream 可以不是资源文件
	 *
     * @param  mix  $stream
     */

	public function __construct($stream)
	{
		// 如果不是资源文件
		if( is_scalar($stream) ){
			$pipeStream = fopen('php://temp', 'r+');
			fwrite($pipeStream, $stream);
			fseek($pipeStream, 0);
			$stream = $pipeStream;
		}

		$this->stream = $stream;
		$metaData = stream_get_meta_data($stream);
		$this->readable  = isset(self::$readWrite['read'][$metaData['mode']]);
		$this->writeable = isset(self::$readWrite['write'][$metaData['mode']]);
		$this->seekable = $metaData['seekable'];
	}

	/**
	 * 检测是否可读
	 *
	 * @return bool
	 */

	public function isReadable()
	{
		return $this->readable;
	}

	/**
	 * 检测是否可写
	 *
	 * @return bool
	 */

	public function isWriteable()
	{
		return $this->writeable;
	}

	/**
	 * 检测是否可移动
	 *
	 * @return bool
	 */

	public function isSeekable()
	{
		return $this->seekable;
	}

	/**
	 * 如果可fseek 执行fseek 定位到头部
	 */

	public function rewind()
    {	
    	if( $this->seekable ) fseek( $this->stream, 0 );
    	return true;
    }

	/**
	 * 检测流是否读取结束
	 * 
	 * @return bool 正确就是读取结束
	 */

	public function isEof()
	{
		return feof($this->stream);
	}

	/**
	 * 从流文件中读取数据
	 *
	 * @param  int  $length 读取长度
	 * @return mix  读取信息
	 */

	public function read($length)
	{
		return fread($this->stream, $length);
	}

	/**
	 * 关闭资源流
	 */

	public function close()
	{
		if( isset($this->stream) && is_resource($this->stream) ){
			fclose($this->stream);
		}
	}

	/**
	 * 销毁类的同时销毁流文件
	 */

	public function __destruct()
	{
		$this->close();
	}

}