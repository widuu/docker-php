<?php

namespace Docker;

use Docker\Lib\Response;
use Docker\Lib\Request;

class Docker
{
	public $request;

	public $response;

	public function __construct( $socket_path = '/var/run/docker.sock', $timeout = ''  )
	{
		$this->request  = new Request( $socket_path,$timeout );
		$this->response = new Response($this->request);
	}

	public function listContainer( $params = [] )
	{
		$this->request->setTarget( '/containers/json' , $params );
		$this->response->getResult();
	}

	public function exportContainer( $id = "" , $tarName='docker.tar.gz' )
	{
		$this->request->setTarget('/containers/'.$id.'/export');
		$this->response->getTar($tarName);
	}

	public function createContainer( $params )
	{
		$this->request->setMethod('POST');
		$this->request->setHeader('Content-Type','application/json');
		$this->request->setHeader('Connection','close');
		$this->request->setTarget('/containers/create',$params);
		return $this->response->getStatus();
	}

	/**
	 * 停止指定的容器 
	 *
	 * @param  $id    int   镜像id
	 * @param  $time  int   多少秒后停止
	 * @return $code  int   204 – no error 
	 *						304 – container already stopped
	 *						404 – no such container
	 *						500 – server error
	 */

	public function stopContainer( $id = "",$time = 0 )
	{
		$this->request->setMethod('POST');
		$this->request->setTarget('/containers/'.$id.'/stop?t='.$time);
		return $this->response->getStatus();
	}

	public function manageContainer( $method = "" ,$id = "",$time = 0 )
	{
		$this->request->setMethod('POST');
		$this->request->setTarget('/containers/'.$id.'/'.$method.'?t='.$time);
		return $this->response->getStatus();
	}

	public function removeImage( $image_name = '' )
	{
		$this->request->setMethod('DELETE');
		$this->request->setTarget('/images/'.$image_name);
		$this->response->getResult();
	}


}