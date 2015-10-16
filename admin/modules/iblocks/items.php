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
rcms_loadAdminLib('file-uploads');

$iblockitem = new iBlockItem();
$iblock = new iBlock();

if(isset($_POST['refreshcat']) && !empty($_POST['refreshcat'])) // refresh items
{
	$frm = new InputForm ('', 'post', '&lt;&lt;&lt; ' . __('Back'));
	$catid = vf($_POST['catid'],3);
	$contid = vf($_REQUEST['contid'],4);
	$ibid = vf($_REQUEST['ibid'],3);
	$frm->hidden('catid', $catid);
	$frm->hidden('contid', $contid);
	$frm->hidden('ibid', $ibid);
	if($system->checkForRight('IBLOCKS-EDITOR') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR'))
	{
		if(empty($catid) || $_POST['refreshcat']!='1')
		{
			exit;
		}

		$iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
		$iblockitem->setId($catid,'catid');
		$iblockitem->BeginDataRead(array('id','ibid','idate'));
		$resultid = $iblockitem->GetLastResultId();
		while ($item=$iblockitem->Read($resultid))
		{
			$date = date("Y-m-d").' '.mb_substr($item['idate'],-8);
			$iblockitem->setId($item['id'],'id');
			$itime = strtotime($item['idate']);
			if($iblockitem->editData(array('idate' => $date)) && rename(IBLOCKS_PATH.'files/'.$item['ibid'].'/'.date("Y",$itime).'/'.date("M",$itime).'/'.$item['id'], IBLOCKS_PATH.'files/'.$item['ibid'].'/'.date("Y").'/'.date("M").'/'.$item['id']))
			{
				$frm->addmessage('Date for element #'.$item['id'].' '.__('refreshed successfully').' ('.$date.')!');
			}
		}

		$frm->addmessage('<b>'.__('Items in category=').$catid.__(' successfuly refreshed!').'</b>');
	}
	else
	{
		$frm->addmessage(__('You do not have rights to refresh full category in this iblock'));
	}
	$frm->show();
	exit;
}
if(isset($_POST['refresh']) && sizeof($_POST['refresh']) > 0) // refresh items
{
	$keys=array_keys($_POST['refresh']);
	$iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
	$frm = new InputForm ('', 'post', '&lt;&lt;&lt; ' . __('Back'));
	$ibid = vf($_REQUEST['ibid'],3);
	$catid = vf($_REQUEST['catid'],3);
	$contid = vf($_REQUEST['contid'],4);
	$frm->hidden('ibid', $ibid);
	$frm->hidden('catid', $catid);
	$frm->hidden('contid', $contid);

	$ids = preg_replace("/[^0-9 ORid=]/",'',implode(' OR id=',$keys));
	$i=0;
	$iblockitem->BeginiBlockItemsListRead(false,array('id','idate'),false,'','','id='.$ids);
	while($item=$iblockitem->Read())
	{
		$items[$i] = $item;
		$i++;
	}

	$count = sizeof($items);
	for($i=0; $i < $count; $i++)
	{
		$iblockitem->setId($items[$i]['id'],'id');
		$date = date("Y-m-d").' '.mb_substr($items[$i]['idate'],-8);
		if($iblockitem->editData(array('idate' => $date)))
		{
			$frm->addmessage(__('Item with id #').$items[$i]['id'].' '.__('successfully refreshed'));
		}
	}
	//*/
	$frm->show();
	if(!isset($_POST['edit']))
	{
		exit;
	}
}
if(!empty($_POST['save']))
{
	// save item
	$ibid = vf($_POST['ibid'],4);
	$contid = vf($_POST['contid'],4);
	$catid = vf($_POST['catid'],3);
	$id = vf($_POST['id'],3);

	$item = $iblockitem->ReadSingleiBlockItemData($id, array('uid'));

	if(!($system->checkForRight('IBLOCKS-POSTER') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER') && $item['uid']==$system->user['username']) && !($system->checkForRight('IBLOCKS-EDITOR') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR')))
	{
		$frm = new InputForm ('', 'post', '&lt;&lt;&lt; ' . __('Back'));
		$frm->hidden('catid', $catid);
		$frm->hidden('contid', $contid);
		$frm->hidden('ibid', $ibid);

		$frm->addmessage(__('You do not have rights to edit this item'));
		$frm->show();
		exit;
	}

	$infoblock = $iblock->ReadSingleiBlockData($ibid);
	$fields = unpack_data($infoblock['fields']);

	$item = ib_checkpost($fields, $id, $ibid);
	if(isset($item['indexed']))
	{
		$indexed = $item['indexed'];
		unset($item['indexed']);
	}
	$item['ibid'] = $ibid;
	$item['contid'] = $contid;
	$item['catid'] = $catid;
	$item['id'] = $id;
	$item['uid'] = $system->user['username'];
	$item['idata'] = vf(pack_data($item));

	if(empty($_POST['tlang']))
	{
		$posted_data = array('catid' => $item['catid'],'title' => $item['title'], 'description' => $item['description'], 'idata' => $item['idata'], 'source' => $item['source'], 'tags' => $item['tags'], 'hidenhigh' => $item['hidenhigh']);

		// indexed data
		if(isset($indexed))
		{
			for($i=1; $i<=5; $i++)
			{
				if(!empty($indexed['index'.$i]))
				{
					$posted_data['index'.$i]=$indexed['index'.$i];
				}
			}
		}
		// end

		if(!isset($fields['fd_store']) || !(array_search('idate',$fields['fd_store'])===false))
		{
			$posted_data['idate'] = $item['idate'];
		}

		$iblockitem->setId($id, 'id');
		if($iblockitem->editData($posted_data))
		{
			rcms_showAdminMessage(__('Item with id #').$id.' '.__('successfully edited'));
		}
		else
		{
			if(mysql_errno($mysql_data['connection']) != 0)
			{
				rcms_showAdminMessage(__('Error'));
			}
			else
			{
				rcms_showAdminMessage(__('Item with id #').' '.$id.__('successfully edited'));
			}
		}
	}
	else
	{
		$posted_data = array($item['id'],$item['catid'],$item['contid'],$item['ibid'],null,null,$item['idate'],$item['title'],$item['description'],$item['idata'],$item['source'],$item['uid'],$item['tags'],$item['hidenhigh']);

		// indexed data
		$pkeys = array('catid','title','description','idata','source','tags','hidenhigh','idate');

		for($i=1; $i<=5; $i++)
		{
			if(!empty($indexed['index'.$i]))
			{
				array_push($posted_data,$indexed['index'.$i]);
				array_push($pkeys,'index'.$i);
			}
			else
			{
				array_push($posted_data,null);
			}
		}
		// end

		/*
		var_dump($iblockitem);
		var_dump($posted_data);
		exit;
		//*/

		$tlang = vf($_POST['tlang'],2);
		$iblockitem->setWorkTable($iblockitem->prefix.'ibitems_'.$tlang);
		$iblockitem->setId($id, 'id');
		if($iblockitem->updateData($posted_data, $pkeys))
		{
			rcms_showAdminMessage(__($system->data['languages'][$tlang]).' '.__('translation for item #').$id.' '.__('successfully edited'));
		}
		else
		{
			if(mysql_errno($mysql_data['connection']) != 0)
			{
				rcms_showAdminMessage(__('Error'));
			}
			else
			{
				rcms_showAdminMessage(__($system->data['languages'][$tlang]).' '.__('translation for item #').$id.' '.__('successfully edited'));
			}
		}
	}

}
if(!empty($_REQUEST['delete']))
{
	// delete item
	$ibid = vf($_REQUEST['ibid'],3);
	$catid = vf($_REQUEST['catid'],3);
	$contid = vf($_REQUEST['contid'],4);

	if(!is_array($_REQUEST['delete']))
	{
		$_REQUEST['delete'] = array($_REQUEST['delete'] => 1);
	}

	//	if(!($system->checkForRight('IBLOCKS-POSTER') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER') && $item['uid']==$system->user['username']) && !($system->checkForRight('IBLOCKS-EDITOR') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR')))
	// TODO: make check if it is the user's item
	if(!($system->checkForRight('IBLOCKS-POSTER') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER')) && !($system->checkForRight('IBLOCKS-EDITOR') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR')))
	{
		$frm = new InputForm ('', 'post', '&lt;&lt;&lt; ' . __('Back'));
		$frm->hidden('catid', $catid);
		$frm->hidden('contid', $contid);
		$frm->hidden('ibid', $ibid);

		$frm->addmessage(__('You do not have rights to delete these items'));
		$frm->show();
		exit;
	}

	$keys=array_keys($_REQUEST['delete']);
	$count = sizeof($keys);
	$iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
	$iblock->setWorkTable($iblock->prefix.'ibcategories');

	$iblock->setId($catid,'id');
	$category = $iblock->ReadSingleCategoryData($catid,array('itemscount', 'last_item'));
	for($i=0; $i < $count; $i++)
	{
		// no deletion of falcificated iids
		$id = vf($keys[$i],3);
		if($id!=$keys[$i])
		{
			continue;
		}

		$curitem = $iblockitem->ReadSingleiBlockItemData($id,array('ibid','idate'));
		$timestamp = strtotime($curitem['idate']);
		rcms_delete_files(IBLOCKS_PATH.'images/'.$curitem['ibid'].'/'.date("Y",$timestamp).'/'.date("M",$timestamp).'/'.$id,true);

		if($iblockitem->dropData('id',$id))
		{
			if($id == $category['last_item'])
			{
				$iblockitem->BeginRawDataRead("SELECT id FROM ".$iblock->prefix.'ibitems'." ORDER BY idate DESC LIMIT 1");
				$item=$iblockitem->Read();
				$iblock->editData(array('itemscount' => 'itemscount-1', 'last_item' => $item['id']),true);
			}
			else
			{
				rcms_showAdminMessage(__('Item with id #').$id.' '.__('successfully deleted'));
			}
		}
	}
	if(!isset($_POST['edit']))
	{
		exit;
	}
}
if(!empty($_REQUEST['edit']))
{
	// edit form
	$id = vf($_REQUEST['edit'],3);
	$frm = new InputForm ('', 'post', '&lt;&lt;&lt; ' . __('Back'));

	$iblockitem = new iBlockItem();
	if(!empty($_GET['tlang']))
	{
		// creating translation
		$tlang = vf($_GET['tlang'],2);
	}
	$item = $iblockitem->ReadSingleiBlockItemData($id, '*', $tlang);

	if($item)
	{
		$item = array_merge(unpack_data($item['idata']),$item);
		$ibid = $item['ibid'];
		$contid = $item['contid'];
		$catid = $item['catid'];

		$iblock->BeginCategoriesListRead($contid,array('catid','title'));
		while($clcategory = $iblock->Read())
		{
			$ibcategories[$clcategory['catid']] = __($clcategory['title']);
		}

		// iblocks v1 compability
		if(empty($item['itext']))
		{
			// try "text"
			$item['itext'] = @$item['text'];
		}
		$itemdate = strtotime($item['idate']);

		// parse bbcodes into html if TinyMCE is used as editor
		if($system->ibconfig['interface']['editor_mode']=='tinymce' && $item['mode']!='html')
		{
			$item['itext'] = rcms_parse_text_by_mode($item['itext'],$item['mode']);
			$item['description'] = rcms_parse_text_by_mode($item['description'],$item['mode']);
		}
		$frm->hidden('catid', $catid);
		$frm->hidden('contid', $contid);
		$frm->hidden('ibid', $ibid);
		if(!($system->checkForRight('IBLOCKS-POSTER') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER') && $item['uid']==$system->user['username']) && !($system->checkForRight('IBLOCKS-EDITOR') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR')))
		{
			$frm->addmessage(__('You do not have rights to edit items in these iblock'));
			$frm->show();
			exit;
		}

		$frm->show();
		/*
		print('<pre>');
		print_r($item['photo']);
		print('</pre>');
		exit;
		//*/

		$iblockext = unpack_data($system->iblocks[$ibid]['extopt']);
		$enable_wms = @$system->config['enable_wms'] && !empty($iblockext['enable_wms']);

		$asyncmgr = new AsyncMgr();
		$ie = false;
		if (mb_strstr(getenv("HTTP_USER_AGENT"),"MSIE"))
		{
			$ie = true;
			$asyncmgr->printImgUpFormJS(array('iblockfiles',$ibid,date("Y",$itemdate),date("M",$itemdate),$id), 'image',false, true, array('load_stopped_3.gif', 'load_process_3.gif'), 120, false, $enable_wms);
		}
		else
		{

			$asyncmgr->printImgUpFormJS(array('iblockimages',$ibid,date("Y",$itemdate),date("M",$itemdate),$id), 'image','intimages',false, array('load_stopped.gif', 'load_process.gif'),120,false, $enable_wms);
			$asyncmgr->printFileUpFormJs(array('iblockfiles',$ibid,date("Y",$itemdate),date("M",$itemdate),$id), 'file','intfiles',false, array('load_stopped_3.gif', 'load_process_3.gif'), $enable_wms);
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

		if($system->ibconfig['interface']['editor_mode']=='default')
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

		if(empty($tlang))
		{
			$frm->addbreak(__('Edit item'));
		}
		else
		{
			// translation create form
			$frm->addbreak(__($system->data['languages'][$tlang]).' '.__('translation for item #').$id);
			$frm->hidden('tlang', $tlang);
		}
		$frm->hidden('save', '1');
		$frm->hidden('contid',$contid);
		$frm->hidden('ibid',$ibid);
		$frm->hidden('id',$id);
		$frm->hidden('idate', date("Y-m-d H:i:s",strtotime($item['idate'])));
		$frm->addrow(__('Select category'), $frm->select_tag('catid', $ibcategories, $item['catid']), 'top');
		$frm->addrow((!empty($iblockext['rnm_title']) ? $iblockext['rnm_title'] : __('Title')), $frm->text_box('title', $item['title'],40), 'top');
		$frm->addrow(__('Source'),$frm->text_box('source',$item['source'],40));
		$frm->addrow(__('Tags'), $frm->text_box('tags',$item['tags'],40),'top');
		$frm->addrow(__('Keywords'), $frm->text_box('keywords',$item['keywords'],40),'top');
		$frm->addrow(__('Description for search engines'), $frm->text_box('sdescription',$item['sdescription'],40),'top');
		if($system->ibconfig['interface']['editor_mode']=='default')
		{
			$frm->addrow('',rcms_show_bbcode_panel('itemadd.description'));
		}
		else
		{
			// TinyMCE
			$frm->addsingle(file_get_contents(JSS_PATH.'tiny_mce/simple.html'));
		}

		$cyear = date("Y",$itemdate);
		$cmonth = date("M",$itemdate);

		// attached images [text]
		$content = '<table><tr><td>'.$frm->textarea('description',$item['description'],70,10,'id="description"').'</td>
	<td>'.($ie ? '' : '<div title="'.__('Image upload panel').'" id="imgpad" style="overflow: auto; display: none; background-color: #ffffff; border: 1px solid 1779DD; width: 200px; height: '.($system->ibconfig['interface']['editor_mode']=='default' ? '154px' : '174px').'; padding: 2px">
		'.$frm->file('upFileField','id="upFileField" style="width: 180px;"').
		'<div id="imagesopt" style="margin: 2px; display:none;">'.__('Resize').': '.$frm->text_box('image_rwidth','',4,0,false,'id="image_rwidth"').' x '.$frm->text_box('image_rheight','auto',4,0,false,'id="image_rheight"').
		($enable_wms ? __('Watermark').' '.$frm->select_tag('image_wmpos',array('left_top' => __('Left top'), 'left_middle' => __('Left middle'), 'left_bottom' => __('Left bottom'), 'center_top' => __('Center top'), 'center_middle' => __('Center middle'), 'center_bottom' => __('Center bottom'), 'right_top' => __('Right top'), 'right_middle' => __('Right middle'), 'right_bottom' => __('Right bottom')), 'center_middle', 'id="image_wmpos"') : '').'</div>'.
		'<a id="optlink" title="'.__('Show options').'" href="#" onclick="showhidero(\'imagesopt\',\'sh_image\', this.id);"><img style="margin-bottom: -4px; margin-right: 2px;" src="admin/show.png" id="sh_image"></a>'.$frm->button(__('Upload'),'id="uplButton" onclick="return ajaxFileUpload();" style="width: 140px"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped.gif" id="loadicon">
	<div id="intimages" style="text-align: center;">');

		if(!$ie)
		{
			$filefield = $item['image'];
			$keys = array_keys($filefield);
			$fcount = sizeof($filefield);
			for($s=0; $s<$fcount; $s++)
			{
				if(!is_file($filefield[$keys[$s]]))
				{
					continue;
				}
				$file = basename($filefield[$keys[$s]]);
				// check if file is in the old folder
				if(mb_strpos($filefield[$keys[$s]],$cyear)===false || mb_strpos($filefield[$keys[$s]],$cmonth)===false)
				{
					// moving file
					$npath = GetPath('iblockimages',$ibid,$cyear,$cmonth,$id).$file;
					copy($filefield[$keys[$s]], $npath);
					$filefield[$keys[$s]] = $npath;
					unset($npath);
				}

				$tpl = '<a title="'.__('Click to paste image').'" href="{dvalue}" id="pasteimglink'.$keys[$s].'" onclick="return addImage(\'description\',\''.$keys[$s].'\');"><img src="{dvalue}" width="100"></a>';
				$content .= $asyncmgr->addDispPart($keys[$s], 'image', $tpl, $filefield[$keys[$s]], $file);
			}

			$content .= '</div></div></td></tr></table>';
		}
		else
		{
			$content .= '</td></tr></table>';
		}
		$frm->addrow((!empty($iblockext['rnm_desc']) ? $iblockext['rnm_desc'] : __('Description')), $content);
		//*/
		if(!@$iblockext['hidefields'])
		{
			$frm->addrow('<center><input type="button" id="hidetextbutton" value="'.__('Hide text').'"></center>',($system->ibconfig['interface']['editor_mode']=='default' ? '<div id="textbbpanel">'.rcms_show_bbcode_panel('itemadd.text').'</div>' : ''));

			// attached images [text]
			$content = '<table id="itext_table"><tr><td>'.$frm->textarea('itext',$item['itext'],70,40,'id="text"').'</td>
	<td><div title="'.__('Image upload panel').'" id="imgpad2" style="overflow: auto; display: none; background-color: #ffffff; border: 1px solid 1779DD; width: 200px; height: '.($system->ibconfig['interface']['editor_mode']=='default' ? '598px' : '528px').'; padding: 2px">
		'.$frm->file('upFileField2','id="upFileField2" style="width: 180px;"').'<br>'.
		'<div id="imagesopt2" style="margin: 2px; display:none;">'.__('Resize').': '.$frm->text_box('image2_rwidth','',4,0,false,'id="image2_rwidth"').' x '.$frm->text_box('image2_rheight','auto',4,0,false,'id="image2_rheight"').
		($enable_wms ? __('Watermark').' '.$frm->select_tag('image2_wmpos',array('left_top' => __('Left top'), 'left_middle' => __('Left middle'), 'left_bottom' => __('Left bottom'), 'center_top' => __('Center top'), 'center_middle' => __('Center middle'), 'center_bottom' => __('Center bottom'), 'right_top' => __('Right top'), 'right_middle' => __('Right middle'), 'right_bottom' => __('Right bottom')), 'center_middle', 'id="image2_wmpos"') : '').'</div>'.
		'<a id="optlink2" title="'.__('Show options').'" href="#" onclick="showhidero(\'imagesopt2\',\'sh_image2\', this.id)"><img style="margin-bottom: -4px; margin-right: 2px;" src="admin/show.png" id="sh_image2"></a>'.$frm->button(__('Upload'),'id="uplButton2" onclick="return ajaxFileUpload(\'upFileField2\',\'intimages2\',\'loadicon2\',\'uplButton2\',\'text\',\'image2\',\'ibimage\');" style="width: 140px"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped.gif" id="loadicon2">
	<div id="intimages2" style="text-align: center;">';

		$filefield = $item['image2'];
		$keys = array_keys($filefield);
		$fcount = sizeof($filefield);
		for($s=0; $s<$fcount; $s++)
		{
			if(file_exists($filefield[$keys[$s]]))
			{
				$file = basename($filefield[$keys[$s]]);
				// check if file is in the old folder
				if(mb_strpos($filefield[$keys[$s]],$ibid)===false || mb_strpos($filefield[$keys[$s]],$cyear)===false || mb_strpos($filefield[$keys[$s]],$cmonth)===false)
				{
					// moving file
					$npath = GetPath('iblockimages',$ibid,$cyear,$cmonth,$id).$file;
					copy($filefield[$keys[$s]], $npath);
					$filefield[$keys[$s]] = $npath;
					unset($npath);
				}

				$tpl = '<a title="'.__('Click to paste image').'" href="{dvalue}" id="pasteimglink'.$keys[$s].'" onclick="return addImage(\'text\',\''.$keys[$s].'\');"><img src="{dvalue}" width="100"></a>';
				$content .= $asyncmgr->addDispPart($keys[$s], 'image2', $tpl, $filefield[$keys[$s]], $file);
			}
		}

		$content .= '</div></div></td></tr></table>';
		$frm->addrow(__('Text'), $content);
		//*/

		// attached files
		$content = '<div id="attfiles">'.$frm->file('upFileField_f','id="upFileField_f"').' '.$frm->button(__('Upload'),'id="uplButton_f" onclick="return ajaxFileUpload_f();" style="width: 140px;"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_f">
	<div id="intfiles">';

		$filefield = $item['file'];
		$keys = array_keys($filefield);
		$fcount = sizeof($filefield);
		for($s=0; $s<$fcount; $s++)
		{
			$file = basename($filefield[$keys[$s]]);
			// check if file is in the old folder
			if(mb_strpos($filefield[$keys[$s]],$ibid)===false || mb_strpos($filefield[$keys[$s]],$cyear)===false || mb_strpos($filefield[$keys[$s]],$cmonth)===false)
			{
				// moving file
				$npath = GetPath('iblockfiles',$ibid,$cyear,$cmonth,$id).$file;
				copy($filefield[$keys[$s]], $npath);
				$filefield[$keys[$s]] = $npath;
				unset($npath);
			}

			$tpl = '<a href="{dvalue}" style="font-weight: bold;" title="'.$file.'">'.$file.'</a><br>- '.__('Size').': '.hcms_filesize($filefield[$keys[$s]]);
			$content .= $asyncmgr->addDispPart($keys[$s], 'file', $tpl, $filefield[$keys[$s]], $file, '_f');
		}

		$content .= '</div>';
		$frm->addrow(__('Attach files'), $content);
		}

		// Custom infoBlock fields

		//$infoblock = $iblock->ReadSingleiBlockData($ibid);
		$infoblock = $system->iblocks[$ibid]; // pre-load usage
		$fields = unpack_data($infoblock['fields']);
		$container = $system->containers[$contid];
		if(isset($container['substitutions']))
		{
			$substitutions = unpack_data($container['substitutions']);
		}

		//var_dump($fields);
		$old_er = error_reporting(E_ERROR); // <<<--- PAY ATTENTION!!!
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

				if(!empty($fields['fd_store'][$i]) && !empty($item[$fields['fd_store'][$i]]) && $fields['fd_store'][$i]!='idata')
				{
					// indexed values
					$item[$fields['fd_id'][$i]] = $item[$fields['fd_store'][$i]];
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
								$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->text_box($fields['fd_id'][$i],$item[$fields['fd_id'][$i]]).' '.__('Please, fill this field according to this format').': <b>'.__('YYYY-MM-DD HH:MM').'</b>');
								break;
							case 'number':
								$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->num_box($fields['fd_id'][$i],$item[$fields['fd_id'][$i]],20));
								break;
							case 'text':
							default:
								$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->text_box($fields['fd_id'][$i],$item[$fields['fd_id'][$i]]));
								break;
						}
						break;
					case 'textarea':
						$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $frm->textarea($fields['fd_id'][$i],$item[$fields['fd_id'][$i]],70,$fields['fd_textarea'][$i]));
						break;
					case 'file':
						if ($ie)
						{
							if(isset($item[$fields['fd_id'][$i]]))
							{
								$filefield = $item[$fields['fd_id'][$i]];
								$keys = array_keys($filefield);
								$file_name = basename($filefield[$keys[0]]);
								$asyncmgr->addEditPart((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]),$frm,'image','<img alt="'.$file_name.'" src="{dvalue}" width="150">',$filefield[$keys[0]],$file_name,$fields['fd_id'][$i],'load_stopped_3.gif');
							}
						}
						else
						{
							if($fields['fd_fileisimage'][$i])
							{
								$content = $frm->file($fields['fd_id'][$i],'id="'.$fields['fd_id'][$i].'"').' '.$frm->button(__('Upload'),'id="uplButton_'.$fields['fd_id'][$i].'" onclick="return ajaxFileUpload_f(\''.$fields['fd_id'][$i].'\',\'intimage_'.$fields['fd_id'][$i].'\',\'loadicon_'.$fields['fd_id'][$i].'\',\'uplButton_'.$fields['fd_id'][$i].'\',\''.$fields['fd_id'][$i].'\',\'galleryimage\', \''.(isset($system->config['th_width']) ? $system->config['th_width'] : 100).'x'.(isset($system->config['th_width']) ? $system->config['th_height'] : 100).'\');" style="width: 140px"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_'.$fields['fd_id'][$i].'">
	<div id="intimage_'.$fields['fd_id'][$i].'">';

								if(isset($item[$fields['fd_id'][$i]]))
								{
									$filefield = $item[$fields['fd_id'][$i]];

									$keys = array_keys($filefield);
									$fcount = sizeof($filefield);

									for($s=0; $s<$fcount; $s++)
									{
										if(!is_file($filefield[$keys[$s]]))
										{
											continue;
										}

										$file = basename($filefield[$keys[$s]]);

										// check if file is in the old folder
										if(mb_strpos($filefield[$keys[$s]],$ibid)===false || mb_strpos($filefield[$keys[$s]],$cyear)===false || mb_strpos($filefield[$keys[$s]],$cmonth)===false)
										{
											// moving file
											$npath = GetPath('iblockfiles',$ibid,$cyear,$cmonth,$id);
											copy($filefield[$keys[$s]], $npath.$file);
											copy(dirname($filefield[$keys[$s]]).'/th_'.$file, $npath.'th_'.$file);
											$filefield[$keys[$s]] = $npath.$file;
											unset($npath);
										}

										$tpl = '<img alt="'.$file.'" src="{dvalue}" width="150">';
										$content .= $asyncmgr->addDispPart($keys[$s], $fields['fd_id'][$i], $tpl, $filefield[$keys[$s]], $file, '_f');
									}

									$content .= '</div>';
									$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $content);
								}
							}
							else
							{
								$content = $frm->file($fields['fd_id'][$i],'id="'.$fields['fd_id'][$i].'"').' '.$frm->button(__('Upload'),'id="uplButton_'.$fields['fd_id'][$i].'" onclick="return ajaxFileUpload_f(\''.$fields['fd_id'][$i].'\',\'intfile_'.$fields['fd_id'][$i].'\',\'loadicon_'.$fields['fd_id'][$i].'\',\'uplButton_'.$fields['fd_id'][$i].'\',\''.$fields['fd_id'][$i].'\',\'ibfile\');" style="width: 140px"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_'.$fields['fd_id'][$i].'">
	<div id="intfile_'.$fields['fd_id'][$i].'">';

								$filefield = $item[$fields['fd_id'][$i]];
								$keys = array_keys($filefield);
								$fcount = sizeof($filefield);
								for($s=0; $s<$fcount; $s++)
								{
									if(!is_file($filefield[$keys[$s]]))
									{
										continue;
									}

									$file = basename($filefield[$keys[$s]]);

									// check if file is in the old folder
									if(mb_strpos($filefield[$keys[$s]],$ibid)===false || mb_strpos($filefield[$keys[$s]],$cyear)===false || mb_strpos($filefield[$keys[$s]],$cmonth)===false)
									{
										// moving file
										$npath = GetPath('iblockfiles',$ibid,$cyear,$cmonth,$id).$file;
										copy($filefield[$keys[$s]], $npath);
										$filefield[$keys[$s]] = $npath;
										unset($npath);
									}

									$tpl = '<a href="{dvalue}" style="font-weight: bold;" title="'.$file.'">'.$file.'</a><br>- '.__('Size').': '.hcms_filesize($filefield[$keys[$s]]);
									$content .= $asyncmgr->addDispPart($keys[$s], $fields['fd_id'][$i], $tpl, $filefield[$keys[$s]], $file, '_f');
								}

								$content .= '</div>';
								$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]), $content);
							}
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
						$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]),$frm->radio_button($fields['fd_id'][$i],$radio,$item[$fields['fd_id'][$i]]));
						break;
					case 'checkbox':
						$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]),$frm->checkbox($fields['fd_id'][$i],1,$fields['fd_checkbox'][$i],($item[$fields['fd_id'][$i]] ? 'checked' : '')));
						break;
					case 'select':
						$pairs = explode("\r\n",$fields['fd_select'][$i]);
						$pcount = sizeof($pairs);
						for($s=0; $s<$pcount; $s++)
						{
							$pair = explode('-',$pairs[$s],2);
							$select[trim($pair[0])] = trim($pair[1]);
						}
						$frm->addrow((isset($substitutions['subst'][$fields['fd_id'][$i]]) ? $substitutions['subst'][$fields['fd_id'][$i]] : $fields['fd_name'][$i]),$frm->select_tag($fields['fd_id'][$i],$select,$item[$fields['fd_id'][$i]]));
						$select = '';
						break;
				}
			}
		}
		error_reporting($old_er);

		// Custom infoBlock fields end


		$frm->addbreak(__('Options'));
		$frm->addrow(__('Display options'), $frm->radio_button('hidenhigh',array('0' => __('Hidden'), '1' => __('General'), '2' => __('Highlight')),$item['hidenhigh']),'top');
		if($system->ibconfig['interface']['editor_mode']=='default')
		{
			$frm->addrow(__('Mode'), $frm->radio_button('mode', array('html' => __('HTML'), 'text' => __('Text'), 'htmlbb' => __('bbCodes') . '+' . __('HTML')), $item['mode']), 'top');
		}
		else
		{
			$frm->addrow(__('Mode'), $frm->radio_button('mode', array('html' => __('HTML'), 'text' => __('Text'), 'htmlbb' => __('bbCodes') . '+' . __('HTML')), 'html'), 'top');
		}
		$frm->addrow(__('Allow comments'), $frm->radio_button('comments', array('1' => __('Allow'), '0' => __('Disallow')), $item['comments']), 'top');
	}
	else
	{
		$frm->addmessage(__('Item with id #').$id.' '.__('is not exist'));
	}
	$frm->show();
	exit;
}
else if(!empty($_REQUEST['catid']))
{
	// items list
	$frm = new InputForm ('', 'post', '&lt;&lt;&lt; ' . __('Back'));
	$contid=vf($_REQUEST['contid'],4);
	$ibid=vf($_REQUEST['ibid'],4);
	$catid = vf($_REQUEST['catid'],3);
	$frm->hidden('contid', $contid);
	$frm->hidden('ibid', $ibid);

	/*
	var_dump($ibid);
	var_dump($system->checkForRight('IBLOCKS-EDITOR'));
	var_dump($system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR'));
	var_dump($system->checkForRight('IBLOCKS-POSTER'));
	var_dump($system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER'));
	//*/

	if($system->checkForRight('IBLOCKS-EDITOR') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-EDITOR'))
	{
		$editor = true;
	}
	else
	{
		$editor = false;
		if(!($system->checkForRight('IBLOCKS-POSTER') && $system->checkForRight('IBLOCKS-'.strtoupper($ibid).'-POSTER')))
		{
			$frm->addmessage(__('You do not have rights to edit items in these iblock'));
			$frm->show();
			exit;
		}
	}

	if(isset($_REQUEST['next']) && $_REQUEST['next'])
	{
		$next=vf($_REQUEST['next'],3);
		$frm->hidden('catid', $catid);
		$frm->hidden('next',($next-$system->config['adm_perpage']));
	}
	$frm->show();

	$frm = new InputForm('admin.php?show=module&id=iblocks.post','post',__('Post item'));
	$frm->hidden('contid', $contid);
	$frm->hidden('ibid', $ibid);
	$frm->show();

	$iblockitem->BeginiBlockItemsListRead($catid,array('id','title','idate','uid'),(isset($next) ? array($next, $system->config['adm_perpage']) : $system->config['adm_perpage']),'idate','DESC',($editor ? '' : 'uid=\''.$system->user['username'].'\''));

	$frm = new InputForm('','post',__('Submit'),__('Reset'));
	$frm->hidden('catid',$catid);
	$frm->hidden('contid',$contid);
	$frm->hidden('ibid',$ibid);
	$i=0;

	$langs = $system->data['languages'];
	unset($langs[$system->config['default_lang']]);
	$li = sizeof($langs);
	$lkeys = array_keys($langs);
	$langs_string = '';
	for($s = 0; $s < $li; $s++)
	{
		$langs_string .= ' | <a href="admin.php?show=module&id=iblocks.items&tlang='.$lkeys[$s].'&edit={id}">'.strtoupper($lkeys[$s]).'</a>';
	}

	while ($item = $iblockitem->Read())
	{
		$c_langs_string = str_replace('{id}', $item['id'], $langs_string);
		$frm->addrow('#'.$item['id'].' | '.date("d.m.Y H:i", strtotime($item['idate'])).$c_langs_string.' | <b>'.$item['title'].'</b> [<a href="index.php?module=user.list&user='.$item['uid'].'">'.$item['uid'].'</a>]', '&nbsp;<a href="admin.php?show=module&id=iblocks.items&edit='.$item['id'].'">'.__('Edit').'</a> '.$frm->checkbox('delete[' . $item['id'] . ']', '1', '<a href="admin.php?show=module&id=iblocks.items&ibid='.$ibid.'&contid='.$contid.'&catid='.$catid.'&delete='.$item['id'].'">'.__('Delete').'</a>').' '.$frm->checkbox('refresh[' . $item['id'] . ']', '1', __('Refresh')));
		$i++;
	}
	if($i>=$system->config['adm_perpage']-1)
	{
		$frm->addmessage(((isset($next) && $next!=0) ? '<a class="newslink" href="admin.php?show=module&id=iblocks.items&ibid='.$ibid.'&contid='.$contid.'&catid='.$catid.'&next='.($next-$system->config['adm_perpage']).'"><< '.__('previous').'</a> | ' : '').'<a class="newslink" href="admin.php?show=module&id=iblocks.items&ibid='.$ibid.'&contid='.$contid.'&catid='.$catid.'&next='.(isset($next) ? $next+$system->config['adm_perpage'] : $system->config['adm_perpage']).'">'.__('next').' >></a>');
	}
	$frm->show();
	exit;
}

$ibgid = (isset($_POST['ibgid']) ? vf($_POST['ibgid'],4) : '');
$ibid = (isset($_REQUEST['ibid']) ? vf($_REQUEST['ibid'],4) : '');
$contid = (isset($_REQUEST['contid']) ? vf($_REQUEST['contid'],4) : '');

// iblock selection
/* old
$iblock->BeginiBlocksListRead();
while($infoblock=$iblock->Read())
{
$infoblocks[$infoblock['ibid']]=$infoblock['title'];
}
//*/
$frm=new InputForm ('', 'post', __('Submit'));
if(!empty($system->ibgroups)) // $infoblocks -> $system->iblocks
{
	$ibgroups = $system->ibgroups;
	$ibgroups['__NONE__'] = __('Show all iblocks');
	array_multisort($ibgroups);
	$frm->addrow(__('Select iblock group'), $frm->select_tag('ibgid', $ibgroups, $ibgid), 'top');
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
	}
	else
	{
		$frm->addmessage(__('No infoblocks'));
	}
}

if(!empty($ibid))
{
	// containers list
	/*
	$iblock->BeginContainersListRead($ibid);
	while($container=$iblock->Read())
	{
	$containers[$container['contid']]=$container['title'];
	}
	//*/
	$frm->hidden('ibid',$ibid);
	$containers = ib_filter_plarrays($system->containers, 'ibid', $ibid);
	if(!empty($containers))
	{
		$frm->addrow(__('Select container'), $frm->select_tag('contid', $containers, $contid), 'top');
	}
	else
	{
		$frm->addmessage(__('No containers'));
	}
}

if(!empty($contid))
{
	// categories list
	$iblock->BeginCategoriesListRead($contid);
	while($category=$iblock->Read())
	{
		$clcategories[$category['catid']]=$category['title'];
	}

	$frm->hidden('ibid',$ibid);
	$frm->hidden('contid',$contid);
	if(!empty($clcategories))
	{
		$frm->addrow(__('Select category'), $frm->select_tag('catid', $clcategories).' '.$frm->checkbox('refreshcat','1',__('Refresh category')), 'top');
	}
	else
	{
		$frm->addmessage(__('No categories in container'));
	}
}
$frm->show();

$frm = new InputForm('','post',__('Submit'));
$frm->addbreak(__('Item fast edit'));
$frm->addrow(__('Item id'), $frm->text_box('edit',''));
$frm->show();
?>