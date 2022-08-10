<?php
/**
 * @package 	mod_bt_contentshowcase - BT ContentShowcase Module
 * @version		1.0
 * @created		June 2012
 * @author		BowThemes
 * @email		support@bowthems.com
 * @website		http://bowthemes.com
 * @support		Forum - http://bowthemes.com/forum/
 * @copyright	Copyright (C) 2012 Bowthemes. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 */

// no direct access

require_once LIB_PATH.'joomla'.DS.'filesystem'.DS.'file.php';

if (!class_exists('ImageClass')) {
	class ImageClass extends JObject {
		static function getImageCreateFunction($type) {
			switch ($type) {
				case 'jpeg':
				case 'jpg':
					$imageCreateFunc = 'imagecreatefromjpeg';
					break;

				case 'png':
					$imageCreateFunc = 'imagecreatefrompng';
					break;

				case 'bmp':
					$imageCreateFunc = 'imagecreatefrombmp';
					break;

				case 'gif':
					$imageCreateFunc = 'imagecreatefromgif';
					break;

				case 'vnd.wap.wbmp':
					$imageCreateFunc = 'imagecreatefromwbmp';
					break;

				case 'xbm':
					$imageCreateFunc = 'imagecreatefromxbm';
					break;

				default:
					$imageCreateFunc = 'imagecreatefromjpeg';
			}

			return $imageCreateFunc;
		}

		static function getImageSaveFunction($type) {
			switch ($type) {
				case 'jpeg':
					$imageSaveFunc = 'imagejpeg';
					break;

				case 'png':
					$imageSaveFunc = 'imagepng';
					break;

				case 'bmp':
					$imageSaveFunc = 'imagebmp';
					break;

				case 'gif':
					$imageSaveFunc = 'imagegif';
					break;

				case 'vnd.wap.wbmp':
					$imageSaveFunc = 'imagewbmp';
					break;

				case 'xbm':
					$imageSaveFunc = 'imagexbm';
					break;

				default:
					$imageSaveFunc = 'imagejpeg';
			}

			return $imageSaveFunc;
		}

		static function crop($imgSrc, $imgDest, $dWidth, $dHeight, $crop = true, $quality = 100, $border = 0, $bcolor = array()) {

			$info 		= getimagesize($imgSrc, $imageinfo);
			$sWidth 	= $info[0];
			$sHeight 	= $info[1];

			if ($sHeight > $dHeight) {
				$width 	= ($dHeight / $sHeight) * $sWidth;
				$height = $dHeight;
			}

			if ($sWidth > $dWidth) {
				$height = ($dWidth / $sWidth) * $sHeight;
				$width 	= $dWidth;
			}
			
			if (!$crop) {
				$sx = 0;
				$sy = 0;
				$width 	= $sWidth;
				$height = $sHeight;
			} else {
				$sx = floor(($sWidth - $width) / 2);
				$sy = floor(($sHeight - $height) / 2);
			}

			#echo "$sx:$sy:$width:$height";die();

			$ext = str_replace('image/', '', $info['mime']);
			$imageCreateFunc = self::getImageCreateFunction($ext);
			$imageSaveFunc = self::getImageSaveFunction(JFile::getExt($imgDest));

			$sImage = $imageCreateFunc($imgSrc);
			
			if (!$border):
				$dImage = imagecreatetruecolor($dWidth, $dHeight);
				#$border = imagecolorallocate($tImage, 0, 0, 0);
				#$color 	= imagecolorallocate($tImage, 255, 255, 255);
				#imagefilltoborder($tImage, 50, 50, $border, $color);
				
				// Make transparent
				if ($ext == 'png') {
					imagealphablending($dImage, false);
					imagesavealpha($dImage, true);
					$transparent = imagecolorallocatealpha($dImage, 255, 255, 255, 127);
					imagefilledrectangle($dImage, 0, 0, $dWidth, $dHeight, $transparent);
				}
	
				imagecopyresampled($dImage, $sImage, 0, 0, $sx, $sy, $dWidth, $dHeight, $width, $height);
	
				if ($ext == 'png') {
					$imageSaveFunc($dImage, $imgDest, 9);
				}
				else if ($ext == 'gif') {
					$imageSaveFunc($dImage, $imgDest);
				}
				else {
					$imageSaveFunc($dImage, $imgDest, $quality);
				}
			else:
				$dWidth	= $dWidth + ($border*2);
				$dHeight= $dHeight + ($border*2);
				$dImage = imagecreatetruecolor($dWidth, $dHeight);
				
				$b_color = imagecolorallocate($dImage, (int)$bcolor[0], (int)$bcolor[1], (int)$bcolor[2]);
				$f_color = imagecolorallocate($dImage, (int)$bcolor[0], (int)$bcolor[1], (int)$bcolor[2]);
				imagefilltoborder($dImage, 0, 0, $b_color, $f_color);
				
				// Make transparent
				if ($ext == 'png') {
					imagealphablending($dImage, false);
					imagesavealpha($dImage, true);
					$transparent = imagecolorallocatealpha($dImage, 255, 255, 255, 127);
					imagefilledrectangle($dImage, $border, $border, $dWidth-$border, $dHeight-$border, $transparent);
				}
	
				imagecopyresampled($dImage, $sImage, $border, $border, $sx, $sy, $dWidth-($border*2), $dHeight-($border*2), $width, $height);
	
				if ($ext == 'png') {
					$imageSaveFunc($dImage, $imgDest, 9);
				}
				else if ($ext == 'gif') {
					$imageSaveFunc($dImage, $imgDest);
				}
				else {
					$imageSaveFunc($dImage, $imgDest, $quality);
				}
			endif;
		}
		
		static function resize($imgSrc, $imgDest, $dWidth = null, $dHeight = null, $update = false, $crop = true, $quality = 90, $border = 0, $bcolor = array()) {
			$info 		= getimagesize($imgSrc, $imageinfo);
			$sWidth 	= $info[0];
			$sHeight 	= $info[1];
            $ratio      = $sWidth / $sHeight;
            $ext        = JFile::getExt($imgSrc);

            if(!$dWidth)    $dWidth  = round($dHeight * $ratio);
            if(!$dHeight)   $dHeight = round($dWidth / $ratio);

            if ((empty($dWidth) && empty($dHeight) && !$update) || ($sWidth == $dWidth && $sHeight == $dHeight && !$update)):
                JFile::copy($imgSrc, $imgDest);
            else:
                $imageCreateFunc 	= self::getImageCreateFunction(JFile::getExt($imgSrc));
                $imageSaveFunc 		= self::getImageSaveFunction(JFile::getExt($imgDest));

                $sImage = $imageCreateFunc($imgSrc);
                $dImage = imagecreatetruecolor($dWidth, $dHeight);
                imagecopyresampled($dImage, $sImage, 0, 0, 0, 0, $dWidth, $dHeight, $sWidth, $sHeight);

                // Make transparent
                if ($ext == 'png') {
                    imagealphablending($dImage, false);
                    imagesavealpha($dImage, true);
                    $transparent = imagecolorallocatealpha($dImage, 255, 255, 255, 127);
                    imagefilledrectangle($dImage, 0, 0, $dWidth, $dHeight, $transparent);
                }

                if ($resource):
                    return array('width' => $dWidth, 'height' => $dHeight, 'resource' => $dImage);
                else:
                    if ($ext == 'png') {
                        $imageSaveFunc($dImage, $imgDest, 9);
                    }
                    else if ($ext == 'gif') {
                        $imageSaveFunc($dImage, $imgDest);
                    }
                    else {
                        $imageSaveFunc($dImage, $imgDest, $quality);
                    }
                endif;
            endif;
		}
		
		static function createImage($imgSrc, $imgDest, $width, $height = null, $crop = true, $quality = 90, $border = 0, $bcolor = array()) {
            # Image is created
            if (JFile::exists($imgDest)) {
				$info = getimagesize($imgDest, $imageinfo);
				if (($info[0] == $width) && ($info[1] == $height)) return;
			}

            $images = self::resize($imgSrc, $imgDest, $width, $height, $quality, true);
            $dImage = imagecreatetruecolor($images['width'], $images['height']);

            # Crop Image
            if ($crop):
                imagecopyresampled($dImage, $images['resource'], 0, 0, $sx, $sy, $dWidth, $dHeight, $width, $height);
            endif;

            # Create Border Image
            if ($border):
                $b_color = imagecolorallocate($dImage, (int)$bcolor[0], (int)$bcolor[1], (int)$bcolor[2]);
                $f_color = imagecolorallocate($dImage, (int)$bcolor[0], (int)$bcolor[1], (int)$bcolor[2]);
                imagefilltoborder($dImage, 0, 0, $b_color, $f_color);
            endif;
		}
		
		static function mergeImage($imgDest, $width, $height, $padding, $frame, $matte, $letters, $quality = 100, $border = 0, $bcolor = array(255, 255, 255), $margin = 0) {
			#------------------------------------------------------------------------
			# Create destination image ----------------------------------------------
			#------------------------------------------------------------------------
			$imageSaveFunc = self::getImageSaveFunction(JFile::getExt($imgDest));
			$dImage = imagecreatetruecolor($width, $height);
			
			#------------------------------------------------------------------------
			# Create background (matte) image ---------------------------------------
			#------------------------------------------------------------------------
			$i_matte = getimagesize($matte);
			$w_matte = $i_matte[0] > $width ? $width : $i_matte[0];
			$h_matte = $i_matte[1] > $height ? $height : $i_matte[1];
			$e_matte = str_replace('image/', '', $i_matte['mime']);
			
			$MCreateFunc = self::getImageCreateFunction($e_matte);
			$mImage = $MCreateFunc($matte);
			$_tempW = ceil($width/$w_matte);
			$_tempH = ceil($height/$h_matte);

			for ($i = 0; $i < $_tempW; $i++):
				for ($j = 0; $j < $_tempH; $j++):
					imagecopyresampled($dImage, $mImage, $i*$w_matte, $j*$h_matte, 0, 0, $w_matte, $h_matte, $w_matte, $h_matte);
				endfor;
			endfor;
			
			#------------------------------------------------------------------------
			# Create frame image ----------------------------------------------------
			#------------------------------------------------------------------------
			$i_frame = getimagesize($frame);
			$w_frame = $i_frame[0];
			$h_frame = $i_frame[1];
			$e_frame = str_replace('image/', '', $i_frame['mime']);
			
			$FCreateFunc = self::getImageCreateFunction($e_frame);
			$fImage = $FCreateFunc($frame);
			imagecopyresampled($dImage, $fImage, 0, 0, 0, 0, $width, $height, $w_frame, $h_frame);
			
			#------------------------------------------------------------------------
			# Create letter image ---------------------------------------------------
			#------------------------------------------------------------------------
			$max_lw = ($width-$padding)/count($letters) - ($border*2 + $margin*2);
			$max_lh	= $height-$padding;
			$l_size	= self::getSizeImage($letters[0], null, $max_lh, $max_lw, $max_lh);
			$lwidth = ($l_size[0]+10)*count($letters);
			$_start = ($width - $lwidth)/2 + $margin;
			
			if (!JFile::exists(JPATH_BASE.DS.'images'.DS.'veast'.DS.'temp')) JFolder::create(JPATH_BASE.DS.'images'.DS.'veast'.DS.'temp', 0777); 
			foreach ($letters as $key => $letter):
				$_ext = strtolower(JFile::getExt($letter));
				$temp = JPATH_BASE.DS.'images'.DS.'veast'.DS.'temp'.DS.date('YmdHis').'_'.$key.'.'.$_ext;
				self::createImage($letter, $temp, $l_size[0], $l_size[1], false, 100, $border, $bcolor);

				$TCreateFunc = self::getImageCreateFunction($_ext);
				$tImage = $TCreateFunc($temp);
				
				imagecopyresampled($dImage, $tImage, $_start, ($padding/2)-$border, 0, 0, $l_size[0]+($border*2), $l_size[1]+($border*2), $l_size[0]+($border*2), $l_size[1]+($border*2));
				
				JFile::delete($temp);
				$_start = $_start + $l_size[0] + ($border*2 + $margin*2);
			endforeach;
			#------------------------------------------------------------------------
			# Save Image ------------------------------------------------------------
			#------------------------------------------------------------------------
			$dest_ext = strtolower(JFile::getExt($imgDest));
			if ($dest_ext == 'png'):
				$imageSaveFunc($dImage, $imgDest, 9);
			elseif ($dest_ext == 'gif'):
				$imageSaveFunc($dImage, $imgDest);
			else:
				$imageSaveFunc($dImage, $imgDest, $quality);
			endif;
			
			imagedestroy($dImage);
			
			return $imgDest;
		}
		
		function getSizeImage($image, $w, $h, $m_w, $m_h) {
			$imageinfo 	= getimagesize($image);
			$source_w	= $imageinfo[0];
			$source_h	= $imageinfo[1];
			
		    if (empty($w)) $w = ($h / $source_h) * $source_w;
		    if (empty($h)) $h = ($w / $source_w) * $source_h;
		
		    if ($w > $m_w):
		    	$w = $m_w;
		    	$h = ($w / $source_w) * $source_h;
		    endif;
		    
		    if ($h > $m_h):
		    	$h = $m_h;
		    	$w = ($h / $source_h) * $source_w;
		    endif;
		    
		    return array($w, $h);
		}
	}
}
?>