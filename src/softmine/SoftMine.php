<?php

namespace {
	function safe_var_dump(){
		static $cnt = 0;
		foreach(func_get_args() as $var){
			switch(true){
				case is_array($var):
					echo str_repeat("  ", $cnt) . "array(" . count($var) . ") {" . PHP_EOL; // = \n
					foreach($var as $key => $value){
						echo str_repeat("  ", $cnt + 1) . "[" . (is_integer($key) ? $key : '"' . $key . '"') . "]=>" . PHP_EOL;
						++$cnt;
						safe_var_dump($value);
						--$cnt;
					}
					echo str_repeat("  ", $cnt) . "}" . PHP_EOL;
					break;
				case is_int($var):
					echo str_repeat("  ", $cnt) . "int(" . $var . ")" . PHP_EOL;
					break;
				case is_float($var):
					echo str_repeat("  ", $cnt) . "float(" . $var . ")" . PHP_EOL;
					break;
				case is_bool($var):
					echo str_repeat("  ", $cnt) . "bool(" . ($var === true ? "true" : "false") . ")" . PHP_EOL;
					break;
				case is_string($var):
					echo str_repeat("  ", $cnt) . "string(" . strlen($var) . ") \"$var\"" . PHP_EOL;
					break;
				case is_resource($var):
					echo str_repeat("  ", $cnt) . "resource() of type (" . get_resource_type($var) . ")" . PHP_EOL;
					break;
				case is_object($var):
					echo str_repeat("  ", $cnt) . "object(" . get_class($var) . ")" . PHP_EOL;
					break;
				case is_null($var):
					echo str_repeat("  ", $cnt) . "NULL" . PHP_EOL;
					break;
			}
		}
	}

	function dummy(){

	}
}	function safe_var_dump(){
		static $cnt = 0;
		foreach(func_get_args() as $var){
			switch(true){
				case is_array($var):
					echo str_repeat("  ", $cnt) . "array(" . count($var) . ") {" . PHP_EOL;
					foreach($var as $key => $value){
						echo str_repeat("  ", $cnt + 1) . "[" . (is_integer($key) ? $key : '"' . $key . '"') . "]=>" . PHP_EOL;
						++$cnt;
						safe_var_dump($value);
						--$cnt;
					}
					echo str_repeat("  ", $cnt) . "}" . PHP_EOL;
					break;
				case is_int($var):
					echo str_repeat("  ", $cnt) . "int(" . $var . ")" . PHP_EOL;
					break;
				case is_float($var):
					echo str_repeat("  ", $cnt) . "float(" . $var . ")" . PHP_EOL;
					break;
				case is_bool($var):
					echo str_repeat("  ", $cnt) . "bool(" . ($var === true ? "true" : "false") . ")" . PHP_EOL;
					break;
				case is_string($var):
					echo str_repeat("  ", $cnt) . "string(" . strlen($var) . ") \"$var\"" . PHP_EOL;
					break;
				case is_resource($var):
					echo str_repeat("  ", $cnt) . "resource() of type (" . get_resource_type($var) . ")" . PHP_EOL;
					break;
				case is_object($var):
					echo str_repeat("  ", $cnt) . "object(" . get_class($var) . ")" . PHP_EOL;
					break;
				case is_null($var):
					echo str_repeat("  ", $cnt) . "NULL" . PHP_EOL;
					break;
			}
		}
	}

	function dummy(){

	}


namespace softmine{
  use softmine\utils\Binary;
	use softmine\utils\MainLogger;
	use softmine\utils\ServerKiller;
	use softmine\utils\Terminal;
	use softmine\utils\Utils;
	use softmine\wizard\Installer;
	
	const VERSION = "1.7";
	const API_VERSION = "1.13.1";
	const CODENAME = "[REDACTED]";
	const MINECRAFT_VERSION = "v0.14.0 alpha";
	const MINECRAFT_VERSION_NETWORK = "0.14.0";
	
	if(\Phar::running(true) !== ""){
		@define("softmine\\PATH", \Phar::running(true) . "/");
	}else{
		@define("softmine\\PATH", \getcwd() . DIRECTORY_SEPARATOR);
		}
	}
	
	
