<?php
 if (!defined("FLIGHT_DAO_INC")) {
	define("FLIGHT_DAO_INC",1);
	
	class FlightDao extends DbConnect /*DbMySQLConnect*/  {
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
            if (!is_null(Generals::getVar('filter_service')))   Generals::setState('filter.service', Generals::getVar('filter_service'));
            if (!is_null(Generals::getVar('filter_season')))    Generals::setState('filter.season', Generals::getVar('filter_season'));
            if (!is_null(Generals::getVar('filter_fprice')))    Generals::setState('filter.fprice', Generals::getVar('filter_fprice'));
            if (!is_null(Generals::getVar('filter_tprice')))    Generals::setState('filter.tprice', Generals::getVar('filter_tprice'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
            if (!is_null(Generals::getVar('filter_location')))  Generals::setState('filter.location', Generals::getVar('filter_location'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
                            'filter_supplier' => Generals::getState('filter.supplier'), 'filter_service' => Generals::getState('filter.service'),
                            'filter_location' => Generals::getState('filter.location'), 'filter_season' => Generals::getState('filter.season'),
                            'filter_fprice' => Generals::getState('filter.fprice'), 'filter_tprice' => Generals::getState('filter.tprice')
						);
		}

        function getPaxRanges(){
            $query = ' SELECT a.id, a.code, a.divpax, b.name FROM tbl_person AS a ';
            $query.= ' LEFT JOIN tbl_person_lang AS b ON a.id = b.person';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ORDER BY a.ordering ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getExistedCode($code, $void, $parent = null) {
            $query = 'SELECT COUNT(a.id) AS count FROM tbl_flight AS a WHERE TRIM(LOWER(a.code)) = ? AND a.id <> ? ';
            if ($parent) $query.= ' AND a.service = '.(int)$parent;
            $this->prepare($query);
            $result = $this->exec(array(trim(strtolower($code)), (int)$void));

            return $result[0]['count'];
        }

        function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_flight_lang WHERE flight = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_flight AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_flight AS a ';
			$query.= ' INNER JOIN tbl_flight_lang AS b ON a.id = b.flight WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, b.name, b.alias, b.introtext FROM tbl_flight AS a ';
			$query.= ' LEFT JOIN tbl_flight_lang AS b ON a.id = b.flight';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
			$query.= ' WHERE a.id = '.(int)$id;
			$this->prepare($query);
			$result = $this->exec(array());
			
			return isset($result[0]) ? $result[0] : array();
		}

        function getSuppliers(){
            $query = ' SELECT a.id, a.code, b.name FROM tbl_supplier AS a ';
            $query.= ' LEFT JOIN tbl_supplier_lang AS b ON a.id = b.supplier';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getSeasons(){
            $query = ' SELECT a.id, a.code, b.name FROM tbl_season AS a ';
            $query.= ' LEFT JOIN tbl_season_lang AS b ON a.id = b.season';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getLocations(){
            $query = ' SELECT a.id, a.code, b.name, c.name AS c_name FROM tbl_location AS a ';
            $query.= ' LEFT JOIN tbl_location_lang AS b ON a.id = b.location';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' LEFT JOIN tbl_country AS c ON a.country = c.id ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ';
            $query.= ' ORDER BY a.country, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getServices($id = null){
            $_lang = Generals::getSession('langcode');
            $query = ' SELECT a.id, a.code, a.supplier, a.location, b.name, c.name AS su_name, d.name AS lo_name FROM tbl_service AS a ';
            $query.= ' LEFT JOIN tbl_service_lang AS b ON a.id = b.service';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' LEFT JOIN tbl_supplier AS s ON a.supplier = s.id';
            $query.= ' LEFT JOIN tbl_supplier_lang AS c ON a.supplier = c.supplier';
            $query.= ' LEFT JOIN tbl_location_lang AS d ON a.location = d.location';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND c.language = ? AND s.services LIKE "%,flight,%" ';
            if ($id) $query.= ' AND a.id = '.(int)$id;
            $query.= ' AND d.language = ? AND a.genre = "flight" ORDER BY d.name, b.name ';

            $this->prepare($query);
            $result = $this->exec(array($_lang, $_lang, $_lang));

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
            $fprice 	= Generals::getState('filter.fprice');
            $tprice 	= Generals::getState('filter.tprice');
            $service 	= Generals::getState('filter.service');
            $location 	= Generals::getState('filter.location');
			$language	= Generals::getSession('langcode');
			$params 	= array($language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' b.language = ? ';

			if (strlen($search)):
				$where[] = ' (LOWER(a.code) LIKE ? OR LOWER(b.name) LIKE ?) ';
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
            if (strlen($service)):
                $where[] = ' a.service = ? ';
                array_push($params, $service);
            endif;
            if (strlen($location)):
                $where[] = ' a.location = ? ';
                array_push($params, $location);
            endif;
            if (strlen($fprice)):
                $where[] = ' (a.cost + a.cost*a.margin/100) >= ? ';
                array_push($params, $fprice);
            endif;
            if (strlen($tprice)):
                $where[] = ' (a.cost + a.cost*a.margin/100) <= ? ';
                array_push($params, $tprice);
            endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name, b.alias, b.introtext, c.name AS lo_name, v.name AS sv_name, ';
            $query.= ' p.name AS su_name, s.name AS se_name FROM tbl_flight AS a ';
			$query.= ' LEFT JOIN tbl_flight_lang AS b ON a.id = b.flight';
            $query.= ' LEFT JOIN tbl_supplier_lang AS p ON a.supplier = p.supplier AND b.language = p.language ';
            $query.= ' LEFT JOIN tbl_location_lang AS c ON a.location = c.location AND b.language = c.language ';
            $query.= ' LEFT JOIN tbl_service_lang AS v ON a.service = v.service AND b.language = v.language ';
            $query.= ' LEFT JOIN tbl_season_lang AS s ON a.season = s.season AND b.language = s.language ';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language '.$where;
			Generals::setState('params', $params);
			
			return $query;
		}

        function getDataList($getall = false) {
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

            if (is_array($result) && !$getall):
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
				$this->prepare(' UPDATE tbl_flight SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_flight SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getDataFull($oid);
			$this->delete_db('tbl_flight', " id = ? ", array($oid));
			$this->delete_db('tbl_flight_lang', " flight = ? ", array($oid));
		}
	}// end class		
 }
?>