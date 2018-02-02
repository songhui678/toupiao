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
$voteuserList = pdo_fetchall("SELECT * FROM " . tablename("tyzm_diamondvote_voteuser") . " WHERE status=0 ");
file_put_contents('/home/www/toupiao/voteuserList.txt', "voteuser-------" . json_encode($voteuserList) . "\n", FILE_APPEND);
if (!empty($voteuserList)) {
	foreach ($voteuserList as $voteuser) {
		$diffTime = $time - $voteuser['createtime'];
		if ($diffTime >= 1000) {
			pdo_delete('tyzm_diamondvote_voteuser', array('id' => $voteuser['id']));
			file_put_contents('/home/www/toupiao/voteuser.txt', "voteuser-------" . json_encode($voteuser) . "\n", FILE_APPEND);
		}
	}
}
exit(json_encode(array('status' => '1', 'msg' => "成功")));