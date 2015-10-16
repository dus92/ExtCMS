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
if(empty($system)) die();
$frame_height = '53%,*';    
$arr_user = $system->getUserData($system->user['username']);
 
if (!empty($arr_user['hidechat']))
    $frame_height = '100%,*';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$system->config['encoding']?>">
<title><?=__('Administration')?></title>
</head>
<frameset cols="20%,80%" border="0" framespacing="0" frameborder="NO">
	<frameset rows="<?=$frame_height?>" border="0" framespacing="0" frameborder="NO" >
		<frame src="./admin.php?show=nav" name="nav" marginwidth="3"
			marginheight="3" scrolling="auto">
		<frame name="chat" src="./admin.php?show=imail" marginwidth="0"
			marginheight="0" scrolling="auto">
	</frameset>
	<frame src="admin.php?show=module" name="main"></frame>
</frameset>
<noframes>
	<body bgcolor="white" text="#000000">
		<p>Sorry, but your browser does not support frames</p>
	</body>
</noframes>
</html>