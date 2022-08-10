<?php
 if (!defined("AGENCY_DAO_INC")) {
	define("AGENCY_DAO_INC",1);
	
	class AgencyDao extends DbConnect /*DbMySQLConnect*/  {
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
            if (!is_null(Generals::getVar('filter_location')))  Generals::setState('filter.location', Generals::getVar('filter_location'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
                            'filter_location' => Generals::getState('filter.location')
						);
		}

        function getExistedCode($code, $void) {
            $query = 'SELECT COUNT(a.id) AS count FROM tbl_agency AS a WHERE TRIM(LOWER(a.code)) = ? AND a.id <> ? ';
            $this->prepare($query);
            $result = $this->exec(array(trim(strtolower($code)), (int)$void));

            return $result[0]['count'];
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


        function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_agency_lang WHERE agency = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_agency AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_agency AS a ';
			$query.= ' INNER JOIN tbl_agency_lang AS b ON a.id = b.agency WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, b.name, b.alias, b.introtext FROM tbl_agency AS a ';
			$query.= ' LEFT JOIN tbl_agency_lang AS b ON a.id = b.agency';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
			$query.= ' WHERE a.id = '.(int)$id;
			$this->prepare($query);
			$result = $this->exec($params);
			
			return isset($result[0]) ? $result[0] : array();
		}

		function _buildQuery(){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$published 	= Generals::getState('filter.published');
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
            else:
                $where[] = ' a.published >= 0 ';
			endif;
            if (strlen($location)):
                $where[] = ' a.location = ? ';
                array_push($params, $location);
            endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name, b.alias, b.introtext, c.name AS lo_name FROM tbl_agency AS a ';
			$query.= ' LEFT JOIN tbl_agency_lang AS b ON a.id = b.agency';
            $query.= ' LEFT JOIN tbl_location_lang AS c ON a.location = c.location AND c.language = b.language ';
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
				$this->prepare(' UPDATE tbl_agency SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_agency SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getDataFull($oid);
			#$this->delete_db('tbl_agency', " id = ? ", array($oid));
			#$this->delete_db('tbl_agency_lang', " agency = ? ", array($oid));

            #=======================================================================
            # Write Log Change Data ================================================
            #=======================================================================
            $this->prepare(" SELECT * FROM tbl_agency WHERE id = ".(int)$oid);
            $result    = $this->execRun(array());
            $_user     = Generals::getUserData();
            $old_datas = array();
            foreach ($result[0] as $key => $value ){
                $old_datas[] = $key.' = '.$value;
            }

            $introtext = 'Delete Table: tbl_agency<br/>';
            $introtext.= 'Where id = '.$oid.'<br/>';
            $introtext.= 'Old Datas:';
            $introtext.= '<p>'.implode('<br/>', $old_datas).'</p>';

            $logdata = array();
            $logdata['title']       = 'Delete Table: tbl_agency';
            $logdata['introtext']   = $introtext;
            $logdata['create_on']   = date('Y-m-d H:i:s');
            $logdata['create_by']   = $_user['id'];
            $this->insert_db('tbl_writelog', $logdata);
            #=======================================================================
            #=======================================================================

            $this->published(-1, array($oid));
		}
	}// end class		
 }
?>