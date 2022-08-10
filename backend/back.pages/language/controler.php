<?php
class controler {
	var $_db;
    var $_genre;
    var $_module;
    var $_permission;

	function __construct($genre = 'front') {
		$this->controler();
        $this->_genre = $genre;
        $this->_module = Generals::getVar('option', 'index');
        $this->_permission = Generals::getSession('permission');
	}

	function controler(){
		$this->_db = new LanguageDao();
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
		Generals::redirect('index.php?option=language&view=list');
	}

	function delete() {
        if (empty($this->_permission['mod_delete'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		if (is_array($cid)) foreach ($cid as $val) $this->_db->delete($val);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('DELETES_SUCCESS')));
		Generals::redirect('index.php?option=language&view=list');
	}

	function create() {
        if (empty($this->_permission['mod_create'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        header('Location: index.php?option=language&view=form');
		return false;
	}

	function frontend() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->frontend(1, $cid);
	}

	function publish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(1, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('PUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=language&view=list');
	}

	function unpublish() {
        if (empty($this->_permission['mod_update'][$this->_module])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $cid = Generals::getVar('cid', array());
		$this->_db->published(0, $cid);
		Generals::setError(str_replace('%s', count($cid), Generals::getTitle('UNPUBLISH_SUCCESS')));
		Generals::redirect('index.php?option=language&view=list');
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
		$view = Generals::getVar('view');
		if ($view == 'text'):
            if ($this->_genre == 'front'):
                $vform 	= Generals::getVar('vform', array());
                $_code	= Generals::getVar('code');
                $_lang	= Generals::getVar('lang');
                $lines 	= "";
                if ($_code):
                    foreach ($vform as $key => $value) $lines .= $key." = ".trim($value)."\n";
                    $handle = @fopen(ROOT_PATH.DS.'frontend'.DS.'language'.DS.$_code.DS.'generals.conf', "w");
                    fwrite($handle, $lines);
                    fclose($handle);
                    unset($handle);
                    Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
                else:
                    Generals::setError(Generals::getTitle('UPDATE_EROR'));
                endif;
            else:
                $vform 	= Generals::getVar('vform', array());
                $_code	= Generals::getVar('code');
                $_lang	= Generals::getVar('lang');
                if ($_code):
                    foreach ($vform as $module => $langtext):
                        $lines 	= "";
                        foreach ($langtext as $key => $value) $lines .= $key." = ".trim($value)."\n";
                        $handle = @fopen(ROOT_PATH.DS.'backend'.DS.'language'.DS.$_code.DS.$module.'.conf', "w");
                        fwrite($handle, $lines);
                        fclose($handle);
                        unset($handle);
                    endforeach;
                    Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
                else:
                    Generals::setError(Generals::getTitle('UPDATE_EROR'));
                endif;
            endif;

            if ($task == "save"):
                $link = 'index.php?option=language&view=list';
            elseif ($task == "apply"):
                $link = 'index.php?option=language&view=text&genre='.$this->_genre.'&cid[]='. $_lang .'';
            endif;

            Generals::redirect($link);
		endif;
		
		require_once LIB_PATH."joomla".DS."filesystem".DS."folder.php";
		require_once LIB_PATH."joomla".DS."filesystem".DS."file.php";
		
		$vform 	= Generals::getVar('vform', array());
		$cid	= Generals::getVar('cid', array(0));
		$user	= Generals::getUserData();
		
		$vform['id'] = (int)$cid[0];

        if (empty($this->_permission['mod_create'][$this->_module]) && empty($vform['id'])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        elseif (empty($this->_permission['mod_update'][$this->_module]) && !empty($vform['id'])):
            Generals::setWarning(Generals::getTitle('PERMISSION_MESSAGE'));
            Generals::redirect('index.php?option='.$this->_module.'&view=list');
        endif;

        $existed = $this->_db->getDataByCode($vform['code'], $vform['id']);
		if ($existed):
			$link= 'index.php?option=language&view=form&cid[]='. $vform['id'];
			Generals::setError(Generals::getTitle('LANGUAGE_CODE_EXISTED'));
			Generals::redirect($link); return;
		endif;
		
		if (!$vform['id']):
			$vform["create_on"] = date('Y-m-d H:i:s');
			$vform["create_by"] = $user['id'];
		else:
			$vform["update_on"] = date('Y-m-d H:i:s');
			$vform["update_by"] = $user['id'];
		endif;
		
		if ($vform["remimage"]):
			$DiskPath = IMG_PATH.DS."language";
			if (JFile::exists($DiskPath.DS.Generals::getVar('hiddenimage'))) JFile::delete($DiskPath.DS.Generals::getVar('hiddenimage'));
			$vform['image'] = '';
		endif;
		
		$file_image = $_FILES['image'];
		
		if($file_image['name'] != '')  {
			if (!Generals::addFile('image', null, IMG_PATH.DS."language")) {
				$link= 'index.php?option=language&view=form&cid[]='. $vform['id'];
				Generals::redirect($link); return;
			} else {
				$vform['image'] = Generals::getVar('image');
			}
		}
		$data = $this->_db->getDataMapTable($vform, 'tbl_language');

		$this->_db->beginTrans();
		try {
			if ($data['id']):
				$this->_db->update_db('tbl_language', $data, ' id = ? ', array($data['id']));
			else:
				$this->_db->insert_db('tbl_language', $data);
			endif;
	        
			$this->_db->commitTrans();
			
			if (!$vform['id'] && $vform['code']):
				if (!JFolder::exists(ROOT_PATH.DS.'frontend'.DS.'language'.DS.$vform['code'])) JFolder::create(ROOT_PATH.DS.'frontend'.DS.'language'.DS.$vform['code']);
				JFile::copy(ROOT_PATH.DS.'frontend'.DS.'language'.DS.'generals.conf', ROOT_PATH.DS.'frontend'.DS.'language'.DS.$vform['code'].DS.'generals.conf');
			endif;
			
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
			$vform['id'] = $data['id'] ? $data['id'] : $this->_db->getLastInsertId();
			if ($data['frontend']) $this->_db->frontend(1, array($vform['id']));
	        if ($task == "save"):
	        	$link = 'index.php?option=language&view=list';
	        elseif ($task == "apply"):
	        	$link = 'index.php?option=language&view=form&cid[]='. $vform['id'] .'';
	        endif;
		} catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			$link = 'index.php?option=language&view=form&cid[]='. $vform['id'] .'';
			Generals::redirect($link);
		}
		#----------------------------------------------------------------
        #----------------------------------------------------------------
        Generals::setState('data.form', null);
		Generals::redirect($link);
	}
	
	function cancel() {
		Generals::redirect('index.php?option=language&view=list');
	}
}
?>