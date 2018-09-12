<?php
/*
 * 应用卸载程序示例
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */ 
if (!defined('IN_DZZ') && !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$op="admin"; 
Hook::listen('adminlogin');

$app=C::t('app_market')->fetch_by_identifier('collabora','dzz');
$app['extra'] && $app['extra']=unserialize($app['extra']); 
if (!submitcheck('confirmsubmit')) {
	include template('admin');
} else {
	if ( $_GET['app_key'] ) { 
		$extra =$app['extra'];
		$extra["DocumentUrl"]=$_GET['app_key'];
		C::t('app_market')->update($app['appid'],array('extra'=>serialize($extra)));
		showmessage('save_success', $_GET['refer'], array(), array('alert' => 'right'));
	} else {
		showmessage('collabora_view_url_setfailed');
	}
	exit();
}