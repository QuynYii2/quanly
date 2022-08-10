<?php
 if (!defined("QUOTATION_DAO_INC")) {
	define("QUOTATION_DAO_INC",1);
	
	class QuotationDao extends DbConnect /*DbMySQLConnect*/  {
		var $_id;
        var $_genre;
		
		function __construct($genre = null) {
			$cid = Generals::getVar('cid', array());
			$this->setState();
            $this->setGenre($genre);
			$this->setId($cid[0]);
			parent::__construct();
		}
		
		function setId($id) {
			$this->_id	= $id;
		}

        function setGenre($genre) {
            $this->_genre = $genre;
        }

		function setState() {
			if (!is_null(Generals::getVar('filter_search'))) 	Generals::setState('filter.search', strtolower(trim(Generals::getVar('filter_search'))));
			if (!is_null(Generals::getVar('filter_published'))) Generals::setState('filter.published', Generals::getVar('filter_published'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));

            if (!is_null(Generals::getVar('filter_agency')))    Generals::setState('filter.agency', Generals::getVar('filter_agency'));
            if (!is_null(Generals::getVar('filter_supplier')))  Generals::setState('filter.supplier', Generals::getVar('filter_supplier'));
            if (!is_null(Generals::getVar('filter_departure'))) Generals::setState('filter.departure', Generals::getVar('filter_departure'));
            if (!is_null(Generals::getVar('filter_location')))  Generals::setState('filter.location', Generals::getVar('filter_location'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
                            'filter_agency' => Generals::getState('filter.agency'), 'filter_supplier' => Generals::getState('filter.supplier'),
                            'filter_departure' => Generals::getState('filter.departure'), 'filter_location' => Generals::getState('filter.location')
						);
		}

        function getJourneies(){
            $query = 'SELECT a.* FROM tbl_journey AS a WHERE a.quotation = ? ORDER BY id ASC';
            $this->prepare($query);
            $result = $this->exec(array($this->_id));

            if (is_array($result)) foreach ($result as $key => $items):
                $query = 'SELECT a.* FROM tbl_journey_service AS a WHERE a.quotation = ? AND journey = ? ORDER BY id ASC';
                $this->prepare($query);
                $result[$key]['services'] = $this->exec(array($this->_id, $items['id']));
            endforeach;

            return $result;
        }

        function getAgencies($ids = null){
            $query = ' SELECT a.id, a.phone, a.address, b.name FROM tbl_agency AS a ';
            $query.= ' LEFT JOIN tbl_agency_lang AS b ON a.id = b.agency';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? '.($ids?' AND a.id IN ('.$ids.') ':'').' ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getSuppliers(){
            $query = ' SELECT a.id, a.phone, b.name FROM tbl_supplier AS a ';
            $query.= ' LEFT JOIN tbl_supplier_lang AS b ON a.id = b.supplier';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getLocations($id = null){
            $query = ' SELECT a.id, b.name, c.name AS c_name FROM tbl_location AS a ';
            $query.= ' LEFT JOIN tbl_location_lang AS b ON a.id = b.location';
            $query.= ' LEFT JOIN tbl_country AS c ON a.country = c.id ';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ';
            if (!empty($id)) $query.= ' AND a.id = '.(int)$id;
            $query.= ' ORDER BY a.country, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getServiceGenre() {
            $return = array();
            $return['hotel']    = Generals::getTitle('MENU_SERVICE_HOTEL');
            $return['guider']   = Generals::getTitle('MENU_SERVICE_GUIDER');
            $return['entrance'] = Generals::getTitle('MENU_SERVICE_ENTRANCE');
            $return['vehicle']  = Generals::getTitle('MENU_SERVICE_VEHICLE');
            $return['boat']     = Generals::getTitle('MENU_SERVICE_BOAT');
            $return['food']     = Generals::getTitle('MENU_SERVICE_FOOD');
            $return['flight']   = Generals::getTitle('MENU_SERVICE_FLIGHT');
            $return['miscell']  = Generals::getTitle('MENU_SERVICE_MISCELL');

            return $return;
        }

        function getServiceData($id) {
            $_lang = Generals::getSession('langcode');
            $query = ' SELECT a.*, b.name, c.name AS su_name, d.name AS lo_name FROM tbl_service AS a ';
            $query.= ' LEFT JOIN tbl_service_lang AS b ON a.id = b.service';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' LEFT JOIN tbl_supplier_lang AS c ON a.supplier = c.supplier';
            $query.= ' LEFT JOIN tbl_location_lang AS d ON a.location = d.location';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND c.language = ? ';
            $query.= ' AND d.language = ? AND a.id =  ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array($_lang, $_lang, $_lang, $id));

            return !empty($result[0]) ? $result[0] : array();
        }

        function getServices($genre = null, $location = null){
            $_lang = Generals::getSession('langcode');
            $query = ' SELECT a.id, a.supplier, a.location, b.name, c.name AS su_name, d.name AS lo_name FROM tbl_service AS a ';
            $query.= ' LEFT JOIN tbl_service_lang AS b ON a.id = b.service';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' LEFT JOIN tbl_supplier_lang AS c ON a.supplier = c.supplier';
            $query.= ' LEFT JOIN tbl_location_lang AS d ON a.location = d.location';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND c.language = ? ';
            if ($genre) $query.= ' AND a.genre = "'.$genre.'" ';
            if ($location) $query.= ' AND a.location = '.(int)$location;
            $query.= ' AND d.language = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array($_lang, $_lang, $_lang));

            return is_array($result) ? $result : array();
        }

        function getSubServices($genre, $parent){
            $_lang = Generals::getSession('langcode');
            $genre = strtolower($genre);
            $query = ' SELECT a.id, b.name FROM tbl_'.$genre.' AS a ';
            $query.= ' LEFT JOIN tbl_'.$genre.'_lang AS b ON a.id = b.'.$genre;
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND a.service = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array($_lang, $parent));

            return is_array($result) ? $result : array();
        }

        function getDetailService($genre, $id){
            $_lang = Generals::getSession('langcode');
            $genre = strtolower($genre);
            $query = ' SELECT a.*, b.name FROM tbl_'.$genre.' AS a ';
            $query.= ' LEFT JOIN tbl_'.$genre.'_lang AS b ON a.id = b.'.$genre;
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND a.id = ? ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec(array($_lang, $id));

            return !empty($result[0]) ? $result[0] : array();
        }

        function getPersons(){
            $query = ' SELECT a.id, a.minpax, a.maxpax, a.divpax, b.name FROM tbl_person AS a ';
            $query.= ' LEFT JOIN tbl_person_lang AS b ON a.id = b.person';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ORDER BY a.ordering ';

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

		function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_quotation_lang WHERE quotation = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_quotation AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_quotation AS a ';
			$query.= ' INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, b.name, b.alias, b.introtext FROM tbl_quotation AS a ';
			$query.= ' LEFT JOIN tbl_quotation_lang AS b ON a.id = b.quotation';
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

            $agency	    = Generals::getState('filter.agency');
            $supplier	= Generals::getState('filter.supplier');
            $departure	= Generals::getState('filter.departure');
            $location	= Generals::getState('filter.location');

			$params 	= array($language, $this->_genre);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' b.language = ? ';
            $where[] = ' a.genre = ? ';
			if (strlen($search)):
				$where[] = ' (LOWER(a.code) LIKE ? OR LOWER(b.name) LIKE ?) ';
				array_push($params, '%'.strtolower($search).'%', '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
            if (strlen($agency)):
                $where[] = ' a.agencies LIKE "%,'.$agency.',%" ';
            endif;
            if (strlen($supplier)):
                $_condition = ' Select js.quotation From tbl_journey_service As js ';
                $_condition.= ' Inner Join tbl_service As se On js.profile = se.id ';
                $_condition.= ' Where se.supplier = ? And se.genre = js.service ';
                $where[] = ' a.id IN('.$_condition.') ';
                array_push($params, $supplier);
            endif;
            if (strlen($departure)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") = ? ';
                array_push($params, date('Ymd', strtotime($departure)));
            endif;
            if (strlen($location)):
                $where[] = ' a.id IN(Select quotation From tbl_journey_service WHERE location = ? ) ';
                array_push($params, $location);
            endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name, b.alias, b.introtext FROM tbl_quotation AS a ';
			$query.= ' LEFT JOIN tbl_quotation_lang AS b ON a.id = b.quotation';
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
				$this->prepare(' UPDATE tbl_quotation SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_quotation SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getDataFull($oid);
			$this->delete_db('tbl_quotation', " id = ? ", array($oid));
			$this->delete_db('tbl_quotation_lang', " quotation = ? ", array($oid));
		}
	}// end class		
 }
?>