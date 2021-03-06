<?php
/**
 * 钻石投票-投票
 *
 * @author 微实惠科技
 * @url https://spf360.taobao.com
 */

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;

// is_weixin();
$rid = intval($_GPC['rid']);
$id = intval($_GPC['id']);
$sourceid = intval($_GPC['sourceid']);
$id = empty($id) ? $sourceid : $id;
$ty = $_GPC['ty'];
$count = intval($_GPC['count']);
$count = empty($count) ? 1 : $count;

$userinfo = $this->oauthuser;
$oauth_openid = $userinfo['oauth_openid'];
m('domain')->randdomain($rid, 1);
if (empty($oauth_openid)) {
	message("无法获取OPNEID，请查看是否借权或配置好公众号！(0101)");
}
$reply = pdo_fetch("SELECT rid,title,sharetitle,shareimg,sharedesc,config,style,giftdata,starttime,endtime,apstarttime,apendtime,votestarttime,voteendtime,status,description  FROM " . tablename($this->tablereply) . " WHERE rid = :rid ", array(':rid' => $rid));
$reply['style'] = @unserialize($reply['style']);
$reply = array_merge($reply, unserialize($reply['config']));unset($reply['config']);
if (empty($reply['status'])) {message("活动已禁用");}
if (empty($reply)) {
	message("参数错误");
}

if ($reply['starttime'] > time()) {
	message("活动还没有开始");
}

//活动未开始
if ($reply['endtime'] < time()) {
	message("活动已经结束");
}

//活动未开始
if (empty($reply['status'])) {
	message("活动已禁用");
}

$giftdata = @unserialize($reply['giftdata']);

$voteuser = pdo_fetch("SELECT * FROM " . tablename($this->tablevoteuser) . " WHERE rid = :rid AND  id = :id ", array(':rid' => $rid, ':id' => $id));

$voteuser['avatar'] = !empty($voteuser['avatar']) ? $voteuser['avatar'] : tomedia($voteuser["img1"]);

if ($ty['ispost']) {
	//是否达到最小人数
	if (!empty($reply['minnumpeople'])) {
		$condition = "";
		if ($reply['ischecked'] == 1) {
			$condition .= " AND status=1 ";
		}
		$jointotal = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->tablevoteuser) . " WHERE   rid = :rid  " . $condition, array(':rid' => $rid));
		if ($reply['minnumpeople'] > $jointotal) {
			exit(json_encode(array('status' => '0', 'msg' => "活动还未开始，没有达到最小参赛人数！")));
		}
	}
	$gift = $giftdata[$_GPC['giftid']];
	$tid = $id . md5($userinfo['openid']);
	$params = array(
		'tid' => $tid,
		'ordersn' => $tid,
		'title' => '报名费',
		'fee' => 2.00,
		'user' => $_W['member']['uid'],
		'module' => $this->module['name'],
	);

	file_put_contents('/home/www/toupiao/join.txt', json_encode($params) . "\n", FILE_APPEND);

	$acid = !empty($_SESSION['oauth_acid']) ? $_SESSION['oauth_acid'] : $_SESSION['acid'];
	if (!empty($_SESSION['oauth_acid'])) {
		$acid = $_SESSION['oauth_acid'];
		$account_wechats = pdo_fetch("SELECT uniacid FROM " . tablename('account_wechats') . " WHERE  acid = :acid ", array(':acid' => $acid));
		$uniacid = $account_wechats['uniacid'];
	} else {
		$acid = $_SESSION['acid'];
		$uniacid = $_W['uniacid'];
	}

	$giftdata = array(
		'rid' => $rid,
		'tid' => $id,
		'type' => 2,
		'uniacid' => $_W['uniacid'],
		'oauth_openid' => $userinfo['oauth_openid'],
		'openid' => $userinfo['openid'],
		'avatar' => $userinfo['avatar'],
		'nickname' => $userinfo['nickname'],
		'user_ip' => $_W['clientip'],
		'gifticon' => $gift['gifticon'],
		'gifttitle' => '报名费',
		'giftcount' => 1,
		'giftvote' => 1,
		'fee' => 2.00,
		'ptid' => $tid,
		'ispay' => 0,
		'status' => 0,
		'createtime' => time(),
	);
	file_put_contents('/home/www/toupiao/join.txt', "giftdata-------" . json_encode($giftdata) . "\n", FILE_APPEND);
	if (pdo_insert($this->tablegift, $giftdata)) {
		// if(empty($reply['defaultpay'])){
		// 	$out['status'] = 200;
		// 	$out['pay_url'] = $_W['siteroot']."payment/wechat/pay.php?i={$uniacid}&auth={$auth}&ps={$sl}&payopenid={$giftdata['oauth_openid']}";
		// }else{
		$_share['title'] = "在线支付";
		$this->pay($params);
		// }
	} else {
		exit(json_encode(array('status' => '0', 'msg' => "操作失败，请刷新后再试！")));
	}
	exit;
}
$lsun = 0;
foreach ($giftdata as $key => $value) {
	$xiuyu = $key % 3;
	if (empty($xiuyu)) {
		$i++;
	}
	$giftlist[$i][$key] = $value;
	$lsun = $key;
}
$pvtotal = pdo_fetch("SELECT pv_total FROM " . tablename($this->tablecount) . " WHERE tid = :tid AND rid = :rid ", array(':tid' => $id, ':rid' => $rid));
if (empty($pvtotal)) {
	$pvtotal['pv_total'] = 0;
}
$pvtotal['pv_total'] = $pvtotal['pv_total'] + $voteuser['vheat'];
$reply['giftunit'] = $reply['giftunit'] ? $reply['giftunit'] : "点";
$_share['title'] = !empty($reply['sharetitle']) ? $reply['sharetitle'] : $reply['title'];
$_share['imgUrl'] = !empty($reply['shareimg']) ? tomedia($reply['shareimg']) : tomedia($reply['thumb']);
$_share['desc'] = !empty($reply['sharedesc']) ? $reply['sharedesc'] : $reply['description'];
$_W['page']['sitename'] = $reply['title'];

include $this->template(m('tpl')->style('payvote', $reply['style']['template']));
