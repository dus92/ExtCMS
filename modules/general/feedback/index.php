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

if(!empty($_POST['support_req']) && (LOGGED_IN || rcms_is_valid_email(@$_POST['support_mail']))) {
	if(isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['keystring'])
	{
    	if(LOGGED_IN) $_POST['support_mail'] = $system->user['email'];
    	guestbook_post_msg($system->user['username'], $system->user['nickname'], $_POST['support_mail'] . "\n" . $_POST['support_req'], DF_PATH . 'support.dat');
    	show_window('', __('Message sent'), 'center');
	}
	else
		show_window('', __('CAPTCHA Test not verified!'), 'center');
}

$result = '<form method="post" action="" name="form1">';
if(!LOGGED_IN) $result .= __('E-mail') . ' <input type="text" name="support_mail" value"" /><br />';
$result .= '<textarea name="support_req" cols="70" rows="7"></textarea><br>
<table align="center"><tr><td><a href="#" OnClick="javascript:location.reload();" title="Click to reload document"><img style="margin: 2px; padding: 2px;" src="tools/kc/index.php?'.session_name().'='.session_id().'"></a></td><td align="left">'.__('Please type the number from the picture').'<br><input size="17" type="text" name="keystring"></td></tr></table>
<input type="submit" value="' . __('Submit') . '" /></p></form>';

unset($_SESSION['captcha_keystring']);
show_window(__('Feedback request'), $result, 'center');
$system->config['pagename'] = __('Feedback');
?>