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

if(!empty($system->config['enable_rss'])){
    $feeds = &$system->feeds;
    $data = '';
    foreach ($feeds as $module => $d) {
        $data .= '<a href="./rss.php?m=' . $module . '"><img src="' . SKIN_PATH . 'rss.png" alt="RSS"/>&nbsp;' . $d[0] . '</a><br />';
    }
    if(!empty($data)) show_window(__('RSS Feeds'), $data);
}
?>