<?php
defined('IN_IA') or exit('Access Denied');
if (in_array($action, array('upgrade', 'profile', 'diagnose', 'sms'))) {
	define('FRAME', 'site');
} else {
	define('FRAME', 'system');
}

if(in_array($action, array('profile', 'device', 'callback', 'appstore', 'sms'))) {
	$do = $action;
	$action = 'redirect';
}
if($action == 'touch') {
	exit('success');
}