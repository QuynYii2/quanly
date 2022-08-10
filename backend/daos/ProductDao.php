<?php
 if (!defined("PRODUCT_DAO_INC")) {
	define("PRODUCT_DAO_INC",1);
	
	class ProductDao extends DbConnect /*DbMySQLConnect*/  {
		var $_id;
		var $_group;
		var $_custom;
		
		function __construct() {
			$cid = Generals::getVar('cid', array());
			$this->setState();
			$this->setId($cid[0]);
			parent::__construct();
		}
		
		function setId($id) {
			$this->_id	= $id;
		}
			
		function setGroup($group) {
			$this->_group = $group;
		}
			
		function setCustom($custom) {
			$this->_custom = $custom;
		}
		
		function setState() {
			if (!is_null(Generals::getVar('filter_search'))) 	Generals::setState('filter.search', strtolower(trim(Generals::getVar('filter_search'))));
			if (!is_null(Generals::getVar('filter_custom'))) 	Generals::setState('filter.custom', Generals::getVar('filter_custom'));
			if (!is_null(Generals::getVar('filter_category'))) 	Generals::setState('filter.category', strtolower(trim(Generals::getVar('filter_category'))));
			if (!is_null(Generals::getVar('filter_grouppro'))) 	Generals::setState('filter.grouppro', strtolower(trim(Generals::getVar('filter_grouppro'))));
			if (!is_null(Generals::getVar('filter_published'))) Generals::setState('filter.published', Generals::getVar('filter_published'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
							'filter_category' => Generals::getState('filter.category'), 'filter_grouppro' => Generals::getState('filter.grouppro'),
							'filter_custom' => Generals::getState('filter.custom')
						);
		}
		
		function getGaras() {
			$query = ' SELECT a.id, b.name FROM tbl_gara AS a ';
			$query.= ' INNER JOIN tbl_gara_lang AS b ON a.id = b.gara ';
			$query.= ' INNER JOIN tbl_language AS l ON l.code = b.language ';
			$query.= ' WHERE b.language = ? ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getCountryName($id) {
			$query = 'SELECT a.name FROM tbl_country AS a WHERE a.id = ? ORDER BY name ASC ';
			$this->prepare($query);
			$result = $this->exec(array($id));
			
			return $result[0]['name'];
		}
		
		function getCityName($id = null) {
			$query = ' SELECT a.name FROM tbl_city AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($id));
			
			return $result[0]['name'];
		}
		
		function getStateName($id = null) {
			$query = ' SELECT a.name FROM tbl_state AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($id));
			
			return $result[0]['name'];
		}
		
		function getCurrencies(){
			$query = ' SELECT id AS value, CONCAT_WS("", CONCAT_WS(" (", code, symbol), ")") AS text FROM tbl_currencies WHERE published = 1 ORDER BY name ASC ';
			$this->prepare($query);
			$result = $this->exec(array());
			
			return isset($result) ? $result : array();
		}
		
		function getImages() {
		 	$query = ' SELECT * FROM tbl_product_image WHERE product = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return isset($result) ? $result : array();
		}
		
		function getProCustomID($product, $custom, $value) {
			if (is_array($value)):
				$param[] = Generals::getSession('langcode');
				$param[] = $product;
				$param[] = $custom;
				foreach ($value as $val):
					$where[] = 'value LIKE ?';
					$param[] = '%,'.$val.',%';
				endforeach;
				$query = ' SELECT id FROM tbl_product_custom WHERE language = ? AND product = ? AND custom = ? AND ('.implode(' OR ', $where).') ';
				$this->prepare($query);
				$result = $this->exec($param);
			else:
				$query = ' SELECT id FROM tbl_product_custom WHERE language = ? AND product = ? AND custom = ? AND value = ? ';
				$this->prepare($query);
				$result = $this->exec(array(Generals::getSession('langcode'), $product, $custom, $value));
			endif;

			return (int)$result[0]['id'];
		}
		
		function getProductCustom($pid, $cid, $lang) {
			$query = ' SELECT a.*, b.name, c.value FROM tbl_custom AS a ';
			$query.= ' INNER JOIN tbl_custom_lang AS b ON a.id = b.custom';
			$query.= ' INNER JOIN tbl_product_custom AS c ON c.custom = a.id ';
			$query.= ' INNER JOIN tbl_language AS l ON l.code = b.language ';
			$query.= ' WHERE b.language = ? AND c.language = ? AND c.product = ? AND a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($lang, $lang, $pid, $cid));
			
			return isset($result[0]) ? $result[0] : array();
		}
		
		function getCustoms(){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_custom AS a ';
			$query.= ' LEFT JOIN tbl_custom_lang AS fl ON a.id = fl.custom';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE fl.language = ? AND a.published = 1 ORDER BY a.id ASC ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return isset($result) ? $result : array();
		}
		
		function getChilds($relative, $lang) {
			$query = ' SELECT a.id FROM tbl_custom AS a ';
			$query.= ' LEFT JOIN tbl_custom_lang AS fl ON a.id = fl.custom ';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE fl.language = ? AND a.relative = ? ORDER BY a.id ASC ';
			$this->prepare($query);
			$result = $this->exec(array($lang, $relative));
			
			return is_array($result) ? $result : array();
		}
		
		function getCustom($id, $lang) {
			$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_custom AS a ';
			$query.= ' LEFT JOIN tbl_custom_lang AS fl ON a.id = fl.custom';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE fl.language = ? AND a.id = ? ORDER BY a.id ASC ';
			$this->prepare($query);
			$result = $this->exec(array($lang, $id));
				
			return isset($result[0]) ? $result[0] : array();
		}
		
		function getAttribute($custom, $lang, $relate = null){
			$param = array($lang, $custom);
			$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_attribute AS a ';
			$query.= ' LEFT JOIN tbl_attribute_lang AS fl ON a.id = fl.attribute';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE fl.language = ? AND a.custom = ? ';
			if (!is_null($relate)):
				$query.= ' AND a.relate = ? ';
				array_push($param, $relate);
			endif;
			$query.= ' ORDER BY a.id ASC ';
			$this->prepare($query);
			$result = $this->exec($param);
			
			return isset($result) ? $result : array();
		}
	
		function getGroupproInId($ids = 0, $language = 'vn'){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_grouppro AS a ';
			$query.= ' LEFT JOIN tbl_grouppro_lang AS fl ON a.id = fl.grouppro';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id IN ('.$ids.') AND fl.language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($language));
			
			return isset($result) ? $result : array();
		}
		
		function getCatetoryInId($ids = 0, $language = 'vn'){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_category AS a ';
			$query.= ' LEFT JOIN tbl_category_lang AS fl ON a.id = fl.category';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id IN ('.$ids.') AND fl.language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($language));
			
			return isset($result) ? $result : array();
		}
		
		function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_product_lang WHERE product = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_product AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_product AS a ';
			$query.= ' INNER JOIN tbl_product_lang AS b ON a.id = b.product WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext, fl.shorttext, fl.tags, fl.meta FROM tbl_product AS a ';
			$query.= ' LEFT JOIN tbl_product_lang AS fl ON a.id = fl.product';
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
			$category 	= Generals::getState('filter.category');
			$grouppro 	= Generals::getState('filter.grouppro');
			$language	= Generals::getSession('langcode');
			$params 	= array($language);
			if ($grouppro) $this->setGroup($grouppro);
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' fl.language = ? ';
			if (strlen($search)):
				$where[] = ' LOWER(fl.name) LIKE ? ';
				array_push($params, '%'.strtolower($search).'%');
			endif;
			if (strlen($this->_group)):
				$where[] = ' a.grouppro = ? ';
				array_push($params, $this->_group);
			endif;
			if (strlen($this->_group)):
				$where[] = ' a.grouppro = ? ';
				array_push($params, $this->_group);
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			if (strlen($category)):
				$CategoryDao = new CategoryDao();
				$categories	 = $CategoryDao->getParentList($category);
				$_cates = array(0);
				$_addor = array();
				foreach ($categories as $cate) $_cates[] = (int)$cate['id'];
				foreach ($_cates as $_cate) $_addor[] = ' LOWER(a.categories) LIKE "%,'.$_cate.',%" ';
				$where[] = ' ('.implode(' OR ', $_addor).') ';
			endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, cr.code AS currency_code, fl.name, fl.alias, fl.introtext, fl.shorttext, fl.tags, fl.meta FROM tbl_product AS a ';
			$query.= ' LEFT JOIN tbl_product_lang AS fl ON a.id = fl.product';
			$query.= ' LEFT JOIN tbl_currencies AS cr ON a.currency = cr.id';
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

			$this->_custom = Generals::getState('filter.custom');
			foreach ($result as $key => $items):
				if (is_array($this->_custom)) foreach ($this->_custom as $cid => $val):
					if ($val):
						$checked = $this->getProCustomID($items['id'], $cid, $val);
						if (empty($checked)) unset($result[$key]);
					endif;
				endforeach;
			endforeach;
			
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
				$this->prepare(' UPDATE tbl_product SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_product SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$this->prepare(' SELECT * FROM tbl_product_image WHERE product = ? ');
			$images = $this->exec(array($oid));
			
			$result = $this->getDataFull($oid);
			$this->delete_db('tbl_product', " id = ? ", array($oid));
			$this->delete_db('tbl_product_lang', " product = ? ", array($oid));
			$this->delete_db('tbl_product_image', " product = ? ", array($oid));
			
			foreach ($images as $image):
				$this->delete_db('tbl_product_image_lang', " image = ? ", array($image['id']));
				JFile::delete(IMG_PATH.DS."product".DS."multiple".DS.$image['icon']);
			endforeach;
			
			JFile::delete(IMG_PATH.DS."product".DS.$result['image']);
			JFile::delete(IMG_PATH.DS."product".DS.'resize'.DS.str_replace('image', 'thumb', $result['image']));
		}
	}// end class		
 }
?>