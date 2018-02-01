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
$voteuserList = pdo_fetchall("SELECT * FROM " . tablename($this->tablevoteuser) . " WHERE rid=13 AND status=0 ");
if (!empty($voteuserList)) {
	foreach ($voteuserList as $voteuser) {
		$diffTime = $time - $voteuser['createtime'];
		if ($diffTime >= 120) {
			$re = pdo_delete($this->tablevoteuser, array('id' => $voteuser['id']));
			file_put_contents('/home/www/toupiao/voteuserList.txt', "voteuser-------" . json_encode($voteuser) . "\n", FILE_APPEND);
		}
	}
}
exit(json_encode(array('status' => '1', 'msg' => "成功")));