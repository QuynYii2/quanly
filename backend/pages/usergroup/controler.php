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
		$this->_db = new UsergroupDao();
	}

	function display() {
        $view = Generals::getVar('view', 'list');
        if (empty($this->_permission['mod_simple'][$this->_module]) && $view == 'list'):
            Generals::redirect('index.php?option=permission');
        elseif (empty($this->_permission['mod_update'][$this->_module]) && $view == 'form'):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;
	}

	function ordering() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $orders = Generals::getVar('order', array());
		if (is_array($orders)) foreach ($orders as $key => $val) $this->_db->ordering($val, $key);
		Generals::setError(Generals::getTitle('ORDERING_SUCCESS'));
		Generals::redirect('index.php?option=usergroup&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option=usergroup&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        header('Location: index.php?option=usergroup&view=form');
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
		Generals::redirect('index.php?option=usergroup&view=list');
	}

	function unpublish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=usergroup&view=list');
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
	
	function save($task) {
		require_once LIB_PATH."joomla".DS."filesystem".DS."folder.php";
		require_once LIB_PATH."joomla".DS."filesystem".DS."file.php";
		
		$vform 	= Generals::getVar('vform', array());
		$cid	= Generals::getVar('cid', array(0));
		$user	= Generals::getUserData();
		
		$vform['id'] = (int)$cid[0];
        $vform["mod_create"] = json_encode($vform["mod_create"]);
        $vform["mod_update"] = json_encode($vform["mod_update"]);
        $vform["mod_delete"] = json_encode($vform["mod_delete"]);
        $vform["mod_detail"] = json_encode($vform["mod_detail"]);
        $vform["mod_simple"] = json_encode($vform["mod_simple"]);

        if (empty($this->_permission['mod_create'][$this->_module]) && empty($vform['id'])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        elseif (empty($this->_permission['mod_update'][$this->_module]) && !empty($vform['id'])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        if (!$vform['id']):
			$vform["create_on"] = date('Y-m-d H:i:s');
			$vform["create_by"] = $user['id'];
		else:
			$vform["update_on"] = date('Y-m-d H:i:s');
			$vform["update_by"] = $user['id'];
		endif;
		
		if ($vform["remimage"]):
			$DiskPath = IMG_PATH.DS."usergroup";
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
			if (!Generals::addFile('image', null, IMG_PATH.DS."usergroup")) {
				$link= 'index.php?option=usergroup&view=form&cid[]='. $vform['id'];
				Generals::redirect($link); return;
			} else {
				$vform['image'] = Generals::getVar('image');
				$vform['thumb'] = str_replace('image', 'thumb', $vform['image']);
				JFolder::create(IMG_PATH.DS."usergroup".DS.'resize');
				ImageClass::createImage(IMG_PATH.DS."usergroup".DS.$vform['image'], IMG_PATH.DS."usergroup".DS.'resize'.DS.$vform['thumb'], 100, 100);
			}
		}
		$data = $this->_db->getDataMapTable($vform, 'tbl_usergroup');

		$this->_db->beginTrans();
		try {
			if ($data['id']):
				$this->_db->update_db('tbl_usergroup', $data, ' id = ? ', array($data['id']));
			else:
				$this->_db->insert_db('tbl_usergroup', $data);
			endif;
	        
			$this->_db->commitTrans();
			
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
			$vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
			if ($data['frontend']) $this->_db->frontend(1, array($vform['id']));
	        if ($task == "save"):
	        	$link = 'index.php?option=usergroup&view=list';
	        elseif ($task == "apply"):
	        	$link = 'index.php?option=usergroup&view=form&cid[]='. $vform['id'] .'';
	        endif;
		} catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option=usergroup&view=form&cid[]='. $vform['id'] .'';
			Generals::redirect($link);
		}
		#----------------------------------------------------------------
        #----------------------------------------------------------------
        Generals::setState('data.form', null);
		Generals::redirect($link);
	}
	
	function cancel() {
		Generals::redirect('index.php?option=usergroup&view=list');
	}
}
?>