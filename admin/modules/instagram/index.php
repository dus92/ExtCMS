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
        rcms_showAdminMessage ( __ ( '�������� ������� �������' ) );
    }
}
if(empty($_POST['btn_create_contest']) && !isset($_POST['ContestName']) || !empty($_POST['btn_back']) ){
    
    $frm = new InputForm ( '', 'post', __ ( 'Submit' ), '', '', 'multipart/form-data', 'frm_contests' );
    
    $inst = new Instagram();
    $inst->setWorkTable($inst->prefix.'contests');
    $inst->InstagramBeginRead();
    $last_id = $inst->GetLastResultId();    
    //$frm->addsingle('<tr><th colspan="7">'.__ ( '���������� ����������' ).'</th></tr>');
    $frm->addsingle('<tr><td colspan="8" class="row1"><input class="btnmain" type="submit" value="������� �������" name="btn_create_contest" /></td></tr>');
    if($inst->GetRowCount()>0){
        $frm->addsingle('<tr><th>'.__('��������').'</th>'.
        '<th>��������</th>'.
        '<th>���� ������</th>'.
        '<th>���� ���������</th>'.
        '<th>���</th>'.
        '<th>���������� (������)</th>'.
        '<th>���������� (�������)</th>'.
        '<th></th>'.
        '</tr>');
        
        while($row = $inst->Read($last_id)){                        
            $latitude = $row['latitude'] ? $row['latitude'] : '�� �������';
            $longitude = $row['longitude'] ? $row['longitude'] : '�� �������';
            $description = $row['ContestDescription'] ? $row['ContestDescription'] : '�� �������';
            $tag = $row['tag'] ? $row['tag'] : '�� ������';
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
        $frm->addroe('��� ��������� ���������');
    }    
    $frm->addsingle('<tr><td colspan="8" style="text-align:center;"><input class="btnmain" type="submit" value="'.__('Send').'" name="btn_delete" /></td></tr>');
    $frm->show(false, false);
}
else if(!empty($_POST['btn_create_contest']) || isset($_POST['ContestName'])){        
    if(isset($_POST['ContestName'])){
        if (empty($_POST['ContestName'])){
            rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( '�������� �������� ��������' ) );
        }
        else if( strtotime($_POST['DateBegin'])=='' || strtotime($_POST['DateEnd'])=='' ){
            rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( '�������� ������ ����' ) );
        }
        else if (empty($_POST['tag']) ){
            rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( '�������� ���� �������' ) );
        }
        else if(!empty($_POST['latitude']) || !empty($_POST['longitude'])){
            if(!is_numeric($_POST['latitude']) || !is_numeric($_POST['longitude']))
                rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( '�������� ������ ���������� ����������' ) );
        }
        else if ((is_numeric($_POST['GeoAccuracy']) === false) || (floor(abs($_POST['GeoAccuracy'])) != $_POST['GeoAccuracy'])){
            rcms_showAdminMessage ( __ ( 'Error' ) . ': ' . __ ( '������������ ���������� ������ ���� ����� �����' ) );
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
            rcms_showAdminMessage ( __ ( '������� ������� ������' ));
        }
    }
    $frm = new InputForm ( '', 'post', __ ( 'Submit' ), '', '', 'multipart/form-data', 'frm_create_contest' );    
    $frm->addbreak('�������� ��������');
    $frm->addsingle('<tr><td class="row2" colspan="2" style="text-align:center;"><input class="btnmain" type="submit" value="<<< �����" name="btn_back" /></td></tr>');
    $frm->addrow('��������', $frm->text_box('ContestName', '', '', '', '', 'style="width:197px;"'));
    $frm->addrow('��������', $frm->textarea('ContestDescription', '', '25', '2'));
    $frm->addrow('���� � ����� ������', '<input type="datetime-local" name="DateBegin" style="width:197px;" ><span> ��.��.���� ��:��</span>');
    $frm->addrow('���� � ����� ���������', '<input type="datetime-local" name="DateEnd" style="width:197px;" ><span> ��.��.���� ��:��</span>');
    //$frm->addrow('����������', $frm->radio_button('filter_elem', array('������', '����������')).
//    '<div style="width:450px;" id="div_tag">'.$frm->text_box('tag', '', '', '', '', 'id="inp_tag" style="width:197px;"').'</div>'.
//    '<div style="width:450px;" id="geo"><label>������</label>'.
//    $frm->text_box('latitude', '').
//    '<label>�������</label>'.
//    $frm->text_box('longitude', '').'</div>');    
    
    $frm->addrow('������', $frm->text_box('tag', '', '', '', '', 'id="inp_tag" style="width:197px;"'));
    $frm->addrow('����������', '<span>������ </span>'.$frm->text_box('latitude','').
    '<span> ������� </span>'.$frm->text_box('longitude','').
    '<span> �����������, �� </span>'.$frm->text_box('GeoAccuracy', '50'));        
    
    $frm->show();    
}

  
?>