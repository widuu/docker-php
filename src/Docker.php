<?php

namespace widuu\Docker;

use widuu\Docker\Lib\Socket;

class Docker
{
	/**
	 * Dispather Object
	 */

	private $dispather = null;

	/**
	 * socket_path - docker unix socket path
	 * timeout 
	 */

	private $config = [
		'socket_path' => '/var/run/docker.sock',
		'timeout'     => null
	];

	

	public function __construct( $config = []  )
	{
		if( !$config ) $config = $this->config;
		$this->dispather = new Dispather(new Socket($config));	
	}


	public function listContainer( $query = [] )
	{
		$raw_data = $this->dispather->setOption( '/containers/json' , $query );
		return $this->dispather->getBody( $raw_data );
	}

	public function exportContainer( $id = "" , $package_name='docker.tar.gz' )
	{
		$raw_data = $this->dispather->setOption( '/containers/'.$id.'/export' );
		return $this->dispather->getPackage( $package_name );
	}

	public function createContainer( $params )
	{
		$this->dispather->setDefault('method',['POST']);
		$raw_data = $this->dispather->setOption( '/containers/create',[],$params );
		return $this->dispather->getBody( $raw_data );
	}

	/**
	 * 管理容器运行状态 
	 *
	 * @param  $method string   start|stop|restart
	 * @param  $id     int   	镜像id
	 * @param  $time   int   	多少秒后停止
	 * @return $code   int   	204 – no error 
	 *						 	304 – container already stopped
	 *						 	404 – no such container
	 *						 	500 – server error
	 */

	public function manageContainer( $method = "" ,$container_id = "",$time = 0 )
	{
		$this->dispather->setDefault('method',['POST']);
		$raw_data = $this->dispather->setOption( '/containers/'.$container_id.'/'.$method.'?t='.$time );
		return $this->dispather->getCode( $raw_data );
	}

	public function pullArchiveContainer( $container_id, $container_path , $package_name='docker.tar.gz' )
	{
		$this->dispather->setDefault('method',['GET']);
		$raw_data = $this->dispather->setOption( '/containers/'.$container_id.'/archive',['path'=>$container_path] );
		$this->dispather->getPackage( $package_name );
		// $return = $this->dispather->getHeader( 'X-Docker-Container-Path-Stat' );
		// return json_decode(base64_decode($return),true);
	}

	public function pushArchiveContainer( $container_id  , $container_path , $filepath )
	{
		$this->dispather->setDefault('method',['PUT']);
		$this->dispather->setFileStream($filepath);
		$raw_data = $this->dispather->setOption( '/containers/'.$container_id.'/archive',['path'=>$container_path] );
		return $this->dispather->getCode( $raw_data );
	}

	public function listImage( $query = [] )
	{
		$raw_data = $this->dispather->setOption( '/images/json' , $query );
		return $this->dispather->getBody( $raw_data );
	}

	public function buildImage( $filepath ,  $query=[] , $registry_config = [] )
	{
		$this->dispather->setDefault('method','POST');
		if( count($registry_config) > 0 ){
			$config = base64_encode(json_encode($registry_config));
			$this->dispather->setHeader([ 'X-Registry-Config' => $config ]);
		}
		$this->dispather->setFileStream($filepath);
		$raw_data = $this->dispather->setOption( '/build', $query);
		return $this->dispather->getCode( $raw_data );
	}

	public function removeImage( $image_name = '' )
	{
		$this->dispather->setDefault('method',['DELETE']);
		$raw_data = $this->dispather->setOption( '/images/'.$image_name );
		return $this->dispather->getBody( $raw_data );
	}

	public function setDebug( $open = true,$filepath = '' )
	{
		$this->dispather->setDebug($open,$filepath);
	}



}