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
$uid = $_G['uid'];
$do = $_GET['do'];
$setting = getsetting($uid);
if ($do == 'mydoc') {//我的文档
	$wheresql=' where r.isdelete < 1';
	$param=array('resources', 'resources_statis', 'folder');
	//筛选出文档
	$wheresql .= " and r.ext IN (%n)";
	$param[]=array('doc', 'docx');

	//关键词筛选
	$keyword = $_GET['keyword'];
	if($keyword){
        //查询标签表中有对应rid
        $tagids = array_keys(DB::fetch_all('select tid from %t where tagname like %s', array('tag', "%$keyword%"), 'tid'));
        $rids = C::t('resources_tag')->fetch_rid_by_tid($tagids);
        if(count($rids) < 1){
            $wheresql .= " and r.name like %s";
			$param[] = "%".$keyword."%";
        }else{
            $wheresql .= " and (r.rid IN (%n) or r.name like %s)";
            $param[]=$rids;
			$param[] = "%".$keyword."%";
        }
	}

	//网盘目录下我的文档
	$root = C::t('folder')->fetch_home_by_uid();
	$fids = get_all_chilrdenfid_by_pfid($root['fid']);
	$fids[] = $root['fid'];
	$fids = array_unique($fids);
	if ($fids) {
		$wheresql .= " and r.pfid in(%n)";
		$param[] = $fids;
	}


	$order = $_GET['order'] ? $_GET['order'] : 'edit';
	switch ($order) {
		case 'open':
			$ordersql = ' ORDER BY opendateline DESC';
			break;
		case 'name':
			$ordersql = ' ORDER BY name';
			break;
		case 'edit':
			$ordersql = ' ORDER BY r.dateline DESC';
			break;
		default:
			$ordersql = ' ORDER BY r.dateline DESC';
			break;
	}
	$urlarr = array(
					'keyword'		=>		$keyword,
					'order'			=>		$order
				);
	$total = DB::result_first("SELECT COUNT(*) FROM %t r LEFT JOIN %t s ON r.rid = s.rid LEFT JOIN %t f ON r.pfid = f.fid $wheresql", $param);
	$start = intval($_GET['start']) ? intval($_GET['start']) : 0;
	$pageSize = 24;
	$nextStart = $start + $pageSize;
	$next = $nextStart > $total ? false : true;
	$limitsql = ' LIMIT '.$start.','.$pageSize;
	$data=array();
	if($total){
		foreach(DB::fetch_all("SELECT r.rid,opendateline,editdateline FROM %t r LEFT JOIN %t s ON r.rid = s.rid LEFT JOIN %t f ON r.pfid = f.fid $wheresql $ordersql $limitsql", $param) as $value){
			if($arr=C::t('resources')->fetch_by_rid($value['rid'])){
				$arr['opendateline'] = $value['opendateline'] ? $value['opendateline'] : 0;
				$data[$arr['rid']]=$arr;
			}
		}
	}
	$turl = '&'.url_implode($urlarr);
	$showtype = $setting['showtype'];
	if($showtype == 'list'){
		$data_n == array('today' => array(), 'ytoday' => array(), 'nearly_week' => array(), 'nearly_month' => array(), 'other' => array());
		foreach ($data as $key => $value) {
			switch ($_GET['order']) {
				case 'name':
					$data_n['other'][] = $value;
					break;
				case 'open':
					$nearly = nearlytime($value['opendateline']);
					$data_n[$nearly][] = $value;
					break;
				default:
					$nearly = nearlytime($value['dateline']);
					$data_n[$nearly][] = $value;
					break;
			}
		}
		$data = $data_n;
	}
	include template('list');

} elseif ($do == 'getitem') {
	$rid = $_GET['rid'];
	$data = array();
	$res = DB::fetch_first("SELECT r.rid, opendateline, editdateline FROM %t r LEFT JOIN %t s ON r.rid = s.rid WHERE r.rid = %s", array('resources', 'resources_statis', $rid));
	if($res){
		if($arr=C::t('resources')->fetch_by_rid($res['rid'])){
			$arr['opendateline'] = $res['opendateline'] ? $res['opendateline'] : $arr['dateline'];
			$arr['editdateline'] = $res['editdateline'] ? $res['editdateline'] : $arr['dateline'];
			$data[$arr['rid']]=$arr;
		}
	}
	if($setting['showtype'] == 'list'){
		$data_n == array('today' => array(), 'ytoday' => array(), 'nearly_week' => array(), 'nearly_month' => array(), 'other' => array());
		foreach ($data as $key => $value) {
			switch ($_GET['order']) {
				case 'name':
					$data_n['other'][] = $value;
					break;
				case 'open':
					$nearly = nearlytime($value['opendateline']);
					$data_n[$nearly][] = $value;
					break;
				default:
					$nearly = nearlytime($value['editdateline']);
					$data_n[$nearly][] = $value;
					break;
			}
		}
		$data = $data_n;
	}
	include template('list');
} elseif ($do == 'uploads') {
	$container = $setting['savepath'];
    $space = dzzgetspace($uid);
    $space['self'] = intval($space['self']);
    $bz = trim($_GET['bz']);
    require_once dzz_libfile('class/UploadHandler');

    $sizeLimit = ($space['maxattachsize']);

    $options = array('accept_file_types' => '/\.(doc|docx)$/i',
        'max_file_size' => $sizeLimit ? $sizeLimit : null,
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
    );
    $upload_handler = new UploadHandler($options);
    exit();
} elseif ($do == 'addbytpl') {//通过模板创建
	$fid = $setting['savepath'];
	$tid = intval($_GET['tid']);
	$tpl = C::t('doc_template')->fetch_by_tid($tid);
	if ($tpl) {
		$bz = getBzByPath($fid);
		$attach=C::t('attachment')->fetch($tpl[$tid]['attach']);
		$content = file_get_contents(Io::getStream('attach::'.$attach['aid']));
		$perm = perm_check::checkperm_Container($fid, 'upload', $bz);
		$name = censor(trim($_GET['name']) ? trim($_GET['name']) : lang('new_word'));
		if ($perm) {
			$arr = IO::upload_by_content($content, $fid, $name.'.'.$attach['filetype']);
		} else {
			exit(json_encode(array('code' => 400, 'message' => lang('privilege'))));
		}
		if ($arr) {
			C::t('doc_template_records')->insert_by_tid($tid);
			exit(json_encode(array('code' => 200, 'dpath' => $arr['dpath'], 'rid' => $arr['rid'], 'message' => lang('opt_success'))));
		} else {
			exit(json_encode(array('code' => 400, 'message' => lang('opt_failed'))));
		}
	}
} elseif ($do == 'addopenrecord') {
	$rid = $_GET['rid'];
    $setarr = array(
        'opendateline'=>TIMESTAMP,
        'views'=>1,
        'uid'=>$uid
    );
    if(C::t('resources_statis')->add_statis_by_rid($rid,$setarr)){
        exit(json_encode(array('mgs'=>'success')));
    }else{
        exit(json_encode(array('mgs'=>'error')));
    }
} elseif ($do == 'rename') {
	
	$path=dzzdecode($_GET['path']);
    $text=censor(str_replace('...','',getstr(io_dzz::name_filter($_GET['text']),80)));
    $ret=IO::rename($path,$text);
    exit(json_encode($ret));

} elseif ($do == 'setting') {

	$appids = array_keys(DB::fetch_all('select appid from %t where ext IN(%n)', array('app_open', array('doc', 'docx')), 'appid'));
	$operation = $_GET['operation'];
	$tplhide = $_GET['tplhide'] ? 1 : 0;
	$createtype = $_GET['createtype'] ? 1 : 0;
	$savepath = $_GET['savepath'] ? $_GET['savepath'] : 0;
	$appid = in_array(intval($_GET['appid']), $appids) ? intval($_GET['appid']) : 0;
	if ($operation == 'setbase') {
		C::t('user_setting')->update(array('doc_word_tplhide' => $tplhide, 'doc_word_createtype' => $createtype, 'doc_word_savepath' => $savepath, 'doc_word_openappid' => $appid), $_G['uid']);
		exit(json_encode(array('code' => 200, 'message' => lang('save_set_cuccess'))));
	} elseif ($operation == 'switchshowtype') {//文档展示方式
		$showtype = $setting['showtype'];
		$showtype = $showtype == 'list' ? 'preview' : 'list';
		C::t('user_setting')->update_by_skey('doc_word_showtype', $showtype, $_G['uid']);
		exit(json_encode(array('code' => 200, 'type' => $showtype)));
	} else {
		$navtitle=lang('settings').' - '.lang('docs');
		$apps = C::t('app_market')->fetch_all_by_appid($appids);
		include template('setting');
	}

} elseif ($do == 'createnew') {
	$fid = $setting['savepath'];
	$bz = getBzByPath($fid);
	$name = censor(trim($_GET['name']) ? trim($_GET['name']) : lang('new_word'));
	$filename = $name . '.docx';
    if (!perm_check::checkperm_Container($setting['savepath'], 'upload', $bz)) {
        exit(json_encode(array('code' => 400, 'message' => lang('privilege'))));
    }
    $content = file_get_contents(DZZ_ROOT . './dzz/images/newfile/word.docx');
    if ($arr = IO::upload_by_content($content, $fid, $filename)) {
    	exit(json_encode(array('code' => 200, 'message' => lang('create_success'), 'rid' => $arr['rid'], 'dpath' => $arr['dpath'])));
    } else {
    	exit(json_encode(array('code' => 400, 'message' => lang('create_failed'))));
    }
} elseif ($do == 'deletedoc') {
	$arr=array();
    $names=array();
    $i=0;
    $icoids=$_GET['rids'];
    $ridarr = array();
    $bz= isset($_GET['bz']) ? trim($_GET['bz']):'';
    foreach($icoids as $icoid){
        $icoid=dzzdecode($icoid);
        if(empty($icoid)){
            continue;
        }
        if(strpos($icoid,'../')!==false){
            $arr['msg'][$return['rid']]=lang('illegal_calls');
        }else{
            $return=IO::Delete($icoid);
            if(!$return['error']){
                //处理数据
                $arr['sucessicoids'][$return['rid']]=$return['rid'];
                $arr['msg'][$return['rid']]='success';
                $ridarr[]= $return['rid'];
                $i++;
            }else{
                $arr['msg'][$return['rid']]=$return['error'];
            }
        }
    }
    //更新剪切板数据
    if(!empty($ridarr)){
        C::t('resources_clipboard')->update_data_by_delrid($ridarr);
    }
    echo json_encode($arr);
    exit();
} elseif ($do == 'showwindow') {

	$operation = $_GET['operation'];
	$id = $_GET['id'];
	include template('ajax');

}
?>
