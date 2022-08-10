<?php
class controler {
	var $_db;
    var $_genre;
    var $_module;
    var $_permission;

	function __construct() {
		$this->controler();
        $this->_genre = Generals::getVar('genre', null);
        $this->_module = Generals::getVar('option', 'index');
        $this->_permission = Generals::getSession('permission');
	}

	function controler(){
		$this->_db = new ServiceDao();
	}

	function display() {
        $view = Generals::getVar('view', 'list');
        if (empty($this->_permission['mod_simple'][$this->_module]) && $view == 'list'):
            Generals::redirect('index.php?option=permission');
        elseif (empty($this->_permission['mod_update'][$this->_module]) && $view == 'form'):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        endif;

        if ($view == 'list') Generals::setState('data.form', null);
	}

	function ordering() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        endif;

        $orders = Generals::getVar('order', array());
		if (is_array($orders)) foreach ($orders as $key => $val) $this->_db->ordering($val, $key);
		Generals::setError(Generals::getTitle('ORDERING_SUCCESS'));
        Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        endif;

        Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=form');
		return false;
	}

	function publish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(1, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('PUBLISH_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
	}

	function unpublish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
        Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
	}

	function trash() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(-1, $cid[0]);
	}
	
	function apply() { $this->save('apply'); }
	
	function update() { $this->save('save'); }

    function saveas() { $this->save('copy'); }

	function save($task) {
		$vform 	= Generals::getVar('vform', array());
		$cid	= Generals::getVar('cid', array(0));
		$user	= Generals::getUserData();
		
        $vform['id'] = $task == "copy" ? 0 : (int)$cid[0];
        $vform['genre'] = Generals::getVar('genre');
		if (!$vform['id']):
			$vform["create_on"] = date('Y-m-d H:i:s');
			$vform["create_by"] = $user['id'];
		else:
			$vform["update_on"] = date('Y-m-d H:i:s');
			$vform["update_by"] = $user['id'];
		endif;

        if (empty($vform['genre'])) Generals::redirect('index.php');
        if (empty($this->_permission['mod_create'][$this->_module]) && empty($vform['id'])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        elseif (empty($this->_permission['mod_update'][$this->_module]) && !empty($vform['id'])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
        endif;

        #=========================================================================================
        # Check Existed Code =====================================================================
        #=========================================================================================
        if ($this->_db->getExistedCode($vform['code'], (int)$vform['id'])):
            if ($task == "copy"):
                $vform['code']  = $vform['code'].'_'.date('YmdHis');
            else:
                Generals::setState('data.form', $vform);
                Generals::setWarning(Generals::getTitle('CODE_EXISTED'));
                Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=form&cid[]='. (int)$vform['id']);
            endif;
        endif;
        #=========================================================================================
		# File thumbnail social ==================================================================
		#=========================================================================================
        if ($vform["remsocial"] && $task != 'copy'):
			$DiskPath = IMG_PATH.DS."service";
			if (JFile::exists($DiskPath.DS.Generals::getVar('hiddensocial'))) JFile::delete($DiskPath.DS.Generals::getVar('hiddensocial'));
			$vform['social'] = '';
		endif;
		
		$file_social = $_FILES['social'];
		
		if($file_social['name'] != '')  {
			if (!Generals::addFile('social', 'normal', IMG_PATH.DS."service")) {
				Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=form&cid[]='. $vform['id']);
                return;
			} else {
				$vform['social'] = Generals::getVar('social');
			}
        } elseif ($task == 'copy' && strlen(trim(Generals::getVar('hiddensocial')))) {
            $vform['social'] = date('YmdHis').'_'.Generals::getVar('hiddensocial');
            JFile::copy(IMG_PATH.DS."service".DS.Generals::getVar('hiddensocial'), IMG_PATH.DS."service".DS.$vform['social']);
        }
		#=========================================================================================
		#=========================================================================================
		$data = $this->_db->getDataMapTable($vform, 'tbl_service');

		$this->_db->beginTrans();
		try {
			if ($data['id']):
				$this->_db->update_db('tbl_service', $data, ' id = ? ', array($data['id']));
			else:
				$this->_db->insert_db('tbl_service', $data);
			endif;
	        
			$this->_db->commitTrans();
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
			$vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
            if ($task == "save" || $task == "copy"):
	        	$link = 'index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list';
	        elseif ($task == "apply"):
	        	$link = 'index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=form&cid[]='. $vform['id'] .'';
	        endif;
		} catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=form&cid[]='. $vform['id'] .'';
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
	        	$data['service'] 	= $vform['id'];
	        	$data['language']	= $lang;
	        	$data['name']		= $name ? $name : $_name[0];
	        	$data['alias']		= JFilterOutput::stringURLSafe(Generals::getCovertVn($data['name']));
	        	$data['introtext']	= $vform["introtext"][$lang] ? $vform["introtext"][$lang] : $_intro[0];
	        	$data['tags']		= $vform["tags"][$lang] ? $vform["tags"][$lang] : $_tags[0];
	        	$data['meta']		= $vform["meta"][$lang] ? $vform["meta"][$lang] : $_meta[0];
	        	if ($data['id']):
					$this->_db->update_db('tbl_service_lang', $data, ' id = ? ', array($data['id']));
				else:
					$this->_db->insert_db('tbl_service_lang', $data);
				endif;
        	endforeach;
        	$this->_db->commitTrans();
        } catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=form&cid[]='. $vform['id'] .'';
			Generals::redirect($link);
		}
        #----------------------------------------------------------------
        #----------------------------------------------------------------
        Generals::setState('data.form', null);
		Generals::redirect($link);
	}
	
	function cancel() {
        Generals::redirect('index.php?option='.$this->_module.'&genre='.$this->_genre.'&view=list');
	}
}
?>