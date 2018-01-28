<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
load()->func('communication');
load()->model('cloud');
load()->func('file');
load()->func('up');

global $_W,$_GPC;

$step = $_GPC['step'];
$steps = array('files','filespro', 'schemas', 'scripts');
$step = in_array($step, $steps) ? $step : 'files';

	$hosturl = urlencode('http://'.$_SERVER['HTTP_HOST']);
	$updatehost = 'http://we7.rocrm.cn/update.php';
	$pathl = IA_ROOT;
    $updatedir = IA_ROOT.'/data/update';
	$backdir = IA_ROOT.'/data/patch';
	$pathlist = to_md5($pathl);


if ($step == 'files' && $_GPC['m'] == 'prepare') {
  
  	$pa = array();
  	$returns = SendCurl($updatehost.'?a=display&u='.$hosturl,$pathlist);
	$lastver = $returns['C']['name'];
  	$link = SendCurl($updatehost.'?a=down&u='.$hosturl,$pa);
  	
    if(!is_dir($updatedir)) {
      mkdirs($updatedir);
    }
  
	$ret = down_f($link, $updatedir,$lastver,$updatehost,$hosturl);
    if($ret == 22222){
		itoast('更新失败，请返回重新尝试！',referer(),'error');
      	die;
    }else{
      
      	$back = date("Ymdhis");
      	$back = $backdir.'/'.$back;
      	if(!mkdirs($back)) {
          itoast('创建回滚目录失败，请返回重新尝试！',create_url('cloud/upgrade',array()),'error');
      	  die;
        }
    	header("Location: ".create_url('cloud/process',array("step"=>"filespro")) );
      	exit;
    }
}

if (!empty($_GPC['m'])) {
	$m = $_GPC['m'];
} elseif (!empty($_GPC['t'])) {

} elseif (!empty($_GPC['w'])) {

} else {
	$m = '';
  	$returns = SendCurl($updatehost.'?a=display&u='.$hosturl,$pathlist);
  	$lastver = $returns['C']['name'];
	$packet = $returns;
}

if ($step == 'filespro') {
  
  $newver = "<?php return array ('ver' => '$lastver');?>";
  $ver = fopen(IA_ROOT.'/framework/version.php','w+');
  fwrite($ver,$newver);
  fclose($ver);
}

template('cloud/process');