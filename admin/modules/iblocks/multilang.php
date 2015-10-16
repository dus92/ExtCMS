<?php 
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) Hakkah~CMS Development Team                                //
//   http://hakkahcms.sourceforge.net                                         //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////

$langs = $system->data['languages'];
unset($langs[$system->config['default_lang']]);
$li = sizeof($langs);
$lkeys = array_keys($langs);

if(isset($_POST['save']))
{
	for($s = 0; $s < $li; $s++)
	{
		file_write_contents(IBLOCKS_PATH.'langs/'.$lkeys[$s].'.dat', pack_data($_POST[$lkeys[$s].'_translate']));
		rcms_showAdminMessage(__('Translate strings for').' '.$langs[$lkeys[$s]].' '.__('saved'));
	}
	//	rcms_showAdminMessage(__('Tasks config file saved'));
}

$frm = new InputForm ('', 'post', __('Submit'));
$frm->addbreak(__('iBlock strings language translations'));
$frm->hidden('save',1);

for($s = 0; $s < $li; $s++)
{
	if(file_exists(IBLOCKS_PATH.'langs/'.$lkeys[$s].'.dat'))
	{
		$langdata[$s] = unpack_data(file_get_contents(IBLOCKS_PATH.'langs/'.$lkeys[$s].'.dat'));
	}
}

$frm->addbreak(__('iBlock groups'));
$size = sizeof($system->ibgroups);
$keys = array_keys($system->ibgroups);
for($i = 0; $i < $size; $i++)
{
	$tb_string = '';
	for($s = 0; $s < $li; $s++)
	{
		$tb_string .= $langs[$lkeys[$s]].': '.$frm->text_box($lkeys[$s].'_translate['.$system->ibgroups[$keys[$i]]['title'].']', (empty($langdata[$s][$system->ibgroups[$keys[$i]]['title']]) ? $system->ibgroups[$keys[$i]]['title'] : $langdata[$s][$system->ibgroups[$keys[$i]]['title']]));
	}
	$frm->addrow('<b>'.$system->ibgroups[$keys[$i]]['title'].'</b>',$tb_string);
}

$frm->addbreak(__('iBlocks'));
$size = sizeof($system->iblocks);
$keys = array_keys($system->iblocks);
for($i = 0; $i < $size; $i++)
{
	$tb_string1 = '';
	$tb_string2 = '';
	for($s = 0; $s < $li; $s++)
	{
		$tb_string1 .= $langs[$lkeys[$s]].': '.$frm->text_box($lkeys[$s].'_translate['.$system->iblocks[$keys[$i]]['title'].']', (empty($langdata[$s][$system->iblocks[$keys[$i]]['title']]) ? $system->iblocks[$keys[$i]]['title'] : $langdata[$s][$system->iblocks[$keys[$i]]['title']]));
		$tb_string2 .= (empty($system->iblocks[$keys[$i]]['description']) ? '' : $langs[$lkeys[$s]].': '.$frm->textarea($lkeys[$s].'_translate['.$system->iblocks[$keys[$i]]['description'].']', (empty($langdata[$s][$system->iblocks[$keys[$i]]['description']]) ? $system->iblocks[$keys[$i]]['description'] : $langdata[$s][$system->iblocks[$keys[$i]]['description']])));
	}
	$frm->addrow('<b>'.$system->iblocks[$keys[$i]]['title'].'</b>',$tb_string1);
	if(!empty($tb_string2))
	{
		$frm->addrow($system->iblocks[$keys[$i]]['description'],$tb_string2);
	}
}

$frm->addbreak(__('Containers'));
$size = sizeof($system->containers);
$keys = array_keys($system->containers);
for($i = 0; $i < $size; $i++)
{
	$tb_string1 = '';
	$tb_string2 = '';
	for($s = 0; $s < $li; $s++)
	{
		$tb_string1 .= $langs[$lkeys[$s]].': '.$frm->text_box($lkeys[$s].'_translate['.$system->containers[$keys[$i]]['title'].']', (empty($langdata[$s][$system->containers[$keys[$i]]['title']]) ? $system->containers[$keys[$i]]['title'] : $langdata[$s][$system->containers[$keys[$i]]['title']]));
		$tb_string2 .= (empty($system->containers[$keys[$i]]['description']) ? '' : $langs[$lkeys[$s]].': '.$frm->textarea($lkeys[$s].'_translate['.$system->containers[$keys[$i]]['description'].']', (empty($langdata[$s][$system->containers[$keys[$i]]['description']]) ? $system->containers[$keys[$i]]['description'] : $langdata[$s][$system->containers[$keys[$i]]['description']])));
	}
	$frm->addrow('<b>'.$system->containers[$keys[$i]]['title'].'</b>',$tb_string1);
	if(!empty($tb_string2))
	{
		$frm->addrow($system->containers[$keys[$i]]['description'],$tb_string2);
	}
}

$frm->addbreak(__('Categories'));
$categories = array();
$iblock->BeginCategoriesListRead($contid);
while($catdata = $iblock->Read())
{
	$categories[$catdata['catid']] = $catdata;
}

$size = sizeof($categories);
$keys = array_keys($categories);
for($i = 0; $i < $size; $i++)
{
	$tb_string1 = '';
	$tb_string2 = '';
	for($s = 0; $s < $li; $s++)
	{
		$tb_string1 .= $langs[$lkeys[$s]].': '.$frm->text_box($lkeys[$s].'_translate['.$categories[$keys[$i]]['title'].']', (empty($langdata[$s][$categories[$keys[$i]]['title']]) ? $categories[$keys[$i]]['title'] : $langdata[$s][$categories[$keys[$i]]['title']]));
		$tb_string2 .= (empty($categories[$keys[$i]]['description']) ? '' : $langs[$lkeys[$s]].': '.$frm->textarea($lkeys[$s].'_translate['.$categories[$keys[$i]]['description'].']', (empty($langdata[$s][$categories[$keys[$i]]['description']]) ? $categories[$keys[$i]]['description'] : $langdata[$s][$categories[$keys[$i]]['description']])));
	}
	$frm->addrow('<b>'.$categories[$keys[$i]]['title'].'</b>',$tb_string1);
	if(!empty($tb_string2))
	{
		$frm->addrow($categories[$keys[$i]]['description'],$tb_string2);
	}
}

$frm->show();
?>