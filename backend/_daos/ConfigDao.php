<?php
 if (!defined("CONFIG_DAO_INC")) {
	define("CONFIG_DAO_INC",1);
	
	class ConfigDao extends DbConnect /*DbMySQLConnect*/  {
		function __construct() {
			parent::__construct();
		}
		
		function getDataLang($language = null, $void = null) {
			$query = ' SELECT * FROM tbl_config_lang WHERE config = ? AND language = ? ';
			$this->prepare($query);
			$result = $this->exec(array($void, $language));
			
			return $result[0];
		}
		
		function getGlobals() {
			$query = ' SELECT a.* FROM tbl_config AS a ';
			$query.= ' WHERE a.config LIKE "config:%" ';
			$query.= ' ORDER BY a.ordering, a.id ASC ';
			$this->prepare($query);
			$result = $this->exec(array());
			
			return $result;
		}
		
		function getOtherConfig() {
			$query = ' SELECT a.* FROM tbl_config AS a ';
			$query.= ' WHERE a.config NOT LIKE "config:%" AND a.config NOT LIKE "image:%" ';
			$query.= ' ORDER BY a.ordering, a.id ASC ';
			$this->prepare($query);
			$result = $this->exec(array());

			return $result;
		}

		function getImageConfig() {
			$query = ' SELECT a.* FROM tbl_config AS a ';
			$query.= ' WHERE a.config LIKE "image:%" ';
			$query.= ' ORDER BY a.ordering, a.id ASC ';
			$this->prepare($query);
			$result = $this->exec(array());

			return $result;
		}

		function published($val, $keys) {
			if (is_array($keys)) foreach ($keys as $key):
				$this->prepare(' UPDATE tbl_album SET published = '.(int)$val.' WHERE id = '.(int)$key);
				$this->exec(array());
			endforeach;
		}
		
		function ordering($val, $key) {
			$this->prepare(' UPDATE tbl_album SET ordering = '.(int)$val.' WHERE id = '.(int)$key);
			$this->exec(array());
		}
		
		public function delete($oid) {
			$this->delete_db('tbl_config', " id = ? ", array($oid));
			$this->delete_db('tbl_config_lang', " config = ? ", array($oid));
		}
	}// end class		
 }
?>