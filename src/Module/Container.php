<?php

namespace widuu\Docker\Module;

use widuu\Docker\Lib\Query;
use widuu\Docker\Lib\Request;

class Container extends Module
{


	public function list( $params = [] )
	{
		$query = new Query('/containers/json');
		$query->setDefault('all', false);
		$query->setDefault('limit', null);
		$query->setDefault('since', null);
		$query->setDefault('before', null);
		$query->setDefault('size', false);
		$query->setDefault('filters', null);
		$request  = new Request('GET',$query->buildQuery($params));
		$response = $request->sendRequest($this->socket,$this->response);
		return json_decode($response->getBody(),true);
	}

	public function inspect( $id, $params = [] )
	{
		$query = new Query('/containers/'.$id.'/json');
		$query->setDefault('size', false);
		$request  = new Request('GET',$query->buildQuery($params));
		$response = $request->sendRequest($this->socket,$this->response);
		return json_decode($response->getBody(),true);
	}

	public function top( $id, $params = []  )
	{
		$query = new Query('/containers/'.$id.'/top');
		$query->setDefault('ps_args', null);
		$request  = new Request('GET',$query->buildQuery($params));
		$response = $request->sendRequest($this->socket,$this->response);
		return json_decode($response->getBody(),true);
	}

	public function logs( $id, $params = [] )
	{
		$query = new Query('/containers/'.$id.'/logs');
		$query->setDefault('details', false);
		$query->setDefault('follow', false);
		$query->setDefault('stdout', false);
		$query->setDefault('stderr', false);
		$query->setDefault('since', 0);
		$query->setDefault('timestamps', false);
		$query->setDefault('tail', null);
		$request  = new Request('GET',$query->buildQuery($params));
		$response = $request->sendRequest($this->socket,$this->response);
		return $response->getBody();
	}

	public function changes( $id )
	{
		$query = new Query('/containers/'.$id.'/changes');
		$request  = new Request('GET',$query->buildQuery());
		$response = $request->sendRequest($this->socket,$this->response);
		return json_decode($response->getBody(),true);
	}

	public function stats( $id, $params = [] )
	{
		$query = new Query('/containers/'.$id.'/stats');
		$query->setDefault('stream', false);
		$request  = new Request('GET',$query->buildQuery());
		$response = $request->sendRequest($this->socket,$this->response);
		return $response->getBody();
	}

	public function stop( $id, $params=[] )
	{
		return $this->manager($id,$params,'stop');
	}

	public function start( $id, $params=[] )
	{
		return $this->manager($id,$params,'start');
	}

	public function restart( $id, $params=[] )
	{
		return $this->manager($id,$params,'restart');
	}

	public function kill( $id, $params=[] )
	{
		return $this->manager($id,$params,'kill');
	}

	public function pause( $id )
	{
		return $this->manager($id,[],'pause');
	}

	public function unpause( $id )
	{
		return $this->manager($id,[],'unpause');
	}

	public function rename($id, $new_name)
	{
		$query = new Query('/containers/'.$id.'/rename?name='.$new_name);
		$request  = new Request('POST',$query->buildQuery());
		$response = $request->sendRequest($this->socket,$this->response);
		$status   = $response->getStatusCode(true);
		return $status;
	}

	private function manager($id, $params=[],  $type)
	{
		$query = new Query('/containers/'.$id.'/'.$type);
		$query->setDefault('t', null);
		$query->setDefault('signal', null);
		$request  = new Request('POST',$query->buildQuery());
		$response = $request->sendRequest($this->socket,$this->response);
		$status   = $response->getStatusCode(true);
		return $status;
	}

	public function test()
	{
		$query = new Query('/containers/f38084700134/json');
		$query->setDefault('all', false);
		$query->setDefault('limit', null);
		$query->setDefault('since', null);
		$query->setDefault('before', null);
		$query->setDefault('size', false);
		$query->setDefault('filters', null);
		$request  = new Request('GET',$query->buildQuery());
		$response = $request->sendRequest($this->socket,$this->response);
		var_dump($response->getBody());
	
	}
}