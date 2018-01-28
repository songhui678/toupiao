<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('oauth', 'save_oauth', 'uc_setting', 'upload_file');
$do = in_array($do, $dos) ? $do : 'oauth';
uni_user_permission_check('profile_setting');
$_W['page']['title'] = '公众平台oAuth选项 - 会员中心';

load()->model('user');

if ($do == 'save_oauth') {
	$type = $_GPC['type'];
	$account = trim($_GPC['account']);
	if ($type == 'oauth') {
		$host = safe_gpc_url(rtrim($_GPC['host'],'/'), false);

		if (!empty($_GPC['host']) && empty($host)) {
			iajax(-1, '域名不合法');
		}
		$data = array(
			'host' => $host,
			'account' => $account,
		);
		pdo_update('uni_settings', array('oauth' => iserializer($data)), array('uniacid' => $_W['uniacid']));
		cache_delete("unisetting:{$_W['uniacid']}");
	}
	if ($type == 'jsoauth') {
		pdo_update('uni_settings', array('jsauth_acid' => $account), array('uniacid' => $_W['uniacid']));
		cache_delete("unisetting:{$_W['uniacid']}");
	}
	iajax(0, '');
}

if ($do == 'oauth') {
<<<<<<< HEAD
	$where = '';
	$params = array();
	if(empty($_W['isfounder'])) {
		$where = " WHERE `uniacid` IN (SELECT `uniacid` FROM " . tablename('uni_account_users') . " WHERE `uid`=:uid)";
		$params[':uid'] = $_W['uid'];
	}
	$sql = "SELECT * FROM " . tablename('uni_account') . $where;
	$user_have_accounts = pdo_fetchall($sql, $params);
	$oauth_accounts = array();
	$jsoauth_accounts = array();
	if(!empty($user_have_accounts)) {
		foreach($user_have_accounts as $uniaccount) {
			$accountlist = uni_accounts($uniaccount['uniacid']);
			if(!empty($accountlist)) {
				foreach($accountlist as $account) {
					if(!empty($account['key']) && !empty($account['secret'])) {
						if (in_array($account['level'], array(4))) {
							$oauth_accounts[$account['acid']] = $account['name'];
						}
						if (in_array($account['level'], array(3, 4))) {
							$jsoauth_accounts[$account['acid']] = $account['name'];
						}
					}
				}
			}
		}
	}
=======
	$user_have_accounts = user_borrow_oauth_account_list();
	$oauth_accounts = $user_have_accounts['oauth_accounts'];
	$jsoauth_accounts = $user_have_accounts['jsoauth_accounts'];
>>>>>>> parent of 775f72a... 654
	$oauth = pdo_fetchcolumn('SELECT `oauth` FROM '.tablename('uni_settings').' WHERE `uniacid` = :uniacid LIMIT 1',array(':uniacid' => $_W['uniacid']));
	$oauth = iunserializer($oauth) ? iunserializer($oauth) : array();
	$jsoauth = pdo_getcolumn('uni_settings', array('uniacid' => $_W['uniacid']), 'jsauth_acid');
}

template('profile/passport');
