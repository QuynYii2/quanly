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
		$this->_db = new ExpGuideDao();
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
		Generals::redirect('index.php?option=expguide&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option=expguide&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        header('Location: index.php?option=expguide&view=form');
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
		Generals::redirect('index.php?option=expguide&view=list');
	}

	function unpublish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=expguide&view=list');
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
        $vform["issued_on"] = $vform["issued_on"] ? date('Y-m-d H:i:s', strtotime($vform["issued_on"])) : date('Y-m-d H:i:s');
        $vform["s_date"]    = $vform["s_date"] ? date('Y-m-d H:i:s', strtotime($vform["s_date"])) : date('Y-m-d H:i:s');
        $vform["e_date"]    = $vform["e_date"] ? date('Y-m-d H:i:s', strtotime($vform["e_date"])) : date('Y-m-d H:i:s');
        $vform["fa_date"]   = $vform["fa_date"] ? date('Y-m-d H:i:s', strtotime($vform["fa_date"])) : date('Y-m-d H:i:s');
        $vform["fd_date"]   = $vform["fd_date"] ? date('Y-m-d H:i:s', strtotime($vform["fd_date"])) : date('Y-m-d H:i:s');

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
			$DiskPath = IMG_PATH.DS."expguide";
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
			if (!Generals::addFile('image', null, IMG_PATH.DS."expguide")) {
				$link= 'index.php?option=expguide&view=form&cid[]='. $vform['id'];
				Generals::redirect($link); return;
			} else {
				$vform['image'] = Generals::getVar('image');
				$vform['thumb'] = str_replace('image', 'thumb', $vform['image']);
				JFolder::create(IMG_PATH.DS."expguide".DS.'resize');
				ImageClass::createImage(IMG_PATH.DS."expguide".DS.$vform['image'], IMG_PATH.DS."expguide".DS.'resize'.DS.$vform['thumb'], 100, 100);
			}
		}
		$data = $this->_db->getDataMapTable($vform, 'tbl_export');
        $data['hotel'] = $this->getJouneyService($data);
        $data['hotel'] = json_encode($data['hotel']);

		$this->_db->beginTrans();
		try {
			if ($data['id']):
				$this->_db->update_db('tbl_export', $data, ' id = ? ', array($data['id']));
			else:
				$this->_db->insert_db('tbl_export', $data);
			endif;
	        
			$this->_db->commitTrans();
			
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
			$vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
			if ($data['frontend']) $this->_db->frontend(1, array($vform['id']));
	        if ($task == "save"):
	        	$link = 'index.php?option=expguide&view=list';
	        elseif ($task == "apply"):
	        	$link = 'index.php?option=expguide&view=form&cid[]='. $vform['id'] .'';
	        endif;
		} catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option=expguide&view=form&cid[]='. $vform['id'] .'';
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
		Generals::redirect('index.php?option=expguide&view=list');
	}

    function getQuotation(){
        $query = ' SELECT a.*, q.code, b.name, DATE_FORMAT(q.departure, "%d-%m-%Y") AS s_date, q.numday ';
        $query.= ' FROM tbl_service AS a LEFT JOIN tbl_service_lang AS b ON a.id = b.service';
        $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language ';
        $query.= ' INNER JOIN tbl_journey_service AS js ON a.id = js.profile ';
        $query.= ' INNER JOIN tbl_quotation AS q ON js.quotation = q.id ';
        $query.= ' WHERE a.genre = "guider" AND js.service = "guider" AND js.quotation = ? ';
        $query.= ' AND b.language = ? ORDER BY js.id ASC LIMIT 1 ';

        $this->_db->prepare($query);
        $result = $this->_db->exec(array(Generals::getVar('qid'), Generals::getSession('langcode')));
        $result = $result[0];
        $result['e_date'] = date('d-m-Y', strtotime('+'.$result['numday'].' day', strtotime($result['s_date'])));

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
                $return[$key]['single']     = $data['paxno']%2;
                $return[$key]['double']     = floor($data['paxno']/2);
                $return[$key]['extrabed']   = 0;
            else:
                $return[$key]['single']     = 0;
                $return[$key]['double']     = floor($data['paxno']/2);
                $return[$key]['extrabed']   = $data['paxno']%2;
            endif;
        endforeach;

        return $return;
    }

    function export2PDF($data) {
        $config = Generals::getConfig();
        $cficon = Generals::getCfIcon();

        $html = '';
        $html.= '<table style="width: 100%;border-collapse: 5px;border-spacing: 5px;">';
        $html.= '   <tr>';
        $html.= '       <td valign="top"><img src="'.JURI::root().'data/images/logo-with-address.jpg"/></td>'; # style="width: 300px;"
        #$html.= '       <td valign="top"><img src="'.JURI::root().'data/images/export-name.png" style="width: 300px;"/><br/>'.$config['config:intro'].'</td>';
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