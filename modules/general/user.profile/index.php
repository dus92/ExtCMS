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

if (!empty($_POST['registration_form']) && !LOGGED_IN) {
	if(isset($_GET['admin']))
	{
//		@unlink(RCMS_ROOT_PATH.'install.php');
	}
    $system->registerUser($_POST['username'], $_POST['nickname'], @$_POST['password'], @$_POST['confirmation'], $_POST['email'], $_POST['userdata']);
    $system->config['pagename'] = __('Registration');
    show_window('', $system->results['registration'], 'center');
} elseif (!empty($_POST['profile_form']) && LOGGED_IN) {
    if(hcms_hash(@$_POST['current_password']) == $system->user['password']){
        $system->updateUser($system->user['username'], $_POST['nickname'], $_POST['password'], $_POST['confirmation'], $_POST['email'], $_POST['userdata']);
        $system->config['pagename'] = __('My profile');
        show_window('', $system->results['profileupdate'], 'center');
    } else {
        show_error(__('Invalid password'));
    }
} elseif (!empty($_POST['password_request']) && !LOGGED_IN) {
    $system->recoverPassword($_POST['name'], $_POST['email']);
    $system->config['pagename'] = __('Password recovery');
    show_window('', $system->results['passrec'], 'center');
}

// Basic data
$act = (!empty($_GET['act'])) ? $_GET['act'] : '';

if (($act == 'register' || $act == '') && !LOGGED_IN) {
    $system->config['pagename'] = __('Registration');
    show_window(__('Registration'), rcms_parse_module_template('user-profile.tpl', array(
        'mode' => 'registration_form',
        'fields' => $system->data['apf'])));
} elseif ($act == 'password_request' && !LOGGED_IN) {
    $system->config['pagename'] = __('Password recovery');
    show_window(__('Password recovery'), rcms_parse_module_template('user-respas.tpl', array()));
} elseif (LOGGED_IN) {
    $system->config['pagename'] = __('My profile');
    show_window(__('My profile'), rcms_parse_module_template('user-profile.tpl', array(
        'mode' => 'profile_form',
        'fields' => $system->data['apf'],
        'values' => rcms_htmlspecialchars_recursive($system->user))));
}
?>