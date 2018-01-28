<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
require_once './../../../framework/bootstrap.inc.php';

load()->func('communication');
load()->model('cloud');
load()->func('file');
load()->func('up');

	$path = $_GET['path'];

	$pathl = IA_ROOT;
    $updatedir = IA_ROOT.'/data/update';
	$backdir = IA_ROOT.'/data/patch';

if($_GET['type'] == 'file'){
  
  	$filterl = file_back($pathl,$updatedir, $backdir, $path);

	echo $filterl;
}

if($_GET['type'] == 'del'){
  
  	deldir($updatedir);
}