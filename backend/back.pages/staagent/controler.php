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
		$this->_db = new StaAgentDao();
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
        $DataList 	 = $this->_db->getDataList(1000000);
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
        $sheet = $objPHPExcel->setActiveSheetIndex(0)->setTitle('Sumamry');
        $sheet->getDefaultStyle()->applyFromArray(array('font'=>array('size'=>10, 'name'=>'tahoma')));

        $sheet->setCellValue('A'.$next, Generals::getTitle('STA_QUOTATION'));
        $sheet->setCellValue('B'.$next, Generals::getTitle('STA_AGENT'));
        $sheet->setCellValue('C'.$next, Generals::getTitle('STA_CREATE'));
        $sheet->setCellValue('D'.$next, Generals::getTitle('STA_PRICE'));
        $sheet->setCellValue('E'.$next, Generals::getTitle('STA_MARKUP'));
        $sheet->setCellValue('F'.$next, Generals::getTitle('STA_NOTE'));
        $sheet->freezePaneByColumnAndRow(0, $next+1);
        $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => '003990')), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>'EAEC50'))));
        $next++;

        foreach ($DataList as $qid => $quotation):
            $quotation['total']     = number_format($quotation['total'], 2, '.', ',');
            $quotation['profit']    = number_format($quotation['profit'], 2, '.', ',');
            $quotation['paid']      = number_format($quotation['paid'], 2, '.', ',');
            $quotation['markup']    = number_format($quotation['markup'], 2, '.', ',');

            $sheet->setCellValue('A'.$next, $quotation['title']);
            $sheet->setCellValue('B'.$next, $quotation['agent']);
            $sheet->setCellValue('C'.$next, Generals::getTitle('STA_TOTAL').': '.$quotation['total'].' - '.Generals::getTitle('STA_PROFIT').': '.$quotation['profit'].' - '.Generals::getTitle('STA_PAID_TOTAL').': '.$quotation['paid'].' - '.Generals::getTitle('STA_PAID_MARKUP').': '.$quotation['markup']);
            $sheet->getStyle('A'.$next.':F'.$next)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>'DFF0D8'))));
            $objPHPExcel->getActiveSheet()->mergeCells('C'.$next.':F'.$next);
            $next++;

            foreach ($quotation['value'] as $items):
                $sheet->setCellValue('C'.$next, date('d-F-Y', strtotime($items['create_on'])));
                $sheet->setCellValue('D'.$next, number_format($items['price'], 2, '.', ','))->getStyle('G'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('E'.$next, number_format($items['markup'], 2, '.', ','))->getStyle('H'.$next)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->setCellValue('F'.$next, $items['introtext']);
                $sheet->getStyle('D'.$next.':E'.$next)->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)));
                $next++;
            endforeach;
            $next++;
        endforeach;

        foreach (range('A', $sheet->getHighestDataColumn()) as $col):
            $sheet->getColumnDimension($col)->setAutoSize(true);
        endforeach;
        $sheet->calculateColumnWidths();
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="agents_statistic.xlsx"');
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