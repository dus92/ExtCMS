<?php
// rebuild iblocks cache form
$frm = new InputForm('','post',__('Rebuild iBlocks structure cache'));
if(!empty($_POST['rebuild_cache']))
{
	$iblock = new iBlock();
	if($iblock->CreateStructCache())
	{
		$frm->addmessage(__('iBlocks structure cache rebuilt and saved'));
	}
}
$frm->addbreak(__('Configure iBlocks'));
$frm->hidden('rebuild_cache',1);
$frm->show();

// iblocks management
$frm = new InputForm('','post',__('Submit'));
if(!empty($_POST['save']))
{
	$system->ibconfig['interface'] = hcms_clean_array($_POST['interface'], 'wide');
	if(file_write_contents(CONFIG_PATH.'ibconfig.dat',pack_data($system->ibconfig)))
	{
		$frm->addmessage(__('Data saved'));
	}
	
	if(!empty($_POST['interface']['enable_simplified_menu']) && @filesize(IBLOCKS_PATH.'items_simplified_menu.dat')<1)
	{
		$iblock->CreateStructCache();
	}
}

// form
$frm->addbreak(__('iBlocks management interface'));
$frm->hidden('save',1);
$frm->addrow(__('Mode'),$frm->select_tag('interface[mode]',array('simple' => __('Simple'), 'advanced' => __('Advanced')),@$system->ibconfig['interface']['mode']));
$frm->addrow(__('Editor mode'),$frm->select_tag('interface[editor_mode]',array('default' => __('Default'), 'tinymce' => __('TinyMCE')),@$system->ibconfig['interface']['editor_mode']));
$frm->addrow('',$frm->checkbox('interface[enable_simplified_menu]',1,__('Generate simplified iblock\'s menu'),@$system->ibconfig['interface']['enable_simplified_menu']));
$frm->show();
?>