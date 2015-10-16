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
//rcms_loadAdminLib ( 'file-uploads' );
require_once ('./modules/engine/api.asyncmgr.php');

$system->config['pagename'] = __('cabinet');
$iblockitem = new iBlockItem ();
$iblock = new iBlock ();
$chat = new Chat();

$now_date = date ( "d.m.y", time () );
$name = $system->user['username'];

if (LOGGED_IN)
{
    //////////script begin///////////
    print ('<script type="text/javascript" src="./modules/js/jquery.js"></script> 
    <script type="text/javascript">
    $(document).ready(function(){
    var k=0;             
      
      $("a[id*=pasteimglink]").each(function()
      {
            $(this).addClass("images_all");
      });
        
      if (window.location.hash == "#cabinet")
      {		
        $(".div_output").hide();        
        $("#div_write").hide();
        $("#send").css("display","none"); 
        $("form[4]").css("display","none");
                
        $(".div_input").show();        
        $(".div_blacklists").hide();
        $(".div_basket").hide();
        $("#div_contacts").hide();
        $("#div_files").hide();
        
        $("#letters_answer").css("display","none");        
         
      }
      else if (window.location.hash == "#output")
      {
         $("#div_write").hide();
         $(".div_output").show();
         $(".div_input").hide();    
         //$("#send").css("display","block");
         $("form[4]").css("display","none");
         $(".div_blacklists").hide();
         $(".div_basket").hide(); 
         $("#div_contacts").hide();
         $("#div_files").hide();        
        
        $("#letters_answer").css("display","none");
      }
      else if (window.location.hash == "#write")
      {
        $("form[4]").css("display","block");
        $("#div_messages").css("display","none");
        $("#div_contacts").hide();
        $("#div_files").hide();        
      }
      else if (window.location.hash == "#blacklists")
      {
        $(".div_output").hide();
        $(".div_input").hide();
        $(".div_blacklists").show();
        $(".div_basket").hide();
        $("form[4]").hide();      
        $("#div_contacts").hide();
        $("#div_files").hide();  
      }
      else if (window.location.hash == "#basket")
      {
        $(".div_output").hide();
        $(".div_input").hide();
        $(".div_blacklists").hide();
        $(".div_basket").show();
        $("form[4]").hide();
        $("#div_contacts").hide();
        $("#div_files").hide();        
      }
      else if (window.location.hash == "#cab_contacts")
      {
        $(".div_output").hide();
        $(".div_input").hide();
        $(".div_blacklists").hide();
        $(".div_basket").hide();
        $("form[4]").hide();
        $("#div_contacts").show();
        $("#div_files").hide();
      }
      else if (window.location.hash == "#files")
      {
            $("#div_messages").hide();
            $("#div_contacts").hide();
            $("form[name=itemadd]").hide();
            $("#div_files").show();
            var k=0;                
            $("#div_messages a[class!=link_output][class!=link_input][class!=images_all]").each(function()
            {
                k++;
                $("#tbl_files").append("<tr id=tr_"+k+"><td id=td_address_"+k+">");
                $("#td_address_"+k).append($(this).parent().parent().parent().prev().html());                                
                $("#tr_"+k).append("</td><td id=td_files_"+k+">");
                $("#td_files_"+k).append($(this));
                $("#tbl_files").append("</td></tr>");    
            });
            if (k==0)
            {
                $("#div_files").append("Нет прикрепленных файлов");
            }                                                  
      }  
      
      $(".link_input").click(function()
      {        
        $("#format").fadeOut("slow");        
        $("#div_messages div input").fadeOut("slow");
        $("#div_messages div a").fadeOut("slow");        
        $(this).next("div").delay(800).fadeIn("slow");
        $("#letters_answer").delay(800).fadeIn("slow");  
        //$("#div_general").css("marginLeft","100px");
        $("#answer").show();
        $("#letters_answer span:first").show();
        $("#div_contacts").hide();
        
        var ch_id = $(this).prev().attr("id").slice(3,$(this).prev().attr("id").length);
        ajax_query(ch_id);
                
        setTimeout(function()
        {
            $(".p_from span").each(function()
            {
                if ($(this).is(":visible"))
                    from = $(this).html(); 
            });
            
            $(".str_title").each(function()
            {
                if ($(this).is(":visible"))
                    title_text = $(this).next().html(); 
            });
            
            $(".str_message").each(function()
            {
                if ($(this).is(":visible"))
                    message_text = $(this).next().html(); 
            });
        },1000);        
        
        $("#answer").click(function()
        {
            window.location.hash = "#write";
            setTimeout(function()
            {
                $("select[name=list_users1] option").each(function()
                {   
                    //alert($(this).text()+" "+$(".p_from span").html());
                    if ($(this).text() == from)
                        $(this).attr("selected", "selected");
                });            
           },1000);        
        });
        
         
      });
      
      $(".link_output").click(function()
      {
        //alert("sdfsdf");
        $("#format").fadeOut("slow");        
        $("#div_messages div input").fadeOut("slow");
        $("#div_messages div a").fadeOut("slow");        
        $(this).next("div").delay(800).fadeIn("slow");
        $("#letters_answer").delay(800).fadeIn("slow");
        //$("#div_messages").css("height","auto");
        $("#answer").hide();
        $("#letters_answer span:first").hide();
        $("#div_contacts").hide();
        
        var ch_id = $(this).prev().attr("id").slice(3,$(this).prev().attr("id").length);
        ajax_query(ch_id);  
        
        setTimeout(function()
        {            
            $(".str_title").each(function()
            {
                if ($(this).is(":visible"))
                    title_text = $(this).next().html(); 
            });
            
            $(".str_message").each(function()
            {
                if ($(this).is(":visible"))
                    message_text = $(this).next().html(); 
            });
        },1000); 
      });      
    
    $("#send").css("display","none");
    
    var id;
      $(".delete").click(function()
      {
        $(".ch_in").each(function()
        {
           if ($(this).is(":checked"))
           {
               //alert($(this).attr("id"));
               
               id = $(this).attr("id");
           } 
        });
      });
      
      
      $(".p_users").click(function()
      {
        var p_this = $(this).children("span:first").html();
        window.location.hash = "#write";
        setTimeout(function()
        {
            $("select[name=list_users1] option").each(function()
            {
                if ($(this).text() == p_this)
                    $(this).attr("selected", "selected");            
            });
        },1000);                                     
      });
      
      
      function ajax_query(id)
      {
        $.ajax(
        {
            url: "index.php?module=cabinet&n_ajax",
            type: "POST",
            async: "false",
            data: 
            {
                "read_clicked": id
            },
            dataType:"html",
            success: function (result) 
            {									  
                //alert("success");        
            },
            error: function ()
            {
                alert("db_error");
            }  
        });
      }
      
    $("#advanced").click(function()
    {
        $("form[name=itemadd]").attr("action","admin.php?show=module&id=cabinet.index");
        
//        $("form[name=itemadd]").submit(function()
//        {
//            return true;
//        });                    
    });    
            
    });
    </script>');
    /////////script end/////////////
    
    //$idd = $_POST['id'];
    
    $chat->setWorkTable ( $chat->prefix . 'users' );

    $chat->setId ( $name, 'username' );
    $chat->BeginDataRead ( array('uid') );//id of current user
    $uid = $chat->GetLastResultId ();
    while ( $row = $chat->Read ( $uid ) )
        $uid_current = $row['uid']; 
        
    $chat->setWorkTable ( $chat->prefix . 'chat' );
    $chat->setId ( 0, 'blacklists' );
    $chat->ChatBeginRead('*', '=', '', ' ORDER BY date_time','asc');
    $resultid = $chat->GetLastResultId ();
    $arr_from=array();
    $k = 0;
    /////////////////////////////////////////////////////////////////////////////////////////
    $result = '';
     
    $col = 0;
    $result = '<div id="div_messages"><strong></strong><div>';
    while ( $to = $chat->Read ( $resultid ) ) 
    {
        ///getting data according to the date///
        $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
        $iblockitem->setId(date( "Y-m-d H:i:s", $to['date_time']),'idate');
        $iblockitem->BeginDataRead();
                
        ////////////////////////////////////////        
        while ($item_data = $iblockitem->Read($iblockitem->GetLastResultId()))
        {                   
            $arr_to = explode(',',$to['uid_to']);  //input messages
            for ($i=0;$i<sizeof($arr_to);$i++)
            {
                if ($arr_to[$i] == $uid_current)
                {
                    $chat->setWorkTable ( $chat->prefix . 'users' );
                    $chat->setId ( $to['uid_from'], 'uid' );
                    $chat->ChatBeginRead (array('username'));
                    while ( $user = $chat->Read ( $chat->GetLastResultId () ) ) 
                    {
                        $title = $item_data['title'];                         
                        $idata = array_merge ( unserialize ( $item_data ['idata'] ), $item_data );
                        $idata['description'] = rcms_parse_text_by_mode ( $idata ['description'], $idata ['mode'] ); 
                        $idata['itext'] = rcms_parse_text_by_mode ( $idata ['itext'], $idata ['mode'] );                    
                        $description = $idata['description'];  
                        $text = $idata['itext'];
                        //if ($item_data['description'] == '')
//                            $description = '<p> </p>';
//                            else
//                                $description = '<strong>Описание</strong><p>'.$description.'</p>';                                    
                        
                        //$result .='<div align="justify" class="input_messages">';
                        if ($to['blacklists'] == '1')
                            $result.='<div class="div_blacklists">';
                            else if ($to['basket'] == '1')
                                $result.='<div class="div_basket">';
                                else
                                    $result.='<div class="div_input">';
                        $result .= '<input type="checkbox" id="ch_'.$to['date_time'].'" class="ch_in" />';                       
                        if (mb_substr ( date ( "d.m.y", $to ['date_time'] ), 0, 8 ) == $now_date)
                        {
                            $date_of_letter = date ( "Сегодня, H:i", $to ['date_time'] ); 
                        }                    
                        else
                        {
                            $date_of_letter = date ( "d.m.y H:i", $to ['date_time'] );                               
                        }
                        if ($to['read'] == '0')
                            $result .= '<a href="#letters_input" id="link'.$col.'" class="link_input"><span style="font-weight: bold;">' . $date_of_letter . ' - </span><span style="font-weight: bold;">' . $user['username'] . ': ' . unserialize ( $to ['message'] ) . '</span><p></p></a>';
                            else
                                $result .= '<a href="#letters_input" id="link'.$col.'" class="link_input"><span>' . $date_of_letter . ' - </span><span>' . $user['username'] . ': ' . unserialize ( $to ['message'] ) . '</span><p></p></a>';
                                    
                        $result .='<div id="div_'.$col.'" style="display:none;" class="div_input_general">
                                <div class="div_input_address"> 
                                <p class="p_from">От кого: &nbsp&nbsp <span>'.$user['username'].'</span></p>
                                <p class="p_to">Кому: &nbsp&nbsp <span>'.$system->user['username'].'</span></p>
                                <p>'. $date_of_letter .'</p></div>
                                <div class="div_input_message"><div>
                                <strong class="str_title">Заголовок: </strong><p>'.$title.'</p>
                                <strong class="str_message">Сообщение: </strong><p>'.unserialize ( $to ['message'] ).'</p>';                               
                        if ($text != "")
                            $result .= '<h3>Прикрепленный документ</h3><strong>Текст</strong>'.$text;
                        $result .= '</div></div></div>'; 
                        
                            $result.='</div>';                             
                        $col++;  
                       // $result .='</div>';                            
                    }
                }
            }            
            if ($to['uid_from'] == $uid_current) //output messages
            {
                $chat->setWorkTable ( $chat->prefix . 'users' );
                $chat->setId ( $to['uid_to'], 'uid' );
                $chat->ChatBeginRead (array('username'));
                                
                $title = $item_data['title'];                         
                $idata = array_merge ( unserialize ( $item_data ['idata'] ), $item_data );
                //hcms_debug($idata);
                $idata['description'] = rcms_parse_text_by_mode ( $idata ['description'], $idata ['mode'] ); 
                $idata['itext'] = rcms_parse_text_by_mode ( $idata ['itext'], $idata ['mode'] );                    
                $description = $idata['description'];  
                $text = $idata['itext'];
                //$idata ['source'] = rcms_parse_text_by_mode ( $idata ['file'], 'text' );
                //$file = $idata ['source'];                                
                
                while ( $user = $chat->Read ( $chat->GetLastResultId () ) )
                {
                    $user_to = $user['username'];                    
                }                                     

                if ($to['blacklists'] == '1')
                    $result.='<div class="div_blacklists">';
                    else if ($to['basket'] == '1')
                        $result.='<div class="div_basket">';
                        else
                            $result.='<div class="div_output">';
                $result .= '<input type="checkbox" id="ch_'.$to['date_time'].'" class="ch_out" />';
                if (mb_substr ( date ( "d.m.y", $to ['date_time'] ), 0, 8 ) == $now_date)
                {
                   $date_of_letter = date ( "Сегодня, H:i", $to ['date_time'] );       
                }
                else
                {
                   $date_of_letter = date ( "d.m.y H:i", $to ['date_time'] );                                              
                }
                if ($to['read'] == '0')
                    $result .= '<a href="#letters_output" id="link'.$col.'" class="link_output"><span style="font-weight: bold;" id="'.$date_of_letter.'">' . $date_of_letter . ' - </span><span style="font-weight: bold;">' . $system->user['username'] . ': ' . unserialize ( $to ['message'] ) . '</span><p></p></a>';
                    else
                        $result .= '<a href="#letters_output" id="link'.$col.'" class="link_output"><span id="'.$date_of_letter.'">' . $date_of_letter . ' - </span><span>' . $system->user['username'] . ': ' . unserialize ( $to ['message'] ) . '</span><p></p></a>';
                $div_data = '<div id="div_data'.$col.'">
                        <p><strong>'.$title.'</strong></p>
                        <p>'.$description.'</p><br>                                
                        <p>'.$text.'</p></div>';
                $result .='<div id="div_'.$col.'" style="display:none;" class="div_output_general">
                        <div class="div_output_address"> 
                        <p class="p_from">От кого: &nbsp&nbsp <span>'.$system->user['username'].'</span></p>
                        <p class="p_to">Кому: &nbsp&nbsp <span>'.$user_to.'</span></p>
                        <p>'. $date_of_letter .'</p></div>
                        <div class="div_output_message"><div>
                        <strong class="str_title">Заголовок: </strong><p>'.$title.'</p>
                        <strong class="str_message">Сообщение: </strong><p>'.unserialize ( $to ['message'] ).'</p>';                       
                if ($text != "")
                    $result .= '<h3>Прикрепленный документ</h3><strong>Текст</strong>'.$text;
                $result .= '</div></div></div>';   
                        
                //if ($to['blacklists'] == '1')
                    $result.='</div>';
                    //hcms_debug($text);                            
                $col++;
              //  $result .= '</div>';           
            }
        }  
    }
    //////////////////////////////////////       
    $result .= '</div></div>';
    ////////////////contacts////////////////
    $chat->setWorkTable ( $chat->prefix . 'users' );

    $chat->setId ( $uid_current, 'uid' );
    $chat->ChatBeginRead( array('username','email'),'!=');//users
    $uid = $chat->GetLastResultId ();
    $result.='<div id="div_contacts"><strong>Список контактов</strong>';
    while ( $row_users = $chat->Read ( $uid ) )
    {        
        $result.='<p class="p_users" title="Отправить сообщение"><b>Пользователь:</b><span>'.$row_users['username'].'</span><b>e-mail:</b><span>'.$row_users['email'].'</span></p>';
    }     
    $result.='</div>';     
    
    //////////////files//////////////////////
    $result .= '<div id="div_files"><h3>Список файлов</h3><br><table id="tbl_files" border="1"></table></div>';
            
    $cur_date = '';
    show_window ('', $result, 'left' );
    ////////////delete messages/////////////
    
    $message_time = array();
    $message_time = &$_POST['message_time'];  
    
    $chat->setWorkTable ( $chat->prefix . 'chat' );
    $iblockitem->setWorkTable($iblockitem->prefix.'ibitems');
    for ($i=0;$i<sizeof($message_time);$i++)
    {         
        $chat->dropData('date_time', $message_time[$i]);
        $iblockitem->dropData('idate', date( "Y-m-d H:i:s", $message_time[$i]));                    
    }
    
    /////////////move to blacklists/////////////////
    $moveto_blacklists = array();
    $moveto_blacklists = &$_POST['moveto_blacklists'];
    for ($i=0;$i<sizeof($moveto_blacklists);$i++)
    {         
        $chat->setId($moveto_blacklists[$i],'date_time');
        $chat->editData(array('blacklists'=>1, 'basket'=>0));                        
    }
    
    /////////////move to basket/////////////////
    $moveto_basket = array();
    $moveto_basket = &$_POST['moveto_basket'];
    for ($i=0;$i<sizeof($moveto_basket);$i++)
    {         
        $chat->setId($moveto_basket[$i],'date_time');
        $chat->editData(array('basket'=>1, 'blacklists'=>0));                        
    }        
    ///////////move from blacklists////////////
    $movefrom_blacklists = array();
    $movefrom_blacklists = &$_POST['movefrom_blacklists'];
    for ($i=0;$i<sizeof($movefrom_blacklists);$i++)
    {         
        $chat->setId($movefrom_blacklists[$i],'date_time');
        $chat->editData(array('blacklists'=>0));                        
    }
    ///////////move from basket////////////
    $movefrom_basket = array();
    $movefrom_basket = &$_POST['movefrom_basket'];
    for ($i=0;$i<sizeof($movefrom_basket);$i++)
    {         
        $chat->setId($movefrom_basket[$i],'date_time');
        $chat->editData(array('basket'=>0));                        
    }
    /////////////read messages///////////////
    $read = array();
    $read = &$_POST['read'];
    for ($i=0;$i<sizeof($read);$i++)
    {         
        $chat->setId($read[$i],'date_time');
        $chat->editData(array('read'=>1));                        
    }
    
    $read_clicked = &$_POST['read_clicked'];
    $chat->setId($read_clicked,'date_time');
    $chat->editData(array('read'=>1));
    
    /////////////unread messages///////////////
    $unread = array();
    $unread = &$_POST['unread'];
    for ($i=0;$i<sizeof($unread);$i++)
    {         
        $chat->setId($unread[$i],'date_time');
        $chat->editData(array('read'=>0));                        
    }
          
    ////////////////////////////////////////
$frm = new InputForm ( '', 'post', __ ( 'Submit' ), '', '', 'multipart/form-data', 'itemadd' );   
///////////////send message///////////////////////////
$no_mes = '';    
/////////////////////////////////////////////////////////////////////////////////////////////
    
//$iblock = new iBlock ();
//$iblockitem = new iBlockItem ();
$ibid = (isset ( $_POST ['ibid'] ) ? vf ( $_POST ['ibid'], 4 ) : '');
$ibgid = (isset ( $_POST ['ibgid'] ) ? vf ( $_POST ['ibgid'], 4 ) : '');

if (! empty ( $_POST ['save'] )) {
	// save item
	$ibid = /*vf ( $_POST ['ibid'], 4 );*/'exchange_docs';
	if (! ($system->checkForRight ( 'IBLOCKS-' . strtoupper ( $ibid ) . '-POSTER' ) || $system->checkForRight ( 'IBLOCKS-' . strtoupper ( $ibid ) . '-EDITOR' ))) {
		rcms_showAdminMessage ( __ ( 'You do not have rights to post items in these iblock' ) );
        //show_window ('', '<h4>'.__ ( 'You do not have rights to post items in these iblock' ).'</h4>', 'left' );
		exit ();
	}
	$contid = vf ( $_POST ['contid'], 4 );
	$catid = /*vf ( $_POST ['catid'], 3 );*/1;
	
	$infoblock = $system->iblocks [$ibid];
	$fields = unserialize ( $infoblock ['fields'] );
	$iblockext = unserialize ( $system->iblocks [$ibid] ['extopt'] );
	$iblockitem = new iBlockItem ();
	$id = $iblockitem->GetTableAINextValue ();
	
	$item = ib_checkpost ( $fields, $id, $ibid );
    $idate = strtotime ( $_POST ['idate'] );
    //hcms_debug(date ( "Y-m-d H:i:s" ));
    $item['mode'] = 'html';
    if ($_POST['url_file']!='null')
    {
        $href = './content/iblocks/files/exchange_docs/'.date ( "Y", $idate ).'/'. date ( "M", $idate ).'/'.$id.'/'.$_POST['url_file'];
        $item['itext'] = '<p><a style="font-weight:bold" href="'.$href.'">'.$_POST['url_file'].'</a></p>';
        $item['file'] = $href;    
    }

    //hcms_debug($href);
	if (isset ( $item ['indexed'] )) {
		$indexed = $item ['indexed'];
		unset ( $item ['indexed'] );
	}
	
	$no_post = false;

	  
		$item ['ibid'] = $ibid;
		$item ['contid'] = $contid;
		$item ['catid'] = $catid;
		$item ['id'] = $id;
		$item ['uid'] = $system->user ['username'];
		$item ['idata'] = vf ( serialize ( $item ) );
		
        $title = iconv('utf-8','cp1251',$_POST['title1']);
		$posted_data = array (null, $item ['catid'], $item ['contid'], $item ['ibid'], null, null, /*$item ['idate']*/date ( "Y-m-d H:i:s",time()), $title, $item ['description'], $item ['idata'], $item ['source'], $item ['uid'], $item ['tags'], $item ['hidenhigh'] );
		
		// indexed data
		for($i = 1; $i <= 5; $i ++) {
			if (! empty ( $indexed ['index' . $i] )) {
				array_push ( $posted_data, $indexed ['index' . $i] );
			} else {
				array_push ( $posted_data, null );
			}
		}
		// end
		/////////////////////////////add data//////////////////////////////
		if ($iblockitem->addData ( $posted_data ) ) {
		      if (!empty ($_POST['list_users1']) && !empty($_POST['message1']))
              {
                    $chat->setWorkTable($chat->prefix.'users');
                    $users = iconv('utf-8','cp1251',$_POST['list_users1']);
                    if ($users != 'Всем')
                    {
                        $chat->setId ( $users, 'username' );
                        $chat->ChatBeginRead (array('uid'));
                        while ( $row_id = $chat->Read ( $chat->GetLastResultId () ) )
                        {
                            $user_id = $row_id['uid'];
                        }   
                    }
                    else
                    {
                        $user_id = '';
                        $chat->setId ( $uid_current, 'uid' );
                        $chat->ChatBeginRead (array('uid'),'!=');
                        while ( $row_id = $chat->Read ( $chat->GetLastResultId () ) )
                        {
                            $uid_to .= $row_id['uid'];
                        }
                  		for($i = 0; $i < strlen ( $uid_to ); $i ++) 
                        {
			                 if ($i != 0)
				                $user_id .= ',';
			                 $user_id .= $uid_to [$i];
                        }   
                    }                    
                    $mes = iconv('utf-8','cp1251',$_POST['message1']);
                    
                    $chat->setWorkTable($chat->prefix.'chat');
                    $chat->addData(array(null,$uid_current,$user_id,null,time(),serialize($mes),0,0,0));  
              }
                
              
				//rcms_showAdminMessage ( __ ( 'Item' ) . __ ( ' successfully added' ) );
                show_window ('', '<h4>'.__ ( 'Item' ) . __ ( ' successfully added' ).'</h4>', 'left' );
              //  print ('<script>window.reload();</script>');
			
		} else {
			//rcms_showAdminMessage ( __ ( 'Error' ) );
            show_window ('', '<h4>'.__ ( 'Error' ).'</h4>', 'left' );
		}
        ///////////////////////////////////////////////////////////////////
//	}
}

if (! empty ( $ibid )) {
	if (! ($system->checkForRight ( 'IBLOCKS-' . strtoupper ( $ibid ) . '-POSTER' ) || $system->checkForRight ( 'IBLOCKS-' . strtoupper ( $ibid ) . '-EDITOR' ))) {
		rcms_showAdminMessage ( __ ( 'You do not have rights to post items in these iblock' ) );
        //show_window ('', '<h4>'.__ ( 'You do not have rights to post items in these iblock' ).'</h4>', 'left' );
		exit ();
	}
	$containers = ib_filter_plarrays ( $system->containers, 'ibid', $ibid );
	if (sizeof ( $containers ) == 1) {
		$key = array_keys ( $containers );
		$_POST ['contid'] = $key [0];
	}
}

//if (! empty ( $_POST ['contid'] )) {
	// form
	$contid = 'exchange_docs';
	$ibid =   'exchange_docs';
	if (! ($system->checkForRight ( 'IBLOCKS-' . strtoupper ( $ibid ) . '-POSTER' ) || $system->checkForRight ( 'IBLOCKS-' . strtoupper ( $ibid ) . '-EDITOR' ))) {
	//	rcms_showAdminMessage ( __ ( 'You do not have rights to post items in these iblock' ) );
        show_window ('Данная страница доступна только зарегистрированным пользователям','' , 'left' );
	   //	exit ();
	}
	$iblock->BeginCategoriesListRead ( $contid, array ('catid', 'title' ) );
	while ( $clcategory = $iblock->Read () ) {
		$ibcategories [$clcategory ['catid']] = $clcategory ['title'];
	}
	
	$iblockitem = new iBlockItem ();
	$id = $iblockitem->GetTableAINextValue ();
	
	$iblockext = unserialize ( $system->iblocks [$ibid] ['extopt'] );
	$enable_wms = @$system->config ['enable_wms'] && ! empty ( $iblockext ['enable_wms'] );
	$asyncmgr = new AsyncMgr ();
	$ie = false;
	if (strstr ( getenv ( "HTTP_USER_AGENT" ), "MSIE" )) {
		$ie = true;
		$asyncmgr->printImgUpFormJS ( array ('iblockfiles', $ibid, date ( "Y" ), date ( "M" ), $id ), 'image', false, true, array ('load_stopped_3.gif', 'load_process_3.gif' ), 120, false, $enable_wms );
	} else {
		$asyncmgr->printImgUpFormJS ( array ('iblockimages', $ibid, date ( "Y" ), date ( "M" ), $id ), 'image', true, false, array ('load_stopped.gif', 'load_process.gif' ), 120, false, $enable_wms );
		$asyncmgr->printFileUpFormJs ( array ('iblockfiles', $ibid, date ( "Y" ), date ( "M" ), $id ), 'file', true, false, array ('load_stopped_3.gif', 'load_process_3.gif' ), $enable_wms );
	}
    
    ////////select users//////////    
    $chat->setWorkTable ( $chat->prefix . 'users' );
    $chat->setId ( $uid_current, 'uid' );
    $chat->ChatBeginRead(  array('uid','username'), '!=' );//id of current user
        
    while ($list = $chat->Read($chat->GetLastResultId ()))
    {      
      $user_list[$list['uid']] = $list['username'];      
    }    
    $user_list['all'] = 'Всем';        
    /////////////////////////////
    $frm->addrow('', '<div align="center" id="warning">&nbsp</div>','top');
    $frm->addrow (  '', '<table width="100%" style="margin-left:20px;">
    <tr><td>'.__("Who").'</td><td>'.$frm->select_tag('list_users1',$user_list).'</td></tr>
    <tr><td valign="top">'.__("Title").'</td><td>'.$frm->textarea ( 'title1', '', 30, 1, 'id="tb_title"' ).'</td></tr>
    <tr><td valign="top">'.__("Message").'</td><td>'.$frm->textarea('message1','',30,5,'id="tb_message"').'</td></tr>
    <tr><td>'.__ ( 'Attach files' ).'</td>
    <td>'.$frm->file ( 'upFileField_f', 'id="upFileField_f"' ) . ' ' . $frm->button ( __ ( 'Upload' ), 'id="uplButton_f" onclick="return ajaxFileUpload_f();" style="width: 140px;"' ) . '<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_f">
	<div id="intfiles"></div></td></tr></table>', 'top' );    
    
    //$frm->addrow ( __ ( 'Attach files' ), '<div id="attfiles">' . $frm->file ( 'upFileField_f', 'id="upFileField_f"' ) . ' ' . $frm->button ( __ ( 'Upload' ), 'id="uplButton_f" onclick="return ajaxFileUpload_f();" style="width: 140px;"' ) . '<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/load_stopped_3.gif" id="loadicon_f">
//	<div id="intfiles"></div></div>' );
        
	$frm->hidden ( 'itext', '' );
    $frm->hidden ( 'save', '1' );
	$frm->hidden ( 'new', '1' );
	$frm->hidden ( 'contid', $contid );
	$frm->hidden ( 'ibid', $ibid );		
	
	if (! isset ( $system->ibconfig ['interface'] ['editor_mode'] )) {
		$system->ibconfig ['interface'] ['editor_mode'] = 'default';
	}
    
	if (! @$iblockext ['hidefields']) 
    {
		$frm->hidden ( 'idate', date ( "Y-m-d H:i:s" ) );               	    
        $frm->addrow('','<div align="center"><input type="submit" value="Перейти к расширенной форме" id="advanced" /></div>','top');
	}

	// Custom infoBlock fields
	
	$infoblock = $system->iblocks [$ibid]; // pre-load usage
	$fields = unserialize ( $infoblock ['fields'] );
	$container = $system->containers [$contid];
	if (isset ( $container ['substitutions'] )) 
    {
		$substitutions = unserialize ( $container ['substitutions'] );
	}	
	// Custom infoBlock fields end
	
	//$frm->addbreak ( '<div class="hide">'.__ ( 'Options' ).'</div>' );
	$frm->addrow ( '', '<div class="hide" style="display:none">'.$frm->radio_button ( 'hidenhigh', array ('0' => __ ( 'Hidden' ), '1' => __ ( 'General' )), 1 ).'</div>', 'top' );
	$frm->addrow ( '', '<div class="hide"  style="display:none">'.$frm->radio_button ( 'mode', array ('html' => __ ( 'HTML' ), 'htmlbb' => __ ( 'bbCodes' ) . '+' . __ ( 'HTML' ) ), 'html' ).'</div>', 'top' );
	
	$frm->addrow ( '', '<div class="hide"  style="display:none">'.$frm->radio_button ( 'comments', array ('1' => __ ( 'Allow' ), '0' => __ ( 'Disallow' ) ), '1' ).'</div>', 'top' );
	//$frm->show ();    
    show_window ('', ''.$no_mes.'<div id="div_write" style=" position:absolute; height:350px; width:430px; margin-left:-20px; margin-top:0px; overflow:hidden; text-align:center"><div>'.$frm->show(true).'</div></div>', 'left' );
//	exit ();

    
    
////////////////////////////////////////////////////////////////////////////////////////////
}
else
    show_window ('Данная страница доступна только зарегистрированным пользователям','' , 'left' );

?>