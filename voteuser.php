<?php
/**
 * 钻石投票-投票
 *
 * @author 微实惠科技
 * @url https://spf360.taobao.com
 */

define('IN_API', true);
require_once './framework/bootstrap.inc.php';
$time = time();
$voteuserList = pdo_fetchall("SELECT * FROM " . tablename("tyzm_diamondvote_voteuser") . " WHERE rid=13 AND status=0 ");
if (!empty($voteuserList)) {
	foreach ($voteuserList as $voteuser) {
		$diffTime = $time - $voteuser['createtime'];
		if ($diffTime >= 300) {
			$re = pdo_delete(tablename("tyzm_diamondvote_voteuser"), array('id' => $voteuser['id']));
		}
	}
}
exit(json_encode(array('status' => '1', 'msg' => "成功")));