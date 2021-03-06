<?php
/**
 * 钻石投票-投票
 *
 * @author 微实惠科技
 * @url https://spf360.taobao.com
 */
defined('IN_IA') or die('Access Denied');
require IA_ROOT . '/addons/tyzm_diamondvote/defines.php';
require TYZM_MODEL_FUNC . '/function.php';
class tyzm_diamondvoteModuleSite extends WeModuleSite {
	public $tablereply = "tyzm_diamondvote_reply";
	public $tablevoteuser = "tyzm_diamondvote_voteuser";
	public $tablevotedata = "tyzm_diamondvote_votedata";
	public $tablegift = "tyzm_diamondvote_gift";
	public $tablecount = "tyzm_diamondvote_count";
	public $table_fans = "tyzm_diamondvote_fansdata";
	public $tableredpack = "tyzm_diamondvote_redpack";
	public $tablefriendship = "tyzm_diamondvote_friendship";
	public $tablelooklist = "tyzm_diamondvote_looklist";
	public $tableviporder = "tyzm_diamondvote_viporder";
	public $tableblacklist = "tyzm_diamondvote_blacklist";
	public $tabledomainlist = "tyzm_diamondvote_domainlist";
	public $tablesetmeal = "tyzm_diamondvote_setmeal";
	public $tablecode = "tyzm_diamondvote_code";
	public function __construct() {
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if (!(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false)) {
			$oauthuser = m('user')->Get_checkoauth();
			$this->oauthuser = $oauthuser;
		}
	}
	public function payResult($params) {
		global $_W, $_GPC;
		if ($params['result'] == 'success' && $params['from'] == 'notify') {
			$tycode = substr($params['tid'], 0, 4);
			if ($tycode == '8888') {
				$viporder = pdo_fetch('SELECT * FROM ' . tablename($this->tableviporder) . " WHERE ptid = :ptid", array(":ptid" => $params["tid"]));
				if ($params['fee'] == $viporder['fee'] && $viporder['ispay'] == 0) {
					$reviporder = pdo_update($this->tableviporder, array("ispay" => "1", "paytype" => $params["type"], "uniontid" => $params["uniontid"]), array("ptid" => $params["tid"]));
				}
				die;
			}
			$order = pdo_fetch('SELECT * FROM ' . tablename($this->tablegift) . " WHERE ptid = :ptid", array(":ptid" => $params["tid"]));
			if ($params['fee'] == $order['fee'] && $order['ispay'] == 0) {
				$reupvote = pdo_update($this->tablegift, array("ispay" => "1", "isdeal" => "1", "paytype" => $params["type"], "uniontid" => $params["uniontid"]), array("ptid" => $params["tid"], "oauth_openid" => $params["user"]));
				if (!empty($reupvote)) {
					$setvotesql = 'update ' . tablename($this->tablevoteuser) . " set status=1,votenum=votenum+" . $order["giftvote"] . ",giftcount=giftcount+" . $order["fee"] . ",lastvotetime=" . time() . "  where id = " . $order["tid"];
					$resetvote = pdo_query($setvotesql);
					if (empty($resetvote)) {
						pdo_update($this->{$tablegift}, array('isdeal' => 0), array('ptid' => $params['tid']));
					} else {

						$reply = pdo_fetch('SELECT config,title FROM ' . tablename($this->tablereply) . " WHERE rid = :rid ", array(":rid" => $order["rid"]));
						$reply = @array_merge($reply, unserialize($reply['config']));
						unset($reply['config']);
						if (empty($reply['isvotemsg']) || !empty($reply['awardgive_num'])) {
							$votedata = pdo_fetch('SELECT * FROM ' . tablename($this->tablevoteuser) . " WHERE id = :id ", array(":id" => $order["tid"]));
						}
						if (!empty($reply['giftgive_num'])) {
							m('present')->upcredit($order["openid"], $reply["giftgive_type"], $reply["giftgive_num"] * $params["fee"], "tyzm_diamondvote");
						}
						if (!empty($reply['awardgive_num'])) {
							m('present')->upcredit($votedata["openid"], $reply["awardgive_type"], $reply["awardgive_num"] * $params["fee"], "tyzm_diamondvote");
						}
						if (empty($reply['isvotemsg'])) {
							if ($order['type'] == 2) {
								//报名
								$signurl = "http://m.ruishivoc.com/col.jsp?id=115";
								$content = '恭喜您报名成功，赶快分享给好友为自己拉票吧！分享之前不要忘记领取参赛礼品哦~！<a href=\"' . $signurl . '\">点此领取参赛礼品<\/a>';
							} else {
								$uservoteurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl("view", array("rid" => $order["rid"], "id" => $votedata["id"]));

								$content = '您的好友给你' . $votedata['noid'] . '号【' . $votedata['name'] . '】送【' . $order['gifttitle'] . '】作为礼物！目前礼物共￥' . $votedata['giftcount'] . '，目前共' . $votedata['votenum'] . '票。<a href=\\"' . $uservoteurl . '\\">点击查看详情<\\/a>';
							}

							m('user')->sendkfinfo($votedata['openid'], $content);
							// header('location: ' . $uservoteurl);
							// header('location: http://www.baidu.com');
						}
					}
				}
			} else {
				die('用户支付的金额与订单金额不符合或已修改状态。');
			}
		}
		if ($params['from'] == 'return') {
			if ($params['result'] == 'success') {
				$url = '';
				$tycode = substr($params['tid'], 0, 4);
				if ($tycode == '8888') {
					$order = pdo_fetch('SELECT rid,tid,uniacid FROM ' . tablename($this->tableviporder) . " WHERE ptid = :ptid", array(":ptid" => $params["tid"]));
					// $url = murl('entry/payresult', array('m' => 'tyzm_diamondvote', 'ty' => 'user', 'rid' => $order['rid'], 'id' => $order['tid'], 'i' => $order['uniacid']));
					$url = $_W['siteroot'] . 'app/' . $this->createMobileUrl("payresult", array("rid" => $order["rid"], "id" => $order["tid"], 'i' => $order['uniacid']));
				} else {
					$order = pdo_fetch('SELECT id,tid,rid,uniacid FROM ' . tablename($this->tablegift) . " WHERE  ptid = :ptid ", array(":ptid" => $params["tid"]));

					if ($order['type'] == 1) {
						//礼物
						$url = $_W['siteroot'] . 'app/' . $this->createMobileUrl("view", array("rid" => $order["rid"], "id" => $order["tid"]));
					} else {
						//报名
						$url = "http://m.ruishivoc.com/col.jsp?id=115";
					}

				}
				header('location: ' . $url);
			} else {
				message('抱歉，支付失败，请刷新后再试！', 'referer', 'error');
			}
		}
	}
	public function authorization() {
	}
	public function doMobileRrcodeurl() {
		global $_W, $_GPC;
		$url = $_GPC['url'];
		require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
		$errorCorrectionLevel = 'L';
		$matrixPointSize = '6';
		QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
		die;
	}
	public function template_footer($name) {
	}
	public function oauth_uniacid() {
		global $_W, $_GPC;
		if ($_W['account']['level'] == 4) {
			$uniacid = $_W['uniacid'];
		} else {
			if ($_W['oauth_account']['level'] == 4) {
				$oauth_acid = $_W['oauth_account']['acid'];
				$account_wechats = pdo_fetch('SELECT uniacid FROM ' . tablename('account_wechats') . ' WHERE acid = :acid ', array(':acid' => $oauth_acid));
				$uniacid = $account_wechats['uniacid'];
			} else {
				$uniacid = $_W['uniacid'];
			}
		}
		return $uniacid;
	}
	public function get_resource($pic_path) {
		$pathInfo = pathinfo($pic_path);
		switch (strtolower($pathInfo["extension"])) {
			case 'jpg':
				$imagecreatefromjpeg = 'imagecreatefromjpeg';
				goto J3p0M;
			case 'jpeg':
				$imagecreatefromjpeg = 'imagecreatefromjpeg';
				goto J3p0M;
			case 'png':
				$imagecreatefromjpeg = 'imagecreatefrompng';
				goto J3p0M;
			case 'gif':
			default:
				$imagecreatefromjpeg = 'imagecreatefromstring';
				$pic_path = file_get_contents($pic_path);
				goto J3p0M;
		}
		J3p0M:
		$resource = $imagecreatefromjpeg($pic_path);
		return $resource;
	}
	public function json_exit($status, $msg) {
		die(json_encode(array('status' => $status, 'msg' => $msg)));
	}
}