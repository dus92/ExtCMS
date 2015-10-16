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

if(!($system->checkForRight('IBLOCKS-EDITOR') && $system->checkForRight('IBLOCKS-STATIC_PAGES-EDITOR')))
{
	rcms_showAdminMessage(__('You do not have rights to edit items in these iblock'));
	exit;
}

$frm = new InputForm('admin.php?show=module&id=iblocks.post','post',__('Create static page'));
$frm->addbreak(__('Static pages'));
$frm->hidden('contid', 'static_pages');
$frm->hidden('ibid', 'articles');
$frm->show();

$iblockitem = new iBlockItem();

$iblockitem->BeginiBlockItemsListRead(array('contid' => 'static_pages'),array('id','title','idate','uid'), false, 'idate','DESC');
$frm = new InputForm('admin.php?show=module&id=iblocks.items','post',__('Submit'),__('Reset'));
$i=0;
while ($item = $iblockitem->Read())
{
	$frm->addrow('#'.$item['id'].' <b>'.$item['title'].'</b>', $frm->checkbox('delete[' . $item['id'] . ']', '1', __('Delete')).' '.$frm->radio_button('edit',array($item['id'] => __('Edit')),0));
	$i++;
}
$frm->show();
?>