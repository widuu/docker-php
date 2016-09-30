<?php

namespace widuu\Docker\Lib;

class Log
{
	public static function write($file,$method,$params)
	{
		$now         = date("Y-m-d H:i:s");

        $path = dirname($file);
        !is_dir($path) && @mkdir($path, 0755, true);
        !file_exists($file) && @touch($file);
 		
 		$log_info = "[{$now} {$method}]\r\n";
 		if( is_array($params) && count($params) >0 ){
 			foreach ($params as $key => $value) {
 				$log_info .= strtoupper($key)." => ".$value."\r\n";
 			}
 		}
 		file_put_contents($file,$log_info ,FILE_APPEND);
	}
}