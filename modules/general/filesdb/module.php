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
$this->registerModule($module, 'main', __('FilesDB'), 'ReloadCMS Team', array(
    'FILESDB' => __('Right to add/edit/delete files and categories of files in filesdb')
));
$this->registerFeed($module, __('FilesDB'), __('Files feed'));
?>