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
$iblock = new iBlock();
$iblock->setWorkTable($iblock->prefix.'ibgroups');

if(!empty($_POST['save']))
{
	if(!empty($_POST['new']))
	{
		if(!empty($_POST['ibids']))
		{
			$ibids = implode(',', array_keys($_POST['ibids']));
		}
		else 
		{
			$ibids = '';
		}
		if(!empty($_POST['ibgids']))
		{
			$ibgids = implode(',', array_keys($_POST['ibgids']));
		}
		else 
		{
			$ibgids = '';
		}
		
		if($iblock->addData(array(vf($_POST['ibgid'],4), vf($_POST['title'],5), $ibids, $ibgids)))
		{
			$ibstruct = $iblock->CreateStructCache();
			$system->ibgroups = $ibstruct['ibgroups'];
			rcms_showAdminMessage(__('iBlock group succesfully added'));
		}
		else
		{
			if(mysql_errno($mysql_data['connection'])==1062)
			{
				rcms_showAdminMessage(__('Error: iBlock group with such ID is already exists!'));
			}
			else
			{
				rcms_showAdminMessage(__('Error'));
			}
		}
	}
	else
	{
		$ibgid = vf($_POST['ibgid'],4);
		$oldibgid = vf($_POST['oldibgid'],4);
		if(!empty($_POST['ibids']))
		{
			$ibids = implode(',', array_keys($_POST['ibids']));
		}
		else 
		{
			$ibids = '';
		}
		if(!empty($_POST['ibgids']))
		{
			$ibgids = implode(',', array_keys($_POST['ibgids']));
		}
		else 
		{
			$ibgids = '';
		}

		$iblock->setId($oldibgid,'ibgid');
		if($iblock->editData(array('ibgid' => $ibgid, 'title' => vf($_POST['title'],5), 'ibids' => $ibids, 'ibgids' => $ibgids)) || mysql_errno($mysql_data['connection']) == 0)
		{
			$ibstruct = $iblock->CreateStructCache();
			$system->ibgroups = $ibstruct['ibgroups'];
			rcms_showAdminMessage(__('iBlock group succesfully edited'));
		}
		else
		{
			rcms_showAdminMessage(__('Error'));
		}
	}
}
else if(!empty($_POST['delete']))
{
	$keys=array_keys($_POST['delete']);
	$count = sizeof($keys);

	$table = $iblock->prefix.'ibgroups';
	$query = "DELETE FROM {table} WHERE ";
	for ($i=0; $i<$count; $i++)
	{
		$ibgid = vf($keys[$i],4);
		$query.='ibgid=\''.$ibgid.'\' ';
		if($i!=$count-1)
		{
			$query.=' OR ';
		}
	}
	$query.=';';
	// rm container
	if($iblock->BeginRawDataRead(str_replace('{table}',$table,$query)) || mysql_errno($mysql_data['connection'])==0)
	{
		$ibstruct = $iblock->CreateStructCache();
		$system->ibgroups = $ibstruct['ibgroups'];
		rcms_showAdminMessage(__('iBlock group(s) successfully deleted'));
	}
	else
	{
		rcms_showAdminMessage(__('Error'));
	}
}
else if(!empty($_POST['newibgroup']) || !empty($_POST['edit']))
{
	if(!empty($system->iblocks))
	{
		$frm = new InputForm ('', 'post', __('Submit'), '', '', '', 'cont');
		$frm->hidden('save', '1');

		if(!empty($_POST['edit']))
		{
			$ibgid = vf($_POST['edit'],4);
			$ibgroup=$system->ibgroups[$ibgid];

			$frm->hidden('oldibgid',$ibgid);
			$frm->addbreak(__('Edit iBlock group'));
			$frm->addrow(__('iBGroup id'), $frm->text_box('ibgid', $ibgid), 'top');
			$frm->addrow(__('Title'), $frm->text_box('title', $ibgroup['title']), 'top');

			$ibgids = array_keys($system->ibgroups);
			$ibgcount = sizeof($ibgids);
			if($ibgcount-1 > 0)
			{
				$frm->addbreak(__('iBlock groups'));
				$ibgroup['ibgids'] = explode(',', $ibgroup['ibgids']);
				for($i=0; $i<$ibgcount; $i++)
				{
					if($ibgids[$i] == $ibgid)
					{
						continue;
					}
					$frm->addrow($system->ibgroups[$ibgids[$i]]['title'], $frm->checkbox('ibgids['.$ibgids[$i].']', 1, '', in_array($ibgids[$i], $ibgroup['ibgids'])));
				}
			}

			$frm->addbreak(__('Infoblocks'));
			$ibgroup['ibids'] = explode(',', $ibgroup['ibids']);
			$ibids = array_keys($system->iblocks);
			$ibcount = sizeof($ibids);
			for($i=0; $i<$ibcount; $i++)
			{
				$frm->addrow($system->iblocks[$ibids[$i]]['title'], $frm->checkbox('ibids['.$ibids[$i].']', 1, '', in_array($ibids[$i], $ibgroup['ibids'])));
			}
		}
		else
		{
			$frm->hidden('new',1);
			$frm->addbreak(__('New iBlock group'));
			$frm->addrow(__('iBGroup id'), $frm->text_box('ibgid', ''), 'top');
			$frm->addrow(__('Title'), $frm->text_box('title', ''), 'top');
			
			$ibgids = array_keys($system->ibgroups);
			$ibgcount = sizeof($ibgids);
			if($ibgcount > 0)
			{
				$frm->addbreak(__('iBlock groups'));
				for($i=0; $i<$ibgcount; $i++)
				{
					$frm->addrow($system->ibgroups[$ibgids[$i]]['title'], $frm->checkbox('ibgids['.$ibgids[$i].']', 1, ''));
				}
			}
			
			$frm->addbreak(__('Infoblocks'));
			$ibids = array_keys($system->iblocks);
			$ibcount = sizeof($ibids);
			for($i=0; $i<$ibcount; $i++)
			{
				$frm->addrow($system->iblocks[$ibids[$i]]['title'], $frm->checkbox('ibids['.$ibids[$i].']', 1, ''));
			}
		}

		$frm->show();
	}
	else
	{
		rcms_showAdminMessage(__('No infoblocks exists!').' <a href="admin.php?show=module&id=iblocks.iblocks">'.__('Create one').'</a>');
	}
	exit;
}

$frm = new InputForm('','post',__('Create iBlock group'),'','','','add');
$frm->hidden('newibgroup',1);
$frm->show();

$frm = new InputForm('','post',__('Submit'),__('Reset'),'','','mng');
if(!empty($system->ibgroups))
{
	$ibgids = array_keys($system->ibgroups);
	$count = sizeof($ibgids);
	for ($i=0; $i<$count; $i++)
	{
		$frm->addrow($system->ibgroups[$ibgids[$i]]['title'], $frm->checkbox('delete[' . $ibgids[$i] . ']', '1', __('Delete')) . ' ' . $frm->radio_button('edit', array($ibgids[$i] => __('Edit')), 0));
	}
}
else
{
	$frm->addmessage(__('No iBlock groups'));
}
$frm->show();
?>