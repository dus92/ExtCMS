<?php
// //////////////////////////////////////////////////////////////////////////////
// Copyright (C) ReloadCMS Development Team //
// http://reloadcms.sf.net //
// //
// This program is distributed in the hope that it will be useful, //
// but WITHOUT ANY WARRANTY, without even the implied warranty of //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. //
// //
// This product released under GNU General Public License v2 //
// //////////////////////////////////////////////////////////////////////////////

$inst = new Instagram();
$inst->setWorkTable($inst->prefix.'contests');

if (!empty($_POST['time_updlikes'])){
    $arr = array();
    $time_updlikes = $_POST['time_updlikes'];
    if ((is_numeric($time_updlikes) === true) && (floor(abs($time_updlikes)) == $time_updlikes)){
        $arr['time_updlikes'] = $time_updlikes;
        $result = json_encode($arr);
        write_ini_file($arr, CONFIG_PATH . 'contests.ini');
        rcms_showAdminMessage ( __ ( 'Настройки конкурсов успешно сохранены' ));
    }
    else{
        rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( 'Введите целое положительное число' ) );
    }
}

$frm = new InputForm ( '', 'post', __ ( 'Submit' ), '', '', 'multipart/form-data', 'frm_settings' );
$frm->addbreak('Настройки конкурсов');

$settings = parse_ini_file(CONFIG_PATH . 'contests.ini', true);

$frm->addrow('Время обновления лайков, сек.', $frm->text_box('time_updlikes', $settings['time_updlikes']), '', '', '', '25%,75%');
$frm->show();

?>