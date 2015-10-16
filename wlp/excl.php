<?php
// ���������� �� �������� - � POST ����������
$POST_EXCLUSIONS=array('current_password','password','confirmation','title','source','keywords','sef_desc','description','text');

// ���������� �� �������� - � GET ����������
$GET_EXCLUSIONS=array('a');

// ���������� �� �������� - � ����� ���������� ��� ���������� register_globals
if(ini_get('register_globals'))
	$GLOBAL_EXCLUSIONS=array();
?>