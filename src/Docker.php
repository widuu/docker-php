<?php

namespace widuu\Docker;

use widuu\Docker\Lib\Socket;
use widuu\Docker\Lib\Response;

class Docker
{

	/**
	 * @var module array
	 */

	private $modules = [];

	/**
	 * @var current Module
	 */

	private $currentModel = null;

	/**
	 * @var socket object
	 */

	private $socket;

	/**
	 * @var Response object
	 */

	private $response;

	/**
	 * 初始化Docker集合类，设置Module
	 * 
	 * @param  array  $config  Socket配置数组 [
	 *											'stream_type'   => 'unix',
	 *											'remote_socket' => '/var/run/docker.sock',
	 *									        'timeout' 		=> null,
	 *									        'stream_context_options' => [
	 *									        	'cafile'      => null,
	 *									            'local_cert'  => null,
	 *									            'local_pk'    => null,
	 *									        ],
	 *									        'ssl' => null,
	 *									        'write_buffer_size' => 8192,
	 *										];
	 * @param  string $module   要使用的Module
	 */

	public function __construct( $config = [], $module = '' )
	{
		$this->socket   = new Socket($config);
		$this->response = new Response();
		if( !empty($module) ) $this->setModule($module);
	}

	/**
	 * 切换Module类型
	 *
	 * @param  string $module 要设置的Module类型
	 * @return object self
	 */

	public function setModule($module)
	{
		$module = ucfirst(strtolower($module));
		$moduleClass = '\\widuu\\Docker\\Module\\'.$module;
		if( class_exists( $moduleClass ) ){
			if( !isset($this->modules[$module]) || !is_object($this->modules[$module]) ){
				$this->modules[$module] = new $moduleClass($this->socket,$this->response);
			}
			$this->currentModel = $this->modules[$module];
		}
		return $this;
	}
    
    /**
	 * 魔术方法实现Module方法访问
	 */                                                                                                                                                                            
	public function __call($method,$args)
	{
		if( $this->currentModel != null && is_object($this->currentModel) ){
			return call_user_func_array([$this->currentModel ,$method],$args);
		}
	}
}