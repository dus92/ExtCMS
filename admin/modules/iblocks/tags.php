<?php
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) Hahhah~CMS Development Team                                //
//   http://hakkahcms.sourceforge.net                                         //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////

$iblock = new iBlock();
$iblock->BeginContainersListRead(false,array('contid'));
$i=0;
while ($container = $iblock->Read())
{
	$containers[$i] = $container['contid'];
	$i++;
}
$count = sizeof($containers);

$frm = new InputForm('','post');
$frm->hidden('save','1');
for($i=0; $i<$count; $i++)
{
	if(!empty($_POST['save']))
	{
		file_write_contents(DATA_PATH.'iblocks/tags_'.$containers[$i].'.dat',$_POST['tags_'.$containers[$i]],"w");
		rcms_showAdminMessage(__('Tags for '.$containers[$i].' changed!'));
	}

	$frm->addrow(__('Tags -> '.$containers[$i]).'<br><b>'.__('one for line').'</b>', $frm->textarea('tags_'.$containers[$i],@file_get_contents(DATA_PATH.'iblocks/tags_'.$containers[$i].'.dat'),90,15));
}
$frm->show();
?>