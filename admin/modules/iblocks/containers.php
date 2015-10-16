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
$iblock->setWorkTable($iblock->prefix.'ibcontainers');

// iblock's options load
$ibconfig = unpack_data(file_get_contents(CONFIG_PATH.'ibconfig.dat'));

if(!empty($_POST['save']))
{
	$contid = vf($_POST['contid'],4);

	if(empty($contid))
	{
		rcms_showAdminMessage(__('Error: Empty container ID!'));
	}
	else if(!empty($_POST['new']))
	{
		$subst['cont_tpls'] = (empty($_POST['cont_tpls']) ? 'default' : $_POST['cont_tpls']);	
		if(!empty($_POST['subst']))
		{
			$subst['subst'] = array_filter($_POST['subst']);	
		}
		if(!empty($_POST['drop']))
		{
			$subst['drop'] = array_filter($_POST['drop']);	
		}
		$subst = pack_data($subst);
		
		if($iblock->addData(array($contid,vf($_POST['ibid'],4), vf($_POST['title'],5), vf($_POST['description'],5), vf($_POST['access'],3), vf($_POST['ordering'],3), $subst)))
		{
			$ibstruct = $iblock->CreateStructCache();
			$system->containers = $ibstruct['containers'];

			if($_POST['cont_tpls'] == 'own')
			{
				if(!file_exists(IBLOCKS_PATH.'cont_tpls'))
				{
					@mkdir(IBLOCKS_PATH.'cont_tpls');
				}
				if(!file_exists(IBLOCKS_PATH.'cont_tpls/'.$contid))
				{
					@mkdir(IBLOCKS_PATH.'cont_tpls/'.$contid);
				}

				file_write_contents(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-full.tpl.php', $_POST['item-full-tpl']);
				file_write_contents(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-list.tpl.php', $_POST['item-list-tpl']);
			}

			rcms_showAdminMessage(__('Container succesfully added'));
		}
		else
		{
			if(mysql_errno($mysql_data['connection'])==1062)
			{
				rcms_showAdminMessage(__('Error: Container with such ID is already exists!'));
			}
			else
			{
				rcms_showAdminMessage(__('Error'));
			}
		}
		//*/
	}
	else
	{
		$contid = vf($_POST['contid'],4);
		$oldcontid = vf($_POST['oldcontid'],4);
		$ibid = vf($_POST['ibid'],4);
		$oldibid = vf($_POST['oldibid'],4);

		$cont_tpls = (empty($_POST['cont_tpls']) ? 'default' : $_POST['cont_tpls']);
		$subst['cont_tpls'] = $cont_tpls;	
		if(!empty($_POST['subst']))
		{
			$subst['subst'] = array_filter($_POST['subst']);	
		}
		if(!empty($_POST['drop']))
		{
			$subst['drop'] = array_filter($_POST['drop']);	
		}
		$subst = pack_data($subst);
		
		$iblock->setId($oldcontid,'contid');
		if($iblock->editData(array('contid' => $contid, 'ibid' => $ibid, 'title' => vf($_POST['title'],5), 'description' => vf($_POST['description'],5), 'access' => vf($_POST['access'],3), 'ordering' => vf($_POST['ordering'],3), 'substitutions' => $subst)) || mysql_errno($mysql_data['connection']) == 0)
		{
			if($ibid != $oldibid || $contid != $oldcontid)
			{
				$iblockitem = new iBlockItem();
				$iblockitem->setId($oldcontid,'contid');
				if($iblockitem->editData((($contid != $oldcontid && $ibid != $oldibid) ? array('ibid' => $ibid, 'contid' => $contid) : ($contid != $oldcontid ? array('contid' => $contid) : array('ibid' => $ibid)))) || mysql_errno($mysql_data['connection']) == 0)
				{
					$iblock->setWorkTable($iblock->prefix.'ibcategories');
					if($iblock->editData((($contid != $oldcontid && $ibid != $oldibid) ? array('ibid' => $ibid, 'contid' => $contid) : ($contid != $oldcontid ? array('contid' => $contid) : array('ibid' => $ibid)))) || mysql_errno($mysql_data['connection']) == 0)
					{
						$ibstruct = $iblock->CreateStructCache();
						$system->containers = $ibstruct['containers'];

						if($_POST['cont_tpls'] == 'own')
						{
							if(!file_exists(IBLOCKS_PATH.'cont_tpls'))
							{
								@mkdir(IBLOCKS_PATH.'cont_tpls');
							}
							if(!file_exists(IBLOCKS_PATH.'cont_tpls/'.$contid))
							{
								@mkdir(IBLOCKS_PATH.'cont_tpls/'.$contid);
							}

							file_write_contents(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-full.tpl.php', $_POST['item-full-tpl']);
							file_write_contents(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-list.tpl.php', $_POST['item-list-tpl']);
						}

						rcms_showAdminMessage(__('Container succesfully edited'));
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
			else
			{
				$ibstruct = $iblock->CreateStructCache();
				$system->containers = $ibstruct['containers'];

				if($cont_tpls == 'own')
				{
					if(!file_exists(IBLOCKS_PATH.'cont_tpls'))
					{
						@mkdir(IBLOCKS_PATH.'cont_tpls');
					}
					if(!file_exists(IBLOCKS_PATH.'cont_tpls/'.$contid))
					{
						@mkdir(IBLOCKS_PATH.'cont_tpls/'.$contid);
					}

					file_write_contents(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-full.tpl.php', $_POST['item-full-tpl']);
					file_write_contents(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-list.tpl.php', $_POST['item-list-tpl']);
				}

				rcms_showAdminMessage(__('Container succesfully edited'));
			}
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

	if(empty($_POST['confirmed']))
	{
		// confirm delete | display stats
		$iblockitem = new iBlockItem();
		$frm = new InputForm('','post',__('Submit'));
		$frm->hidden('confirmed',1);
		$frm->addbreak(__('Warning'));
		$frm->addrow(__('You choosed to delete these containers').'. '.__('Please, confirm your choise'));

		for ($i=0; $i<$count; $i++)
		{
			$contid = vf($keys[$i],4);
			$icount = $iblockitem->BeginiBlockItemsListRead(array('contid' => $contid),'*',20,'','',false,2);
			$frm->addrow('<b>'.$contid.'</b>','<b style="color: red;">'.$icount.'</b> '.__('items exists').' | '.$frm->checkbox('delete[' . $contid . ']', '1', __('Delete')));
		}
		$frm->addrow(__('You could delete single categories instead'));
		$frm->show();
		exit;
	}
	else
	{
		$table = $iblock->prefix.'ibcontainers';
		$query = "DELETE FROM {table} WHERE ";
		$filter['contid'] = array();
		for ($i=0; $i<$count; $i++)
		{
			$contid = vf($keys[$i],4);
			$query.='contid=\''.$contid.'\' ';
			if($i!=$count-1)
			{
				$query.=' OR ';
			}
			array_push($filter['contid'],$contid);
			rcms_delete_files(IBLOCKS_PATH.'cont_tpls/'.$contid,true);
		}
		$query.=';';
		// rm container
		if($iblock->BeginRawDataRead(str_replace('{table}',$table,$query)) || mysql_errno($mysql_data['connection'])==0)
		{
			// rm categories, caticons
			$iblock->BeginCategoriesListRead($filter,array('icon'),false);
			while ($catdata = $iblock->Read())
			{
				@rcms_delete_files(IBLOCKS_PATH.'caticons/'.basename($catdata['icon']));
			}

			if($iblock->BeginRawDataRead(str_replace('{table}',$iblock->prefix.'ibcategories',$query)) || mysql_errno($mysql_data['connection'])==0)
			{
				$iblockitem = new iBlockItem();
				if($iblockitem->DropiBlockItems($filter))
				{
					$ibstruct = $iblock->CreateStructCache();
					$system->containers = $ibstruct['containers'];
					rcms_showAdminMessage(__('Container(s) successfully deleted'));
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
		else
		{
			rcms_showAdminMessage(__('Error'));
		}
	}
}
else if(!empty($_POST['newcont']) || !empty($_POST['edit']))
{
	if(!empty($system->iblocks))
	{
		$int_simple = strtolower(__('Simple').__(' interface mode'));
		$int_advanced = strtolower(__('Advanced').__(' interface mode'));
	
		$frm = new InputForm ('', 'post', __('Submit'), '', '', '', 'cont');
		$frm->hidden('save', '1');
		$frm->addsingle('<script type="text/javascript" src="modules/js/jquery.js"></script>
		<script type="text/javascript">
		$(document).ready(
		function()
		{
			$("input.cont_tplradio").change(function()
			{
				$("#cont_owntpls").slideToggle("fast");
			});
			$("#interface_switcher").click(function()
			{
				if($(this).text() == "'.$int_simple.'")
				{
					$("div.ibopts").fadeIn("slow");
					$(this).text("'.$int_advanced.'");
				}
				else
				{
					$("div.ibopts").fadeOut("slow");
					$(this).text("'.$int_simple.'");
				}
			});
		});
		</script>');

		if(!empty($_POST['edit']))
		{
			$contid = vf($_POST['edit'],4);
			$container=$system->containers[$contid];

			$frm->addbreak(__('Edit container').'<div style="display:inline; position: absolute; right: 10px;"><a id="interface_switcher" style="font-size:0.9em; color: #aaaaaa;" href="#">'.strtolower(($ibconfig['interface']['mode'] == 'simple' ? __('Simple') : __('Advanced').__(' interface mode')).'</a></div>'));
			$frm->hidden('oldibid',$container['ibid']);
			$frm->hidden('oldcontid',$contid);
			$frm->addrow(__('Infoblock'),$frm->select_tag('ibid',$system->iblocks,$container['ibid']));
			$frm->addrow(__('Identifier'), $frm->text_box('contid', $contid), 'top');
			$frm->addrow(__('Title'), $frm->text_box('title', $container['title']), 'top');
			$frm->addrow(__('Description'), $frm->textarea('description', $container['description'], 70, 5), 'top');
			$frm->addrow(__('Access level'),$frm->text_box('access',$container['access']),'top');
			$frm->addrow(__('Ordering'),$frm->text_box('ordering',@$container['ordering']),'top');

			$frm->addsingle('</table>
		<div class="ibopts" '.($ibconfig['interface']['mode'] == 'simple' ? 'style="display:none;"' : '').'>
		<table border="0" cellspacing="2" cellpadding="2" width="100%">');
		
			$frm->addbreak(__('Item post form substitutions'));
			$fields = unpack_data($system->iblocks[$container['ibid']]['fields']);
			$substitutions = unpack_data($system->containers[$contid]['substitutions']);
			$count = sizeof($fields['fd_id']);
			for($i=0; $i<$count; $i++)
			{
				$frm->addrow($fields['fd_name'][$i].' ['.$fields['fd_id'][$i].']', __(' <-change-> ').$frm->text_box('subst['.$fields['fd_id'][$i].']', @$substitutions['subst'][$fields['fd_id'][$i]]).__(' or ').$frm->checkbox('drop['.$fields['fd_id'][$i].']',1,__('Hide'),@$substitutions['drop'][$fields['fd_id'][$i]]));
			}

			$frm->addbreak(__('Container\'s templates'));
			$frm->addrow(__('Templates'), $frm->radio_button('cont_tpls',array('default' => __('Use iblock templates'), 'own' => __('Container\'s own templates')), @$substitutions['cont_tpls'], ' ', 'class="cont_tplradio"').
			'<div style="margin: 2px;'.(@$substitutions['cont_tpls']=='own' ? '' : ' display: none;').'" id="cont_owntpls">
			'.__('Item full view template').'<br>'.
			$frm->textarea('item-full-tpl',@file_get_contents(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-full.tpl.php'),80,5).'<br>'.
			__('Item list view template').'<br>'.
			$frm->textarea('item-list-tpl',@file_get_contents(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-list.tpl.php'),80,5).'</div>');
		}
		else
		{
			$ibid = vf($_POST['ibid'],4);
			$frm->addbreak(__('New container').'<div style="display:inline; position: absolute; right: 10px;"><a id="interface_switcher" style="font-size:0.9em; color: #aaaaaa;" href="#">'.strtolower(($ibconfig['interface']['mode'] == 'simple' ? __('Simple') : __('Advanced').__(' interface mode')).'</a></div>'));
			$frm->hidden('new',1);
			$frm->addrow(__('Infoblock'),$frm->select_tag('ibid',$system->iblocks,$ibid));
			$frm->addrow(__('Identifier'), $frm->text_box('contid', ''), 'top');
			$frm->addrow(__('Title'), $frm->text_box('title', ''), 'top');
			$frm->addrow(__('Description'), $frm->textarea('description', '', 70, 5), 'top');
			$frm->addrow(__('Access level'),$frm->text_box('access',''),'top');
			$frm->addrow(__('Ordering'),$frm->text_box('ordering',''),'top');

			$frm->addsingle('</table>
		<div class="ibopts" '.($ibconfig['interface']['mode'] == 'simple' ? 'style="display:none;"' : '').'>
		<table border="0" cellspacing="2" cellpadding="2" width="100%">');
		
			$frm->addbreak(__('Item post form substitutions'));
			$fields = unpack_data($system->iblocks[$ibid]['fields']);
			$count = sizeof($fields['fd_id']);
			for($i=0; $i<$count; $i++)
			{
				$frm->addrow($fields['fd_name'][$i].' ['.$fields['fd_id'][$i].']', ' <-change-> '.$frm->text_box('subst['.$fields['fd_id'][$i].']', '').__(' or ').$frm->checkbox('drop['.$fields['fd_id'][$i].']',1,__('Hide')));
			}

			$frm->addbreak(__('Container\'s templates'));
			$frm->addrow(__('Templates'), $frm->radio_button('cont_tpls',array('default' => __('Use iblock templates'), 'own' => __('Container\'s own templates')), 'default', ' ', 'id="cont_tplradio"').
			'<div style="margin: 2px; display: none;" id="cont_owntpls">
			'.__('Item full view template').'<br>'.
			$frm->textarea('item-full-tpl','',80,5).'<br>'.
			__('Item list view template').'<br>'.
			$frm->textarea('item-list-tpl','',80,5).'</div>');
		}
		$frm->addsingle('</table>
		</div>
		<table border="0" cellspacing="2" cellpadding="2" width="100%">');
		$frm->show();
	}
	else
	{
		rcms_showAdminMessage(__('No infoblocks exists!').' <a href="admin.php?show=module&id=iblocks.iblocks">'.__('Create one').'</a>');
	}
	exit;
}

if(isset($_POST['ibid']))
{
	$ibid = vf($_POST['ibid'],4);
	$containers = ib_filter_plarrays($system->containers, 'ibid', $ibid);

	$frm = new InputForm('','post',__('Create container'),'','','','add');
	$frm->hidden('newcont',1);
	$frm->hidden('ibid',$ibid);
	$frm->show();

	$frm = new InputForm('','post',__('Submit'),__('Reset'),'','','mng');
	if(!empty($containers))
	{
		foreach ($containers as $id => $container){
			$title = $container['title'];
			$frm->addrow($title, $frm->checkbox('delete[' . $id . ']', '1', __('Delete')) . ' ' . $frm->radio_button('edit', array($id => __('Edit')), 0));
		}
	}
	else
	{
		$frm->addmessage(__('No containers'));
	}
	$frm->show();
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
	$frm->show();
}
?>