<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
require IA_ROOT . '/addons/tyzm_diamondvote/defines.php';
global $_W, $_GPC;
define('TG_PATH', IA_ROOT.'/addons/'.$_GPC['m'].'/');
require TG_PATH.'version.php';
load ()->func ( 'tpl' );
load()->func('file');
load()->func('communication');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'download'){
	$domain = trim( preg_replace( "/http(s)?:\/\//", "", rtrim($_W['siteroot'],"/") )  );
	$ip = getUserIP();
	$code = getVerifyCode();	
	$tmpdir  = IA_ROOT.'/addons/'.$_GPC['m'].'/temp';
    $f       = file_get_contents($tmpdir.'/file.txt');
    $upgrade = json_decode($f, true);
    $files   = $upgrade['files'];
    $path = "";
    foreach ($files as $f) {
      if (empty($f['download'])) {
        $path = $f['path'];
        break;
      }
    }
	$resp = ihttp_post(HOSTAPI,array('type' => 'download','ip' => $ip,'domain' => $domain,'code' => $code,'module' => $_GPC['m'],'path' => $path));
	$ret = @json_decode($resp['content'], true);
	if (!empty($ret['message'])){
		message($ret['message'], $this -> createWebUrl('auth'), 'error');
		}else{
			global $_GPC;
			$tmpdir  = IA_ROOT.'/addons/'.$_GPC['m'].'/temp';
			$f       = file_get_contents($tmpdir.'/file.txt');
			$upgrade = json_decode($f, true);
			$files   = $upgrade['files'];
			$path = "";
			foreach ($files as $f) {
				if (empty($f['download'])) {
					$path = $f['path'];
					break;
					}
				}
			if ( ! empty($path)) { 
				if (is_array($ret)) {
					$path    = $ret['path'];
					$dirpath = dirname($path);
					if ( ! is_dir(IA_ROOT.'/addons/'.$_GPC['m'].'/'.$dirpath)) {
						mkdirs(IA_ROOT.'/addons/'.$_GPC['m'].'/'.$dirpath, '0777');
					}
					$content = base64_decode($ret['content']);
					file_put_contents(IA_ROOT.'/addons/'.$_GPC['m'].'/'.$path, $content);
					$success = 1;
					foreach ($files as & $f) {
						if ($f['path'] == $path) {
							$f['download'] = 1;
							break;
						}
						if ($f['download']) {
							$success++;
						}
					}
					unset($f);
					$upgrade['files'] = $files;
					$tmpdir           = IA_ROOT.'/addons/'.$_GPC['m'].'/temp';
					if ( ! is_dir($tmpdir)) {
						mkdirs($tmpdir);
					}
					file_put_contents($tmpdir.'/file.txt', json_encode($upgrade));
					die(json_encode(array('result'  => 1,'total' => count($files),'success' => $success,'path' => $path,)));
				}

			}else{
				$updatefile = IA_ROOT.'/addons/'.$_GPC['m'].'/upgrade.php';
				if (file_exists($updatefile)) {
					require $updatefile;
				}
				$tmpdir = IA_ROOT.'/addons/'.$_GPC['m'].'/temp';
				@rmdirs($tmpdir);
				message('恭喜您，系统更新成功！', $this -> createWebUrl('upgrade'), 'success');
				exit();			
			}
		}
		}else if ($operation == 'display'){
			$status = hasVerify();
			$version = TG_VERSION;
			$versionfile = TG_PATH.'version.php';
			$release = date('YmdHis', filemtime($versionfile));
			$domain = trim( preg_replace( "/http(s)?:\/\//", "", rtrim($_W['siteroot'],"/") )  );
			$ip = getUserIP();
			$code = getVerifyCode();
			$resp = ihttp_post(HOSTAPI,array('type' => 'check','module' => $_GPC['m'],'ip' => $ip,'domain' => $domain,'code' => $code,'version' => $version));
			$upgrade = json_decode($resp['content'], true);
			if ($upgrade['result'] != 1){
				message($upgrade['message'], $this -> createWebUrl('auth'),'refresh');
			}
		}
include $this -> template('process');
function hasVerify(){
	global $_GPC;
	$result = ihttp_request(HOSTAPI,array('type' => 'checkauth','module' => $_GPC['m'],'website' => 201900,'domain' => getDomain(),'code' => getVerifyCode()),null,5);
	$result = @json_decode($result['content'], true);
	if ($result['status'] === '1') {
		return getVerifyCode();
	}
		return false;
}
function getUserIP(){
	global $_GPC;
	$resp = ihttp_request(HOSTAPI,array('type'    => 'user','module'  => $_GPC['m'],'website' => 201900,'domain'  => getDomain()),null,1);
	$resp = @json_decode($resp['content'], true);
	$ip   = $resp['ip'];
	return $ip;
}
function getDomain(){
        return str_replace('www.', '', strtolower($_SERVER['SERVER_NAME']));
}
function getVerifyCode(){
	$path = __DIR__.'/#diamondvote#.cer';
	if (file_exists($path)) {
		return trim(file_get_contents($path));
	}
}
function setVerifyCode($code){
	$path = __DIR__.'/#diamondvote#.cer';
	file_put_contents($path, trim($code));
}