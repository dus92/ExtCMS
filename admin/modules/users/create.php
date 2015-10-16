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

if(!empty($_POST['new']))
{
	// create
	if($system->registerUser($_POST['username'], $_POST['nickname'], @$_POST['password'], @$_POST['confirmation'], $_POST['email'], $_POST['userdata']))
	{
		rcms_showAdminMessage('User '.vf($_POST['username'],1).' created');
	}
	else
	{
		rcms_showAdminMessage('Error: '. $system->results['registration']);
	}	
}
else 
{
	// form
	$frm = new InputForm('','post');
	$frm->hidden('new',1);
	$frm->addbreak(__('Create user'));
	$frm->addrow(__('Login'), $frm->text_box('username',''));
	$frm->addrow(__('Nickname'), $frm->text_box('nickname',''));
	$frm->addrow(__('Password'), $frm->text_box('password',''));
	$frm->addrow(__('Confirm password'), $frm->text_box('confirmation',''));
	$frm->addrow(__('E-mail'), $frm->text_box('email',''));
	$frm->addrow(__('Hide e-mail from other users'), $frm->checkbox('userdata[hideemail]',1,__('On')));
	
	$tzs = DateTimeZone::listIdentifiers();
    
	$data = '<select name="userdata[timezone]">' . "\n";
    foreach($tzs as $value => $text)
    {
    	$data .= '<option value="' . $value . '" ' . (($text == $system->config['timezone']) ? 'selected' : '') . '>' . $text . '</option>' . "\n";
    }
    $data .= '</select> ';
    
    $frm->addrow(__('Time zone'), $data);
	
	$frm->addrow(__('ICQ'), $frm->text_box('userdata[icquin]',''));
	$frm->addrow(__('Website'), $frm->text_box('userdata[website]',''));
	$frm->show();
}
?>