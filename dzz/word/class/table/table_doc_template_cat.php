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

class table_doc_template_cat extends dzz_table
{
	public function __construct()
	{

		$this->_table = 'doc_template_cat';
		$this->_pk    = 'cid';
		parent::__construct();
	}

	public function insert($setarr)
	{
		return parent::insert($setarr, 1);
	}

	public function fetch_all()
	{
		return DB::fetch_all('select * from %t where type = 1 order by time desc', array($this->_table), 'cid');
	}
	
}

?>
