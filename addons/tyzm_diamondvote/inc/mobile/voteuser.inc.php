<?php
/**
 * 钻石投票-投票
 *
 * @author 微实惠科技
 * @url https://spf360.taobao.com
 */

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$time = time();
$voteuserList = pdo_fetch("SELECT * FROM " . tablename($this->tablevoteuser) . " WHERE rid=:rid AND status=:status ", array(
	':rid' => 13,
	':status' => 0,
));
if (!empty($voteuserList)) {
	foreach ($voteuserList as $voteuser) {
		$diffTime = $time - $voteuser['createtime'];
		if ($diffTime >= 300) {
			$re = pdo_delete($this->tablevoteuser, array('id' => $voteuser['id']));
		}
	}
}
exit(json_encode(array('status' => '1', 'msg' => "成功")));