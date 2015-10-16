<?php 
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) ReloadCMS Development Team                                 //
//   http://reloadcms.sf.net                                                  //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////
define('RCMS_ROOT_PATH', './');

include(RCMS_ROOT_PATH . 'common.php');

// abnormal server? he-he
// (if you're watching many errors using asynchronous file upload, try set it true)
define("ABNORMAL",true);

function rcms_loadAdminLib($lib){
	require_once(ADMIN_PATH . 'libs/' . $lib . '.php');
}

header('Content-Type: text/html; charset=' . $system->config['encoding']);

if(isset($_GET['exit']))
{
	$system->logOutUser();
	setcookie('reloadcms_user');
	$_COOKIE['reloadcms_user'] = '';
	header('Location: admin.php');
	die(__('Access denied'));
}

//------------------------------------------------------------------------------------------------------//
// preparations...

$rights = &$system->rights;
$root   = &$system->root;
if(!LOGGED_IN){
	$message = __('Access denied');
	$message .= '<br />
<form method="post" action="">
<input type="hidden" name="login_form" value="1" />
<table cellpadding="2" cellspacing="1" style="width: 100%;">
<tr>
    <td class="row1">' . __('Username') . ':</td>
    <td class="row1" style="width: 100%;"><input type="text" name="username" style="text-align: left; width: 95%;" /></td>
</tr>
<tr>
    <td class="row1">' . __('Password') . ':</td>
    <td class="row1" style="width: 100%;"><input type="password" name="password" style="text-align: left; width: 95%;" /></td>
</tr>
<tr>
    <td class="row1" colspan="2">
        <input type="checkbox" name="remember" id="remember" value="1" />
        <label for="remember">' . __('Remember me') . '</label>
    </td>
</tr>
<tr>
    <td class="row2" colspan="2"><input type="submit" value="' . __('Log in') . '" /></td>
</tr>
</table>
</form>';
	include(ADMIN_PATH . 'error.php');
} elseif (empty($rights) && !$root) {
	$message = __('You are not administrator of this site');
	include(ADMIN_PATH . 'error.php');
} else {
	if(!empty($_GET['show'])) $show = $_GET['show']; else $show = '';

	$categories = rcms_scandir(ADMIN_PATH . 'modules', '', 'dir');
	$MODULES = array();
	foreach ($categories as $category){
		if(file_exists(ADMIN_PATH . 'modules/' . $category . '/module.php')){
			include_once(ADMIN_PATH . 'modules/' . $category . '/module.php');
		}
	}
	switch($show){
        case 'imail' :
            include (ADMIN_PATH . 'imail.php');                     
			break;
        case 'nav':
			include(ADMIN_PATH . 'navigation.php');
			break;
		case 'module':
			$iblock = new iBlock();
			$module = (!empty($_GET['id'])) ? basename($_GET['id']) : '.index';
			$module = explode('.', $module, 2);
			if(!is_file(ADMIN_PATH . 'modules/' . $module[0] . '/' . $module[1] . '.php')) {
				$message = __('Module not found') . ': ' . $module[0] . '/' . $module[1];
				include(ADMIN_PATH . 'error.php');
			} elseif($module[1] != 'index' && empty($MODULES[$module[0]][1][$module[1]])) {
				$message = __('Access denied') . ': ' . $module[0] . '/' . $module[1];
				include(ADMIN_PATH . 'error.php');
			} else {
				include(ADMIN_PATH . 'module.php');
			}
			break;
		case 'upload':
			error_reporting(E_ERROR);
			$error = '';
			$fileElementName = (!empty($_GET['field']) ? $_GET['field'] : 'fileToUpload');
			if(!empty($_FILES[$fileElementName]['error']))
			{
				switch($_FILES[$fileElementName]['error'])
				{
					case '1':
					case '2':
						$error = __('Error! Code').': '.$_FILES[$fileElementName]['error'].'; '.__('File size is too big');
						break;
					case '3':
						$error = __('Error! Code').': 3; '.__('The uploaded file was only partially uploaded');
						break;
					case '4':
						$error = __('Error! Code').': 4; '.__('No file was uploaded');
						break;
					case '6':
						$error = __('Error! Code').': 6; '.__('Missing a temporary folder');
						break;
					case '7':
						$error = __('Error! Code').': 7; '.__('Failed to write file to disk');
						break;
					case '8':
						$error = __('Error! Code').': 8; '.__('File upload stopped by extension');
						break;
					case '999':
					default:
						$error = __('Unknown file upload error!');
				}
			}
			else if(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none')
			{
				$error = __('Error! No file was uploaded');
			}
			else
			{
				$path = GetPath($_GET['path'],(isset($_GET['ibid']) ? $_GET['ibid'] : false),(isset($_GET['year']) ? $_GET['year'] : false),(isset($_GET['month']) ? $_GET['month'] : false),(isset($_GET['id']) ? $_GET['id'] : false));
				$fname = preg_replace("/[^0-9a-zA-Z_\.]/",rcms_random_string('1'),basename($_FILES[$fileElementName]['name']));
				$th_fname = $path.'th_'.$fname;
				$fname = $path.$fname;
				if(ABNORMAL)
				{
					if(file_exists($fname))
					{
						unlink($fname);
					}
					$result = rename($_FILES[$fileElementName]['tmp_name'],$fname);
				}
				else
				{
					$result = move_uploaded_file($_FILES[$fileElementName]['tmp_name'],$fname);
				}
				if(!$result || !file_exists($fname))
				{
					@unlink($_FILES[$fileElementName]);
					$error = __('Unknown file upload error');
					$fname = '';
				}
				else
				{
					if(isset($_GET['mime']))
					{
						$type = $_FILES[$fileElementName]['type'];
					}
					else if(@getimagesize($fname))
					{
						$type='image';
					}
					else
					{
						$type = '';
					}

					if(!empty($_GET['thumbs']))
					{
						if(in_array($_FILES[$fileElementName]['type'], array('image/gif','image/png','image/jpeg','image/pjpeg')))
						{
							$tsize = explode('x',preg_replace("/[^0-9x]/",'',$_GET['thumbs']));
							img_resize($fname, $th_fname, $tsize[0],$tsize[1]);
						}
					}

					if(isset($_GET['resize']))
					{
						$nsize = explode('x',preg_replace("/[^0-9x]/",'',$_GET['resize']));
						if(in_array($_FILES[$fileElementName]['type'], array('image/gif','image/png','image/jpeg')))
						{
							img_resize($fname, $fname, $nsize[0],$nsize[1],isset($_GET['wm']));
						}
					}
					else if(isset($_GET['wm'])) 
					{
						if(in_array($_FILES[$fileElementName]['type'], array('image/gif','image/png','image/jpeg')))
						{
							$fsize = getimagesize($fname);
							img_resize($fname, $fname, $fsize[0], $fsize[1], true, isset($_GET['wmpos']) ? $_GET['wmpos'] : 'center_middle');
						}
					}
				}
			}
			echo "{
	type: '$type',\n
	error: '$error',\n
	fname: '$fname',\n
	fnamenp: '" . basename($fname) . "'\n
	}";
			break;
		case 'drop':
			header('Content-Type: text/plain; charset=utf-8');
			$path = GetPath($_GET['path'],(isset($_GET['ibid']) ? $_GET['ibid'] : false),(isset($_GET['year']) ? $_GET['year'] : false),(isset($_GET['month']) ? $_GET['month'] : false),(isset($_GET['id']) ? $_GET['id'] : false));
			$fname = preg_replace("/[^0-9a-zA-Z_\.]/",'',$_GET['file']);
			$th_fname = $path.'th_'.$fname;
			$fname = $path.$fname;
			if(!file_exists($fname))
			{
				//die(__('Error').': '.__('File doesn\'t exists').': '.$fname);
				die('success');
			}
			else if(unlink($fname))
			{
				if(!file_exists($th_fname))
				{
					//die(__('Error').': '.__('File doesn\'t exists').': '.$fname);
					die('success');
				}
				else if(unlink($th_fname))
				{
					die('success');
				}
			}
			else
			{
				die(__('Error').': '.__('No rights to delete this file'));
			}
			break;
		case 'formfdpart':
			header('Content-Type: text/plain; charset=utf-8');
			$nid = rcms_random_string(4);
			$att = '';
			$style = '';
			$file = preg_replace("/[^0-9a-zA-Z_\.]/",'',$_GET['file']);
			$dvalue = GetPath($_GET['path'],(isset($_GET['ibid']) ? $_GET['ibid'] : false),(isset($_GET['year']) ? $_GET['year'] : false),(isset($_GET['month']) ? $_GET['month'] : false),(isset($_GET['id']) ? $_GET['id'] : false)).$file;
			if(!file_exists($dvalue))
			{
				die('error');
			}
			switch ($_GET['tpl'])
			{
				case 'image':
					$tpl = '<img alt="'.$file.'" src="{dvalue}" '.(!empty($_GET['dwidth']) ? 'width="'.vf($_GET['dwidth'],3).'"' : '').'>';
					$style = 'display: none;';
					break;
				case 'ibimage':
					$tpl = '<a title="'.__('Click to paste image').'" href="{dvalue}" id="pasteimglink'.$nid.'" onclick="return addImage(\''.vf($_GET['taid'],1).'\',\''.$nid.'\');"><img src="{dvalue}" '.(!empty($_GET['dwidth']) ? 'width="'.vf($_GET['dwidth'],3).'"' : '').'></a>';
					$style = 'display: none;';
					break;
				case 'ibfile':
					$type = preg_replace("/[^0-9a-zA-Z\/\-]/",'',$_GET['type']);
					$size = hcms_filesize($dvalue);
					$tpl = '<a href="{dvalue}" style="font-weight: bold;" title="'.$file.'">'.$file.'</a><br>- '.__('Size').': '.$size.'<br>- '.__('Type').': '.$type;
					$att = '_f';
					$style = 'display: none;';
					break;
				case 'galleryimage':
					$tpl = '<img src="{dvalue}" width="150">';
					$att = '_f';
					$style = 'display: none;';
					break;
				default:
					die('error');
					break;
			}

			$asyncmgr = new AsyncMgr(false);
			print(json_encode(array(
			'id' => $nid,
			'fname' => $dvalue,
			'fnamenp' => $file,
			'display' => $asyncmgr->addDispPart($nid,vf($_GET['idpart'],1),$tpl,$dvalue,$file,(isset($att) ? $att : ''),(isset($style) ? $style : ''))
			)));

			break;
		default:
			include(ADMIN_PATH . 'frameset.php');
			break;
	}
}
?>