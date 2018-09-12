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
if(!$_G['adminid']) showmessage(lang('no_privilege'));

$navtitle=lang('tpl_manage').' - '.lang('docs');
$setting = getsetting($_G['uid']);

$do = $_GET['do'];
if ($do == 'cat_rename') {
	$cid = $_GET['cid'];
	$name = $_GET['name'];
	if($cid && $name && !DB::result_first('select count(*) from %t where name = %s', array('doc_template_cat', $name))){

		if(DB::update('doc_template_cat', array('name' => $name), array('cid' => $cid))){
			exit(json_encode(array('code' => 200, 'message' => lang('rename_success'))));
		}
	} else {
		exit(json_encode(array('code' => 400, 'message' => lang('rename_failed'))));
	}

} elseif($do == 'tpl_rename') {
	$tid = $_GET['tid'];
	$title = $_GET['title'];
	if($tid && $title && !DB::result_first('select count(*) from %t where title = %s', array('doc_template', $title))){

		if(C::t('doc_template')->rename($tid, $title)){
			exit(json_encode(array('code' => 200, 'message' => lang('rename_success'))));
		}
	} else {
		exit(json_encode(array('code' => 400, 'message' => lang('rename_failed'))));
	}
} elseif($do == 'movetpl') {//移动模板
	$cid = intval($_GET['cid']);
	$tid = intval($_GET['tid']);
	$cat = C::t('doc_template_cat')->fetch(array('cid' => $cid, 'type' => 3));
	$tpl = C::t('doc_template')->fetch_by_tid($tid);
	if($cat && $tpl){
		DB::update('doc_template', array('cat_id' => $cid), array('tid' => $tid));
		exit(json_encode(array('code' => 200, 'message' => lang('move_tpl_success'))));
	} else {
		exit(json_encode(array('code' => 400, 'message' => lang('tpl_or_cat_no_exist'))));
	}
} elseif($do == 'tpl_delete') {
	$tid = $_GET['tid'];
	if(C::t('doc_template')->delete_by_tid($tid)){
		exit(json_encode(array('code' => 200, 'message' => lang('delete_success'))));
	} else {
		exit(json_encode(array('code' => 400, 'message' => lang('delete_failed'))));
	}
} elseif($do == 'cat_delete') {
	$cid = $_GET['cid'];
	$tpls = C::t('doc_template')->fetch_by_cid($cid);
	foreach ($tpls as $key => $value) {
		C::t('doc_template')->delete_by_tid($value['tid']);
	}
	DB::delete('doc_template_cat', array('cid' => $cid));
	exit(json_encode(array('code' => 200, 'message' => lang('delete_success'))));

} elseif($do == 'uploadbg') {//修改封面
	include libfile('class/uploadhandler');
	$options=array( 'accept_file_types' => '/\.(gif|jpe?g|jpg|png)$/i',
					'upload_dir' =>$_G['setting']['attachdir'].'cache/',
					'upload_url' => $_G['setting']['attachurl'].'cache/',
					'thumbnail'=>array('max-width'=>100,'max-height'=>100)
					);
	$upload_handler = new uploadhandler($options);
	exit();

} elseif($do == 'savebg') {
	if(submitcheck('savebg')){
		$tid = $_GET['tid'];
		$aid = $_GET['aid'];
		$tpl = C::t('doc_template')->fetch_by_tid($tid);
		C::t('attachment')->delete_by_aid($tpl['cover_aid']);
		DB::update('doc_template', array('cover_aid' => $aid), array('tid' => $tid));
		C::t('attachment')->addcopy_by_aid($aid);
		exit(json_encode(array('code' => 200, 'src' => C::t('attachment')->getThumbByAid($aid,178,142))));
	} else {
		exit(json_encode(array('code' => 400)));
	}
} elseif($do == 'uploadtpl') {//上传模板
	include libfile('class/uploadhandler');
	$options=array( 'accept_file_types' => '/\.(ppt|pptx)$/i',
					'upload_dir' =>$_G['setting']['attachdir'].'cache/',
					'upload_url' => $_G['setting']['attachurl'].'cache/',
					'thumbnail'=>array('max-width'=>200,'max-height'=>255)
					);
	$upload_handler = new uploadhandler($options);
	exit();
} elseif($do == 'addtpl') {
	if(submitcheck('addtpl')){
		$cid = intval($_GET['cid']);
		if (!C::t('doc_template_cat')->fetch(array('cid' => $cid, 'type' => 3))) exit(json_encode(array('code' => 400, 'message' => lang('chose_cat_error'))));
		$setarr = array(
					'title' 		=>		 $_GET['name'],
					'attach'		=>		 $_GET['aid'],
					'authorid'		=>		 intval($_G['uid']),
					'cover_aid'		=>		 '',
					'cat_id'		=>		 $cid,
					'time'			=>		 TIMESTAMP,
					'updatetime'	=>		 TIMESTAMP
				);
		if(DB::result_first('select count(*) from %t where attach = %d', array('doc_template', $_GET['aid']))){
			exit(json_encode(array('code' => 400, 'message' => lang('tpl_existed'))));
		}
		$tid = DB::insert('doc_template', $setarr, 1);
		C::t('attachment')->addcopy_by_aid($_GET['aid']);
		$data = C::t('doc_template')->fetch_by_tid($tid);
		if ($setting['openurl']) {
			$openurl = $setting['openurl'].'&path='.dzzencode('attach::'.$_GET['aid']);
		}
		exit(json_encode(array('code' => 200, 'message' => lang('tpl_save_cuccess'), 'data' => array('tid' => $tid, 'img' => $data[$tid]['img']), 'openurl' => $openurl) ) );
	} else {
		exit(json_encode(array('code' => 400, 'message' => lang('tpl_save_failed'))));
	}

} elseif($do == 'addtplcat') {//增加模板分类
	$name = $_GET['name'];
	if (DB::result_first('select count(*) from %t where name = %s and type = 2', array('doc_template_cat', $name))) {
		exit(json_encode(array('code' => 400, 'message' => lang('cat_existed'))));
	}
	$setarr = array(
				'name' => $name,
				'type' =>	3,
				'authorid' => $_G['uid'],
				'time'	=>	TIMESTAMP,
			);
	$cid = C::t('doc_template_cat')->insert($setarr);
	if($cid){
		exit(json_encode(array('code' => 200, 'data' => array('cid' => $cid, 'name' => $name))));
	}
} else {

	$tpl = C::t('doc_template')->fetch_all();
	$tpl_cat = C::t('doc_template_cat')->fetch_all();
	$data = array();
	foreach ($tpl_cat as $k => $v) {
		$data[$v['cid']] = array();
		foreach ($tpl as $kk => $vv) {
			if ($vv['cat_id'] == $v['cid']) {
				$data[$v['cid']][] = $vv;
				unset($tpl[$kk]);
			}
		}
	}
	include template('tplmanage');

}

?>
