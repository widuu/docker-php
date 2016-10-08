<?php

namespace widuu\Docker\Module;

use widuu\Docker\Factory\SocketInterface;
use widuu\Docker\Factory\ResponseInterface;

class Module
{
	protected $response = null;

	protected $socket   = null;

	protected $query    = null;

	public function __construct(SocketInterface $socket, ResponseInterface $response )
	{
		$this->socket   = $socket;
		$this->response = $response;
	}

}