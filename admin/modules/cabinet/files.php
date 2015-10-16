<?php
// //////////////////////////////////////////////////////////////////////////////
// Copyright (C) Hahhah~CMS Development Team //
// http://hakkahcms.sourceforge.net //
// //
// This program is distributed in the hope that it will be useful, //
// but WITHOUT ANY WARRANTY, without even the implied warranty of //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. //
// //
// This product released under GNU General Public License v2 //
// //////////////////////////////////////////////////////////////////////////////

$iblock = new iBlock ();
$iblockitem = new iBlockItem ();
$imail = new iMail();
$name = $system->user['username'];
$now_date = date ( "d.m.y", time () );

///получаем id текущего пользователя
$imail->setWorkTable ( $imail->prefix . 'users' );
$imail->setId ( $name, 'username' );
$imail->BeginDataRead ( array('uid') );//id of current user
$uid = $imail->GetLastResultId ();
while ( $row = $imail->Read ( $uid ) )
    $uid_current = $row['uid'];

$ibid = 'exchange_docs';

if (! empty ( $_POST ['save'] ) && empty($_POST['btn_letters']) && empty($_POST['btn_contacts']) && empty($_POST['btn_files']) ) {
	// save item
	$ibid = 'exchange_docs';
	
	$contid = 'exchange_docs';
	$catid = 1;
	
	$infoblock = $system->iblocks [$ibid];
	$fields = unpack_data ( $infoblock ['fields'] );
	$iblockext = unpack_data ( $system->iblocks [$ibid] ['extopt'] );
	$iblockitem = new iBlockItem ();
	$id = $iblockitem->GetTableAINextValue ();
	
	$item = ib_checkpost ( $fields, $id, $ibid );
	if (isset ( $item ['indexed'] )) {
		$indexed = $item ['indexed'];
		unset ( $item ['indexed'] );
	}
	
	$no_post = false;
	if (empty ( $item ['title'] ) ) 
    {
		rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( 'Заполните заголовок сообщения' ) );
	}
    else if (empty ( $_POST['message'])) 
    {
		rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( 'Заполните поле сообщения' ) );
	} 
    else {
		$item ['ibid'] = $ibid;
		$item ['contid'] = $contid;
		$item ['catid'] = $catid;
		$item ['id'] = $id;
		$item ['uid'] = $system->user ['username'];
		$item ['idata'] = vf ( pack_data ( $item ) );
		
		$posted_data = array (null, $item ['catid'], $item ['contid'], $item ['ibid'], null, null, /*$item ['idate']*/date ( "Y-m-d H:i:s",time()), $item ['title'], $item ['description'], $item ['idata'], $item ['source'], $item ['uid'], $item ['tags'], $item ['hidenhigh'] );
		
		// indexed data
		for($i = 1; $i <= 5; $i ++) {
			if (! empty ( $indexed ['index' . $i] )) {
				array_push ( $posted_data, $indexed ['index' . $i] );
			} else {
				array_push ( $posted_data, null );
			}
		}
		// end
		
		if ($iblockitem->addData ( $posted_data )) 
        {
		      if (!empty ($_POST['list_users']) && !empty($_POST['message']))
              {                    
                    $imail->setWorkTable($imail->prefix.'users');
                    //$users = iconv('utf-8','cp1251',$_POST['list_users']);
                    $users = $_POST['list_users'];
                    if ($users != 'all')
                    {
                        $imail->setId ( $users, 'uid' );
                        $imail->iMailBeginRead (array('uid'));
                        while ( $row_id = $imail->Read ( $imail->GetLastResultId () ) )
                        {
                            $user_id = $row_id['uid'];
                        }   
                    }
                    else
                    {
                        $user_id = '';
                        $imail->setId ( $uid_current, 'uid' );
                        $imail->iMailBeginRead (array('uid'),'!=');
                        $uid_to = '';
                        while ( $row_id = $imail->Read ( $imail->GetLastResultId () ) )
                        {
                            $uid_to .= $row_id['uid'];
                        }
                  		for($i = 0; $i < mb_strlen ( $uid_to ); $i ++) 
                        {
			                 if ($i != 0)
				                $user_id .= ',';
			                 $user_id .= $uid_to [$i];
                        }   
                    }                    
                    //$mes = iconv('utf-8','cp1251',$_POST['message']);
                    $mes = $_POST['message'];
                    
                    $imail->setWorkTable($imail->prefix.'imail');
                    $imail->addData(array(null,$uid_current,$user_id,null,time(),pack_data($mes),0,0,0)); 
                    rcms_showAdminMessage ( __ ( 'Сообщение успешно отправлено' ));  
              }                
		}
	    else 
        {
			rcms_showAdminMessage ( __ ( 'Error' ) );
		}
	}
}

if (! empty ( $ibid )) {
	if (! ($system->checkForRight ( 'IBLOCKS-' . mb_strtoupper ( $ibid ) . '-POSTER' ) || $system->checkForRight ( 'IBLOCKS-' . mb_strtoupper ( $ibid ) . '-EDITOR' ))) {
		rcms_showAdminMessage ( __ ( 'You do not have rights to post items in these iblock' ) );
		exit ();
	}
	$containers = ib_filter_plarrays ( $system->containers, 'ibid', $ibid );
	if (sizeof ( $containers ) == 1) {
		$key = array_keys ( $containers );
		$_POST ['contid'] = $key [0];
	}
}

if (! empty ( $_POST ['contid'] )) {
	// form
	$contid = 'exchange_docs';
	$ibid = 'exchange_docs';
	if (! ($system->checkForRight ( 'IBLOCKS-' . mb_strtoupper ( $ibid ) . '-POSTER' ) || $system->checkForRight ( 'IBLOCKS-' . mb_strtoupper ( $ibid ) . '-EDITOR' ))) {
		rcms_showAdminMessage ( __ ( 'You do not have rights to post items in these iblock' ) );
		exit ();
	}
	$iblock->BeginCategoriesListRead ( $contid, array ('catid', 'title' ) );
	while ( $clcategory = $iblock->Read () ) {
		$ibcategories [$clcategory ['catid']] = $clcategory ['title'];
	}
	
	$iblockitem = new iBlockItem ();
	$id = $iblockitem->GetTableAINextValue ();
	
	$iblockext = unpack_data ( $system->iblocks [$ibid] ['extopt'] );
	$enable_wms = @$system->config ['enable_wms'] && ! empty ( $iblockext ['enable_wms'] );
	$asyncmgr = new AsyncMgr ();
	$ie = false;
	if (mb_strstr ( getenv ( "HTTP_USER_AGENT" ), "MSIE" )) {
		$ie = true;
		$asyncmgr->printImgUpFormJS ( array ('iblockfiles', $ibid, date ( "Y" ), date ( "M" ), $id ), 'image', false, true, array ('load_stopped_3.gif', 'load_process_3.gif' ), 120, false, $enable_wms );
	} else {
		$asyncmgr->printImgUpFormJS ( array ('iblockimages', $ibid, date ( "Y" ), date ( "M" ), $id ), 'image', true, false, array ('load_stopped.gif', 'load_process.gif' ), 120, false, $enable_wms );
		$asyncmgr->printFileUpFormJs ( array ('iblockfiles', $ibid, date ( "Y" ), date ( "M" ), $id ), 'file', true, false, array ('load_stopped_3.gif', 'load_process_3.gif' ), $enable_wms );
	}
	
	print ('<script>
	$(document).ready(function()
	{
		$("#imgpad").slideDown("slow");
		$("#imgpad2").slideDown("slow");
		
		$("input#hidetextbutton").click(function()
		{
			if(this.value==\'' . __ ( 'Show text' ) . '\')
			{
				this.value = "' . __ ( 'Hide text' ) . '";
			}
			else
			{
				this.value = "' . __ ( 'Show text' ) . '";				
			}
			$("#imgpad2").slideToggle("fast");
			$("#textbbpanel").slideToggle("fast");			
			$("#attfiles").slideToggle("fast");
			$("#itext_table").slideToggle("fast");			
		});
	});') ;
	
	if ($system->ibconfig ['interface'] ['editor_mode'] == 'default') {
		print ('function addImage(textareaId, linkId)
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
	}') ;
	} else {
		print ('function addImage(textareaId, linkId)
	{
		tinyMCE.activeEditor.selection.setContent(\'<img src="\' + $("#pasteimglink" + linkId).attr("href") + \'">\');
		
		return false;
	}') ;
	}
	
	print ('
	function showhidero(imgoptdivId, iconId, linkId)
	{
		$("#" + imgoptdivId).slideToggle("slow");
		if($("#" + iconId).attr("src") == "admin/show.png")
		{
			$("#" + iconId).attr("src","admin/hide.png");
			$("#" + linkId).attr("title","' . __ ( 'Hide options' ) . '");
		}
		else
		{
			$("#" + iconId).attr("src","admin/show.png");
			$("#" + linkId).attr("title","' . __ ( 'Show options' ) . '");
		}
	}
	</script>') ;
	/////////////////////////////////////////////////////////////////////////////////////////////////
    ////////select users//////////    
    $imail->setWorkTable ( $imail->prefix . 'users' );
    $imail->setId ( $uid_current, 'uid' );
    $imail->iMailBeginRead(  array('uid','username'), '!=' );//id of current user
    $k=0;
    while ($list = $imail->Read($imail->GetLastResultId ()))
    {
      $user_list[$list['uid']] = $list['username'];
      $k++;          
    }
    $k++;
    $user_list['all'] = 'Всем';     
    
    $frm = new InputForm ( '', 'post', __ ( 'Submit' ), '', '', 'multipart/form-data', 'adm_itemadd' );

	$frm->addbreak ( __ ( 'Отправка сообщений' ) );
	$frm->hidden ( 'save', '1' );
	$frm->hidden ( 'new', '1' );
	$frm->hidden ( 'contid', $contid );
	$frm->hidden ( 'ibid', $ibid );
	//$frm->addrow ( __ ( 'Select category' ), $frm->select_tag ( 'catid', $ibcategories ), 'top' );
	if (!empty($_POST['message1']))
        $mess = $_POST['message1'];
        else
            $mess = '';
            
    if (!empty($_POST['title1']))
        $title = $_POST['title1'];
        else
            $title = '';
            
    if (!empty($_POST['list_users1']))
        $cur_user = $_POST['list_users1'];
        else
            $cur_user = '';            
    
    
    $frm->addrow(__("Who"), $frm->select_tag('list_users',$user_list,$cur_user,'id="adm_list_users"'));
    $frm->addrow ( __ ( 'Title' ), $frm->text_box ( 'title', $title, 73,'','','id="tb_adm_title"' ).'*поле обязательно для заполнения', 'top' );
    $frm->addrow ( __("Message"), $frm->text_box ( 'message', $mess, 73,'','','id="tb_adm_message"' ).'*поле обязательно для заполнения', 'top' );
	
    print("<script> $(document).ready(function() { ");
    
    if (!empty($_POST['btn_answer'])) //ответить
    {
        print("
            $('#adm_list_users option').each(function()
            {                        
                if ($(this).text() == '".$_POST['hidden_answer']."')
                    $(this).attr('selected', 'selected');
            });                
        ");
    }
    
    if (!empty($_POST['btn_answer_all'])) //ответить всем
    {
        print("
            $('#adm_list_users option:last').attr('selected', 'selected');
        ");      
    }
    
    if (!empty($_POST['hidden_contacts'])) //ответить при выборе пользователя в разделе "контакты"
    {
        print("
            $('#adm_list_users option').each(function()
            {                        
                if ($(this).text() == '".$_POST['hidden_contacts']."')
                    $(this).attr('selected', 'selected');
            });                
        ");
    }
    

    
	if (! isset ( $system->ibconfig ['interface'] ['editor_mode'] )) {
		$system->ibconfig ['interface'] ['editor_mode'] = 'default';
	}
	if ($system->ibconfig ['interface'] ['editor_mode'] == 'default') {
		$frm->addrow ( '', rcms_show_bbcode_panel ( 'adm_itemadd.description' ) );
	} else {
		// TinyMCE
		$frm->addsingle ( file_get_contents ( JSS_PATH . 'tiny_mce/simple.html' ) );
	}
	//$frm->addrow ( (! empty ( $iblockext ['rnm_desc'] ) ? $iblockext ['rnm_desc'] : __ ( 'Description' )), '<table><tr><td>' . $frm->textarea ( 'description', '', 70, 12, 'id="description"' ) . '</td>
	//<td>' . ($ie ? '' : '<div title="' . __ ( 'Image upload panel' ) . '" id="imgpad" style="overflow: auto; display: none; background-color: #ffffff; border: 1px solid 1779DD; width: 200px; height: ' . ($system->ibconfig ['interface'] ['editor_mode'] == 'default' ? '154px' : '187px') . '; padding: 2px">' . $frm->file ( 'upFileField', 'id="upFileField" style="width: 180px;"' ) . '<br>
	//<div id="imagesopt" style="margin: 2px; display:none;">' . __ ( 'Resize' ) . ': ' . $frm->text_box ( 'image_rwidth', '', 4, 0, false, 'id="image_rwidth"' ) . ' x ' . $frm->text_box ( 'image_rheight', 'auto', 4, 0, false, 'id="image_rheight"' ) . ($enable_wms ? __ ( 'Watermark' ) . ' ' . $frm->select_tag ( 'image_wmpos', array ('left_top' => __ ( 'Left top' ), 'left_middle' => __ ( 'Left middle' ), 'left_bottom' => __ ( 'Left bottom' ), 'center_top' => __ ( 'Center top' ), 'center_middle' => __ ( 'Center middle' ), 'center_bottom' => __ ( 'Center bottom' ), 'right_top' => __ ( 'Right top' ), 'right_middle' => __ ( 'Right middle' ), 'right_bottom' => __ ( 'Right bottom' ) ), 'center_middle', 'id="image_wmpos"' ) : '') . '</div>' . '<a id="optlink" title="' . __ ( 'Show options' ) . '" href="#" onclick="showhidero(\'imagesopt\',\'sh_image\', this.id);"><img style="margin-bottom: -4px; margin-right: 2px;" src="admin/show.png" id="sh_image"></a>' . $frm->button ( __ ( 'Upload' ), 'id="uplButton" onclick="return ajaxFileUpload();" style="width: 140px"' ) . '<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped.gif" id="loadicon">
	//<div id="intimages" style="text-align: center;"></div>
	//</div>') . '</td></tr></table>' );
	if (! @$iblockext ['hidefields']) {
		$frm->hidden ( 'idate', date ( "Y-m-d H:i:s" ) );
		$frm->addrow ( '<center><input type="button" id="hidetextbutton" value="' . __ ( 'Hide text' ) . '"></center>', ($system->ibconfig ['interface'] ['editor_mode'] == 'default' ? '<div id="textbbpanel">' . rcms_show_bbcode_panel ( 'adm_itemadd.text' ) . '</div>' : '') );
		$frm->addrow ( __ ( 'Document text' ), '<table id="itext_table"><tr><td>' . $frm->textarea ( 'itext', '', 70, 40, 'id="text"' ) . '</td>
	<td>' . ($ie ? '' : '<div title="' . __ ( 'Image upload panel' ) . '" id="imgpad2" style="overflow: auto; display: none; background-color: #ffffff; border: 1px solid 1779DD; width: 200px; height: ' . ($system->ibconfig ['interface'] ['editor_mode'] == 'default' ? '598px' : '607px') . '; padding: 2px">
	' . $frm->file ( 'upFileField2', 'id="upFileField2" style="width: 180px;"' ) . '<br>
	<div id="imagesopt2" style="margin: 2px; display:none;">' . __ ( 'Resize' ) . ': ' . $frm->text_box ( 'image2_rwidth', '', 4, 0, false, 'id="image2_rwidth"' ) . ' x ' . $frm->text_box ( 'image2_rheight', 'auto', 4, 0, false, 'id="image2_rheight"' ) . ($enable_wms ? __ ( 'Watermark' ) . ' ' . $frm->select_tag ( 'image2_wmpos', array ('left_top' => __ ( 'Left top' ), 'left_middle' => __ ( 'Left middle' ), 'left_bottom' => __ ( 'Left bottom' ), 'center_top' => __ ( 'Center top' ), 'center_middle' => __ ( 'Center middle' ), 'center_bottom' => __ ( 'Center bottom' ), 'right_top' => __ ( 'Right top' ), 'right_middle' => __ ( 'Right middle' ), 'right_bottom' => __ ( 'Right bottom' ) ), 'center_middle', 'id="image2_wmpos"' ) : '') . '</div>' . '<a id="optlink2" title="' . __ ( 'Show options' ) . '" href="#" onclick="showhidero(\'imagesopt2\',\'sh_image2\', this.id);"><img style="margin-bottom: -4px; margin-right: 2px;" src="admin/show.png" id="sh_image2"></a>' . $frm->button ( __ ( 'Upload' ), 'id="uplButton2" onclick="return ajaxFileUpload(\'upFileField2\',\'intimages2\',\'loadicon2\',\'uplButton2\',\'text\',\'image2\',\'ibimage\');" style="width: 140px"' ) . '<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped.gif" id="loadicon2">
	<div id="intimages2" style="text-align: center;"></div>
	</div>') . '</td></tr></table>' );
		$frm->addrow ( __ ( 'Attach files' ), '<div id="attfiles">' . $frm->file ( 'upFileField_f', 'id="upFileField_f"' ) . ' ' . $frm->button ( __ ( 'Upload' ), 'id="uplButton_f" onclick="return ajaxFileUpload_f();" style="width: 140px;"' ) . '<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_f">
	<div id="intfiles"></div></div>' );
	}
	
    
    if (!empty($_POST['btn_resend'])) //переслать
    {
        print("
            $('#tb_adm_title').val('".$_POST['hidden_resend_title']."');
            $('#tb_adm_message').val('".$_POST['hidden_resend_message']."');            
        ");
        
        if (!empty($_POST['hidden_resend_document']))
        {
            $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
            $iblockitem->setId(date( "Y-m-d H:i:s", $_POST['hidden_resend_document']),'idate');
            $iblockitem->BeginDataRead();                        
            while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId()))
            {
                $idata = array_merge ( unpack_data ( $item_data ['idata'] ), $item_data );
                $text = $idata['itext'];                                  
            }
            
            print("                              
                setTimeout(function()
                {
                    $('#text_ifr').contents().find('p').html('".$text."');    
                },500);                        
            ");                                           
        }               
    } 
    
    print("}) </script>");
	// Custom infoBlock fields
	
	$infoblock = $system->iblocks [$ibid]; // pre-load usage
	$fields = unpack_data ( $infoblock ['fields'] );
	$container = $system->containers [$contid];
	if (isset ( $container ['substitutions'] )) {
		$substitutions = unpack_data ( $container ['substitutions'] );
	}
	
	// var_dump($fields);
	$count = sizeof ( $fields ['fd_id'] );
	if ($count) {
		$frm->addbreak ( __ ( 'Custom iBlock fields' ) );
		for($i = 0; $i < $count; $i ++) {
			if (isset ( $substitutions ['drop'] [$fields ['fd_id'] [$i]] )) {
				continue;
			}
			
			switch ($fields ['fd_type'] [$i]) {
				case 'text' :
					if (! isset ( $fields ['fd_text'] [$i] )) {
						$fields ['fd_text'] [$i] = '';
					}
					switch ($fields ['fd_text'] [$i]) {
						case 'date' :
							$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->text_box ( $fields ['fd_id'] [$i], date ( "d.m.Y H:i" ) ) . ' ' . __ ( 'Please, fill this field according to this format' ) . ': <b>' . __ ( 'YYYY-MM-DD HH:MM' ) . '</b>' );
							break;
						case 'number' :
							$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->num_box ( $fields ['fd_id'] [$i], '', 20 ) );
							break;
						case 'text' :
						default :
							$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->text_box ( $fields ['fd_id'] [$i], '' ) );
							break;
					}
					break;
				case 'textarea' :
					$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->textarea ( $fields ['fd_id'] [$i], '', 70, $fields ['fd_textarea'] [$i] ) );
					break;
				case 'file' :
					if ($fields ['fd_fileisimage'] [$i]) {
						if ($ie) {
							$asyncmgr->addAddPart ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm, 'image', $fields ['fd_id'] [$i], 'load_stopped_3.gif' );
						} else {
							$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->file ( $fields ['fd_id'] [$i], 'id="' . $fields ['fd_id'] [$i] . '"' ) . ' ' . $frm->button ( __ ( 'Upload' ), 'id="uplButton_' . $fields ['fd_id'] [$i] . '" onclick="return ajaxFileUpload_f(\'' . $fields ['fd_id'] [$i] . '\',\'intimage_' . $fields ['fd_id'] [$i] . '\',\'loadicon_' . $fields ['fd_id'] [$i] . '\',\'uplButton_' . $fields ['fd_id'] [$i] . '\',\'' . $fields ['fd_id'] [$i] . '\',\'galleryimage\', \'' . (isset ( $system->config ['th_width'] ) ? $system->config ['th_width'] : 100) . 'x' . (isset ( $system->config ['th_height'] ) ? $system->config ['th_height'] : 100) . '\');" style="width: 140px"' ) . '<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_' . $fields ['fd_id'] [$i] . '">
	<div id="intimage_' . $fields ['fd_id'] [$i] . '"></div>' );
						}
					} else {
						$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->file ( $fields ['fd_id'] [$i], 'id="' . $fields ['fd_id'] [$i] . '"' ) . ' ' . $frm->button ( __ ( 'Upload' ), 'id="uplButton_' . $fields ['fd_id'] [$i] . '" onclick="return ajaxFileUpload_f(\'' . $fields ['fd_id'] [$i] . '\',\'intfile_' . $fields ['fd_id'] [$i] . '\',\'loadicon_' . $fields ['fd_id'] [$i] . '\',\'uplButton_' . $fields ['fd_id'] [$i] . '\',\'' . $fields ['fd_id'] [$i] . '\');" style="width: 140px;"' ) . '<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_' . $fields ['fd_id'] [$i] . '">
	<div id="intfile_' . $fields ['fd_id'] [$i] . '"></div>' );
					}
					break;
				case 'radio' :
					$pairs = explode ( "\r\n", $fields ['fd_radio'] [$i] );
					$pcount = sizeof ( $pairs );
					for($s = 0; $s < $pcount; $s ++) {
						$pair = explode ( '-', $pairs [$s], 2 );
						$radio [trim ( $pair [0] )] = trim ( $pair [1] );
					}
					$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->radio_button ( $fields ['fd_id'] [$i], $radio ) );
					break;
				case 'checkbox' :
					$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->checkbox ( $fields ['fd_id'] [$i], 1, $fields ['fd_checkbox'] [$i] ) );
					break;
				case 'select' :
					$pairs = explode ( "\r\n", $fields ['fd_select'] [$i] );
					$pcount = sizeof ( $pairs );
					// var_dump($pairs);
					for($s = 0; $s < $pcount; $s ++) {
						$pair = explode ( '-', $pairs [$s], 2 );
						$select [trim ( $pair [0] )] = trim ( $pair [1] );
					}
					$frm->addrow ( (isset ( $substitutions ['subst'] [$fields ['fd_id'] [$i]] ) ? $substitutions ['subst'] [$fields ['fd_id'] [$i]] : $fields ['fd_name'] [$i]), $frm->select_tag ( $fields ['fd_id'] [$i], $select ) );
					$select = '';
					break;
			}
		}
	}
	
	// Custom infoBlock fields end
	
//	$frm->addbreak ( __ ( 'Options' ) );
	$frm->hidden ( 'hidenhigh', '' );
    $frm->hidden ( 'mode', 'html' );
    $frm->hidden ( 'comments', '' );            
    
    $frm->addrow('',$frm->button('К сайту','id="but_to_site" style="display:none"').'&nbsp'.$frm->button('К панели администрирования','id="but_to_admin" style="display:none"'));
    if (isset($_POST['message1']) || isset($_POST['message']))
        print('<script>
        $(document).ready(function()
        {
            $("#but_to_site").click(function()
            {
               //window.location.href = "./index.php";
               window.open("./index.php"); 
            });
            $("#but_to_admin").click(function()
            {
               //window.location.href = "./admin.php";
               window.open("./admin.php"); 
            });
            $("#but_to_site").show();
            $("#but_to_admin").show();
        });
        </script>');
            
    $frm->addrow('','<form action="" type="submit" method="post" enctype="multipart/form-data" name="frm_click_letters"><input type="submit" value="Письма" name="btn_letters" /><input type="submit" value="Контакты" name="btn_contacts" /><input type="submit" value="Файлы" name="btn_files" /></form>');    
    
    $letters = array('input','output','blacklists','basket');
    
    for ($j=0;$j<sizeof($letters);$j++)
    {
        $imail->setWorkTable ( $imail->prefix . 'imail' );
        $imail->setId ( '', '' );
        $all_messages = $imail->GetRowCount();  //всего сообщений      
        
        $imail->setWorkTable ( $imail->prefix . 'imail' );
        $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
        
        if (!empty($_POST['btn_delete_'.$letters[$j].''])) //удаление сообщений
        {
            $message_time = array();                                
                
            for ($i=0;$i<$all_messages;$i++)
            {         
                $message_time[$i] = $_POST['hidden_'.$letters[$j].''][$i];
                if ($message_time[$i]!='')
                {
                    $imail->dropData('date_time', $message_time[$i]);
                    $iblockitem->dropData('idate', date( "Y-m-d H:i:s", $message_time[$i]));
                    //hcms_debug($_POST['hidden_input'][$i]);    
                }                                                                  
            }         
        }
        
        if (!empty($_POST['btn_read_'.$letters[$j].''])) //прочитано
        {
            $read = array();
            
            for ($i=0;$i<$all_messages;$i++)
            {         
                $read[$i] = $_POST['hidden_'.$letters[$j].''][$i];
                if ($read[$i]!='')
                {
                    $imail->setId($read[$i],'date_time');
                    $imail->editData(array('read'=>1));   
                }                                        
            }
        }
        
        if (!empty($_POST['mess_'.$letters[$j].'_click'])) //прочитано при нажатии на сообщение
        {
            $read = $_POST['mess_'.$letters[$j].'_click'];
            if ($read!='')
            {
                $imail->setId($read,'date_time');
                $imail->editData(array('read'=>1));   
            }                                                    
        }
        
        if (!empty($_POST['btn_unread_'.$letters[$j].''])) //не прочитано
        {
            $unread = array();
            
            for ($i=0;$i<$all_messages;$i++)
            {         
                $unread[$i] = $_POST['hidden_'.$letters[$j].''][$i];
                if ($unread[$i]!='')
                {
                    $imail->setId($unread[$i],'date_time');
                    $imail->editData(array('read'=>0));   
                }                                        
            }
        }
        
        if (!empty($_POST['btn_blacklists_'.$letters[$j].''])) //в черновики
        {
            $moveto_blacklists = array();
            
            for ($i=0;$i<$all_messages;$i++)
            {         
                $moveto_blacklists[$i] = $_POST['hidden_'.$letters[$j].''][$i];
                if ($moveto_blacklists[$i]!='')
                {
                    $imail->setId($moveto_blacklists[$i],'date_time');
                    $imail->editData(array('blacklists'=>1, 'basket'=>0));    
                }                                        
            }
        }
        
        if (!empty($_POST['btn_basket_'.$letters[$j].''])) //в корзину
        {
            $moveto_basket = array();
            
            for ($i=0;$i<$all_messages;$i++)
            {         
                $moveto_basket[$i] = $_POST['hidden_'.$letters[$j].''][$i];
                if ($moveto_basket[$i]!='')
                {
                    $imail->setId($moveto_basket[$i],'date_time');
                    $imail->editData(array('blacklists'=>0, 'basket'=>1));    
                }                                        
            }
        }
        
        if (!empty($_POST['btn_from_'.$letters[$j].''])) //убрать из черновиков/из корзины
        {
            $movefrom = array();
            
            for ($i=0;$i<$all_messages;$i++)
            {         
                $movefrom[$i] = $_POST['hidden_'.$letters[$j].''][$i];
                if ($movefrom[$i]!='')
                {
                    $imail->setId($movefrom[$i],'date_time');
                    $imail->editData(array($letters[$j]=>0));   
                }                                        
            }
        }        
           
    }    
    
    
    if  (!empty($_POST['btn_resend']) || !empty($_POST['btn_answer_all']) || !empty($_POST['hidden_contacts']) || !empty($_POST['mode']))  //отобразаить форму отправки сообщения
    {
      //  $frm->show ();   
    }

    else if (empty($_POST['btn_files'])) //файлы
    {
        $frm = new InputForm ( '', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_files' );        
        $frm->addbreak('Список прикрепленных файлов');
        
        $imail->setWorkTable ( $imail->prefix . 'imail' );
        $imail->setId ( '', '' );
        $imail->iMailBeginRead('*', '=', '', ' ORDER BY date_time','asc');
        $resultid = $imail->GetLastResultId ();
        $count_records = 0;
        while ( $to = $imail->Read ( $resultid ) ) 
        {
            $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
            $iblockitem->setId(date( "Y-m-d H:i:s", $to['date_time']),'idate');
            $iblockitem->BeginDataRead();                        
            while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId()))
            {
                if (mb_substr ( date ( "d.m.y", $to ['date_time'] ), 0, 8 ) == $now_date)                
                    $date_of_letter = date ( __('Today').", H:i", $to ['date_time'] );                                     
                    else
                        $date_of_letter = date ( "d.m.y H:i", $to ['date_time'] );
                
                $imail->setWorkTable ( $imail->prefix . 'users' );
                $imail->setId ( $to['uid_from'], 'uid' );
                $imail->iMailBeginRead (array('uid', 'username'));
                
                $arr_to = explode(',',$to['uid_to']);  //input messages
                $u_to = '';
                while ( $user = $imail->Read ( $imail->GetLastResultId () ) ){                
                    for ($i=0;$i<sizeof($arr_to);$i++)
                    {
                        // if ($arr_to[$i] == $uid_current)
                        // {
                        if ($name == $user['username']){                                
                            $imail->setId ( $arr_to[$i], 'uid' );
                            $imail->iMailBeginRead (array('uid', 'username'));
                            while ( $u = $imail->Read ( $imail->GetLastResultId () ) )
                                $u_to .= $u['username'].'&nbsp;&nbsp;&nbsp;';                        
                        }
                        else
                            $u_to = $name;
                        // }
                    }
                }
                
                $imail->setWorkTable ( $imail->prefix . 'users' );
                $imail->setId ( $to['uid_from'], 'uid' );
                $imail->iMailBeginRead (array('uid', 'username'));
               
                while ( $user = $imail->Read ( $imail->GetLastResultId () ) )
                {
                    $title = $item_data['title'];
                    $idata = array_merge ( unpack_data ( $item_data ['idata'] ), $item_data );
                    $text = $idata['itext'];
                    $file = $idata['file'];
                    
                    $content = '';        
                    if (!empty($file)){                    
                        $count_records++;
                        $filefield = $file;
                        $keys = array_keys($filefield);
                        $fcount = sizeof($filefield);
                        for($s=0; $s<$fcount; $s++){
                            $content .= '<a href="download.php?att_file='.$filefield[$keys[$s]].'" style="font-weight: bold;" title="'.__('Download!').'">'.
                            basename($filefield[$keys[$s]]).'</a><br>
                            - '.__('Size').': '.hcms_filesize($filefield[$keys[$s]]).'<br><br>';
                            //- '.__('Type').': '.mime_content_type($filefield[$keys[$s]]);                                    
                        }
                        $frm->addrow(/*__('Attached files').*/
                        '<p>От кого: <b>'.$user['username'].'</b></p>'.
                        '<p>Кому: <b>'.$u_to.'</b></p>'.
                        '<p>Дата: <b>'.$date_of_letter.'</b></p>', $content, '', '', '', '25%,60%');
                    
                        //$frm->addsingle('<tr class="row1"><td><input type="submit" value="Ответить" name="btn_answer" /><input type="submit" value="Ответить всем" name="btn_answer_all" /><input type="submit" value="Переслать" name="btn_resend" /></td><td></td></tr>');
                        //$frm->addsingle('<tr class="row1"></tr>');
                        $frm->hidden('hidden_answer', $user['username']);
                        $frm->hidden('hidden_resend_title', $title);
                        $frm->hidden('hidden_resend_message', unpack_data ( $to ['message'] ));                                                                                                                    
                        $frm->hidden('user_id',  $to ['uid_to'] );
                        $frm->hidden('item_id',  $item_data ['id'] );
                    }
                }                                 
            }
        }
        if ($count_records == 0){
            $frm->addsingle('<tr class="row1"><td>Нет прикрепленных файлов</td></tr>');
        }
                
        $frm->show(false, false);      
    }
    
        
    print('<script type="text/javascript">
        $(document).ready(function()
        {
            $(".link_contacts").click(function()
            {
                $("input[name=hidden_contacts]").val($(this).find("span").html());
                $("form[name=frm_contacts]").submit();
                return false;
            });            
        })
        </script>');

    ///////////////////////////////////////////////////////////////////////////////        
    
	//exit ();
}
  
?>