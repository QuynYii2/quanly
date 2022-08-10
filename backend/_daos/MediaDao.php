<?php
 if (!defined("MEDIA_DAO_INC")) {
	define("MEDIA_DAO_INC",1);
	
	class MediaDao extends DbConnect /*DbMySQLConnect*/  {
		var $_id;
		
		function __construct() {
			$cid = Generals::getVar('cid', array());
			$this->setState();
			$this->setId($cid[0]);
			parent::__construct();
		}
		
		function setId($id) {
			$this->_id	= $id;
		}
			
		function setState() {
			if (!is_null(Generals::getVar('filter_search'))) 	Generals::setState('filter.search', strtolower(trim(Generals::getVar('filter_search'))));
			if (!is_null(Generals::getVar('filter_published'))) Generals::setState('filter.published', Generals::getVar('filter_published'));
			if (!is_null(Generals::getVar('page'))) 			Generals::setState('page', Generals::getVar('page', 1));
			if (!is_null(Generals::getVar('orderby_order'))) 	Generals::setState('orderby.order', Generals::getVar('orderby_order', 'ASC'));
			if (!is_null(Generals::getVar('orderby_field'))) 	Generals::setState('orderby.field', Generals::getVar('orderby_field', 'a.id'));
		}
		
		function getState() {
			return array(
							'filter_search' => Generals::getState('filter.search'), 'filter_published' => Generals::getState('filter.published'),
							'orderby_order' => Generals::getState('orderby.order'), 'orderby_field' => Generals::getState('orderby.field')
						);
		}
		
		function getGenreMedia($file) {
			$video = array('webm', 'mkv', 'flv', 'ogv', 'ogg', 'drc', 'mng', 'avi', 'mov', 'qt', 'wmv', 'yuv', 'rm', 'rmvb', 'asf', 'mp4', 'm4p', 'm4v', 'mpg', 'mp2', 'mpeg', 'mpe', 'mpv', 'm2v', 'svi', '3gp', '3g2', 'mxf', 'roq', 'nsv');
			$audio = array('3gp', 'act', 'aiff', 'aac', 'amr', 'au', 'awb', 'dct', 'dss', 'dvf', 'flac', 'gsm', 'iklax', 'ivs', 'm4a', 'm4p', 'mmf', 'mp3', 'mpc', 'msv', 'ogg', 'oga', 'opus', 'ra', 'rm', 'raw', 'sln', 'tta', 'vox', 'wav', 'wma', 'wv');
			$image = array('jpg', 'bmp', 'eps', 'gif', 'png', 'pdf', 'psd', 'tiff', 'ps', 'pict', 'avs', 'yuv', 'fits', 'sgi', 'cin', 'miff', 'otb', 'palm', 'pcd', 'pcds', 'pnm', 'pbm', 'pgm', 'ppm', 'dpx', 'tga', 'rle', 'xbm', 'xpm', 'p7', 'dcx', 'pcx', 'mvg', 'wmf', 'ai', 'svg', 'sk', 'sk1', 'cgm', 'wmf', 'pdf', 'ps');
			
			$ext = JFile::getExt($file);
			$ext = strtolower($ext);
			
			if (in_array($ext, $image)) 	return 0;
			elseif (in_array($ext, $video)) return 1;
			elseif (in_array($ext, $audio)) return 2;
			else return 3;
		}
		
		function getAlbumInId($ids = 0) {
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_album AS a ';
			$query.= ' LEFT JOIN tbl_album_lang AS fl ON a.id = fl.album';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id IN ('.$ids.') AND fl.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
				
			return isset($result) ? $result : array();
		}
		
		function getAlbumList() {
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_album AS a ';
			$query.= ' LEFT JOIN tbl_album_lang AS fl ON a.id = fl.album';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE fl.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
				
			return isset($result) ? $result : array();
		}
		
		function getDataLang($language = null, $void = null) {
			$void  = $void ? $void : $this->_id;
			$query = ' SELECT * FROM tbl_media_lang WHERE media = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getData() {
			$query = 'SELECT a.* FROM tbl_media AS a WHERE a.id = ? ';
			$this->prepare($query);
			$result = $this->exec(array($this->_id));
			
			return $result[0];
		}

		function getOrdering() {
			$query = ' SELECT a.ordering AS value, b.name AS text FROM tbl_media AS a ';
			$query.= ' INNER JOIN tbl_media_lang AS b ON a.id = b.media WHERE b.language = ? ORDER BY a.ordering ';
			$this->prepare($query);
			$result = $this->exec(array(Generals::getSession('langcode')));
			
			return $result;
		}
		
		function getDataFull($id){
		 	$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_media AS a ';
			$query.= ' LEFT JOIN tbl_media_lang AS fl ON a.id = fl.media';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language ';
			$query.= ' WHERE a.id = '.(int)$id;
			$this->prepare($query);
			$result = $this->exec($params);
			
			return isset($result[0]) ? $result[0] : array();
		}

		function _buildQuery(){
			$search 	= Generals::getState('filter.search');
			$search 	= JString::strtolower($search);
			$published 	= Generals::getState('filter.published');
			$language	= Generals::getSession('langcode');
			$params 	= array($language);
			
			if (strpos($search, '"') !== false) $search = str_replace(array('=', '<'), '', $search);
			
			$where[] = ' fl.language = ? ';
			if (strlen($search)):
				$where[] = ' LOWER(fl.name) LIKE ? ';
				array_push($params, '%'.strtolower($search).'%');
			endif;
			if (strlen($published)):
				$where[] = ' a.published = ? ';
				array_push($params, $published);
			endif;
			
			$where = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			$query = ' SELECT a.*, fl.name, fl.alias, fl.introtext FROM tbl_media AS a ';
			$query.= ' LEFT JOIN tbl_media_lang AS fl ON a.id = fl.media';
			$query.= ' LEFT JOIN tbl_language AS l ON l.code = fl.language '.$where;
			Generals::setState('params', $params);
			
			return $query;
		}
		
		function getDataList() {
			$field = Generals::getState('orderby.field', 'a.id') ? Generals::getState('orderby.field', 'a.id') : 'a.id';
			$order = Generals::getState('orderby.order', 'DESC') ? Generals::getState('orderby.order', 'DESC') : 'DESC';
			$query = $this->_buildQuery();
			$query.= ' ORDER BY '.$field.' '.$order;
			$this->prepare($query);
			$result = $this->exec(Generals::getState('params'));
			$this->total = sizeof($result);
			
			while (true):
				$offset = (int)(Generals::getState('page', 1)-1)*LIMIT_RECORD;
				if ($offset >= $this->total) {
					$offset	= (int)$offset - (int)LIMIT_RECORD;
					Generals::setState('page', Generals::getState('page', 1)-1);
				} elseif ($offset < 0) {
					$offset = 0;
					break;
				} else {
					break;
				}
			endwhile;
			
			if (is_array($result)):
				$result = array_chunk($result, LIMIT_RECORD);
				$page	= Generals::getState('page', 1)-1 >= 0 ? Generals::getState('page', 1)-1 : 0;
			else:
				$page = 0;
				$result[0] = $result;
			endif;
	
			return $result[$page];
		}
		
		function getCountData() {
			return $this->total;
		}
		
		function published($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_media SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_media SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$result = $this->getDataFull($oid);
			$this->delete_db('tbl_media', " id = ? ", array($oid));
			$this->delete_db('tbl_media_lang', " media = ? ", array($oid));
			
			JFile::delete(IMG_PATH.DS."media".DS.$result['image']);
			JFile::delete(IMG_PATH.DS."media".DS.'resize'.DS.str_replace('image', 'thumb', $result['image']));
		}
	}// end class		
 }
?>