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
$usergroup = new UserGroup();
$usergroup->setWorkTable($usergroup->prefix.'usergroups');

if(!empty($_POST['save']))
{	
	$rights = pack_data(isset($_POST['rootuser']) && $_POST['rootuser'] ? '*' : (!empty($_POST['rights']) ? array_keys($_POST['rights']) : ''));
	if(!empty($_POST['new']))
	{
		if($usergroup->addData(array(null, vf($_POST['catid'],3), vf($_POST['title'],5), vf($_POST['description'],5), vf($_POST['type'],3), '', vf($_POST['level'],3), $rights)))
		{
			rcms_showAdminMessage(__('Group succesfully added'));
		}
		else
		{
			rcms_showAdminMessage(__('Error'));
		}
	}
	else
	{
		$oldcatid = vf($_POST['oldcatid'],3);

		$usergroup->setId($oldcatid,'catid');
		if($usergroup->editData(array('title' => vf($_POST['title'],5), 'description' => vf($_POST['description'],5), 'type' => vf($_POST['type'],3), 'level' => vf($_POST['level'],3), 'rights' => $rights)))
		{
			rcms_showAdminMessage(__('Group succesfully edited'));
		}
		else
		{
			if(mysql_errno($mysql_data['connection']) != 0)
			{
				rcms_showAdminMessage(__('Error'));
			}
			else
			{
				rcms_showAdminMessage(__('Group succesfully edited'));
			}
		}
	}
}
else if(!empty($_POST['delete']))
{
	$keys=array_keys($_POST['delete']);
	$count = sizeof($keys);

	$table = $usergroup->prefix.'usergroups';
	$query = "DELETE FROM {table} WHERE ";
	for ($i=0; $i<$count; $i++)
	{
		$query.='gid=\''.vf($keys[$i],3).'\' ';
		if($i!=$count-1)
		{
			$query.=' OR ';
		}
	}
	$query .= ';';

	if($usergroup->BeginRawDataRead($query) || mysql_errno($mysql_data['connection'])==0)
	{
		rcms_showAdminMessage(__('Group(s) successfully deleted'));
	}
	else
	{
		rcms_showAdminMessage(__('Error'));
	}

}
else if(!empty($_POST['newgrp']) || !empty($_POST['edit']))
{
	if(!empty($_POST['edit']))
	{
		$gid = vf($_POST['edit'],3);
	}
	else
	{
		$usergroup->setWorkTable($usergroup->prefix.'usergroups');
		$gid = $usergroup->GetTableAINextValue();
	}

	$usergroup->BeginUGCategoriesListRead(array('catid','title'),false);
	while($clcategory=$usergroup->Read())
	{
		$ugcategories[$clcategory['catid']]=$clcategory['title'];
	}

	$frm = new InputForm ('', 'post', __('Submit'), '', '', 'multipart/form-data', 'cat');
	$frm->hidden('save', '1');

	if(!empty($_POST['edit']))
	{
		$group=$usergroup->ReadSingleUserGroupData($gid);
		$frm->hidden('oldcatid',$group['catid']);
		$frm->addbreak(__('Edit category'));
		$frm->addrow(__('Select category'), $frm->select_tag('catid', $ugcategories, $group['catid']));
		$frm->addrow(__('Title'), $frm->text_box('title', $group['title']), 'top');
		$frm->addrow(__('Description'), $frm->textarea('description', $group['description'], 70, 5), 'top');
		$frm->addrow(__('Group type'), $frm->radio_button('type',array('0' => __('Hidden'), '1' => __('Public'), '2' => __('Private')),$group['type']),'top');
		$frm->addrow(__('Access level'),$frm->text_box('level',$group['level']),'top');

		$rights = unpack_data($group['rights']);
		if($rights == '*')
		{
			$frm->addrow(__('Root administrators'), $frm->checkbox('rootuser', '1', '', true));
		}
		else
		{
			if(empty($rights))
			{
				$rights = array();
			}
			$frm->addrow(__('Root administrators'), $frm->checkbox('rootuser', '1', '', false));
			foreach ($system->rights_database as $right_id => $right_desc)
			{
				$frm->addrow($right_desc, $frm->checkbox('rights[' . $right_id . ']', '1', '', in_array($right_id, $rights)));
			}
		}
	}
	else
	{
		$frm->hidden('new',1);
		$frm->addbreak(__('Add category'));
		$frm->addrow(__('Select category'), $frm->select_tag('catid', $ugcategories));
		$frm->addrow(__('Title'), $frm->text_box('title', ''), 'top');
		$frm->addrow(__('Description'), $frm->textarea('description', '', 70, 5), 'top');
		$frm->addrow(__('Group type'), $frm->radio_button('type',array('0' => __('Hidden'), '1' => __('Public'), '2' => __('Private'))),'top');
		$frm->addrow(__('Access level'),$frm->text_box('level',''),'top');
		
		$frm->addrow(__('Root administrators'), $frm->checkbox('rootuser', '1', '', false));
		foreach ($system->rights_database as $right_id => $right_desc)
		{
			$frm->addrow($right_desc, $frm->checkbox('rights[' . $right_id . ']', '1', ''));
		}
	}

	$frm->show();
	exit;
}

if(isset($_POST['catid']))
{
	$catid = vf($_POST['catid'],3);
	$usergroup->BeginUserGroupsListRead(false,array('gid','title'),false);
	while($group=$usergroup->Read())
	{
		$usergroups[$group['gid']]=$group['title'];
	}

	$frm = new InputForm('','post',__('Create group'),'','','','add');
	$frm->hidden('newgrp',1);
	$frm->hidden('catid',$catid);
	$frm->show();

	if(!empty($usergroups))
	{
		$frm = new InputForm('','post',__('Submit'),__('Reset'),'','','mng');
		foreach ($usergroups as $id => $title){
			$frm->addrow($title, $frm->checkbox('delete[' . $id . ']', '1', __('Delete')) . ' ' . $frm->radio_button('edit', array($id => __('Edit')), 0));
		}
		$frm->show();
	}
}
else
{
	$usergroup->BeginUGCategoriesListRead(array('catid','title'),false);
	while($clcategory=$usergroup->Read())
	{
		$ugcategories[$clcategory['catid']]=$clcategory['title'];
	}
	if(!empty($ugcategories))
	{
		$frm = new InputForm('','post',__('Submit'));
		$frm->addrow(__('Select category'), $frm->select_tag('catid', $ugcategories));
		$frm->show();
	}
}

?>