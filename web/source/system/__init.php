<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
<<<<<<< HEAD
if (in_array($action, array('site', 'menu', 'attachment', 'systeminfo', 'logs', 'filecheck', 'optimize', 'database', 'scan', 'bom'))) {
=======
if (in_array($action, array('site', 'menu', 'attachment', 'systeminfo', 'logs', 'filecheck', 'optimize',
	'database', 'scan', 'bom', 'ipwhitelist', 'workorder', 'sensitiveword', 'thirdlogin', 'oauth'))) {
>>>>>>> parent of 775f72a... 654
	define('FRAME', 'site');
} else {
	define('FRAME', 'system');
}
