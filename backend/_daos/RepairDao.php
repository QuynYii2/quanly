<?php
 if (!defined("REPAIR_DAO_INC")) {
	define("REPAIR_DAO_INC",1);
	
	class RepairDao extends DbConnect /*DbMySQLConnect*/  {
		var $_id;
		
		function __construct() {
			$cid = Generals::getVar('cid', array());
			$this->setState();
			$this->setId($cid[0]);
			parent::__construct();
		}
		
		function setId($id) {
			$this->_id	= $id;
		}
			
		function setState() {
			if (!is_null(Generals::getVar('filter_search'))) 	Generals::setState('filter.search', strtolower(trim(Generals::getVar('filter_search'))));
			if (!is_null(Generals::getVar('filter_published'))) Generals::setState('filter.published', Generals::getVar('filter_published'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field')
						);
		}
		
		function getComfortsName($inid) {
			$inids = explode(',', $inid);
			foreach ($inids as $key => $val) if (empty($val)) unset($inids[$key]);
			$query = ' SELECT b.name, a.* FROM tbl_comfort AS a ';
			$query.= ' INNER JOIN tbl_comfort_lang AS b ON a.id = b.comfort ';
			$query.= ' WHERE a.id IN ('.implode(',', $inids).') AND b.language = ? ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			if (is_array($result)) foreach ($result as $key => $val):
				if ($val['percent']) $saleoff = $val['discount'];
				else $saleoff = $val['price']*$val['discount']/100;
				
				$return[] = $val['name'].': '.number_format($val['price'] - $saleoff, 0, ',', '.').'VNĐ';
			endforeach;
			
			return $return;
		}
		
		function getData($void = null) {
			$void	= $void ? $void : $this->_id;
			$query = ' SELECT a.*, b.name AS city_name FROM tbl_repair AS a ';
			$query.= ' LEFT JOIN tbl_city AS b ON a.city = b.id WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, a.name AS text FROM tbl_repair AS a ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array());
			
			return $result;
		}
		
		function _buildQuery(){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$published 	= Generals::getState('filter.published');
			$params 	= array();
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where	= array();
			if (strlen($search)):
				$where[] = ' (LOWER(a.person) LIKE ? OR LOWER(a.title) LIKE ?) ';
				array_push($params, '%'.strtolower($search).'%', '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			
			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name AS city_name FROM tbl_repair AS a ';
			$query.= ' LEFT JOIN tbl_city AS b ON a.city = b.id '.$where;
			Generals::setState('params', $params);
			
			return $query;
		}
		
		function getDataList() {
			$field = Generals::getState('orderby.field', 'a.id') ? Generals::getState('orderby.field', 'a.id') : 'a.id';
			$order = Generals::getState('orderby.order', 'DESC') ? Generals::getState('orderby.order', 'DESC') : 'DESC';
			$query = $this->_buildQuery();
			$query.= ' ORDER BY '.$field.' '.$order;
			$this->prepare($query);
			$result = $this->exec(Generals::getState('params'));
			$this->total = sizeof($result);
			
			while (true):
				$offset = (int)(Generals::getState('page', 1)-1)*LIMIT_RECORD;
				if ($offset >= $this->total) {
					$offset	= (int)$offset - (int)LIMIT_RECORD;
					Generals::setState('page', Generals::getState('page', 1)-1);
				} elseif ($offset < 0) {
					$offset = 0;
					break;
				} else {
					break;
				}
			endwhile;
			
			if (is_array($result)):
				$result = array_chunk($result, LIMIT_RECORD);
				$page	= Generals::getState('page', 1)-1 >= 0 ? Generals::getState('page', 1)-1 : 0;
			else:
				$page = 0;
				$result[0] = $result;
			endif;
	
			return $result[$page];
		}
		
		function getCountData() {
			return $this->total;
		}
		
		function frontend($val, $keys) {
			if ($val):
				$this->prepare(' UPDATE tbl_repair SET frontend = 0 ');
				$this->exec(array());
			endif;
			
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_repair SET frontend = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function published($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_repair SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_repair SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getData($oid);
			$this->delete_db('tbl_repair', " id = ? ", array($oid));
			JFile::delete(IMG_PATH.DS."user".DS.$result['image']);
		}
	}// end class		
 }
?>