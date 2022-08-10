<?php
 if (!defined("ATTRIBUTE_DAO_INC")) {
	define("ATTRIBUTE_DAO_INC",1);
	
	class AttributeDao extends DbConnect /*DbMySQLConnect*/  {
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
			if (!is_null(Generals::getVar('filter_custom'))) 	Generals::setState('filter.custom', strtolower(trim(Generals::getVar('filter_custom'))));
			if (!is_null(Generals::getVar('filter_published'))) Generals::setState('filter.published', Generals::getVar('filter_published'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
							'filter_custom' => Generals::getState('filter.custom')
						);
		}
		
		function getCustoms(){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_custom AS a ';
			$query.= ' LEFT JOIN tbl_custom_lang AS fl ON a.id = fl.custom';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE fl.language = ? AND (a.genre = ? OR a.genre = ? OR a.genre = ? OR a.genre = ? OR a.genre = ?) ORDER BY a.id ASC ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode'), 'checkbox', 'selectbox', 'radiobox', 'colorimg', 'relative'));
			
			return isset($result) ? $result : array();
		}
		
		function getRelates($custom) {
		 	$query = ' SELECT a.id, a.code, fl.name FROM tbl_attribute AS a ';
			$query.= ' LEFT JOIN tbl_attribute_lang AS fl ON a.id = fl.attribute';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE fl.language = ? AND a.custom = '.(int)$custom;
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return is_array($result) ? $result : array();
		}
	
		function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_attribute_lang WHERE attribute = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_attribute AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_attribute AS a ';
			$query.= ' INNER JOIN tbl_attribute_lang AS b ON a.id = b.attribute WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_attribute AS a ';
			$query.= ' LEFT JOIN tbl_attribute_lang AS fl ON a.id = fl.attribute';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id = '.(int)$id;
			$this->prepare($query);
			$result = $this->exec($params);
			
			return isset($result[0]) ? $result[0] : array();
		}

		function _buildQuery(){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$published 	= Generals::getState('filter.published');
			$custom 	= Generals::getState('filter.custom');
			$language	= Generals::getSession('langcode');
			$params 	= array($language, $language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' fl.language = ? ';
			$where[] = ' cl.language = ? ';
			if (strlen($search)):
				$where[] = ' LOWER(fl.name) LIKE ? ';
				array_push($params, '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			if (strlen($custom)):
				$where[] = ' a.custom = ? ';
				array_push($params, $custom);
			endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext, cl.name AS custom_name FROM tbl_attribute AS a ';
			$query.= ' LEFT JOIN tbl_attribute_lang AS fl ON a.id = fl.attribute';
			$query.= ' LEFT JOIN tbl_custom_lang AS cl ON a.custom = cl.custom';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language '.$where;
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
		
		function published($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_attribute SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function search($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_attribute SET search = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_attribute SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$this->delete_db('tbl_attribute', " id = ? ", array($oid));
			$this->delete_db('tbl_attribute_lang', " attribute = ? ", array($oid));
		}
	}// end class		
 }
?>