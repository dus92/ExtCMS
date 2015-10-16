<?php
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) Hakkah ~ CMS Development Team                              //
//   http://hakkahcms.org                                                     //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////

define("WIDTH_AUTO_MATCH", 400);

/**
 * Resizes images using GDlib2
 *
 * @param string $src
 * @param string $dest
 * @param integer $width
 * @param integer $height
 * @param boolean $wm
 * @param string $rgb
 * @param integer $quality
 * @return boolean
 */
function img_resize($src, $dest, $width, $height=0, $wm = false, $wmpos = 'center_middle', $rgb=0xFFFFFF, $quality=100)
{
	global $system;
	if (!file_exists($src)) return false;

	$size = getimagesize($src);

	if ($size === false) return false;

	// ���������� �������� ������ �� MIME-����������, ���������������
	// �������� getimagesize, � �������� ��������������� �������
	// imagecreatefrom-�������.
	$format = strtolower(mb_substr($size['mime'], mb_strpos($size['mime'], '/')+1));
	$icfunc = "imagecreatefrom" . $format;
	if (!function_exists($icfunc)) return false;

	$skipresample = false;
	if($width == 0)
	{
		if($size[0] > WIDTH_AUTO_MATCH)
		{
			$width=WIDTH_AUTO_MATCH;
		}
		else
		{
			$width=$size[0];
		}
	}

	if($height == 0) // auto
	{
		$height = $size[1]*$width/$size[0];
	}

	$x_ratio = $width / $size[0];
	$y_ratio = $height / $size[1];

	$ratio       = min($x_ratio, $y_ratio);
	$use_x_ratio = ($x_ratio == $ratio);

	$new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
	$new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
	$new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
	$new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

	$isrc = $icfunc($src);
	$idest = imagecreatetruecolor($width, $height);

	imagefill($idest, 0, 0, $rgb);
	imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
	$new_width, $new_height, $size[0], $size[1]);

	$wmsize = getimagesize($system->config['watermark']);
	if($wm && $size[0] >= $wmsize[0])
	{
		$size[0] = $width;
		$size[1] = $height;
		switch ($size[2])
		{
			case 1:
				$main_img_obj = imagecreatefromgif($src);
				break;
			case 3:
				$main_img_obj = imagecreatefrompng($src);
				break;
			case 2:
			default:
				$main_img_obj = imagecreatefromjpeg($src);
				break;
		}

		$idest = create_watermark($idest,$system->config['watermark'],$size, $system->config['wm_alpha_level'], $wmsize[2], $wmpos);
	}

	imagejpeg($idest, $dest, $quality);

	imagedestroy($isrc);
	imagedestroy($idest);

	return true;
}

/**
 * Creates watermarked image from source image
 *
 * @param resource $srcim
 * @param string $wmrk
 * @param array $size
 * @param integer $alpha_level
 * @param boolean $wmmime
 * @param string $wmpos
 * @return resource
 */
function create_watermark($srcim, $wmrk, $size, $alpha_level = 15, $wmmime = false, $wmpos = 'center_middle')
{
	switch ($wmmime)
	{
		case 1:
			$watermark_img_obj = imagecreatefromgif($wmrk);
			break;
		case 3:
			$watermark_img_obj = imagecreatefrompng($wmrk);
			break;
		case 2:
		default:
			$watermark_img_obj = imagecreatefromjpeg($wmrk);
			break;
	}

	$watermark_width = imagesx($watermark_img_obj);
	$watermark_height = imagesy($watermark_img_obj);

	switch($wmpos)
	{
		case 'left_top':
			$dest_x = 0;
			$dest_y = 0;
			break;
		case 'left_middle':
			$dest_x = 0;
			$dest_y = ($size[1] / 2) - ($watermark_height / 2);
			break;
		case 'left_bottom':
			$dest_x = 0;
			$dest_y = $size[1] - $watermark_height;
			break;
		case 'center_top':
			$dest_x = ($size[0] / 2) - ($watermark_width / 2);
			$dest_y = 0;
			break;
		case 'center_middle':
			$dest_x = ($size[0] / 2) - ($watermark_width / 2);
			$dest_y = ($size[1] / 2) - ($watermark_height / 2);
			break;
		case 'center_bottom':
			$dest_x = ($size[0] / 2) - ($watermark_width / 2);
			$dest_y = $size[1] - $watermark_height;
			break;
		case 'right_top':
			$dest_x = $size[0] - $watermark_width;
			$dest_y = 0;
			break;
		case 'right_middle':
			$dest_x = $size[0] - $watermark_width;
			$dest_y = ($size[1] / 2) - ($watermark_height / 2);
			break;
		case 'right_bottom':
		default:
			$dest_x = $size[0] - $watermark_width;
			$dest_y = $size[1] - $watermark_height;
			break;
	}
	
	imagecopymerge($srcim, $watermark_img_obj, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $alpha_level);
	return $srcim;
}
?>