<?php
/**
 * 钻石投票-投票
 *
 * @author 微实惠科技
 * @url https://spf360.taobao.com
 */

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
is_weixin();
load()->func('communication');
$type = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
$rid = intval($_GPC['rid']);
$userinfo = $this->oauthuser;
$oauth_openid = $userinfo['oauth_openid'];
$openid = $userinfo['openid'];
m('domain')->randdomain($rid);
$reply = pdo_fetch("SELECT rid,title,sharedesc,description,config,style,area,addata,applydata,endtime,apstarttime,apendtime,status FROM " . tablename($this->tablereply) . " WHERE rid = :rid ", array(':rid' => $rid));
$reply['style'] = @unserialize($reply['style']);
if (empty($reply['status'])) {message("活动已禁用");}
$reply = @array_merge($reply, @unserialize($reply['config']));unset($reply['config']);
if ($reply['apstarttime'] > time()) {
	$aptime = 1; //未开始报名
} elseif ($reply['apendtime'] < time()) {
	$aptime = 2; //报名已结束
}

$voteuser = pdo_fetch("SELECT * FROM " . tablename($this->tablevoteuser) . " WHERE rid = :rid AND  openid = :openid ", array(':rid' => $rid, ':openid' => $openid));
if ($voteuser['status'] != 1) {
	{message("请先去报名");}
}
$id = $voteuser['id'];
$voteuser['avatar'] = !empty($voteuser['avatar']) ? $voteuser['avatar'] : $voteuser["img1"];

$pvtotal = pdo_fetch("SELECT pv_total FROM " . tablename($this->tablecount) . " WHERE tid = :tid AND rid = :rid ", array(':tid' => $id, ':rid' => $rid));
if (empty($pvtotal)) {
	$pvtotal['pv_total'] = 0;
}
$pvtotal['pv_total'] = $pvtotal['pv_total'] + $voteuser['vheat'];
if ($voteuser['openid'] != $userinfo['openid']) {
	$myvoteuser = pdo_fetch("SELECT id FROM " . tablename($this->tablevoteuser) . " WHERE rid = :rid AND  openid = :openid ", array(':rid' => $rid, ':openid' => $openid));
	if (!empty($myvoteuser)) {
		$myvoteid = $myvoteuser['id'];
	}
}

$userCode = pdo_fetch('SELECT id,code FROM ' . tablename($this->tablecode) . " WHERE status = 1 AND tid = :tid AND rid = :rid AND type = :type", array(':tid' => $id, ':rid' => $rid, ':type' => $type));
if (empty($userCode)) {
//兑换码
	$userCode = pdo_fetch('SELECT id,code FROM ' . tablename($this->tablecode) . " WHERE status = 0 and type=:type order by createtime limit 1", array(':type' => $type));
	if (!empty($userCode)) {
		pdo_update($this->tablecode, array('tid' => $id, 'rid' => $rid, 'type' => $type, 'status' => 1), array('id' => $userCode['id']));
	}
}

$_share['desc'] = !empty($reply['sharedesc']) ? $reply['sharedesc'] : $reply['description'];
$_share['link'] = $_W['siteroot'] . "app/" . $this->createMobileUrl('view', array('id' => $id, 'rid' => $rid));
$_W['page']['sitename'] = "我是" . $voteuser['name'] . ",编号" . $voteuser['noid'] . ",正在参加" . $reply['title'] . "活动，来帮我投一票吧。";

include $this->template(m('tpl')->style('award', $reply['style']['template']));
