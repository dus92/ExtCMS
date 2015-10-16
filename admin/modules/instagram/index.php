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
$inst = new Instagram();
$name = $system->user['username'];
$uid_current = $system->user['uid'];
$now_date = date ( "d.m.y", time () );
$inst->setWorkTable($inst->prefix.'contests');

if(!empty($_POST['delete'])){
    $keys=array_keys($_POST['delete']);
    $count = sizeof($keys);        
    if($count>0){
        for($i=0; $i<$count; $i++){
            $inst->setId($keys[$i], 'id');
            $inst->dropData();
        }
        rcms_showAdminMessage ( __ ( 'Конкурсы успешно удалены' ) );
    }
}
if(empty($_POST['btn_create_contest']) && !isset($_POST['ContestName']) || !empty($_POST['btn_back']) ){
    
    $frm = new InputForm ( '', 'post', __ ( 'Submit' ), '', '', 'multipart/form-data', 'frm_contests' );
    
    $inst = new Instagram();
    $inst->setWorkTable($inst->prefix.'contests');
    $inst->InstagramBeginRead();
    $last_id = $inst->GetLastResultId();    
    //$frm->addsingle('<tr><th colspan="7">'.__ ( 'Управление конкурсами' ).'</th></tr>');
    $frm->addsingle('<tr><td colspan="8" class="row1"><input class="btnmain" type="submit" value="Создать конкурс" name="btn_create_contest" /></td></tr>');
    if($inst->GetRowCount()>0){
        $frm->addsingle('<tr><th>'.__('Название').'</th>'.
        '<th>Описание</th>'.
        '<th>Дата начала</th>'.
        '<th>Дата окончания</th>'.
        '<th>Тег</th>'.
        '<th>Геолокация (широта)</th>'.
        '<th>Геолокация (долгота)</th>'.
        '<th></th>'.
        '</tr>');
        
        while($row = $inst->Read($last_id)){                        
            $latitude = $row['latitude'] ? $row['latitude'] : 'не указана';
            $longitude = $row['longitude'] ? $row['longitude'] : 'не указана';
            $description = $row['ContestDescription'] ? $row['ContestDescription'] : 'не указано';
            $tag = $row['tag'] ? $row['tag'] : 'не указан';
            $frm->addsingle('<tr>'.
            '<td class="row1">'.$row['ContestName'].'</td>'.
            '<td class="row1">'.$description.'</td>'.
            '<td class="row1">'.date("d.m.Y H:i:s", $row['DateBegin']).'</td>'.
            '<td class="row1">'.date("d.m.Y H:i:s", $row['DateEnd']).'</td>'.
            '<td class="row1">'.$tag.'</td>'.
            '<td class="row1">'.$latitude.'</td>'.
            '<td class="row1">'.$longitude.'</td>'.
            '<td class="row1">'.$frm->checkbox('delete['.$row['id'].']', '1', __('Delete')).'</td>'.        
            '</tr>');        
        }
    }
    else{
        $frm->addroe('Нет созданных конкурсов');
    }    
    $frm->addsingle('<tr><td colspan="8" style="text-align:center;"><input class="btnmain" type="submit" value="'.__('Send').'" name="btn_delete" /></td></tr>');
    $frm->show(false, false);
}
else if(!empty($_POST['btn_create_contest']) || isset($_POST['ContestName'])){        
    if(isset($_POST['ContestName'])){
        if (empty($_POST['ContestName'])){
            rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( 'Зполните название конкурса' ) );
        }
        else if( strtotime($_POST['DateBegin'])=='' || strtotime($_POST['DateEnd'])=='' ){
            rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( 'Неверный формат даты' ) );
        }
        else if (empty($_POST['tag']) ){
            rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( 'Зполните поле хэштега' ) );
        }
        else if(!empty($_POST['latitude']) || !empty($_POST['longitude'])){
            if(!is_numeric($_POST['latitude']) || !is_numeric($_POST['longitude']))
                rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( 'Неверный формат параметров геолокации' ) );
        }
        else if ((is_numeric($_POST['GeoAccuracy']) === false) || (floor(abs($_POST['GeoAccuracy'])) != $_POST['GeoAccuracy'])){
            rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( 'Погрешностью геолокации должно быть целое число' ) );
        }
        else{            
            $ContestName = $_POST['ContestName'];
            $ContestDescription = $_POST['ContestDescription'];
            $DateBegin = strtotime($_POST['DateBegin']);
            $DateEnd = strtotime($_POST['DateEnd']);
            $tag = $_POST['tag'];
            $latitude = $_POST['latitude'];
            $longitude = $_POST['longitude'];
            $GeoAccuracy = (INT)$_POST['GeoAccuracy'];
            
            $inst->addData(array('', $ContestName, $ContestDescription, $DateBegin, $DateEnd, $tag, $latitude, $longitude, $GeoAccuracy));
            rcms_showAdminMessage ( __ ( 'Конкурс успешно создан' ));
        }
    }
    $frm = new InputForm ( '', 'post', __ ( 'Submit' ), '', '', 'multipart/form-data', 'frm_create_contest' );    
    $frm->addbreak('Создание конкурса');
    $frm->addsingle('<tr><td class="row2" colspan="2" style="text-align:center;"><input class="btnmain" type="submit" value="<<< Назад" name="btn_back" /></td></tr>');
    $frm->addrow('Название', $frm->text_box('ContestName', '', '', '', '', 'style="width:197px;"'));
    $frm->addrow('Описание', $frm->textarea('ContestDescription', '', '25', '2'));
    $frm->addrow('Дата и время начала', '<input type="datetime-local" name="DateBegin" style="width:197px;" ><span> дд.мм.ГГГГ ЧЧ:мм</span>');
    $frm->addrow('Дата и время окончания', '<input type="datetime-local" name="DateEnd" style="width:197px;" ><span> дд.мм.ГГГГ ЧЧ:мм</span>');
    //$frm->addrow('Фильтрация', $frm->radio_button('filter_elem', array('Хэштег', 'Геолокация')).
//    '<div style="width:450px;" id="div_tag">'.$frm->text_box('tag', '', '', '', '', 'id="inp_tag" style="width:197px;"').'</div>'.
//    '<div style="width:450px;" id="geo"><label>Широта</label>'.
//    $frm->text_box('latitude', '').
//    '<label>Долгота</label>'.
//    $frm->text_box('longitude', '').'</div>');    
    
    $frm->addrow('Хэштег', $frm->text_box('tag', '', '', '', '', 'id="inp_tag" style="width:197px;"'));
    $frm->addrow('Геолокация', '<span>Широта </span>'.$frm->text_box('latitude','').
    '<span> Долгота </span>'.$frm->text_box('longitude','').
    '<span> Погрешность, км </span>'.$frm->text_box('GeoAccuracy', '50'));        
    
    $frm->show();    
}

  
?>