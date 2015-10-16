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

////////////////////////////////////////////////////////////////////////////////
// Initializations                                                            //
////////////////////////////////////////////////////////////////////////////////
ob_start();
error_reporting(E_ERROR);
define('RCMS_ROOT_PATH', './');
require_once(RCMS_ROOT_PATH . 'common.php');
$menu_points = parse_ini_file(CONFIG_PATH . 'menus.ini', true);

if(isset($_GET['exit']))
{
	$system->logOutUser();
	setcookie('reloadcms_user');
	$_COOKIE['reloadcms_user'] = '';
	header('Location: index.php');
	die('Logout performed');
}

// Send main headers
header('Last-Modified: ' . gmdate('r')); 
header('Content-Type: text/html; charset=' . $system->config['encoding']);
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Pragma: no-cache");

session_start();

// Page gentime start 
$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

// Loading main module
$system->setCurrentPoint('__MAIN__');
if(!empty($_GET['module'])) $module = basename($_GET['module']); else $module = 'index';
if(!empty($system->modules['main'][$module])) include_once(MODULES_PATH . $module . '/index.php');

// Load menu modules
include_once(CUR_SKIN_PATH . 'skin.php');
if(!empty($menu_points)){
   	foreach($menu_points as $point => $menus){
       	$system->setCurrentPoint($point);
       	if(!empty($menus) && isset($skin['menu_point'][$point])){
       	  	foreach ($menus as $menu){
               	if(mb_substr($menu, 0, 4) == 'ucm:' && is_readable(DF_PATH . mb_substr($menu, 4) . '.ucm')) {
                   	$file = file(DF_PATH . mb_substr($menu, 4) . '.ucm');
                   	$title = preg_replace("/[\n\r]+/", '', $file[0]);
                   	$align = preg_replace("/[\n\r]+/", '', $file[1]);
                   	unset($file[0]);
                   	unset($file[1]);
                   	show_window($title, implode('', $file), $align);
               	} elseif (!empty($system->modules['menu'][$menu])){
                   	$module = $menu;
                   	$module_dir = MODULES_PATH . $menu;
                   	require(MODULES_PATH . $menu . '/index.php');
               	} else {
                   	show_window('', __('Module not found'), 'center');
               	}
           	}
       	}
   	}
}

@mysql_close();

// Start output
if(isset($_GET['n_ajax']))
{
	rcms_show_element('main_point', $module . '@window.center');	
	$mtime = explode(' ', microtime());
  	$totaltime = $mtime[0] + $mtime[1] - $starttime;
	print('
	<script type="text/javascript">
    $(document).ready(
		function ()
		{
			$("title").text("'.$system->config['title'] . ' - ' . $system->config['pagename'] .'");
			$("form[action=\'\']").each(function()
			{
				$(this).attr("action","index.php?'.getenv('QUERY_STRING').'".replace("&n_ajax",""));
			});
			$("span.queries_used").text("'.$mysql_data['queries_used'].'");
			$("span.gen_time").text("'.round($totaltime,3).'");
		});    
    </script>');
	ob_end_flush();
	exit;
}

require_once(CUR_SKIN_PATH . 'skin.general.php');
ob_end_flush();
?>