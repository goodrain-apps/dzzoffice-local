<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
if($operation == 'filelist'){
    $limit=isset($_GET['perpage'])?intval($_GET['perpage']):20;//默认每页条数
    $page = empty($_GET['page'])?1:intval($_GET['page']);//页码数
    $start = ($page-1)*$perpage;//开始条数
    $limitsql = "limit $start,$limit";
    $disp = isset($_GET['disp']) ? intval($_GET['disp']):4;

    $keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';

    $asc = isset($_GET['asc'])?intval($_GET['asc']):1;

    $order = $asc > 0 ? 'ASC' : "DESC";

    switch ($disp) {
        case 0:
            $orderby = 'filename';
            break;
        case 1:
            $orderby = 'size';
            break;
        case 2:
            $orderby = '';
            break;
        case 3:
            $orderby = 'dateline';
            break;

    }
    $ordersql='';
    if(is_array($orderby)){
        foreach($orderby as $key=>$value){
            $orderby[$key]=$value.' '.$order;
        }
        $ordersql=' ORDER BY '.implode(',',$orderby);
    }elseif($orderby){
        $ordersql=' ORDER BY '.$orderby.' '.$order;
    }
    $collects = C::t('resources_collect')->fetch_by_uid($limitsql,$ordersql);
    $explorer_setting = get_resources_some_setting();
    $data=array();
    $folderids=$folderdata=array();
    foreach($collects as $v){
        $val = C::t('resources')->fetch_by_rid($v['rid']);
        if(!$explorer_setting['useronperm'] && $val['gid'] == 0){
            continue;
        }
        if(!$explorer_setting['grouponperm'] && $val['gid'] > 0){
            if(DB::result_first("select `type` from %t where orgid = %d",array('organization',$val['gid'])) == 1){
                continue;
            }
        }
        if(!$explorer_setting['orgonperm'] && $val['gid'] > 0){
            if(DB::result_first("select `type` from %t where orgid = %d",array('organization',$val['gid'])) == 0){
                continue;
            }
        }
        $folderids[$val['pfid']]=$val['pfid'];
        if($val['type']=='folder') $folderids[$val['oid']]=$val['oid'];
        if($val['isdelete'] < 1){
            if ($val['type'] == 'folder') {
                $val['filenum'] = $val['contaions']['contain'][0];
                $val['foldernum'] = $val['contaions']['contain'][1];
            } else {
                $val['monthdate'] = dgmdate($val['dateline'], 'm-d');
                $val['hourdate'] = dgmdate($val['dateline'], 'H:i');
            }
            if ($val['type'] == 'image') {
                $val['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $val['aid']);
                $val['imgpath'] =  DZZSCRIPT.'?mod=io&op=thumbnail&path=' .dzzencode('attach::' . $val['aid']);
            }
            $val['name'] = addslashes($val['name']);
            $data[$val['rid']]=$val;
        }

    }
    //获取目录信息
    foreach($folderids as $fid){
        if($folder = C::t('folder')->fetch_by_fid($fid)) $folderdata[$fid] =$folder;
    }

    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : 0;//文件排序
    $iconview=isset($_GET['iconview']) ? intval($_GET['iconview']):4;//排列方式
    $next = false;
    if(count($data) >= $perpage){
        $next = $page + 1;
    }
    $folderjson = json_encode($folderdata);
    //返回数据
    $return=array(
        'data'=>($data) ? $data:array(),
        'param'=>array(
            'disp'=>$disp,
            'view'=>$iconview,
            'bz'=>$bz,
            'datatotal'=>$total,
            'asc'=>$asc,
            'page' => $next,
            'keyword'=>$keyword,
            'localsearch'=>$bz?1:0
        )
    );
    $return = json_encode($return);
    include template('mobile/filelist');
}elseif($operation == 'canclecollect'){//取消收藏
    $rids = isset($_GET['rids'])?$_GET['rids']:'';
    $return = C::t('resources_collect')->delete_usercollect_by_rid($rids);
    exit(json_encode($return));
}else{
    include template('mobile/collect');
}