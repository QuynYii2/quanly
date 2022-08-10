<?php
 if (!defined("QUOTATION_DAO_INC")) {
	define("QUOTATION_DAO_INC",1);
	
	class QuotationDao extends DbConnect /*DbMySQLConnect*/  {
		var $_id;
        var $_genre;
        var $_hotel;
        var $_publish;
        var $_process;
        var $_paid;

		function __construct($publish = 0) {
			$cid = Generals::getVar('cid', array());
			$this->setState();
            $this->setPublish($publish);
			$this->setId($cid[0]);
			parent::__construct();
		}
		
		function setId($id) {
			$this->_id	= $id;
		}

        function setPublish($publish) {
            $this->_publish = $publish;
        }

        function setProcess($process) {
            $this->_process = $process;
        }

        function setPaid($paid) {
            $this->_paid = $paid;
        }

        function setGenre($genre) {
            $this->_genre = $genre;
        }

        function setHotel($hotel) {
            $this->_hotel = $hotel;
        }

        function setState() {
			if (!is_null(Generals::getVar('filter_search'))) 	Generals::setState('filter.search', strtolower(trim(Generals::getVar('filter_search'))));
            if (!is_null(Generals::getVar('filter_code'))) 	    Generals::setState('filter.code', strtolower(trim(Generals::getVar('filter_code'))));
            if (!is_null(Generals::getVar('filter_name'))) 	    Generals::setState('filter.name', strtolower(trim(Generals::getVar('filter_name'))));
			if (!is_null(Generals::getVar('filter_published'))) Generals::setState('filter.published', Generals::getVar('filter_published'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));

            if (!is_null(Generals::getVar('filter_agency')))    Generals::setState('filter.agency', Generals::getVar('filter_agency'));
            if (!is_null(Generals::getVar('filter_supplier')))  Generals::setState('filter.supplier', Generals::getVar('filter_supplier'));
            if (!is_null(Generals::getVar('filter_departure'))) Generals::setState('filter.departure', Generals::getVar('filter_departure'));
            if (!is_null(Generals::getVar('filter_location')))  Generals::setState('filter.location', Generals::getVar('filter_location'));
            if (!is_null(Generals::getVar('filter_billno')))    Generals::setState('filter.billno', Generals::getVar('filter_billno'));

            if (!is_null(Generals::getVar('filter_f_date'))) Generals::setState('filter.f_date', Generals::getVar('filter_f_date'));
            if (!is_null(Generals::getVar('filter_t_date'))) Generals::setState('filter.t_date', Generals::getVar('filter_t_date'));
		}
		
		function getState() {
			return array(
							'filter_search'     => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
                            'filter_code'       => Generals::getState('filter.code'), 'filter_name' => Generals::getState('filter.name'),
							'orderby_order'     => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field'),
                            'filter_agency'     => Generals::getState('filter.agency'), 'filter_supplier' => Generals::getState('filter.supplier'),
                            'filter_departure'  => Generals::getState('filter.departure'), 'filter_location' => Generals::getState('filter.location'),
                            'filter_f_date'     => Generals::getState('filter.f_date'), 'filter_t_date' => Generals::getState('filter.t_date'),
                            'filter_billno'     => Generals::getState('filter.billno')
						);
		}

        function getMaxCode() {
            $maxid = $this->getMaxId('tbl_quotation', 'id');
            return sprintf("%05d", $maxid+1);
        }

        function getStatisticalAgency($agency, $year){
            $query = ' SELECT DATE_FORMAT(departure, "%m") AS month, SUM(total) AS total FROM tbl_quotation ';
            $query.= ' WHERE agency = ? AND DATE_FORMAT(departure, "%Y") = ? ';
            $query.= ' GROUP BY DATE_FORMAT(departure, "%Y%m") ORDER BY DATE_FORMAT(departure, "%Y%m") ';

            $this->prepare($query);
            $result = $this->exec(array($agency, $year));
            $return = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0);

            if (is_array($result)) foreach ($result as $items):
                $return[(int)$items['month']] = $items['total'];
            endforeach;

            return $return;
        }

        function getExistedCode($code, $void) {
            $query = 'SELECT COUNT(a.id) AS count FROM tbl_quotation AS a WHERE TRIM(LOWER(a.code)) = ? AND a.id <> ? ';
            $this->prepare($query);
            $result = $this->exec(array(trim(strtolower($code)), (int)$void));

            return $result[0]['count'];
        }

        function getPaymentTypes(){
            $query = ' SELECT a.id, a.code, b.name FROM tbl_payment AS a ';
            $query.= ' LEFT JOIN tbl_payment_lang AS b ON a.id = b.payment';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE b.language = ? ORDER BY a.code, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getSupplierAmount($qid = null){
            $query = ' SELECT qu.id, qu.pricefor, qu.paxrange, js.service, js.`profile`, sv.name AS pr_name, js.detail, SUM(js.price) AS price, SUM(js.perpax) AS perpax, SUM(js.single) AS single, js.markup, js.detailname AS dt_name, ';
            $query.= ' (Select Sum(price) From tbl_quotation_payment As qp Where qp.quotation = qu.id And qp.service = js.service And qp.detail = js.detail) AS paid, sp.id AS supid, sp.name AS sp_name ';
            $query.= ' FROM tbl_journey_service AS js INNER JOIN tbl_quotation AS qu ON qu.id = js.quotation ';
            $query.= ' INNER JOIN tbl_service AS sr ON sr.id = js.`profile` ';
            $query.= ' INNER JOIN tbl_service_lang AS sv ON sv.service = js.`profile` ';
            $query.= ' INNER JOIN tbl_supplier_lang AS sp ON sp.supplier = sr.supplier ';
            $query.= ' LEFT JOIN tbl_language AS lg ON lg.`code` = sv.`language` AND lg.`code` = sp.language ';
            $query.= ' WHERE lg.code = ? '.($qid ? ' AND js.quotation = '.(int)$qid : '').' GROUP BY js.detail, sr.genre ORDER BY sp_name ';

            $this->prepare($query);
            $totals = $this->exec(array(Generals::getSession('langcode', 'vn')));
            $result = array();
            if (is_array($totals)) foreach ($totals as $key => $items):
                if ($items['service'] == 'hotel' && $items['pricefor'] == 2): # Single Room
                    #$items['total'] = $items['perpax']*$items['paxrange'];
                    $items['total'] = $items['single']*$items['paxrange'];
                elseif ($items['service'] == 'hotel' && ($items['pricefor'] || $items['paxrange'] == 1)):
                    $items['total'] = $items['perpax']*floor($items['paxrange']/2)*2 + $items['single'];
                else:
                    $service = $this->getDetailService($items['service'], $items['detail']);
                    if (empty($service['perpax'])):
                        $items['total'] = $items['price'];
                    else:
                        $items['total'] = $items['perpax']*$items['paxrange'];
                    endif;
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

            return $result;
        }

        function _getSupplierAmount($qid = null){
            $query = ' SELECT qu.id, qu.pricefor, qu.paxrange, js.service, js.`profile`, sv.name AS pr_name, js.detail, SUM(js.price) AS price, SUM(js.perpax) AS perpax, SUM(js.single) AS single, js.markup, js.detailname AS dt_name, ';
            $query.= ' (Select Sum(price) From tbl_quotation_payment As qp Where qp.quotation = qu.id And qp.service = js.service And qp.detail = js.detail) AS paid, sp.id AS supid, sp.name AS sp_name ';
            $query.= ' FROM tbl_journey_service AS js INNER JOIN tbl_quotation AS qu ON qu.id = js.quotation ';
            $query.= ' INNER JOIN tbl_service AS sr ON sr.id = js.`profile` ';
            $query.= ' INNER JOIN tbl_service_lang AS sv ON sv.service = js.`profile` ';
            $query.= ' INNER JOIN tbl_supplier_lang AS sp ON sp.supplier = sr.supplier ';
            $query.= ' LEFT JOIN tbl_language AS lg ON lg.`code` = sv.`language` ';
            $query.= ' WHERE lg.code = ? '.($qid ? ' AND js.quotation = '.(int)$qid : '').' GROUP BY js.detail ORDER BY sp_name ';
            $this->prepare($query);
            $totals = $this->exec(array(Generals::getSession('langcode', 'vn')));
            $result = array();
            if (is_array($totals)) foreach ($totals as $key => $items):
                if ($items['service'] == 'hotel' && $items['pricefor'] == 2):
                    $items['total'] = $items['perpax']*$items['paxrange'];
                elseif ($items['service'] == 'hotel' && ($items['pricefor'] || $items['paxrange'] == 1)):
                    $items['total'] = $items['perpax']*floor($items['paxrange']/2)*2 + $items['single'];
                else:
                    $service = $this->getDetailService($items['service'], $items['detail']);
                    if (empty($service['perpax'])):
                        $items['total'] = $items['price'];
                    else:
                        $items['total'] = $items['perpax']*$items['paxrange'];
                    endif;
                endif;
                $items['unpaid'] = $items['total'] - $items['paid'];
                $totals[$key] = $items;
            endforeach;
            /**
            if (is_array($totals)) foreach ($totals as $key => $items):
            if ($items['service'] == 'hotel' && $items['pricefor'] == 2):
            $items['total'] = ($items['perpax']+$items['perpax']*$items['markup']/100)*$items['paxrange'];
            elseif ($items['service'] == 'hotel' && ($items['pricefor'] || $items['paxrange'] == 1)):
            $items['total'] = ($items['perpax']+$items['perpax']*$items['markup']/100)*floor($items['paxrange']/2)*2 + $items['single']+$items['single']*$items['markup']/100;
            else:
            $items['total'] = ($items['perpax']+$items['perpax']*$items['markup']/100)*$items['paxrange'];
            endif;
            $items['unpaid'] = $items['total'] - $items['paid'];
            $totals[$key] = $items;
            endforeach;
             **/

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

            return $result;
        }

        function getPayments($genre = 0, $qid = null){
            $qid = $qid ? $qid : $this->_id;
            if (empty($genre)):
                $query = 'SELECT a.* FROM tbl_quotation_payment AS a WHERE a.genre = ? AND a.quotation = ? ORDER BY id DESC';
                $this->prepare($query);
                return $this->exec(array($genre, $qid));
            else:
                $result = $this->getSupplierAmount($qid);
                foreach ($result as $key => $payment):
                    foreach ($payment['value'] as $i => $items):
                        $query = 'SELECT a.* FROM tbl_quotation_payment AS a WHERE a.genre = ? AND a.quotation = ? AND a.service = ? AND a.detail = ? ORDER BY id DESC';
                        $this->prepare($query);
                        $result[$key]['value'][$i]['payment'] = $this->exec(array($genre, $qid, $items['service'], $items['detail']));
                    endforeach;
                endforeach;

                return $result;
            endif;
        }

        function getChangeLogs($qid = null, $genre = 0){
            $query = ' SELECT a.*, b.name AS user_name FROM tbl_quotation_log AS a ';
            $query.= ' INNER JOIN tbl_user_lang AS b ON a.userid = b.user ';
            $query.= ' WHERE b.language = ? AND a.genre = ? ';
            if ($qid) $query.= ' AND a.quotation = '.(int)$qid;
            $query.= ' ORDER BY a.id DESC ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode', 'vn'), $genre));

            return $result;
        }

        function getJourneies($id = null){
            $id = $id ? $id : $this->_id;
            $query = 'SELECT a.* FROM tbl_journey AS a WHERE a.quotation = ? ORDER BY id ASC';
            $this->prepare($query);
            $result = $this->exec(array($id));

            if (is_array($result)) foreach ($result as $key => $items):
                $query = 'SELECT a.* FROM tbl_journey_service AS a WHERE a.quotation = ? AND journey = ? ORDER BY id ASC';
                $this->prepare($query);
                $result[$key]['services'] = $this->exec(array($id, $items['id']));
            endforeach;

            return $result;
        }

        function getAgencies($ids = null){
            $query = ' SELECT a.id, a.code, a.phone, a.address, b.name, c.name AS l_name FROM tbl_agency AS a ';
            $query.= ' LEFT JOIN tbl_agency_lang AS b ON a.id = b.agency';
            $query.= ' LEFT JOIN tbl_location_lang AS c ON a.location = c.location AND c.language = b.language ';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            if ($ids):
                if ($ids == '0,,0') $ids = 0;
                $query.= ' WHERE b.language = ? AND a.id IN ('.$ids.') ';
            else:
                $query.= ' WHERE b.language = ? ';
            endif;
            $query.= ' ORDER BY c.name, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getSuppliers(){
            $query = ' SELECT a.id, a.code, a.phone, b.name, c.name AS l_name FROM tbl_supplier AS a ';
            $query.= ' LEFT JOIN tbl_supplier_lang AS b ON a.id = b.supplier';
            $query.= ' LEFT JOIN tbl_location_lang AS c ON a.location = c.location AND c.language = b.language ';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE b.language = ? ORDER BY c.name, b.name ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode')));

            return is_array($result) ? $result : array();
        }

        function getLocations($id = null, $title = null){
            $param = array(Generals::getSession('langcode'));
            $query = ' SELECT a.id, a.code, b.name, c.name AS c_name FROM tbl_location AS a ';
            $query.= ' LEFT JOIN tbl_location_lang AS b ON a.id = b.location';
            $query.= ' LEFT JOIN tbl_country AS c ON a.country = c.id ';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? ';
            if (!empty($id)):
                $query.= ' AND a.id = ? ';
                array_push($param, (int)$id);
            endif;
            if (strlen($title)):
                $query.= ' AND UPPER(b.name) LIKE ? ';
                array_push($param, '%'.strtoupper(trim($title)).'%');
            endif;
            $query.= ' ORDER BY a.country, b.name ';

            $this->prepare($query);
            $result = $this->exec($param);

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
            $return['subtour']  = Generals::getTitle('MENU_SERVICE_SUBTOUR');

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

        function getServiceName($id){
            $query = ' SELECT b.name FROM tbl_service AS a ';
            $query.= ' LEFT JOIN tbl_service_lang AS b ON a.id = b.service ';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND a.id = ? ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode'), $id));

            return $result[0]['name'];
        }

        function getServices($genre = null, $location = null, $title = null){
            $_lang = Generals::getSession('langcode');
            $param = array($_lang, $_lang, $_lang);
            $query = ' SELECT a.id, a.code, a.supplier, a.location, b.name, c.name AS su_name, d.name AS lo_name FROM tbl_service AS a ';
            $query.= ' LEFT JOIN tbl_service_lang AS b ON a.id = b.service ';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' LEFT JOIN tbl_supplier_lang AS c ON a.supplier = c.supplier ';
            $query.= ' LEFT JOIN tbl_location_lang AS d ON a.location = d.location ';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND c.language = ? AND d.language = ? ';
            if ($genre):
                $query.= ' AND a.genre = ? ';
                array_push($param, $genre);
            endif;
            if ($location):
                $query.= ' AND a.location = ? ';
                array_push($param, (int)$location);
            endif;
            if (strlen($title)):
                $query.= ' AND UPPER(b.name) LIKE ? ';
                array_push($param, '%'.strtoupper(trim($title)).'%');
            endif;
            $query.= ' ORDER BY c.name, b.name ';

            $this->prepare($query);
            $result = $this->exec($param);

            return is_array($result) ? $result : array();
        }

        function getSubServiceCode($genre, $id){
            $genre = strtolower($genre);
            $query = ' SELECT a.code FROM tbl_'.$genre.' AS a ';
            $query.= ' LEFT JOIN tbl_'.$genre.'_lang AS b ON a.id = b.'.$genre;
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND a.id = ? ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode'), $id));

            return $result[0]['code'];
        }

        function getSubServiceMarkup($genre, $id){
            $genre = strtolower($genre);
            $query = ' SELECT a.margin FROM tbl_'.$genre.' AS a ';
            $query.= ' LEFT JOIN tbl_'.$genre.'_lang AS b ON a.id = b.'.$genre;
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND a.id = ? ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode'), $id));

            return $result[0]['margin'];
        }

        function getSubServiceName($genre, $id){
            $genre = strtolower($genre);
            $query = ' SELECT b.name FROM tbl_'.$genre.' AS a ';
            $query.= ' LEFT JOIN tbl_'.$genre.'_lang AS b ON a.id = b.'.$genre;
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND a.id = ? ';

            $this->prepare($query);
            $result = $this->exec(array(Generals::getSession('langcode'), $id));

            return $result[0]['name'];
        }

        function getSubServices($genre, $parent, $arrival = null, $paxgroup = null, $title = null){
            $param = array(Generals::getSession('langcode'), $parent);
            $genre = strtolower($genre);
            $query = ' SELECT a.id, a.code, b.name FROM tbl_'.$genre.' AS a ';
            $query.= ' LEFT JOIN tbl_'.$genre.'_lang AS b ON a.id = b.'.$genre;
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' LEFT JOIN tbl_season AS s ON s.id = a.season ';
            $query.= ' WHERE a.published = 1 AND b.language = ? AND a.service = ? ';
            if ($arrival):
                $query.= ' AND DATE_FORMAT(s.startdate, "%Y%m%d") <= ? ';
                $query.= ' AND DATE_FORMAT(s.enddate, "%Y%m%d") >= ? ';
                array_push($param, date('Ymd', strtotime($arrival)), date('Ymd', strtotime($arrival)));
            endif;
            if ($paxgroup):
                $query.= ' AND a.paxranges LIKE ? ';
                array_push($param, '%'.(int)$paxgroup.'%');
            endif;
            if (strlen($title)):
                $query.= ' AND UPPER(b.name) LIKE ? ';
                array_push($param, '%'.strtoupper(trim($title)).'%');
            endif;
            $query.= ' ORDER BY b.name ';

            $this->prepare($query);
            $result = $this->exec($param);

            return is_array($result) ? $result : array();
        }

        function getDetailService($genre, $id){
            if (empty($genre)) return array();

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

        function getPerson($id){
            $query = ' SELECT a.id, a.code, a.minpax, a.maxpax, a.divpax, b.name FROM tbl_person AS a ';
            $query.= ' LEFT JOIN tbl_person_lang AS b ON a.id = b.person';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
            $query.= ' WHERE a.id = ? AND a.published = 1 AND b.language = ? ORDER BY a.ordering ';

            $this->prepare($query);
            $result = $this->exec(array($id, Generals::getSession('langcode')));

            return !empty($result[0]) ? $result[0] : array();
        }

        function getPersons(){
            $query = ' SELECT a.id, a.code, a.minpax, a.maxpax, a.divpax, b.name FROM tbl_person AS a ';
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
		
		function getData($id = null) {
            $id = $id ? $id : $this->_id;
			$query = ' SELECT a.*, uc.name AS create_name FROM tbl_quotation AS a ';
            $query.= ' INNER JOIN tbl_user_lang AS uc ON a.create_by = uc.user AND uc.language = ? ';
            $query.= ' WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode'), $id));

			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_quotation AS a ';
			$query.= ' INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation WHERE b.language = ? AND a.genre = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode'), $this->_genre));
			
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
            $code 	    = Generals::getState('filter.code');
            $code 	    = JString::strtolower($code);
            $name 	    = Generals::getState('filter.name');
            $name 	    = JString::strtolower($name);
			$published 	= Generals::getState('filter.published');
			$language	= Generals::getSession('langcode');

            $agency	    = Generals::getState('filter.agency');
            $supplier	= Generals::getState('filter.supplier');
            $departure	= Generals::getState('filter.departure');
            $location	= Generals::getState('filter.location');
            $billno	    = Generals::getState('filter.billno');

            $f_date	    = Generals::getState('filter.f_date');
            $t_date	    = Generals::getState('filter.t_date');

			$params 	= array($language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
            if (strpos($billno, '"') !== false) $billno = str_replace(array('=', '<'), '', $billno);

			$where[] = ' b.language = ? ';

			if (strlen($search)):
				$where[] = ' (LOWER(a.code) LIKE ? OR LOWER(b.name) LIKE ?) ';
				array_push($params, '%'.strtolower($search).'%', '%'.strtolower($search).'%');
			endif;

            if (strlen($code)):
                $where[] = ' LOWER(a.code) LIKE ? ';
                array_push($params, '%'.strtolower($code).'%');
            endif;

            if (strlen($name)):
                $where[] = ' LOWER(b.name) LIKE ? ';
                array_push($params, '%'.strtolower($name).'%');
            endif;

            if (strlen($this->_publish) || strlen($published)):
				if ($this->_publish) $where[] = ' a.published > 0 ';
                else $where[] = ' a.published = 0 ';
				#array_push($params, $this->_publish);
			endif;

            if (strlen($agency)):
                $where[] = ' a.agencies LIKE "%,'.$agency.',%" ';
            endif;

            if (strlen($supplier)):
                $_condition = ' Select Distinct js.quotation From tbl_journey_service As js ';
                $_condition.= ' Inner Join tbl_service As se On js.profile = se.id ';
                $_condition.= ' Where se.supplier = ? And se.genre = js.service ';
                $where[] = ' a.id IN('.$_condition.') ';
                array_push($params, $supplier);
            endif;
            if (strlen($departure)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") = ? ';
                array_push($params, date('Ymd', strtotime($departure)));
            endif;
            if (strlen($f_date)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") >= ? ';
                array_push($params, date('Ymd', strtotime($f_date)));
            endif;
            if (strlen($t_date)):
                $where[] = ' DATE_FORMAT(a.departure, "%Y%m%d") <= ? ';
                array_push($params, date('Ymd', strtotime($t_date)));
            endif;
            if (strlen($location)):
                $where[] = ' a.id IN(Select quotation From tbl_journey_service Where location = ? ) ';
                array_push($params, $location);
            endif;
            if (strlen($billno)):
                $where[] = ' a.id IN(Select quotation From tbl_quotation_payment Where LOWER(billno) LIKE ? ) ';
                array_push($params, '%'.strtolower($billno).'%');
            endif;

            if (strlen($this->_hotel)):
                if (empty($this->_hotel)):
                    $where[] = ' a.id IN (Select Distinct quotation From tbl_journey_service Where service = "hotel" And quotation = a.id) ';
                else:
                    $where[] = ' a.id IN (Select Distinct quotation From tbl_journey_service Where service = "hotel" And quotation = a.id) ';
                endif;
            endif;

			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, b.name, b.alias, b.introtext, uc.name AS create_name FROM tbl_quotation AS a ';
			$query.= ' LEFT JOIN tbl_quotation_lang AS b ON a.id = b.quotation';
            $query.= ' LEFT JOIN tbl_user_lang AS uc ON a.create_by = uc.user AND b.language = uc.language ';
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

            if (is_array($result[$page])) foreach ($result[$page] as $key => $items):
                if ($items['departure']):
                    $items['start_date'] = date('Y-m-d', strtotime($items['departure']));
                    $items['end_date'] = date('Y-m-d', strtotime('+'.((int)$items['numday']-1).' day', strtotime($items['departure'])));
                endif;
                $result[$page][$key] = $items;
            endforeach;

			return $result[$page];
		}
		
		function getCountData() {
			return $this->total;
		}
		
		function setStatus($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_quotation SET status = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}

        function setMoney($val, $keys) {
            if (is_array($keys)) foreach ($keys as $key):
                $this->prepare(' UPDATE tbl_quotation SET money = '.(int)$val.' WHERE id = '.(int)$key);
                $this->exec(array());
            endforeach;
        }

        function published($val, $keys) {
            if (is_array($keys)) foreach ($keys as $key):
                #=======================================================================
                # Write Log Change Data ================================================
                #=======================================================================
                $this->prepare(" SELECT * FROM tbl_quotation WHERE id = ".(int)$key);
                $result    = $this->execRun(array());
                $_user     = Generals::getUserData();
                $published = array(0 => Generals::getTitle('OPTION_0'), 1 => Generals::getTitle('OPTION_1'), 2 => Generals::getTitle('OPTION_2'), 3 => Generals::getTitle('OPTION_3'), 4 => Generals::getTitle('OPTION_4'));

                $introtext = 'Update Table: tbl_quotation<br/>';
                $introtext.= 'Where id = '.$key.'<br/>';
                $introtext.= '<p>'.$published[$result[0]['published']].' To '.$published[$val].'</p>';

                $logdata = array();
                $logdata['title']       = 'Update Table: tbl_quotation';
                $logdata['introtext']   = $introtext;
                $logdata['create_on']   = date('Y-m-d H:i:s');
                $logdata['create_by']   = $_user['id'];
                $this->insert_db('tbl_writelog', $logdata);
                #=======================================================================
                #=======================================================================

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
			#$this->delete_db('tbl_quotation', " id = ? ", array($oid));
			#$this->delete_db('tbl_quotation_lang', " quotation = ? ", array($oid));

            #=======================================================================
            # Write Log Change Data ================================================
            #=======================================================================
            $this->prepare(" SELECT * FROM tbl_quotation WHERE id = ".(int)$oid);
            $result    = $this->execRun(array());
            $_user     = Generals::getUserData();
            $old_datas = array();
            foreach ($result[0] as $key => $value ){
                $old_datas[] = $key.' = '.$value;
            }

            $introtext = 'Delete Table: tbl_quotation<br/>';
            $introtext.= 'Where id = '.$oid.'<br/>';
            $introtext.= 'Old Datas:';
            $introtext.= '<p>'.implode('<br/>', $old_datas).'</p>';

            $logdata = array();
            $logdata['title']       = 'Delete Table: tbl_quotation';
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