<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
//此页的调用地址  index.php?mod=test;
//同目录的其他php文件调用  index.php?mod=test&op=test1;

if (!defined('IN_DZZ')) {//所有的php文件必须加上此句，防止被外部调用
	exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
if($_GET['do']=='saveIndex'){
	$appids=implode(',',$_GET['appids']);
	C::t('user_setting')->update_by_skey('index_simple_appids',$appids);
	$ret=C::t('user_setting')->insert(array('index_simple_appids'=>$appids));
	exit(json_encode(array('success'=>$ret)));
}else{
	$config = array();
	if($_G['uid']){
		$config = C::t('user_field')->fetch($_G['uid']);
		
		if(!$config){
			$config= dzz_userconfig_init();
			if($config['applist']){
				$applist=explode(',',$config['applist']);
			}else{
				$applist=array();
			}
		 }else{//检测不允许删除的应用,重新添加进去
			if($config['applist']){
				$applist=explode(',',$config['applist']);
			}else{
				$applist=array();
			}
			if($applist_n =array_keys(C::t('app_market')->fetch_all_by_notdelete($_G['uid']))) {
			
				$newappids = array();
				foreach ($applist_n as $appid) {
					if (!in_array($appid, $applist)) {
						$applist[] = $appid;
						$newappids[] = $appid;
					}
				}
				if ($newappids) C::t('app_user')->insert_by_uid($_G['uid'], $newappids);
				C::t('user_field')->update($_G['uid'], array('applist' => implode(',', $applist)));
			}
		 }

	}else{
		 $applist =array_keys(C::t('app_market')->fetch_all_by_default());
	}



	//获取已安装应用
	$app=C::t('app_market')->fetch_all_by_appid($applist); 
	$applist_1=array();
	foreach($app as $key => $value){
		if($value['isshow']<1) continue;
		if($value['available']<1) continue;
		if($value['appurl']=='{dzzscript}?mod=index_simple') continue;
		if($value['position']<1) continue;//位置为无的忽略
		//判断管理员应用
		if($_G['adminid']!=1 && $value['group']==3){
			continue;
		}
		//if($value['system'] == 2) continue;
		$applist_1[$value['appid']] = $value; 
	}

	if($sortids=C::t('user_setting')->fetch_by_skey('index_simple_appids')){
		$appids=explode(',',$sortids);
		$temp=array();
		foreach($appids as $appid){
			if($applist_1[$appid]){
				$temp[$appid]=$applist_1[$appid];
				unset($applist_1[$appid]);
			}
		}
		
		foreach($applist_1 as $appid =>$value){
			$temp[$appid]=$value;
		}
		$applist_1=$temp;
	}else{
	
		//对应用根据disp 排序
		if($applist_1){
			$sort = array(
				  'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
				  'field'     => 'disp', //排序字段
			);
			$arrSort = array();
			foreach($applist_1 AS $uniqid => $row){
				foreach($row AS $key=>$value){
					$arrSort[$key][$uniqid] = $value;
				}
			}
			if($sort['direction']){
				array_multisort($arrSort[$sort['field']], constant($sort['direction']), $applist_1);
			} 
		}
	}

	include  template('main');
		
}
