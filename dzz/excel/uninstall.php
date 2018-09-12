<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

//提示用户删除的严重程度
if($_GET['confirm']=='DELETE'){
	@set_time_limit(0);
	//卸载程序；

	//删除数据
	$tpl_list = DB::fetch_all("select tid,attach,cover_aid,c.cid as cid from %t t left join %t c on t.cat_id = c.cid where c.type = 2", array('doc_template', 'doc_template_cat'));
	foreach($tpl_list as $value){
		C::t('attachment')->delete_by_aid($value['attach']);
		C::t('attachment')->delete_by_aid($value['cover_aid']);
		DB::delete('doc_template', array('tid' => $value['tid']));//C::tp_t('doc_template')->delete($value['tid']);
		DB::delete('doc_template_records', array('tid' => $value['tid']));//C::tp_t('doc_template_records')->where(array('tid' => $value['tid']))->delete();
	}
	//删除设置值
	DB::delete('user_setting', array('skey' => 'doc_excel_tplhide'));
	DB::delete('user_setting', array('skey' => 'doc_excel_createtype'));
	DB::delete('user_setting', array('skey' => 'doc_excel_savepath'));
	DB::delete('user_setting', array('skey' => 'doc_excel_openappid'));
	DB::delete('user_setting', array('skey' => 'doc_excel_showtype'));
	//删除分类
	DB::delete('doc_template_cat', array('type' => 2));
	$finish = true;
}else{
	header("Location: $confirm_uninstall_url");
	exit();
}
