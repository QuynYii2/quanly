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

	function isImage($img){
	    if(!getimagesize($img)){
	        return 0;
	    } else {
	        return 1;
	    }
	} 
	
	function removeFolder() {
		$path 	= base64_decode(Generals::getVar('path'));
		JFolder::delete($path);
	}
	
	function createFolder() {
		$path 	= base64_decode($_SESSION['UPLOAD_PATH']);
		$folder = trim(Generals::getVar('folder'));
		JFolder::create($path.DS.$folder, 777);
	}
	
	function delImage(){
		$file 	= Generals::getVar('file');
		$file	= base64_decode($file);
		if (JFile::exists($file)):
			JFile::delete($file);
		else:
			JFile::delete(base64_decode($_SESSION['UPLOAD_PATH']).DS.$file);
		endif;
		
		exit();
	}
	
	function getImages() {
		$_SESSION['UPLOAD_PATH'] = Generals::getVar('path', DATA_PATH.DS.'fckeditor');
		$_SESSION['SOURCE_PATH'] = str_replace('\\', '/', str_replace(DATA_PATH, DOMAIN.'/data', base64_decode($_SESSION['UPLOAD_PATH'])));
		$path 	= Generals::getVar('path', DATA_PATH.DS.'fckeditor');
		$path	= base64_decode($path);
		$files 	= JFolder::files($path);
		$result	= array();
		if (is_array($files)) foreach ($files as $key => $file):
			$result[$key]['path'] = str_replace('\\', '/', str_replace(DATA_PATH, DOMAIN.'/data', $path)).'/'.$file;
			$result[$key]['file'] = base64_encode($path.DS.$file);
			$result[$key]['name'] = $file;
			$result[$key]['type'] = $this->isImage($path.DS.$file);
		endforeach;
		
		$return['path'] = str_replace('\\', '/', str_replace(DATA_PATH, DOMAIN.'/data', $path));
		$return['file'] = $result;
		
		echo json_encode($return);
		exit();
	}
	
	var $_html = null;
	function getTreeFolder($path) {
		$this->_html = null;
		$this->buildTreeFolder($path, 0);
		return $this->_html;
	}
	
	function buildTreeFolder($path, $level) {
		$folders = JFolder::folders($path);
		if (is_array($folders) && count($folders)):
			$this->_html.= '<ul>';
			foreach ($folders as $name):
				$this->_html.= '	<li>';
				$this->_html.= '		<a href="javascript:;" path="'.base64_encode($path.DS.$name).'"><span style="margin-left: '.($level*20).'px;">'.$name.'</span></a>';
				$this->_html.= '		<label path="'.base64_encode($path.DS.$name).'" class="remfolder"></label>';
				$this->buildTreeFolder($path.DS.$name, $level+1);
				$this->_html.= '	</li>';
			endforeach;
			$this->_html.= '</ul>';
		endif;
	}
}
?>