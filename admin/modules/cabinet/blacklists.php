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
error_reporting (55);
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
$contid = 'exchange_docs';
$catid = 1;
	
$infoblock = $system->iblocks [$ibid];
$fields = unpack_data ( $infoblock ['fields'] );
$iblockext = unpack_data ( $system->iblocks [$ibid] ['extopt'] );
$iblockitem = new iBlockItem ();
$id = $iblockitem->GetTableAINextValue ();

	// form
	$contid = 'exchange_docs';
	$ibid = 'exchange_docs';

	$iblockitem = new iBlockItem ();
	$id = $iblockitem->GetTableAINextValue ();

	$asyncmgr = new AsyncMgr ();

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
    //show imail messages
    //get user data
    $imail->setWorkTable($imail->prefix.'users');
    $imail->setId($uid_current, 'uid');
    $imail->iMailBeginRead(array('ext'));
    $checked = '';
    while ($userdata = $imail->Read()){
      $u_data = unpack_data($userdata['ext']);
      if (!isset($_POST['mail_show_chat_msgs']))
         ($u_data['mail_show_chat_msgs'] != 0) ? $checked = 1 : $checked = null;
         else
            $_POST['mail_show_chat_msgs']!= 0 ? $checked = 1 : $checked = null;     
    }    
    $mail_show = $u_data['mail_show_chat_msgs'];    
    if (isset($_POST['mail_show_chat_msgs'])){      
      $u_data['mail_show_chat_msgs'] = $_POST['mail_show_chat_msgs']=='1'?'1':'0';      
      $imail->editData(array('ext'=>pack_data($u_data)));         
    }                          
    //////////////////////////////////////////////////////////      
    

    if (empty($_POST['mess_output_click']) && empty($_POST['mess_input_click']) )//отображение писем
    {        
        print('<script type="text/javascript">
            $(document).ready(function(){');
        
        $frm = new InputForm ( '', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_letters' );
        //$frm->addbreak ( __ ( 'Письма' ) );
        $frm->addrow('<b>'.__('Show chat messages').'</b>&nbsp;&nbsp;'.
        $frm->checkbox('','','',$checked,'class="ch_show"'), $frm->hidden('mail_show_chat_msgs', $checked?'1':'0'));
      
        $frm->addsingle('<tr id="tr_blacklists_header"><th colspan="4">'.__('Blacklists').'</th></tr>');
        $frm->addsingle('<tr class="row1"><td><div id="adm_div_blacklists"></div></td></tr>');
        $frm->addsingle('<tr class="row1"><td><div id="div_blacklists_tools" style="display:none;"><input type="submit" value="Удалить" name="btn_delete_blacklists" /><input type="submit" value="Прочитано" name="btn_read_blacklists" /><input type="submit" value="Не прочитано" name="btn_unread_blacklists" /><input type="submit" value="Убрать из черновиков" name="btn_from_blacklists" /><input type="submit" value="В корзину" name="btn_basket_blacklists" /></div></td></tr>');

        $imail->setWorkTable ( $imail->prefix . 'imail' );
        $imail->setId ( 0, 'blacklists' );
        $imail->iMailBeginRead('*', '=', '', ' ORDER BY date_time','asc');
        $resultid = $imail->GetLastResultId ();
        
        $mess_input = array();
        $mess_output = array();
        $count_mes_input = 0; //количество сообщений 
        while ( $to = $imail->Read ( $resultid ) ) 
        {
            $title = '';
            $item = 0; //если = 0, то сообщение из чата, если = 1, то письмо 
            //$msg = iconv('utf-8', 'cp1251', unpack_data($to['message']));
            $msg = str_replace("\n","",unpack_data($to['message']));
            $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
            $iblockitem->setId(date( "Y-m-d H:i:s", $to['date_time']),'idate');
            $iblockitem->BeginDataRead();       
            while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId())){
               $title = $item_data['title'];
               if (($mail_show == 0 && !isset($_POST['mail_show_chat_msgs'])) || $_POST['mail_show_chat_msgs']==0)
                  $item = 1;                  
            }
            
            if (empty($title)){
               if (mb_strlen($msg)<10)
                  $title = $msg;
                  else
                     $title = mb_substr($msg,0,9).'...';
            }                 
         //   while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId()))
          //  {
                if (mb_substr ( date ( "d.m.y", $to ['date_time'] ), 0, 8 ) == $now_date)                
                    $date_of_letter = date ( __('Today').", H:i", $to ['date_time'] );
                    else                
                        $date_of_letter = date ( "d.m.y H:i", $to ['date_time'] );
                                 
                $arr_to = explode(',',$to['uid_to']);  //input messages                
            if ( ($item || (($mail_show == 1 && !isset($_POST['mail_show_chat_msgs'])) || $_POST['mail_show_chat_msgs']==1))){
                for ($i=0;$i<sizeof($arr_to);$i++)
                {
                    if ($arr_to[$i] == $uid_current)
                    {
                        $imail->setWorkTable ( $imail->prefix . 'users' );
                        $imail->setId ( $to['uid_from'], 'uid' );
                        $imail->iMailBeginRead (array('username'));
                        while ( $user = $imail->Read ( $imail->GetLastResultId () ) ) 
                        {
                            if ($to['read'] == '0')
                                $mess_input[$count_mes_input] = '<tr class="row1"><td>'.
                                '<div class="div_input_info"><table><tr><td width="100%" style="max-width:500px;"><a href="" class="adm_link_input" id="link_'.$to['date_time'].'">'.
                                '<span style="font-weight: bold;">' . $date_of_letter . ' - </span><span style="font-weight: bold;">'.
                                $user['username'] . ': ' . $title. '</span></a></td>'.
                                '<td><input type="checkbox" id="adm_ch_'.$count_mes_input.'" name="adm_ch_input_'.$to['date_time'].'" class="adm_ch_input" />'.
                                '<input type="hidden" id="area_hidden_'.$count_mes_input.'" class="area_hidden" name="hidden_input['.$count_mes_input.']" /></td></tr></table>'.
                                '</div></td><td width="50px"><input type="hidden" name="click_tools" />'.
                                '<input type="hidden" name="mess_input_click" /></td></tr>';
                            else
                                $mess_input[$count_mes_input] = '<tr class="row1"><td>'.
                                '<div class="div_input_info"><table><tr><td width="100%" style="max-width:500px;">'.
                                '<a href="" class="adm_link_input" id="link_'.$to['date_time'].'"><span>' . $date_of_letter . ' - </span>'.
                                '<span><b>' . $user['username'] . '</b>: ' . $title . '</span></a></td>'.
                                '<td><input type="checkbox" id="adm_ch_'.$count_mes_input.'" name="adm_ch_input_'.$to['date_time'].'" class="adm_ch_input" />'.
                                '<input type="hidden" id="area_hidden_'.$count_mes_input.'" class="area_hidden" name="hidden_input['.$count_mes_input.']" /></td></tr></table>'.
                                '</div></td><td width="400px"><input type="hidden" name="click_tools" />'.
                                '<input type="hidden" name="mess_input_click" /></td></tr>';                                                                
                            if ($to['blacklists'] == '1')
                                print("$('#adm_div_blacklists').append('".$mess_input[$count_mes_input]."');
                                $('#adm_div_blacklists input:checkbox').attr('class','adm_ch_blacklists');
                                $('#adm_div_blacklists input[id=adm_ch_".$count_mes_input."]').attr('name','adm_ch_blacklists_".$to['date_time']."');
                                $('#adm_div_blacklists input[id=area_hidden_".$count_mes_input."]').attr('name','hidden_blacklists[".$count_mes_input."]');                                
                                ");        
                                else if ($to['basket'] == '1')
                                    print("$('#adm_div_basket').append('".$mess_input[$count_mes_input]."');
                                    $('#adm_div_basket input:checkbox').attr('class','adm_ch_basket');
                                    $('#adm_div_basket input[id=adm_ch_".$count_mes_input."]').attr('name','adm_ch_basket_".$to['date_time']."');
                                    $('#adm_div_basket input[id=area_hidden_".$count_mes_input."]').attr('name','hidden_basket[".$count_mes_input."]');
                                    ");
                                    else
                                    {                                        
                                        print("$('#adm_div_input').append('".$mess_input[$count_mes_input]."');");                                            
                                    }                                                                                                                                                                                                     
                            $count_mes_input++;                                                                    
                        }                                                                                                                    
                    }
                       
                }
                
                if ($to['uid_from'] == $uid_current) //output messages
                {
                    if ($to['read'] == '0')
                        $mess_output[$count_mes_input] = '<tr class="row1"><td>'.
                        '<div class="div_input_info"><table><tr><td width="100%" style="max-width:500px;">'.
                        '<a href="" class="adm_link_output" id="link_'.$to['date_time'].'">'.
                        '<span style="font-weight: bold;">' . $date_of_letter . ' - </span><span style="font-weight: bold;">'.
                        $name . ': ' . $title . '</span></a></td>'.
                        '<td><input id="adm_ch_'.$count_mes_input.'" type="checkbox" name="adm_ch_output_'.$to['date_time'].'" class="adm_ch_output"/>'.
                        '<input type="hidden" id="area_hidden_'.$count_mes_input.'" name="hidden_output['.$count_mes_input.']" /></td></tr></table>'.
                        '</div></td><td width="400px"><input type="hidden" name="click_tools" />'.
                        '<input type="hidden" name="mess_output_click" /></td></tr>';
                        else
                            $mess_output[$count_mes_input] = '<tr class="row1"><td>'.
                            '<div class="div_input_info"><table><tr><td width="100%" style="max-width:500px;">'.
                            '<a href="" class="adm_link_output" id="link_'.$to['date_time'].'">'.
                            '<span>' . $date_of_letter . ' - </span><span><b>' . $name . '</b>: ' . $title . '</span></a></td>'.
                            '<td><input id="adm_ch_'.$count_mes_input.'" type="checkbox" name="adm_ch_output_'.$to['date_time'].'" class="adm_ch_output"/>'.
                            '<input type="hidden" id="area_hidden_'.$count_mes_input.'" name="hidden_output['.$count_mes_input.']" /></td></tr></table>'.
                            '</div></td><td width="400px"><input type="hidden" name="click_tools" />'.
                            '<input type="hidden" name="mess_output_click" /></td></tr>';                        
                    if ($to['blacklists'] == '1')
                        print("$('#adm_div_blacklists').append('".$mess_output[$count_mes_input]."');
                        $('#adm_div_blacklists input:checkbox').attr('class','adm_ch_blacklists');
                        $('#adm_div_blacklists input[id=adm_ch_".$count_mes_input."]').attr('name','adm_ch_blacklists_".$to['date_time']."');
                        $('#adm_div_blacklists input[id=area_hidden_".$count_mes_input."]').attr('name','hidden_blacklists[".$count_mes_input."]');                        
                        ");        
                        else if ($to['basket'] == '1')
                            print("$('#adm_div_basket').append('".$mess_output[$count_mes_input]."');
                            $('#adm_div_basket input:checkbox').attr('class','adm_ch_basket');
                            $('#adm_div_basket input[id=adm_ch_".$count_mes_input."]').attr('name','adm_ch_basket_".$to['date_time']."');
                            $('#adm_div_basket input[id=area_hidden_".$count_mes_input."]').attr('name','hidden_basket[".$count_mes_input."]');
                            ");
                            else
                                print("$('#adm_div_output').append('".$mess_output[$count_mes_input]."');");                             
                    $count_mes_input++;            
                }                                          
            }                        
        }
//        if (($mail_show == 1 && !isset($_POST['mail_show_chat_msgs'])) || $_POST['mail_show_chat_msgs']==1){
//            print("if (!$('#adm_div_blacklists').html())                                         
//                     $('.ch_show').parent().hide();
//            ");    
//        }       
        
        print(" $('.adm_link_input').click(function()
                {
                    var mess_time = $(this).attr('id').slice(5,$(this).attr('id').length);                    
                    $('input[name=mess_input_click]').val(mess_time); //если нажали на письмо(ссылку) для подробного просмотра
                    //$(this).next().next().val(mess_time);
                    $('form[name=frm_letters]').submit();                    
                    return false;     
                });
                
                $('.adm_link_output').click(function()
                {
                    var mess_time = $(this).attr('id').slice(5,$(this).attr('id').length);                    
                    $('input[name=mess_output_click]').val(mess_time); //если нажали на письмо(ссылку) для подробного просмотра
                    //$(this).next().next().val(mess_time);
                    $('form[name=frm_letters]').submit();                    
                    return false;     
                });
                
                $('form[name=frm_letters]').submit(function()
                {
                   $('input[name=click_tools]').val('1'); 
                });
                
                var letters = new Array('input', 'output', 'blacklists', 'basket');
                                
                for (var i=0;i<letters.length;i++)
                {
                    if (!$('#adm_div_'+letters[i]).html())
                    {
                        $('#adm_div_'+letters[i]).html('Нет писем');
                    }
                }
                
                $('input:checkbox[class!=ch_show]').click(function(e)
                {
                    var ch_name = $(e.target).attr('name').slice(7,$(e.target).attr('name').lastIndexOf('_'));
                    var ch_time = $(e.target).attr('name').slice($(e.target).attr('name').lastIndexOf('_')+1,$(e.target).attr('name').length);                
                                                              
                    if ($(this).is(':checked'))
                    {
                        $('#div_'+ch_name+'_tools').slideDown('slow');
                        $(this).next().val(ch_time);
                       
                    }    
                    else if ($('.adm_ch_'+ch_name).length == $('.adm_ch_'+ch_name+':not(:checked)').length)                        
                    {
                        $('#div_'+ch_name+'_tools').slideUp('slow');
                    }
                    
                    if (!$(this).is(':checked'))
                    {
                        $(this).next().val('');
                    }
                                                               
                });
                
                $('.ch_show').click(function(){                        
                  if ($(this).is(':checked'))
                     $('input[name=mail_show_chat_msgs]').val('1');
                     else                              
                        $('input[name=mail_show_chat_msgs]').val('0');                                                                                                            
                  $('form[name=frm_letters]').submit();                                            
                });
                
                if ($.browser.safari){
                    $('.div_input_info').each(function(){
                        $(this).find('td:first').width('250px');
                    }); 
                }                
                
        }); </script>");
        //$frm->addsingle('<td align="center" colspan="4"><input name="btn_back" class="btnmain" type="submit" value="'.__('Back').'"></td>');        
        $frm->show(false,false);
                                    
    }
    else if (!empty($_POST['mess_input_click'])) //подробный просмотр входящих сообщений
    {
        $frm = new InputForm ( 'admin.php?show=module&id=cabinet.index', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_input' );
        $frm->addsingle('<tr><th colspan="4">'.__('View incoming message').'</th></tr>');
        $cur_date = $_POST['mess_input_click'];
        $imail->setWorkTable ( $imail->prefix . 'imail' );
        $imail->setId ( $cur_date, 'date_time' );
        $imail->iMailBeginRead('*', '=', '', ' ORDER BY date_time','asc');
        $resultid = $imail->GetLastResultId ();
        while ( $to = $imail->Read ( $resultid ) ) 
        {
            $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
            $iblockitem->setId(date( "Y-m-d H:i:s", $cur_date),'idate');
            $iblockitem->BeginDataRead();   
            while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId())){
               $title = $item_data['title'];
               $idata = array_merge ( unpack_data ( $item_data ['idata'] ), $item_data );
               $text = $idata['itext'];
               $file = $idata['file'];
               $item_id = $item_data['id'];   
            }                     
           // while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId()))
          //  {
                if (mb_substr ( date ( "d.m.y", $to ['date_time'] ), 0, 8 ) == $now_date)                
                    $date_of_letter = date ( __('Today').", H:i", $to ['date_time'] );                                     
                    else
                        $date_of_letter = date ( "d.m.y H:i", $to ['date_time'] );
                
                $arr_to = explode(',',$to['uid_to']);  //input messages                
                for ($i=0;$i<sizeof($arr_to);$i++)
                {
                    if ($arr_to[$i] == $uid_current)
                    {
                        $imail->setWorkTable ( $imail->prefix . 'users' );
                        $imail->setId ( $to['uid_from'], 'uid' );
                        $imail->iMailBeginRead (array('uid', 'username'));
                        while ( $user = $imail->Read ( $imail->GetLastResultId () ) )
                        {                                                                                 
                            $frm->addrow(__('From'), $user['username'], '', '', '', '25%,60%');
                            $frm->addrow(__('Who'), $name);
                            $frm->addrow(__('Date'), $date_of_letter);
                            if (!empty($title))
                              $frm->addrow(__('Title'), $title);
                            $frm->addrow(__('Message'), unpack_data ( $to ['message'] ));
                            if ($text != ""){
                                $frm->addrow(__('Attached document'), $text);    
                                $frm->hidden('hidden_resend_document', $to ['date_time']);
                            }
                            if (!empty($file)){
                                $filefield = $file;
                                $keys = array_keys($filefield);
                                $fcount = sizeof($filefield);
                                for($s=0; $s<$fcount; $s++){
                                    $content .= '<a href="download.php?att_file='.$filefield[$keys[$s]].'" style="font-weight: bold;" title="'.__('Download!').'">'.
                                    basename($filefield[$keys[$s]]).'</a><br>
                                    - '.__('Size').': '.hcms_filesize($filefield[$keys[$s]]).'<br><br>';
                                    //- '.__('Type').': '.mime_content_type($filefield[$keys[$s]]);                                    
                                }
                                $frm->addrow(__('Attached files'), $content);
                            }
                            $frm->addsingle('<tr class="row1"><td><input type="submit" value="'.__('Answer').'" name="btn_answer" /><input type="submit" value="'.__('Answer all').'" name="btn_answer_all" /><input type="submit" value="'.__('Resend').'" name="btn_resend" /></td><td></td></tr>');
                            $frm->hidden('hidden_answer', $user['username']);
                            $frm->hidden('hidden_resend_title', $title);
                            $frm->hidden('hidden_resend_message', unpack_data ( $to ['message'] ));                                                                                                                    
                            $frm->hidden('user_id',  $user ['uid'] );
                            $frm->hidden('item_id',  $item_id );
                        }        
                    }
                }                              
          //  }
        }
        print('<script type="text/javascript">
            $(document).ready(function()
            {
                $(".btnmain").click(function()
                {                   
                  $("form[name=frm_input]").attr("action", "admin.php?show=module&id=cabinet.blacklists"); 
                  $("form[name=frm_input]").submit();
                });
            }) </script>');
        $frm->show();                       
    }
    else if (!empty($_POST['mess_output_click'])) //подробный просмотр отправленных сообщений
    {
        $frm = new InputForm ( 'admin.php?show=module&id=cabinet.index', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_output' );
        $frm->addsingle('<tr><th colspan="4">'.__('View outgoing message').'</th></tr>');
        $cur_date = $_POST['mess_output_click'];
        $imail->setWorkTable ( $imail->prefix . 'imail' );
        $imail->setId ( $cur_date, 'date_time' );
        $imail->iMailBeginRead('*', '=', '', ' ORDER BY date_time','asc');
        $resultid = $imail->GetLastResultId ();
        while ( $to = $imail->Read ( $resultid ) ) 
        {
            $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
            $iblockitem->setId(date( "Y-m-d H:i:s", $cur_date),'idate');
            $iblockitem->BeginDataRead();                        
            while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId())){
               $title = $item_data['title'];
               $idata = array_merge ( unpack_data ( $item_data ['idata'] ), $item_data );
               $text = $idata['itext'];
               $file = $idata['file'];
               $item_id = $item_data['id'];   
            }
         //   while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId()))
          //  {
                if (mb_substr ( date ( "d.m.y", $to ['date_time'] ), 0, 8 ) == $now_date)                
                    $date_of_letter = date ( __('Today').", H:i", $to ['date_time'] );                                     
                    else
                        $date_of_letter = date ( "d.m.y H:i", $to ['date_time'] );
                
                $imail->setWorkTable ( $imail->prefix . 'users' );
                $imail->setId ( $to['uid_to'], 'uid' );
                $imail->iMailBeginRead (array('username'));
                while ( $user = $imail->Read ( $imail->GetLastResultId () ) ){
                    $user_to = $user['username'];  
                }                             
                $frm->addrow(__('From'), $name, '', '', '', '25%,60%');
                $frm->addrow(__('Who'), $user_to);
                $frm->addrow(__('Date'), $date_of_letter);
                if (!empty($title))
                  $frm->addrow(__('Title'), $title);
                $frm->addrow(__('Message'), unpack_data ( $to ['message'] ));
                if ($text != ""){
                    $frm->addrow(__('Attached document'), $text);    
                    $frm->hidden('hidden_resend_document', $to ['date_time']);
                }
                if (!empty($file)){
                    $filefield = $file;
                    $keys = array_keys($filefield);
                    $fcount = sizeof($filefield);
                    for($s=0; $s<$fcount; $s++){
                        $content .= '<a href="download.php?att_file='.$filefield[$keys[$s]].'" style="font-weight: bold;" title="'.__('Download!').'">'.
                        basename($filefield[$keys[$s]]).'</a><br>
                        - '.__('Size').': '.hcms_filesize($filefield[$keys[$s]]).'<br><br>';
                        //- '.__('Type').': '.mime_content_type($filefield[$keys[$s]]);                                    
                    }
                    $frm->addrow(__('Attached files'), $content);
                }
                $frm->addsingle('<tr class="row1"><td><input type="submit" value="'.__('Answer all').'" name="btn_answer_all" /><input type="submit" value="'.__('Resend').'" name="btn_resend" /></td><td></td></tr>');                
                $frm->hidden('hidden_resend_title', $title);
                $frm->hidden('hidden_resend_message', unpack_data ( $to ['message'] ));
                $frm->hidden('user_id',  $user ['uid'] );
                $frm->hidden('item_id',  $item_id );                                                                                                                                            
          //  }                                              
        }
        
        print('<script type="text/javascript">
            $(document).ready(function()
            {
                $(".btnmain").click(function()
                {                   
                  $("form[name=frm_output]").attr("action", "admin.php?show=module&id=cabinet.blacklists"); 
                  $("form[name=frm_output]").submit();
                });
            }) </script>');
        $frm->show();                   
    }
?>