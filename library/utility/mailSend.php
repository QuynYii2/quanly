<?php
/**
 * filename : mailsend.inc
 * 
 * $mailsend = new MailSend();
 * $mailsend->send($mailaddress,$subject,$body,$from,'-f '.$returnAddress);
 * 
 * * 
 * 
 * $headers = "From: tuji@earthje.to\r\n";
 * $headers.= "Cc: makino@earthje.to\r\n";
 * $headers.= "Bcc: hine@earthje.to";
 * 
 **/
 /**
 * @project_name: localproject 
 * @file_name: mailSend.inc
 * @descr:
 * 			$mailsend = new MailSend();
 * 			$mailsend->send($mailaddress,$subject,$body,$from,'-f '.$returnAddress);
 * 			
 * 
 **/

if(!defined("MAILSEND_INC")){

	define("MAILSEND_INC",1);
	
	class MailSend{
	
		private $subject;
		private $headers;
		private $to;
		private $body;
		private $param; // contents
		private $logFile;
	
		function __construct($logFile = "") {
			if (function_exists(mb_language)) mb_language("English");		
			
			//initialize
			$this->subject = "";
			$this->headers = "";
			$this->to = "";
			$this->body = "";
			$this->param = "";
		
			if ($logFile == "") {
				$this->logFile = LOGS_DIR."MailSend".date("Ymd").".log";
			}			
		}
	
		public function send($to,$subject,$body,$headers,$param,$level = 0) {
			$this->setTo($to);
			$this->setSubject($subject);
			$this->setBody($body);
			$this->setHeader($headers);
			$this->setParam($param);
			if ($level > 0) {
				$lw = new LogWriter($this->logFile,0);
				$lw->write(date("[Y-m-d H:i:s]"));
				$lw->write($this->to);
				$lw->write($subject);
				if($level >= 2){
					$lw->write($this->body);
					$lw->write($this->headers);
					$lw->write($this->param);
				}
				$lw->close();
			}
			if (function_exists(mb_send_mail)) mb_send_mail($this->to, $this->subject, $this->body,$this->headers,$this->param);
			else mail($this->to, $this->subject, $this->body, $this->headers, $this->param);
			
		}
		
		private function setTo($to) {
			$this->to = $to;
			return $this->to;
		}
		
		private function setSubject($subject) {
			if (function_exists(mb_convert_encoding)) $subject = mb_convert_encoding($subject,"UTF-8", "auto");
			$this->subject = $subject;
			return $this->subject;
		}
	
		private function setBody($body) {
			if (function_exists(mb_convert_encoding)) $body = mb_convert_encoding($body,"UTF-8", "auto");
			$this->body = $body;
			return $this->body;
		}
	
		private function setHeader($headers) {
			$headers = trim($headers);
			$str_headers  	 = "MIME-Version: 1.0" . "\r\n";
			$str_headers 	.= "Content-type: text/html; charset=UTF-8" . "\r\n";
			//$headers	 	.= "Content-Transfer-Encoding: 7bit";
			if (function_exists(mb_convert_encoding)) $this->headers 	 = $str_headers.mb_convert_encoding($headers,"UTF-8", "auto");
			else $this->headers 	 = $str_headers.$headers;
			return $this->headers;
		}
	
		private function setParam($param) {
			if (function_exists(mb_convert_encoding)) $this->param = mb_convert_encoding($param,"UTF-8", "auto");
			else $this->param = $param;
			return $this->param;
		}
	}// end class
}
?>
