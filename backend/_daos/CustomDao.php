<?php
 if (!defined("CUSTOM_DAO_INC")) {
	define("CUSTOM_DAO_INC",1);
	
	class CustomDao extends DbConnect /*DbMySQLConnect*/  {
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
			if (!is_null(Generals::getVar('filter_genre'))) 	Generals::setState('filter.genre', strtolower(trim(Generals::getVar('filter_genre'))));
			if (!is_null(Generals::getVar('filter_published'))) Generals::setState('filter.published', Generals::getVar('filter_published'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
							'filter_genre' 	=> Generals::getState('filter.genre')
						);
		}
		
		function getGenres(){
			//return array('checkbox' => Generals::getTitle('CUSTOM_GENRE_0'), 'selectbox' => Generals::getTitle('CUSTOM_GENRE_1'), 'radiobox' => Generals::getTitle('CUSTOM_GENRE_2'), 'link' => Generals::getTitle('CUSTOM_GENRE_3'), 'textbox' => Generals::getTitle('CUSTOM_GENRE_4'), 'areabox' => Generals::getTitle('CUSTOM_GENRE_5'), 'editor' => Generals::getTitle('CUSTOM_GENRE_6'), 'between' => Generals::getTitle('CUSTOM_GENRE_7'), 'datetime' => Generals::getTitle('CUSTOM_GENRE_8'), 'separator' => Generals::getTitle('CUSTOM_GENRE_9'), 'colorimg' => Generals::getTitle('CUSTOM_GENRE_10'), 'country' => Generals::getTitle('CUSTOM_GENRE_11'), 'city' => Generals::getTitle('CUSTOM_GENRE_12'), 'state' => Generals::getTitle('CUSTOM_GENRE_13'));
			//return array('checkbox' => Generals::getTitle('CUSTOM_GENRE_0'), 'selectbox' => Generals::getTitle('CUSTOM_GENRE_1'), 'radiobox' => Generals::getTitle('CUSTOM_GENRE_2'), 'textbox' => Generals::getTitle('CUSTOM_GENRE_4'), 'areabox' => Generals::getTitle('CUSTOM_GENRE_5'), 'datetime' => Generals::getTitle('CUSTOM_GENRE_8'), 'colorimg' => Generals::getTitle('CUSTOM_GENRE_10'));
			return array('checkbox' => Generals::getTitle('CUSTOM_GENRE_0'), 'selectbox' => Generals::getTitle('CUSTOM_GENRE_1'), 'radiobox' => Generals::getTitle('CUSTOM_GENRE_2'), 'textbox' => Generals::getTitle('CUSTOM_GENRE_4'), 'areabox' => Generals::getTitle('CUSTOM_GENRE_5'), 'city' => Generals::getTitle('CUSTOM_GENRE_12'), 'relative' => Generals::getTitle('CUSTOM_GENRE_14'));
		}
		
		function getValidates(){
			return array('required' => Generals::getTitle('CUSTOM_VALIDATE_0'), 'email' => Generals::getTitle('CUSTOM_VALIDATE_1'), 'url' => Generals::getTitle('CUSTOM_VALIDATE_2'), 'date' => Generals::getTitle('CUSTOM_VALIDATE_3'), 'dateISO' => Generals::getTitle('CUSTOM_VALIDATE_4'), 'number' => Generals::getTitle('CUSTOM_VALIDATE_5'), 'digits' => Generals::getTitle('CUSTOM_VALIDATE_6'), 'creditcard' => Generals::getTitle('CUSTOM_VALIDATE_7'));
		}
		
		function getCgroupList(){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_cgroup AS a ';
			$query.= ' LEFT JOIN tbl_cgroup_lang AS fl ON a.id = fl.cgroup';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE fl.language = ? ORDER BY a.ordering ASC ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result ? $result : array();
		}
		
		function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_custom_lang WHERE custom = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_custom AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_custom AS a ';
			$query.= ' INNER JOIN tbl_custom_lang AS b ON a.id = b.custom WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_custom AS a ';
			$query.= ' LEFT JOIN tbl_custom_lang AS fl ON a.id = fl.custom';
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
			$genre 		= Generals::getState('filter.genre');
			$language	= Generals::getSession('langcode');
			$params 	= array($language, $language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' fl.language = ? ';
			$where[] = ' cg.language = ? ';
			if (strlen($search)):
				$where[] = ' LOWER(fl.name) LIKE ? ';
				array_push($params, '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			if (strlen($genre)):
				$where[] = ' a.genre = ? ';
				array_push($params, $genre);
			endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext, cg.name AS group_name FROM tbl_custom AS a ';
			$query.= ' LEFT JOIN tbl_custom_lang AS fl ON a.id = fl.custom';
			$query.= ' LEFT JOIN tbl_cgroup_lang AS cg ON a.cgroup = cg.cgroup';
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
				$this->prepare(' UPDATE tbl_custom SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function search($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_custom SET search = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_custom SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$this->delete_db('tbl_custom', " id = ? ", array($oid));
			$this->delete_db('tbl_custom_lang', " custom = ? ", array($oid));
		}
	}// end class		
 }
?>