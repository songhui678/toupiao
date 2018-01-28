<?php
/**
 * [We8 System] Copyright (c) 2017 
 * 
 */
define('IN_IA', true);
error_reporting(0);
@set_time_limit(0);
ob_start();
define('IA_ROOT', str_replace("\\",'/', dirname(__FILE__)));
define('APP_URL', 'http://www.we8.club/we8_apc-front.html');
define('APP_STORE_URL', 'http://www.we8.club/we8_apc-front.html');
define('APP_STORE_API', 'http://www.we8.club/api.php?mod=js&bid=155');
if($_GET['res']) {
	$res = $_GET['res'];
	$reses = tpl_resources();
	if(array_key_exists($res, $reses)) {
		if($res == 'css') {
			header('content-type:text/css');
		} else {
			header('content-type:image/png');
		}
		echo base64_decode($reses[$res]);
		exit();
	}
}
$actions = array('license', 'env', 'db', 'finish');
$action = $_COOKIE['action'];
$action = in_array($action, $actions) ? $action : 'license';
$ispost = strtolower($_SERVER['REQUEST_METHOD']) == 'post';

if(file_exists(IA_ROOT . '/data/install.lock') && $action != 'finish') {
	header('location: ./index.php');
	exit;
}
header('content-type: text/html; charset=utf-8');
if($action == 'license') {
	if($ispost) {
		setcookie('action', 'env');
		header('location: ?refresh');
		exit;
	}
	tpl_install_license();
}
if($action == 'env') {
	if($ispost) {
		setcookie('action', $_POST['do'] == 'continue' ? 'db' : 'license');
		header('location: ?refresh');
		exit;
	}
	$ret = array();
	$ret['server']['os']['value'] = php_uname();
	if(PHP_SHLIB_SUFFIX == 'dll') {
		$ret['server']['os']['remark'] = '建议使用 Linux 系统以提升程序性能';
		$ret['server']['os']['class'] = 'warning';
	}
	$ret['server']['sapi']['value'] = $_SERVER['SERVER_SOFTWARE'];
	if(PHP_SAPI == 'isapi') {
		$ret['server']['sapi']['remark'] = '建议使用 Apache 或 Nginx 以提升程序性能';
		$ret['server']['sapi']['class'] = 'warning';
	}
	$ret['server']['php']['value'] = PHP_VERSION;
	$ret['server']['dir']['value'] = IA_ROOT;
	if(function_exists('disk_free_space')) {
		$ret['server']['disk']['value'] = floor(disk_free_space(IA_ROOT) / (1024*1024)).'M';
	} else {
		$ret['server']['disk']['value'] = 'unknow';
	}
	$ret['server']['upload']['value'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';

	$ret['php']['version']['value'] = PHP_VERSION;
	$ret['php']['version']['class'] = 'success';
	if(version_compare(PHP_VERSION, '5.3.0') == -1) {
		$ret['php']['version']['class'] = 'danger';
		$ret['php']['version']['failed'] = true;
		$ret['php']['version']['remark'] = 'PHP版本必须为 5.3.0 以上，强烈建议您使用 7.0';
	}

	$ret['php']['pdo']['ok'] = extension_loaded('pdo') && extension_loaded('pdo_mysql');
	if($ret['php']['pdo']['ok']) {
		$ret['php']['pdo']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['pdo']['class'] = 'success';
		$ret['php']['pdo']['remark'] = '';
	} else {
		$ret['php']['pdo']['failed'] = true;
		$ret['php']['pdo']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['pdo']['class'] = 'danger';
		$ret['php']['pdo']['remark'] = '您的PHP环境不支持PDO, 系统无法正常运行';
	}

	$ret['php']['curl']['ok'] = extension_loaded('curl') && function_exists('curl_init');
	if($ret['php']['curl']['ok']) {
		$ret['php']['curl']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['curl']['class'] = 'success';
		$ret['php']['curl']['remark'] = '';
	} else {
		$ret['php']['curl']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['curl']['class'] = 'danger';
			$ret['php']['curl']['remark'] = '您的PHP环境不支持cURL, 也不支持 allow_url_fopen, 系统无法正常运行';
		$ret['php']['curl']['failed'] = true;
	}

	$ret['php']['ssl']['ok'] = extension_loaded('openssl');
	if($ret['php']['ssl']['ok']) {
		$ret['php']['ssl']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['ssl']['class'] = 'success';
	} else {
		$ret['php']['ssl']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['ssl']['class'] = 'danger';
		$ret['php']['ssl']['failed'] = true;
		$ret['php']['ssl']['remark'] = '没有启用OpenSSL, 将无法访问公众平台的接口, 系统无法正常运行';
	}

	$ret['php']['gd']['ok'] = extension_loaded('gd');
	if($ret['php']['gd']['ok']) {
		$ret['php']['gd']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['gd']['class'] = 'success';
	} else {
		$ret['php']['gd']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['gd']['class'] = 'danger';
		$ret['php']['gd']['failed'] = true;
		$ret['php']['gd']['remark'] = '没有启用GD, 将无法正常上传和压缩图片, 系统无法正常运行';
	}

	$ret['php']['dom']['ok'] = class_exists('DOMDocument');
	if($ret['php']['dom']['ok']) {
		$ret['php']['dom']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['dom']['class'] = 'success';
	} else {
		$ret['php']['dom']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['dom']['class'] = 'danger';
		$ret['php']['dom']['failed'] = true;
		$ret['php']['dom']['remark'] = '没有启用DOMDocument, 将无法正常安装使用模块, 系统无法正常运行';
	}

	$ret['php']['session']['ok'] = ini_get('session.auto_start');
	if($ret['php']['session']['ok'] == 0 || strtolower($ret['php']['session']['ok']) == 'off') {
		$ret['php']['session']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['session']['class'] = 'success';
	} else {
		$ret['php']['session']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['session']['class'] = 'danger';
		$ret['php']['session']['failed'] = true;
		$ret['php']['session']['remark'] = '系统session.auto_start开启, 将无法正常注册会员, 系统无法正常运行';
	}

	$ret['php']['asp_tags']['ok'] = ini_get('asp_tags');
	if(empty($ret['php']['asp_tags']['ok']) || strtolower($ret['php']['asp_tags']['ok']) == 'off') {
		$ret['php']['asp_tags']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['php']['asp_tags']['class'] = 'success';
	} else {
		$ret['php']['asp_tags']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['php']['asp_tags']['class'] = 'danger';
		$ret['php']['asp_tags']['failed'] = true;
		$ret['php']['asp_tags']['remark'] = '请禁用可以使用ASP 风格的标志，配置php.ini中asp_tags = Off';
	}

	$ret['write']['root']['ok'] = local_writeable(IA_ROOT . '/');
	if($ret['write']['root']['ok']) {
		$ret['write']['root']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['write']['root']['class'] = 'success';
	} else {
		$ret['write']['root']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['write']['root']['class'] = 'danger';
		$ret['write']['root']['failed'] = true;
		$ret['write']['root']['remark'] = '本地目录无法写入, 将无法使用自动更新功能, 系统无法正常运行';
	}
	$ret['write']['data']['ok'] = local_writeable(IA_ROOT . '/data');
	if($ret['write']['data']['ok']) {
		$ret['write']['data']['value'] = '<span class="glyphicon glyphicon-ok text-success"></span>';
		$ret['write']['data']['class'] = 'success';
	} else {
		$ret['write']['data']['value'] = '<span class="glyphicon glyphicon-remove text-danger"></span>';
		$ret['write']['data']['class'] = 'danger';
		$ret['write']['data']['failed'] = true;
		$ret['write']['data']['remark'] = 'data目录无法写入, 将无法写入配置文件, 系统无法正常安装. ';
	}

	$ret['continue'] = true;
	foreach($ret['php'] as $opt) {
		if($opt['failed']) {
			$ret['continue'] = false;
			break;
		}
	}
	if($ret['write']['failed']) {
		$ret['continue'] = false;
	}
	tpl_install_env($ret);
}
if($action == 'db') {
	if($ispost) {
		if($_POST['do'] != 'continue') {
			setcookie('action', 'env');
			header('location: ?refresh');
			exit();
		}
		$family = $_POST['family'] == 'x' ? 'x' : 'v';
		$db = $_POST['db'];
		$user = $_POST['user'];
		list($host, $port) = explode(':', $db['server']);
		if (empty($port)) {
			$port = '3306';
		}
		try {
			$link = new PDO("mysql:host={$host};port={$port}", $db['username'], $db['password']);
		} catch (Exception $error) {
			$error = $error->getMessage();
			if (strpos($error, 'Access denied for user') !== false) {
				$error = '您的数据库访问用户名或是密码错误. <br />';
			} else {
				$error = iconv('gbk', 'utf-8', $error);
			}
		}
		if(!empty($link)) {
			$link->query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
			$link->query("SET sql_mode=''");

			if($link->errorCode() != '00000') {
				$error = $link->errorInfo();
				$error = $error[2];
			} else {
				$db_found = $link->query("SHOW DATABASES LIKE '{$db['name']}';")->fetchColumn();
				if (empty($db_found)) {
					if(version_compare($link->query('select version()')->fetchColumn(), '4.1') == '1') {
						$link->query("CREATE DATABASE IF NOT EXISTS `{$db['name']}` DEFAULT CHARACTER SET utf8");
					} else {
						$link->query("CREATE DATABASE IF NOT EXISTS `{$db['name']}`");
					}
				}
				$db_found = $link->query("SHOW DATABASES LIKE '{$db['name']}';")->fetchColumn();
				if (empty($db_found)) {
					$error .= "数据库不存在且创建数据库失败. <br />";
				}
				if($link->errorCode() != '00000') {
					$errorinfo = $link->errorInfo();
					$error .= $errorinfo[2];
				}
			}
		}
		if(empty($error)) {
			$link->query("USE `{$db['name']}`");
			$table_found = $link->query("SHOW TABLES LIKE '{$db['prefix']}%';")->fetchColumn();
			if (!empty($table_found)) {
				$error = '您的数据库不为空，请重新建立数据库或是清空该数据库或更改表前缀！';
			}
		}
		if(empty($error)) {
			$config = local_config();
			$cookiepre = local_salt(4) . '_';
			$authkey = local_salt(8);
			$config = str_replace(array(
				'{db-server}', '{db-username}', '{db-password}', '{db-port}', '{db-name}', '{db-tablepre}', '{cookiepre}', '{authkey}', '{attachdir}'
			), array(
				$host, $db['username'], $db['password'], $port, $db['name'], $db['prefix'], $cookiepre, $authkey, 'attachment'
			), $config);
			$verfile = IA_ROOT . '/framework/version.inc.php';
			$dbfile = IA_ROOT . '/data/db.php';

/* 			if($_POST['type'] == 'remote') {
				$link = null;
				$ins = remote_install();
				if(empty($ins) || !is_array($ins)) {
					die('<script type="text/javascript">alert("连接不到服务器, 请稍后重试！");history.back();</script>');
				}
				if($ins['error']) {
					die('<script type="text/javascript">alert("链接维吧更新服务器失败, 错误为: ' . $ins['error'] . '！");history.back();</script>');
				}
				$archive = $ins['files'];
				if(!$archive) {
					die('<script type="text/javascript">alert("未能下载程序包, 请确认你的安装程序目录有写入权限. 多次安装失败, 请访问论坛获取解决方案！");history.back();</script>');
				}
				$link = new PDO("mysql:dbname={$db['name']};host={$host};port={$port}", $db['username'], $db['password']);
				$link->query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
				$link->query("SET sql_mode=''");

				$version = $ins['version'];
				$release = $ins['release'];
				$family = $ins['family'];
								$tmpfile = IA_ROOT . '/we8source.tmp';
				file_put_contents($tmpfile, $archive);
				local_mkdirs(IA_ROOT . '/data');
				file_put_contents(IA_ROOT . '/data/db.php', base64_decode($ins['schemas']));

				$fp = fopen($tmpfile, 'r');
				if ($fp) {
					$buffer = '';
					while (!feof($fp)) {
						$buffer .= fgets($fp, 4096);
						if($buffer[strlen($buffer) - 1] == "\n") {
							$pieces = explode(':', $buffer);
							$path = base64_decode($pieces[0]);
							$dat = base64_decode($pieces[1]);
							$fname = IA_ROOT . $path;
							local_mkdirs(dirname($fname));
							file_put_contents($fname, $dat);
							$buffer = '';
						}
					}
					fclose($fp);
				}
				unlink($tmpfile);
			} else { */
				if (file_exists($verfile)) {
					include $verfile;
					$version = IMS_VERSION;
					$release = IMS_RELEASE_DATE;
				} else {
					$version = '';
					$release = date('YmdHis');
				}
			//}
$verdat = <<<VER
<?php
/**
 * 版本号
 *
 * [WE8 System] Copyright (c) 2018 WE8.CLUB
 */

defined('IN_IA') or exit('Access Denied');

define('IMS_FAMILY', '{$family}');
define('IMS_VERSION', '{$version}');
define('IMS_RELEASE_DATE', '{$release}');
VER;
			$is_ok = file_put_contents($verfile, $verdat);
			if(!$is_ok) {
				die('<script type="text/javascript">alert("生成版本文件失败");history.back();</script>');
			}
			if(file_exists(IA_ROOT . '/index.php') && is_dir(IA_ROOT . '/web') && file_exists($verfile) && file_exists($dbfile)) {
				$dat = require $dbfile;
				if(empty($dat) || !is_array($dat)) {
					die('<script type="text/javascript">alert("安装包不正确, 数据安装脚本缺失.");history.back();</script>');
				}
				foreach($dat['schemas'] as $schema) {
					$sql = local_create_sql($schema);
					local_run($sql);
				}
				foreach($dat['datas'] as $data) {
					local_run($data);
				}
			} else {
				die('<script type="text/javascript">alert("你正在使用本地安装, 但未下载完整安装包, 请从维吧社区官网下载完整安装包后重试.");history.back();</script>');
			}

			$salt = local_salt(8);
			$password = sha1("{$user['password']}-{$salt}-{$authkey}");
			$link->query("INSERT INTO {$db['prefix']}users (username, password, salt, joindate) VALUES('{$user['username']}', '{$password}', '{$salt}', '" . time() . "')");
			local_mkdirs(IA_ROOT . '/data');
			file_put_contents(IA_ROOT . '/data/config.php', $config);
			touch(IA_ROOT . '/data/install.lock');
			setcookie('action', 'finish');
			header('location: ?refresh');
			exit();
		}
	}
	tpl_install_db($error);

}
if($action == 'finish') {
	setcookie('action', '', -10);
	$dbfile = IA_ROOT . '/data/db.php';
	//@unlink($dbfile);
	define('IN_SYS', true);
	require IA_ROOT . '/framework/bootstrap.inc.php';
	require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
	$_W['uid'] = $_W['isfounder'] = 1;
	load()->web('common');
	load()->web('template');
	load()->model('setting');
	load()->model('cache');

	cache_build_frame_menu();
	cache_build_setting();
	cache_build_users_struct();
	cache_build_module_subscribe_type();
	tpl_install_finish();
}

function local_writeable($dir) {
	$writeable = 0;
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = fopen("$dir/test.txt", 'w')) {
			fclose($fp);
			unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function local_salt($length = 8) {
	$result = '';
	while(strlen($result) < $length) {
		$result .= sha1(uniqid('', true));
	}
	return substr($result, 0, $length);
}

function local_config() {
	$cfg = <<<EOF
<?php
defined('IN_IA') or exit('Access Denied');

\$config = array();

\$config['db']['master']['host'] = '{db-server}';
\$config['db']['master']['username'] = '{db-username}';
\$config['db']['master']['password'] = '{db-password}';
\$config['db']['master']['port'] = '{db-port}';
\$config['db']['master']['database'] = '{db-name}';
\$config['db']['master']['charset'] = 'utf8';
\$config['db']['master']['pconnect'] = 0;
\$config['db']['master']['tablepre'] = '{db-tablepre}';

\$config['db']['slave_status'] = false;
\$config['db']['slave']['1']['host'] = '';
\$config['db']['slave']['1']['username'] = '';
\$config['db']['slave']['1']['password'] = '';
\$config['db']['slave']['1']['port'] = '3307';
\$config['db']['slave']['1']['database'] = '';
\$config['db']['slave']['1']['charset'] = 'utf8';
\$config['db']['slave']['1']['pconnect'] = 0;
\$config['db']['slave']['1']['tablepre'] = 'ims_';
\$config['db']['slave']['1']['weight'] = 0;

\$config['db']['common']['slave_except_table'] = array('core_sessions');

// --------------------------  CONFIG COOKIE  --------------------------- //
\$config['cookie']['pre'] = '{cookiepre}';
\$config['cookie']['domain'] = '';
\$config['cookie']['path'] = '/';

// --------------------------  CONFIG SETTING  --------------------------- //
\$config['setting']['charset'] = 'utf-8';
\$config['setting']['cache'] = 'mysql';
\$config['setting']['timezone'] = 'Asia/Shanghai';
\$config['setting']['memory_limit'] = '256M';
\$config['setting']['filemode'] = 0644;
\$config['setting']['authkey'] = '{authkey}';
\$config['setting']['founder'] = '1';
\$config['setting']['development'] = 0;
\$config['setting']['referrer'] = 0;
\$config['setting']['https'] = 0;

// --------------------------  CONFIG UPLOAD  --------------------------- //
\$config['upload']['image']['extentions'] = array('gif', 'jpg', 'jpeg', 'png');
\$config['upload']['image']['limit'] = 5000;
\$config['upload']['attachdir'] = '{attachdir}';
\$config['upload']['audio']['extentions'] = array('mp3');
\$config['upload']['audio']['limit'] = 5000;

// --------------------------  CONFIG MEMCACHE  --------------------------- //
\$config['setting']['memcache']['server'] = '';
\$config['setting']['memcache']['port'] = 11211;
\$config['setting']['memcache']['pconnect'] = 1;
\$config['setting']['memcache']['timeout'] = 30;
\$config['setting']['memcache']['session'] = 1;

// --------------------------  CONFIG PROXY  --------------------------- //
\$config['setting']['proxy']['host'] = '';
\$config['setting']['proxy']['auth'] = '';
EOF;
	return trim($cfg);
}

function local_mkdirs($path) {
	if(!is_dir($path)) {
		local_mkdirs(dirname($path));
		mkdir($path);
	}
	return is_dir($path);
}

function local_run($sql) {
	global $link, $db;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(' ims_', ' '.$db['prefix'], $sql));
	$sql = str_replace("\r", "\n", str_replace(' `ims_', ' `'.$db['prefix'], $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			if(!$link->query($query)) {
				$errorinfo = $link->errorInfo();
				echo $errorinfo[2] . ": " . $link->errorCode() . "<br />";
				exit($query);
			}
		}
	}
}

function local_create_sql($schema) {
	$pieces = explode('_', $schema['charset']);
	$charset = $pieces[0];
	$engine = $schema['engine'];
	$sql = "CREATE TABLE IF NOT EXISTS `{$schema['tablename']}` (\n";
	foreach ($schema['fields'] as $value) {
		if(!empty($value['length'])) {
			$length = "({$value['length']})";
		} else {
			$length = '';
		}

		$signed  = empty($value['signed']) ? ' unsigned' : '';
		if(empty($value['null'])) {
			$null = ' NOT NULL';
		} else {
			$null = '';
		}
		if(isset($value['default'])) {
			$default = " DEFAULT '" . $value['default'] . "'";
		} else {
			$default = '';
		}
		if($value['increment']) {
			$increment = ' AUTO_INCREMENT';
		} else {
			$increment = '';
		}

		$sql .= "`{$value['name']}` {$value['type']}{$length}{$signed}{$null}{$default}{$increment},\n";
	}
	foreach ($schema['indexes'] as $value) {
		$fields = implode('`,`', $value['fields']);
		if($value['type'] == 'index') {
			$sql .= "KEY `{$value['name']}` (`{$fields}`),\n";
		}
		if($value['type'] == 'unique') {
			$sql .= "UNIQUE KEY `{$value['name']}` (`{$fields}`),\n";
		}
		if($value['type'] == 'primary') {
			$sql .= "PRIMARY KEY (`{$fields}`),\n";
		}
	}
	$sql = rtrim($sql);
	$sql = rtrim($sql, ',');

	$sql .= "\n) ENGINE=$engine DEFAULT CHARSET=$charset;\n\n";
	return $sql;
}

/* function __remote_install_headers($ch = '', $header = '') {
	static $hash;
	if(!empty($header)) {
		$pieces = explode(':', $header);
		if(trim($pieces[0]) == 'hash') {
			$hash = trim($pieces[1]);
		}
	}
	if($ch == '' && $header == '') {
		return $hash;
	}
	return strlen($header);
} */

/* function remote_install() {
	global $family;
	$token = '';
	$pars = array();
	$pars['host'] = $_SERVER['HTTP_HOST'];
	$pars['version'] = '1.0';
	$pars['release'] = '';
	$pars['type'] = 'install';
	$pars['product'] = '';
	$url = 'http://www.we8.club/gateway.php';
	$urlset = parse_url($url);
	$cloudip = gethostbyname($urlset['host']);
	$headers[] = "Host: {$urlset['host']}";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $cloudip . $urlset['path']);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pars);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, '__remote_install_headers');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$content = curl_exec($ch);
	curl_close($ch);
	$sign = __remote_install_headers();
	$ret = array();
	if(empty($content)) {
		return showerror(-1, '获取安装信息失败，可能是由于网络不稳定，请重试。');
	}
	$ret = unserialize($content);
	if($sign != md5($ret['data'] . $token)) {
		return showerror(-1, '发生错误: 数据校验失败，可能是传输过程中网络不稳定导致，请重试。');
	}
	$ret['data'] = unserialize($ret['data']);
	return $ret['data'];
} */

/* function __remote_download_headers($ch = '', $header = '') {
	static $hash;
	if(!empty($header)) {
		$pieces = explode(':', $header);
		if(trim($pieces[0]) == 'hash') {
			$hash = trim($pieces[1]);
		}
	}
	if($ch == '' && $header == '') {
		return $hash;
	}
	return strlen($header);
} */

/* function remote_download($archive) {
	$pars = array();
	$pars['host'] = $_SERVER['HTTP_HOST'];
	$pars['version'] = '';
	$pars['release'] = '';
	$pars['archive'] = base64_encode(json_encode($archive));
	$url = 'http://www.we8.club/gateway.php';
	$urlset = parse_url($url);
	$cloudip = gethostbyname($urlset['host']);
	$headers[] = "Host: {$urlset['host']}";
	$tmpfile = IA_ROOT . '/we8.zip';
	$fp = fopen($tmpfile, 'w+');
	if(!$fp) {
		return false;
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $cloudip . $urlset['path']);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pars);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, '__remote_download_headers');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	if(!curl_exec($ch)) {
		return false;
	}
	curl_close($ch);
	fclose($fp);
	$sign = __remote_download_headers();
	if(md5_file($tmpfile) == $sign) {
		return $tmpfile;
	}
	return false;
}
 */
function tpl_frame() {
	global $action, $actions;
	$action = $_COOKIE['action'];
	$step = array_search($action, $actions);
	$steps = array();
	for($i = 0; $i <= $step; $i++) {
		if($i == $step) {
			$steps[$i] = ' list-group-item-info';
		} else {
			$steps[$i] = ' list-group-item-success';
		}
	}
	$progress = $step * 25 + 25;
	$content = ob_get_contents();
	ob_clean();
	$tpl = <<<EOF
<!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>安装系统 - 维吧活动运维系统</title>
		<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<style>
			html,body{font-size:13px;font-family:"Microsoft YaHei UI", "微软雅黑", "宋体";}
			.pager li.previous a{margin-right:10px;}
			.header a{color:#FFF;}
			.header a:hover{color:#428bca;}
			.footer{padding:10px;}
			.footer a,.footer{color:#eee;font-size:14px;line-height:25px;}
		</style>
		<!--[if lt IE 9]>
		  <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body style="background-color:#28b0e4;">
		<div class="container">
			<div class="header" style="margin:15px auto;">
				<ul class="nav nav-pills pull-right" role="tablist">
					<li role="presentation" class="active"><a style="background-color:#E81010;" href="javascript:;">安装维吧系统</a></li>
					<!--<li role="presentation"><a href="http://www.we8.club">维吧官网</a></li>-->
					<li role="presentation"><a href="http://www.we8.club">访问维吧社区</a></li>
				</ul>
				<img src="?res=logo" />
			</div>
			<div class="row well" style="margin:auto 0;">
				<div class="col-xs-3">
					<div class="progress" title="安装进度">
						<div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100" style="width: {$progress}%;">
							{$progress}%
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							安装步骤
						</div>
						<ul class="list-group">
							<a href="javascript:;" class="list-group-item{$steps[0]}"><span class="glyphicon glyphicon-copyright-mark"></span> &nbsp; 许可协议</a>
							<a href="javascript:;" class="list-group-item{$steps[1]}"><span class="glyphicon glyphicon-eye-open"></span> &nbsp; 环境监测</a>
							<a href="javascript:;" class="list-group-item{$steps[2]}"><span class="glyphicon glyphicon-cog"></span> &nbsp; 参数配置</a>
							<a href="javascript:;" class="list-group-item{$steps[3]}"><span class="glyphicon glyphicon-ok"></span> &nbsp; 成功</a>
						</ul>
					</div>
				</div>
				<div class="col-xs-9">
					{$content}
				</div>
			</div>
			<div class="footer" style="margin:15px auto;">
				<div class="text-center">
					<a href="http://www.we8.club">维吧社区</a> &nbsp; &nbsp; <a href="http://www.we8.club/forum-63-1.html">帮助文档</a> &nbsp; &nbsp; <a href="http://www.we8.club/forum.php">访问社区</a>
				</div>
				<div class="text-center">
					Powered by <a href="http://www.we8.club"><b>维吧社区</b></a> v1.0 &copy; 2017 <a href="http://www.we8.club">www.we8.club</a>
				</div>
			</div>
		</div>
		<script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
		<script src="http://cdn.bootcss.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	</body>
</html>
EOF;
	echo trim($tpl);
}

function tpl_install_license() {
	echo <<<EOF
		<div class="panel panel-default">
			<div class="panel-heading">阅读许可协议</div>
			<div class="panel-body" style="overflow-y:scroll;max-height:400px;line-height:20px;">
				<h3>版权所有 (c)2014-2018，微信CMS团队保留部分权利。 </h3>
				<p>
					感谢您选择微信CMS - 微信公众平台插件开源免费框架（以下简称维吧，维吧基于 PHP + MySQL的技术开发） <br />
					为了使你正确并合法的使用本软件，请你在使用前务必阅读清楚下面的协议条款：
				</p>
				<p>
					<strong>一、本授权协议适用且仅适用于微信CMS系统(维吧. 以下简称微信CMS)任何版本，微信CMS官方对本授权协议的最终解释权。</strong>
				</p>
				<p>
					<strong>二、协议许可的权利 </strong>
					<ol>
						<li>您可以在协议规定的约束和限制范围内修改微信CMS源代码或界面风格以适应您的网站要求。</li>
						<li>您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。</li>
					</ol>
				</p>
				<p>
					<strong>三、协议规定的约束和限制 </strong>
					<ol>
						<li>未经官方许可，不得对本软件或与之关联的代码进行出租、出售、抵押或发放子许可证。</li>
						<li>未经官方许可，禁止在微信的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</li>
						<li>如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。</li>
					</ol>
				</p>
				<p>
					<strong>四、有限担保和免责声明 </strong>
					<ol>
						<li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。</li>
						<li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺对免费用户提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。</li>
						<li>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始确认本协议并安装  维吧，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</li>
						<li>本软件源自网上第三方免费源码-微擎，并且这些文件是没经过授权发布的，请参考微擎软件的使用许可合法的使用。</li>
					</ol>
				</p>
			</div>
		</div>
		<form class="form-inline" role="form" method="post">
			<ul class="pager">
				<li class="pull-left" style="display:block;padding:5px 10px 5px 0;">
					<div class="checkbox">
						<label>
							<input type="checkbox"> 我已经阅读并同意此协议
						</label>
					</div>
				</li>
				<li class="previous"><a href="javascript:;" onclick="if(jQuery(':checkbox:checked').length == 1){jQuery('form')[0].submit();}else{alert('您必须同意软件许可协议才能安装！')};">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>
			</ul>
		</form>
EOF;
	tpl_frame();
}

function tpl_install_env($ret = array()) {
	if(empty($ret['continue'])) {
		$continue = '<li class="previous disabled"><a href="javascript:;">请先解决环境问题后继续</a></li>';
	} else {
		$continue = '<li class="previous"><a href="javascript:;" onclick="$(\'#do\').val(\'continue\');$(\'form\')[0].submit();">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>';
	}
	echo <<<EOF
		<div class="panel panel-default">
			<div class="panel-heading">服务器信息</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">参数</th>
					<th>值</th>
					<th></th>
				</tr>
				<tr class="{$ret['server']['os']['class']}">
					<td>服务器操作系统</td>
					<td>{$ret['server']['os']['value']}</td>
					<td>{$ret['server']['os']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['sapi']['class']}">
					<td>Web服务器环境</td>
					<td>{$ret['server']['sapi']['value']}</td>
					<td>{$ret['server']['sapi']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['php']['class']}">
					<td>PHP版本</td>
					<td>{$ret['server']['php']['value']}</td>
					<td>{$ret['server']['php']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['dir']['class']}">
					<td>程序安装目录</td>
					<td>{$ret['server']['dir']['value']}</td>
					<td>{$ret['server']['dir']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['disk']['class']}">
					<td>磁盘空间</td>
					<td>{$ret['server']['disk']['value']}</td>
					<td>{$ret['server']['disk']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['upload']['class']}">
					<td>上传限制</td>
					<td>{$ret['server']['upload']['value']}</td>
					<td>{$ret['server']['upload']['remark']}</td>
				</tr>
			</table>
		</div>

		<div class="alert alert-info">PHP环境要求必须满足下列所有条件，否则系统或系统部份功能将无法使用。</div>
		<div class="panel panel-default">
			<div class="panel-heading">PHP环境要求</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">选项</th>
					<th style="width:180px;">要求</th>
					<th style="width:50px;">状态</th>
					<th>说明及帮助</th>
				</tr>
				<tr class="{$ret['php']['version']['class']}">
					<td>PHP版本</td>
					<td>5.3或者5.3以上</td>
					<td>{$ret['php']['version']['value']}</td>
					<td>{$ret['php']['version']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['pdo']['class']}">
					<td>PDO_MYSQL</td>
					<td>支持</td>
					<td>{$ret['php']['pdo']['value']}</td>
					<td>{$ret['php']['pdo']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['curl']['class']}">
					<td>cURL</td>
					<td>支持</td>
					<td>{$ret['php']['curl']['value']}</td>
					<td>{$ret['php']['curl']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['ssl']['class']}">
					<td>openSSL</td>
					<td>支持</td>
					<td>{$ret['php']['ssl']['value']}</td>
					<td>{$ret['php']['ssl']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['gd']['class']}">
					<td>GD2</td>
					<td>支持</td>
					<td>{$ret['php']['gd']['value']}</td>
					<td>{$ret['php']['gd']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['dom']['class']}">
					<td>DOM</td>
					<td>支持</td>
					<td>{$ret['php']['dom']['value']}</td>
					<td>{$ret['php']['dom']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['session']['class']}">
					<td>session.auto_start</td>
					<td>关闭</td>
					<td>{$ret['php']['session']['value']}</td>
					<td>{$ret['php']['session']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['asp_tags']['class']}">
					<td>asp_tags</td>
					<td>关闭</td>
					<td>{$ret['php']['asp_tags']['value']}</td>
					<td>{$ret['php']['asp_tags']['remark']}</td>
				</tr>
			</table>
		</div>

		<div class="alert alert-info">系统要求维吧整个安装目录必须可写, 才能使用维吧所有功能。</div>
		<div class="panel panel-default">
			<div class="panel-heading">目录权限监测</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">目录</th>
					<th style="width:180px;">要求</th>
					<th style="width:50px;">状态</th>
					<th>说明及帮助</th>
				</tr>
				<tr class="{$ret['write']['root']['class']}">
					<td>/</td>
					<td>整目录可写</td>
					<td>{$ret['write']['root']['value']}</td>
					<td>{$ret['write']['root']['remark']}</td>
				</tr>
				<tr class="{$ret['write']['data']['class']}">
					<td>/</td>
					<td>data目录可写</td>
					<td>{$ret['write']['data']['value']}</td>
					<td>{$ret['write']['data']['remark']}</td>
				</tr>
			</table>
		</div>
		<form class="form-inline" role="form" method="post">
			<input type="hidden" name="do" id="do" />
			<ul class="pager">
				<li class="previous"><a href="javascript:;" onclick="$('#do').val('back');$('form')[0].submit();"><span class="glyphicon glyphicon-chevron-left"></span> 返回</a></li>
				{$continue}
			</ul>
		</form>
EOF;
	tpl_frame();
}

function tpl_install_db($error = '') {
	if(!empty($error)) {
		$message = '<div class="alert alert-danger">发生错误: ' . $error . '</div>';
	}
	$insTypes = array();
	if(file_exists(IA_ROOT . '/index.php') && is_dir(IA_ROOT . '/app') && is_dir(IA_ROOT . '/web')) {
		$insTypes['local'] = ' checked="checked"';
	} else {
		$insTypes['remote'] = ' checked="checked"';
	}
	if (!empty($_POST['type'])) {
		$insTypes = array();
		$insTypes[$_POST['type']] = ' checked="checked"';
	}
	$disabled = empty($insTypes['local']) ? ' disabled="disabled"' : '';
	echo <<<EOF
	{$message}
	<form class="form-horizontal" method="post" role="form">
		<div class="panel panel-default">
			<div class="panel-heading">安装选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">安装方式</label>
					<div class="col-sm-10">
						<!--<label class="radio-inline">
							<input type="radio" name="type" value="remote"{$insTypes['remote']}> 在线安装
						</label>-->
						<label class="radio-inline">
							<input type="radio" name="type" value="local"{$insTypes['local']}{$disabled}> 离线安装
						</label>
						<div class="help-block">
							<!--<span style="color:red">由于在线安装是精简版，安装后，请务必注册云服务更新到完整版</span> <br/>-->
							<span style="color:red">安装完成后，请务必注册云服务更新到完整版</span> <br/>
							在线安装能够直接安装最新版本维吧系统, 如果在线安装困难, 请下载离线安装包后使用本地安装. <br/>
							离线安装包可能不是最新程序, 如果你不确定, 可以现在访问官网重新下载一份最新的.
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">数据库选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库主机</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[server]" value="127.0.0.1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库用户</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[username]" value="root">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[password]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">表前缀</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[prefix]" value="ims_">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库名称</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[name]" value="we8">
					</div>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">管理选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员账号</label>
					<div class="col-sm-4">
						<input class="form-control" type="username" name="user[username]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="password" name="user[password]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">确认密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="password"">
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="do" id="do" />
		<ul class="pager">
			<li class="previous"><a href="javascript:;" onclick="$('#do').val('back');$('form')[0].submit();"><span class="glyphicon glyphicon-chevron-left"></span> 返回</a></li>
			<li class="previous"><a href="javascript:;" onclick="if(check(this)){jQuery('#do').val('continue');if($('input[name=type]').val() == 'remote'){alert('在线线安装时，安装程序会下载精简版快速完成安装，完成后请务必注册云服务更新到完整版。')}$('form')[0].submit();}">继续 <span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</form>
	<script>
		var lock = false;
		function check(obj) {
			if(lock) {
				return;
			}
			$('.form-control').parent().parent().removeClass('has-error');
			var error = false;
			$('.form-control').each(function(){
				if($(this).val() == '') {
					$(this).parent().parent().addClass('has-error');
					this.focus();
					error = true;
				}
			});
			if(error) {
				alert('请检查未填项');
				return false;
			}
			if($(':password').eq(0).val() != $(':password').eq(1).val()) {
				$(':password').parent().parent().addClass('has-error');
				alert('确认密码不正确.');
				return false;
			}
			lock = true;
			$(obj).parent().addClass('disabled');
			$(obj).html('正在执行安装');
			return true;
		}
	</script>
EOF;
	tpl_frame();
}

function tpl_install_finish() {
	//$modules = get_store_module();
	//$themes = get_store_theme();
	$url = APP_STORE_API;
	$url2 = APP_STORE_URL;
	echo <<<EOF
	<div class="page-header"><h3>安装完成</h3></div>
	<div class="alert alert-success">
		恭喜您!已成功安装“维吧 - 活动运维”系统，您现在可以: <a target="_blank" class="btn btn-success" href="./web/index.php">访问网站首页</a>
	</div>
	<div class="form-group">
		<h5><strong>维吧社区应用商城</strong></h5>
		<span class="help-block">应用商城特意为您推荐了一批优秀模块、主题，赶紧来安装几个吧！</span>
		<table class="table table-bordered">
			<tbody>
				<script type="text/javascript" src="{$url}"></script>
				</iframe>
			</tbody>
		</table>
	</div>

	<div class="alert alert-warning">
		我们强烈建议您立即注册云服务，享受“在线更新”等云服务。
		<a target="_blank" class="btn btn-success" href="./web/index.php?c=cloud&a=profile">马上去注册</a>
		<a target="_blank" class="btn btn-success" href="{$url2}" target="_blank">访问应用商城首页</a>
	</div>
EOF;
	tpl_frame();
}

function tpl_resources() {
	static $res = array(
		'logo' => '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',
	);
	return $res;
}

function showerror($errno, $message = '') {
	return array(
		'errno' => $errno,
		'error' => $message,
	);
}

/* function get_store_module() {
	load()->func('communication');
	$response = ihttp_request(APP_STORE_API, array('controller' => 'store', 'action' => 'api', 'do' => 'module'));
	$response = json_decode($response['content'], true);

	$modules = '';
	foreach ($response['message'] as $key => $module) {
		if ($key % 3 < 1) {
			$modules .= '</tr><tr>';
		}
		$module['detail_link'] = APP_STORE_URL . trim($module['detail_link'], '.');
		$modules .= '<td>';
		$modules .= '<div class="col-sm-4">';
		$modules .= '<a href="' . $module['detail_link'] . '" title="查看详情" target="_blank">';
		$modules .= '<img src="' . $module['logo']. '"' . ' width="50" height="50" ' . $module['title'] . '" /></a>';
		$modules .= '</div>';
		$modules .= '<div class="col-sm-8">';
		$modules .= '<p><a href="' . $module['detail_link'] .'" title="查看详情" target="_blank">' . $module['title'] . '</a></p>';
		$modules .= '<p>安装量：<span class="text-danger">' . $module['purchases'] . '</span></p>';
		$modules .= '</div>';
		$modules .= '</td>';
	}
	$modules = substr($modules, 5) . '</tr>';

	return $modules;
} */

/* function get_store_theme() {
	load()->func('communication');
	$response = ihttp_request(APP_STORE_API, array('controller' => 'store', 'action' => 'api', 'do' => 'theme'));
	$response = json_decode($response['content'], true);

	$themes = '<tr><td colspan="' . count($response['message']) . '">';
	$themes .= '<div class="form-group">';
	foreach ($response['message'] as $key => $theme) {
		$theme['detail_link'] = APP_STORE_URL . trim($theme['detail_link'], '.');
		$themes .= '<div class="col-sm-2" style="padding-left: 7px;margin-right: 25px;">';
		$themes .= '<a href="' . $theme['detail_link'] .'" title="查看详情" target="_blank" /><img src="' . $theme['logo']. '" /></a>';
		$themes .= '<p></p><p class="text-right">';
		$themes .= '<a href="' . $theme['detail_link']. '" title="查看详情" target="_blank">'  . $theme['title'] . '</a></p>';
		$themes .= '</div>';
	}
	$themes .= '</div>';

	return $themes;
} */
