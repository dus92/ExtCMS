<?php 
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) Hakkah~CMS Development Team                                //
//   http://hakkahcms.sourceforge.net                                         //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////

if(isset($_POST['save']))
{
	$config['tname'] = hcms_clean_array($_POST['tname'],'text');
	$config['tdesc'] = hcms_clean_array($_POST['tdesc'],'text');
	$config['tfile'] = hcms_clean_array($_POST['tfile'],'wide');
	$config['ttime'] = hcms_clean_array($_POST['ttime'],'text');
	$config['tstate'] = hcms_clean_array($_POST['tstate']);
	
	$tnum = sizeof($config['tstate']);
	$keys = array_keys($config['tstate']);
	for($i=0; $i<$tnum; $i++)
	{
		if(empty($config['tname'][$keys[$i]]) || empty($config['ttime'][$keys[$i]]))
		{
			unset($config['tname'][$keys[$i]]);
			unset($config['tdesc'][$keys[$i]]);
			unset($config['tfile'][$keys[$i]]);
			unset($config['ttime'][$keys[$i]]);
			unset($config['tstate'][$keys[$i]]);
		}
		else if(!file_exists(DATA_PATH.'tasks/'.ru2lat($config['tname'][$keys[$i]].'.touch')))
		{
			file_write_contents(DATA_PATH.'tasks/'.ru2lat($config['tname'][$keys[$i]].'.touch'),'');
		}
	}
	
	file_write_contents(CONFIG_PATH.'tasks.dat.block','');
	file_write_contents(CONFIG_PATH.'tasks.dat',pack_data($config));
	unlink(CONFIG_PATH.'tasks.dat.block');
	rcms_showAdminMessage(__('Tasks config file saved'));
}

if(file_exists(CONFIG_PATH.'tasks.dat'))
{
	$config = unpack_data(file_get_contents(CONFIG_PATH.'tasks.dat'));
}

$tasks = array_values(rcms_scandir(TASKS_PATH));
$tasks = array_combine($tasks,$tasks);

$frm =new InputForm ('', 'post', __('Submit'));
$frm->addbreak(__('Tasks'));
if(isset($config))
{
	$tnum = sizeof($config['tname']);
	$keys = array_keys($config['tname']);
	for($i=0; $i<$tnum; $i++)
	{
		$id = rcms_random_string(3);
		$frm->addrow($frm->text_box('tname['.$id.']',$config['tname'][$keys[$i]],0,0,false,'style="border: none; font-size:14px; font-weight:bold;"'), $frm->text_box('tdesc['.$id.']',$config['tdesc'][$keys[$i]]).' - '.$frm->select_tag('tfile['.$id.']',$tasks, $config['tfile'][$keys[$i]]).' - '.$frm->text_box('ttime['.$id.']',$config['ttime'][$keys[$i]]).' - '.$frm->radio_button('tstate['.$id.']',array(0 => __('Off'), 1 => __('On')),$config['tstate'][$keys[$i]]));
	}
}

$frm->addbreak(__('New task'));
$frm->hidden('save',1);
$id = rcms_random_string(3);
$frm->addrow(__('Task name'), $frm->text_box('tname['.$id.']',''));
$frm->addrow(__('Task description'), $frm->text_box('tdesc['.$id.']',''));
$frm->addrow(__('Script file'), $frm->select_tag('tfile['.$id.']',$tasks));
$frm->addrow(__('Time string'), $frm->text_box('ttime['.$id.']',''));
$frm->addrow(__('State'), $frm->radio_button('tstate['.$id.']',array(0 => __('Off'), 1 => __('On')), 1));

$frm->show();
?>