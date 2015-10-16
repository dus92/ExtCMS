<?php

session_start ();

$imail = new iMail();        
// текущее время
$now_time = time ();
global $now_date;
$now_date = date ( "d.m.y", $now_time );

function safe_var($var) // защита переменных от XSS и SQL-инъекций
{
	$var = trim ( $var );
	$var = mysql_real_escape_string ( $var );    
	$var = htmlspecialchars ( $var );
    $var = stripslashes( $var );
	return $var;
}

// Функция экранирования переменных
function quote_smart($value) {
  //если magic_quotes_gpc включена - используем stripslashes
  if (get_magic_quotes_gpc()) {
    $value = stripslashes($value);
  }
  //экранируем
  $value = mysql_real_escape_string($value);
  return $value;
}
 

function formatStr($str){ //форматируем строку (параметры через запятую)
    $str = str_replace ( ",", "", $str );
    for($i = 0; $i < mb_strlen ( $str ); $i ++) {
        if ($i != 0)
            $str_res .= ',';
        $str_res .= $str [$i];
    }
    return $str_res;
}

function wrapMessage($mes){ //делаем перенос строки если в тексте сообщения нет пробелов и текст длиннее 10 симв.
    $space = 0;
    if (mb_strlen($mes)>10){ //делаем перенос строки если в тексте сообщения нет пробелов и текст длиннее 10 симв.
        for ($i=0;$i<mb_strlen($mes);$i++){
            if ($mes[$i] === ' '){
                $space = 1;
                break;
            }
        }
        if (!$space){
            $mes = wordwrap($mes, 20, "\n", 1);    
        }    
    }
    return $mes;    
}

function getCurDate($date){ //форматирование даты
    $res_date = '';
    global $now_date;
    if (mb_substr ( date ( "d.m.y", $date ), 0, 8 ) == $now_date)
        //$res_date = iconv('cp1251', 'utf-8', date ( __('Today')." H:i", $date ));
        $res_date = date( __('Today')." H:i", $date);
        else    
            $res_date = date ( "d.m.y H:i", $date );
    return $res_date;    
}
// //////////////////////////////////////////////////////////////////////////////

$name = safe_var ( $system->user['username'] );
// $name = safe_var($system->user['username']);
global $res;
$imail->setWorkTable($imail->prefix.'users');
$imail->setId($name, 'username');
$imail->iMailBeginRead(array('uid'));
while($row = $imail->Read()){
    $res = $row ['uid']; //id текущего пользователя
}

global $ch1;
// отправка сообщения
if ($_POST ['action'] == 'add_message') {
	//$message_text = pack_data ( safe_var ( $_POST ['message_text'] ) );
    $message_text = addslashes(pack_data($_POST ['message_text']));
	
	$uids_from_groups = $_POST ['uids_from_groups'];
	$ch_groups = $_POST ['ch_groups'];
	$ch1 = $_POST ['ch1'];
	
	$data_str1 = array ('ch1' => $ch1 );
	echo json_encode ( $data_str1 );
	
	// строка получателей
    $str = formatStr($ch1);
	// строка групп
    $str_groups = formatStr($ch_groups);
	// строка идентификаторов пользователей выбранных групп
	$str_uids_from_groups = formatStr($uids_from_groups);
	// ///////////////////////////////
	if ($_POST ['sel_index'] == 1){ // конкретный пользователь		
        $imail->setWorkTable($imail->prefix.'imail');
        $imail->addData(array((INT)null, $res, $str, '', $now_time, $message_text, 0, 0, 0));
    }
	else if ($_POST ['sel_index'] == 0) 	// все
	{
		$imail->setWorkTable($imail->prefix.'users');
        $imail->setId($res, 'uid');
        $imail->iMailBeginRead(array('uid'),'!=');
        
		while($row = $imail->Read()){  
			$res1 .= $row ['uid'];
		}
        $str = '';
		for($i = 0; $i < mb_strlen ( $res1 ); $i ++) {
			if ($i != 0)
				$str .= ',';
			$str .= $res1 [$i];
		}		
        $imail->setWorkTable($imail->prefix.'imail');
        $imail->addData(array((INT)null, $res, $str, '', $now_time, $message_text, 0, 0, 0));
	} else if ($_POST ['sel_index'] == 2) {	 //группе пользователей	
        $imail->setWorkTable($imail->prefix.'imail');
        $imail->addData(array((INT)null, $res, $str_uids_from_groups, $str_groups, $now_time, $message_text, 0, 0, 0));
	}
}
// получение новых сообщений
if ($_POST ['action'] == 'get_imail_message') {
	
	$last_act = safe_var ( $_POST ['last_act'] ); // номер бывшего последнего сообщения
	$imail->setWorkTable($imail->prefix.'imail');
    $imail->setId($last_act, 'id');
    $imail->iMailBeginRead('*', '>', '', 'ORDER BY date_time ASC, id ASC');
    	
	if ($imail->iMailNumRows () > 0){   	
		$message_code = '';
		while($sel_row = $imail->Read()) {			
			$mes = wrapMessage(unpack_data ( $sel_row ['message'] ));
            //$mes = wrapMessage($sel_row ['message'] );            
            $date = '';
			if ($sel_row ['uid_from'] == $res){ 			// собственное сообщение			
                $date = getCurDate($sel_row ['date_time']);
				$message_code .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0"><tr>'.
                '<td width="45%" valign="top">'.$date.'</td>'.
                '<td valign="top"><span class="imail_nickname">' . $name . ': </span>' . $mes . '</td></tr></table></p>';
			} else{	// чужое сообщение			
				$from = $sel_row ['uid_from'];
                $imail->setWorkTable($imail->prefix.'users');                            
                $imail->setId($name, 'username');
                $imail->iMailBeginRead(array('uid'));
				
				$to = explode ( ",", $sel_row ['uid_to'] );
                while ( $row_to = $imail->Read() ) {
					for($i = 0; $i < sizeof ( $to ); $i ++) {
						if ($to [$i] == $row_to ['uid']) {
						    $imail->setId($from, 'uid');
                            $imail->iMailBeginRead(array('username'));							
							while ( $row = $imail->Read() ) {
                                $date = getCurDate($sel_row ['date_time']);
								$message_code .= '<p class="imail_post_my">'.
                                '<table cellpadding="0" cellspacing="0" width="100%"><tr><td width="45%" valign="top">'.$date.
                                '</td><td valign="top"><span class="imail_nickname other">' . $row ['username'] . ': </span>'.
                                $mes . '</td></tr></table></p>';								
							}
						}
					}
				}
			}
			
			$last_act = $sel_row ['id']; // номер текущего последнего сообщения
		}		
		// отправляем полученные переменные в формате json
		$data_str = array ('message_code' => $message_code, 'last_act' => $last_act );
		echo json_encode ( $data_str );
	}
}
// ////////////////////загрузка имеющихся сообщений////////////////////////////////
if ($_POST ['action'] == 'show_messages') {
    $imail->setWorkTable($imail->prefix.'imail');
    $imail->setId('', '');    
    $imail->iMailBeginRead('*', '', '', 'ORDER BY id ASC');
	$last_res_id = $imail->GetLastResultId();
	if ($_POST ['sel_index'] == 0) 	// все
	{				
		if ($imail->iMailNumRows() > 0){  			
			while($sel_row = $imail->Read($last_res_id)){ 
				$mes = wrapMessage(unpack_data ( $sel_row ['message'] ));                                
				if ($sel_row ['uid_from'] == $res){	// собственное сообщение				
                    $date = getCurDate($sel_row ['date_time']);
					$message_code .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                    '<tr><td width="45%" valign="top">'.$date.'</td><td valign="top"><span class="imail_nickname">'.
                    $name . ': </span>' . $mes . '</td></tr></table></p>';					
				} else{	// чужое сообщение				
					$from = $sel_row ['uid_from'];					
                    $imail->setWorkTable($imail->prefix.'users');
                    $imail->setId($from, 'uid');    
                    $imail->iMailBeginRead(array('username'));  				
		            while($row = $imail->Read()){  						    							
                        $date = getCurDate($sel_row ['date_time']);
                        $message_code .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                        '<tr><td width="45%" valign="top">'.$date.'</td><td valign="top"><span class="imail_nickname other">'.
                        $row['username'] . ': </span>' . $mes . '</td></tr></table></p>';                         						
					}
				}
			}
		}
	} else if ($_POST ['sel_index'] == 1){ 	// мои + адресованные мне			
        $imail->setWorkTable($imail->prefix.'imail');
        $imail->setId('', '');    
        $imail->iMailBeginRead('*', '', '', 'ORDER BY id ASC');
        $last_res_id = $imail->GetLastResultId();  
		while($sel_row = $imail->Read($last_res_id)){  
			$mes = wrapMessage(unpack_data ( $sel_row ['message'] ));
			$to = explode ( ",", $sel_row ['uid_to'] );
			
			if ($sel_row ['uid_from'] == $res){ // мои			
				$date = getCurDate($sel_row ['date_time']);
                $message_code .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                '<tr><td width="45%" valign="top">'.$date.'</td><td valign="top"><span class="imail_nickname">'.
                $name . ': </span>' . $mes . '</td></tr></table></p>';                				
			} else{	// адресованные мне			
				for($i = 0; $i < sizeof ( $to ); $i ++) {
					if ($to [$i] == $res) {
						$from = $sel_row ['uid_from'];						
                        $imail->setWorkTable($imail->prefix.'users');
                        $imail->setId($from, 'uid');    
                        $imail->iMailBeginRead(array('username'));						
						while($row = $imail->Read()){    
							$date = getCurDate($sel_row ['date_time']);	
                            $message_code .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                            '<tr><td width="45%" valign="top">'.$date.'</td><td valign="top"><span class="imail_nickname other">'.
                            $row['username'] . ': </span>' . $mes . '</td></tr></table></p>';													
						}
					}
				}
			}		
		}
	} else if ($_POST ['sel_index'] == 2){ 	// группы пользователей (по умолчанию группа с наименьшим id)			
        $imail->setWorkTable($imail->prefix.'usergroups');
        $imail->setId('', '');    
        $imail->iMailBeginRead(array('MIN(gid)'));
		while($row_gid = $imail->Read()){  
			$min_gid = $row_gid ['MIN(gid)'];
		}	
        $imail->setWorkTable($imail->prefix.'imail');
        $imail->iMailBeginRead();
		$last_res_id = $imail->GetLastResultId();
		while ( $row = $imail->Read($last_res_id) ) {  
			$str_gids = explode ( ",", $row ['to_gids'] );
			if ($min_gid > 0) {
				for($i = 0; $i < sizeof ( $str_gids ); $i ++) {
					if ($min_gid == $str_gids [$i]) {
						$mes = wrapMessage(unpack_data ( $row ['message'] ));
						if ($row ['uid_from'] == $res){ // мои
							$date = getCurDate($row ['date_time']);
                            $message_code .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                            '<tr><td width="45%" valign="top">'.$date.'</td><td valign="top"><span class="imail_nickname">'.
                            $name . ': </span>' . $mes . '</td></tr></table></p>';	                            							
						} else{ // чужие						
							$from = $row ['uid_from'];							
                            $imail->setWorkTable($imail->prefix.'users');
                            $imail->setId($from, 'uid');    
                            $imail->iMailBeginRead(array('username'));							
						    while ( $row_others = $imail->Read() ) {
								$date = getCurDate($row ['date_time']);
                                $message_code .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                                '<tr><td width="45%" valign="top">'.$date.'</td><td valign="top"><span class="imail_nickname other">'.
                                $row_others['username'] . ': </span>' . $mes . '</td></tr></table></p>';																
							}
						}
					}
				}
			} else
				$message_code = "Нет созданных групп пользователей!";
		}	
	}
	
	if ($_POST ['sel_index_gr'] >= 0) {		
        $imail->setWorkTable($imail->prefix.'imail');
        $imail->setId('', '');
        $imail->iMailBeginRead();	
        $last_res_id = $imail->GetLastResultId();	
		while ( $row = $imail->Read($last_res_id) ) {  
			$str_gids = explode ( ",", $row ['to_gids'] );
			for($i = 0; $i < sizeof ( $str_gids ); $i ++) {
				if ($_POST ['sel_value'] == $str_gids [$i]) {
					$mes = wrapMessage(unpack_data ( $row ['message'] ));
					if ($row ['uid_from'] == $res){	// мои					
						$date = getCurDate($row ['date_time']);
                        $message .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                        '<tr><td width="45%" valign="top">'.$date.'</td><td valign="top"><span class="imail_nickname">'.
                        $name . ': </span>' . $mes . '</td></tr></table></p>';	                          						
					} else{	// чужие					
						$from = $row ['uid_from'];						
                        $imail->setWorkTable($imail->prefix.'users');
                        $imail->setId($from, 'uid');    
                        $imail->iMailBeginRead(array('username'));						
						while ( $row_others = $imail->Read() ) {
                            $date = getCurDate($row ['date_time']);
                            $message .= '<p class="imail_post_my"><table width="100%" cellpadding="0" cellspacing="0">'.
                            '<tr><td width="45%" valign="top">'.$date.'</td><td valign="top"><span class="imail_nickname other">'.
                            $row_others['username'] . ': </span>' . $mes . '</td></tr></table></p>';							                            
						}
					}										
				}
			}
		}				
	}		
	$str = '<p style="margin-bottom:1px; margin-top:1px; text-align:center;">---------------------------------------------------------</p>';
	
	$sel_index = $_POST ['sel_index'];
	$data_str1 = array ('sel_index' => $sel_index, 'message_code' => $message_code, 'message' => $message, 'str' => $str );
	echo json_encode ( $data_str1 );
}

?>
