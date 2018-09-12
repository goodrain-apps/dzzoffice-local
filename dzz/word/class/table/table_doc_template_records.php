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

class table_doc_template_records extends dzz_table
{
	public function __construct()
	{

		$this->_table = 'doc_template_records';
		$this->_pk    = 'rid';
		parent::__construct();
	}

	public function insert_by_tid($tid)
	{
		global $_G;
        $uid = $_G['uid'];
        if(!$uid) return array();
        return parent::insert(array('tid' => $tid, 'uid' => $uid, 'time' => TIMESTAMP), 1);
	}
	public function fetch_lately_records_by_uid()
	{
		global $_G;
        $uid = $_G['uid'];
        if(!$uid) return array();
        $tids = array();
        $data = DB::fetch_all('select r.tid tid,max(r.time) time from %t r left join %t t on t.tid = r.tid left join %t c on t.cat_id = c.cid where uid = %d and c.type = 1 group by r.tid order by r.time desc limit 6', array($this->_table, 'doc_template', 'doc_template_cat', $uid), 'time');
        krsort($data);
        foreach($data as $value){
        	$tids[] = $value['tid'];
        }
        if ((6 - count($tids)) > 0) {
        	$tids = array_merge($tids, array_keys(DB::fetch_all('select t.tid from %t t left join %t c on t.cat_id = c.cid where c.type = 1 and t.tid not in(%n) group by t.tid order by t.time desc limit '.(6 - count($tids)), array('doc_template', 'doc_template_cat', $tids), 'tid')));
        }
        return C::t('doc_template')->fetch_by_tid($tids);
	}

	public function delete_by_tid($tid)
	{
		parent::delete($this->_table, array('tid' => $tid));
	}

}

?>
