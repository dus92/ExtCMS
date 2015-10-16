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
$iblockitem = new iBlockItem();
$ibid = (isset($_POST['ibid']) ? vf($_POST['ibid'],4) : '');
$ibgid = (isset($_POST['ibgid']) ? vf($_POST['ibgid'],4) : '');

if(!empty($_POST['save']))
{
	// save item
	$ibid = vf($_POST['ibid'],4);
	if(!($system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER') || $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR')))
	{
		rcms_showAdminMessage(__('You do not have rights to post items in these iblock'));
		exit;
	}
	$contid = vf($_POST['contid'],4);
	$catid = vf($_POST['catid'],3);

	$infoblock = $system->iblocks[$ibid];
	$fields = unpack_data($infoblock['fields']);
	$iblockext = unpack_data($system->iblocks[$ibid]['extopt']);
	$iblockitem = new iBlockItem();
	$id = $iblockitem->GetTableAINextValue();

	$item = ib_checkpost($fields, $id, $ibid);
	if(isset($item['indexed']))
	{
		$indexed = $item['indexed'];
		unset($item['indexed']);
	}

	$no_post=false;
	if(empty($item['title']) || empty($item['idate']))
	{
		rcms_showAdminMessage(__('Error').': '.__('fill the item title'));
	}
	else
	{
		$item['ibid'] = $ibid;
		$item['contid'] = $contid;
		$item['catid'] = $catid;
		$item['id'] = $id;
		$item['uid'] = $system->user['username'];
		$item['idata'] = vf(pack_data($item));

		$posted_data = array(null,$item['catid'],$item['contid'],$item['ibid'],null,null,$item['idate'],$item['title'],$item['description'],$item['idata'],$item['source'],$item['uid'],$item['tags'],$item['hidenhigh']);

		// indexed data
		for($i=1; $i<=5; $i++)
		{
			if(!empty($indexed['index'.$i]))
			{
				array_push($posted_data,$indexed['index'.$i]);
			}
			else
			{
				array_push($posted_data,null);
			}
		}
		// end

		if($iblockitem->addData($posted_data))
		{
			if($contid=='static_pages')
			{
				rcms_showAdminMessage(__('Static page').' "'.$item['title'].'" '.__('successfully created').'<br>URL: <input size="100" type="text" value="'.hcms_get_weblocation().'index.php?module=iblocks&action=show&item='.$item['id'].'">');
			}
			else
			{
				rcms_showAdminMessage(__('Item with id #').' '.$id.' '.__(' successfully added'));
			}
		}
		else
		{
			rcms_showAdminMessage(__('Error'));
		}
	}
}

if(!empty($ibid))
{
	if(!($system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER') || $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR')))
	{
		rcms_showAdminMessage(__('You do not have rights to post items in these iblock'));
		exit;
	}
	$containers = ib_filter_plarrays($system->containers, 'ibid', $ibid);
	if(sizeof($containers) == 1)
	{
		$key = array_keys($containers);
		$_POST['contid'] = $key[0];
	}
}

if(!empty($_POST['contid']))
{
	// form
	$contid = vf($_POST['contid'],4);
	$ibid = vf($_POST['ibid'],4);
	if(!($system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER') || $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR')))
	{
		rcms_showAdminMessage(__('You do not have rights to post items in these iblock'));
		exit;
	}
	$iblock->BeginCategoriesListRead($contid,array('catid','title'));
	while($clcategory = $iblock->Read())
	{
		$ibcategories[$clcategory['catid']] = $clcategory['title'];
	}

	$iblockitem = new iBlockItem();
	$id = $iblockitem->GetTableAINextValue();

	$iblockext = unpack_data($system->iblocks[$ibid]['extopt']);
	$enable_wms = @$system->config['enable_wms'] && !empty($iblockext['enable_wms']);
	$asyncmgr = new AsyncMgr();
	$ie = false;
	if (mb_strstr(getenv("HTTP_USER_AGENT"),"MSIE"))
	{
		$ie = true;
		$asyncmgr->printImgUpFormJS(array('iblockfiles',$ibid,date("Y"),date("M"),$id), 'image',false, true, array('load_stopped_3.gif', 'load_process_3.gif'), 120, false, $enable_wms);
	}
	else
	{
		$asyncmgr->printImgUpFormJS(array('iblockimages',$ibid,date("Y"),date("M"),$id), 'image',true, false, array('load_stopped.gif', 'load_process.gif'),120, false, $enable_wms);
		$asyncmgr->printFileUpFormJs(array('iblockfiles',$ibid,date("Y"),date("M"),$id), 'file',true, false, array('load_stopped_3.gif', 'load_process_3.gif'), $enable_wms);
	}


	print('<script>
	$(document).ready(function()
	{
		$("#imgpad").slideDown("slow");
		$("#imgpad2").slideDown("slow");
		
		$("input#hidetextbutton").click(function()
		{
			if(this.value==\''.__('Show text').'\')
			{
				this.value = "'.__('Hide text').'";
			}
			else
			{
				this.value = "'.__('Show text').'";				
			}
			$("#imgpad2").slideToggle("fast");
			$("#textbbpanel").slideToggle("fast");			
			$("#attfiles").slideToggle("fast");
			$("#itext_table").slideToggle("fast");			
		});
	});');

	if(empty($system->ibconfig['interface']['editor_mode']) || $system->ibconfig['interface']['editor_mode']=='default')
	{
		print('function addImage(textareaId, linkId)
	{
		var image = $("#pasteimglink" + linkId).attr("href");
		$("#" + textareaId).focus();
		
		if(document.selection)
		{
			document.selection.createRange().text = "[img]" + image + "[/img]";
		}
		else // gecko
		{
			var textarea = document.getElementById(textareaId);
			var selLength = textarea.textLength;
			var selStart = textarea.selectionStart;
			var selEnd = textarea.selectionEnd;
			if (selEnd == 1 || selEnd == 2) 
				selEnd = selLength;

			var s1 = (textarea.value).substring(0,selStart);
			var s2 = (textarea.value).substring(selStart, selLength);
			textarea.value = s1 + \'[img]\' + image + \'[/img]\' + s2;
		}
		
		return false;
	}');
	}
	else
	{
		print('function addImage(textareaId, linkId)
	{
		tinyMCE.activeEditor.selection.setContent(\'<img src="\' + $("#pasteimglink" + linkId).attr("href") + \'">\');
		
		return false;
	}');
	}

	print('
	function showhidero(imgoptdivId, iconId, linkId)
	{
		$("#" + imgoptdivId).slideToggle("slow");
		if($("#" + iconId).attr("src") == "admin/show.png")
		{
			$("#" + iconId).attr("src","admin/hide.png");
			$("#" + linkId).attr("title","'.__('Hide options').'");
		}
		else
		{
			$("#" + iconId).attr("src","admin/show.png");
			$("#" + linkId).attr("title","'.__('Show options').'");
		}
	}
	</script>');

	$frm = new InputForm ('', 'post', __('Submit'), '', '', 'multipart/form-data', 'itemadd');
	if(empty($ibcategories))
	{
		$frm->addmessage(__('No categories in container'));
		$frm->show();
		return ;
	}
	$frm->addbreak(__('Post item'));
	$frm->hidden('save', '1');
	$frm->hidden('new', '1');
	$frm->hidden('contid',$contid);
	$frm->hidden('ibid',$ibid);
	$frm->addrow(__('Select category'), $frm->select_tag('catid', $ibcategories), 'top');
	$frm->addrow((!empty($iblockext['rnm_title']) ? $iblockext['rnm_title'] : __('Title')), $frm->text_box('title', '',40), 'top');
	$frm->addrow(__('Source'),$frm->text_box('source','',40));
	$frm->addrow(__('Tags'), $frm->text_box('tags','',40),'top');
	$frm->addrow(__('Keywords'), $frm->text_box('keywords','',40),'top');
	$frm->addrow(__('Description for search engines'), $frm->text_box('sdescription','',40),'top');

	if(!isset($system->ibconfig['interface']['editor_mode']))
	{
		$system->ibconfig['interface']['editor_mode']='default';
	}
	if($system->ibconfig['interface']['editor_mode']=='default')
	{
		$frm->addrow('',rcms_show_bbcode_panel('itemadd.description'));
	}
	else
	{
		// TinyMCE
		$frm->addsingle(file_get_contents(JSS_PATH.'tiny_mce/simple.html'));
	}
	$frm->addrow((!empty($iblockext['rnm_desc']) ? $iblockext['rnm_desc'] : __('Description')),'<table><tr><td>'.$frm->textarea('description','',70,12,'id="description"').'</td>
	<td>'.($ie ? '' : '<div title="'.__('Image upload panel').'" id="imgpad" style="overflow: auto; display: none; background-color: #ffffff; border: 1px solid 1779DD; width: 200px; height: '.($system->ibconfig['interface']['editor_mode']=='default' ? '154px' : '187px').'; padding: 2px">'
	.$frm->file('upFileField','id="upFileField" style="width: 180px;"').'<br>
	<div id="imagesopt" style="margin: 2px; display:none;">'.__('Resize').': '.$frm->text_box('image_rwidth','',4,0,false,'id="image_rwidth"').' x '.$frm->text_box('image_rheight','auto',4,0,false,'id="image_rheight"').
	($enable_wms ? __('Watermark').' '.$frm->select_tag('image_wmpos',array('left_top' => __('Left top'), 'left_middle' => __('Left middle'), 'left_bottom' => __('Left bottom'), 'center_top' => __('Center top'), 'center_middle' => __('Center middle'), 'center_bottom' => __('Center bottom'), 'right_top' => __('Right top'), 'right_middle' => __('Right middle'), 'right_bottom' => __('Right bottom')), 'center_middle', 'id="image_wmpos"') : '').'</div>'.
	'<a id="optlink" title="'.__('Show options').'" href="#" onclick="showhidero(\'imagesopt\',\'sh_image\', this.id);"><img style="margin-bottom: -4px; margin-right: 2px;" src="admin/show.png" id="sh_image"></a>'
	.$frm->button(__('Upload'),'id="uplButton" onclick="return ajaxFileUpload();" style="width: 140px"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped.gif" id="loadicon">
	<div id="intimages" style="text-align: center;"></div>
	</div>').'</td></tr></table>');
	if(!@$iblockext['hidefields'])
	{
		$frm->hidden('idate', date("Y-m-d H:i:s"));
		$frm->addrow('<center><input type="button" id="hidetextbutton" value="'.__('Hide text').'"></center>',($system->ibconfig['interface']['editor_mode']=='default' ? '<div id="textbbpanel">'.rcms_show_bbcode_panel('itemadd.text').'</div>' : ''));
		$frm->addrow(__('Text'),'<table id="itext_table"><tr><td>'.$frm->textarea('itext','',70,40,'id="text"').'</td>
	<td>'.($ie ? '' : '<div title="'.__('Image upload panel').'" id="imgpad2" style="overflow: auto; display: none; background-color: #ffffff; border: 1px solid 1779DD; width: 200px; height: '.($system->ibconfig['interface']['editor_mode']=='default' ? '598px' : '607px').'; padding: 2px">
	'.$frm->file('upFileField2','id="upFileField2" style="width: 180px;"').'<br>
	<div id="imagesopt2" style="margin: 2px; display:none;">'.__('Resize').': '.$frm->text_box('image2_rwidth','',4,0,false,'id="image2_rwidth"').' x '.$frm->text_box('image2_rheight','auto',4,0,false,'id="image2_rheight"').
	($enable_wms ? __('Watermark').' '.$frm->select_tag('image2_wmpos',array('left_top' => __('Left top'), 'left_middle' => __('Left middle'), 'left_bottom' => __('Left bottom'), 'center_top' => __('Center top'), 'center_middle' => __('Center middle'), 'center_bottom' => __('Center bottom'), 'right_top' => __('Right top'), 'right_middle' => __('Right middle'), 'right_bottom' => __('Right bottom')), 'center_middle', 'id="image2_wmpos"') : '').'</div>'.
	'<a id="optlink2" title="'.__('Show options').'" href="#" onclick="showhidero(\'imagesopt2\',\'sh_image2\', this.id);"><img style="margin-bottom: -4px; margin-right: 2px;" src="admin/show.png" id="sh_image2"></a>'
	.$frm->button(__('Upload'),'id="uplButton2" onclick="return ajaxFileUpload(\'upFileField2\',\'intimages2\',\'loadicon2\',\'uplButton2\',\'text\',\'image2\',\'ibimage\');" style="width: 140px"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped.gif" id="loadicon2">
	<div id="intimages2" style="text-align: center;"></div>
	</div>').'</td></tr></table>');
	$frm->addrow(__('Attach files'), '<div id="attfiles">'.$frm->file('upFileField_f','id="upFileField_f"').' '.$frm->button(__('Upload'),'id="uplButton_f" onclick="return ajaxFileUpload_f();" style="width: 140px;"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_f">
	<div id="intfiles"></div></div>');
	}

	// Custom infoBlock fields

	$infoblock = $system->iblocks[$ibid]; // pre-load usage
	$fields = unpack_data($infoblock['fields']);
	$container = $system->containers[$contid];
	if(isset($container['substitutions']))
	{
		$substitutions = unpack_data($container['substitutions']);
	}

	//var_dump($fields);
	$count = sizeof($fields['fd_id']);
	if($count)
	{
		$frm->addbreak(__('Custom iBlock fields'));
		for($i=0; $i<$count; $i++)
		{
			if(isset($substitutions['drop'][$fields['fd_id'][$i]]))
			{
				continue;
			}

			switch ($fields['fd_type'][$i])
			{
				case 'text':
					if(!isset($fields['fd_text'][$i]))
					{
						$fields['fd_text'][$i] = '';
					}
					switch($fields['fd_text'][$i])
					{
						case 'date':
							$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->text_box($fields['fd_id'][$i],date("d.m.Y H:i")).' '.__('Please, fill this field according to this format').': <b>'.__('YYYY-MM-DD HH:MM').'</b>');
							break;
						case 'number':
							$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->num_box($fields['fd_id'][$i],'',20));
							break;
						case 'text':
						default:
							$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->text_box($fields['fd_id'][$i],''));
							break;
					}
					break;
				case 'textarea':
					$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->textarea($fields['fd_id'][$i],'',70,$fields['fd_textarea'][$i]));
					break;
				case 'file':
					if($fields['fd_fileisimage'][$i])
					{
						if ($ie)
						{
							$asyncmgr->addAddPart((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]),$frm,'image',$fields['fd_id'][$i],'load_stopped_3.gif');
						}
						else
						{
							$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->file($fields['fd_id'][$i],'id="'.$fields['fd_id'][$i].'"').' '.$frm->button(__('Upload'),'id="uplButton_'.$fields['fd_id'][$i].'" onclick="return ajaxFileUpload_f(\''.$fields['fd_id'][$i].'\',\'intimage_'.$fields['fd_id'][$i].'\',\'loadicon_'.$fields['fd_id'][$i].'\',\'uplButton_'.$fields['fd_id'][$i].'\',\''.$fields['fd_id'][$i].'\',\'galleryimage\', \''.(isset($system->config['th_width']) ? $system->config['th_width'] : 100).'x'.(isset($system->config['th_height']) ? $system->config['th_height'] : 100).'\');" style="width: 140px"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_'.$fields['fd_id'][$i].'">
	<div id="intimage_'.$fields['fd_id'][$i].'"></div>');
						}
					}
					else
					{
						$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->file($fields['fd_id'][$i],'id="'.$fields['fd_id'][$i].'"').' '.$frm->button(__('Upload'),'id="uplButton_'.$fields['fd_id'][$i].'" onclick="return ajaxFileUpload_f(\''.$fields['fd_id'][$i].'\',\'intfile_'.$fields['fd_id'][$i].'\',\'loadicon_'.$fields['fd_id'][$i].'\',\'uplButton_'.$fields['fd_id'][$i].'\',\''.$fields['fd_id'][$i].'\');" style="width: 140px;"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_'.$fields['fd_id'][$i].'">
	<div id="intfile_'.$fields['fd_id'][$i].'"></div>');
					}
					break;
				case 'radio':
					$pairs = explode("\r\n",$fields['fd_radio'][$i]);
					$pcount = sizeof($pairs);
					for($s=0; $s<$pcount; $s++)
					{
						$pair = explode('-',$pairs[$s],2);
						$radio[trim($pair[0])] = trim($pair[1]);
					}
					$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]),$frm->radio_button($fields['fd_id'][$i],$radio));
					break;
				case 'checkbox':
					$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]),$frm->checkbox($fields['fd_id'][$i],1,$fields['fd_checkbox'][$i]));
					break;
				case 'select':
					$pairs = explode("\r\n",$fields['fd_select'][$i]);
					$pcount = sizeof($pairs);
					//var_dump($pairs);
					for($s=0; $s<$pcount; $s++)
					{
						$pair = explode('-',$pairs[$s],2);
						$select[trim($pair[0])] = trim($pair[1]);
					}
					$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]),$frm->select_tag($fields['fd_id'][$i],$select));
					$select = '';
					break;
			}
		}
	}

	// Custom infoBlock fields end


	$frm->addbreak(__('Options'));
	$frm->addrow(__('Display options'), $frm->radio_button('hidenhigh',array('0' => __('Hidden'), '1' => __('General'), '2' => __('Highlight')),1),'top');
	if($system->ibconfig['interface']['editor_mode']=='default')
	{
		$frm->addrow(__('Mode'), $frm->radio_button('mode', array('html' => __('HTML'), 'text' => __('Text'), 'htmlbb' => __('bbCodes') . '+' . __('HTML')), 'text'), 'top');
	}
	else
	{
		$frm->addrow(__('Mode'), $frm->radio_button('mode', array('html' => __('HTML'), 'text' => __('Text'), 'htmlbb' => __('bbCodes') . '+' . __('HTML')), 'html'), 'top');
	}
	$frm->addrow(__('Allow comments'), $frm->radio_button('comments', array('1' => __('Allow'), '0' => __('Disallow')), '1'), 'top');
	$frm->show();
	exit;
}

// iblock selection
$frm=new InputForm ('', 'post', __('Submit'));
if(!empty($system->ibgroups)) // $infoblocks -> $system->iblocks
{
	$ibgroups = $system->ibgroups;
	$ibgroups['__NONE__'] = __('Show all iblocks');
	array_multisort($ibgroups);
	$frm->addrow(__('Select iblock group'), $frm->select_tag('ibgid', $ibgroups, '__NONE__'), 'top');
}

if(!empty($ibgid) || empty($system->ibgroups))
{
	if(!empty($system->iblocks)) // $infoblocks -> $system->iblocks
	{
		if($ibgid && $ibgid!='__NONE__')
		{
			$ibids = explode(',', $system->ibgroups[$ibgid]['ibids']);
			$count = sizeof($ibids);
			for($i=0; $i<$count; $i++)
			{
				$sel_ibids[$ibids[$i]] = $system->iblocks[$ibids[$i]]['title'];
			}
		}
		else
		{
			$sel_ibids = $system->iblocks;
		}
		$frm->addrow(__('Select infoblock'), $frm->select_tag('ibid', $sel_ibids, $ibid), 'top');
	}	else
	{
		$frm->addmessage(__('No infoblocks in group'));
	}
}

if(!empty($ibid))
{
	// containers list
	$frm->hidden('ibid',$ibid);

	if(!($system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER') || $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR')))
	{
		$frm->addmessage(__('You do not have rights to post items in these iblock'));
		$frm->show();
		exit;
	}

	if(!empty($containers))
	{
		$frm->addrow(__('Select container'), $frm->select_tag('contid', $containers), 'top');
	}
	else
	{
		$frm->addmessage(__('No containers in infoblock'));
	}
}
$frm->show();
?>