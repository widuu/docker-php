<?php

namespace widuu\Docker\Lib;

class Query
{
	private $params = [];

	private $url;

	public function __construct( $url = '' )
	{
		$this->url = $url;
	}

	public function setDefault($name,$value)
	{
		$this->params[$name] = $value;
	}

	public function setParams($params = [])
	{
		foreach ($params as $name => $value) {
			if( isset($this->params[$name]) ){
				if( $value == 'true'  || $value  ) $value = 1;
				if( $value == 'false' || !$value ) $value = 0;
				$this->params[$name] = $value;
			}
		}
	}

	public function setUrl($url)
	{
		$this->url = $url;
	}

	public function buildQuery($params = [])
	{
		if( count($params) > 0 ) $this->setParams($params);
		$params = http_build_query($this->params);
		if( !empty($params) ){
			return $this->url.'?'.$params;
		}
		return $this->url;
	}

}