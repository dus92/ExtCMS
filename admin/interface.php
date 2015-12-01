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
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?=$system->config['encoding']?>" />
        <title><?=__('Administration')?></title>        
        <link rel="stylesheet" href="<?RCMS_ROOT_PATH?>admin/resources/ext-all.css" />
        <link rel="stylesheet" href="<?RCMS_ROOT_PATH?>css/main.css" />
        <script src="<?RCMS_ROOT_PATH?>modules/js/jquery.js"></script>
        <script src="<?RCMS_ROOT_PATH?>admin/ext-all.js"></script>
        <script src="<?RCMS_ROOT_PATH?>admin/inc/fields.js"></script>
        <script src="<?RCMS_ROOT_PATH?>admin/inc/common.js"></script>
        <script type="text/javascript" src="<?RCMS_ROOT_PATH?>admin/app.js"></script>
    </head>
<body>
</body>
</html>