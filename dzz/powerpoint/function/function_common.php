<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function nearlytime($time)
{
	if($time >= strtotime(date('Ymd', TIMESTAMP))){
		return 'today';
	} elseif($time >= strtotime(date('Ymd', TIMESTAMP)) - 24*3600) {
		return 'ytoday';
	} elseif($time >= strtotime(date('Ymd', TIMESTAMP)) - 7*24*3600) {
		return 'nearly_week';
	} elseif($time >= strtotime(date('Ymd', TIMESTAMP)) - 30*24*3600) {
		return 'nearly_month';
	} else {
		return 'other';
	}
}


//取得设置val
function getsetting($uid)
{
	$uid = $uid ? $uid : $_G['uid'];
	$appid = C::t('user_setting')->fetch_by_skey('doc_powerpoint_openappid', $uid);
	$openappid = $appid;
	if (!$appid) {
		$openappid = DB::result_first('select appid from %t where ext IN(%n) order by isdefault desc, disp asc', array('app_open', array('ppt', 'pptx')));
	}
	$app = C::t('app_market')->fetch_by_appid($openappid);
	$savepath = C::t('user_setting')->fetch_by_skey('doc_powerpoint_savepath', $uid);
	if (!$savepath) {
		$default_path = C::t('folder')->fetch_home_by_uid($uid); 
		$savepath = $default_path['fid'];
	}
	$folder = C::t('folder')->fetch_folderinfo_by_fid($savepath);
	$dirpath = explode(':', $folder['path']);
	$tplhide = C::t('user_setting')->fetch_by_skey('doc_powerpoint_tplhide', $uid);
	$createtype = C::t('user_setting')->fetch_by_skey('doc_powerpoint_createtype', $uid);
	return $setting = array(
						'showtype'		 =>		 C::t('user_setting')->fetch_by_skey('doc_powerpoint_showtype', $uid),
						'tplhide'		 =>		 is_null($tplhide) ? 1 : $tplhide,
						'createtype'	 =>		 is_null($createtype) ? 1 : $createtype,
						'savepath'		 =>		 $savepath,
						'appid'			 =>		 $appid,
						'openurl'		 =>		 replace_canshu($app['appurl']),
						'dirpath'		 =>		 $dirpath[2],	 
						);
}

//取得所有子级目录
function get_all_chilrdenfid_by_pfid($pfid)
{
	static $fids = array();
    foreach(C::t('folder')->fetch_fid_by_pfid($pfid) as $v){
        $fids[] = $v['fid'];
        get_all_chilrdenfid_by_pfid($v['fid']);
    }
    return $fids;
}

?> 
