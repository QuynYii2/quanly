<?php
class Generals {
    /**
     * Function check string is json
     * @param $string
     * @return bool
     */
    static function isJson($string) {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    static function getMimeContentType($filename) {
		$pathinfo = pathinfo($filename);
		$filemime = LIB_PATH.'helpers'.DS.'mime.csv';
		$row = 1;
		if (($handle = fopen($filemime, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		    	if (strtolower('.'.$pathinfo['extension']) == strtolower($data[0])) return $data[1];
		    }
		    fclose($handle);
		}
	}
		
	static function getCurrentPageURL() {
		$current = 'http';
		if ($_SERVER["HTTPS"] == "on") {$current .= "s";}
		$current .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$current .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$current .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $current;
	}
	
	static function getYouTubeName($id) {
	    $url = "http://gdata.youtube.com/feeds/api/videos/". $id;
	    $doc = new DOMDocument;
	    $doc->load($url);
	    $title = $doc->getElementsByTagName("title")->item(0)->nodeValue;
	    
	    return $title;
	}
	
	static function getYouTubeId($iframe) {
		$pattern = '#(?<=(?:v|i)=)[a-zA-Z0-9-]+(?=&)|(?<=(?:v|i)\/)[^&\n]+|(?<=embed\/)[^"&\n]+|(?<=‌​(?:v|i)=)[^&\n]+|(?<=youtu.be\/)[^&\n]+#';
		preg_match($pattern, $iframe, $matches);
		$youtube = str_replace('?wmode=transparent', '', $matches[0]);
		if (empty($youtube)):
			parse_str( parse_url( $iframe, PHP_URL_QUERY ), $_vars );
			$youtube = $_vars['v'];
		endif;
		
		return $youtube;
	}
	
	static function getPassword($length, $allow = "abcdefghjkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ23456789") {
		$i 	 = 1;
		$ret = null;
		while ($i <= $length) {
			$max 	= strlen($allow)-1;
			$num 	= rand(0, $max);
			$temp 	= substr($allow, $num, 1);
			$ret 	= $ret . $temp;
			$i++;
		}
		return $ret;
	}
	
	static function getStaticPage($lang = 'vn') {
		$db = new DbConnect();
		$query = ' SELECT a.* FROM tbl_config AS a ';
		$query.= ' WHERE a.config NOT LIKE "config:%" ';
		$query.= ' ORDER BY a.ordering, a.id ASC ';
		$db->prepare($query);
		$result = $db->exec(array());
		$return = array();
		
		if (is_array($result)) foreach ($result as $items):
			$db->prepare(' SELECT * FROM tbl_config_lang WHERE config = ? AND language = ? ');
			$datalang = $db->exec(array($items['id'], $lang));
			$return[$items['config']]['name'] = $datalang[0]['name'];
			$return[$items['config']]['text'] = $datalang[0]['introtext'];
		endforeach;
		
		return $return;
	}
	
	static function getConfig(){
		$db = new DbConnect();
		$query = ' SELECT a.* FROM tbl_config AS a ';
		$query.= ' WHERE a.config LIKE "config:%" ';
		$query.= ' ORDER BY a.ordering, a.id ASC ';
		$db->prepare($query);
		$result = $db->exec(array());
		$return = array();
		
		if (is_array($result)) foreach ($result as $items):
			$return[$items['config']] = $items['value'];
		endforeach;
		
		return $return;
	}
	
	static function getCfIcon(){
		$db = new DbConnect();
		$query = ' SELECT a.* FROM tbl_config AS a ';
		$query.= ' WHERE a.config LIKE "image:%" ';
		$query.= ' ORDER BY a.ordering, a.id ASC ';
		$db->prepare($query);
		$result = $db->exec(array());
		$return = array();
		
		if (is_array($result)) foreach ($result as $items):
			$return[$items['config']] = $items['value'];
		endforeach;
		
		return $return;
	}
	
	/**
	 * Function get variables of request
	 * @param 	String $key
	 * @param 	String $default
	 * @param 	String $method [POST, GET]
	 * @return	value of reques
	 */
	static function getVar($key, $default = null, $method = null) {
		if (strtolower(trim($method)) == 'post') return isset($_POST[$key]) ? $_POST[$key] : $default;
		if (strtolower(trim($method)) == 'get')  return isset($_GET[$key]) ? $_GET[$key] : $default;
		
		return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
	}
	
	/**
	 * Function set variables to request
	 * @param 	String $key
	 * @param 	String $val
	 * @param 	String $method [POST, GET]
	 */
	static function setVar($key, $val, $method = null) {
		if (strtolower(trim($method)) == 'post') $_POST[$key] = $val;
		else if (strtolower(trim($method)) == 'get') $_GET[$key] = $val;
		else $_REQUEST[$key] = $val;
	}
	
	/**
	 * Function create toolbar button
	 * @param 	String $task
	 * @param 	String $title
	 * @param 	String $class
	 * @return	String[html code]
	 */
	static function getToolBar($task, $title, $class) {
		return '<a class="toolbar" href="javascript:;" task="'.$task.'" onclick="javascript:return submitForm(\''.$task.'\');"><span class="'.$class.'"></span>'.$title.'</a>';
	}
	
	static function getBuildOrder($field, $title, $field_active = null, $order_active = 'asc') {
		$title = Generals::getTitle($title, $title);
		$class = trim(strtolower($field)) == trim(strtolower($field_active)) ? 'sortby_'.trim(strtolower($order_active)) : '';
		return '<a href="javascript:;" field="'.$field.'" class="sort-order '.$class.'">'.$title.'</a>';
	}
	
	/**
	 * 
	 * @param unknown_type $key
	 * @param unknown_type $val
	 */
	static function setState($key, $val) {
		$option = Generals::getVar('option');
        $view   = Generals::getVar('view');
		$_SESSION[JURI::root()]['_STATE_'][$option.'_'.$view][$key] = $val;
	}
	
	/**
	 * 
	 * @param unknown_type $key
	 */
	static function getState($key, $default = null) {
		$option = Generals::getVar('option');
        $view   = Generals::getVar('view');
		return isset($_SESSION[JURI::root()]['_STATE_'][$option.'_'.$view][$key]) ? $_SESSION[JURI::root()]['_STATE_'][$option.'_'.$view][$key] : $default;
	}
	
	/**
	 * 
	 * @param unknown_type $key
	 * @param unknown_type $val
	 */
	static function setSession($key, $val) {$_SESSION[JURI::root()]['_SESSION_'][$key] = $val;}
	
	/**
	 * 
	 * @param unknown_type $key
	 * @param unknown_type $default
	 */
	static function getSession($key, $default = null) {return isset($_SESSION[JURI::root()]['_SESSION_'][$key]) ? $_SESSION[JURI::root()]['_SESSION_'][$key] : $default;}
	
	/**
	 * 
	 * @param unknown_type $key
	 * @param unknown_type $default
	 */
	static function getTitle($key, $default = null) {
		global $smarty;
		return isset($smarty->_config[0]["vars"][$key]) ? $smarty->_config[0]["vars"][$key] : $default;
	}
	
	/**
	 * Function get user information after login backend
	 * @return user information
	 */
	static function getUserData() {
		return Generals::getSession('LOGIN_DATA');
	}
	
	/**
	 * Function get user information after login frontend
	 * @return user information
	 */
	static function getUserLogin() {
		return Generals::getSession('LOGIN_USER');
	}
	
	static function getCaptcha($length = 6) {
		$captcha = self::getPassword($length);
		self::setSession('_CAPTCHA', strtolower($captcha));
		self::addTextToImage($captcha, ROOT_PATH."media".DS."bgcaptcha.gif", ROOT_PATH."captcha.gif", strrchr("captcha.gif", "."));
		
		return DOMAIN.'captcha.gif';
	}
	
	/**
	 * @note:	Function Add text To Image Use GD PHP
	 * @param	String:		$_text - Text to put into
	 * @param	String: 	$_background - Full path and file name image source
	 * @param	String: 	$_imgresult - Full path and file name image destination
	 * @param	String: 	$ext - File Extension
	 * @param	Integer:	$font - Font size
	 * 
	 * @return	Resource image
	 */
	static function addTextToImage($_text = null, $_background, $_imgresult, $ext, $font = 55) {
		$string = $_text ? $_text : "VNSSOFT";
		$width 	= imagefontwidth($font) * strlen($string) ;
		$height = imagefontheight($font) ;
		if($ext == ".jpeg" || $ext == ".jpg") {
			$im = imagecreatefromjpeg($_background);
			$x 	= imagesx($im)/2 - $width/2;
			$y 	= imagesy($im)/2 - $height/2;
			$background = imagecolorallocate ($im, 255, 255, 255);
			$text_color = imagecolorallocate ($im, 0, 0, 0);
			imagestring ($im, $font, $x, $y,  $string, $text_color);
			return imagejpeg($im, $_imgresult);
		} elseif ($ext == ".gif") {
			$im = imagecreatefromgif($_background);
			$x 	= imagesx($im)/2 - $width/2;
			$y 	= imagesy($im)/2 - $height/2;
			$background = imagecolorallocate ($im, 255, 255, 255);
			$text_color = imagecolorallocate ($im, 0, 0, 0);
			imagestring ($im, $font, $x, $y,  $string, $text_color);
			return imagegif($im, $_imgresult);
		} elseif ($ext == ".png") {
			$im = imagecreatefrompng($_background);
			$x 	= imagesx($im)/2 - $width/2;
			$y 	= imagesy($im)/2 - $height/2;
			$background = imagecolorallocate ($im, 255, 255, 255);
			$text_color = imagecolorallocate ($im, 0, 0, 0);
			imagestring ($im, $font, $x, $y,  $string, $text_color);
			return imagepng($im, $_imgresult);
		} else {
			return false;
		}
	}
	
	/**
	 * Function redirect url
	 * @param unknown_type $link
	 */
	static function redirect($link) {
		header('Location: '.$link);
		exit();
	}
	
	static function addFile($file, $type = null, $path = IMG_PATH, $task = null, $key = null)
	{
		if ($type != 'normal'):
			if(!Generals::checkFiles($file, $type, $key)) return false;
		endif;

		if(!JFolder::exists($path))	 JFolder::create($path);
	
		$_name= is_array($_FILES[$file]["name"]) ? $_FILES[$file]["name"][$key] : $_FILES[$file]["name"];
		$_ext = JFile::getExt($_name); 
		$_src = is_array($_FILES[$file]["tmp_name"]) ? $_FILES[$file]["tmp_name"][$key]: $_FILES[$file]["tmp_name"];
		$name = str_replace($_ext, '', JFile::getName($_name));
		#$name = $file.'.'.date("Hi").".".JFilterOutput::stringURLSafe(Generals::getCovertVn($name)).".".$_ext;
		$name = JFilterOutput::stringURLSafe(Generals::getCovertVn($name)).".".$_ext;
		$_dst = $path.DS.$name;
		
		$wrong = array('.php', '.cgi', '.pl', '.htaccess');
		foreach ($wrong as $val):
			if (strpos(strtolower($_name), $val) !== false):
				Generals::setError('System! Unsupported Media Type!');
				return false;
			endif;
		endforeach;
		
		if(!JFile::upload($_src, $_dst)) {	
			Generals::setError('System! Unsupported Media Type!');
			return false;
		} else {
			if (is_array($_FILES[$file]["name"])):
				$names = Generals::getVar($file, array());
				$names[$key] = $name;
				Generals::setVar($file, $names);
				if (Generals::getVar('hidden'.$file.$key) && $task != 'copy' && $name != Generals::getVar('hidden'.$file.$key)):
					JFile::delete($path.DS.Generals::getVar('hidden'.$file.$key));
					JFile::delete($path.DS.'resize'.DS.str_replace('image', 'thumb', Generals::getVar('hidden'.$file.$key)));
				endif;
			else:
				Generals::setVar($file, $name);
				if (Generals::getVar('hidden'.$file) && $task != 'copy' && $name != Generals::getVar('hidden'.$file)):
					JFile::delete($path.DS.Generals::getVar('hidden'.$file));
					JFile::delete($path.DS.'resize'.DS.str_replace('image', 'thumb', Generals::getVar('hidden'.$file)));
				endif;
			endif;
			return true;
		}
	}

	static function checkFiles($file, $type = null, $key = null) {
		global $mainframe;

		$_name = is_array($_FILES[$file]["name"]) ? $_FILES[$file]["name"][$key] : JFile::makeSafe($_FILES[$file]["name"]);

		if (!$_name) {	
			Generals::setError('System! Unsupported Media Type!');
			return false;
		}
		if(empty($type)) { # File image
			$_Extensions = array("jpg","jpeg","gif","png","bmp");
		} else if ($type == "file") { # File document
			$_Extensions = array("txt","csv","htm","html","xml","css","doc","xls","rtf","ppt","pdf");
		} else if ($type == "video") { # File video
			$_Extensions = array("swf","flv","avi","wmv","mov","rar"); 
		} else if ($type == "contact") { # File Contact
			$_Extensions = array("doc","docx","xls","xlsx","pdf","ppt","jpg","jpeg"); 
		}

		if (!strlen(array_search(end(explode(".", strtolower($_name))), $_Extensions))) { 
			Generals::setError($_name.' '.Generals::getTitle('FILE_NOT_FORMAT').' - ['.implode(", ", $_Extensions).']<br />');
			return false;
		}

		return true;
	}
	
	/**
	 * 
	 * @param String $message
	 * @param String $method [override, writeto]
	 */
	static function setError($message, $method = 'override') {
		if ($method == 'override') Generals::setSession('_ERROR', array($message));
		else {
			$_message = Generals::getSession('_ERROR');
			array_push($_message, $message);
			Generals::setSession('_ERROR', $_message);
		}
	}

    static function setWarning($message, $method = 'override') {
        if ($method == 'override') Generals::setSession('_WARNING', array($message));
        else {
            $_message = Generals::getSession('_WARNING');
            array_push($_message, $message);
            Generals::setSession('_WARNING', $_message);
        }
    }

	static function getError() {
		$message = Generals::getSession('_ERROR');
		Generals::setSession('_ERROR', array());
		return $message;
	}

    static function getWarning() {
        $message = Generals::getSession('_WARNING');
        Generals::setSession('_WARNING', array());
        return $message;
    }

	public static function getCovertVn($str = null) {
		$str = strip_tags($str);
		#$str = preg_replace("/(0|1|2|3|4|5|6|7|8|9)/", '', $str);
		#$str = preg_replace("/[-|.|(|)]/", '', trim($str));
		$str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
		$str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
		$str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
		$str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
		$str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
		$str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
		$str = preg_replace("/(đ)/", 'd', $str);
		$str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
		$str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
		$str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
		$str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
		$str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
		$str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
		$str = preg_replace("/(Đ)/", 'D', $str);
		$str = preg_replace("/( )/", '-', $str);
		$str = preg_replace("/[\/|.|#|:|=|?|\|+ -]+/", '-', $str);

		return strtolower($str);
	}
}