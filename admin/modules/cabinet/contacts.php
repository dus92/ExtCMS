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
$asyncmgr = new AsyncMgr ();
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

    if (empty($_POST['mess_output_click']) && empty($_POST['mess_input_click']) && !empty($_POST['user_id']) )//отображение писем
    {        
        if (!empty($_POST['user_id']))
            $user_id = (int)$_POST['user_id'];
            
        print('<script type="text/javascript">
            $(document).ready(function(){');
        
        $frm = new InputForm ( '', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_letters' );
        //$frm->addbreak ( __ ( 'Письма' ) );
      
        $frm->addsingle('<tr id="tr_input_header"><th colspan="4">Связанные письма</th></tr>');
        $frm->addsingle('<tr class="row1"><td><div id="adm_div_input"></div></td></tr>');
        $frm->addsingle('<tr class="row1"><td><div id="div_input_tools" style="display:none;"><input type="submit" value="Удалить" name="btn_delete_input" /><input type="submit" value="Прочитано" name="btn_read_input" /><input type="submit" value="Не прочитано" name="btn_unread_input" /><input type="submit" value="В черновики" name="btn_blacklists_input" /><input type="submit" value="В корзину" name="btn_basket_input" /></div></td></tr>');

       // $frm->addsingle('<tr class="row1"><td><div id="adm_div_output"></div></td></tr>');
//        $frm->addsingle('<tr class="row1"><td><div id="div_output_tools" style="display:none;"><input type="submit" value="Удалить" name="btn_delete_output" /><input type="submit" value="Прочитано" name="btn_read_output" /><input type="submit" value="Не прочитано" name="btn_unread_output" /><input type="submit" value="В черновики" name="btn_blacklists_output" /><input type="submit" value="В корзину" name="btn_basket_output" /></div></td></tr>');

        $imail->setWorkTable ( $imail->prefix . 'imail' );
        $imail->setId ( 0, 'blacklists' );
        $imail->iMailBeginRead('*', '=', '', ' ORDER BY date_time','asc');
        $resultid = $imail->GetLastResultId ();
        
        $mess_input = array();
        $mess_output = array();
        $count_mes_input = 0; //количество сообщений 
        while ( $to = $imail->Read ( $resultid ) ) 
        {
            $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
            $iblockitem->setId(date( "Y-m-d H:i:s", $to['date_time']),'idate');
            $iblockitem->BeginDataRead();                        
            while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId()))
            {
                if (mb_substr ( date ( "d.m.y", $to ['date_time'] ), 0, 8 ) == $now_date)                
                    $date_of_letter = date ( "Сегодня, H:i", $to ['date_time'] );
                    else                
                        $date_of_letter = date ( "d.m.y H:i", $to ['date_time'] );
                                 
                $arr_to = explode(',',$to['uid_to']);  //input messages                
                    for ($i=0;$i<sizeof($arr_to);$i++)
                    {
                        if ( ($arr_to[$i] == $uid_current && $user_id == $to['uid_from']) ||
                            ($to['uid_from'] == $uid_current && $arr_to[$i] == $user_id) ){
                            $imail->setWorkTable ( $imail->prefix . 'users' );
                            $imail->setId ( $to['uid_from'], 'uid' );
                            $imail->iMailBeginRead (array('username'));
                            while ( $user = $imail->Read ( $imail->GetLastResultId () ) ) {
                                if ($to['read'] == '0')
                                    $mess_input[$count_mes_input] = '<tr class="row1"><td>'.
                                    '<div class="div_input_info"><table><tr><td width="100%" style="max-width:500px;"><a href="" class="adm_link_input" id="link_'.$to['date_time'].'">'.
                                    '<span style="font-weight: bold;">' . $date_of_letter . ' - </span><span style="font-weight: bold;">'.
                                    $user['username'] . ': ' . $item_data['title']. '</span></a></td>'.
                                    '<td><input type="checkbox" id="adm_ch_'.$count_mes_input.'" name="adm_ch_input_'.$to['date_time'].'" class="adm_ch_input" />'.
                                    '<input type="hidden" id="area_hidden_'.$count_mes_input.'" class="area_hidden" name="hidden_input['.$count_mes_input.']" /></td></tr></table>'.
                                    '</div></td><td width="50px"><input type="hidden" name="click_tools" />'.
                                    '<input type="hidden" name="mess_input_click" /></td></tr>';
                                else
                                    $mess_input[$count_mes_input] = '<tr class="row1"><td>'.
                                    '<div class="div_input_info"><table><tr><td width="100%" style="max-width:500px;">'.
                                    '<a href="" class="adm_link_input" id="link_'.$to['date_time'].'"><span>' . $date_of_letter . ' - </span>'.
                                    '<span><b>' . $user['username'] . '</b>: ' . $item_data['title'] . '</span></a></td>'.
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
                                    print("$('#adm_div_input').append('".$mess_input[$count_mes_input]."');");                                                                                                                                                                                                                                                                                    
                                $count_mes_input++;                                                                    
                            }                                                                                                                    
                        }                       
                    }                                         
            }                        
        }       
        
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
                
                $('input:checkbox').click(function(e)
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
                
        }); </script>");
        //$frm->addsingle('<td align="center" colspan="4"><input name="btn_back" class="btnmain" type="submit" value="'.__('Back').'"></td>');        
        $frm->show();
                                    
    }
    else if (!empty($_POST['mess_input_click'])) //подробный просмотр входящих сообщений
    {
        $frm = new InputForm ( 'admin.php?show=module&id=cabinet.index', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_input' );
        $frm->addsingle('<tr><th colspan="4">Просмотр сообщения</th></tr>');
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
            while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId()))
            {
                if (mb_substr ( date ( "d.m.y", $to ['date_time'] ), 0, 8 ) == $now_date)                
                    $date_of_letter = date ( "Сегодня, H:i", $to ['date_time'] );                                     
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
                             
                    $frm->addrow('От кого', $user['username'], '', '', '', '25%,60%');  
                    $frm->addrow('Кому', $u_to);
                    $frm->addrow('Дата', $date_of_letter);
                    $frm->addrow('Заголовок', $title);
                    $frm->addrow('Сообщение', unpack_data ( $to ['message'] ));
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
                    $frm->addsingle('<tr class="row1"><td><input type="submit" value="Ответить" name="btn_answer" /><input type="submit" value="Ответить всем" name="btn_answer_all" /><input type="submit" value="Переслать" name="btn_resend" /></td><td></td></tr>');
                    $frm->hidden('hidden_answer', $user['username']);
                    $frm->hidden('hidden_resend_title', $title);
                    $frm->hidden('hidden_resend_message', unpack_data ( $to ['message'] ));                                                                                                                    
                    $frm->hidden('user_id',  $to ['uid_to'] );
                    $frm->hidden('item_id',  $item_data ['id'] );
                }                                 
            }
        }
        print('<script type="text/javascript">
            $(document).ready(function()
            {
                $(".btnmain").click(function()
                {                   
                   history.back();
                   return false; 
                });
            }) </script>');
        $frm->show();                       
    }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    if (empty($_POST['user_id']) && empty($_POST['mess_input_click'])) //контакты
    {
        $frm = new InputForm ( 'admin.php?show=module&id=cabinet.index', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_contacts' );
        $frm->addbreak(__('Contact list'));
        
        $imail->setWorkTable ( $imail->prefix . 'users' );
        $imail->setId ( $uid_current, 'uid' );
        $imail->iMailBeginRead( array('uid', 'username', 'email'),'!=');//users
        $uid = $imail->GetLastResultId ();
        while ( $row_users = $imail->Read ( $uid ) )
        {
            //$frm->addsingle('<tr class="row1"><td><a href="" class="link_contacts" title="'.__('Write letter').'" >'.            
            $frm->addsingle('<tr class="row1"><td>'.
            '<table><tr><td><b>'.__('User').': </b><span>'.$row_users['username'].'</span>&nbsp;&nbsp;&nbsp;</td>'.
            '<td><input type="hidden" name="user_id" value="'.$row_users['uid'].'" />'.
            '<a href="" name="link_write" class="link_write" id="'.$row_users['uid'].'">'.__('Write letter').'</a></td></tr>'.
            '<tr><td valign="top"><b>E-mail: </b><a href="mailto:'.$row_users['email'].'">'.$row_users['email'].'</a></td>'.
            '<td valign="top"><a href="" name="link_show_letters" class="link_show_letters" id="'.$row_users['uid'].'">'.__('Show related letters').'</a></td></tr></table>'.            
            '</td></tr>');
            
            $frm1 = new InputForm ( 'admin.php?show=module&id=cabinet.index', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_contacts_'.$row_users['uid'] );
            $frm1->hidden('user_id', $row_users['uid']);
            $frm1->show(false, false);
            
            $frm2 = new InputForm ( '', 'post', __('Back'), '', '', 'multipart/form-data', 'frm_show_'.$row_users['uid'] );
            $frm2->hidden('user_id', $row_users['uid']);
            $frm2->show(false, false);
        }
        $frm->hidden('hidden_contacts','');
        $frm->show(false, false);        
    }
    
        
    print('<script type="text/javascript">
        $(document).ready(function()
        {
            $(".link_write").click(function()
            {                
                var id = $(this).attr("id");
                $("form[name=frm_contacts_"+id+"]").submit();
                return false;
            });
            
            $(".link_show_letters").click(function()
            {                                
                var id = $(this).attr("id");
                $("form[name=frm_show_"+id+"]").submit();
                return false;
            });            
        })
        </script>');
  
?>