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
		$this->_db = new RptGuideDao();
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
		Generals::redirect('index.php?option=rptguide&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option=rptguide&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        header('Location: index.php?option=rptguide&view=form');
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
		Generals::redirect('index.php?option=rptguide&view=list');
	}

	function unpublish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=rptguide&view=list');
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
		$cid	= Generals::getVar('cid', array(0));
		$user	= Generals::getUserData();
		
		$vform['id']        = (int)$cid[0];
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

        if ($vform["remimage"]):
			$DiskPath = IMG_PATH.DS."rptguide";
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
			if (!Generals::addFile('image', null, IMG_PATH.DS."rptguide")) {
				$link= 'index.php?option=rptguide&view=form&cid[]='. $vform['id'];
				Generals::redirect($link); return;
			} else {
				$vform['image'] = Generals::getVar('image');
				$vform['thumb'] = str_replace('image', 'thumb', $vform['image']);
				JFolder::create(IMG_PATH.DS."rptguide".DS.'resize');
				ImageClass::createImage(IMG_PATH.DS."rptguide".DS.$vform['image'], IMG_PATH.DS."rptguide".DS.'resize'.DS.$vform['thumb'], 100, 100);
			}
		}
		$data = $this->_db->getDataMapTable($vform, 'tbl_report');
        $data['introtext'] = serialize($data['introtext']);

		$this->_db->beginTrans();
		try {
			if ($data['id']):
				$this->_db->update_db('tbl_report', $data, ' id = ? ', array($data['id']));
			else:
				$this->_db->insert_db('tbl_report', $data);
			endif;
	        
			$this->_db->commitTrans();
			
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
			$vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
			if ($data['frontend']) $this->_db->frontend(1, array($vform['id']));
	        if ($task == "save"):
	        	$link = 'index.php?option=rptguide&view=list';
	        elseif ($task == "apply"):
	        	$link = 'index.php?option=rptguide&view=form&cid[]='. $vform['id'] .'';
	        endif;
		} catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option=rptguide&view=form&cid[]='. $vform['id'] .'';
			Generals::redirect($link);
		}
		#----------------------------------------------------------------
        #----------------------------------------------------------------
        if ($task == 'export'):
            Generals::getError();
            $this->export2excel($data);
            exit();
        endif;
        #----------------------------------------------------------------
        #----------------------------------------------------------------

        Generals::setState('data.form', null);
		Generals::redirect($link);
	}
	
	function cancel() {
		Generals::redirect('index.php?option=rptguide&view=list');
	}

    function getQuotation(){
        $query = ' SELECT a.code, a.paxrange, DATE_FORMAT(a.departure, "%d-%m-%Y") AS s_date, a.journeis, ';
        $query.= ' j.numday, j.f_name, j.t_name, al.name AS agent_name ';
        $query.= ' FROM tbl_quotation AS a INNER JOIN tbl_journey AS j ON a.id = j.quotation ';
        $query.= ' LEFT JOIN tbl_agency_lang AS al ON al.agency = a.agency ';
        $query.= ' LEFT JOIN tbl_language AS l ON l.code = al.language ';
        $query.= ' WHERE a.id = ? AND al.language = ? ORDER BY j.numday ASC ';

        $this->_db->prepare($query);
        $result = $this->_db->exec(array(Generals::getVar('qid'), Generals::getSession('langcode')));
        if (is_array($result)) foreach ($result as $key => $items):
            $result[$key]['e_date'] = date('d-m-Y', strtotime('+'.(count($result)-1).' day', strtotime($items['s_date'])));
            $result[$key]['n_date'] = date('d-F', strtotime('+'.$key.' day', strtotime($items['s_date'])));
        endforeach;

        echo json_encode($result);
        exit();
    }

    function export2PDF($data) {
        $config = Generals::getConfig();
        $cficon = Generals::getCfIcon();

        $html = '';
        $html.= '<table style="width: 100%;border-collapse: 5px;border-spacing: 5px;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><img src="'.JURI::root().'data/images/export-logo.png" style="width: 300px;"/></td>';
        $html.= '       <td valign="top"><img src="'.JURI::root().'data/images/export-name.png" style="width: 300px;"/><br/>'.$config['config:intro'].'</td>';
        $html.= '   </tr>';
        $html.= '</table>';
        $html.= '<h1 style="text-align: center;">'.Generals::getTitle('EXP_GENRE_1').'</h1>';
        $html.= '<table style="width: 100%;border-collapse: 5px;border-spacing: 5px;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_ATTN').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['attn'].'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_VOUCHER').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['voucher'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_GUIDE').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['guide'].'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_ISSUED_BY').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['issued_by'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_ADDRESS').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['address'].'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_ISSUED_ON').':</strong> </td>';
        $html.= '       <td valign="top">'.date('d-F-Y', strtotime($data['issued_on'])).'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"></td>';
        $html.= '       <td valign="top" colspan="3">'.Generals::getTitle('EXP_PHONE').': '.$data['phone'].' '.Generals::getTitle('EXP_FAXNO').': '.$data['faxno'].' '.Generals::getTitle('EXP_EMAIL').': '.$data['phone'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="4"><strong>'.Generals::getTitle('EXP_GNAME').':</strong> '.$data['g_name'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="4"><strong>'.Generals::getTitle('EXP_GUEST').':</strong> '.$data['guest'].'</td>';
        $html.= '   </tr>';
        $html.= '</table>';
        $html.= '<br/>';
        $html.= '<table style="width: 100%;border-collapse: 5px;border-spacing: 5px;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_PAXNO').':</strong> </td>';
        $html.= '       <td valign="top" colspan="5">'.$data['paxno'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_S_DATE').':</strong> </td>';
        $html.= '       <td valign="top">'.date('d-F-Y', strtotime($data['s_date'])).'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_FA_DATE').':</strong> </td>';
        $html.= '       <td valign="top">'.date('d-F-Y', strtotime($data['fa_date'])).'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_FA_TIME').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['fa_time'].'</td>';
        $html.= '   </tr>';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_E_DATE').':</strong> </td>';
        $html.= '       <td valign="top">'.date('d-F-Y', strtotime($data['e_date'])).'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_FD_DATE').':</strong> </td>';
        $html.= '       <td valign="top">'.date('d-F-Y', strtotime($data['fd_date'])).'</td>';
        $html.= '       <td valign="top"><strong>'.Generals::getTitle('EXP_FD_TIME').':</strong> </td>';
        $html.= '       <td valign="top">'.$data['fd_time'].'</td>';
        $html.= '   </tr>';
        $html.= '</table>';
        $html.= '<br/>';
        $html.= '<table style="width: 100%;border-spacing: 0;border-collapse: 0;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;"><strong>'.Generals::getTitle('EXP_ROOM_CAT').'</strong></td>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;text-align: center;"><strong>'.Generals::getTitle('EXP_ROOM_SINGLE').'</strong></td>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;text-align: center;"><strong>'.Generals::getTitle('EXP_ROOM_DOUBLE').'</strong></td>';
        $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;border-right: #000000 1px solid;padding: 3px 5px;text-align: center;"><strong>'.Generals::getTitle('EXP_ROOM_EXTRA').'</strong></td>';
        $html.= '   </tr>';

        $hotels = $data['hotel'] ? json_decode($data['hotel']) : array();
        foreach ($hotels as $items):
            $html.= '   <tr>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;">'.$items->name.'</td>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;text-align: center;">'.$items->single.'</td>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;padding: 3px 5px;text-align: center;">'.$items->double.'</td>';
            $html.= '       <td valign="top" style="border-top: #000000 1px solid;border-left: #000000 1px solid;border-right: #000000 1px solid;padding: 3px 5px;text-align: center;">'.$items->extrabed.'</td>';
            $html.= '   </tr>';
        endforeach;
        $html.= '   <tr>';
        $html.= '       <td valign="top" colspan="4" align="right" style="border-top: #000000 1px solid;"></td>';
        $html.= '   </tr>';
        $html.= '</table>';


        $mpdf = new mPDF ();
        $mpdf->WriteHTML ( $html );
        $mpdf->Output ( 'guide_voucher_'.$data['voucher'].'.pdf', 'D' );

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
        $first = 8;
        $next  = 8;
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->getDefaultStyle()->applyFromArray(array('font'=>array('size'=>10, 'name'=>'tahoma')));

        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName("VisitAsia");
        $objDrawing->setDescription("VisitAsia");
        $objDrawing->setPath(DATA_PATH.'images'.DS.'export-logo.png');
        $objDrawing->setHeight(100);
        $objDrawing->setCoordinates('A1');
        $objDrawing->setWorksheet($sheet);

        $sheet->setCellValue('C1', Generals::getTitle('RPT_FORM_TITLE'))->mergeCells('C1:F'.($next-2));
        $sheet->getStyle('C1:F'.($next-2))->applyFromArray(array('font' => array('size' => 20), 'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)));

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_AGENCY').':');
        $sheet->setCellValue('B'.$next, $data['agency']);
        $sheet->setCellValue('C'.$next, Generals::getTitle('RPT_TELEX').':')->mergeCells('C'.$next.':E'.$next);
        $sheet->setCellValue('F'.$next, $data['telex'])->getStyle('F'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $next++;

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_PNRNO').':');
        $sheet->setCellValue('B'.$next, $data['pnrno'])->getStyle('B'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->setCellValue('C'.$next, Generals::getTitle('RPT_PHONE').':')->mergeCells('C'.$next.':E'.$next);
        $sheet->setCellValue('F'.$next, $data['phone'])->getStyle('F'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $next++;

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_PAXNO').':');
        $sheet->setCellValue('B'.$next, $data['paxno']);
        $sheet->setCellValue('C'.$next, Generals::getTitle('RPT_CREATE').':')->mergeCells('C'.$next.':E'.$next);
        $sheet->setCellValue('F'.$next, date('d/m/Y', strtotime($data['create_on'])));
        $next++;

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_PERIOD').':');
        $sheet->setCellValue('B'.$next, date('d/m/Y', strtotime($data['fperiod'])).' - '.date('d/m/Y', strtotime($data['tperiod'])));
        $next++;

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_GUIDE').':');
        $sheet->setCellValue('B'.$next, $data['guide']);
        $next++;

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_OWNER').':');
        $sheet->setCellValue('B'.$next, $data['owner']);
        $next++;

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_PLATE').':');
        $sheet->setCellValue('B'.$next, $data['plate'])->mergeCells('B'.$next.':F'.$next);
        $sheet->getStyle('A'.$first.':A'.$next)->applyFromArray(array('font' => array('bold' => true), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
        $sheet->getStyle('B'.$first.':B'.$next)->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)));
        $sheet->getStyle('C'.$first.':E'.$next)->applyFromArray(array('font' => array('bold' => true), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
        $next++;$next++;

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_ITEM_DATE'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_ITEM_DESC'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('RPT_ITEM_PAX'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('RPT_ITEM_CASH'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('RPT_ITEM_ACTUAL'));
        $sheet->setCellValue('F'.$next, Generals::getTitle('RPT_ITEM_ENTRANCE'));
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('font' => array('bold' => true), 'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $start = $next;
        $next++;$next++;

        $introtext = unserialize($data['introtext']);
        $total1 = $total2 = 0;
        $sum1   = array($next);
        $sum2   = array($next);
        if (is_array($introtext['numday'])) foreach ($introtext['numday'] as $key => $numday):
            $sheet->setCellValue('A'.$next, $numday);
            $sheet->setCellValue('B'.$next, $introtext['plates'][$key]);
            $next++;

            if (is_array($introtext['intro'][$key])) foreach ($introtext['intro'][$key] as $i => $intro):
                $sheet->setCellValue('B'.$next, $intro);
                $sheet->setCellValue('C'.$next, $introtext['paxno'][$key][$i]);
                $sheet->setCellValue('D'.$next, $introtext['cash'][$key][$i])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('E'.$next, $introtext['actual'][$key][$i])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('D'.$next.':E'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                if (is_array($introtext['entrance'][$key][$i])):
                    foreach ($introtext['entrance'][$key][$i] as $entrance):
                        $sheet->setCellValue('F'.$next, $entrance);
                        $next++;
                    endforeach;
                else:
                    $next++;
                endif;

                $total1+= $introtext['cash'][$key][$i];
                $total2+= $introtext['actual'][$key][$i];
            endforeach;
        endforeach;
        array_push($sum1, $next-1);

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $next++;

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_TOTAL').':');
        $sheet->setCellValue('D'.$next, '=SUM(D'.$sum1[0].':D'.$sum1[1].')')->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->setCellValue('F'.$next, Generals::getTitle('RPT_ITEM_RESTAURANT').':');
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $sheet->getStyle('D'.$next.':D'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $next++;

        if (is_array($introtext['restaurant'])):
            $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_EXTRA').':')->mergeCells('B'.$next.':D'.$next);
            $sheet->getStyle('B'.$next.':D'.$next)->applyFromArray(array('font' => array('bold' => true)));
            foreach ($introtext['restaurant'] as $key => $restaurant):
                $sheet->mergeCells('B'.$next.':D'.$next);
                $sheet->setCellValue('E'.$next, $introtext['res_price'][$key])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('F'.$next, $restaurant);
                $sheet->getStyle('E'.$next.':E'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $total2+= $introtext['res_price'][$key];
                $next++;
            endforeach;
        endif;
        array_push($sum2, $next-1);

        $sheet->getStyle('A'.$start.':A'.($next-1))->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.($next-1))->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.($next-1))->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.($next-1))->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.($next-1))->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.($next-1))->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_GRAND').':')->mergeCells('B'.$next.':D'.$next);
        $sheet->setCellValue('E'.$next, '=SUM(E'.$sum2[0].':E'.$sum2[1].')')->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('B'.$next.':E'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $sheet->getStyle('E'.$next.':E'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $next++;

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_CASH').':')->mergeCells('B'.$next.':D'.$next);
        $sheet->setCellValue('E'.$next, '=SUM(D'.$sum1[0].':D'.$sum1[1].')')->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $sheet->getStyle('E'.$next.':E'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $next++;

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_PAID').':')->mergeCells('B'.$next.':D'.$next);
        $sheet->setCellValue('E'.$next, '=SUM(E'.$sum2[0].':E'.$sum2[1].')')->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $sheet->getStyle('E'.$next.':E'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $next++;

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_REFUND').':')->mergeCells('B'.$next.':D'.$next);
        $sheet->setCellValue('E'.$next, '=IF(E'.($next-1).'>E'.($next-2).', E'.($next-1).'-E'.($next-2).', "-")')->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $sheet->getStyle('E'.$next.':E'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $next++;

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_VAT').':')->mergeCells('B'.$next.':D'.$next);
        $sheet->setCellValue('E'.$next, '=IF(E'.($next-3).'>E'.($next-2).', E'.($next-3).'-E'.($next-2).', "-")')->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $sheet->getStyle('E'.$next.':E'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $sheet->getStyle('A'.$start.':A'.$next)->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('B'.$start.':B'.$next)->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('C'.$start.':C'.$next)->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('D'.$start.':D'.$next)->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('E'.$start.':E'.$next)->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));
        $sheet->getStyle('F'.$start.':F'.$next)->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN))));

        $next++;$next++;$next++;

        $footer = Generals::getTitle('RPT_OFFICE').'            '.Generals::getTitle('RPT_SALES').'             '.Generals::getTitle('RPT_ACCOUNTING').'             ';
        $footer.= Generals::getTitle('RPT_OPERATION').'             '.Generals::getTitle('RPT_MANAGER').'             '.Generals::getTitle('RPT_REPORT');
        $sheet->setCellValue('A'.$next, $footer)->mergeCells('A'.$next.':F'.$next);
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        /**
        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_OFFICE'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_SALES'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('RPT_ACCOUNTING'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('RPT_OPERATION'));
        $sheet->setCellValue('F'.$next, Generals::getTitle('RPT_RECIEVER'));
        **/
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $next++;$next++;$next++;$next++;$next++;$next++;

        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
        endforeach;
        $sheet->calculateColumnWidths();
        $sheet->setTitle($data['pnrno']);
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$data['pnrno'].'.xlsx"');
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