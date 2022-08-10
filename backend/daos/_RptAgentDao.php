<?php
 if (!defined("RPTAGENT_DAO_INC")) {
	define("RPTAGENT_DAO_INC",1);
	
	class RptAgentDao extends DbConnect /*DbMySQLConnect*/  {
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
			if (!is_null(Generals::getVar('filter_fdate'))) 	Generals::setState('filter.fdate', Generals::getVar('filter_fdate'));
			if (!is_null(Generals::getVar('filter_tdate')))     Generals::setState('filter.tdate', Generals::getVar('filter_tdate'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_fdate' => Generals::getState('filter.fdate'), 'filter_tdate' => Generals::getState('filter.tdate'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field')
						);
		}

		function _buildQuery(){
			$fdate 	= Generals::getState('filter.fdate');
			$tdate 	= Generals::getState('filter.tdate');
			$params = array(Generals::getSession('langcode'));
			
			$where	= array('b.language = ?');
			if (strlen($fdate)):
				$where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") >= ? ';
				array_push($params, date('Ymd', strtotime($fdate)));
			endif;
			if (strlen($tdate)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($tdate)));
			endif;
			
			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name AS guest, ag.name AS agent_name, (Select sum(price) From tbl_quotation_payment AS p Where p.quotation = a.id) AS paid ';
            $query.= ' FROM tbl_quotation AS a INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation ';
            $query.= ' LEFT JOIN tbl_agency_lang AS ag ON ag.id = a.agency AND ag.language = b.language ';
            $query.= ' INNER JOIN tbl_language AS l ON l.code = b.language ';
            $query.= $where;
			Generals::setState('params', $params);
			
			return $query;
		}
		
		function getDataList($limit = null) {
			$field = Generals::getState('orderby.field', 'a.id') ? Generals::getState('orderby.field', 'a.id') : 'a.id';
			$order = Generals::getState('orderby.order', 'DESC') ? Generals::getState('orderby.order', 'DESC') : 'DESC';
			$query = $this->_buildQuery();
			$query.= ' ORDER BY '.$field.' '.$order;
			$this->prepare($query);
			$result = $this->exec(Generals::getState('params'));
			$this->total = sizeof($result);
            $limit = $limit ? $limit : LIMIT_RECORD;
			while (true):
				$offset = (int)(Generals::getState('page', 1)-1)*$limit;
				if ($offset >= $this->total) {
					$offset	= (int)$offset - (int)$limit;
					Generals::setState('page', Generals::getState('page', 1)-1);
				} elseif ($offset < 0) {
					$offset = 0;
					break;
				} else {
					break;
				}
			endwhile;
			
			if (is_array($result)):
				$result = array_chunk($result, $limit);
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
	}// end class
 }
?>