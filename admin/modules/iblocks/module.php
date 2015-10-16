<?php
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) Hahhah~CMS Development Team                                //
//   http://hakkahcms.sourceforge.net                                         //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////
$MODULES[$category][0] = __('iBlocks');
if($system->checkForRight('IBLOCKS-ADMIN')) $MODULES[$category][1]['ibconfig'] = __('Configure iBlocks');
if($system->checkForRight('IBLOCKS-ADMIN')) $MODULES[$category][1]['multilang'] = __('Multilang features');
if($system->checkForRight('IBLOCKS-EDITOR')) $MODULES[$category][1]['static'] = __('Static pages');
if($system->checkForRight('IBLOCKS-ADMIN')) $MODULES[$category][1]['ibgroups'] = __('Manage iBlock groups');
if($system->checkForRight('IBLOCKS-ADMIN')) $MODULES[$category][1]['iblocks'] = __('Manage infoblocks');
if($system->checkForRight('IBLOCKS-ADMIN')) $MODULES[$category][1]['containers'] = __('Manage containers');
if($system->checkForRight('IBLOCKS-ADMIN')) $MODULES[$category][1]['categories'] = __('Manage categories');
if($system->checkForRight('IBLOCKS-ADMIN') || $system->checkForRight('IBLOCKS-EDITOR')) $MODULES[$category][1]['tags'] = __('Manage auto-tags');
if($system->checkForRight('IBLOCKS-EDITOR') || $system->checkForRight('IBLOCKS-POSTER'))
{
	if(@filesize(IBLOCKS_PATH.'items_simplified_menu.dat')>1 && !empty($system->ibconfig['interface']['enable_simplified_menu']))
	{
		$MODULES[$category][1]['items'] = @unpack_data(file_get_contents(IBLOCKS_PATH.'items_simplified_menu.dat'));
	}
	else 
	{
		$MODULES[$category][1]['items'] = __('Manage items');
	}
}
if($system->checkForRight('IBLOCKS-EDITOR') || $system->checkForRight('IBLOCKS-POSTER')) $MODULES[$category][1]['post'] = __('Post item');


?>