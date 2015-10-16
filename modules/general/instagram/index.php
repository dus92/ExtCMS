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


//include_once('modules/general/instagram/update.php');
include_once('modules/general/instagram/simple_html_dom.php');
set_time_limit(0);
//  $html = new simple_html_dom();
$get_info = new simple_html_dom();
$html = new simple_html_dom();
$inst = new Instagram();

// $file=fopen("res.txt", "w");
$curtime = time();

if(!empty($_POST['isActiveContest'])){ //запрос на доступные конкурсы    
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
        echo json_encode(array('success'=>true, 'ContestID'=>$ContestID, 'tag'=>$tag));
    }
    else
        echo json_encode(array('success'=>false, 'msg'=>'Нет доступных конкурсов'));
}

if (!empty($_POST['getImg'])){ //получение новых фото
    $inst->setWorkTable($inst->prefix.'photos');
    $resp_arr = array();
    $img_arr = array();
    $tags = '';    
    $resp_arr = $_POST['img_arr'];
    $tag = $_POST['tag'];
    $img_arr = $resp_arr['data'];
    $ContestID = (INT)$_POST['ContestID']; 
    
    //load images
    $img_data = array();
    $i = 0;     
    $inst->setId($tag, 'tags');    
    $inst->InstagramBeginRead('*', '=', 'and ContestID='.$ContestID.'', 'ORDER BY likes', 'DESC');     
    while($row = $inst->Read()){
        //echo json_encode(array('data'=>$row['id']));
        $img_data[$i]['id'] = $row['img_id'];
        $img_data[$i]['created_time'] = $row['created_time'];
        $img_data[$i]['link'] = $row['link'];
        $img_data[$i]['likes']['count'] = $row['likes'];
        $img_data[$i]['images']['low_resolution']['url'] = $row['img'];
        $img_data[$i]['user']['id'] = $row['user_id'];
        $img_data[$i]['user']['username'] = $row['username'];
        $img_data[$i]['user']['profile_picture'] = $row['profile_img'];
        $img_data[$i]['user']['full_name'] = $row['fullname'];
       
        $i++;          
    }
    echo json_encode(array('data'=>$img_data));        
}

$result =  '<script type="text/javascript">'.        
    'var script = document.createElement("script");'.
    'script.src = "modules/js/instagram.js";'.
    'document.documentElement.children[0].appendChild(script);'.
    
    'var script = document.createElement("script");'.
    'script.src = "modules/js/underscore-min.js";'.
    'document.documentElement.children[0].appendChild(script);'.
    '</script>';

$result .= '<link rel="stylesheet" href="skins/default/instagram.css" type="text/css" media="screen" title="no title" charset="utf-8">';
$result .=  '<div class="container">'.
    '<div><input style="display:none" id="btn_update" type="button" value="'.__('Update').'" /></div>'.
    '<div class="row">'.
      '<div class="span12">'.
        '<!--<form id="search">'.
          '<div class="input-append">'.
            '<input class="search-tag" type="text" tabindex="1" />'.
            '<input value="'.__('Search').'" class="btn" id="search-button" type="submit">'.
              '<i class="icon-search"></i>'.            
          '</div>'.
        '</form>-->'.

        '<div id="photos-wrap"></div>'.
        '<div id="photos-hide" style="display: none;"></div>'.        
        '<div id="div-hide" style="display:none;"></div>'.
      '</div>'.
    '</div>'.
  '</div>';
  
$result .= '<script type="text/template" id="photo-template">'.
  '<div class="photo" id="photo_<%= id %>">'.
    '<a href="<%= url %>" target="_blank">'.
      '<img id="img_<%= id %>" class="main" src="<%= photo %>" width="250" height="250" style="display:none;" onload="Instagram.App.showPhoto(this);" />'.
    '</a>'.
    '<img class="avatar" width="40" height="40" src="<%= avatar %>" />'.
    '<span id="heart_<%= id %>" class="heart"><strong id="likes_<%= count %>"><%= count %></strong></span>'.
  '</div>'.
  '</script>'; 
show_window(__('Instagram'), $result, 'center');
$system->config['pagename'] = __('Instagram');
?>