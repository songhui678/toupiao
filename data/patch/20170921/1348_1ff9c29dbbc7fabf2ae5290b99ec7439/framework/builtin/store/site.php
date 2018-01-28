<?php
/**
 * 站内商城
 * @author WeEngine Team
 */
defined('IN_IA') or exit('Access Denied');
class StoreModuleSite extends WeModuleSite {
	public function __construct() {
		global $_W;
		if ((!$_W['isfounder'] || user_is_vice_founder()) && $_W['setting']['store']['status'] == 1) {
			itoast('商城已被创始人关闭！', referer(), 'error');
		}
	}
	public function payResult($params) {
		global $_W;
		if($params['result'] == 'success' && $params['from'] == 'notify') {
			$order = pdo_get('site_store_order', array('id' => $params['tid'], 'type' => 1));
			if(!empty($order)) {
				pdo_update('site_store_order', array('type' => 3), array('id' => $params['tid']));
				cache_delete(cache_system_key($order['uniacid'] . ':site_store_buy_modules'));
				cache_build_account_modules($order['uniacid']);
			}
		}
	}

	public function doWebPaySetting() {
		global $_W, $_GPC;
		if (!$_W['isfounder'] || user_is_vice_founder()) {
			itoast('', referer(), 'info');
		}
		$operate = $_GPC['operate'];
		$operates = array('alipay');
		$operate = in_array($operate, $operates) ? $operate : 'alipay';

		$_W['page']['title'] = '支付设置 - 商城';
		$settings = $_W['setting']['store_pay'];
		if ($operate == 'alipay') {
			$alipay = $settings['alipay'];
			$data = array();
			if (checksubmit('submit')) {
				$data['alipay'] = array(
					'account' => trim($_GPC['account']),
					'partner' => trim($_GPC['partner']),
					'secret' => trim($_GPC['secret']),
				);
				setting_save($data, 'store_pay');
				itoast('设置成功！', referer(), 'success');
			}
		}
		include $this->template('paysetting');
	}

	public function doWebOrders() {
		global $_GPC, $_W;
		load()->model('store');
		load()->model('module');

		$operates = array('display', 'change_price', 'delete');
		$operate = $_GPC['operate'];
		$operate = in_array($operate, $operates) ? $operate : 'display';

		$_W['page']['title'] = '订单管理 - 商城';
		if (user_is_vice_founder()) {
			$role = 'buyer';
		} elseif (!empty($_W['isfounder'])) {
			$role = 'seller';
		} else {
			$role = 'buyer';
		}

		if ($operate == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;

			$store_table = table('store');
			if (isset($_GPC['type']) && intval($_GPC['type']) > 0) {
				$order_type = intval($_GPC['type']);
				$store_table->searchOrderType($order_type);
			} else {
				$store_table->searchOrderType(STORE_ORDER_PLACE, STORE_ORDER_FINISH);
			}
			if (empty($_W['isfounder']) || user_is_vice_founder()) {
				$store_table->searchOrderWithUid($_W['uid']);
			}
			$order_list = $store_table->searchOrderList($pindex, $psize);
			$total = $store_table->getLastQueryTotal();
			$pager = pagination($total, $pindex, $psize);
			if (!empty($order_list)) {
				foreach ($order_list as &$order) {
					$order['createtime'] = date('Y-m-d H:i:s', $order['createtime']);
					$order['goods_info'] = store_goods_info($order['goodsid']);
					$order['abstract_amount'] = $order['duration'] * $order['goods_info']['price'];
					if (!empty($order['goods_info']) && $order['goods_info']['type'] == STORE_TYPE_MODULE) {
						$order['goods_info']['module_info'] = module_fetch($order['goods_info']['module']);
					}
				}
				unset($order);
			}
		}

		if ($operate == 'change_price') {
			if (user_is_vice_founder() || empty($_W['isfounder'])) {
				iajax(-1, '无权限更改！');
			}
			$id = intval($_GPC['id']);
			$price = floatval($_GPC['price']);
			$if_exists = store_order_info($id);
			if (empty($if_exists)) {
				iajax(-1, '订单不存在！');
			}
			$result = store_order_change_price($id, $price);
			if (!empty($result)) {
				iajax(0, '修改成功！');
			} else {
				iajax(-1, '修改失败！');
			}
		}

		if ($operate == 'delete') {
			$id = intval($_GPC['id']);
			if (empty($id)) {
				itoast('订单错误，请刷新后重试！');
			}
			$order_info = store_order_info($id);
			if (empty($order_info)) {
				itoast('订单不存在！');
			}
			if ($order_info['type'] != STORE_ORDER_PLACE) {
				itoast('只可删除未完成交易的订单！');
			}
			$result = store_order_delete($id);
			if (!empty($result)) {
				itoast('删除成功！', referer(), 'success');
			} else {
				itoast('删除失败，请稍候重试！', referer(), 'error');
			}
		}
		include $this->template('orders');
	}

	public function doWebSetting() {
		global $_GPC, $_W;
		if (!$_W['isfounder'] || user_is_vice_founder()) {
			itoast('', referer(), 'info');
		}
		$operate = $_GPC['operate'];
		$operates = array('setting');
		$operate = in_array($operate, $operates) ? $operate : 'setting';

		$_W['page']['title'] = '商城设置 - 商城';

		$settings = $_W['setting']['store'];
		if ($operate == 'setting') {
			if (checksubmit('submit')) {
				$status = intval($_GPC['status']) > 0 ? 1 : 0;
				$data = array(
					'status' => $status,
				);
				$test = setting_save($data, 'store');
				itoast('更新设置成功！', referer(), 'success');
			}
			include $this->template('storesetting');
		}
	}

	public function doWebGoodsSeller() {
		global $_GPC, $_W;
		load()->model('store');
		load()->model('module');
		if (!$_W['isfounder'] || user_is_vice_founder()) {
			itoast('', referer(), 'info');
		}
		$operate = $_GPC['operate'];
		$operates = array('display', 'delete', 'changestatus');
		$operate = in_array($operate, $operates) ? $operate : 'display';

		$_W['page']['title'] = '商品列表 - 商城管理 - 商城';
		if ($operate == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;

			$store_table = table('store');
			$keyword = trim($_GPC['keyword']);
			if (!empty($keyword)) {
				$store_table->searchWithKeyword($keyword);
			}
			$status = isset($_GPC['online']) && $_GPC['online'] == 0 ? 0 : 1;
			$store_table->searchWithStatus($status);
			if(isset($_GPC['letter']) && strlen($_GPC['letter']) == 1) {
				$store_table->searchWithLetter($_GPC['letter']);
			}
			$goods_list = $store_table->searchGoodsList(STORE_TYPE_MODULE, $pindex, $psize);
			$total = $goods_list['total'];
			$goods_list = $goods_list['goods_list'];
			$pager = pagination($total, $pindex, $psize);
			if (!empty($goods_list)) {
				foreach ($goods_list as &$good) {
					$good['module_info'] = module_fetch($good['module']);
				}
				unset($good);
			}
			$module_list = array();
			$modules = user_uniacid_modules($_W['uid']);
			$have_module_goods = $store_table->searchHaveModule();
			$have_module_goods = array_keys($have_module_goods);
			$have_module_goods = array_unique($have_module_goods);
			if (!empty($modules)) {
				foreach ($modules as $module) {
					if (in_array($module['name'], $have_module_goods)) {
						continue;
					}
					$module = module_fetch($module['name']);
					$module_list[] = $module;
				}
			}
		}

		if ($operate == 'changestatus' || $operate == 'delete') {
			$id = intval($_GPC['id']);
			$if_exist = store_goods_info($id);
			if (empty($if_exist)) {
				itoast('商品不存在，请刷新后重试！', referer(), 'error');
			}
		}
		if ($operate == 'changestatus') {
			$result = store_goods_changestatus($id);
			if (!empty($result)) {
				itoast('更新成功！', referer(), 'success');
			} else {
				itoast('更新失败！', referer(), 'error');
			}
		}

		if ($operate == 'delete') {
			$result = store_goods_delete($id);
			if (!empty($result)) {
				itoast('删除成功！', referer(), 'success');
			} else {
				itoast('删除失败！', referer(), 'error');
			}
		}
		include $this->template('goodsseller');
	}

	public function doWebGoodsPost() {
		global $_GPC, $_W;
		load()->model('store');
		if (!$_W['isfounder'] || user_is_vice_founder()) {
			itoast('', referer(), 'info');
		}
		$operate = $_GPC['operate'];
		$operates = array('post', 'add');
		$operate = in_array($operate, $operates) ? $operate : 'post';

		$_W['page']['title'] = '编辑商品 - 商城管理 - 商城';

		if ($operate == 'post') {
			$id = intval($_GPC['id']);
			if (checksubmit('submit')) {
				if (!empty($_GPC['price']) && !is_numeric($_GPC['price'])) {
					itoast('请填写有效数字！', referer(), 'error');
				}
				$data = array(
					'title' => !empty($_GPC['title']) ? trim($_GPC['title']) : '',
					'price' => !empty($_GPC['price']) ? $_GPC['price'] : 0,
					'slide' => !empty($_GPC['slide']) ? iserializer($_GPC['slide']) : '',
				);
				if ($_GPC['submit'] == '保存并上架') {
					$data['status'] = 1;
				}
				if (!empty($id)) {
					$data['id'] = $id;
				}
				$result = store_goods_post($data);
				if (!empty($result)) {
					if (!empty($id)) {
						itoast('编辑成功！', $this->createWebUrl('goodsseller', array('direct' =>1, 'online' => 0)), 'success');
					} else {
						itoast('添加成功！', $this->createWebUrl('goodsSeller', array('direct' =>1)), 'success');
					}
				} else {
					itoast('未作任何更改或编辑/添加失败！', referer(), 'error');
				}
			}

			if (!empty($id)) {
				$goods_info = store_goods_info($id);
				$goods_info['slide'] = !empty($goods_info['slide']) ? (array)iunserializer($goods_info['slide']) : array();
				$goods_info['price'] = floatval($goods_info['price']);
			}
		}
		if ($operate == 'add') {
			if (empty($_GPC['module'])) {
				iajax(-1, '请选择一个模块！');
			}
			$data = array(
				'title' => trim($_GPC['module']['title']),
				'module' => trim($_GPC['module']['name']),
				'synopsis' => trim($_GPC['module']['ability']),
				'description' => trim($_GPC['module']['description']),
			);
			$result = store_goods_post($data);
			if (!empty($result)) {
				if (isset($_GPC['toedit']) && !empty($_GPC['toedit'])) {
					$id = pdo_insertid();
					iajax(0, $id);
				} else {
					iajax(0, '添加成功！');
				}
			} else {
				iajax(-1, '添加失败！');
			}
		}
		include $this->template('goodspost');
	}

	public function doWebGoodsBuyer()
	{
		global $_GPC, $_W;
		load ()->model ('module');
		load ()->model ('payment');
		load ()->func ('communication');

		$operate = $_GPC['operate'];
		$operates = array ('display', 'goods_info', 'get_expiretime', 'submit_order', 'pay_order');
		$operate = in_array ($operate, $operates) ? $operate : 'display';
		$_W['page']['title'] = '商品列表 - 商城';

		if ($operate == 'display') {
			$pageindex = max (intval ($_GPC['page']), 1);
			$pagesize = 24;
			$type = !empty($_GPC['type']) ? $_GPC['type'] : 1;
			$store_table = table ('store');
			$store_table->searchWithStatus (1);
			$store_table = $store_table->searchGoodsList ($type, $pageindex, $pagesize);
			$store_goods = $store_table['goods_list'];
			$purchased_goods = pdo_fetchall ('SELECT * FROM ' . tablename ('site_store_order') . " WHERE buyerid = :uid AND createtime + duration * 2592000 >= :times AND type = 3", array (':times' => time (), ':uid' => $_W['uid']), 'goodsid');
			if ($type == STORE_TYPE_MODULE && is_array ($store_goods)) {
				foreach ($store_goods as $key => &$goods) {
					if (in_array ($goods['id'], array_keys ($purchased_goods))) {
						unset($store_goods[$key]);
						continue;
					}
					$goods['module'] = module_fetch ($goods['module']);
				}
				unset($goods);
			}
			$pager = pagination ($store_table['total'], $pageindex, $pagesize);
		}

		if ($operate == 'goods_info') {
			$goods = intval ($_GPC['goods']);
			if (empty($goods)) {
				itoast ('商品不存在', '', 'info');
			}
			$goods = pdo_get ('site_store_goods', array ('id' => $goods));
			if ($goods['type'] == STORE_TYPE_MODULE) {
				$goods['module'] = module_fetch ($goods['module']);
				$goods['slide'] = iunserializer ($goods['slide']);
			}
			$account_table = table ('account');
			$user_account = $account_table->userOwnedAccount ();
		}

		if ($operate == 'get_expiretime') {
			$duration = intval ($_GPC['duration']);
			$date = date ('Y-m-d', strtotime ('+' . $duration . 'month', time ()));
			iajax (0, $date);
		}
		if ($operate == 'submit_order') {
			$uniacid = intval ($_GPC['uniacid']);
			$uid = empty($_W['uid']) ? '000000' : sprintf ("%06d", $_W['uid']);
			$orderid = date ('YmdHis') . $uid . random (8, 1);
			$duration = intval ($_GPC['duration']);
			$order = array (
				'orderid' => $orderid,
				'duration' => $duration,
				'amount' => $_GPC['price'] * $duration,
				'goodsid' => intval($_GPC['goodsid']),
				'buyer' => $_W['user']['username'],
				'buyerid' => $_W['uid'],
				'type' => 1,
				'createtime' => time (),
				'uniacid' => $uniacid
			);
			pdo_insert ('site_store_order', $order);
			$store_orderid = pdo_insertid();
			$pay_log = array(
				'type' => 'alipay',
				'uniontid' => $orderid,
				'tid' => $store_orderid,
				'fee' => $order['amount'],
				'card_fee' => $order['amount'],
				'module' => 'store'
			);
			pdo_insert('core_paylog', $pay_log);
			iajax (0, $store_orderid);
		}

		if ($operate == 'pay_order') {
			$orderid = intval ($_GPC['orderid']);
			$order = pdo_get ('site_store_order', array ('id' => $orderid));
			if ($order['type'] != 1) {
				$message = $order['type'] == 1 ? '订单已删除.' : '订单已付款成功';
				itoast ($message, referer (), 'info');
			} else {
				if ($order['amount'] == 0) {
					pdo_update('site_store_order', array('type' => 3), array('id' => $order['id']));
					pdo_update('core_paylog', array('status' => 1), array('uniontid' => $order['orderid']));
					cache_delete(cache_system_key($order['uniacid'] . ':site_store_buy_modules'));
					cache_build_account_modules($order['uniacid']);
					itoast('支付成功!', $this->createWebUrl('orders', array('direct' => 1)), 'success');
				}
			}
			$goods = pdo_get ('site_store_goods', array ('id' => $order['goodsid']));
			$alipay_setting = setting_load ('store_pay');
			$alipay_setting = $alipay_setting['store_pay']['alipay'];

			$alipay_params = array (
				'service' => 'create_direct_pay_by_user',
				'title' => $goods['title'],
				'fee' => $order['amount'],
				'uniontid' => $order['orderid'],
			);
			$alipay_result = alipay_build ($alipay_params, $alipay_setting);
			header ('Location: ' . $alipay_result['url']);
			exit();
		}
		include $this->template ('goodsbuyer');
	}
}