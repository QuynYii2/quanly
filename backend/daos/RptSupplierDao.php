<?php
 if (!defined("RPTSUPPLIER_DAO_INC")) {
	define("RPTSUPPLIER_DAO_INC",1);
	
	class RptSupplierDao extends DbConnect /*DbMySQLConnect*/  {
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

        function getDataSupplier(){
            $fdate 	= Generals::getState('filter.fdate');
            $tdate 	= Generals::getState('filter.tdate');
            $params = array(Generals::getSession('langcode'));
            $where	= array(' c.code = ? ');
            if (strlen($fdate)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") >= ? ';
                array_push($params, date('Ymd', strtotime($fdate)));
            endif;
            if (strlen($tdate)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($tdate)));
            endif;

            $where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
            $query = ' SELECT a.id, a.code AS file_no, b.name AS guest, a.departure, a.numday FROM tbl_quotation AS a ';
            $query.= ' INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation ';
            $query.= ' LEFT JOIN tbl_language AS c ON c.code = b.language '.$where.' ORDER BY a.id ASC ';

            $this->prepare($query);
            $quotations = $this->exec($params);
            $return     = array();
            $index      = 0;
            foreach ($quotations as $qkey => $quotation):
                $query = ' SELECT qu.id, qu.pricefor, qu.paxrange, js.service, js.`profile`, sv.name AS pr_name, js.detail, SUM(js.perpax) AS perpax, js.detailname AS dt_name, ';
                $query.= ' (Select Sum(price) From tbl_quotation_payment As qp Where qp.quotation = qu.id And qp.service = js.service And qp.detail = js.detail) AS paid, sp.id AS supid, sp.name AS sp_name ';
                $query.= ' FROM tbl_journey_service AS js INNER JOIN tbl_quotation AS qu ON qu.id = js.quotation ';
                $query.= ' INNER JOIN tbl_service AS sr ON sr.id = js.`profile` ';
                $query.= ' INNER JOIN tbl_service_lang AS sv ON sv.service = js.`profile` ';
                $query.= ' INNER JOIN tbl_supplier_lang AS sp ON sp.supplier = sr.supplier ';
                $query.= ' LEFT JOIN tbl_language AS lg ON lg.`code` = sv.`language` ';
                $query.= ' WHERE lg.code = ? '.($quotation['id'] ? ' AND js.quotation = '.(int)$quotation['id'] : '').' GROUP BY js.detail ORDER BY sp_name ';
                $this->prepare($query);
                $totals = $this->exec(array(Generals::getSession('langcode', 'vn')));
                $result = array();
                if (is_array($totals)) foreach ($totals as $key => $items):
                    if ($items['service'] == 'hotel' && $items['pricefor'] == 2):
                        $items['total'] = $items['perpax']*$items['paxrange'];
                    elseif ($items['service'] == 'hotel' && ($items['pricefor'] || $items['paxrange'] == 1)):
                        $items['total'] = $items['perpax']*floor($items['paxrange']/2)*2 + $items['single'];
                    else:
                        $items['total'] = $items['perpax']*$items['paxrange'];
                    endif;
                    $items['unpaid'] = $items['total'] - $items['paid'];
                    $totals[$key] = $items;
                endforeach;

                foreach ($totals as $items):
                    $result[$items['supid']]['title'] = $items['sp_name'];
                    $result[$items['supid']]['value'][] = $items;
                endforeach;

                foreach ($result as $key => $items):
                    foreach ($items['value'] as $item):
                        $result[$key]['total']  += $item['total'];
                        $result[$key]['paid']   += $item['paid'];
                        $result[$key]['unpaid'] += $item['total'] - $item['paid'];
                    endforeach;
                    $return[$index]['supid']    = $key;
                    $return[$index]['title']    = $items['title'];
                    $return[$index]['total']    = $result[$key]['total'];
                    $return[$index]['paid']     = $result[$key]['paid'];
                    $return[$index]['unpaid']   = $result[$key]['unpaid'];
                    $index++;
                endforeach;
            endforeach;

            $result = array();
            foreach ($return as $items):
                $result[$items['supid']]['title']   = $items['title'];
                $result[$items['supid']]['total']  += $items['total'];
                $result[$items['supid']]['paid']   += $items['paid'];
                $result[$items['supid']]['unpaid'] += $items['unpaid'];
            endforeach;

            return array_values($result);
        }

        function getDataSummary(){
            $fdate 	= Generals::getState('filter.fdate');
            $tdate 	= Generals::getState('filter.tdate');
            $params = array(Generals::getSession('langcode'));
            $where	= array(' c.code = ? ');
            if (strlen($fdate)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") >= ? ';
                array_push($params, date('Ymd', strtotime($fdate)));
            endif;
            if (strlen($tdate)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($tdate)));
            endif;

            $where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
            $query = ' SELECT a.id, a.code AS file_no, b.name AS guest, a.departure, a.numday FROM tbl_quotation AS a ';
            $query.= ' INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation ';
            $query.= ' LEFT JOIN tbl_language AS c ON c.code = b.language '.$where.' ORDER BY a.id ASC ';

            $this->prepare($query);
            $quotations = $this->exec($params);
            $return     = array();
            $index      = 0;
            foreach ($quotations as $qkey => $quotation):
                $query = ' SELECT qu.id, qu.pricefor, qu.paxrange, js.service, js.`profile`, sv.name AS pr_name, js.detail, SUM(js.perpax) AS perpax, js.detailname AS dt_name, ';
                $query.= ' (Select Sum(price) From tbl_quotation_payment As qp Where qp.quotation = qu.id And qp.service = js.service And qp.detail = js.detail) AS paid, sp.id AS supid, sp.name AS sp_name, ';
                $query.= ' (Select ul.name FROM tbl_quotation_payment As qp Inner Join tbl_user_lang AS ul On qp.create_by = ul.user Where qp.quotation = qu.id And qp.service = js.service And qp.detail = js.detail And ul.language = lg.`code` Order By qp.id DESC Limit 1) AS update_by, ';
                $query.= ' (Select qp.create_on FROM tbl_quotation_payment As qp Where qp.quotation = qu.id And qp.service = js.service And qp.detail = js.detail Order By qp.id DESC Limit 1) AS paid_date, ';
                $query.= ' (Select pl.name FROM tbl_quotation_payment As qp Inner Join tbl_payment_lang AS pl On qp.payment = pl.payment Where qp.quotation = qu.id And qp.service = js.service And qp.detail = js.detail And pl.language = lg.`code` Order By qp.id DESC Limit 1) AS pay_type ';
                $query.= ' FROM tbl_journey_service AS js INNER JOIN tbl_quotation AS qu ON qu.id = js.quotation ';
                $query.= ' INNER JOIN tbl_service AS sr ON sr.id = js.`profile` ';
                $query.= ' INNER JOIN tbl_service_lang AS sv ON sv.service = js.`profile` ';
                $query.= ' INNER JOIN tbl_supplier_lang AS sp ON sp.supplier = sr.supplier ';
                $query.= ' LEFT JOIN tbl_language AS lg ON lg.`code` = sv.`language` ';
                $query.= ' WHERE lg.code = ? '.($quotation['id'] ? ' AND js.quotation = '.(int)$quotation['id'] : '').' GROUP BY js.detail ORDER BY sp_name ';
                $this->prepare($query);
                $totals = $this->exec(array(Generals::getSession('langcode', 'vn')));

                if (is_array($totals)) foreach ($totals as $key => $items):
                    if ($items['service'] == 'hotel' && $items['pricefor'] == 2):
                        $items['total'] = $items['perpax']*$items['paxrange'];
                    elseif ($items['service'] == 'hotel' && ($items['pricefor'] || $items['paxrange'] == 1)):
                        $items['total'] = $items['perpax']*floor($items['paxrange']/2)*2 + $items['single'];
                    else:
                        $items['total'] = $items['perpax']*$items['paxrange'];
                    endif;
                    $items['unpaid'] = $items['total'] - $items['paid'];
                    $totals[$key] = $items;
                endforeach;

                foreach ($totals as $items):
                    $return[$index]['paxrange'] = $items['paxrange'];
                    $return[$index]['file_no']  = $quotation['file_no'];
                    $return[$index]['code']     = Generals::getTitle('EXPORT_SERVICE_'.strtoupper($items['service']));
                    $return[$index]['sp_name']  = $items['sp_name'];
                    $return[$index]['pr_name']  = $items['pr_name'];
                    $return[$index]['dt_name']  = $items['dt_name'];
                    $return[$index]['total']    = $items['total'];
                    $return[$index]['paid']     = $items['paid'];
                    $return[$index]['unpaid']   = $items['unpaid'];
                    $return[$index]['invoice']  = $items['invoice'];
                    $return[$index]['check_in'] = $quotation['departure'];
                    $return[$index]['check_out']= date('Y-m-d H:i:s', strtotime('+'.$quotation['numday'].' day', strtotime($quotation['departure'])));
                    $return[$index]['update_by']= $items['update_by'];
                    $return[$index]['paid_date']= $items['paid_date'];
                    $return[$index]['pay_type'] = $items['pay_type'];
                    $index++;
                endforeach;
            endforeach;

            return $return;
        }

		function _buildQuery(){
			$fdate 	= Generals::getState('filter.fdate');
			$tdate 	= Generals::getState('filter.tdate');
			$params = array(1, Generals::getSession('langcode'));
			$where	= array(' a.published = ? ', ' c.code = ? ');
			if (strlen($fdate)):
				$where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") >= ? ';
				array_push($params, date('Ymd', strtotime($fdate)));
			endif;
			if (strlen($tdate)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($tdate)));
			endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.id, a.code AS file_no, b.name AS guest FROM tbl_quotation AS a ';
            $query.= ' INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation ';
            $query.= ' LEFT JOIN tbl_language AS c ON c.code = b.language '.$where;
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

            $return = $result[$page];
            #===========================================================================================================
            if (is_array($return)) foreach ($return as $qkey => $quotation):
                $query = ' SELECT qu.id, qu.pricefor, qu.paxrange, js.service, js.`profile`, sv.name AS pr_name, js.detail, SUM(js.perpax) AS perpax, js.detailname AS dt_name, ';
                $query.= ' (Select Sum(price) From tbl_quotation_payment As qp Where qp.quotation = qu.id And qp.service = js.service And qp.detail = js.detail) AS paid, sp.id AS supid, sp.name AS sp_name ';
                $query.= ' FROM tbl_journey_service AS js INNER JOIN tbl_quotation AS qu ON qu.id = js.quotation ';
                $query.= ' INNER JOIN tbl_service AS sr ON sr.id = js.`profile` ';
                $query.= ' INNER JOIN tbl_service_lang AS sv ON sv.service = js.`profile` ';
                $query.= ' INNER JOIN tbl_supplier_lang AS sp ON sp.supplier = sr.supplier ';
                $query.= ' LEFT JOIN tbl_language AS lg ON lg.`code` = sv.`language` ';
                $query.= ' WHERE lg.code = ? '.($quotation['id'] ? ' AND js.quotation = '.(int)$quotation['id'] : '').' GROUP BY js.detail ORDER BY sp_name ';
                $this->prepare($query);
                $totals = $this->exec(array(Generals::getSession('langcode', 'vn')));
                $result = array();
                if (is_array($totals)) foreach ($totals as $key => $items):
                    if ($items['service'] == 'hotel' && $items['pricefor'] == 2):
                        $items['total'] = $items['perpax']*$items['paxrange'];
                    elseif ($items['service'] == 'hotel' && ($items['pricefor'] || $items['paxrange'] == 1)):
                        $items['total'] = $items['perpax']*floor($items['paxrange']/2)*2 + $items['single'];
                    else:
                        $items['total'] = $items['perpax']*$items['paxrange'];
                    endif;
                    $items['unpaid'] = $items['total'] - $items['paid'];
                    $totals[$key] = $items;
                endforeach;

                foreach ($totals as $items):
                    $result[$items['supid']]['title'] = $items['sp_name'];
                    $result[$items['supid']]['value'][] = $items;
                endforeach;

                foreach ($result as $key => $items):
                    foreach ($items['value'] as $item):
                        $result[$key]['total']  += $item['total'];
                        $result[$key]['paid']   += $item['paid'];
                        $result[$key]['unpaid'] += $item['total'] - $item['paid'];
                    endforeach;
                endforeach;
                $return[$qkey]['listdata'] = array_values($result);
            endforeach;
            #===========================================================================================================

			return $return;
		}
		
		function getCountData() {
			return $this->total;
		}
	}// end class
 }
?>