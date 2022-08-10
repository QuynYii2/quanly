<?php
 if (!defined("CATEGORY_DAO_INC")) {

	define("CATEGORY_DAO_INC",1);
	
	class CategoryDao extends DbConnect /*DbMySQLConnect*/  {
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
			if (!is_null(Generals::getVar('page'))) Generals::setState('page', Generals::getVar('page'));
		}
		
		function getState() {
			return array('filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'));
		}
		
		function getPosition() {
			return array(1 => "Menu Top", 2 => "Home Page", 3 => "Right Column", 4 => "Custom");
		}
		
		function getLevel($id) {
			$query = 'SELECT a.level FROM tbl_category AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($id));
			
			return (int)$result[0]['level']+1;
		}
		
		function getCategoryLang($language = null, $category = null) {
			$category = $category ? $category : $this->_id;
			$query = ' SELECT * FROM tbl_category_lang WHERE category = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($category, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_category AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			# build the html select list for ordering
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_category AS a ';
			$query.= ' INNER JOIN tbl_category_lang AS b ON a.id = b.category WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		/**
		 * @note:	Thuc hien lay danh sach cac categories va phan cap
		 * @param	
		 * @version 1.0
		 */
		private $total = null;
		private $dhtml = null;	# Bien ghi lai cac categories duoc lay ra
		private $_text = "---";	# Bien xac dinh cac categories con va cha
		private $__key = 0;		# Bien dem so luong cac categories duoc lay ra
	
		function setInsertText($text) {
			$this->_text = $text;
		}
		
		function getCategoryById($id){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext, fl.tags, fl.meta FROM tbl_category AS a ';
			$query.= ' LEFT JOIN tbl_category_lang AS fl ON a.id = fl.category';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id = '.(int)$id;
			$this->prepare($query);
			$result = $this->exec($params);
			
			return isset($result[0]) ? $result[0] : array();
		}
		
		function getCategoryByParentId($parent = 0){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$published 	= Generals::getState('filter.published');
			$language	= Generals::getSession('langcode');
			$params 	= array($language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' fl.language = ? ';
			if (strlen($search)):
				$where[] = ' LOWER(fl.name) LIKE ? ';
				array_push($params, '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			if (strlen($parent)):
				$where[] = ' a.parent = ? ';
				array_push($params, $parent);
			endif;
			if (strlen($this->_id)):
				$where[] = ' a.id <> ? ';
				array_push($params, $this->_id);
			endif;
			
			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext, fl.tags, fl.meta FROM tbl_category AS a ';
			$query.= ' LEFT JOIN tbl_category_lang AS fl ON a.id = fl.category';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language '.$where;
			$query.= ' ORDER BY a.ordering ASC ';
			
			$this->prepare($query);
			$result = $this->exec($params);
			
			return $result;
		}
	
		function getCategories($parentid = 0, $_level = null, $insert_text = null) {
			$_result = $this->getCategoryByParentId((int)$parentid);
			if (!empty($_result)) :
				foreach ($_result as $items) :
					if (empty($_level) || $items['level'] <= $_level) :
						$items['name'] = $insert_text.' '.$items['name'];
						$this->dhtml[$this->__key]	= $items;
						$this->__key++;
						if ($items['id']) :
							$this->getCategories($items['id'], $_level, $insert_text.$this->_text);
						endif;
					endif;
				endforeach;
			endif;
		}
	
		function getDataList($parentid = 0, $_level = null, $insert_text = null) {
			$this->dhtml = null;
			if ($parentid) $result = $this->getCategoryById((int)$parentid);
			if (!empty($result)) :
				$this->dhtml[$this->__key]			= $result;
				$this->dhtml[$this->__key]['name'] 	= $insert_text.' '.$result['name'];
				$this->getCategories($parentid, $_level, $insert_text.$this->_text);
			else :
				$this->getCategories($parentid, $_level, $insert_text);
			endif;
			
			$this->total = sizeof($this->dhtml);
			
			
			while (true):
				$offset = (int)(Generals::getState('page', 1)-1)*LIMIT_RECORD;
				if ($offset >= $this->total) {
					$offset	= (int)$offset - (int)LIMIT_RECORD;
					Generals::setState('page', Generals::getState('page', 1)-1);
				} else if ($offset < 0) {
					$offset = 0;
					break;
				} else {
					break;
				}
			endwhile;
			
			if (is_array($this->dhtml)):
				$result = array_chunk($this->dhtml, LIMIT_RECORD);
				$page	= Generals::getState('page', 1)-1 >= 0 ? Generals::getState('page', 1)-1 : 0;
			else:
				$page = 0;
				$result[0] = $this->dhtml;
			endif;
	
			$this->_data = $result[$page];
			
			return $this->_data;
		}
		
		function getParentList($parentid = 0, $_level = null, $insert_text = null) {
			$this->dhtml = null;
			
			if ($parentid) $result = $this->getCategoryById((int)$parentid);
			if (!empty($result)) :
				$this->dhtml[$this->__key]			= $result;
				$this->dhtml[$this->__key]['name'] 	= $insert_text.' '.$result['name'];
				$this->getCategories($parentid, $_level, $insert_text.$this->_text);
			else :
				$this->getCategories($parentid, $_level, $insert_text);
			endif;
			
			return $this->dhtml;
		}
		
		function getCountData() {
			return $this->total;
		}
		
		function published($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_category SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_category SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getCategoryById($oid);
			$this->delete_db('tbl_category', " id = ? ", array($oid));
			$this->delete_db('tbl_category_lang', " category = ? ", array($oid));
			
			JFile::delete(IMG_PATH.DS."category".DS.$result['image']);
			JFile::delete(IMG_PATH.DS."category".DS.'resize'.DS.str_replace('image', 'thumb', $result['image']));
		}
	}// end class		
 }
?>