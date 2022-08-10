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
		$this->_db = new RptSupplierDao();
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

	function export() { $this->export2excel(); }

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


    function export2excel(){
        require_once (LIB_PATH.DS.'phpexcel'.DS.'PHPExcel'.DS.'IOFactory.php');
        $DataList 	 = $this->_db->getDataSummary();
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("PHPExcel Test Document")
            ->setSubject("PHPExcel Test Document")
            ->setDescription("Test document for PHPExcel, generated using PHP classes.")
            ->setKeywords("office PHPExcel php")
            ->setCategory("Test result file");


        # Line First Empty
        $first = 1;
        $next  = 1;
        $sheet = $objPHPExcel->setActiveSheetIndex(0)->setTitle(Generals::getTitle('RPT_SUMMARY'));
        $sheet->getDefaultStyle()->applyFromArray(array('font'=>array('size'=>10, 'name'=>'tahoma')));

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_PAX'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_FILE_NO'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('RPT_CODE'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('RPT_PROFILE'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('RPT_DETAIL'));
        $sheet->setCellValue('F'.$next, Generals::getTitle('RPT_AMOUNT'));
        $sheet->setCellValue('G'.$next, Generals::getTitle('RPT_PAID'));
        $sheet->setCellValue('H'.$next, Generals::getTitle('RPT_BALANCE'));
        $sheet->setCellValue('I'.$next, Generals::getTitle('RPT_INVOICE'));
        $sheet->setCellValue('J'.$next, Generals::getTitle('RPT_CHECKIN'));
        $sheet->setCellValue('K'.$next, Generals::getTitle('RPT_CHECKOUT'));
        $sheet->setCellValue('L'.$next, Generals::getTitle('RPT_USER'));
        $sheet->setCellValue('M'.$next, Generals::getTitle('RPT_PAID_DATE'));
        $sheet->setCellValue('N'.$next, Generals::getTitle('RPT_PAY_TYPE'));
        $sheet->freezePaneByColumnAndRow(0, $next+1);
        $sheet->getStyle('A'.$next.':N'.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => '003990')), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>'EAEC50'))));
        $next++;

        foreach ($DataList as $key => $items):
            $items['total']  = number_format($items['total'], 2, '.', ',');
            $items['paid']   = number_format($items['paid'], 2, '.', ',');
            $items['unpaid'] = number_format($items['unpaid'], 2, '.', ',');

            $sheet->setCellValue('A'.$next, $items['paxrange']);
            $sheet->setCellValue('B'.$next, $items['file_no']);
            $sheet->setCellValue('C'.$next, $items['code']);
            $sheet->setCellValue('D'.$next, $items['sp_name']);
            $sheet->setCellValue('E'.$next, $items['dt_name']);
            $sheet->setCellValue('F'.$next, $items['total'])->getStyle('I'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('G'.$next, $items['paid'])->getStyle('G'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('H'.$next, $items['unpaid'])->getStyle('H'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('I'.$next, $items['invoice']);
            $sheet->setCellValue('J'.$next, date('d-F-Y', strtotime($items['check_in'])));
            $sheet->setCellValue('K'.$next, date('d-F-Y', strtotime($items['check_out'])));
            $sheet->setCellValue('L'.$next, $items['update_by']);
            $sheet->setCellValue('M'.$next, $items['paid_date'] ? date('d-F-Y', strtotime($items['paid_date'])) : '');
            $sheet->setCellValue('N'.$next, $items['pay_type']);
            $sheet->getStyle('F'.$next.':H'.$next)->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
            $next++;
        endforeach;

        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
        endforeach;
        $sheet->calculateColumnWidths();

        #==================================================================================
        $first = 1;
        $next  = 1;
        $objPHPExcel->createSheet();
        $sheet = $objPHPExcel->setActiveSheetIndex(1)->setTitle(Generals::getTitle('RPT_BY_TYPE'));
        $sheet->getDefaultStyle()->applyFromArray(array('font'=>array('size'=>10, 'name'=>'tahoma')));

        $services = array();
        foreach ($DataList as $items):
            $services[$items['code']]['code']   = $items['code'];
            $services[$items['code']]['total'] += $items['total'];
            $services[$items['code']]['paid']  += $items['paid'];
            $services[$items['code']]['unpaid']+= $items['unpaid'];
        endforeach;
        $services = array_values($services);
        
        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_NO'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_TYPE'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('RPT_AMOUNT'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('RPT_PAID'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('RPT_BALANCE'));
        $sheet->getStyle('A'.$next.':E'.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => '0000C6')), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>'F79646'))));
        $sheet->freezePaneByColumnAndRow(0, $next+1);
        $next++;

        if (is_array($services)) foreach ($services as $key => $items):
            $sheet->setCellValue('A'.$next, $key+1);
            $sheet->setCellValue('B'.$next, $items['code']);
            $sheet->setCellValue('C'.$next, number_format($items['total'], 2, '.', ','))->getStyle('D'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('D'.$next, number_format($items['paid'], 2, '.', ','))->getStyle('E'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('E'.$next, number_format($items['unpaid'], 2, '.', ','))->getStyle('F'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('C'.$next.':E'.$next)->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
            $next++;
        endforeach;
        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
        endforeach;
        $sheet->calculateColumnWidths();

        #==================================================================================
        $first = 1;
        $next  = 1;
        $objPHPExcel->createSheet();
        $sheet = $objPHPExcel->setActiveSheetIndex(2)->setTitle(Generals::getTitle('RPT_BY_NAME'));
        $sheet->getDefaultStyle()->applyFromArray(array('font'=>array('size'=>10, 'name'=>'tahoma')));

        $DataList = $this->_db->getDataSupplier();

        $sheet->setCellValue('A'.$next, Generals::getTitle('RPT_NO'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('RPT_PROFILE'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('RPT_AMOUNT'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('RPT_PAID'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('RPT_BALANCE'));
        $sheet->getStyle('A'.$next.':E'.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => '0000C6')), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>'F79646'))));
        $sheet->freezePaneByColumnAndRow(0, $next+1);
        $next++;

        if (is_array($DataList)) foreach ($DataList as $key => $items):
            $sheet->setCellValue('A'.$next, $key+1);
            $sheet->setCellValue('B'.$next, $items['title']);
            $sheet->setCellValue('C'.$next, number_format($items['total'], 2, '.', ','))->getStyle('D'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('D'.$next, number_format($items['paid'], 2, '.', ','))->getStyle('E'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('E'.$next, number_format($items['unpaid'], 2, '.', ','))->getStyle('F'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('C'.$next.':E'.$next)->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
            $next++;
        endforeach;

        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
        endforeach;
        $sheet->calculateColumnWidths();
        #=================================================================================
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="suppliers_report.xlsx"');
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