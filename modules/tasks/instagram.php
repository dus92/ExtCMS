<?php 
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) ReloadCMS Development Team                                 //
//   http://reloadcms.sf.net                                                  //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////


//include_once('modules/general/instagram/simple_html_dom.php');
define('CONFIG_PATH', './config/');
require_once('./modules/engine/api.instagram.php');

require_once('./modules/general/instagram/simple_html_dom.php');
$html = new simple_html_dom();

$clientID = 'b3d184209c7b43d1be39c19dc000541b';
$apiHost = 'https://api.instagram.com';

function __($string){
	global $lang;
	if(!empty($lang['def'][$string])) {
		return $lang['def'][$string];
	} else {
		return $string;
	}
}

set_time_limit(0);
$inst = new Instagram();
$curtime = time();

$ContestID = '';
$tag = '';
$inst->setWorkTable($inst->prefix.'contests');
$inst->setId($curtime, 'DateBegin');
$inst->InstagramBeginRead('*', '<=', 'and DateEnd>'.$curtime);
if($inst->InstagramNumRows() > 0){ //есть доступные конкурсы
    while($contest = $inst->Read()){
        $ContestID = (INT)$contest['id'];
        $tag = $contest['tag'];
    }    
    $inst->setWorkTable($inst->prefix.'photos');
    $inst->setId($tag, 'tags');    
    
    $img_arr = file_get_contents("https://api.instagram.com/v1/tags/".$tag."/media/recent?access_token=1190957630.b3d1842.79aced89d6e6467cb2465a5f149a13cb");        
    $img_arr = json_decode($img_arr, true);
    //echo sizeof($img_arr['data']);
    $img_data = $img_arr['data']; 
    //echo $inst->GetRowCount();
    if ($inst->GetRowCount() == 0){        
        for ($i=0; $i<sizeof($img_data); $i++){            
            $img_id = substr($img_data[$i]['id'], 0, strpos($img_data[$i]['id'], '_'));
            $username = mysql_real_escape_string($img_data[$i]['user']['username']);
            $fullname = mysql_real_escape_string($img_data[$i]['user']['full_name']);
            $inst->addData(array($img_id, $ContestID, $img_data[$i]['created_time'], $img_data[$i]['link'], $img_data[$i]['likes']['count'], $img_data[$i]['images']['low_resolution']['url'], $tag, $img_data[$i]['user']['id'], $username, $img_data[$i]['user']['profile_picture'], $fullname));    
        }        
    }
    else{
        $inst->setId($ContestID, 'ContestID');
        $inst->InstagramBeginRead();
        $count = 0;
        $count_new = 0;
        $arr = array();        
        while($row = $inst->Read()){
            $arr[$count]['id'] = $row['img_id'].'_'.$row['user_id'];
            $count++;
        }
        for ($i=0; $i<sizeof($img_data); $i++){
            for ($j=0; $j<sizeof($arr); $j++){
                if ($img_data[$i]['id'] != $arr[$j]['id']){
                    $count_new++;            
                }                    
            }
            $img_id = substr($img_data[$i]['id'], 0, strpos($img_data[$i]['id'], '_'));
            $username = mysql_real_escape_string($img_data[$i]['user']['username']);
            $fullname = mysql_real_escape_string($img_data[$i]['user']['full_name']);
            if ($count_new == sizeof($arr)){ //new img, add to DB
                $inst->addData(array($img_id, $ContestID, $img_data[$i]['created_time'], $img_data[$i]['link'], $img_data[$i]['likes']['count'], $img_data[$i]['images']['low_resolution']['url'], $tag, $img_data[$i]['user']['id'], $username, $img_data[$i]['user']['profile_picture'], $fullname));
                $count_new = 0;                    
            }
        }     
    }
    ///update likes
    $inst->setId($ContestID, 'ContestID');
    $inst->InstagramBeginRead();
    $img_url = '';
    while($row = $inst->Read()){
        $img_url = $row['link'];
        $html = file_get_html($img_url);                      
        $i = 0;
        $likes = 0;
        $img_id = '';                  
        foreach($html->find("script") as $element){
            if ($i == 3){
                $res = str_replace("window._sharedData = ", "", $element->innertext());
                $res = str_replace(";", "", $res);
                $res_arr = array();
                $res_arr = json_decode($res, true);
            
                $likes = $res_arr['entry_data']['DesktopPPage'][0]['media']['likes']['count'];                                                                        
                $img_id = $res_arr['entry_data']['DesktopPPage'][0]['media']['id'];
                $inst->setId($img_id, 'img_id');
                $inst->editData(array('likes'=>$likes));                                                                    
            }
            $i++;
        }
    }        
}
else{
    echo json_encode(array('success'=>false, 'msg'=>'Нет доступных конкурсов'));
}
      
?>