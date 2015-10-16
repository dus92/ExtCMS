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

if(!empty($_GET['user']) && $userdata = load_user_info(basename($_GET['user']))){
    $system->config['pagename'] = __('User profile of') . ' ' . $userdata['username'];
    show_window('', rcms_parse_module_template('user-view.tpl', array('userdata' => $userdata, 'fields' => $system->data['apf'])));
}if(!empty($_GET['nick']) && $userdata = load_user_info(basename($system->users_cache->getUser('nicks', $_GET['nick'])))){
    $system->config['pagename'] = __('User profile of') . ' ' . $userdata['username'];
    show_window('', rcms_parse_module_template('user-view.tpl', array('userdata' => $userdata, 'fields' => $system->data['apf'])));
} else {
    $system->config['pagename'] = __('Member list');
    $userlist = $system->getUserList('*', 'nickname');
    ksort($userlist);
    show_window(__('Member list'), rcms_parse_module_template('user-list.tpl', $userlist));
}
?>