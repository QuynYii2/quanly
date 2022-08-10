<?php
 if (!defined("HOTEL_DAO_INC")) {
	define("HOTEL_DAO_INC",1);
	
	class HotelDao extends DbConnect /*DbMySQLConnect*/  {
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
            if (!is_null(Generals::getVar('filter_supplier')))  Generals::setState('filter.supplier', Generals::getVar('filter_supplier'));
            if (!is_null(Generals::getVar('filter_season')))    Generals::setState('filter.season', Generals::getVar('filter_season'));
            if (!is_null(Generals::getVar('filter_location')))  Generals::setState('filter.location', Generals::getVar('filter_location'));
            if (!is_null(Generals::getVar('filter_service')))   Generals::setState('filter.service', Generals::getVar('filter_service'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
                            'filter_supplier' => Generals::getState('filter.supplier'), 'filter_season' => Generals::getState('filter.season'),
                            'filter_location' => Generals::getState('filter.location'), 'filter_service' => Generals::getState('filter.service')
						);
		}
		
		function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_hotel_lang WHERE hotel = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_hotel AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_hotel AS a ';
			$query.= ' INNER JOIN tbl_hotel_lang AS b ON a.id = b.hotel WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_hotel AS a ';
			$query.= ' LEFT JOIN tbl_hotel_lang AS fl ON a.id = fl.hotel';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id = '.(int)$id;
			$this->prepare($query);
			$result = $this->exec(array());
			
			return isset($result[0]) ? $result[0] : array();
		}

        function getSuppliers(){
            $query = ' SELECT a.id, b.name FROM tbl_supplier AS a ';
            $query.= ' LEFT JOIN tbl_supplier_lang AS b ON a.id = b.supplier';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getLocations(){
            $query = ' SELECT a.id, b.name FROM tbl_location AS a ';
            $query.= ' LEFT JOIN tbl_location_lang AS b ON a.id = b.location';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getServices($id = null){
            $_lang = Generals::getSession('langcode');
            $query = ' SELECT a.id, a.supplier, a.location, b.name, c.name AS su_name, d.name AS lo_name FROM tbl_service AS a ';
            $query.= ' LEFT JOIN tbl_service_lang AS b ON a.id = b.service';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' LEFT JOIN tbl_supplier_lang AS c ON a.supplier = c.supplier';
            $query.= ' LEFT JOIN tbl_location_lang AS d ON a.location = d.location';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND c.language = ? ';
            if ($id) $query.= ' AND a.id = '.(int)$id;
            $query.= ' AND d.language = ? AND a.genre = "hotel" ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array($_lang, $_lang, $_lang));

            return is_array($result) ? $result : array();
        }

        function getSeasons(){
            $query = ' SELECT a.id, b.name FROM tbl_season AS a ';
            $query.= ' LEFT JOIN tbl_season_lang AS b ON a.id = b.season';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getCurrencies(){
            $query = ' SELECT a.id, a.code, a.symbol FROM tbl_currencies AS a ';
            $query.= ' WHERE a.published = 1 ORDER BY a.featured DESC, a.name ASC ';

            $this->prepare($query);
            $result = $this->exec(array());

            return is_array($result) ? $result : array();
        }

        function _buildQuery(){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$published 	= Generals::getState('filter.published');
            $supplier 	= Generals::getState('filter.supplier');
            $season 	= Generals::getState('filter.season');
            $location 	= Generals::getState('filter.location');
            $service 	= Generals::getState('filter.service');
			$language	= Generals::getSession('langcode');
			$params 	= array($language, $language, $language, $language, $language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' fl.language = ? ';
            $where[] = ' p.language = ? ';
            $where[] = ' c.language = ? ';
            $where[] = ' s.language = ? ';
            $where[] = ' v.language = ? ';
			if (strlen($search)):
				$where[] = ' (LOWER(a.code) LIKE ? OR LOWER(fl.name) LIKE ?) ';
				array_push($params, '%'.strtolower($search).'%', '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
            if (strlen($supplier)):
                $where[] = ' a.supplier = ? ';
                array_push($params, $supplier);
            endif;
            if (strlen($season)):
                $where[] = ' a.season = ? ';
                array_push($params, $season);
            endif;
            if (strlen($location)):
                $where[] = ' a.location = ? ';
                array_push($params, $location);
            endif;
            if (strlen($service)):
                $where[] = ' a.service = ? ';
                array_push($params, $service);
            endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext, v.name AS sv_name, ';
            $query.= ' p.name AS su_name, c.name AS lo_name, s.name AS se_name ';
            $query.= ' FROM tbl_hotel AS a ';
			$query.= ' LEFT JOIN tbl_hotel_lang AS fl ON a.id = fl.hotel';
            $query.= ' LEFT JOIN tbl_supplier_lang AS p ON a.supplier = p.supplier';
            $query.= ' LEFT JOIN tbl_location_lang AS c ON a.location = c.location';
            $query.= ' LEFT JOIN tbl_service_lang AS v ON a.service = v.service';
            $query.= ' LEFT JOIN tbl_season_lang AS s ON a.season = s.season';
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
				$this->prepare(' UPDATE tbl_hotel SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_hotel SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getDataFull($oid);
			$this->delete_db('tbl_hotel', " id = ? ", array($oid));
			$this->delete_db('tbl_hotel_lang', " hotel = ? ", array($oid));
		}
	}// end class		
 }
?>