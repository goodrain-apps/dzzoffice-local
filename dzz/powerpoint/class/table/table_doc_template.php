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

class table_doc_template extends dzz_table
{
	public function __construct()
	{

		$this->_table = 'doc_template';
		$this->_pk    = 'tid';
		parent::__construct();
	}

	public function fetch_all()
	{
		$data = DB::fetch_all('select t.* from %t t left join %t c on t.cat_id = c.cid where c.type = 3 group by t.tid order by t.time', array($this->_table, 'doc_template_cat'), 'tid');
		foreach ($data as $key => $value) {
			if ($value['cover_aid']) {
				$data[$key]['img'] = C::t('attachment')->getThumbByAid($value['cover_aid'],178,142);
			} else {
				$attach = C::t('attachment')->fetch($value['attach']);
				$data[$key]['img'] = geticonfromext($attach['filetype'],'document');
			}
			$data[$key]['path'] = dzzencode('attach::'.$value['attach']);
		}
		return $data;
	}

	public function fetch_by_tid($tid)
	{//可以是单个或者数组
		$tids = is_array($tid) ? $tid : array($tid);
		$data = DB::fetch_all('select * from %t where tid IN (%n) order by field(tid, %n)', array($this->_table, $tids, $tids), 'tid');
		foreach ($data as $key => $value) {
			if ($value['cover_aid']) {
				$data[$key]['img'] = C::t('attachment')->getThumbByAid($value['cover_aid'],178,142);
			} else {
				$attach = C::t('attachment')->fetch($value['attach']);
				$data[$key]['img'] = geticonfromext($attach['filetype'],'document');
			}
			$data[$key]['path'] = dzzencode('attach::'.$value['attach']);
		}
		return $data;
	}

	public function rename($tid, $name)
	{
		if(DB::update('doc_template', array('title' => $name), array('tid' => $tid))){
			return true;
		} else {
			return false;
		}
	}
	public function fetch_by_cid($cid)
	{
		return DB::fetch_all('select * from %t where cat_id = %d', array($this->_table, $cid), 'tid');
	}
	
	public function delete_by_tid($tid)
	{
		$tpl = parent::fetch($tid);
		if(parent::delete($tid)){
			C::t('attachment')->delete_by_aid($tpl['attach']);
			C::t('attachment')->delete_by_aid($tpl['cover_aid']);
			return true;
		} else {
			return false;
		}
	}
}

?>
