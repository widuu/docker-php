<?php

namespace widuu\Docker\Lib;

use widuu\Docker\Factory\SocketInterface;
use widuu\Docker\Factory\RequestInterface;
use widuu\Docker\Factory\ResPonseInterface;

class Socket implements SocketInterface
{
	/**
	 * @var 打开的Socket流资源
	 */

	private $socket = null;

	/**
	 * @var 是否可读
	 */

	private $read   = false;

	/**
	 * @var 是否可写
	 */

	private $write  = false;

	/**
	 * @var 是否读完毕
	 */

	private $eof	= false;

	/**
	 * socket 配置文件
	 */

	private $config = [
		'stream_type'   => 'unix',
		'remote_socket' => '/var/run/docker.sock',
        'timeout' 		=> null,
        'stream_context_options' => [
        	'cafile'      => null,
            'local_cert'  => null,
            'local_pk'    => null,
        ],
        'ssl' => null,
        'write_buffer_size' => 8192,
        //'ssl_method' => 'STREAM_CRYPTO_METHOD_TLS_CLIENT',
	];

	/**
	 * 读写 mode 
	 */

	private static $readWrite = [
		'write' => [
			'w' => true, 'r+' => true, 'w+' => true, 'x'  => true,
			'c' => true, 'a'  => true, 'a+' => true, 'c+' => true,
			'x+'=> true
		],
		'read'  => [
			'r' => true, 'r+' => true, 'w+' => true, 'a+' => true, 
			'x+'=> true, 'c+' => true
		]
	];

	/**
	 * 实例化Socket类
	 *
	 * @param array $socketConfig 配置文件
	 */

	public function __construct( $socketConfig = [] )
	{
		$this->parseConfig( $socketConfig );
	}

	/**
	 * 创建 Socket Stream
	 */

	public function createSocket()
	{
		// 如果存在Socket存在，直接返回Socket
		if( $this->socket != null && is_resource($this->socket) ) return $this->socket;
		
		$socketConfig = $this->config;

		if( empty($socketConfig['stream_type']) || empty($socketConfig['remote_socket']) ){
			throw new \Exception("Socket Type And Socket Remote is Null");
		}

		$separator = $socketConfig['stream_type'] == 'unix' ? 'unix:///' : $socketConfig['stream_type'].'://';
		$socketPath = $separator.trim($socketConfig['remote_socket'],'/');

		$streamContext = [];
		if( $socketConfig['ssl'] ){
			$streamContext['ssl'] = [];
			foreach( $socketConfig['stream_context_options'] as $name => $value ) {
				if( !empty($value) ) $streamContext['ssl'][$name] = $value;
			}
		}

		$errNo = null;
        $errMsg = null;
        // 配置超时时间
        if( $socketConfig['timeout'] == null ) $timeout = ini_get('default_socket_timeout');
        // 
        $socket = @stream_socket_client($socketPath, $errNo, $errMsg, $timeout, STREAM_CLIENT_CONNECT,stream_context_create($streamContext));

		if( false === $socket ) throw new \Exception($errMsg, $errNo);

		if( $socketConfig['ssl'] ){
			// if (false === @stream_socket_enable_crypto($socket, true, $this->config['ssl_method'])) {
			// 	throw new \Exception("Set SSL Type Error");
			// }
		}

		$this->getMetaData($socket);
		$this->socket = $socket;
	}

	public function getSocket(){
		if( !is_resource($this->socket) || $this->socket == null ){
			$this->createSocket();
		}
		return $this->socket;
	}

	/**
     * 写入数据
     *
     * @return bool
     */

	public function writeReauest(RequestInterface $request)
	{
		if( !$request instanceof RequestInterface ){
			throw new Exception("Request Not Instanceof RequestInterface");
		}

		$header = $request->getRequestHeader();
	}

	/**
     * 关闭 Socket 连接
     *
     * @return bool
     */

	public function close() 
	{	
		if( !$this->socket ) fclose($this->socket);
	}

	/**
     * 返回是否读取到结尾
     *
     * @return bool
     */

	public function eof()
	{
		return $this->eof;
	}

	/**
     * 返回是否可读
     *
     * @return bool
     */

	public function isReadable()
	{
		return $this->read;
	}

	/**
	 * 返回是否可写
	 * 
	 * @return bool
	 */

	public function isWriteable()
	{
		return $this->write;
	}
	
	/**
	 * 设置meta信息
	 * @param $sock
	 */
	
	public function getMetaData($socket)
	{
		$metaData = stream_get_meta_data($socket);
		$this->read  = isset(self::$readWrite['read'][$metaData['mode']]);
		$this->write = isset(self::$readWrite['write'][$metaData['mode']]);
		$this->eof   = isset($metaData['eof']) && !$metaData['eof'] ? false : true;
		$this->type  = $metaData['stream_type'];
	}
	
	/**
	 * 返回是否可写
	 * 
	 * @return bool
	 */

	public function parseConfig( $socketConfig )
	{	
		foreach ( $this->config as $name => $value) {
			if( isset($socketConfig[$name]) && !empty($socketConfig[$name]) ){
				$this->config[$name] = $socketConfig[$name];
			}
		}
	}
}