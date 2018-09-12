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
require DZZ_ROOT.'./core/api/wopi/wopi.php';
$app=C::t('app_market')->fetch_by_identifier('collabora');
$app['extra']=unserialize($app['extra']);
$wopiRemote= $app['extra']['DocumentUrl'];
if(empty($wopiRemote)) showmessage('应用文档服务器为空，不能调用，请卸载重新安装');

$path = dzzdecode($_GET['path']);
$meta=IO::getMeta($path);
$maxUploadFilesize =102400000;//bytes 
$retVal = array(
	'enable_previews' => true,  
	'wopi_url' => $webSocket, 
);  

$wopiInfo=wopi::GenerateFileLink($path,$wopiRemote,'collabora');
if(isset($wopiInfo['error'])){
	showmessage($wopiInfo['error']);
}
$docs['mimetype'] = $meta["ext"];
$docs['path'] =  $meta["dpath"];
$docs['name'] = $meta["name"]?$meta["name"]:$meta["filename"];
$docs['fileid'] = $wopiInfo['fileID'];

$docRetVal = array(
	'permissions' => 31, 
	'title' => $meta["name"],
	'fileId' => $wopiInfo['fileID'],//$meta["rid"].'_'.$meta["dpath"],
	'token' => $meta['md5'],
	'urlsrc' => $wopiInfo["urlsrc"],
	'path' => $docs['path'],
	'wopi_url'=>$wopiRemote,
	'wopi_url2'=>$wopiInfo['fullsrc'],
	'access_token'=> $wopiInfo['access_token'],
	'lockstatus'=>intval($wopiInfo['lockstatus'])//锁状态，intval($wopiInfo['lockstatus'])==409说明被别人锁定，没有编辑权限
);
$retVal = array_merge($retVal, $docRetVal);
include template('index'); 
?>