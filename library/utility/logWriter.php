<?php
/**
 * @project_name: localproject
 * @package: package_name
 * @file_name: LogWriter.inc
 * @descr:
 * 
 * @version 1.0
 **/
 
 if ( !defined("LOGWRITER_INC") ) {
 	define("LOGWRITER_INC",1);
 	
 	class LogWriter
 	{
 		var $fp;
		var $logDir;
		var $date_f;
		var $level;
		
		function LogWriter($filename,$date_f = 1,$level = 0) {
			if (!(int)WRITELOG) return;
			$this->date_f 	= $date_f;
			$this->level 	= $level;			
			$this->open($filename);
		}
		
		function open($filename) {
			if (!(int)WRITELOG) return;
			if ($this->fp) return 0;
			if (!file_exists($filename)) $f = true;
			else $f = false;
			
			$this->fp = fopen($filename,"a");
			if ($f) @chmod($filename,0777);
		}
		
		/**
		* write
		**/	
		function write($msg,$level = 0) {
			if (!(int)WRITELOG) return;
			else {
				if ($this->level < $level) return;
				if ($this->date_f) fwrite($this->fp,date("[Y-m-d H:i:s]"));
				fwrite($this->fp,"$msg\n");
			}
		}
		
		/**
		 * close file
		 **/
		function close(){
			if (!(int)WRITELOG) return;
			if ($this->fp) fclose($this->fp);
		}				
 	}// end class	
 } // end defined
?>
