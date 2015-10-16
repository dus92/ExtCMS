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
$usergroup->setWorkTable($usergroup->prefix.'ugcategories');

if(!empty($_POST['save']))
{
	if(!empty($_POST['new']))
	{
		if($usergroup->addData(array(null, vf($_POST['title'],5), vf($_POST['description'],5), vf($_POST['level'],3), vf($_POST['hidenhigh'],3))))
		{
			rcms_showAdminMessage(__('Category succesfully added'));
		}
		else
		{
			rcms_showAdminMessage(__('Error'));
		}
	}
	else
	{
		$catid = vf($_POST['catid'],3);

		$usergroup->setId($catid,'catid');
		if($usergroup->editData(array('title' => vf($_POST['title'],5), 'description' => vf($_POST['description'],5), 'level' => vf($_POST['level'],3), 'hidenhigh' => vf($_POST['hidenhigh'],3))))
		{
			rcms_showAdminMessage(__('Category succesfully edited'));
		}
		else
		{
			if(mysql_errno($mysql_data['connection']) != 0)
			{
				rcms_showAdminMessage(__('Error'));
			}
			else
			{
				rcms_showAdminMessage(__('Category succesfully edited'));
			}
		}
	}
}
else if(!empty($_POST['delete']))
{
	$keys=array_keys($_POST['delete']);
	$count = sizeof($keys);
	if(empty($_POST['confirmed']))
	{
		// confirm delete | display stats
		$frm = new InputForm('','post',__('Submit'));
		$frm->hidden('confirmed',1);
		$frm->addbreak(__('Warning'));
		$frm->addrow(__('You choosed to delete theese categories.').' '.__('Please, confirm your choise'));

		for ($i=0; $i<$count; $i++)
		{
			$catid = vf($keys[$i],3);
			$clcategory = $usergroup->ReadSingleUGCategoryData($catid,array('title'));
			$icount = $usergroup->BeginUserGroupsListRead(array('catid' => $catid),'*',20,'','',false,2);
			$frm->addrow('<b>'.$clcategory['title'].'</b>','<b style="color: red;">'.$icount.'</b> '.__('items exists').' | '.$frm->checkbox('delete[' . $catid . ']', '1', __('Delete')));
		}
		$frm->addrow(__('You could delete single groups instead'));
		$frm->show();
		exit;
	}
	else
	{
		$table = $usergroup->prefix.'ugcategories';
		$query = "DELETE FROM {table} WHERE ";
		$filter['logic'] = 'OR';
		$filter['catid'] = array();
		for ($i=0; $i<$count; $i++)
		{
			$catid = vf($keys[$i],3);
			$query.='catid=\''.$catid.'\' ';
			if($i!=$count-1)
			{
				$query.=' OR ';
			}
			array_push($filter['catid'],$catid);
		}
		$query.=';';

		if($usergroup->BeginRawDataRead(str_replace('{table}',$table,$query)) || mysql_errno($mysql_data['connection'])==0)
		{
			$where = $usergroup->ParseFilter($filter,'id');

			$table = $usergroup->prefix.'usergroups';
			if($usergroup->BeginRawDataRead("DELETE FROM $table $where;") || mysql_errno($mysql_data['connection'])==0)
			{
				rcms_showAdminMessage(__('Category(ies) successfully deleted'));
			}
			else
			{
				rcms_showAdminMessage(__('Error'));
			}
		}
		else
		{
			rcms_showAdminMessage(__('Error'));
		}
	}
}
else if(!empty($_POST['newcat']) || !empty($_POST['edit']))
{
	if(!empty($_POST['edit']))
	{
		$catid = vf($_POST['edit'],3);
	}
	else
	{
		$usergroup->setWorkTable($usergroup->prefix.'ugcategories');
		$catid = $usergroup->GetTableAINextValue();
	}
	$frm = new InputForm ('', 'post', __('Submit'), '', '', 'multipart/form-data', 'cat');
	$frm->hidden('save', '1');

	if(!empty($_POST['edit']))
	{
		$clcategory=$usergroup->ReadSingleUGCategoryData($catid);
		$frm->hidden('catid',$catid);
		$frm->addbreak(__('Edit category'));
		$frm->addrow(__('Title'), $frm->text_box('title', $clcategory['title']), 'top');
		$frm->addrow(__('Description'), $frm->textarea('description', $clcategory['description'], 70, 5), 'top');
		$frm->addrow(__('Access level'),$frm->text_box('level',$clcategory['level']),'top');
		$frm->addrow(__('Display options'), $frm->radio_button('hidenhigh',array('0' => __('Hidden'), '1' => __('General'), '2' => __('Highlight')),$clcategory['hidenhigh']),'top');
	}
	else
	{
		$frm->hidden('new',1);
		$frm->addbreak(__('Add category'));
		$frm->addrow(__('Title'), $frm->text_box('title', ''), 'top');
		$frm->addrow(__('Description'), $frm->textarea('description', '', 70, 5), 'top');
		$frm->addrow(__('Access level'),$frm->text_box('level',''),'top');
		$frm->addrow(__('Display options'), $frm->radio_button('hidenhigh',array('0' => __('Hidden'), '1' => __('General'), '2' => __('Highlight'))),'top');
	}

	$frm->show();
	exit;
}

$usergroup->BeginUGCategoriesListRead(array('catid','title'),false);
while($clcategory=$usergroup->Read())
{
	$ugcategories[$clcategory['catid']]=$clcategory['title'];
}

$frm = new InputForm('','post',__('Create category'),'','','','add');
$frm->hidden('newcat',1);
$frm->show();

if(!empty($ugcategories))
{
	$frm = new InputForm('','post',__('Submit'),__('Reset'),'','','mng');
	foreach ($ugcategories as $id => $title){
		$frm->addrow($title, $frm->checkbox('delete[' . $id . ']', '1', __('Delete')) . ' ' . $frm->radio_button('edit', array($id => __('Edit')), 0));
	}
	$frm->show();
}

?>