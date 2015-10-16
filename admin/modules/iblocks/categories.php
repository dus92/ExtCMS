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
$iblock->setWorkTable($iblock->prefix.'ibcategories');

if(!empty($_POST['save']))
{
	if(!empty($_POST['new']))
	{
		if(!empty($_POST['icon']))
		{
			$icon = IBLOCKS_PATH.'caticons/'.preg_replace("/[^0-9a-zA-Z_\.]/",'',$_POST['icon']);
			$icon = (file_exists($icon) ? $icon : '');
		}
		else
		{
			$icon = '';
		}

		if($iblock->addData(array(null, vf($_POST['contid'],4), vf($_POST['ibid'],4), vf($_POST['title'],5), vf($_POST['description'],5), vf($_POST['access'],3), $icon, null,null)))
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
		$contid = vf($_POST['contid'],4);
		$oldcontid = vf($_POST['oldcontid'],4);

		if(!empty($_POST['icon']))
		{
			$icon = IBLOCKS_PATH.'caticons/'.preg_replace("/[^0-9a-zA-Z_\.]/",'',$_POST['icon']);
			$icon = (file_exists($icon) ? $icon : '');
		}
		else
		{
			$icon = '';
		}
		$iblock->setId($catid,'catid');
		if($iblock->editData(array('contid' => $contid, 'title' => vf($_POST['title'],5), 'description' => vf($_POST['description'],5), 'access' => vf($_POST['access'],3), 'icon' => $icon)))
		{
			if($contid != $oldcontid)
			{
				$iblockitem = new iBlockItem();
				$iblockitem->setId($catid,'catid');
				if($iblockitem->editData(array('contid' => $contid)))
				{
					rcms_showAdminMessage(__('Category succesfully edited'));
				}
				else
				{
					rcms_showAdminMessage(__('Error'));
				}
			}
			else 
			{
				rcms_showAdminMessage(__('Category succesfully edited'));
			}
		}
		else
		{
			if(mysql_errno($mysql_data['connection']) != 0)
			{
				rcms_showAdminMessage(__('Error'));
			}
			else
			{
				if($contid != $oldcontid)
				{
					$iblockitem = new iBlockItem();
					$iblockitem->setId($catid,'catid');
					if($iblockitem->editData(array('contid' => $contid)))
					{
						rcms_showAdminMessage(__('Category succesfully edited'));
					}
					else
					{
						rcms_showAdminMessage(__('Error'));
					}
				}
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
		$iblockitem = new iBlockItem();
		$frm = new InputForm('','post',__('Submit'));
		$frm->hidden('confirmed',1);
		$frm->addbreak(__('Warning'));
		$frm->addrow(__('You choosed to delete these categories').'. '.__('Please, confirm your choise'));

		for ($i=0; $i<$count; $i++)
		{
			$catid = vf($keys[$i],3);
			$clcategory = $iblock->ReadSingleCategoryData($catid,array('title'));
			$icount = $iblockitem->BeginiBlockItemsListRead($catid,'*',20,'','',false,2);
			$frm->addrow('<b>'.$clcategory['title'].'</b>','<b style="color: red;">'.$icount.'</b> '.__('items exists').' | '.$frm->checkbox('delete[' . $catid . ']', '1', __('Delete')));
		}
		$frm->addrow(__('You could delete single items instead'));
		$frm->show();
		exit;
	}
	else
	{
		$table = $iblock->prefix.'ibcategories';
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
		
		$iblock->BeginCategoriesListRead($filter,array('icon'),false);
		while ($catdata = $iblock->Read())
		{
			@rcms_delete_files(IBLOCKS_PATH.'caticons/'.basename($catdata['icon']));
		}
		
		if($iblock->BeginRawDataRead(str_replace('{table}',$table,$query)) || mysql_errno($mysql_data['connection'])==0)
		{
			$iblockitem = new iBlockItem();
			if($iblockitem->DropiBlockItems($filter))
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
		$iblock->setWorkTable($iblock->prefix.'ibcategories');
		$catid = $iblock->GetTableAINextValue();
	}
	
	$ibid = vf($_POST['ibid'],4);
	$contid = vf($_POST['contid'],4);
	$frm = new InputForm ('', 'post', __('Submit'), '', '', 'multipart/form-data', 'cat');
	$frm->hidden('save', '1');
	$frm->hidden('ibid',$ibid);

	$asyncmgr = new AsyncMgr();
	$asyncmgr->printImgUpFormJS('caticons','image',false, true, array('load_stopped_3.gif','load_process_3.gif'),false,'100x100');

	if(!empty($system->containers))
	{
		if(!empty($_POST['edit']))
		{
			$clcategory=$iblock->ReadSingleCategoryData($catid);

			$frm->hidden('catid',$catid);
			$frm->hidden('oldcontid',$clcategory['contid']);
			$frm->addbreak(__('Edit category'));
			$frm->addrow(__('Select container'), $frm->select_tag('contid', $system->containers, $clcategory['contid']));
			$frm->addrow(__('Title'), $frm->text_box('title', $clcategory['title']), 'top');
			$frm->addrow(__('Description'), $frm->textarea('description', $clcategory['description'], 70, 5), 'top');
			$frm->addrow(__('Access level'),$frm->text_box('access',$clcategory['access']),'top');

			if(file_exists($clcategory['icon']))
			{
				$asyncmgr->addEditPart(__('Category icon'),$frm,'image','<img src="{dvalue}">',$clcategory['icon'],basename($clcategory['icon']),'icon','load_stopped_3.gif');
			}
			else
			{
				$asyncmgr->addAddPart(__('Category icon'),$frm,'image','icon','load_stopped_3.gif');
			}
		}
		else
		{
			$contid = vf($_POST['contid'],4);
			$frm->hidden('new',1);
			$frm->addbreak(__('Add category'));
			$frm->addrow(__('Select container'), $frm->select_tag('contid', $system->containers, $contid));
			$frm->addrow(__('Title'), $frm->text_box('title', ''), 'top');
			$frm->addrow(__('Description'), $frm->textarea('description', '', 70, 5), 'top');
			$frm->addrow(__('Access level'),$frm->text_box('access',''),'top');
			$asyncmgr->addAddPart(__('Category icon'),$frm,'image','icon','load_stopped_3.gif');
		}
	}

	$frm->show();
	exit;
}

if(isset($_POST['contid']))
{
	$contid = vf($_POST['contid'],4);
	$ibid = vf($_POST['ibid'],4);
	$iblock->BeginCategoriesListRead($contid,array('catid','title'));
	while($clcategory=$iblock->Read())
	{
    
		$ibcategories[$clcategory['catid']]=__($clcategory['title']);
	}

	$frm = new InputForm('','post',__('Create category'),'','','','add');
	$frm->hidden('newcat',1);
	$frm->hidden('contid',$contid);
	$frm->hidden('ibid',$ibid);
	$frm->show();

	if(!empty($ibcategories))
	{
		$frm = new InputForm('','post',__('Submit'),__('Reset'),'','','mng');
		$frm->hidden('ibid',$ibid);
		$frm->hidden('contid',$contid);
		foreach ($ibcategories as $id => $title){
			$frm->addrow($title, $frm->checkbox('delete[' . $id . ']', '1', __('Delete')) . ' ' . $frm->radio_button('edit', array($id => __('Edit')), 0));
		}
		$frm->show();
	}
	exit;
}
else
{
	// iblock selection
	$frm=new InputForm ('', 'post', __('Submit'));
	if(!empty($system->iblocks))
	{
		$frm->addrow(__('Select infoblock'), $frm->select_tag('ibid', $system->iblocks,(isset($_POST['ibid']) ? vf($_POST['ibid'],4) : '')), 'top');
	}
	else
	{
		$frm->addmessage(__('No infoblocks'));
	}
}

if(isset($_POST['ibid']))
{
	// container selection
	$ibid = vf($_POST['ibid'],4);
	$containers = ib_filter_plarrays($system->containers, 'ibid', $ibid);
	if(!empty($containers))
	{
		$frm->addrow(__('Select container'), $frm->select_tag('contid', $containers), 'top');
	}
	else 
	{
		$frm->addmessage(__('No containers'));
	}
}
$frm->show();
?>