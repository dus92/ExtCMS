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
if(isset($_POST['remarks'])) {
	file_write_contents(DATA_PATH . 'admin_remarks.txt', $_POST['remarks']);
}
$frm = new InputForm ('', 'post', __('Submit'));
$frm->addbreak(__('Welcome to administration panel'));
if(!$root) {
	foreach ($rights as $right => $right_desc) {
		$frm->addrow($right, $right_desc, 'top');
	}
} else {
	$frm->addrow(__('You have all rights on this site'));
}
$frm->addbreak(__('Information'));
$frm->addrow('httpd', $_SERVER['SERVER_SOFTWARE']);
$frm->addrow('php', phpversion());
$frm->addrow('Hakkah ~ CMS', RCMS_VERSION_A . '.'  . RCMS_VERSION_B. '.'  . RCMS_VERSION_C);

if($system->checkForRight('IBLOCKS-EDITOR')){
	$count = 0;
	$frm->addrow($count . ' ' . __('article(s) awaits moderation'));
}
if($system->checkForRight('SUPPORT')) {
	$count = sizeof(guestbook_get_msgs(null, true, false, DF_PATH . 'support.dat'));
	$frm->addrow($count . ' ' . __('feedback requests in database'));
}
$frm->addbreak(__('Here you can leave message for other administrators'));
$frm->addrow($frm->textarea('remarks', file_get_contents(DATA_PATH . 'admin_remarks.txt'), 60, 10), '', 'middle', 'center');
$frm->show();
?>