<?php
class controler {
	var $_db;
    var $_module;
    var $_permission;

	function __construct() {
		$this->controler();
        $this->_module = Generals::getVar('option', 'index');
        $this->_permission = Generals::getSession('permission');
	}

	function controler(){
		$this->_db = new InvoiceDao();
	}

	function display() {
        $view = Generals::getVar('view', 'list');
        if (empty($this->_permission['mod_simple'][$this->_module]) && $view == 'list'):
            Generals::redirect('index.php?option=permission');
        elseif (empty($this->_permission['mod_update'][$this->_module]) && $view == 'form'):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        if ($view == 'list') Generals::setState('data.form', null);
	}

	function ordering() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $orders = Generals::getVar('order', array());
		if (is_array($orders)) foreach ($orders as $key => $val) $this->_db->ordering($val, $key);
		Generals::setError(Generals::getTitle('ORDERING_SUCCESS'));
		Generals::redirect('index.php?option=invoice&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option=invoice&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        header('Location: index.php?option=invoice&view=form');
		return false;
	}

	function publish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(1, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('PUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=invoice&view=list');
	}

	function unpublish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=invoice&view=list');
	}

	function trash() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(-1, $cid[0]);
	}
	
	function apply() { $this->save('apply'); }
	
	function update() { $this->save('save'); }

    function export() { $this->save('export'); }

	function save($task) {
		require_once LIB_PATH."joomla".DS."filesystem".DS."folder.php";
		require_once LIB_PATH."joomla".DS."filesystem".DS."file.php";
		
		$vform 	= Generals::getVar('vform', array());
        $intro 	= Generals::getVar('intro', array());
		$cid	= Generals::getVar('cid', array(0));
		$user	= Generals::getUserData();

		$vform['id']        = (int)$cid[0];
        $vform["issued_on"] = $vform["issued_on"] ? date('Y-m-d H:i:s', strtotime($vform["issued_on"])) : date('Y-m-d H:i:s');
        $vform["due_date"]  = $vform["due_date"] ? date('Y-m-d H:i:s', strtotime($vform["due_date"])) : date('Y-m-d H:i:s');
        $vform["fperiod"]   = $vform["fperiod"] ? date('Y-m-d H:i:s', strtotime($vform["fperiod"])) : date('Y-m-d H:i:s');
        $vform["tperiod"]   = $vform["tperiod"] ? date('Y-m-d H:i:s', strtotime($vform["tperiod"])) : date('Y-m-d H:i:s');

		if (!$vform['id']):
			$vform["create_on"] = date('Y-m-d H:i:s');
			$vform["create_by"] = $user['id'];
		else:
			$vform["update_on"] = date('Y-m-d H:i:s');
			$vform["update_by"] = $user['id'];
		endif;

        if (empty($this->_permission['mod_create'][$this->_module]) && empty($vform['id'])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        elseif (empty($this->_permission['mod_update'][$this->_module]) && !empty($vform['id'])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        if (is_array($vform['quotation'])) foreach ($vform['quotation'] as $qid):
            if ($this->_db->getExistInvoice($qid, (int)$vform['id'])):
                Generals::setState('data.form', $vform);
                Generals::setWarning(Generals::getTitle('INV_QUOTECODE_EXIST'));
                Generals::redirect('index.php?option='.$this->_module.'&view=form&cid[]='. (int)$vform['id']);
            endif;
        endforeach;

        if ($vform["remimage"]):
			$DiskPath = IMG_PATH.DS."invoice";
			if (JFile::exists($DiskPath.DS.Generals::getVar('hiddenimage'))):
				$thumbnail = str_replace('image', 'thumb', Generals::getVar('hiddenimage'));
				if (JFile::exists($DiskPath.DS.Generals::getVar('hiddenimage'))) JFile::delete($DiskPath.DS.Generals::getVar('hiddenimage'));
				if (JFile::exists($DiskPath.DS.'resize'.DS.$thumbnail)) JFile::delete($DiskPath.DS.'resize'.DS.$thumbnail);
			endif;
			
			$vform['image'] = '';
			$vform['thumb'] = '';
		endif;
		
		$file_image = $_FILES['image'];
		
		if($file_image['name'] != '')  {
			if (!Generals::addFile('image', null, IMG_PATH.DS."invoice")) {
				$link= 'index.php?option=invoice&view=form&cid[]='. $vform['id'];
				Generals::redirect($link); return;
			} else {
				$vform['image'] = Generals::getVar('image');
				$vform['thumb'] = str_replace('image', 'thumb', $vform['image']);
				JFolder::create(IMG_PATH.DS."invoice".DS.'resize');
				ImageClass::createImage(IMG_PATH.DS."invoice".DS.$vform['image'], IMG_PATH.DS."invoice".DS.'resize'.DS.$vform['thumb'], 100, 100);
			}
		}
		$data = $this->_db->getDataMapTable($vform, 'tbl_invoice');
        $data['payment'] = (int)$data['payment'];
        $data['introtext'] = array();
        if (is_array($vform['quotation'])) foreach ($vform['quotation'] as $qid):
            if ($qid):
                $query = ' SELECT a.*, b.name, (Select sum(price) From tbl_quotation_payment AS p Where p.quotation = a.id AND p.genre = 0) AS paid FROM tbl_quotation AS a ';
                $query.= ' INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation ';
                $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language WHERE a.id = ? AND b.language = ? ';

                $this->_db->prepare($query);
                $result = $this->_db->exec(array($qid, Generals::getSession('langcode')));
                $result[0]['paid'] = floatval($result[0]['paid']);
                $data['introtext'][] = $result[0];
            endif;
        endforeach;
        $data['introtext'] = json_encode(array('quote' => $data['introtext'], 'intro' => $intro));

		$this->_db->beginTrans();
		try {
			if ($data['id']):
				$this->_db->update_db('tbl_invoice', $data, ' id = ? ', array($data['id']));
			else:
				$this->_db->insert_db('tbl_invoice', $data);
			endif;
	        
			$this->_db->commitTrans();
			
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
			$vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
			if ($data['frontend']) $this->_db->frontend(1, array($vform['id']));
	        if ($task == "save"):
	        	$link = 'index.php?option=invoice&view=list';
	        elseif ($task == "apply"):
	        	$link = 'index.php?option=invoice&view=form&cid[]='. $vform['id'] .'';
	        endif;
		} catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option=invoice&view=form&cid[]='. $vform['id'] .'';
			Generals::redirect($link);
		}
		#----------------------------------------------------------------
        #----------------------------------------------------------------
        if ($task == 'export'):
            Generals::getError();
            $this->export2PDF($data);
            exit();
        endif;
        #----------------------------------------------------------------
        #----------------------------------------------------------------

        Generals::setState('data.form', null);
		Generals::redirect($link);
	}
	
	function cancel() {
		Generals::redirect('index.php?option=invoice&view=list');
	}

    function getAgency(){
        $query = ' SELECT a.*, b.name FROM tbl_agency AS a ';
        $query.= ' LEFT JOIN tbl_agency_lang AS b ON a.id = b.agency ';
        $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
        $query.= ' WHERE a.published = 1 AND a.id = ? AND b.language = ? ';

        $this->_db->prepare($query);
        $result = $this->_db->exec(array(Generals::getVar('aid'), Generals::getSession('langcode')));
        $result = $result[0];

        echo json_encode($result);
        exit();
    }

    function getAccounting(){
        $result = $this->_db->getQuotations(Generals::getVar('aid'));
        $option = '<option value="">'.Generals::getTitle('INV_QUOTATION_SELECT').'</option>\n';
        if (is_array($result)) foreach ($result as $items):
            $option.= '<option value="'.$items['id'].'">'.$items['code'].' - '.$items['name'].'</option>\n';
        endforeach;
        echo $option;
        exit();
    }

    function getQuotation(){
        $query = ' SELECT a.*, (Select sum(price) From tbl_quotation_payment AS p Where p.quotation = a.id AND p.genre = 0) AS paid, b.name ';
        $query.= ' FROM tbl_quotation AS a INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation INNER JOIN tbl_language AS l ON l.code = b.language ';
        $query.= ' WHERE a.id = ? AND l.code = ? ';
        $this->_db->prepare($query);
        $result = $this->_db->exec(array(Generals::getVar('qid'), Generals::getSession('langcode')));
        $result = $result[0];
        $result['departure'] = date('d-m-Y', strtotime($result['departure']));
        $result['arrival']   = date('d-m-Y', strtotime('+'.$result['numday'].' days', strtotime($result['departure'])));

        echo json_encode($result);
        exit();
    }

    function getJouneyService($data) {
        $query = ' SELECT b.name, q.pricefor ';
        $query.= ' FROM tbl_journey_service AS a INNER JOIN tbl_hotel_lang AS b ON a.detail = b.hotel ';
        $query.= ' INNER JOIN tbl_quotation AS q ON q.id = a.quotation ';
        $query.= ' WHERE a.service = "hotel" AND a.quotation = ? AND b.language = ? ORDER BY a.id ASC ';

        $this->_db->prepare($query);
        $result = $this->_db->exec(array($data['quotation'], Generals::getSession('langcode')));
        $return = array();

        foreach ($result as $key => $items):
            $return[$key]['name']       = $items['name'];
            if ($items['pricefor'] == 2):
                $return[$key]['single']     = $data['paxno'];
                $return[$key]['double']     = 0;
                $return[$key]['extrabed']   = 0;
            elseif ($items['pricefor'] == 1):
                $return[$key]['single']     = 1;
                $return[$key]['double']     = floor($data['paxno']/2);
                $return[$key]['extrabed']   = 0;
            else:
                $return[$key]['single']     = 0;
                $return[$key]['double']     = floor($data['paxno']/2);
                $return[$key]['extrabed']   = 1;
            endif;
        endforeach;

        return $return;
    }

    function export2PDF($data) {
        $config = Generals::getConfig();
        $cficon = Generals::getCfIcon();
        $payment = $this->_db->getPayments();
        foreach ($payment as $items):
            if ($data['payment'] == $items['id']) $data['payment'] = $items['name'];
        endforeach;

        $html = '';
        $html.= '<table style="width: 100%;border-collapse: 5px;border-spacing: 5px;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><img src="'.JURI::root().'data/images/logo-with-address.jpg" /></td>';
        $html.= '   </tr>';
        $html.= '</table>';
        $html.= '<h1 style="text-align: center;">'.Generals::getTitle('INV_EXPORT_TITLE').'</h1>';
        $html.= '<table style="width: 100%;border-collapse: 5px;border-spacing: 5px;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_INVOINO').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['invoiceno'].'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_ISSUED_ON').':</strong> </td>';
        $html.= '       <td valign="top">'.date('d-F-Y', strtotime($data['issued_on'])).'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_MANAGER').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['manager'].'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_ISSUED_BY').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['issued_by'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_REFERNO').':</strong> </td>';
        $html.= '       <td valign="top"></td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_PRERIOD').':</strong> </td>';
        $html.= '       <td valign="top">'.date('d-F-Y', strtotime($data['fperiod'])).' - '.date('d-F-Y', strtotime($data['tperiod'])).'</td>';
        $html.= '   </tr>';
        $html.= '</table>';
        $html.= '<br/>';
        $html.= '<table style="width: 100%;border-collapse: 5px;border-spacing: 5px;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_COMPANY').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['company'].'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_NIGHT').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['night'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_ADDRESS').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['address'].'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_PAYMENT').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['payment'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td colspan="2" valign="top"><strong>'.Generals::getTitle('INV_PHONE').':</strong> '.$data['phone'].'   <strong>'.Generals::getTitle('INV_FAXNO').':</strong> '.$data['faxno'].'   <strong>'.Generals::getTitle('INV_EMAIL').':</strong> '.$data['email'].'   <strong>'.Generals::getTitle('INV_WEBSITE').':</strong> '.$data['website'].'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_DUE_DATE').':</strong> </td>';
        $html.= '       <td valign="top">'.date('d-F-Y', strtotime($data['due_date'])).'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('INV_GUEST').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['guest'].'</td>';
        $html.= '   </tr>';
        $html.= '</table>';
        $html.= '<br/>';
        $html.= '<table style="width: 100%;border-spacing: 0;border-collapse: 0;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;"><strong>'.Generals::getTitle('INV_ITEM_DESC').'</strong></td>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;"><strong>'.Generals::getTitle('INV_ITEM_NIGHT').'</strong></td>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;"><strong>'.Generals::getTitle('INV_ITEM_QTY').'</strong></td>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;"><strong>'.Generals::getTitle('INV_ITEM_PRICE').'</strong></td>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;"><strong>'.Generals::getTitle('INV_ITEM_CURRENCY').'</strong></td>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;border-right: #000000 1px solid;padding: 3px 5px;"><strong>'.Generals::getTitle('INV_ITEM_AMOUNT').'</strong></td>';
        $html.= '   </tr>';

        $introtext = $data['introtext'] ? json_decode($data['introtext'], true) : array();
        $totals = 0;
        $paid   = 0;
        foreach ($introtext['quote'] as $items):
            $paid+= $items['paid'];
            $html.= '   <tr>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;">'.$items['name'].'</td>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;">'.$items['numday'].'</td>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;">1</td>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;">'.number_format($items['total'], 2, '.', '').'</td>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;">USD</td>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;border-right: #000000 1px solid;padding: 3px 5px;">'.number_format($items['total'], 2, '.', '').'</td>';
            $html.= '   </tr>';
            $totals+= $items['total'];
        endforeach;
        $intros = null;
        if (is_array($introtext['intro']['intro'])) foreach ($introtext['intro']['intro'] as $key => $items):
            $intros[] = '<p>'.$items.': <strong>'.number_format($introtext['intro']['amount'][$key], 2, '.', ',').'USD</strong></p>';
        endforeach;

        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="6" align="right" style="border-top: #000000 1px solid;">&nbsp;&nbsp;</td>';
        $html.= '   </tr>';
        /**
        $html.= '   <tr>';
        $html.= '       <td valign="top" rowspan="6">'.implode('', $intros).'</td>';
        $html.= '       <td valign="top" colspan="3" align="right"><strong>'.Generals::getTitle('INV_BEFORE_VAT').':</strong> </td>';
        $html.= '       <td valign="top">USD</td>';
        $html.= '       <td valign="top">'.number_format($totals, 2, '.', '').'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="3" align="right"><strong>'.Generals::getTitle('INV_VAT').':</strong> </td>';
        $html.= '       <td valign="top">USD</td>';
        $html.= '       <td valign="top">'.number_format(0, 2, '.', '').'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="3" align="right"><strong>'.Generals::getTitle('INV_TOTAL').':</strong> </td>';
        $html.= '       <td valign="top">USD</td>';
        $html.= '       <td valign="top">'.number_format($totals, 2, '.', '').'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="5" align="right">&nbsp;&nbsp;</td>';
        $html.= '   </tr>';
        **/
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="3" align="right"><strong>'.Generals::getTitle('INV_RECEIVABLE').':</strong> </td>';
        $html.= '       <td valign="top">USD</td>';
        $html.= '       <td valign="top">'.number_format($paid, 2, '.', '').'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="3" align="right"><strong>'.Generals::getTitle('INV_BALANCE').':</strong> </td>';
        $html.= '       <td valign="top">USD</td>';
        $html.= '       <td valign="top">'.number_format($totals, 2, '.', '').'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="6" align="right">&nbsp;&nbsp;</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="6">'.$data['awords'].'</td>';
        $html.= '   </tr>';
        $html.= '</table>';

        $foot = '<table style="width: 100%;border-collapse: 5px;border-spacing: 5px;">';
        $foot.= '   <tr>';
        $foot.= '       <td valign="top"><img src="'.JURI::root().'data/images/footer-address.jpg" /></td>';
        $foot.= '   </tr>';
        $foot.= '</table>';

        $mpdf = new mPDF ();
        $mpdf->WriteHTML ( $html );
        $mpdf->SetHTMLFooter($foot);
        $mpdf->Output ( 'invoice_'.$data['invoiceno'].'.pdf', 'D' );

        exit();
    }

    function export2excel($data){
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("PHPExcel Test Document")
            ->setSubject("PHPExcel Test Document")
            ->setDescription("Test document for PHPExcel, generated using PHP classes.")
            ->setKeywords("office PHPExcel php")
            ->setCategory("Test result file");

        # Line First Empty
        $first = 5;
        $next  = 5;
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->getDefaultStyle()->applyFromArray(array('font'=>array('size'=>10)));

        $sheet->setCellValue('A'.$next, Generals::getTitle('EXP_ATTN'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('EXPORT_FROM'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('EXPORT_TO'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('EXPORT_SERVIE'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('EXPORT_CODE'));
        $sheet->setCellValue('F'.$next, Generals::getTitle('EXPORT_INTRO'));
        $sheet->setCellValue('G'.$next, Generals::getTitle('EXPORT_PRICE'));
        $sheet->setCellValue('H'.$next, Generals::getTitle('EXPORT_PERPAX'));
        $sheet->setCellValue('I'.$next, Generals::getTitle('EXPORT_SINGLE'));
        $sheet->freezePaneByColumnAndRow(0, $next+1);
        $sheet->getStyle('A'.$next.':I'.$next)->applyFromArray(array('font' => array('bold' => true)));

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$quotation['code'].'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
?>