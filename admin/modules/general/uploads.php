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
rcms_loadAdminLib('file-uploads');

/******************************************************************************
* Perform uploading                                                           *
******************************************************************************/
if(!empty($_FILES['upload'])) {
	if(@$_POST['preview']==1)
		$ok=fupload_array($_FILES['upload'],PREVIEWS_PATH);
	else
		$ok=fupload_array($_FILES['upload']);
	
    if($ok){
        @rcms_showAdminMessage(__('Files uploaded').': '.(empty($_FILES['upload']['name'][0]) ? 'none' : '<input size="25" value="'.$_FILES['upload']['name'][0]).'">'.' | '.(empty($_FILES['upload']['name'][1]) ? 'none' : '<input size="25" value="'.$_FILES['upload']['name'][1].'">').' | '.(empty($_FILES['upload']['name'][2]) ? 'none' : '<input size="25" value="'.$_FILES['upload']['name'][2].'">'));
    } else {
        rcms_showAdminMessage(__('Error occurred'));
    }
}

/******************************************************************************
* Perform deletion                                                            *
******************************************************************************/
if(!empty($_POST['delete'])) {
    $result = '';
    foreach ($_POST['delete'] as $file => $cond){
        $file = basename($file);
        if(!empty($cond)) {
            if(fupload_delete($file)) $result .= __('File removed') . ': ' . $file . '<br>';
            else $result .= __('Error occurred') . ': ' . $file . '<br>';
        }
    }
    if(!empty($result)) rcms_showAdminMessage($result);
}

/******************************************************************************
* Interface                                                                   *
******************************************************************************/
$frm =new InputForm ('', 'post', __('Submit'), '', '', 'multipart/form-data');
$frm->addbreak(__('Upload files'));
$frm->addrow(__('Select files to upload'), $frm->file('upload[]') . $frm->file('upload[]') . $frm->file('upload[]'), 'top');
$frm->addrow(__('Preview files?'), $frm->checkbox('preview',1,' Warning! This files will be uploaded to another directory.'), 'top');
$frm->show();
$files = fupload_get_list();
$frm =new InputForm ('', 'post', __('Submit'));
$frm->addbreak(__('Uploaded files'));
if(!empty($files)) {
    foreach ($files as $file) {
        $frm->addrow(__('Filename') . ' = ' . $file['name'] . ' [' . __('Size of file') . ' = ' . $file['size'] . '] [' . __('Last modification time') . ' = ' . date("d F Y H:i:s", $file['mtime']) . ']', $frm->checkbox('delete[' . $file['name'] . ']', 'true', __('Delete')), 'top');
    }
}
$frm->show();
?>