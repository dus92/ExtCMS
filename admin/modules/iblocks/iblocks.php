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
$iblock->setWorkTable($iblock->prefix.'iblocks');
error_reporting(E_ERROR | E_WARNING);

// iblock's options load
$ibconfig = unpack_data(file_get_contents(CONFIG_PATH.'ibconfig.dat'));

/*
if(isset($_POST['save']))
{
var_dump($_POST);
exit;
}
//*/

if(isset($_GET['clean']) && isset($_GET['addfield']))
{
	$frm = new InputForm();
	$id=rcms_random_string(3);
	header("Content-Type: text/html; charset=utf-8");

	$fd_radio = '<div class="fieldopt" id="radio'.$id.'"><textarea name="fd_radio[]"></textarea> '.__('value - caption').'</div>';
	$fd_checkbox = '<div class="fieldopt" id="checkbox'.$id.'"><input type="text" name="fd_checkbox[]"> '.__('Caption').'</div>';
	$fd_textarea = '<br><div class="fieldopt" id="textarea'.$id.'"><input type="text" value="10" name="fd_textarea[]"> '.__('Rows count').'</div>';
	$fd_text = '<div id="text'.$id.'">'.__('Data type').': '.$frm->select_tag('fd_text[]',array('' => __('Text'), 'number' => __('Number'), 'date' => __('Date')),'').'</div>';
	$fd_select = '<div class="fieldopt" id="select'.$id.'"><textarea name="fd_select[]"></textarea> '.__('value - caption').'</div>';
	$capt = rcms_random_string(3);
	$fd_file = '<br><div class="fieldopt" id="file'.$id.'"><input type="checkbox" value="1" name="fd_fileisimage[]" id="'.$capt.'"> <label for="'.$capt.'">'.__('Images gallery').'</label></div>';
	$fd_typesel = '<select name="fd_type[]">
<option value="text" onclick="showelembyid(\''.$id.'\', \'text\')">'.__('Text').'</option>
<option value="textarea" onclick="showelembyid(\''.$id.'\', \'textarea\')" >'.__('Textarea').'</option>
<option value="file" onclick="fileclick(\''.$id.'\')">'.__('File(s)').'</option>
<option value="radio" onclick="showelembyid(\''.$id.'\', \'radio\')">'.__('Radio').'</option>
<option value="checkbox" onclick="showelembyid(\''.$id.'\', \'checkbox\')" >'.__('Checkbox').'</option>
<option value="select" onclick="showelembyid(\''.$id.'\', \'select\')" >'.__('SELECT tag').'</option>
</select>';
	$fd_storesel = '<select id="fd_store_'.$id.'" name="fd_store[]">
	<option value="idata">'.__('General').'</option>
	<option value="index1">'.__('Index 1(int)').'</option>
	<option value="index2">'.__('Index 2(int)').'</option>
	<option value="index3">'.__('Index 3(text)').'</option>
	<option value="index4">'.__('Index 4(text)').'</option>
	<option value="index5">'.__('Index 5(text)').'</option>
	<option value="idate">'.__('Date').'</option>
	</select>';

	print('<tr id="'.$id.'">
  <td valign="middle" align="left" class="row2" ><a href="#" class="drop_field_link" id="'.$id.'" title="'.__('Drop field').'">[x]</a> '.__('Name').' '.$frm->text_box('fd_name[]','','18').'</td>
  <td valign="middle" align="left" class="row3">'.__('ID').' '.$frm->text_box('fd_id[]','','12').' '.$frm->checkbox('fd_nec[]',1,__('Necessary')).' | '.__('Store type').' '.$fd_storesel.' '.__('Type').' '.$fd_typesel.$fd_textarea.$fd_text.$fd_radio.$fd_select.$fd_checkbox.$fd_file.'</td>
</tr>');
	$frm->show(true);
	exit;
}

if(!empty($_POST['save']))
{
	if(!empty($_POST['new']))
	{
		// iblock add
		$siblock['ibid'] = vf($_POST['ibid'],4);
		$siblock['title'] = vf($_POST['title'],5);
		$siblock['description'] = vf($_POST['description'],5);

		$siblock['extopt'] = array();
		$siblock['extopt']['delim_cond'] = vf($_POST['delim_cond'],3);
		if(!empty($_POST['enable_wms']))
		{
			$siblock['extopt']['enable_wms'] = true;
		}
		if(!empty($_POST['hidefields']))
		{
			$siblock['extopt']['hidefields'] = true;
		}
		if(!empty($_POST['rnm_title']))
		{
			$siblock['extopt']['rnm_title'] = vf($_POST['rnm_title'],5);
		}
		if(!empty($_POST['rnm_desc']))
		{
			$siblock['extopt']['rnm_desc'] = vf($_POST['rnm_desc'],5);
		}
		if(!empty($_POST['loadritems']))
		{
			$siblock['extopt']['loadritems'] = true;
			$siblock['extopt']['ritems_cnt'] = vf($_POST['ritems_cnt'],3);
			$siblock['extopt']['ritems_matchtag'] = !empty($_POST['ritems_matchtag']) ? true : false;
			$siblock['extopt']['ritems_extcond'] = str_replace('\'','"',$_POST['ritems_extcond']);
			if(!empty($_POST['ritems_sel']))
			{
				$siblock['extopt']['ritems_sel'] = explode(',', $_POST['ritems_sel']);
				array_walk($siblock['extopt']['ritems_sel'],"hcms_trim_array_w");
			}
			else 
			{
				$siblock['extopt']['ritems_sel'] = '';
			}
		}
		$siblock['extopt'] = pack_data($siblock['extopt']);
		
		// templates
		$itlisttplsrc = (!empty($_POST['dfitlisttpl']) ? file_get_contents(MODULES_TPL_PATH.'iblocks-item-list.tpl.php') : $_POST['itlisttplsrc']);
		$catlisttplsrc = (!empty($_POST['dfcatlisttpl']) ? file_get_contents(MODULES_TPL_PATH.'iblocks-cat-list.tpl.php') : $_POST['catlisttplsrc']);
		$contlisttplsrc = (!empty($_POST['dfcontlisttpl']) ? file_get_contents(MODULES_TPL_PATH.'iblocks-cont-list.tpl.php') : $_POST['contlisttplsrc']);
		$fulltplsrc = (!empty($_POST['dffulltpl']) ? file_get_contents(MODULES_TPL_PATH.'iblocks-item-full.tpl.php') : $_POST['fulltplsrc']);
		$listupsrc = $_POST['listupsrc'];
		$listdelimsrc = $_POST['listdelimsrc'];
		$listdownsrc = $_POST['listdownsrc'];

		if(!file_exists(DATA_PATH.'iblocks/templates/'.$siblock['ibid']))
		{
			if(!mkdir(DATA_PATH.'iblocks/templates/'.$siblock['ibid']))
			{
				rcms_showAdminMessage(__('Error creation iblock templates catalog'));
			}
		}
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/item-full.tpl.php',$fulltplsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/item-list.tpl.php',$itlisttplsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/cat-list.tpl.php',$catlisttplsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/cont-list.tpl.php',$contlisttplsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/listup.tpl.php',$listupsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/listdelim.tpl.php',$listdelimsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/listdown.tpl.php',$listdownsrc,"w");

		//fields options
		$fields['fd_name'] = array_filter($_POST['fd_name']);
		$fields['fd_id'] = array_filter($_POST['fd_id']);
		$needed = sizeof($fields['fd_id']);

		$fields['fd_nec'] = array_filter(array_slice($_POST['fd_nec'],0,$needed));
		$fields['fd_type'] = array_filter(array_slice($_POST['fd_type'],0,$needed));
		$fields['fd_store'] = array_slice($_POST['fd_store'],0,$needed);
		$fields['fd_radio'] = array_filter(array_slice($_POST['fd_radio'],0,$needed));
		$fields['fd_select'] = array_filter(array_slice($_POST['fd_select'],0,$needed));
		$fields['fd_checkbox'] = array_filter(array_slice($_POST['fd_checkbox'],0,$needed));
		$fields['fd_textarea'] = array_filter(array_slice($_POST['fd_textarea'],0,$needed));
		$fields['fd_text'] = array_filter(array_slice($_POST['fd_text'],0,$needed));
		$fields['fd_fileisimage'] = array_slice($_POST['fd_fileisimage'],0,$needed);
		$siblock['fields'] = pack_data($fields);

		if($iblock->addData(array(vf($siblock['ibid'], 4), vf($siblock['title'],5), vf($siblock['description'],5), $siblock['fields'], $siblock['extopt'])))
		{
			$ibstruct = $iblock->CreateStructCache();
			$system->iblocks = $ibstruct['iblocks'];
			rcms_showAdminMessage(__('Infoblock succesfully added'));
		}
		else
		{
			rcms_showAdminMessage(__('Error'));
		}
		//*/
	}
	else
	{
		// iblock edit
		$ibid = vf($_POST['ibid'],4);
		$oldibid = vf($_POST['oldibid'],4);
		$siblock['ibid'] = $ibid;
		$siblock['title'] = vf($_POST['title'],5);
		$siblock['description'] = vf($_POST['description'],5);

		$siblock['extopt'] = array();
		$siblock['extopt']['delim_cond'] = vf($_POST['delim_cond'],3);
		if(!empty($_POST['enable_wms']))
		{
			$siblock['extopt']['enable_wms'] = true;
		}
		if(!empty($_POST['hidefields']))
		{
			$siblock['extopt']['hidefields'] = true;
		}
		if(!empty($_POST['rnm_title']))
		{
			$siblock['extopt']['rnm_title'] = vf($_POST['rnm_title'],5);
		}
		if(!empty($_POST['rnm_desc']))
		{
			$siblock['extopt']['rnm_desc'] = vf($_POST['rnm_desc'],5);
		}
		if(!empty($_POST['loadritems']))
		{
			$siblock['extopt']['loadritems'] = true;
			$siblock['extopt']['ritems_cnt'] = vf($_POST['ritems_cnt'],3);
			$siblock['extopt']['ritems_matchtag'] = !empty($_POST['ritems_matchtag']) ? true : false;
			$siblock['extopt']['ritems_extcond'] = str_replace('\'','"',$_POST['ritems_extcond']);
			if(!empty($_POST['ritems_sel']))
			{
				$siblock['extopt']['ritems_sel'] = explode(',', $_POST['ritems_sel']);
				array_walk($siblock['extopt']['ritems_sel'],"hcms_trim_array_w");
			}
			else 
			{
				$siblock['extopt']['ritems_sel'] = '';
			}
		}
		$siblock['extopt'] = pack_data($siblock['extopt']);

		// templates
		$itlisttplsrc = (!empty($_POST['dfitlisttpl']) ? file_get_contents(MODULES_TPL_PATH.'iblocks-item-list.tpl.php') : $_POST['itlisttplsrc']);
		$catlisttplsrc = (!empty($_POST['dfcatlisttpl']) ? file_get_contents(MODULES_TPL_PATH.'iblocks-cat-list.tpl.php') : $_POST['catlisttplsrc']);
		$contlisttplsrc = (!empty($_POST['dfcontlisttpl']) ? file_get_contents(MODULES_TPL_PATH.'iblocks-cont-list.tpl.php') : $_POST['contlisttplsrc']);
		$fulltplsrc = (!empty($_POST['dffulltpl']) ? file_get_contents(MODULES_TPL_PATH.'iblocks-item-full.tpl.php') : $_POST['fulltplsrc']);
		$listupsrc = $_POST['listupsrc'];
		$listdelimsrc = $_POST['listdelimsrc'];
		$listdownsrc = $_POST['listdownsrc'];

		if(!file_exists(DATA_PATH.'iblocks/templates/'.$siblock['ibid']))
		{
			if(!mkdir(DATA_PATH.'iblocks/templates/'.$siblock['ibid']))
			{
				rcms_showAdminMessage(__('Error creation iblock templates catalog'));
			}
		}
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/item-full.tpl.php',$fulltplsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/item-list.tpl.php',$itlisttplsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/cat-list.tpl.php',$catlisttplsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/cont-list.tpl.php',$contlisttplsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/listup.tpl.php',$listupsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/listdelim.tpl.php',$listdelimsrc,"w");
		dm_fwrite(DATA_PATH.'iblocks/templates/'.$siblock['ibid'].'/listdown.tpl.php',$listdownsrc,"w");

		//fields options
		$i = 0;
		$fields['fd_name'] = array();
		$fields['fd_id'] = array();
		if(isset($_POST['fd_name']))
		{
			$i++;
			$fields['fd_name'] = array_filter($_POST['fd_name']);
		}
		if(isset($_POST['fd_id']))
		{
			$i++;
			$fields['fd_id'] = array_filter($_POST['fd_id']);
		}
		if($i==2)
		{
			$needed = sizeof($fields['fd_id']);

			$fields['fd_nec'] = array_filter(array_slice($_POST['fd_nec'],0,$needed));
			$fields['fd_type'] = array_filter(array_slice($_POST['fd_type'],0,$needed));
			$fields['fd_store'] = array_slice($_POST['fd_store'],0,$needed);
			$fields['fd_radio'] = array_filter(array_slice($_POST['fd_radio'],0,$needed));
			$fields['fd_select'] = array_filter(array_slice($_POST['fd_select'],0,$needed));
			$fields['fd_checkbox'] = array_filter(array_slice($_POST['fd_checkbox'],0,$needed));
			$fields['fd_textarea'] = array_filter(array_slice($_POST['fd_textarea'],0,$needed));
			$fields['fd_text'] = array_filter(array_slice($_POST['fd_text'],0,$needed));
			$fields['fd_fileisimage'] = array_slice($_POST['fd_fileisimage'],0,$needed);
		}
		$siblock['fields'] = pack_data($fields);

		$iblock->setID($oldibid,'ibid');
		if($iblock->editData(array('ibid' => $siblock['ibid'], 'title' => $siblock['title'], 'description' => $siblock['description'], 'fields' => $siblock['fields'], 'extopt' => $siblock['extopt'])) || mysql_errno($mysql_data['connection']) == 0)
		{
			if($ibid != $oldibid)
			{
				$iblockitem = new iBlockItem();
				$iblockitem->setID($oldibid,'ibid');
				if($iblockitem->editData(array('ibid' => $ibid)) || mysql_errno($mysql_data['connection']) == 0)
				{
					$iblock->setWorkTable($iblock->prefix.'ibcategories');
					if($iblock->editData(array('ibid' => $ibid)) || mysql_errno($mysql_data['connection']) == 0)
					{
						$iblock->setWorkTable($iblock->prefix.'ibcontainers');
						if($iblock->editData(array('ibid' => $ibid)) || mysql_errno($mysql_data['connection']) == 0)
						{
							$ibstruct = $iblock->CreateStructCache();
							$system->iblocks = $ibstruct['iblocks'];
							rcms_showAdminMessage(__('Infoblock succesfully edited'));
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
			else
			{
				$ibstruct = $iblock->CreateStructCache();
				$system->iblocks = $ibstruct['iblocks'];
				rcms_showAdminMessage(__('Infoblock succesfully edited'));
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
	// iblock delete
	$keys=array_keys($_POST['delete']);
	$count = sizeof($keys);
	if(empty($_POST['confirmed']))
	{
		// confirm delete | display stats
		$iblockitem = new iBlockItem();
		$frm = new InputForm('','post',__('Submit'));
		$frm->hidden('confirmed',1);
		$frm->addbreak(__('Warning'));
		$frm->addrow(__('You choosed to delete these infoblocks').'. '.__('Please, confirm your choise'));

		for ($i=0; $i<$count; $i++)
		{
			$ibid = vf($keys[$i],4);
			$icount = $iblockitem->BeginiBlockItemsListRead(array('ibid' => $ibid),'*',20,'','',false,2);
			$frm->addrow('<b>'.$ibid.'</b>','<b style="color: red;">'.$icount.'</b> '.__('items exists').' | '.$frm->checkbox('delete[' . $ibid . ']', '1', __('Delete')));
		}
		$frm->addrow(__('You could delete single containers or categories instead'));
		$frm->show();
		exit;
	}
	else
	{
		$table = $iblock->prefix.'iblocks';
		$query = "DELETE FROM {table} WHERE ";
		$filter['ibid'] = array();
		for ($i=0; $i<$count; $i++)
		{
			$ibid = vf($keys[$i],4);
			$query.='ibid=\''.$ibid.'\' ';
			if($i!=$count-1)
			{
				$query.=' OR ';
			}
			array_push($filter['ibid'],$ibid);
			@rcms_delete_files(IBLOCKS_PATH.'files/'.$ibid,true);
			@rcms_delete_files(IBLOCKS_PATH.'images/'.$ibid,true);
			@rcms_delete_files(IBLOCKS_TPL_PATH.$ibid,true);
		}
		$query.=';';
		if($iblock->BeginRawDataRead(str_replace('{table}',$table,$query)) || mysql_errno($mysql_data['connection'])==0)
		{
			// rm container
			if($iblock->BeginRawDataRead(str_replace('{table}',$iblock->prefix.'ibcontainers',$query)) || mysql_errno($mysql_data['connection'])==0)
			{
				// rm categories, caticons
				$iblock->BeginCategoriesListRead($filter,array('icon'),false);
				while ($catdata = $iblock->Read())
				{
					@rcms_delete_files(IBLOCKS_PATH.'caticons/'.basename($catdata['icon']));
				}

				if($iblock->BeginRawDataRead(str_replace('{table}',$iblock->prefix.'ibcategories',$query)) || mysql_errno($mysql_data['connection'])==0)
				{
					// rm items
					$iblockitem = new iBlockItem();
					if($iblockitem->DropiBlockItems($filter))
					{
						$ibstruct = $iblock->CreateStructCache();
						$system->iblocks = $ibstruct['iblocks'];
						rcms_showAdminMessage(__('Infoblock(s) successfully deleted'));
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
		else
		{
			rcms_showAdminMessage(__('Error'));
		}
	}
}
else if(!empty($_POST['newib']) || !empty($_POST['edit']) || !empty($_POST['clone']))
{
	$frm = new InputForm ('', 'post', __('Submit'), '', '', '', 'iblockform');
	$frm->hidden('save', '1');

	if(!empty($_POST['edit']) || !empty($_POST['clone']))
	{
		// iblock edit form
		addEAcodejs();
		if(!empty($_POST['edit']))
		{
			$ibid = vf($_POST['edit'],4);
			$clone = false;
		}
		else
		{
			$ibid = vf($_POST['clone'],4);
			$clone = true;
			$frm->hidden('new',1);
		}
		//$infoblock=$iblock->ReadSingleiBlockData($ibid);
		$infoblock = $system->iblocks[$ibid];
		$infoblock['extopt'] = unpack_data($infoblock['extopt']);

		print('<style>
		.ifbuttons_l {overflow: auto; height: 200px; width: 120px;} 
		.ifbuttons {overflow: auto; height: 150px; width: 120px;} 
		.ifbuttons_s {overflow: auto; height: 70px; width: 120px;} 
		</style>');
		addibeditjs();
		$frm->hidden('oldibid',$ibid);
		if($clone)
		{
			$frm->addbreak(__('Clone iBlock').'<div style="display:inline; position: absolute; right: 10px;"><a id="interface_switcher" style="font-size:0.9em; color: #aaaaaa;" href="#">'.strtolower(($ibconfig['interface']['mode'] == 'simple' ? __('Simple').__(' interface mode') : __('Advanced').__(' interface mode')).'</a></div>'));
			$frm->addmessage('<b>'.__('Based on infoblock').' "'.$infoblock['title'].'" '.'</b>');
		}
		else
		{
			$frm->addbreak(__('Edit iBlock').'<div style="display:inline; position: absolute; right: 10px;"><a id="interface_switcher" style="font-size:0.9em; color: #aaaaaa;" href="#">'.strtolower(($ibconfig['interface']['mode'] == 'simple' ? __('Simple').__(' interface mode') : __('Advanced').__(' interface mode')).'</a></div>'));
		}
		$frm->addrow(__('Infoblock id'), $frm->text_box('ibid',  ($clone ? '' : $infoblock['ibid']), 0, 0, false,($clone ? '' :  'title="'.__('Change is not recommended').'" id="ibidtb" disabled')).($clone ? '' : ' <input type="button" value="'.__('Change').'" id="ibidbt"> ').__('Choose it carefully for SEO'), 'top');
		$frm->addrow(__('Title'), $frm->text_box('title', ($clone ? '' : $infoblock['title'])), 'top');
		$frm->addrow(__('Description'), $frm->textarea('description', ($clone ? '' : $infoblock['description']), 70, 5), 'top');
		$frm->addrow(__('Add watermarks to images'), $frm->checkbox('enable_wms', $infoblock['extopt']['enable_wms'], __('Yes'),$infoblock['extopt']['enable_wms']));
		$frm->addrow('',$frm->checkbox('hidefields', $infoblock['extopt']['hidefields'],__('Hide all fields except for extended from item edit/post form'),$infoblock['extopt']['hidefields']));
		$frm->addrow(__('Rename "Title" at post/edit form'),$frm->text_box('rnm_title', $infoblock['extopt']['rnm_title']));
		$frm->addrow(__('Rename "Description" at post/edit form'),$frm->text_box('rnm_desc', $infoblock['extopt']['rnm_desc']));
		$frm->addsingle('</table>
		<div class="ibopts" '.($ibconfig['interface']['mode'] == 'simple' ? 'style="display:none;"' : '').'>
		<table border="0" cellspacing="2" cellpadding="2" width="100%">');
		addritemshelpbreak($frm);
		$frm->addsingle('</table><table border="0" cellspacing="2" cellpadding="2" width="100%">');
		$frm->addrow(__('Load related items'), $frm->checkbox('loadritems', $infoblock['extopt']['loadritems'], __('Yes'),$infoblock['extopt']['loadritems']));
		$frm->addrow(__('Tag usage'), $frm->checkbox('ritems_matchtag', $infoblock['extopt']['ritems_matchtag'], __('Yes'),$infoblock['extopt']['ritems_matchtag']));
		$frm->addrow(__('Related items count'), $frm->text_box('ritems_cnt', $infoblock['extopt']['ritems_cnt']), 'top');
		$frm->addrow(__('Extended condition'), $frm->text_box('ritems_extcond', $infoblock['extopt']['ritems_extcond']), 'top');
		$frm->addrow(__('Selection').'<br><i>'.__('divide fields with comma').'</i>', $frm->text_box('ritems_sel', $infoblock['extopt']['ritems_sel']).' '.__('i.e., "index1, uid"'), 'top');
		addhelpbreak($frm);
		$frm->addsingle('</table><table id="fields" border="0" cellspacing="2" cellpadding="2" width="100%">');

		$fields = unpack_data($infoblock['fields']);
		$count = sizeof($fields['fd_id']);
		//var_dump($fields);
		$ifbuttons = '';

		$gallery_exists = false;
		for($i=0; $i<$count; $i++)
		{
			$ifbuttons.="\r\n".$frm->button($fields['fd_id'][$i],'style=\"display:block;\" onclick="return addItemField(\'{tplta}\',this.value);"');
			$id=rcms_random_string(3);
			$fd_radio = '<div class="fieldopt" style="'.($fields['fd_type'][$i] == 'radio' ? 'display:inline;' : '').'" id="radio'.$id.'"><textarea name="fd_radio[]">'.$fields['fd_radio'][$i].'</textarea> '.__('value - caption').'</div>';
			$fd_checkbox = '<div class="fieldopt" style="'.($fields['fd_type'][$i] == 'checkbox' ? 'display:inline;' : '').'" id="checkbox'.$id.'"><input type="text" name="fd_checkbox[]" value="'.$fields['fd_checkbox'][$i].'"> '.__('Caption').'</div>';
			$fd_textarea = '<br><div class="fieldopt" style="'.($fields['fd_type'][$i] == 'textarea' ? 'display:inline;' : '').'" id="textarea'.$id.'"><input type="text" value="'.$fields['fd_textarea'][$i].'" name="fd_textarea[]"> '.__('Rows count').'</div>';
			$fd_text = '<div class="fieldopt" style="'.($fields['fd_type'][$i] == 'text' ? 'display:inline;' : '').'" id="text'.$id.'">'.__('Data type').': '.$frm->select_tag('fd_text[]',array('' => __('Text'), 'number' => __('Number'), 'date' => __('Date')),$fields['fd_text'][$i]).'</div>';
			$fd_select = '<div class="fieldopt" style="'.($fields['fd_type'][$i] == 'select' ? 'display:inline;' : '').'" id="select'.$id.'"><textarea name="fd_select[]">'.$fields['fd_select'][$i].'</textarea> '.__('value - caption').'</div>';
			$capt = rcms_random_string(3);
			$fd_file = '<br><div class="fieldopt" style="'.($fields['fd_type'][$i] == 'file' ? 'display:inline;' : '').'" id="file'.$id.'"><input type="checkbox" '.($fields['fd_fileisimage'][$i] ? 'value="1" checked' : 'value="0"').' name="fd_fileisimage[]" id="'.$capt.'"> <label for="'.$capt.'">'.__('Images gallery').'</label></div>';
			if($fields['fd_fileisimage'][$i])
			{
				$gallery_exists = true;
			}

			$fd_typesel = '<select name="fd_type[]">
<option value="text" '.($fields['fd_type'][$i] == 'text' ? 'selected' : '').' onclick="showelembyid(\''.$id.'\',\'text\')">'.__('Text').'</option>
<option value="textarea" '.($fields['fd_type'][$i] == 'textarea' ? 'selected' : '').' onclick="showelembyid(\''.$id.'\',\'textarea\')">'.__('Textarea').'</option>
<option value="file" '.($fields['fd_type'][$i] == 'file' ? 'selected' : '').' onclick="fileclick(\''.$id.'\')">'.__('File(s)').'</option>
<option value="radio" '.($fields['fd_type'][$i] == 'radio' ? 'selected' : '').' onclick="showelembyid(\''.$id.'\', \'radio\')">'.__('Radio').'</option>
<option value="checkbox" '.($fields['fd_type'][$i] == 'checkbox' ? 'selected' : '').' onclick="showelembyid(\''.$id.'\', \'checkbox\')" >'.__('Checkbox').'</option>
<option value="select" '.($fields['fd_type'][$i] == 'select' ? 'selected' : '').' onclick="showelembyid(\''.$id.'\', \'select\')" >'.__('SELECT tag').'</option>
</select>';
			$fd_storesel = '<select id="fd_store_'.$id.'" name="fd_store[]">
	<option value="idata" '.($fields['fd_store'][$i] == 'idata' ? 'selected' : '').'>'.__('General').'</option>
	<option value="index1" '.($fields['fd_store'][$i] == 'index1' ? 'selected' : '').'>'.__('Index 1(int)').'</option>
	<option value="index2" '.($fields['fd_store'][$i] == 'index2' ? 'selected' : '').'>'.__('Index 2(int)').'</option>
	<option value="index3" '.($fields['fd_store'][$i] == 'index3' ? 'selected' : '').'>'.__('Index 3(text)').'</option>
	<option value="index4" '.($fields['fd_store'][$i] == 'index4' ? 'selected' : '').'>'.__('Index 4(text)').'</option>
	<option value="index5" '.($fields['fd_store'][$i] == 'index5' ? 'selected' : '').'>'.__('Index 5(text)').'</option>
	<option value="idate" '.($fields['fd_store'][$i] == 'idate' ? 'selected' : '').'>'.__('Date').'</option>
	</select>';

			$frm->addrow('<a href="#" class="drop_field_link" id="'.$id.'" title="'.__('Drop field').'">[x]</a> '.__('Name').' '.$frm->text_box('fd_name[]',$fields['fd_name'][$i],'18'),__('ID').' '.$frm->text_box('fd_id[]',$fields['fd_id'][$i],'12').' '.$frm->checkbox('fd_nec[]',$fields['fd_nec'][$i],__('Necessary'),$fields['fd_nec'][$i]).' | '.__('Store type').' '.$fd_storesel.' '.__('Type').' '.$fd_typesel.$fd_textarea.$fd_text.$fd_radio.$fd_select.$fd_checkbox.$fd_file, 'middle', 'left', 'id="'.$id.'"');
		}

		$frm->addsingle('</table><table border="0" cellspacing="2" cellpadding="2" width="100%">');
		$frm->addmessage('<center><span style="align: center; color: red; font-weight:bold;">'.__('Check up that all Name\'s and ID\'s are filled!').'</span></center>');

		// gallery code generation
		if($gallery_exists)
		{
			$nufrm = new InputForm();
			$frm->addsingle('</table><table id="gcg_table" border="0" cellspacing="2" cellpadding="2" width="100%">');
			$frm->addbreak(__('Gallery code generation'));
			$frm->addrow(__('Field id'),$nufrm->text_box('gcg_fieldid',''));
			$frm->addrow(__('Max image display width'),$nufrm->text_box('gcg_clip_width','400'));
			//$frm->addrow(__('Preview\'s columns count'),$nufrm->text_box('gcg_cols',''));
			$frm->addrow('','<div id="gcg_codediv" style="display:none; width:100%;"></div>'.$frm->button(__('Generate'),'id="gcg_genbutton"'));
			$frm->addsingle('</table><table border="0" cellspacing="2" cellpadding="2" width="100%">');
		}

		$frm->addbreak(__('Templates'));
		$fulltplsrc = (file_exists(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/item-full.tpl.php') ? file_get_contents(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/item-full.tpl.php') : '');
		$itlisttplsrc = (file_exists(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/item-list.tpl.php') ? file_get_contents(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/item-list.tpl.php') : '');
		$catlisttplsrc = (file_exists(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/cat-list.tpl.php') ? file_get_contents(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/cat-list.tpl.php') : '');
		$contlisttplsrc = (file_exists(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/cont-list.tpl.php') ? file_get_contents(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/cont-list.tpl.php') : '');

		$listupsrc = (file_exists(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/listup.tpl.php') ? file_get_contents(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/listup.tpl.php') : '');
		$listdelimsrc = (file_exists(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/listdelim.tpl.php') ? file_get_contents(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/listdelim.tpl.php') : '');
		$listdownsrc = (file_exists(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/listdown.tpl.php') ? file_get_contents(DATA_PATH.'iblocks/templates/'.$infoblock['ibid'].'/listdown.tpl.php') : '');

		$frm->addrow(__('Item full view template'),'<table><tr><td>'.$frm->checkbox('dffulltpl',1,__(__('Default template')),0,'id="dffulltplcb"').'<br>'.$frm->textarea('fulltplsrc',$fulltplsrc,80,20,'id="fulltplta"').'</td><td></td></tr></table>');
		$frm->addrow(__('Item list view template'),'<table><tr><td>'.$frm->checkbox('dfitlisttpl',1,__('Default template'),0,'id="dfitlisttplcb"').'<br>'.$frm->textarea('itlisttplsrc',$itlisttplsrc,80,10,'id="itlisttplta"').'</td><td></td></tr></table>');
		$frm->addrow(__('Category full view template'),'<table><tr><td>'.$frm->checkbox('dfcatlisttpl',1,__('Default template'),0,'id="dfcatlisttplcb"').'<br>'.$frm->textarea('catlisttplsrc',$catlisttplsrc,80,10,'id="catlisttplta"').'</td></tr></table>');
		$frm->addrow(__('Container full view template'),'<table><tr><td>'.$frm->checkbox('dfcontlisttpl',1,__('Default template'),0,'id="dfcontlisttplcb"').'<br>'.$frm->textarea('contlisttplsrc',$contlisttplsrc,80,10,'id="contlisttplta"').'</td></tr></table>');
		$frm->addrow(__('Listing top part'),'<table><tr><td>'.$frm->textarea('listupsrc',$listupsrc,80,4,'id="listuptplta"').'</td></tr></table>');
		$frm->addrow(__('Put delimiter for each #items'),$frm->num_box('delim_cond',$infoblock['extopt']['delim_cond']));
		$frm->addrow(__('Listing delimiter part'),'<table><tr><td>'.$frm->textarea('listdelimsrc',$listdelimsrc,80,2,'id="listdelimtplta"').'</td></tr></table>');
		$frm->addrow(__('Listing bottom part'),'<table><tr><td>'.$frm->textarea('listdownsrc',$listdownsrc,80,4,'id="listdowntplta"').'</td></tr></table>');

		$frm->addmessage('<center><span style="align: center; color: red; font-weight:bold;">'.__('Uncheck any to make your own template').'</span></center>');
	}
	else
	{
		// iblock add form
		addEAcodejs(true);
		print('<style>
		.ifbuttons_l {overflow: auto; height: 200px; width: 120px;} 
		.ifbuttons {overflow: auto; height: 150px; width: 120px;} 
		.ifbuttons_s {overflow: auto; height: 70px; width: 120px;} 
		</style>');
		addibeditjs();
		$frm->hidden('new',1);
		$frm->addbreak(__('New iBlock').'<div style="display:inline; position: absolute; right: 10px;"><a id="interface_switcher" style="font-size:0.9em; color: #aaaaaa;" href="#">'.strtolower(($ibconfig['interface']['mode'] == 'simple' ? __('Simple').__(' interface mode') : __('Advanced').__(' interface mode')).'</a></div>'));
		$frm->addrow(__('Infoblock id'), $frm->text_box('ibid', '').' '.__('Choose it carefully for SEO'), 'top');
		$frm->addrow(__('Title'), $frm->text_box('title', ''), 'top');
		$frm->addrow(__('Description'), $frm->textarea('description', '', 70, 5), 'top');
		$frm->addrow(__('Add watermarks to images'), $frm->checkbox('enable_wms', 1, __('Yes')));
		$frm->addrow('',$frm->checkbox('hidefields',1,__('Hide all fields except for extended from item edit/post form')));
		$frm->addrow(__('Rename "Title" at post/edit form'),$frm->text_box('rnm_title', ''));
		$frm->addrow(__('Rename "Description" at post/edit form'),$frm->text_box('rnm_desc', ''));
		$frm->addsingle('</table>
		<div class="ibopts" '.($ibconfig['interface']['mode'] == 'simple' ? 'style="display:none;"' : '').'>
		<table border="0" cellspacing="2" cellpadding="2" width="100%">');
		addritemshelpbreak($frm);
		$frm->addsingle('</table><table border="0" cellspacing="2" cellpadding="2" width="100%">');
		$frm->addrow(__('Load related items'), $frm->checkbox('loadritems', 1,__('Yes')));
		$frm->addrow(__('Tag usage'), $frm->checkbox('ritems_matchtag', 1, __('Yes')));
		$frm->addrow(__('Related items count'), $frm->text_box('ritems_cnt', '3'), 'top');
		$frm->addrow(__('Extended condition'), $frm->text_box('ritems_extcond', ''), 'top');
		$frm->addrow(__('Selection').'<br><i>'.__('divide fields with comma').'</i>', $frm->text_box('ritems_sel', '').' '.__('i.e., "index1, uid"'), 'top');

		addhelpbreak($frm);
		$frm->addsingle('</table><table id="fields" border="0" cellspacing="2" cellpadding="2" width="100%">');

		for($i=0;$i<5;$i++)
		{
			$id=rcms_random_string(3);
			$fd_radio = '<div class="fieldopt" id="radio'.$id.'"><textarea name="fd_radio[]"></textarea> '.__('value - caption').'</div>';
			$fd_checkbox = '<div class="fieldopt" id="checkbox'.$id.'"><input type="text" name="fd_checkbox[]"> '.__('Caption').'</div>';
			$fd_textarea = '<br><div class="fieldopt" id="textarea'.$id.'"><input type="text" value="10" name="fd_textarea[]"> '.__('Rows count').'</div>';
			$fd_text = '<div id="text'.$id.'">'.__('Data type').': '.$frm->select_tag('fd_text[]',array('' => __('Text'), 'number' => __('Number'), 'date' => __('Date')),'').'</div>';
			$fd_select = '<div class="fieldopt" id="select'.$id.'"><textarea name="fd_select[]"></textarea> '.__('value - caption').'</div>';
			$capt = rcms_random_string(3);
			$fd_file = '<br><div class="fieldopt" id="file'.$id.'"><input type="checkbox" value="1" name="fd_fileisimage[]" id="'.$capt.'"> <label for="'.$capt.'">'.__('Images gallery').'</label></div>';
			$fd_typesel = '<select name="fd_type[]">
<option value="text" onclick="showelembyid(\''.$id.'\', \'text\')">'.__('Text').'</option>
<option value="textarea" onclick="showelembyid(\''.$id.'\', \'textarea\')" >'.__('Textarea').'</option>
<option value="file" onclick="fileclick(\''.$id.'\')">'.__('File(s)').'</option>
<option value="radio" onclick="showelembyid(\''.$id.'\', \'radio\')">'.__('Radio').'</option>
<option value="checkbox" onclick="showelembyid(\''.$id.'\', \'checkbox\')" >'.__('Checkbox').'</option>
<option value="select" onclick="showelembyid(\''.$id.'\', \'select\')" >'.__('SELECT tag').'</option>
</select>';
			$fd_storesel = '<select id="fd_store_'.$id.'" name="fd_store[]">
	<option value="idata">'.__('General').'</option>
	<option value="index1">'.__('Index 1(int)').'</option>
	<option value="index2">'.__('Index 2(int)').'</option>
	<option value="index3">'.__('Index 3(text)').'</option>
	<option value="index4">'.__('Index 4(text)').'</option>
	<option value="index5">'.__('Index 5(text)').'</option>
	<option value="idate">'.__('Date').'</option>
	</select>';

			$frm->addrow('<a href="#" class="drop_field_link" id="'.$id.'" title="'.__('Drop field').'">[x]</a> '.__('Name').' '.$frm->text_box('fd_name[]','','18'),__('ID').' '.$frm->text_box('fd_id[]','','12').' '.$frm->checkbox('fd_nec[]',1,__('Necessary')).' | '.__('Store type').' '.$fd_storesel.' '.__('Type').' '.$fd_typesel.$fd_textarea.$fd_text.$fd_radio.$fd_select.$fd_checkbox.$fd_file, 'middle', 'left', 'id="'.$id.'"');
		}
		$frm->addsingle('</table><table border="0" cellspacing="2" cellpadding="2" width="100%">');
		$frm->addmessage('<center><span style="align: center; color: red; font-weight:bold;">'.__('Check up that all Name\'s and ID\'s are filled!').'</span></center>');

		// gallery code generation
		$nufrm = new InputForm();
		$frm->addsingle('</table><table id="gcg_table" style="display:none;" border="0" cellspacing="2" cellpadding="2" width="100%">');
		$frm->addbreak(__('Gallery code generation'));
		$frm->addrow(__('Field id'),$nufrm->text_box('gcg_fieldid',''));
		$frm->addrow(__('Max image display width'),$nufrm->text_box('gcg_clip_width','400'));
		//$frm->addrow(__('Preview\'s columns count'),$nufrm->text_box('gcg_cols',''));
		$frm->addrow('','<div id="gcg_codediv" style="display:none; width:100%;"></div>'.$frm->button(__('Generate'),'id="gcg_genbutton"'));
		$frm->addsingle('</table><table border="0" cellspacing="2" cellpadding="2" width="100%">');

		$frm->addbreak(__('Templates'));
		$frm->addrow(__('Item full view template'),'<table><tr><td>'.$frm->checkbox('dffulltpl',1,__(__('Default template')),1,'id="dffulltplcb"').'<br>'.$frm->textarea('fulltplsrc','',80,20,'id="fulltplta" style="display: none;"').'</td><td></td></tr></table>');
		$frm->addrow(__('Item list view template'),'<table><tr><td>'.$frm->checkbox('dfitlisttpl',1,__('Default template'),1,'id="dfitlisttplcb"').'<br>'.$frm->textarea('itlisttplsrc','',80,10,'id="itlisttplta" style="display: none;"').'</td><td></td></tr></table>');
		$frm->addrow(__('Category full view template'),'<table><tr><td>'.$frm->checkbox('dfcatlisttpl',1,__('Default template'),1,'id="dfcatlisttplcb"').'<br>'.$frm->textarea('catlisttplsrc','',80,10,'id="catlisttplta" style="display: none;"').'</td></tr></table>');
		$frm->addrow(__('Container full view template'),'<table><tr><td>'.$frm->checkbox('dfcontlisttpl',1,__('Default template'),1,'id="dfcontlisttplcb"').'<br>'.$frm->textarea('contlisttplsrc','',80,10,'id="contlisttplta" style="display: none;"').'</td></tr></table>');
		$frm->addrow(__('Listing top part'),'<table><tr><td>'.$frm->textarea('listupsrc','',80,4,'id="listuptplta"').'</td></tr></table>');
		$frm->addrow(__('Put delimiter for each #items'),$frm->num_box('delim_cond',0));
		$frm->addrow(__('Listing delimiter part'),'<table><tr><td>'.$frm->textarea('listdelimsrc','',80,2,'id="listdelimtplta"').'</td></tr></table>');
		$frm->addrow(__('Listing bottom part'),'<table><tr><td>'.$frm->textarea('listdownsrc','',80,4,'id="listdowntplta"').'</td></tr></table>');
		$frm->addmessage('<center><span style="align: center; color: red; font-weight:bold;">'.__('Uncheck any to make your own template').'</span></center>');
	}
	$frm->addsingle('</table>
	</div>
	<table border="0" cellspacing="2" cellpadding="2" width="100%">');
	$frm->show();
	exit;
}

$frm = new InputForm('','post',__('Create infoblock'),'','','','add');
$frm->hidden('newib',1);
$frm->show();

$frm = new InputForm('','post',__('Submit'),__('Reset'),'','','mng');
if(!empty($system->iblocks))
{
	foreach ($system->iblocks as $id => $title){
		$title = $title['title'];
		$frm->addrow($title, $frm->checkbox('delete[' . $id . ']', '1', __('Delete')) . ' ' . $frm->radio_button('edit', array($id => __('Edit'))) . ' ' . $frm->radio_button('clone', array($id => __('Clone'))));
	}
}
$frm->show();

function ifbuttons($ifbuttons, $tplta)
{
	return str_replace('{tplta}',$tplta,$ifbuttons);
}

function addibeditjs()
{
	$int_simple = strtolower(__('Simple').__(' interface mode'));
	$int_advanced = strtolower(__('Advanced').__(' interface mode'));
	print('
		<script type="text/javascript" src="modules/js/jquery.js"></script>
		<script type="text/javascript">
		var galleries;
		galleries=0;
		
		$(document).ready(
		function ()
		{
			// Gallery code generation functions
			$("input[name=\'fd_fileisimage[]\']").click(function()
			{
				if(this.checked)
				{
					galleries++;
				}
				else if(galleries!=0)
				{
					galleries--;
				}
				if(galleries>0)
				{
					$("#gcg_table").fadeIn("slow");
				}
				else
				{
					$("#gcg_table").fadeOut("slow");
				}
			});
			
			$("a.drop_field_link").click(function()
			{
				$("tr#" + $(this).attr("id")).slideDown("slow");
				$("tr#" + $(this).attr("id")).remove();
				
				return false;
			});
			
			$("input#gcg_genbutton").click(function()
			{
				var field_id;
				field_id = $("input[name=\'gcg_fieldid\']").attr("value");
				//var cols;
				//cols = $("input[name=\'gcg_cols\']").attr("value");
				var clip_width;
				clip_width = $("input[name=\'gcg_clip_width\']").attr("value");
				
				var code;
				code = \'<b>'.__('Javascript code: ').'</b>'.__('insert at the top of the template').'<br>\n\
				<textarea cols="80" rows="15">'.hcms_htmlsecure('<script type="text/javascript">	\n\
function gl_showhide(id)	\n\
{	\n\
	$("#" + id).slideToggle("fast");\n\
}\n\
\n\
$(document).ready(function()\n\
{\n\
	$("a.item_thumbs").click(function()\n\
	{\n\
		var largePath = $(this).attr("href");\n\
\n\
		$("#largeImg").animate({opacity: 0.8}, "fast");\n\
		$("#largeImg").attr({ src: largePath});\n\
		$("#largeImgP").attr({ href: largePath});\n\
		$("#largeImg").animate({opacity: 1}, "fast");\n\
\n\
		return false;\n\
	});\n\
});\n\
</script>\n\\').'</textarea>\n\
<br><b>'.__('HTML/PHP Code: ').'</b>'.__('"big" image + thumbnails').'\n\
<br>\n\
<textarea cols="80" rows="15"><?php // '.__('preparing for gallery generation').'\n\
if(sizeof(@$tpldata[\\\'\'+ field_id +\'\\\']) > 0) // '.__('check size of image\\\'s array').'\n\
	{\n\
		// forming gallery\n\
		$pa_keys = array_keys($tpldata[\\\'\'+ field_id +\'\\\']);\n\
		$pa_count = sizeof($pa_keys);\n\
		$size = getimagesize($tpldata[\\\'\'+ field_id +\'\\\'][$pa_keys[0]]);\n\
		if(file_exists($tpldata[\\\'\'+ field_id +\'\\\'][$pa_keys[0]]))\n\
		{\n\
   ?>   \n\
   \n\
   <!-- GALLERY / -->\n\
   <a target="_blank" id="largeImgP" href="<?=$tpldata[\\\'\'+ field_id +\'\\\'][$pa_keys[0]]?>"><img <?=($size[0] > \'+ clip_width +\' ? \\\' width="\'+ clip_width +\'"\\\' : \\\'\\\')?> src="<?=$tpldata[\\\'\'+ field_id +\'\\\'][$pa_keys[0]]?>" id="largeImg" />\n\
   <!-- ^^ '.__('"big" image').' ^^ -->\n\
		<?php\n\
		for($pa_index=0; $pa_index<$pa_count; $pa_index++)\n\
		{\n\
			if(!file_exists($tpldata[\\\'\'+ field_id +\'\\\'][$pa_keys[$pa_index]]))\n\
			{\n\
				continue;\n\
			}\n\
			$pa_fname = basename($tpldata[\\\'\'+ field_id +\'\\\'][$pa_keys[$pa_index]]);\n\
			$pa_path = mb_substr($tpldata[\\\'\'+ field_id +\'\\\'][$pa_keys[$pa_index]],0,-mb_strlen($pa_fname));\n\
			// '.__('cyclic thumbnails code generation').'\n\
			print(\\\'<a href="\\\'.$tpldata[\\\'\'+ field_id +\'\\\'][$pa_keys[$pa_index]].\\\'" class="item_thumbs"><img src="\\\'.$pa_path.\\\'th_\\\'.$pa_fname.\\\'" class="hand" /></a>\\\');\n\
		}\n\
   		?>\n\
	<!-- / GALLERY -->\n\
	<?php\n\
		}\n\
	}\n\
   ?>\';
				
				$("#gcg_codediv").html(code);
				$("#gcg_codediv").slideDown("slow");
			});
			
			// end ^^

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
			
			$("input#helpbutton").click(function()
			{
				if(this.value==\''.__('Show help').'\')
				{
					this.value = "'.__('Hide help').'";
				}
				else
				{
					this.value = "'.__('Show help').'";				
				}
				$("#helppad").slideToggle("slow");			
			});
			
			$("input#ritemshelpbutton").click(function()
			{
				if(this.value==\''.__('Show help').'\')
				{
					this.value = "'.__('Hide help').'";
				}
				else
				{
					this.value = "'.__('Show help').'";				
				}
				$("#ritemshelppad").slideToggle("slow");			
			});
		
			$("input.btnmain").click(function()
			{
				$("input:checkbox").each(
				function()
				{
					if(this.checked)
					{
						this.setAttribute("value",1);
					}
					else
					{
						this.setAttribute("checked","checked");
						this.setAttribute("value",0);
					}
					this.style.display = \'none\';
				});
				
				$("input#ibidtb").attr("disabled","");
			});
			
			$("input#ibidbt").click(function()
			{
				this.style.display = \'none\';
				$("input#ibidtb").attr("disabled","");
			});
			
			$("input[@name=\'dffulltpl\']:checkbox").click(function()
			{
				$("textarea#fulltplta").slideToggle("slow");		
				$("div#fulltpldiv").slideToggle("slow");
			});
			
			$("input[@name=\'dfitlisttpl\']:checkbox").click(function()
			{
				$("textarea#itlisttplta").slideToggle("slow");
				$("div#itlisttpldiv").slideToggle("slow");
			});
			
			$("input[@name=\'dfcatlisttpl\']:checkbox").click(function()
			{
				$("textarea#catlisttplta").slideToggle("slow");
				$("div#catlisttpldiv").slideToggle("slow");
			});
			
			$("input[@name=\'dfcontlisttpl\']:checkbox").click(function()
			{
				$("textarea#contlisttplta").slideToggle("slow");
				$("div#contlisttpldiv").slideToggle("slow");	
			});
		});
		
		function showelembyid(id,type)
		{
			hideelems(id);
			$("#" + type + id).slideDown("slow");
		}
		
		function fileclick(id)
		{
			hideelems(id);
			$("#file" + id).slideDown("slow");
			$("#fd_store_" + id).attr("disabled","disabled");
			$("#fd_store_" + id).attr("value","idata");
			//$("input:checkbox." + id).attr("checked","checked");
			//$("input:checkbox." + id).attr("disabled","disabled");
		}
		
		function hideelems(id)
		{
			$("#radio" + id).fadeOut("fast");
			$("#checkbox" + id).fadeOut("fast");
			$("#select" + id).fadeOut("fast");
			$("#file" + id).fadeOut("fast");
			$("#textarea" + id).fadeOut("fast");
			$("#text" + id).fadeOut("fast");
			$("#fd_store_" + id).attr("disabled","");
			//$("input:checkbox." + id).attr("disabled","");
		}
		function loadfieldtr()
		{
			$.get("admin.php?show=module&id=iblocks.iblocks&clean&addfield",function(data){
				$("#fields").append(data);
				
				$("a.drop_field_link").click(function()
				{
					$("tr#" + $(this).attr("id")).slideDown("slow");
					$("tr#" + $(this).attr("id")).remove();
					
					return false;
				});
			});
		}
		
		function addItemField(textareaID, itemField)
		{
			$("#" + textareaID).focus();
		
			if(document.selection)
			{
				document.selection.createRange().text = "<?=$tpldata[\'" + itemField + "\']?>";
			}
			else // gecko
			{
				var textarea = document.getElementByID(textareaID);
				var selLength = textarea.textLength;
				var selStart = textarea.selectionStart;
				var selEnd = textarea.selectionEnd;
				if (selEnd == 1 || selEnd == 2) 
					selEnd = selLength;

				var s1 = (textarea.value).substring(0,selStart);
				var s2 = (textarea.value).substring(selStart, selLength);
				textarea.value = s1 + "<?=$tpldata[\'" + itemField + "\']?>" + s2;
			}
			
			return false;
		}	
		</script>
		<style>.fieldopt {margin: 2px; display: none;}</style>
		');
}

function addritemshelpbreak($frm)
{
	$frm->addbreak(__('Related items options').' <input type="button" value="'.__('Show help').'" id="ritemshelpbutton" style="width: 120px">
		<div id="ritemshelppad" style="margin: 2px; padding:2px; border: 2px solid eeeeff; background-color: #2288aa; height: 140px; overflow: auto; position: relative; z-index: 10; display:none;">
		<table width="100%" valign="top" style="color: #eeeeff;">
		<tr><td width="100%">'.
	__('<b>Extended condition</b><br>
You can type there your extended search condition, using simple MySQL syntax: i.e. "<i>(index1="{index1}" AND index2 BETWEEN 5 AND {index2}) OR hidenhigh=2</i>"; <br>This condition means that related item must have the same "index1" value and "index2" value between 5 and main item "index2" value OR "hidenhigh" value equal to 2.<p>
<b>Selection</b><br>
By default, only fields "id", "title", "idate" will be selected from the MySQL table, but you can manually select any other related item field; <b>Note:</b> some fields as "idata" are stored packed, use unpack_data() Core function to retrieve data from them.')
	.'</td></tr>
		</table>	
		</div>');
}

function addhelpbreak($frm)
{
	$frm->addbreak(__('Extended fields').' <input type="button" value="'.__('Add one field').'" onclick="loadfieldtr();"> <input type="button" value="'.__('Show help').'" id="helpbutton" style="width: 120px">
		<div id="helppad" style="margin: 2px; padding:2px; border: 2px solid eeeeff; background-color: #2288aa; height: 190px; overflow: auto; position: relative; z-index: 10; display:none;">
		<table width="100%" valign="top" style="color: #eeeeff;">
		<tr><td width="50%">
'.__('<b>Fields description:</b><br>
- Name - for creation of add/edit form;<br>
- ID - for markup elements on the template;<br>
- Array is used for creation of multiple same fields;').'
		</td><td rowspan="2">
		'.__('<b>Existing fields:</b><br>
- id - general identifier of the iblock item;<br>
- contid - item container identifier;<br>
- catid - item category identifier;<br>
- comcount - comments count;<br>
- views - views count;<br>
- idate - item publication date;<br>
- title - item title;<br>
- description - item description;<br>
- itext - item full text;<br>
- source - item source (text);<br>
- uid - publicator user id;<br>
- tags - item tags;<br>
- hidenhigh - hidden/general/highlighted item option;').'
		</td></tr>
		<tr><td>
'.__('<b>Notes:</b><br>
- for the field type "Checkbox" you should define caption;<br>
- for the field types "Radio" and "SELECT tag" you should define list of values and captions;').'
		</td></tr>
		</table>	
		</div>');
}

function addEAcodejs($later = false)
{
	print('<script language="javascript" type="text/javascript" src="modules/js/edit_area/edit_area_full.js"></script>
<script language="javascript" type="text/javascript">
editAreaLoader.init({
	id : "catlisttplta"
	'.getEAopt($later).'
});
editAreaLoader.init({
	id : "itlisttplta"
	'.getEAopt($later).'
});
editAreaLoader.init({
	id : "fulltplta"
	'.getEAopt($later).'
});
editAreaLoader.init({
	id : "contlisttplta"
	'.getEAopt($later).'
});
editAreaLoader.init({
	id : "listuptplta"
	'.getEAopt($later).'
});
editAreaLoader.init({
	id : "listdelimtplta"
	'.getEAopt($later).'
});
editAreaLoader.init({
	id : "listdowntplta"
	'.getEAopt($later).'
});
</script>');
}

function getEAopt($later)
{
	return ',syntax: "php",
	start_highlight: true,
	font_size: 9,
	language: "ru"'.($later ? ', display: "later"' : '');
}

/**
 * ���������� � ���� ����������.
 *
 * @param string $file
 * @param string $data
 * @param char $mode
 */
function dm_fwrite($file,$data,$mode)
{
	$f=fopen($file,$mode);
	fwrite($f,$data);
	fclose($f);
}
?>