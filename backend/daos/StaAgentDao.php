<?php
 if (!defined("STAAGENT_DAO_INC")) {
	define("STAAGENT_DAO_INC",1);
	
	class StaAgentDao extends DbConnect /*DbMySQLConnect*/  {
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
            if (!is_null(Generals::getVar('filter_agent')))     Generals::setState('filter.agent', Generals::getVar('filter_agent'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_fdate' => Generals::getState('filter.fdate'), 'filter_tdate' => Generals::getState('filter.tdate'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
                            'filter_agent' => Generals::getState('filter.agent')
						);
		}

        function getAgencies($ids = null){
            $query = ' SELECT a.id, a.code, a.phone, a.address, b.name, c.name AS l_name FROM tbl_agency AS a ';
            $query.= ' LEFT JOIN tbl_agency_lang AS b ON a.id = b.agency';
            $query.= ' LEFT JOIN tbl_location_lang AS c ON a.location = c.location AND c.language = b.language ';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? '.($ids?' AND a.id IN ('.$ids.') ':'').' ';
            $query.= ' ORDER BY c.name, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getReport(){
            $this->getDataList(1000000);
        }

		function _buildQuery(){
			$fdate 	= Generals::getState('filter.fdate');
			$tdate 	= Generals::getState('filter.tdate');
            $agent 	= Generals::getState('filter.agent');
			$params = array(Generals::getSession('langcode'));
			$where	= array('l.code = ?');

			if (strlen($fdate)):
				$where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") >= ? ';
				array_push($params, date('Ymd', strtotime($fdate)));
			endif;
			if (strlen($tdate)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($tdate)));
			endif;
            if (strlen($agent)):
                $where[] = ' LOWER(a.agencies) LIKE ? ';
                array_push($params, '%,'.strtolower($agent).',%');
            endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name, al.name AS agent_name, (Select Sum(price) From tbl_quotation_payment AS fp Where fp.quotation = a.id) AS paid,  ';
            $query.= ' (Select Sum(markup) From tbl_quotation_payment AS fp Where fp.quotation = a.id) AS paid_markup ';
            $query.= ' FROM tbl_quotation AS a INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation ';
            $query.= ' INNER JOIN tbl_agency_lang AS al ON al.agency = a.agency AND al.language = b.language ';
            $query.= ' INNER JOIN tbl_language AS l ON l.code = b.language '.$where;

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

            $result = $result[$page];
            $return = array();
            if (is_array($result)) foreach ($result as $items):
                $query = ' SELECT qp.*, ql.name AS quotation_name, al.name AS agent_name, q.id AS qid ';
                $query.= ' FROM tbl_quotation AS q INNER JOIN tbl_quotation_lang AS ql ON q.id = ql.quotation ';
                $query.= ' INNER JOIN tbl_agency_lang AS al ON al.agency = q.agency ';
                $query.= ' INNER JOIN tbl_quotation_payment AS qp ON qp.quotation = q.id AND qp.genre = 0 ';
                $query.= ' INNER JOIN tbl_language AS l ON l.code = ql.language AND l.code = al.language ';
                $query.= ' WHERE q.id = ? AND l.code = ? ORDER BY qp.id ASC ';
                $this->prepare($query);
                $return[$items['id']]['value']  = $this->exec(array($items['id'], Generals::getSession('langcode')));
                $return[$items['id']]['title']  = $items['name'];
                $return[$items['id']]['agent']  = $items['agent_name'];
                $return[$items['id']]['total']  = $items['total'];
                $return[$items['id']]['profit'] = $items['profit'];
                $return[$items['id']]['paid']   = $items['paid'];
                $return[$items['id']]['markup'] = $items['paid_markup'];
            endforeach;

            return $return;
		}
		
		function getCountData() {
			return $this->total;
		}
	}// end class
 }
?>