<?php
/**
 * [WECHAT 2018]
 * [WECHAT  a free software]
 */

defined('IN_IA') or exit('Access Denied');

class QrcodeTable extends We7Table {
	public function searchTime($start_time, $end_time) {
		$this->query->where('createtime >=', $start_time)->where('createtime <=', $end_time);
		return $this;
	}

	public function searchKeyword($keyword) {
		$this->query->where('name LIKE', "%{$keyword}%");
		return $this;
	}

	public function qrcodeStaticList($status) {
		global $_W;
		$this->query->from('qrcode_stat')->where('uniacid', $_W['uniacid'])->where('acid', $_W['acid']);
		if (!empty($status)) {
			$this->query->groupby('qid');
			$this->query->groupby('openid');
			$this->query->groupby('type');
		}
		$this->query->orderby('createtime', 'DESC');
		return $this->query->getall();
	}
}