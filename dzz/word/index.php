<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
Hook::listen('check_login');
include_once libfile('function/common');

$navtitle=lang('docs_list').' - '.lang('docs');

//获取最近使用模板
$lately_tpl = C::t('doc_template_records')->fetch_lately_records_by_uid();

//全部模板
$res = C::t('doc_template')->fetch_all();
$tpl_cat = DB::fetch_all('select * from %t where type = 1', array('doc_template_cat'), 'cid');
$data = array();
foreach ($tpl_cat as $k => $v) {
	$data[$v['cid']] = array();
	foreach ($res as $kk => $vv) {
		if ($vv['cat_id'] == $v['cid']) {
			$data[$v['cid']][] = $vv;
			unset($res[$kk]);
		}
	}
}

$setting = getsetting($_G['uid']);

include template('index');

?>
