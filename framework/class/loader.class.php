<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function load() {
	static $loader;
	if(empty($loader)) {
		$loader = new Loader();
	}
	return $loader;
}


class Loader {
	
	private $cache = array();
<<<<<<< HEAD
	
	function func($name) {
		global $_W;
		if (isset($this->cache['func'][$name])) {
=======
	private $singletonObject = array();
	private $libraryMap = array(
		'agent' => 'agent/agent.class',
		'captcha' => 'captcha/captcha.class',
		'pdo' => 'pdo/PDO.class',
		'qrcode' => 'qrcode/phpqrcode',
		'ftp' => 'ftp/ftp',
		'pinyin' => 'pinyin/pinyin',
		'pkcs7' => 'pkcs7/pkcs7Encoder',
		'json' => 'json/JSON',
		'phpmailer' => 'phpmailer/PHPMailerAutoload',
		'oss' => 'alioss/autoload',
		'qiniu' => 'qiniu/autoload',
		'cos' => 'cosv4.2/include',
		'cosv3' => 'cos/include',
	);
	private $loadTypeMap = array(
		'func' => '/framework/function/%s.func.php',
		'model' => '/framework/model/%s.mod.php',
		'classs' => '/framework/class/%s.class.php',
		'library' => '/framework/library/%s.php',
		'table' => '/framework/table/%s.table.php',
		'web' => '/web/common/%s.func.php',
		'app' => '/app/common/%s.func.php',
	);

	public function __call($type, $params) {
		global $_W;
		$name = $cachekey = array_shift($params);
		if (!empty($this->cache[$type]) && isset($this->cache[$type][$cachekey])) {
>>>>>>> parent of 775f72a... 654
			return true;
		}
		$file = IA_ROOT . '/framework/function/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['func'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Helper Function /framework/function/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
	function model($name) {
		global $_W;
		if (isset($this->cache['model'][$name])) {
			return true;
		}
<<<<<<< HEAD
		$file = IA_ROOT . '/framework/model/' . $name . '.mod.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['model'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Model /framework/model/' . $name . '.mod.php', E_USER_ERROR);
=======
		$file = sprintf($this->loadTypeMap[$type], $name);
		if (file_exists(IA_ROOT . $file)) {
			include IA_ROOT . $file;
			$this->cache[$type][$cachekey] = true;
			return true;
		} else {
			trigger_error('Invalid ' . ucfirst($type) . $file, E_USER_WARNING);
>>>>>>> parent of 775f72a... 654
			return false;
		}
	}
	
	function classs($name) {
		global $_W;
		if (isset($this->cache['class'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/framework/class/' . $name . '.class.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['class'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Class /framework/class/' . $name . '.class.php', E_USER_ERROR);
			return false;
		}
	}
	
	function web($name) {
		global $_W;
		if (isset($this->cache['web'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/web/common/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['web'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Web Helper /web/common/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
	function app($name) {
		global $_W;
		if (isset($this->cache['app'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/app/common/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['app'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid App Function /app/common/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
	function module($module, $file) {
		if (isset($this->cache['encrypte'][$name])) {
			return true;
		}
		if (strexists(file_get_contents($name), '<?php')) {
			$this->cache['encrypte'][$name] = true;
			require $name;
		} else {
			$key = cache_load('module:cloud:key:1');
			$vars = cache_load('module:cloud:vars:1');
			if (empty($vars)) {
				trigger_error('Module is missing critical files , please reinstall');
			}
			echo <<<EOF
\$_ENV = unserialize(base64_decode('$vars'));
EOF;
			
			
			exit;
		}
	}
}
