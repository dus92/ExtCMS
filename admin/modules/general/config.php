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

if(!empty($_POST['nconfig']))
{
	if(!empty($_POST['nconfig']['watermark']))
	{
		$wm = IBLOCKS_PATH.preg_replace("/[^0-9a-zA-Z_\.]/",'',$_POST['nconfig']['watermark']);
		$_POST['nconfig']['watermark'] = (file_exists($wm) ? $wm : '');
	}
	else
	{
		$_POST['nconfig']['watermark'] = '';
	}
	
	$tzs = DateTimeZone::listIdentifiers();
	$_POST['nconfig']['timezone'] = $tzs[(int)$_POST['nconfig']['timezone']];
	write_ini_file($_POST['nconfig'], CONFIG_PATH . 'config.ini');
}
if(isset($_POST['meta_tags'])) file_write_contents(DATA_PATH . 'meta_tags.html', $_POST['meta_tags']);
if(isset($_POST['welcome_mesg'])) file_write_contents(DATA_PATH . 'intro.html', $_POST["welcome_mesg"]);

$system->config = parse_ini_file(CONFIG_PATH . 'config.ini');
$config = &$system->config;

$avaible_modules = array();
$avaible_modules[''] = __('Latest news');
foreach ($system->modules['main'] as $module => $module_data){
	if($module !== 'index'){
		$avaible_modules[$module] = $module_data['title'];
	}
}

// Interface generation
$frm =new InputForm ('', 'post', __('Submit'));
$frm->addbreak(__('Site configuration'));
$frm->addrow(__('Your site\'s title'), $frm->text_box("nconfig[title]", $config['title'], 40));
$frm->addrow(__('Your site\'s short title'), $frm->text_box("nconfig[short_title]", @$config['short_title'], 40));
$frm->addrow(__('Do not show sitename in title'), $frm->checkbox('nconfig[hide_title]', '1', '', @$config['hide_title']));
$frm->addrow(__('Your site\'s URL') . '<br />' . __('Leave empty for autodetect'), $frm->text_box("nconfig[site_url]", $config['site_url'], 40));
$frm->addrow(__('Copyright for your content'), $frm->text_box("nconfig[copyright]", @$config['copyright'], 60));
$frm->addrow(__('Enable RSS'), $frm->checkbox('nconfig[enable_rss]', '1', '', @$config['enable_rss']));
$frm->addrow(__('Enable logging'), $frm->checkbox('nconfig[logging]', '1', '', @$config['logging']));
$frm->addrow(__('Enable IDS (logging must be enabled)'), $frm->checkbox('nconfig[enable_ids]', '1', '', @$config['enable_ids']));
$frm->addrow(__('Number of element that will be considered as latest'), $frm->text_box('nconfig[num_of_latest]', @$config['num_of_latest']));
$frm->addrow(__('Number of elements per page'), $frm->text_box('nconfig[perpage]', @$config['perpage']));
$frm->addrow(__('Number of items per page [admin panel]'), $frm->text_box('nconfig[adm_perpage]', @$config['adm_perpage']));
$frm->addrow(__('Module on index page'), $frm->select_tag('nconfig[index_module]', $avaible_modules, @$config['index_module']));
$frm->addrow(__('Hide welcome message'), $frm->checkbox('nconfig[wmh]', '1', '', @$config['wmh']));
$frm->addrow(__('Text of Welcome message'), $frm->textarea('welcome_mesg', file_get_contents(DATA_PATH . 'intro.html'), 80, 10), 'top');
$frm->addrow(__('Additional meta tags for your site'), $frm->textarea('meta_tags', file_get_contents(DATA_PATH . 'meta_tags.html'), 80, 5), 'top');

$asyncmgr = new AsyncMgr();
$asyncmgr->printImgUpFormJS('watermark','image',false, true, array('load_stopped_3.gif','load_process_3.gif'),false);

$frm->addbreak(__('iBlocks options'));
$frm->addrow(__('Thumbnails size'), $frm->text_box('nconfig[th_width]', @$config['th_width']).' x '.$frm->text_box('nconfig[th_height]', @$config['th_height']));
$frm->addrow(__('Watermarks'), $frm->checkbox('nconfig[enable_wms]', '1', '', @$config['enable_wms'], 'title="'.__('All images with suitable size will be watermarked').'"'));
$frm->addrow(__('Alpha level (default 15)'), $frm->text_box('nconfig[wm_alpha_level]', @$config['wm_alpha_level']));
//$frm->addrow(__('Default position'), $frm->select_tag('nconfig[wm_position]', array(),@$config['wm_position']));
if(file_exists(@$config['watermark']))
{
	$asyncmgr->addEditPart(__('Watermark file'),$frm,'image','<img src="{dvalue}">',$config['watermark'],basename($config['watermark']),'nconfig[watermark]','load_stopped_3.gif');
}
else
{
	$asyncmgr->addAddPart(__('Watermark file'),$frm,'image','nconfig[watermark]','load_stopped_3.gif');
}
$frm->addbreak(__('Interaction with user'));
$frm->addrow(__('Disallow user selection of password in registration form'), $frm->checkbox('nconfig[regconf]', '1', '', @$config['regconf']));
$frm->addrow(__('Period when one password request can be acomplished (seconds)'), $frm->text_box('nconfig[pr_flood]', @$config['pr_flood']));
$frm->addrow(__('Access level for registered users'), $frm->text_box('nconfig[registered_accesslevel]', @$config['registered_accesslevel']));
$frm->addrow(__('Try to detect user\'s language'), $frm->checkbox('nconfig[detect_lang]', '1', '', @$config['detect_lang']));
$frm->addrow(__('Default skin'), user_skin_select(SKIN_PATH, 'nconfig[default_skin]', $config['default_skin']));
$frm->addrow(__('Allow users to select skin'), $frm->checkbox('nconfig[allowchskin]', '1', '', @$config['allowchskin']));
$frm->addrow(__('Default language'), user_lang_select('nconfig[default_lang]', $config['default_lang']));
$frm->addrow(__('Allow users to select language'), $frm->checkbox('nconfig[allowchlang]', '1', '', @$config['allowchlang']));
//$frm->addrow(__('Default timezone'), user_tz_select((int)@$config['default_tz'], 'nconfig[default_tz]'));
$tzs = DateTimeZone::listIdentifiers();
$key = array_search($config['timezone'],$tzs);
$frm->addrow(__('Default timezone'), $frm->select_tag('nconfig[timezone]', $tzs, $key));
$frm->show();
?>