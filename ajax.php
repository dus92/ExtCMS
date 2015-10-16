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

if($_REQUEST['module'] !== 'installer')
{
	include(RCMS_ROOT_PATH . 'common.php');
}
error_reporting(E_ERROR);

// abnormal server? he-he
// (if you're watching many errors using asynchronous file upload, try set it true)
define("ABNORMAL",true);


//------------------------------------------------------------------------------------------------------//
// preparations...

$rights = &$system->rights;
$root   = &$system->root;


switch($_REQUEST['module'])
{
	case 'imail' :
		require_once('admin/ajax/imail.php');
		break;
    case 'instagram' :
		require_once('modules/general/instagram/index.php');
		break;
	case 'installer':
		require_once('modules/system/installer.php');
		switch ($_REQUEST['action'])
		{
			case 'removeInstaller':
				$answ = remove_installer();
				break;
			case 'checkModules':
				$answ = check_modules();
				break;
			case 'checkRights':
				$answ = check_rights();
				break;
			case 'dbinstall':
				$answ = db_install($_REQUEST['Host'],
				$_REQUEST['Port'],
				$_REQUEST['User'],
				$_REQUEST['Password'],
				$_REQUEST['Create'],
				$_REQUEST['DBname'],
				$_REQUEST['Prefix'],
				$_REQUEST['iBlocksTables'],
				$_REQUEST['UsersTables'],
				$_REQUEST['Engine']);
				break;
			case 'infbinstall':
				$answ = infb_install($_REQUEST['mode'],
				$_REQUEST['editor_mode'],
				$_REQUEST['enable_simplified_menu'],
				$_REQUEST['timezone']);
				break;
			case 'loadext':
				$answ = upl_extension();
				break;
			case 'getExtensions':
				$answ = get_extensions();
				break;
			case 'remExtension':
				$answ = remove_extension($_REQUEST['file_name']);
				break;
			case 'extensionsInstallation':
				$answ = extensions_installation();
				break;
			case 'getTimezones':
				$answ = getTimezone();
				break;
			default:
				$answ = "Unknown command";
				break;
		}
		echo json_encode($answ);
		break;
	case 'calendar':
		header('Content-Type: text/html; charset=utf-8');

		$sel_ibid = vf($_GET['ibid'],4);
		$iblockitem = new iBlockItem();

		$current_year  = rcms_format_time('Y', mktime());     // Текущий год
		$current_month = rcms_format_time('n', mktime());     // Текущий месяц
		// Выбор года для отображения в календаре.
		// Он не может быть больше текущего,
		// а нижняя граница = $current_year - 6,
		// т.е в поле выбора года будет отображаться диапазон 2003 - 2009.
		$year = intval($_REQUEST['cal_year']);
		if (!empty($year) && $year >= $current_year - 6 && $year <= $current_year+1)
		{
			$selected_year = $year;             // Выбор года. Если не сделан -
			unset($year);
		}
		else
		{
			$selected_year = $current_year;                  // текущий год.
		}
		$month = intval($_REQUEST['cal_month']);
		if (!empty($month) && $month >= 1 && $month <= 12)
		{
			$selected_month = $month;           // Выбор месяца. Если не сделан -
			unset($month);
		}
		else
		{
			$selected_month = $current_month;                // текущий месяц.
		}

		$cur_month_ts = strtotime($selected_year.'-'.$selected_month.'-01');
		$prev_month_ts=$cur_month_ts-(3600*24*2);
		$next_month_ts=$cur_month_ts+(3600*24*32);

		$calendar = new calendar($selected_month, $selected_year);
		$iblockitem->BeginiBlockItemsListRead(array('ibid' => $sel_ibid), array('id','idate'),false,'','','MONTH(idate)='.$selected_month.' AND YEAR(idate)='.$selected_year.'');
		while($item = $iblockitem->Read())
		{
			$time = strtotime($item['idate']);
			if ((rcms_format_time('n', $time) == $selected_month) && (rcms_format_time('Y', $time) == $selected_year)) {
				// Формируем ссылки на статьи только за выбранный месяц и год.
				// Если выбор не сделан - формируются ссылки только за текущие месяц и год.
				$calendar->assignEvent(rcms_format_time('d', $time), '?module=iblocks&ibid=calendar&from='.mktime(0, 0, 0, $selected_month, rcms_format_time('d', $time), $selected_year).'&until='.mktime(23, 59, 59, $selected_month, rcms_format_time('d', $time), $selected_year));
			}


		}
		if($selected_month == $current_month)
		{
			$calendar->highlightDay(rcms_format_time('d', mktime()));
		}

		$date_pick =
		'<tr>
	<td colspan="7">
<script type="text/javascript">
$(document).ready(
function ()
{
	$("#next_month_link").click(function()
	{
		$.get("ajax.php",
		{
			module: "calendar",
			ibid: "'.$sel_ibid.'",
			cal_month: "'.date("m",$next_month_ts).'",
			cal_year: "'.date("Y",$next_month_ts).'"
		}, OnReloadCalendarReqSuccess);
	});
	
	$("#prev_month_link").click(function()
	{
		$.get("ajax.php",
		{
			module: "calendar",
			ibid: "'.$sel_ibid.'",
			cal_month: "'.date("m",$prev_month_ts).'",
			cal_year: "'.date("Y",$prev_month_ts).'"	
		}, OnReloadCalendarReqSuccess);
	});
	
	function OnReloadCalendarReqSuccess(data)
	{
		$("#calendar_div").html(data);
	}
});
</script>
<a href="#" onclick="return false;" id="prev_month_link" class="link_all">&lt;&lt;&lt;</a> <strong>'.$lang['datetime'][date("F",$cur_month_ts)].'</strong> <a href="#" onclick="return false;" id="next_month_link" class="link_all">&gt;&gt;&gt;</a></td>
</tr>';
		print($calendar->returnCalendar().$date_pick.'</tbody></table>');
		break;
	case 'files':
		if($system->checkForRight('UPLOAD'))
		{
			switch ($_REQUEST['action'])
			{
				case 'upload':
					header('Content-Type: text/html; charset=utf-8');
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
						$fname = preg_replace("/[^0-9a-zA-Z_\.]/",'_',ru2lat(basename($_FILES[$fileElementName]['name'])));
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
					die('error: action not specified');
					break;
			}
		}
		break;
	default:
		die('error: module not specified');
		break;
}
?>