<?php
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) Hakkah~CMS Development Team                                //
//   http://hakkahcms.sourceforge.net                                         //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////
$this->registerModule($module, 'main', __('iBlocks'), 'Hakkah~CMS Team',array('IBLOCKS-EDITOR' => __('Right to post and edit iblock items'), 'IBLOCKS-ADMIN' => __('Right to manipulate iblock containers/categories'), 'IBLOCKS-POSTER' => __('Right to post iblock items and edit its own items'), 'IBLOCKS-STATIC_PAGES-EDITOR' => __('Right to create and edit static pages')));
require_once(ENGINE_PATH.'api.iblocks.php');
$iblock = new iBlock($this);
$ibids = array_keys($this->iblocks);
$count = sizeof($this->iblocks);
for($i=0; $i<$count; $i++)
{
	$this->registerFeed($module . '@' . $ibids[$i], $this->iblocks[$ibids[$i]]['title'], __('Feed for iblock') . ' ' . $this->iblocks[$ibids[$i]]['title'], $module);
}
?>