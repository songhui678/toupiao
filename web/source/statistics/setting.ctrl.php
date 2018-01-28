<?php
/**
 * [WECHAT 2018]
 * [WECHAT  a free software]
 */
defined('IN_IA') or exit('Access Denied');

load()->model('statistics');

$dos = array('display', 'edit_highest_visit');
$do = in_array($do, $dos) ? $do : 'display';

$statistics_setting = (array)uni_setting_load(array('statistics'), $_W['uniacid']);
$statistics_setting = $statistics_setting['statistics'];
if ($do == 'display') {
	$highest_visit = empty($statistics_setting['owner']) ? 0 : $statistics_setting['owner'];
}
if ($do == 'edit_highest_visit') {
	$new_highest_visit = intval($_GPC['highest_visit']);
	if (!empty($statistics_setting)) {
		$highest_visit = $statistics_setting;
		$highest_visit['owner'] = $new_highest_visit;
	} else {
		$highest_visit = array('owner' => $new_highest_visit);
	}
	$result = pdo_update('uni_settings', array('statistics' => iserializer($highest_visit)), array('uniacid' => $_W['uniacid']));
	if (!empty($result)) {
		cache_delete("unisetting:{$_W['uniacid']}");
		iajax(0, '修改成功！');
	} else {
		iajax(-1, '修改失败！');
	}
}
template('statistics/setting');