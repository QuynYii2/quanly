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
		$this->_db = new GuiderDao();
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
		Generals::redirect('index.php?option=guider&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option=guider&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        header('location: index.php?option=guider&view=form');
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
		Generals::redirect('index.php?option=guider&view=list');
	}

	function unpublish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=guider&view=list');
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

    function saveas() { $this->save('copy'); }

    function bulk() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=bulk');
        endif;

        $DataUser = Generals::getUserData();
        $DataBulk = Generals::getVar('bulk', array());
        $IsSearch = $this->_db->getState();
        $DataList = $this->_db->getDataList(true);

        foreach ($IsSearch as $key => $val):
            if (strlen($val)):
                #$DataList = $this->_db->getDataList(true);
                break;
            endif;
        endforeach;

        if ($DataBulk['type']):
            $this->_db->beginTrans();
            try {
                foreach ($DataList as $items):
                    $object = array();
                    $object['bulk_price']   = (float)$DataBulk['price'];
                    $object['bulk_price']   = $DataBulk['unit'] ? $object['bulk_price']*100/$items['cost'] : $object['bulk_price'];;
                    $object['bulk_unit']    = (int)$DataBulk['unit'];
                    $object["update_on"]    = date('Y-m-d H:i:s');
                    $object["update_by"]    = $DataUser['id'];
                    $object['margin']       = $object['bulk_price'];
                    $this->_db->update_db('tbl_'.$this->_module, $object, ' id = ? ', array($items['id']));
                endforeach;

                $this->_db->commitTrans();
                Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
            } catch (Exception $ex) {
                $this->_db->rollbackTrans();
                Generals::setError(Generals::getTitle('UPDATE_EROR'));
            }
        endif;
        Generals::redirect('index.php?option='.$this->_module.'&view=bulk');
    }

    function save($task) {
		$vform 	= Generals::getVar('vform', array());
		$cid	= Generals::getVar('cid', array(0));
		$user	= Generals::getUserData();
        $service = $this->_db->getServices($vform['service']);

        $vform['id'] = $task == "copy" ? 0 : (int)$cid[0];
        $vform['margin']    = (float)$vform['margin'];
        $vform['perpax']    = (int)$vform['perpax'];
        $vform['supplier']  = $service[0]['supplier'];
        $vform['location']  = $service[0]['location'];
        $vform['paxranges'] = !empty($vform['paxranges']) ? ','.implode(',', $vform['paxranges']).',' : null;
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

        #=========================================================================================
        # Check Existed Code =====================================================================
        #=========================================================================================
        if ($this->_db->getExistedCode($vform['code'], (int)$vform['id'], $vform['service'])):
            if ($task == "copy"):
                $vform['code']  = $vform['code'].'_'.date('YmdHis');
            else:
                Generals::setState('data.form', $vform);
                Generals::setWarning(Generals::getTitle('CODE_EXISTED'));
                Generals::redirect('index.php?option='.$this->_module.'&view=form&cid[]='. (int)$vform['id']);
            endif;
        endif;
        #=========================================================================================
		# File thumbnail social ==================================================================
		#=========================================================================================
        if ($vform["remsocial"] && $task != 'copy'):
			$DiskPath = IMG_PATH.DS."guider";
			if (JFile::exists($DiskPath.DS.Generals::getVar('hiddensocial'))) JFile::delete($DiskPath.DS.Generals::getVar('hiddensocial'));
			$vform['social'] = '';
		endif;
		
		$file_social = $_FILES['social'];
		
		if($file_social['name'] != '')  {
			if (!Generals::addFile('social', 'normal', IMG_PATH.DS."guider")) {
				$link= 'index.php?option=guider&view=form&cid[]='. $vform['id'];
				Generals::redirect($link); return;
			} else {
				$vform['social'] = Generals::getVar('social');
			}
        } elseif ($task == 'copy' && strlen(trim(Generals::getVar('hiddensocial')))) {
            $vform['social'] = date('YmdHis').'_'.Generals::getVar('hiddensocial');
            JFile::copy(IMG_PATH.DS."guider".DS.Generals::getVar('hiddensocial'), IMG_PATH.DS."guider".DS.$vform['social']);
        }
		#=========================================================================================
		#=========================================================================================
		$data = $this->_db->getDataMapTable($vform, 'tbl_guider');

		$this->_db->beginTrans();
		try {
			if ($data['id']):
				$this->_db->update_db('tbl_guider', $data, ' id = ? ', array($data['id']));
			else:
				$this->_db->insert_db('tbl_guider', $data);
			endif;
	        
			$this->_db->commitTrans();
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
			$vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
            if ($task == "save" || $task == "copy"):
	        	$link = 'index.php?option=guider&view=list';
	        elseif ($task == "apply"):
	        	$link = 'index.php?option=guider&view=form&cid[]='. $vform['id'] .'';
	        endif;
		} catch (Exception $ex) {
			$this->_db->rollbackTrans();
            print_r($ex);die;
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option=guider&view=form&cid[]='. $vform['id'] .'';
			Generals::redirect($link);
		}
		#----------------------------------------------------------------
        #----------------------------------------------------------------
        $_name 	= array_values($vform["name"]);
        $_intro	= array_values($vform["introtext"]);
        $_tags	= array_values($vform["tags"]);
        $_meta	= array_values($vform["meta"]);
        
        $this->_db->beginTrans();
        try {
	        if (is_array($vform["name"])) foreach ($vform["name"] as $lang => $name):
	        	$data = array();
                $data['id']			= $task == "copy" ? 0 : (int)$vform['oldid'][$lang];
	        	$data['guider'] 	= $vform['id'];
	        	$data['language']	= $lang;
	        	$data['name']		= $name ? $name : $_name[0];
	        	$data['alias']		= JFilterOutput::stringURLSafe(Generals::getCovertVn($data['name']));
	        	$data['introtext']	= $vform["introtext"][$lang] ? $vform["introtext"][$lang] : $_intro[0];
	        	$data['tags']		= $vform["tags"][$lang] ? $vform["tags"][$lang] : $_tags[0];
	        	$data['meta']		= $vform["meta"][$lang] ? $vform["meta"][$lang] : $_meta[0];
	        	if ($data['id']):
					$this->_db->update_db('tbl_guider_lang', $data, ' id = ? ', array($data['id']));
				else:
					$this->_db->insert_db('tbl_guider_lang', $data);
				endif;
        	endforeach;
        	$this->_db->commitTrans();
        } catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option=guider&view=form&cid[]='. $vform['id'] .'';
			Generals::redirect($link);
		}
        #----------------------------------------------------------------
        #----------------------------------------------------------------
        Generals::setState('data.form', null);
		Generals::redirect($link);
	}
	
	function cancel() {
		Generals::redirect('index.php?option=guider&view=list');
	}

    function export1() {
        $field = Generals::getState('orderby.field', 'a.id') ? Generals::getState('orderby.field', 'a.id') : 'a.id';
        $order = Generals::getState('orderby.order', 'DESC') ? Generals::getState('orderby.order', 'DESC') : 'DESC';
        $query = $this->_db->_buildQuery();
        $query.= ' ORDER BY '.$field.' '.$order;
        $this->_db->prepare($query);
        $result = $this->_db->exec(Generals::getState('params'));

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

        $sheet->setCellValue('D1', strtoupper(Generals::getTitle('MENU_'.strtoupper($this->_module))).' - COSTING')->mergeCells('D1:G'.($next-2));
        $sheet->getStyle('D1:G'.($next-2))->applyFromArray(array('font' => array('size' => 20), 'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)));

        $sheet->setCellValue('A'.$next, Generals::getTitle('FIELD_ID'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('LOCATION'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('CODE'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('SERVICE'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('TITLE'));
        $sheet->setCellValue('F'.$next, Generals::getTitle('SEASON'));
        $sheet->setCellValue('G'.$next, Generals::getTitle('COST'));
        $sheet->getStyle('A'.$next.':G'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $next++;

        if (is_array($result)) foreach ($result as $items):
            $sheet->setCellValue('A'.$next, $items['id']);
            $sheet->setCellValue('B'.$next, $items['lo_name']);
            $sheet->setCellValue('C'.$next, $items['code']);
            $sheet->setCellValue('D'.$next, $items['sv_name']);
            $sheet->setCellValue('E'.$next, $items['name']);
            $sheet->setCellValue('F'.$next, $items['se_name']);
            $sheet->setCellValue('G'.$next, $items['cost'])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G'.$next.':G'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $next++;
        endforeach;

        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
        endforeach;
        $sheet->calculateColumnWidths();
        #$sheet->freezePane( "E".($first+1) );

        $sheet->setTitle(Generals::getTitle('MENU_'.strtoupper($this->_module)).' - Costing');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.strtolower($this->_module).'_cost_'.date('Ymd').'.xlsx"');
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

    function export2() {
        $field = Generals::getState('orderby.field', 'a.id') ? Generals::getState('orderby.field', 'a.id') : 'a.id';
        $order = Generals::getState('orderby.order', 'DESC') ? Generals::getState('orderby.order', 'DESC') : 'DESC';
        $query = $this->_db->_buildQuery();
        $query.= ' ORDER BY '.$field.' '.$order;
        $this->_db->prepare($query);
        $result = $this->_db->exec(Generals::getState('params'));

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

        $sheet->setCellValue('D1', strtoupper(Generals::getTitle('MENU_'.strtoupper($this->_module))).' - SELLING')->mergeCells('D1:G'.($next-2));
        $sheet->getStyle('D1:G'.($next-2))->applyFromArray(array('font' => array('size' => 20), 'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)));

        $sheet->setCellValue('A'.$next, Generals::getTitle('FIELD_ID'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('LOCATION'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('CODE'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('SERVICE'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('TITLE'));
        $sheet->setCellValue('F'.$next, Generals::getTitle('SEASON'));
        $sheet->setCellValue('G'.$next, Generals::getTitle('COST'));
        $sheet->getStyle('A'.$next.':G'.$next)->applyFromArray(array('font' => array('bold' => true)));
        $next++;

        if (is_array($result)) foreach ($result as $items):
            $sheet->setCellValue('A'.$next, $items['id']);
            $sheet->setCellValue('B'.$next, $items['lo_name']);
            $sheet->setCellValue('C'.$next, $items['code']);
            $sheet->setCellValue('D'.$next, $items['sv_name']);
            $sheet->setCellValue('E'.$next, $items['name']);
            $sheet->setCellValue('F'.$next, $items['se_name']);
            $sheet->setCellValue('G'.$next, $items['cost'] + $items['cost']*$items['margin']/100)->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G'.$next.':G'.$next)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $next++;
        endforeach;

        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
        endforeach;
        $sheet->calculateColumnWidths();
        #$sheet->freezePane( "E".($first+1) );

        $sheet->setTitle(Generals::getTitle('MENU_'.strtoupper($this->_module)).' - Selling');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.strtolower($this->_module).'_sell_'.date('Ymd').'.xlsx"');
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