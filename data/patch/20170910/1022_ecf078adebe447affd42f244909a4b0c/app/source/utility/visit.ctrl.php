<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we8.club/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('app');
app_update_today_visit($_GPC['m']);