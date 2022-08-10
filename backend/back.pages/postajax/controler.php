<?php
class controler {
	var $_db;

	function __construct() {
		$this->controler();
	}

	function controler(){
	}

	function display() {
	}
	
	function postajax() {
	}

    function getDepartureDay(){
        $start  = Generals::getVar('start');
        $step   = Generals::getVar('step');

        echo date('d-m-Y', strtotime('+'.(int)$step.' day', strtotime($start)));
        exit();
    }

	function getRelated() {
		$this->_db = new ArticleDao();
		$related = Generals::getVar('related');
		$this->_db->setName($related);
		$datalist = $this->_db->getDataList();
		$datalist = is_array($datalist) ? $datalist : array();
		
		echo json_encode($datalist);
		exit();
	}
}
?>