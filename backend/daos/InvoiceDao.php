<?php
 if (!defined("INVOICE_DAO_INC")) {
	define("INVOICE_DAO_INC",1);
	
	class InvoiceDao extends DbConnect /*DbMySQLConnect*/  {
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
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field')
						);
		}

        function getExistInvoice($qid, $cid){
            $this->prepare(' SELECT COUNT(a.id) as count FROM tbl_invoice AS a WHERE a.introtext LIKE ? AND a.id <> ? ');
            $result = $this->exec(array('%"quote":[{"id":"'.(int)$qid.'"%', (int)$cid));

            return $result[0]['count'];
        }

        function getPayments(){
            $query = ' SELECT a.id, a.code, b.name FROM tbl_payment AS a ';
            $query.= ' LEFT JOIN tbl_payment_lang AS b ON a.id = b.payment';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE b.language = ? ORDER BY a.code, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getAgencies(){
            $query = ' SELECT a.id, a.code, b.name FROM tbl_agency AS a ';
            $query.= ' LEFT JOIN tbl_agency_lang AS b ON a.id = b.agency';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE b.language = ? ';
            $query.= ' ORDER BY a.code, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getQuotations($agency = null){
            $query = ' SELECT a.id, a.code, a.paxrange, b.name FROM tbl_quotation AS a ';
            $query.= ' LEFT JOIN tbl_quotation_lang AS b ON a.id = b.quotation';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE b.language = ? '.($agency ? ' AND  agencies LIKE "%,'.(int)$agency.',%" ' : '');
            $query.= ' AND a.published > 0 ORDER BY a.code, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getExistedCode($code, $void) {
            $query = 'SELECT COUNT(a.id) AS count FROM tbl_invoice AS a WHERE TRIM(LOWER(a.voucher)) = ? AND a.id <> ? ';
            $this->prepare($query);
            $result = $this->exec(array(trim(strtolower($code)), (int)$void));

            return $result[0]['count'];
        }

        function getData($void = null) {
			$void	= $void ? $void : $this->_id;
			$query 	= 'SELECT a.* FROM tbl_invoice AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, a.invoiceno AS text FROM tbl_invoice AS a ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array());
			
			return $result;
		}
		
		function _buildQuery(){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$published 	= Generals::getState('filter.published');
			$params 	= array();
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where	= array();
			if (strlen($search)):
				$where[] = ' (LOWER(a.invoiceno) LIKE ? OR LOWER(a.manager) LIKE ? OR LOWER(a.company) LIKE ? OR LOWER(a.address) LIKE ? OR LOWER(a.phone) LIKE ? OR LOWER(a.email) LIKE ? OR LOWER(a.guest) LIKE ?) ';
				array_push($params, '%'.strtolower($search).'%', '%'.strtolower($search).'%', '%'.strtolower($search).'%', '%'.strtolower($search).'%', '%'.strtolower($search).'%', '%'.strtolower($search).'%', '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			
			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, (Select Count(b.id) From tbl_invoice AS b Where b.quotation = a.quotation) AS num_quote FROM tbl_invoice AS a '.$where;
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
				$this->prepare(' UPDATE tbl_invoice SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_invoice SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getData($oid);
			$this->delete_db('tbl_invoice', " id = ? ", array($oid));
			JFile::delete(IMG_PATH.DS."user".DS.$result['image']);
		}
	}// end class		
 }
?>