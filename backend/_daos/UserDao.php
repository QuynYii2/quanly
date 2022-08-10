<?php
 if (!defined("USER_DAO_INC")) {
	define("USER_DAO_INC",1);
	
	class UserDao extends DbConnect /*DbMySQLConnect*/  {
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
		
		function getGroupData() {
            $query = ' SELECT a.id, a.name FROM tbl_usergroup AS a ';
            $query.= ' WHERE a.published = 1 ORDER BY ordering ';
            $this->prepare($query);

            $result = $this->exec(array());

            return is_array($result) ? $result : array();
		}
		
		function getLoginData($username, $password) {
		 	$query = ' SELECT a.*, b.name, b.alias, b.introtext, g.name AS group_name,';
            $query.= ' g.mod_create, g.mod_update, g.mod_delete, g.mod_detail FROM tbl_user AS a ';
			$query.= ' LEFT JOIN tbl_user_lang AS b ON a.id = b.user';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' LEFT JOIN tbl_usergroup AS g ON a.groupid = g.id';
			$query.= ' WHERE TRIM(LOWER(a.username)) = ? AND a.password = ? AND a.published = 1 ';
			$this->prepare($query);
			
			$result = $this->exec(array(trim(strtolower($username)), md5($password)));
			
			return isset($result[0]) ? $result[0] : array();
		}
		
		function getDataByEmail($email, $void) {
			$query = 'SELECT a.* FROM tbl_user AS a WHERE TRIM(LOWER(a.email)) = ? AND a.id <> ? ';
			$this->prepare($query);
			$result = $this->exec(array(trim(strtolower($email)), (int)$void));
			
			return $result[0];
		}
		
		function getDataByUser($username, $void) {
			$query = 'SELECT a.* FROM tbl_user AS a WHERE TRIM(LOWER(a.username)) = ? AND a.id <> ? ';
			$this->prepare($query);
			$result = $this->exec(array(trim(strtolower($username)), (int)$void));
			
			return $result[0];
		}
		
		function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_user_lang WHERE user = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_user AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_user AS a ';
			$query.= ' INNER JOIN tbl_user_lang AS b ON a.id = b.user WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, b.name, b.alias, b.introtext FROM tbl_user AS a ';
			$query.= ' LEFT JOIN tbl_user_lang AS b ON a.id = b.user';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
			$query.= ' WHERE a.id = '.(int)$id;
			$this->prepare($query);
			$result = $this->exec(array());
			
			return isset($result[0]) ? $result[0] : array();
		}

		function _buildQuery(){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$published 	= Generals::getState('filter.published');
			$language	= Generals::getSession('langcode');
			$params 	= array($language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' b.language = ? ';
			if (strlen($search)):
				$where[] = ' LOWER(b.name) LIKE ? ';
				array_push($params, '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			
			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name, b.alias, b.introtext, g.name AS g_name FROM tbl_user AS a ';
			$query.= ' LEFT JOIN tbl_user_lang AS b ON a.id = b.user';
            $query.= ' LEFT JOIN tbl_usergroup AS g ON a.groupid = g.id';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language '.$where;
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
				$this->prepare(' UPDATE tbl_user SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_user SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getDataFull($oid);
			$this->delete_db('tbl_user', " id = ? ", array($oid));
			$this->delete_db('tbl_user_lang', " user = ? ", array($oid));
			
			JFile::delete(IMG_PATH.DS."user".DS.$result['image']);
			JFile::delete(IMG_PATH.DS."user".DS.'resize'.DS.str_replace('image', 'thumb', $result['image']));
		}
	}// end class		
 }
?>