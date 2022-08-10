<?php
 if (!defined("ARTICLE_DAO_INC")) {
	define("ARTICLE_DAO_INC",1);
	
	class ArticleDao extends DbConnect /*DbMySQLConnect*/  {
		var $_id;
		var $_name;
		var $_cate;
		
		function __construct() {
			$cid = Generals::getVar('cid', array());
			$this->setState();
			$this->setId($cid[0]);
			parent::__construct();
		}
		
		function setId($id) {
			$this->_id	= $id;
		}
			
		function setName($name) {
			$this->_name = $name;
		}
			
		function setState() {
			if (!is_null(Generals::getVar('search'))) 	Generals::setState('filter.search', strtolower(trim(Generals::getVar('filter_search'))));
			if (!is_null(Generals::getVar('search')))	Generals::setState('filter.section', Generals::getVar('filter_section'));
			if (!is_null(Generals::getVar('search'))) 	Generals::setState('filter.published', Generals::getVar('filter_published'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
							'filter_section' => Generals::getState('filter.section')
						);
		}
		
		function getGenres() {
			return array(1 => Generals::getTitle('ARTICLE_GENRE_1'), 2 => Generals::getTitle('ARTICLE_GENRE_2'), 3 => Generals::getTitle('ARTICLE_GENRE_3'), 4 => Generals::getTitle('ARTICLE_GENRE_4'), 5 => Generals::getTitle('ARTICLE_GENRE_5'));
		}
		
		function getImages() {
		 	$query = ' SELECT * FROM tbl_article_image WHERE article = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return isset($result) ? $result : array();
		}
		
		function getSectionInId($ids = 0, $language = 'vn'){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_section AS a ';
			$query.= ' LEFT JOIN tbl_section_lang AS fl ON a.id = fl.section';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id IN ('.$ids.') AND fl.language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($language));
			
			return isset($result) ? $result : array();
		}
		
		function getArticleInId($ids = 0, $language = 'vn'){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_article AS a ';
			$query.= ' LEFT JOIN tbl_article_lang AS fl ON a.id = fl.article';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id IN ('.$ids.') AND fl.language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($language));
			
			return isset($result) ? $result : array();
		}
		
		function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_article_lang WHERE article = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_article AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_article AS a ';
			$query.= ' INNER JOIN tbl_article_lang AS b ON a.id = b.article WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext, fl.shorttext, fl.tags, fl.meta FROM tbl_article AS a ';
			$query.= ' LEFT JOIN tbl_article_lang AS fl ON a.id = fl.article';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id = '.(int)$id;
			$this->prepare($query);
			$result = $this->exec($params);
			
			return isset($result[0]) ? $result[0] : array();
		}

		function _buildQuery(){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$section 	= Generals::getState('filter.section');
			$published 	= Generals::getState('filter.published');
			$language	= Generals::getSession('langcode');
			$params 	= array($language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' fl.language = ? ';
			if (strlen($search)):
				$where[] = ' LOWER(fl.name) LIKE ? ';
				array_push($params, '%'.strtolower($search).'%');
			endif;
			if (strlen($this->_name)):
				$where[] = ' LOWER(fl.name) LIKE ? ';
				array_push($params, '%'.strtolower($this->_name).'%');
			endif;
			if (strlen($section)):
				$where[] = ' a.sections LIKE "%,'.$section.',%" ';
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			
			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext, fl.shorttext, fl.tags, fl.meta FROM tbl_article AS a ';
			$query.= ' LEFT JOIN tbl_article_lang AS fl ON a.id = fl.article';
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
				$this->prepare(' UPDATE tbl_article SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function feature($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_article SET feature = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_article SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$this->prepare(' SELECT * FROM tbl_article_image WHERE article = ? ');
			$images = $this->exec(array($oid));
			
			$result = $this->getDataFull($oid);
			$this->delete_db('tbl_article', " id = ? ", array($oid));
			$this->delete_db('tbl_article_lang', " article = ? ", array($oid));
			$this->delete_db('tbl_article_image', " article = ? ", array($oid));
			
			foreach ($images as $image):
				$this->delete_db('tbl_article_image_lang', " image = ? ", array($image['id']));
				JFile::delete(IMG_PATH.DS."article".DS."multiple".DS.$image['icon']);
			endforeach;
			
			JFile::delete(IMG_PATH.DS."article".DS.$result['image']);
			JFile::delete(IMG_PATH.DS."article".DS.'resize'.DS.str_replace('image', 'thumb', $result['image']));
		}
	}// end class
 }
?>