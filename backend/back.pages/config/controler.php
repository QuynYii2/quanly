<?php
class controler {
	var $_db;

	function __construct() {
		$this->controler();
	}

	function controler(){
		$this->_db = new ConfigDao();
	}

	function display() {
	}

	function apply() { $this->save('apply'); }
	
	function update() { $this->save('save'); }
	
	function save($task) {
		require_once LIB_PATH."joomla".DS."filesystem".DS."folder.php";
		require_once LIB_PATH."joomla".DS."filesystem".DS."file.php";

        #==========================================================================
        # Update param global =====================================================
        #==========================================================================
        $global 	= Generals::getVar('global');
        $config 	= Generals::getVar('config');
        $title 		= Generals::getVar('title');
        $value 		= Generals::getVar('value');
        $ordering 	= Generals::getVar('ordering');
        $published 	= Generals::getVar('published');
        $user		= Generals::getUserData();

        foreach ($global as $key => $val):
            $data = array();
            $data['id'] 		= $val;
            $data['config'] 	= $config[$key];
            $data['title'] 		= $title[$key];
            $data['value'] 		= $value[$key];
            $data['ordering'] 	= $ordering[$key];
            $data['published'] 	= $published[$key];
            $data['update_on'] 	= date('Y-m-d H:i:s');
            $data['update_by'] 	= $user['id'];

            $data = $this->_db->getDataMapTable($data, 'tbl_config');
            $this->_db->beginTrans();
            try {
                if ($data['id']):
                    $this->_db->update_db('tbl_config', $data, ' id = ? ', array($data['id']));
                else:
                    $this->_db->insert_db('tbl_config', $data);
                endif;

                $this->_db->commitTrans();
                Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
            } catch (Exception $ex) {
                $this->_db->rollbackTrans();
                Generals::setError(Generals::getTitle('UPDATE_EROR'));
                Generals::redirect('index.php');
            }
        endforeach;
		#==========================================================================
		# Update logo page and favicon website ====================================
		#==========================================================================
		$images = $_FILES['image'];
		$hidden = Generals::getVar('hidden');
		$labels = Generals::getVar('label');
		
		if(!JFolder::exists(IMG_PATH)) JFolder::create(IMG_PATH);
		if (is_array($images['name'])) foreach ($images['name'] as $key => $name):
			$_ext = JFile::getExt($name);
			$name = substr_replace($name, '', strlen($name)- strlen($_ext) - 1);
			$name = JFilterOutput::stringURLSafe(Generals::getCovertVn($name)).".".$_ext;
	
			if(!JFile::upload($_FILES['image']["tmp_name"][$key], IMG_PATH.DS.$name)) {	
				Generals::setError('System! Unsupported Media Type!');
			} else {
				if ($hidden[$key]) JFile::delete(IMG_PATH.DS.$hidden[$key]);
				$hidden[$key] = $name;
			}
		endforeach;
		
		
		$this->_db->beginTrans();
        try {
	        if (is_array($labels)) foreach ($labels as $id => $label):
	        	$data = array();
		        $data['id']			= $id;
	        	$data['label']		= $label;
	        	$data['value']		= $hidden[$id];
	        	$data['genre']		= 0;
	        	$data['published']	= 1;
	        	if ($data['id']):
					$this->_db->update_db('tbl_config', $data, ' id = ? ', array($data['id']));
				else:
					$this->_db->insert_db('tbl_config', $data);
				endif;
        	endforeach;
        	$this->_db->commitTrans();
        } catch (Exception $ex) {
			$this->_db->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			Generals::redirect('index.php?option=config&view=list');
		}
		#==========================================================================
		#==========================================================================
		
		$primary 	= Generals::getVar('primary', array());
		$fullname 	= Generals::getVar('name', array());
		$introtext 	= Generals::getVar('introtext', array());
		$_name 		= array_values($fullname);
		$_intro		= array_values($introtext);
		
		if (is_array($fullname)) foreach ($fullname as $lang => $items):
		
	        $this->_db->beginTrans();
	        try {
		        if (is_array($items)) foreach ($items as $key => $val):
		        
		        	$data = array();
			        $data['id']			= (int)$primary[$lang][$key];
		        	$data['config'] 	= $key;
		        	$data['language']	= $lang;
		        	$data['name']		= $fullname[$lang][$key] ? $fullname[$lang][$key] : $_name[0][$key];
		        	$data['introtext']	= $introtext[$lang][$key] ? $introtext[$lang][$key] : $_intro[0][$key];
		        	if ($data['id']):
						$this->_db->update_db('tbl_config_lang', $data, ' id = ? ', array($data['id']));
					else:
						$this->_db->insert_db('tbl_config_lang', $data);
					endif;
					
	        	endforeach;
	        	$this->_db->commitTrans();
	        } catch (Exception $ex) {
				$this->_db->rollbackTrans();
				Generals::setError(Generals::getTitle('UPDATE_EROR'));
				Generals::redirect('index.php?option=config&view=list');
			}
		endforeach;
        #----------------------------------------------------------------
        #----------------------------------------------------------------
		Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
		Generals::redirect('index.php?option=config&view=list');
	}
}
?>