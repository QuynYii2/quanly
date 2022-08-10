<?php
class controler {
	var $_db;

	function __construct() {
		$this->controler();
	}

	function controler(){
		$this->_db = new StateDao();
	}

	function display() {
	}
	
	function getCities() {
		$StateDao 	= new StateDao();
		$cities	 	= $StateDao->getCities(Generals::getVar('country'));
		$_html		= '';
		if (is_array($cities)) foreach ($cities as $city):
			$_html.= '<option id="'.$city['id'].'">'.$city['name'].'</option>';
		endforeach;
		echo $_html;
		
		exit();
	}

	function ordering() {
		$orders = Generals::getVar('order', array());
		if (is_array($orders)) foreach ($orders as $key => $val) $this->_db->ordering($val, $key);
		Generals::setError(Generals::getTitle('ORDERING_SUCCESS'));
		Generals::redirect('index.php?option=state&view=list');
	}

	function delete() {
		$cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option=state&view=list');
	}

	function create() {
		header('Location: index.php?option=state&view=form');
		return false;
	}

	function publish() {
		$cid = Generals::getVar('cid', array());
		$this->_db->published(1, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('PUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=state&view=list');
	}

	function unpublish() {
		$cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=state&view=list');
	}

	function trash() {
		$cid = Generals::getVar('cid', array());
		$this->_db->published(-1, $cid[0]);
	}
	
	function apply() { $this->save('apply'); }
	
	function update() { $this->save('save'); }
	
	function save($task) {
		require_once LIB_PATH."joomla".DS."filesystem".DS."folder.php";
		require_once LIB_PATH."joomla".DS."filesystem".DS."file.php";
		
		$vform 	= Generals::getVar('vform', array());
		$cid	= Generals::getVar('cid', array(0));
		$user	= Generals::getUserData();
		
		$vform['id'] = (int)$cid[0];
		
		if (!$vform['id']):
			$vform["create_on"] = date('Y-m-d H:i:s');
			$vform["create_by"] = $user['id'];
		else:
			$vform["update_on"] = date('Y-m-d H:i:s');
			$vform["update_by"] = $user['id'];
		endif;
		
		if ($vform["remimage"]):
			$DiskPath = IMG_PATH.DS."state";
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
			if (!Generals::addFile('image', null, IMG_PATH.DS."state")) {
				$link= 'index.php?option=state&view=form&cid[]='. $vform['id'];
				Generals::redirect($link); return;
			} else {
				$vform['image'] = Generals::getVar('image');
				$vform['thumb'] = str_replace('image', 'thumb', $vform['image']);
				JFolder::create(IMG_PATH.DS."state".DS.'resize');
				ImageClass::createImage(IMG_PATH.DS."state".DS.$vform['image'], IMG_PATH.DS."state".DS.'resize'.DS.$vform['thumb'], 100, 100);
			}
		}
		$data = $this->_db->getDataMapTable($vform, 'tbl_state');

		$this->_db->beginTrans();
		try {
			if ($data['id']):
				$this->_db->update_db('tbl_state', $data, ' id = ? ', array($data['id']));
			else:
				$this->_db->insert_db('tbl_state', $data);
			endif;
	        
			$this->_db->commitTrans();
			
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
			$vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
			if ($data['frontend']) $this->_db->frontend(1, array($vform['id']));
	        if ($task == "save"):
	        	$link = 'index.php?option=state&view=list';
	        elseif ($task == "apply"):
	        	$link = 'index.php?option=state&view=form&cid[]='. $vform['id'] .'';
	        endif;
		} catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option=state&view=form&cid[]='. $vform['id'] .'';
			Generals::redirect($link);
		}
		#----------------------------------------------------------------
        #----------------------------------------------------------------
		Generals::redirect($link);
	}
	
	function cancel() {
		Generals::redirect('index.php?option=state&view=list');
	}
}
?>