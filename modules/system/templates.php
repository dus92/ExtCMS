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

function user_tz_select($default = 0, $select_name = 'timezone') {
	global $lang;

	$tz_select = '<select name="' . $select_name . '">';
	while(list($offset, $zone) = @each($lang['tz'])) {
		$selected = ( $offset == $default ) ? ' selected="selected"' : '';
		$tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
	}
	$tz_select .= '</select>';

	return $tz_select;
}

function user_skin_select($dir, $select_name, $default = '', $style = '', $script = '') {
    $skins = rcms_scandir($dir);
    $frm = '<select name="' . $select_name . '" style="' . $style . '" ' . $script . '>';
    foreach ($skins as $skin){
        if(is_dir($dir . $skin) && is_file($dir . $skin . '/skin_name.txt')){
            $name = file_get_contents($dir . $skin . '/skin_name.txt');
            $frm .= '<option value="' . $skin . '"' . (($default == $skin) ? ' selected="selected">' : '>') . $name . '</option>';
        }
    }
    $frm .= '</select>';
    return $frm;
}

function user_lang_select($select_name, $default = '', $style = '', $script = '') {
    global $system;
    $frm = '<select name="' . $select_name . '" style="' . $style . '" ' . $script . '>';
    foreach ($system->data['languages'] as $lang_id => $lang_name){
        $frm .= '<option value="' . $lang_id . '"' . (($default == $lang_id) ? ' selected="selected">' : '>') . $lang_name . '</option>';
    }
    $frm .= '</select>';
	return $frm;
}

function rcms_pagination($total, $perpage, $current, $link){
    $return = '';
    $link = preg_replace("/((&|&)page=(\d*))/", '', $link);
    if(!empty($perpage)) {
        $pages = ceil($total/$perpage);
        if($pages != 1){
            $c = 1;
            while($c <= $pages){
if ((($c < $current+4) and ($c > $current-4)) or ($c==$pages) or ($c==1)) {
                if($c != $current) $return .= ' [' . '<a href="' . $link . '&page=' . $c . '">' . $c . '</a>] ';
                else $return .= ' [' . $c . '] ';
} else {
                if($c<8)
                     $return .= '.';
}
                $c++;
            }
        }
    }
    return $return;
}

function rcms_parse_menu($format) {
	global $system;
	$navigation = parse_ini_file(CONFIG_PATH . 'navigation.ini', true);
	$result = array();
	foreach ($navigation as $link) {
		if(mb_substr($link['url'], 0, 9) == 'external:') {
			$target = '_blank';
			$link['url'] = mb_substr($link['url'], 9);
		} else {
			$target = '';
		}
		$tdata = explode(':', $link['url'], 2);
		if(count($tdata) == 2){
			list($modifier, $value) = $tdata;
		} else {
			$modifier = $tdata[0];
		}
		if(!empty($value) && !empty($system->navmodifiers[$modifier])){
			if($clink = call_user_func($system->navmodifiers[$modifier]['m'], $value)){
				$result[] = array($clink[0], (empty($link['name'])) ? $clink[1] : __($link['name']), $target);
			}
		} else {
			$result[] = array($link['url'], __($link['name']));
		}
	}
	$menu = '';
	foreach ($result as $item){
		if(empty($item[2])) {
			$item[2] = '_top';
		}
		$menu .= str_replace('{link}', $item[0], str_replace('{title}', $item[1], str_replace('{target}', @$item[2], $format)));
	}
	$result = $menu;
	return $result;
}

function rcms_parse_module_template($module, $tpldata = array()) {
    global $system;
    ob_start();
   	if(is_file(CUR_SKIN_PATH . $module . '.php')) {
        include(CUR_SKIN_PATH . $module . '.php');
    } elseif(file_exists(IBLOCKS_TPL_PATH . $module . '.php')) {
        include(IBLOCKS_TPL_PATH . $module . '.php');
    } elseif(is_file(MODULES_TPL_PATH . $module . '.php')) {
        include(MODULES_TPL_PATH . $module . '.php');
    }
    $return = ob_get_contents();
    ob_end_clean();
    return $return;
}

function rcms_parse_module_template_mpath($module, $tpldata = array()) {
    global $system;
    ob_start();
   	if(file_exists($module . '.php')) {
        include($module . '.php');
    } 
    $return = ob_get_contents();
    ob_end_clean();
    return $return;
}

function rcms_open_browser_window($id, $link, $attributes = '', $return = false){
	global $system;
	$code = '<script language="javascript">window.open(\'' . addslashes($link) . '\', \'' . $id . '\',\'' . $attributes . '\');</script>';
	if($return){
		return $code;
	} else {
		@$system->config['meta'] .= $code;
	}
}

function rcms_parse_module_template_path($module) {
    if(is_file(CUR_SKIN_PATH . $module . '.php')) {
        return (CUR_SKIN_PATH . $module . '.php');
    } elseif(is_file(MODULES_TPL_PATH . $module . '.php')) {
        return (MODULES_TPL_PATH . $module . '.php');
    } else {
        return false;
    }
}

function rcms_show_element($element, $parameters = ''){
    global $system;
    switch($element){
        case 'title':
            if(!@$system->config['hide_title']) {
                echo __($system->config['title']);
                if(!empty($system->config['pagename'])) echo ' - ';
            }
            echo (!empty($system->config['pagename'])) ? __($system->config['pagename']) : '';
            break;
        case 'menu_point':
            list($point, $template) = explode('@', $parameters);
            
    		if(is_file(CUR_SKIN_PATH . 'skin.' . $template . '.php')) {
        		$tpl_path = CUR_SKIN_PATH . 'skin.' . $template . '.php';
    		} elseif(is_file(MODULES_TPL_PATH . $template . '.php')) {
        		$tpl_path = MODULES_TPL_PATH . $template . '.php';
    		}
    		
            if(!empty($tpl_path) && !empty($system->output['menus'][$point])){
                foreach($system->output['menus'][$point] as $module){
                    $system->showWindow($module[0], $module[1], $module[2], $tpl_path);
                }
            }
            break;
        case 'main_point':
            foreach ($system->output['modules'] as $module) {
                $system->showWindow($module[0], $module[1], $module[2], CUR_SKIN_PATH . 'skin.' . mb_substr(mb_strstr($parameters, '@'), 1) . '.php');
            }
            break;
        case 'navigation':
            echo rcms_parse_menu($parameters);
            break;
        case 'meta':
            readfile(DATA_PATH . 'meta_tags.html');
            echo '<meta http-equiv="Content-Type" content="text/html; charset=' . $system->config['encoding'] . '" />' . "\r\n";
            if(!empty($system->config['enable_rss'])){
                foreach ($system->feeds as $module => $d) {
                    echo '<link rel="alternate" type="application/rss+xml" title="RSS ' . $d[0] . '" href="./rss.php?m=' . $module . '" />' . "\r\n";
                }
            }
            if(!empty($system->config['meta'])) echo $system->config['meta'];
            break;
        case 'head_js':
        	echo '<script type="text/javascript" src="modules/js/jquery.js"></script>
            <script type="text/javascript" src="'.RCMS_ROOT_PATH.'modules/js/general.js"></script>
            <script type="text/javascript" src="'.RCMS_ROOT_PATH.'modules/js/jquery.cookie.js"></script>';
            if ($_GET['module'] == 'instagram'){
                echo '<script src="'.RCMS_ROOT_PATH.'modules/js/instagram.js" type="text/javascript" charset="utf-8"></script>
                <script src="'.RCMS_ROOT_PATH.'modules/js/underscore-min.js" type="text/javascript" charset="utf-8"></script>';
                //<script src="'.RCMS_ROOT_PATH.'modules/js/jquery-1.7.2.min.js" type="text/javascript" charset="utf-8"></script>                                
            }
        	break;

        case 'copyright':
            if(!defined('RCMS_COPYRIGHT_SHOWED') || !RCMS_COPYRIGHT_SHOWED){
                echo RCMS_POWERED . ' ' . RCMS_VERSION_A . '.'  . RCMS_VERSION_B . '.' . RCMS_VERSION_C . RCMS_VERSION_SUFFIX . '<br />' . RCMS_COPYRIGHT;
            }
            break;
    }
}
?>