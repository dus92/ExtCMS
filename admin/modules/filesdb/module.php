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
$MODULES[$category][0] = __('FilesDB');
if($system->checkForRight('FILESDB')) {
    $MODULES[$category][1]['categories'] = __('Manage categories of files');
    $MODULES[$category][1]['files'] = __('Manage files');
}
?>