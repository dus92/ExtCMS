<?php
require_once('wlp/config.inc.php');

if(isset($_POST['action']))
	$action=$_POST['action'];
else
	$action='';

switch($action)
{
case 'editrulez':
	
	break;
case 'editsett':
	print_r($_POST);
	dm_fwrite('wlp/config.inc.php','<?php'.CR.'define(\'ENABLE_WLP\','.($_POST['wlp_enabled']=='on' ? 'true' : 'false').');'.CR.CR,"w");
	dm_fwrite('wlp/config.inc.php','define(\'CR\',"\r\n");'.CR.CR,"a");
	dm_fwrite('wlp/config.inc.php','define(\'ALLOWED_SS\',\''.$_POST['allowedss'].'\');'.CR,"a");
	dm_fwrite('wlp/config.inc.php','define(\'WORKMETHOD\',\''.($_POST['workmethod']=='fakeerrs' ? 'FAKEERRORS' : 'RMFUCKS').'\');'.CR,"a");
	dm_fwrite('wlp/config.inc.php','define(\'BAN_HEKKERS\','.($_POST['ban_at']=='on' ? 'true' : 'false').');'.CR,"a");
	dm_fwrite('wlp/config.inc.php','define(\'WAIT_FOR_HEK_NUM\','.$_POST['waitfor'].');'.CR,"a");
	dm_fwrite('wlp/config.inc.php','define(\'HOURS_TO_BAN_FOR\','.$_POST['banhours'].');'.CR,"a");
	dm_fwrite('wlp/config.inc.php','define(\'REDIR_TO\',\''.$_POST['redirect'].'\');'.CR.'?>',"a");
	go('admin.php?show=module&id=general.wlpadmin');
	break;
default:
	print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link rel="stylesheet" href="admin/style.css" type="text/css">
</head>
<body>

<table width="100%" cellspacing="2px" cellpading="2px">
<tr>
	<th colspan="2" valign="middle">
		<b>ReloadCMS White-List Protection</b>
	</th>
<tr>
	<td class="row2" width="250">
		'.__('Enable protection').'
	</td>
	<td class="row2">
		<form action="" method="POST">
		<input type="hidden" name="action" value="editsett">
		<input name="wlp_enabled" type="checkbox" '.(ENABLE_WLP==true ? 'checked' : '').'>
	</td>
</tr>
<tr>
	<td class="row2">
		'.__('Work method').'
	</td>
	<td class="row2">
		<select name="workmethod">
			<option value="fakeerrs" '.(WORKMETHOD=='FAKEERRORS' ? 'selected' : '').'> '.__('Fake errors').'
			<option value="rmfucks" '.(WORKMETHOD=='RMFUCKS' ? 'selected' : '').'> '.__('Remove malicious').'
		</select>
	</td>
</tr>
<tr>
	<td class="row2">
		'.__('Allowed symbols').'
	</td>
	<td class="row2">
		<input type="text" value="'.ALLOWED_SS.'" name="allowedss">
	</td>
</tr>
<tr>
	<td class="row2">
		'.__('Ban attackers').'
	</td>
	<td class="row2">
		<input type="checkbox" '.(BAN_HEKKERS==true ? 'checked' : '').' name="ban_at">
	</td>
</tr>
<tr>
	<td class="row2">
		-- '.__('Wait for # attempts').'
	</td>
	<td class="row2">
		<input type="number" value="'.WAIT_FOR_HEK_NUM.'" name="waitfor">
	</td>
</tr>
<tr>
	<td class="row2">
		-- '.__('Hours to ban for').'
	</td>
	<td class="row2">
		<input type="number" value="'.HOURS_TO_BAN_FOR.'" name="banhours">
	</td>
</tr>
<tr>
	<td class="row2">
		'.__('Redirect to [empty == no redirect]').'
	</td>
	<td class="row2">
		<input type="text" value="'.REDIR_TO.'" name="redirect">
	</td>
</tr>
<tr>
	<td colspan="2" align="center">
		<input type="submit" value="'.__('Send').'"></form>
	</td>
</tr>
</table>

</body>
</html>');

	break;
}

/**
 * ���������� � ���� ����������.
 *
 * @param string $file
 * @param string $data
 * @param char $mode
 */
function dm_fwrite($file,$data,$mode)
{
	$f=fopen($file,$mode);
	fwrite($f,$data);
	fclose($f);	
}

/**
 * ���������� ����� JavaScript �� �������� � ���������
 *
 * @param string $address
 */
function go($address)
{
	print(' �������������...
	<script>
	setTimeout(\'redir()\',1000);	
	
	function redir()
	{
		document.location=\''.$address.'\'
	};
	</script>');
	return;	
}
?>