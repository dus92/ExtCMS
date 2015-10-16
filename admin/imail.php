<?php

$now_time = time ();
$last_act = 0;

function safe_var($var) // защита переменных от XSS и
                         // SQL-инъекций
{
	$var = trim ( $var );
	$var = mysql_real_escape_string ( $var );
	//$var = hcms_htmlsecure( $var );
	return $var;
}

// ///////////////////////////////////////////////////////////////////////
global $system;
$now_date = date ( "d.m.y", time () );
//$_SESSION ['name'] = $system->user ['username'];
$name = $system->user ['username'];

// global $name;
global $i;

$imail = new iMail ();
$sel_uid = $imail->iMailQuery ( $name, 'username', 'users', array ('uid' ) );

$message_code = '';
global $res;
$res = $sel_uid ['uid'];

// загрузка имеющихся сообщений

$sel_result = $imail->iMailSelect ( '', '', 'imail', '*', 'ORDER BY date_time', 'ASC' );
if ($imail->iMailNumRows () > 0) {
	// $sel_row = mysql_fetch_array($sel_result);
	$message_code = '';        
	
	while ( $sel_row = $imail->iMailReadArray ( $sel_result ) ) {
	    $mes = unpack_data ( $sel_row ['message'] ); //message text        
        $space = 0;
        if (mb_strlen($mes)>10){ //делаем перенос строки если в тексте сообщения нет пробелов и текст длиннее 10 симв.
            for ($i=0;$i<mb_strlen($mes);$i++){
               if ($mes[$i] === ' '){
                    $space = 1;
                    break;
                }
            }
            if (!$space){
                $mes = wordwrap($mes, 10, "\n", 1);    
            }    
        }        
        
		if ($sel_row ['uid_from'] == $res){	// собственное сообщение		
			if (date ( "d.m.y", $sel_row ['date_time'] ) == $now_date)
                $date = date ( __('Today')." H:i", $sel_row ['date_time'] );
                else
                    $date = date ( "d.m.y H:i", $sel_row ['date_time'] );				
                $message_code .= '<p class="imail_post_my"><table width="100%"  cellpadding="0" cellspacing="0"><tr>'.
                '<td valign="top" width="45%">'.$date.'</td>'.
                '<td valign="top"><span class="imail_nickname">' . $name . ': </span>' .$mes.'</td>'.
                '</tr></table></p>';
		} else{	// чужое сообщение
			$from = $sel_row ['uid_from'];			
			$sel_user = $imail->iMailSelect ( $from, 'uid', 'users', array ('username' ), 'ORDER BY uid', 'ASC' );
			while ( $sel_others = $imail->iMailReadArray ( $sel_user ) ) {
				if (date ( "d.m.y", $sel_row ['date_time'] ) == $now_date)
                    $date = date ( __('Today')." H:i", $sel_row ['date_time'] );
                    else
                        $date = date ( "d.m.y H:i", $sel_row ['date_time'] );
                $message_code .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                '<tr><td width="45%" valign="top">'.$date.'</td>'.
                '<td valign="top"><span class="imail_nickname other">'.$sel_others ['username'].': </span>'.$mes.'</td>'.
                '</tr></table></p>';				
			}
		}		
		$last_act = $sel_row ['id']; // номер текущего последнего сообщения
	}

}

function checkbox($name, $value, $id, $caption, $checked = 0, $extra = '') {
	echo '<p style="margin-bottom:1px; margin-top:1px"><input type="checkbox" name="' . $name . '" value="' . $value . '" id="' . $id . '" ' . ((! empty ( $checked )) ? 'checked' : '') . ' ' . $extra . ' /><label for="' . $id . '">' . $caption . '</label></p>';
}

function show_users() {
	global $system;
	// $sel_result = mysql_query("select username,uid from rcms_users");
	$imail = new iMail ();
	$sel = $imail->iMailSelect ( '', '', 'users', array ('username', 'uid' ) );
	if ($imail->iMailNumRows () > 0) {
		echo '<form method="post" id = "i1" name="form_ch">';
		$i = 1;
		// while($row = mysql_fetch_array($sel_result))
		while ( $sel_result = $imail->iMailReadArray ( $sel ) ) {
			// echo '<form>';
			if ($system->user['username'] != $sel_result ['username']) {
				checkbox ( 'ch[]', $sel_result ['uid'], $i, $sel_result ['username'], 0, '' );
				$i ++;
			}
			// echo '</form>';
		}
		echo '</form>';
		// echo '<input type="submit" id="but_save" value="OK"/>';
	}
}

// /////////////////////////groups/////////////////////////////////////////////////////
function show_groups() {
	$imail = new iMail ();
	// $sel_result = mysql_query("select gids from rcms_users");
	$sel_result = $imail->iMailSelect ( '', '', 'users', array ('gids' ) );
	if ($imail->iMailNumRows () > 0) {
		echo '<form method="post" id = "i2" name="form_gids">';
		$i = 1;
		
		// $sel_gids = mysql_query("select title,gid from rcms_usergroups order
		// by gid");
		
		// while($row = mysql_fetch_array($sel_gids))
		$sel = $imail->iMailSelect ( '', '', 'usergroups', array ('title', 'gid' ), 'ORDER BY gid' );
		if ($imail->iMailNumRows () > 0) {
			while ( $sel_gids = $imail->iMailReadArray ( $sel ) ) {
				$gid = $sel_gids ['gid'];
				// $sel_users = mysql_query("select username,gids,uid from
				// rcms_users");
				$sel_res = $imail->iMailSelect ( '', '', 'users', array ('username', 'gids', 'uid' ) );
				$u = '';
				$uid = '';
				
				// while($row1 = mysql_fetch_array($sel_users))
				while ( $sel_users = $imail->iMailReadArray ( $sel_res ) ) {
					$gids = mb_substr ( $sel_users ['gids'], 7, 1 );
					if ($gid == $gids) {
						$u .= $sel_users ['username'] . ' ';
						$uid .= $sel_users ['uid'];
					}
				}
				if ($uid != '') {
					checkbox ( $uid, $sel_gids ['gid'], $i, '<b>'.$sel_gids ['title'].'</b>' . ' ( ' . $u . ' )', 0, '' );
					$i ++;
				} else
					//echo '<p id="no_groups">' . $sel_gids ['title'] . '  (No users of this group)</p>';
                    echo '<p id="no_groups">' . $sel_gids ['title'] . ' '.__('(There are no users in this group)').'</p>';
			
			}
		} else
			//echo '<p id="no_groups">There are no groups!</p>';
            echo '<p id="no_groups">'.__('No created groups!').'</p>';
			
			// $i++;
		
		echo '</form>';
		// echo '<input type="submit" id="but_save" value="OK"/>';
	}
}

// ///////////////////////////////////////селектор////////////////////////////////////////////
function selector($text = array()) {
	$i = 0;
	// $data = '<select name="imail" id="1" style="background: #DDDDD9;">' .
	// "\n";
	$data = '';
	for($i = 0; $i < count ( $text ); $i ++) {
		$data .= '<option value="' . $i . '" id="' . $i . '" ' . '>' . $text [$i] . '</option>' . "\n";
	}
	echo $data;
}
// /////////////селектор групп///////////
function sel_groups() {
	$imail = new iMail ();
	$i = 0;
	// $sel_gids = mysql_query("select title,gid from rcms_usergroups order by
	// gid");
	// while($row = mysql_fetch_array($sel_gids))
    $data = '';
	$sel = $imail->iMailSelect ( '', '', 'usergroups', array ('title', 'gid' ), 'ORDER BY gid' );
	while ( $row = $imail->iMailReadArray ( $sel ) ) {
		// selector($row['title']);
		$data .= '<option value="' . $row ['gid'] . '" id="' . $i . '" ' . '>' . $row ['title'] . '</option>' . "\n";
		$i ++;
	}
	echo $data;
}

function button($id, $name, $class, $value) {
	echo '  <input id="' . $id . '" name="' . $name . '" type="button" class="' . $class . '" value="' . $value . '" /> ';
}

// конвертирование кодировок
function convert($from, $to, $var) {
	if (is_array ( $var )) {
		$new = array ();
		foreach ( $var as $key => $val ) {
			$new [convert ( $from, $to, $key )] = convert ( $from, $to, $val );
		}
		$var = $new;
	} else if (is_string ( $var )) {
		$var = iconv ( $from, $to, $var );
	}
	return $var;
}
// echo date("d.m.y H:i:s",time());
// адресация
$arr_to = array (__('To all'), __('Specific user'), __('User groups') );
//$arr_to = convert ( 'utf-8', 'cp1251', $arr_to );
// selector($arr_to);
// просмотр сообщений
$arr_watch = array (__('All'), __('My messages'), __('To a group of users') );
//$arr_watch = convert ( 'utf-8', 'cp1251', $arr_watch );
// selector($arr_watch);

// кнопки
$arr_but = array (__('Send'), __('Clear') );
//$arr_but = convert ( 'utf-8', 'cp1251', $arr_but );
// надписи
$arr_label = array (__('Addressing'), __('Viewing of messages'), __('Current user: ') );
//$arr_label = convert ( 'utf-8', 'cp1251', $arr_label );

//$message_code = convert ( 'utf-8', 'cp1251', $message_code );

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$system->config['encoding']?>" />
<link rel="stylesheet" href="<?=RCMS_ROOT_PATH?>/admin/mail.css" type="text/css" />
<link rel="stylesheet" href="<?=RCMS_ROOT_PATH?>/admin/style.css" type="text/css" />
<title>Hakkah~Cms~Mail</title>

<script type="text/javascript" src="<?=RCMS_ROOT_PATH?>/modules/js/jquery.js"></script>
<script type="text/javascript" src="<?=RCMS_ROOT_PATH?>/modules/js/mail_scripts.js"></script>
</head>
<body id="body">
    <table width="100%"><tr><th style="text-align: left">&nbsp;&#0187; <?=__('Messages')?></th></tr></table>
    <form name="form_watch" method="post">		
		<p class="p"><?=$arr_label[1] ?></p>
        <div style="width: 100%;">
            <div id="div_sel_watch">
                <select name="sel_watch" id="sel_watch">
                    <? selector($arr_watch);?>
                </select>
            </div>
            <div id="div_sel_groups">
                <select name="sel_groups" id="sel_groups" class="selector">
                    <? sel_groups()?>
                </select>
            </div>
            <div style="clear: both;"></div>
        </div>
	</form>
	<div id="imail_body">		
        <div id="imail_text_field">
			<?=$message_code; ?>
		</div>

		<input id="last_act" name="last_act" type="hidden"
			value="<?=$last_act?>" />
		<!--Номер последнего сообщения-->
		<input id="block" name="block" type="hidden" value="no" />
		<!--Блокировка повторного выполнения функции get_imail_messages()-->
        <!--<form name="form_to">-->
		  <!--<p class="p"><?//=$arr_label[0] ?></p>-->
		  <!--
<select style="width: 40px;" name="sel_to" id="sel_to" class="selector">
            <?// selector($arr_to);?>
          </select>
-->
        
    
   	    <div id="div_users">
            <? show_users(); ?>
        </div>

	    <div id="div_groups">
            <? show_groups(); ?>
        </div>
        <form name="form_to">
		<!-- <input id="imail_text_input" name="imail_text_input" type="text" /> -->        
        <div style="float: left; width: 58%">
            <textarea rows="4" style="resize: none;" id="imail_text_input" name="imail_text_input" ></textarea>
        </div>
        <div style="float: left; width: 20%; margin: 7px; 0px; 0px; 5px;">
            <select style="width: 87px;" name="sel_to" id="sel_to" class="selector">
                <? selector($arr_to);?>
            </select>
            <br />
            <? button('imail_button', 'imail_button', 'btnmain', $arr_but[0])  ?>
        </div>
        <div style="clear: both;"></div>
        </form>	
<!--
		<div style="margin-left: 30px;">
            <? button('imail_button', 'imail_button', 'btnmain', $arr_but[0])  ?> 
            <? //button('clear', 'clear', 'btnmain', $arr_but[1])?>
        </div>
-->
	</div>	    
</body>
</html>
