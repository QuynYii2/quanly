<?php
	Class Router {
		static public $_options = array(
								'article' 		=> 'bai-viet',
								'product' 		=> 'san-pham',
								'introduction' 	=> 'gioi-thieu',
								'service' 		=> 'dich-vu',
								'promotion' 	=> 'khuyen-mai',
								'recruitment' 	=> 'tuyen-dung',
								'consulting' 	=> 'tu-van',
								'contact' 		=> 'lien-he',
								'media' 		=> 'thu-vien',
								'statics'		=> 'tuy-chinh',
								'event'			=> 'su-kien',
								'partner'		=> 'doi-tac',
								'newsletter'	=> 'dang-ky-nhan-mail',
								'youtube'		=> 'video',
								'customer'		=> 'khach-hang'
							);
		
		static public $_views = array(
								'list' 		=> 'danh-sach',
								'detail' 	=> 'chi-tiet',
								'compare' 	=> 'so-sanh',
								'image' 	=> 'hinh-anh',
								'default'	=> ''
							);
		
		/**
		 * @constructor
		 * @access public
		 * @return void
		 */
		function __construct() {}
		
		/**
		 * 
		 * @param unknown_type $option
		 * @param unknown_type $view
		 * @param unknown_type $id
		 */
		function getAlias($option, $view, $id) {
			$option = trim(strtolower($option));
			switch ($option):
				case 'statics':
					$query = ' SELECT alias FROM tbl_statics_lang WHERE language = ? AND statics = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'catevent':
					$query = ' SELECT alias FROM tbl_catevent_lang WHERE language = ? AND catevent = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'event':
					if ($view == 'list'):
						$query = ' SELECT alias FROM tbl_catevent_lang WHERE language = ? AND catevent = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					else:
						$query = ' SELECT alias FROM tbl_event_lang WHERE language = ? AND event = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					endif;
					break;
				case 'catpartner':
					$query = ' SELECT alias FROM tbl_catpartner_lang WHERE language = ? AND catpartner = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'partner':
					if ($view == 'list'):
						$query = ' SELECT alias FROM tbl_catpartner_lang WHERE language = ? AND catpartner = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					else:
						$query = ' SELECT alias FROM tbl_partner_lang WHERE language = ? AND partner = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					endif;
					break;
				case 'section':
					$query = ' SELECT alias FROM tbl_section_lang WHERE language = ? AND section = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'article':
					if ($view == 'list'):
						$query = ' SELECT alias FROM tbl_section_lang WHERE language = ? AND section = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					else:
						$query = ' SELECT alias FROM tbl_article_lang WHERE language = ? AND article = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					endif;
					break;
				case 'introduction':
					$query = ' SELECT alias FROM tbl_introduction_lang WHERE language = ? AND introduction = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'promotion':
					$query = ' SELECT alias FROM tbl_promotion_lang WHERE language = ? AND promotion = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'recruitment':
					$query = ' SELECT alias FROM tbl_recruitment_lang WHERE language = ? AND recruitment = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'consulting':
					$query = ' SELECT alias FROM tbl_consulting_lang WHERE language = ? AND consulting = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'service':
					if ($view == 'list'):
						$query = ' SELECT alias FROM tbl_classify_lang WHERE language = ? AND classify = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					else:
						$query = ' SELECT alias FROM tbl_service_lang WHERE language = ? AND service = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					endif;
					#$query = ' SELECT alias FROM tbl_service_lang WHERE language = ? AND service = ? ';
					#$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'category':
					$query = ' SELECT alias FROM tbl_category_lang WHERE language = ? AND category = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'product':
					if ($view == 'list'):
						$query = ' SELECT alias FROM tbl_category_lang WHERE language = ? AND category = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					elseif ($view == 'image'):
						$query = ' SELECT alias FROM tbl_product_image_lang WHERE language = ? AND image = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					else:
						$query = ' SELECT alias FROM tbl_product_lang WHERE language = ? AND product = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					endif;
					break;
				case 'media':
					if ($view == 'list'):
						$query = ' SELECT alias FROM tbl_album_lang WHERE language = ? AND album = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					else:
						$query = ' SELECT alias FROM tbl_media_lang WHERE language = ? AND media = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					endif;
					break;
				case 'youtube':
					if ($view == 'list'):
						$query = ' SELECT alias FROM tbl_tubecate_lang WHERE language = ? AND tubecate = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					else:
						$query = ' SELECT alias FROM tbl_youtube_lang WHERE language = ? AND youtube = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					endif;
					break;
				case 'faq':
					$query = ' SELECT alias FROM tbl_faq_lang WHERE language = ? AND faq = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'blog':
					$query = ' SELECT alias FROM tbl_blog_lang WHERE language = ? AND blog = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'customer':
					$query = ' SELECT alias FROM tbl_customer_lang WHERE language = ? AND customer = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'advice':
					$query = ' SELECT alias FROM tbl_advice_lang WHERE language = ? AND advice = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'info':
					$query = ' SELECT alias FROM tbl_info_lang WHERE language = ? AND info = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'gara':
					$query = ' SELECT alias FROM tbl_gara_lang WHERE language = ? AND gara = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'part':
					$query = ' SELECT alias FROM tbl_part_lang WHERE language = ? AND part = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'classified':
					$query = ' SELECT alias FROM tbl_classified_lang WHERE language = ? AND classified = ? ';
					$param = array(Generals::getSession('langcode'), $id);
					break;
				case 'inquire':
					if ($view == 'list'):
						$query = ' SELECT alias FROM tbl_classify_lang WHERE language = ? AND classify = ? ';
						$param = array(Generals::getSession('langcode'), $id);
					else:
						$query = ' SELECT name AS alias FROM tbl_inquire WHERE id = ? ';
						$param = array($id);
					endif;
					break;
				endswitch;
			
			if ($query):
				$_dbo 	= new DbConnect();
				$_dbo->prepare($query);
				$alias 	= $_dbo->exec($param);
				
				#return $alias[0]['alias'];
				return JFilterOutput::stringURLSafe(Generals::getCovertVn($alias[0]['alias']));
			endif;
			
			return null;
		}
		
		static function getOption($option) {
			return self::$_options[trim(strtolower($option))] ? self::$_options[trim(strtolower($option))] : trim(strtolower($option));
		}
		
		static function getView($view) {
			return self::$_views[trim(strtolower($view))] ? self::$_views[trim(strtolower($view))] : trim(strtolower($view));
		}
		
		static function getUrl($url) {
			if (!REWRITE) return $url;
			
			$segments 	= array();
			$parse_url 	= parse_url($url);
			parse_str($parse_url['query'], $query);
			
			if (isset($query['option'])) {
				$segments['option'] = self::getOption($query['option']);
				$option = $query['option']; unset($query['option']);
			}
			
			if (isset($query['view'])) {
				$segments['view'] = self::getView($query['view']);
				$view = $query['view']; unset($query['view']);
			}
			
			if (isset($query['id'])) {
				$alias = self::getAlias($option, $view, $query['id']);
				$segments['id'] = $query['id'].'-'.($alias ? $alias : 'all');
				unset($query['id']);
			}
			
			if (isset($query['page'])) {
				if (Generals::getSession('langcode') == 'vn') $segments['page'] = 'trang-'.$query['page'];
				if (Generals::getSession('langcode') != 'vn') $segments['page'] = 'page-'.$query['page'];
				unset($query['page']);
			}
			
			$vars['option'] = !empty($segments['option']) ? $segments['option'] : $query['option'];
			$vars = $vars + $segments;
			
			unset($query['option']); $params = array();
			foreach ($query as $key => $val):
				if (is_array($val)):
					foreach ($val as $k => $v):
						if (is_array($v)):
							foreach ($v as $_k => $_v):
								$params[] = $key.'['.$k.']['.$_k.']='.$_v;
							endforeach;
						else:
							$params[] = $key.'['.$k.']='.$v;
						endif;
					endforeach;
				else:
					$params[] = $key.'='.$val;
				endif;
			endforeach;
			
			return JURI::base().implode('/', $vars).(!empty($params)?'?'.implode('&', $params):'') ;
		}
		
		static function setVars() {
			if (REWRITE) {
				$_dbo 	= new DbConnect();
				$_dbo->prepare(' SELECT * FROM tbl_router WHERE genre = 0 AND published = 1 ORDER BY id ');
				$result = $_dbo->exec(array());
				if (is_array($result)) foreach ($result as $items):
					self::$_options[$items['module']] = $items['router'];
				endforeach;
				
				$_dbo->prepare(' SELECT * FROM tbl_router WHERE genre = 1 AND published = 1 ORDER BY id ');
				$result = $_dbo->exec(array());
				if (is_array($result)) foreach ($result as $items):
					self::$_views[$items['module']] = $items['router'];
				endforeach;
				
				$route = empty($_REQUEST['option']) ? '' : $_REQUEST['option'];
				$parts = explode('/', $route);
				
				$option = array_search($parts[0], self::$_options);
				$view	= array_search($parts[1], self::$_views);
				
				$_REQUEST['option'] = $parts[0] ? ($option ? $option : $parts[0]) : ($_REQUEST['option'] ? $_REQUEST['option'] : 'index');
				$_REQUEST['view'] 	= $parts[1] ? ($view ? $view : $parts[1]) : ($_REQUEST['view'] ? $_REQUEST['view'] : 'default');
				
				if(isset( $parts[2])) :
					list($_REQUEST['id'], $_REQUEST['alias']) = explode('-', $parts[2], 2);
				endif;
				
				if(isset( $parts[3])) :
					list($title, $_REQUEST['page']) = explode('-', $parts[3], 2);
				endif;
			}
		}
	}
?>