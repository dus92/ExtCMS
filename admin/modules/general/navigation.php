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
if(!empty($_POST['urls']) && !empty($_POST['names']) && is_array($_POST['urls']) && is_array($_POST['names'])){
	if(sizeof($_POST['urls']) !== sizeof($_POST['names'])){
		rcms_showAdminMessage(__('Error occurred'));
	} else {
		$result = array();
		foreach ($_POST['urls'] as $i => $url) {
			if(!empty($url)){
				if(!empty($_POST['ext'][$i])) {
					$ins['url'] = 'external:' . $url;
				} else {
					$ins['url'] = $url;
				}
				$ins['name'] = $_POST['names'][$i];
				$result[] = $ins;
			}
		}
		write_ini_file($result, CONFIG_PATH . 'navigation.ini', true) or rcms_showAdminMessage(__('Error occurred'));
	}
} elseif (!empty($_POST['addlink']) && !empty($_POST['addlink']['url'])) {
	$links = parse_ini_file(CONFIG_PATH . 'navigation.ini', true);
	$links[] = $_POST['addlink'];
	write_ini_file($links, CONFIG_PATH . 'navigation.ini', true) or rcms_showAdminMessage(__('Error occurred'));
}

$links = parse_ini_file(CONFIG_PATH . 'navigation.ini', true);

$frm = new InputForm ('', 'post', __('Submit'));
$frm->addbreak(__('Navigation editor'));
$frm->addrow(__('Link'), __('Title'));
$i = 0;
foreach ($links as $link){
	$tmp = explode(':', $link['url'], 2);
	$checked = $tmp[0] == 'external';
	if($checked){
		$link['url'] = $tmp[1];
	}
	$frm->addrow($frm->text_box('urls[' . $i . ']', $link['url']), $frm->text_box('names[' . $i . ']', $link['name']) . $frm->checkbox('ext[' . $i . ']', '1', __('Open in new window'), $checked));
	$i++;
}
$frm->addrow($frm->text_box('urls[' . $i . ']', ''), $frm->text_box('names[' . $i . ']', '') . $frm->checkbox('ext[' . $i . ']', '1', __('Open in new window')));
$frm->addmessage(__('If you want to remove link leave it\'s URL empty. If you want to add new item fill in the last row.'));
$frm->addmessage(__('You can use modifiers to create link to specified part of your site. Type MODIFIER:OPTIONS in "Link" column. If you want to override default title of modified link you must enter your title to "Title" column, or leave it empty to use default one. Here is a list of modifiers:'));
foreach ($system->navmodifiers as $modifier => $options){
	$frm->addrow($modifier, call_user_func($system->navmodifiers[$modifier]['h']));
}
$frm->show();
?>