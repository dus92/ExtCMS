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
if(empty($_POST['search']))
{
	$_POST['search'] = '*';
}

if(!empty($_POST['block']) && is_array($_POST['block'])){
    $res = '';
    foreach ($_POST['block'] as $username=>$block) {
        if($block) $res .= (user_change_field($username, 'blocked', '1')) ? __('Blocked') . ': ' . $username . '<br>' : __('Error occurred') . ': ' . $username . '<br>';
    }
    rcms_showAdminMessage($res);
}
if(!empty($_POST['unblock']) && is_array($_POST['unblock'])){
    $res = '';
    foreach ($_POST['unblock'] as $username=>$unblock) {
        if($unblock) $res .= (user_change_field($username, 'blocked', '0')) ? __('Unblocked') . ': ' . $username . '<br>' : __('Error occurred') . ': ' . $username . '<br>';
    }
    rcms_showAdminMessage($res);
}
if(!empty($_POST['delete']) && is_array($_POST['delete'])){
    $res = '';
    foreach ($_POST['delete'] as $username=>$delete) if($delete) $res .= (user_delete($username)) ? __('Deleted') . ': ' . $username . '<br>' : __('Error occurred') . ': ' . $username . '<br>';
    rcms_showAdminMessage($res);
}
if(!empty($_POST['username']) && !empty($_POST['save'])){
    $system->updateUser($_POST['username'], $_POST['nickname'], $_POST['password'], $_POST['confirmation'], $_POST['email'], $_POST['userdata'], true);
    rcms_showAdminMessage($system->results['profileupdate']);
}
if(!empty($_POST['rights']) && !empty($_POST['save'])){
	$mysql_data['qcache']['enable'] = false;
    if($system->setRightsForUser($_POST['rights'], @$_POST['_rights'], @$_POST['rootuser'], @$_POST['level'])) {
        rcms_showAdminMessage(__('Rights changed'));
    } else {
        rcms_showAdminMessage(__('Error occurred'));
    }
}

/******************************************************************************
* Interface                                                                   *
******************************************************************************/
$frm =new InputForm ('', 'post', __('Find users'));
$frm->addrow(__('Enter username or mask of usernames'), $frm->text_box('search', @$_POST['search']));
$frm->show();
if(!empty($_POST['edit']) && $userdata = load_user_info($_POST['edit'])){
    $frm =new InputForm ('', 'post', __('Submit'));
    $frm->addbreak($userdata['username']);
    $frm->hidden('username', $userdata['username']);
    $frm->hidden('save', '1');
    $frm->addrow(__('Username'), $userdata['username']);
    $frm->addrow(__('New password') . '<br><small>' . __('if you do not want change password you must leave this field empty'), $frm->text_box('password', ''));
    $frm->addrow(__('Confirm password'), $frm->text_box('confirmation', ''));
    $frm->addrow(__('Nickname'), $frm->text_box('nickname', $userdata['nickname']));
    $frm->addrow(__('E-mail'), $frm->text_box('email', $userdata['email']));
    $frm->addrow(__('Hide e-mail from other users'), $frm->checkbox('userdata[hideemail]', '1', '', ((!isset($userdata['hideemail'])) ? true : ($userdata['hideemail']) ? true : false)));
    
    $tzs = DateTimeZone::listIdentifiers();
    $selected = array_search($userdata['timezone'], $tzs);
    
    $data = '<select name="userdata[timezone]">' . "\n";
    foreach($tzs as $value => $text)
    {
    	$data .= '<option value="' . $value . '" ' . (($selected == $value) ? 'selected' : '') . '>' . $text . '</option>' . "\n";
    }
    $data .= '</select> ';
    
    $frm->addrow(__('Time zone'), $data);
//    $frm->addrow(__('Time zone'), user_tz_select($userdata['timezone'], 'userdata[timezone]'));
    foreach ($system->data['apf'] as $field_id => $field_name) {
        $frm->addrow($field_name, $frm->text_box('userdata[' . $field_id . ']', $userdata[$field_id]));
    }
    $frm->addrow(__('Hide chat'), $frm->checkbox('userdata[hidechat]', '1', '', ((!isset($userdata['hidechat'])) ? true : ($userdata['hidechat']) ? true : false)));
    
    $user = new iMail();
    $user->setWorkTable($user->prefix.'users');
    $user->setId($userdata['username'], 'username');
    $user->iMailBeginRead(array('ext'));
    while ($ext = $user->Read()){
        $show = unpack_data($ext['ext']);
    }
    
    $frm->hidden('userdata[mail_show_chat_msgs]', !empty($show['mail_show_chat_msgs'])?$show['mail_show_chat_msgs']:'');
    
    
    $frm->addbreak(__('User groups'));
    if(!empty($userdata['gids']))
    {
    	$userdata['gids'] = unpack_data($userdata['gids']);
    }
    else 
    {
    	$userdata['gids'] = array();
    }
    $usergroup = new UserGroup();
    $usergroup->BeginUserGroupsListRead(false,'*',false);
    $system->rights_database['*'] = __('Root administrator');
    while($group=$usergroup->Read())
	{
		$grights = unpack_data($group['rights']);
		$grcount = sizeof($grights);
		$groupopt = __('Access level').': '.$group['level'].'<br><b>'.__('Rights').':</b><br>';
		
		for($i=0; $i<$grcount; $i++)
		{
			$groupopt .= '- '.$system->rights_database[$grights[$i]].'<br>';
		}
		$frm->addrow($frm->checkbox('userdata[gids]['.$group['gid'].']',1,$group['title'],in_array($group['gid'], $userdata['gids'])), $groupopt);
	}    
	
    $frm->show();
} elseif(!empty($_POST['rights']) && $system->getRightsForUser($_POST['rights'], $rights, $root, $level)){
    $frm =new InputForm ('', 'post', __('Submit'));
    $frm->addbreak(__('Rights for') . ' ' . $_POST['rights']);
    $frm->hidden('rights', $_POST['rights']);
    $frm->hidden('save', '1');
    $frm->addrow(__('Access level'), $frm->text_box('level', $level));
    if($root){
        $frm->addrow(__('Root administrator'), $frm->checkbox('rootuser', '1', '', true));
    } else {
        $frm->addrow(__('Root administrator'), $frm->checkbox('rootuser', '1', '', false));
        foreach ($system->rights_database as $right_id => $right_desc)
        { 
        	// TODO: rewrite to decrease queries number
            $frm->addrow($right_desc, $frm->checkbox('_rights[' . $right_id . ']', '1', '', user_check_right($_POST['rights'], $right_id)));
        }
    }
    $frm->show();
} elseif(!empty($_POST['search'])){
    $result = user_get_list($_POST['search']);
    
    $frm = new InputForm ('', 'post', __('Submit'), __('Reset'));
    $frm->addbreak(__('Search results'));
    $frm->addrow(__('Please do not delete users, just block it. This will help you keep solid structure of site.'));
    $frm->hidden('search', $_POST['search']);
    foreach ($result as $userdata){
        $frm->addrow(__('Username') . ': ' . $userdata['username'] . ', ' . __('Nickname') . ': ' . $userdata['nickname'],
            $frm->checkbox('delete[' . $userdata['username'] . ']', '1', __('Delete')) . ' ' .
            ((!@$userdata['blocked']) ? $frm->checkbox('block[' . $userdata['username'] . ']', '1', __('Block')) . ' ' :
            $frm->checkbox('unblock[' . $userdata['username'] . ']', '1', __('Unblock')) . '' ) . 
            $frm->radio_button('edit', array($userdata['username'] => __('Profile'))) . ' ' .
            $frm->radio_button('rights', array($userdata['username'] => __('Rights'))));
    }
    $frm->show();
}
?>