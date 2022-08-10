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

        function getReport(){
            $fdate 	= Generals::getState('filter.fdate');
            $tdate 	= Generals::getState('filter.tdate');
            $params = array(Generals::getSession('langcode'));
            $where	= array('a.language = ?');
            if (strlen($fdate)):
                $where[] = ' DATE_FORMAT(q.departure, "%Y%m%d") >= ? ';
                array_push($params, date('Ymd', strtotime($fdate)));
            endif;
            if (strlen($tdate)):
                $where[] = ' DATE_FORMAT(q.departure, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($tdate)));
            endif;

            $where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
            $query = ' SELECT a.name, COUNT(q.id) AS booking, SUM(q.total) AS amount, SUM(p.price) AS paid ';
            $query.= ' FROM tbl_agency_lang AS a INNER JOIN tbl_quotation AS q ON q.agency = a.agency ';
            $query.= ' LEFT JOIN tbl_quotation_payment AS p ON p.quotation = q.id AND p.genre = 0 ';
            $query.= ' INNER JOIN tbl_language AS l ON l.code = a.language ';
            $query.= $where.' GROUP BY a.name ORDER BY a.id ';

            $this->prepare($query);
            $result = $this->exec($params);

            return $result;
        }

        function getPaidFee($quotation){
            $this->prepare(' SELECT SUM(price) AS price FROM tbl_quotation_payment WHERE quotation = ? ');
            $result = $this->exec(array($quotation));

            return (float)$result[0]['price'];
        }

        function getBankFee($quotation){
            $this->prepare(' SELECT SUM(bankfee) AS bankfee FROM tbl_quotation_payment WHERE quotation = ? ');
            $result = $this->exec(array($quotation));

            return (float)$result[0]['bankfee'];
        }

		function _buildQuery(){
			$fdate 	= Generals::getState('filter.fdate');
			$tdate 	= Generals::getState('filter.tdate');
			$params = array(Generals::getSession('langcode'));
			
			$where	= array('b.language = ?');
			if (strlen($fdate)):
				$where[] = ' DATE_FORMAT(a.fperiod, "%Y%m%d") >= ? ';
				array_push($params, date('Ymd', strtotime($fdate)));
			endif;
			if (strlen($tdate)):
                $where[] = ' DATE_FORMAT(a.fperiod, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($tdate)));
			endif;
			
			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name AS agent_name, uc.name AS user_create, ue.name AS user_update ';
            $query.= ' FROM tbl_invoice AS a INNER JOIN tbl_agency_lang AS b ON a.agency = b.agency ';
            $query.= ' INNER JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' INNER JOIN tbl_user_lang AS uc ON a.create_by = uc.user AND uc.language = b.language ';
            $query.= ' LEFT JOIN tbl_user_lang AS ue ON a.update_by = ue.user AND ue.language = b.language ';
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