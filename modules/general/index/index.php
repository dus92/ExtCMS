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
$ibstruct = unpack_data(file_get_contents(IBLOCKS_PATH.'ibstruct.dat'));
$result = '';

$intro = file_get_contents(DATA_PATH . 'intro.html');
if(!empty($intro) && !@$system->config['wmh'])
{
	show_window('', $intro, 'left');
}

if(!empty($menu_points['index-menus']))
{
	$old_point = $system->current_point;
	$system->setCurrentPoint('index-menus');
	$c_module = $module;
	foreach ($menu_points['index-menus'] as $menu)
	{
		if(mb_substr($menu, 0, 4) == 'ucm:' && is_readable(DF_PATH . mb_substr($menu, 4) . '.ucm'))
		{
			$file = file(DF_PATH . mb_substr($menu, 4) . '.ucm');
			$title = preg_replace("/[\n\r]+/", '', $file[0]);
			$align = preg_replace("/[\n\r]+/", '', $file[1]);
			unset($file[0]);
			unset($file[1]);
			show_window($title, implode('', $file), $align);
		}
		else if (!empty($system->modules['menu'][$menu]))
		{
			$module = $menu;
			$module_dir = MODULES_PATH . $menu;
			require(MODULES_PATH . $menu . '/index.php');
		}
		else
		{
			show_window('', __('Module not found'), 'center');
		}
	}
	$system->setCurrentPoint('index-main');
	$module = $c_module;
}

//if(empty($system->config['index_module']) || $system->config['index_module'] == 'news' || $system->config['index_module'] == 'default')
//{
//	$iblockitem = new iBlockItem();
//	$ibid = false;
//	$title = false;
//	$own_tpls = false;
//	$filter = false;
//	$contid = 'news';
//	
//	$url_filter = '&amp;contid='.$contid;
//	if(isset($system->containers[$contid]))
//	{
//		$title = $system->containers[$contid]['title']; // as upper
//		$ibid = $system->containers[$contid]['ibid'];
//		$contsubst = @unpack_data($system->containers[$contid]['substitutions']);
//		$own_tpls = $contsubst['cont_tpls'] == 'own';
//		$filter = array('contid' => $contid); // associated array -> here item's 'contid' must be $contid
//	}
//	else
//	{
//		$result .= '<h2>'.__('Container with this identifier does not exist').'</h2>';
//		break;
//	}
//
//	// indexed values
//	$count = 0;
//	if($ibid)
//	{
//		if(!isset($system->iblocks[$ibid]))
//		{
//			return;
//		}
//		$iblockdata = $system->iblocks[$ibid];
//		$iblockdata['fields'] = unpack_data($iblockdata['fields']); // read indexed values options
//		$iblockdata['extopt'] = unpack_data($iblockdata['extopt']); // read extended values options
//		if(isset($iblockdata['fields']['fd_store']))
//		{
//			$indexes = array_filter($iblockdata['fields']['fd_store'], "ib_filter_store"); // filter not needed keys
//			$keys = array_keys($indexes);
//			$count = sizeof($keys);
//		}
//	}
//	if(isset($_REQUEST['next'])) // specifies offset
//	{
//		$next=vf($_REQUEST['next'],3);
//	}
//	else
//	{
//		$next = 0;
//	}
//
//	$order = 'idate';
//	$desc = 'DESC';
//	
//	if(isset($_GET['tag'])) // tags filter
//	{
//		$tag = preg_replace("/[^ ".__('a-zA-Z')."0-9\-]/",'',$_GET['tag']);
//	}
//
//	$i=0;
//
//	// Building query
//	$extwhere = (!empty($tag) ? 'tags LIKE \'%'.$tag.'%\'' : '');
//	
//	$iblockitem->setWorkTable($iblockitem->prefix.'ibitems'.($system->language === $system->config['default_lang'] ? '' : '_'.$system->language));
//	$cnt = $iblockitem->BeginiBlockItemsListRead($filter, array('id','ibid','title','idata','idate','tags','description','source','uid'),(isset($next) ? array($next,$system->config['perpage']) : $system->config['perpage']),$order,$desc,$extwhere, true);
//	if($cnt>$system->config['perpage'])
//	{
//		// making pagination
//		$pagination = hcms_npagination($cnt, $system->config['perpage'], $next,'?module=iblocks'.$url_filter.(!empty($tag) ? '&amp;tag='.$tag : ''), '<span>{current}</span>');
//	}
//
//	$ibid = false;
//	$item_i = 0;
//	while ($item = $iblockitem->Read())
//	{
//		// extended item data stored in 'idata'
//		$item = array_merge(unpack_data($item['idata']),$item);
//		$item_i ++;
//
//		$item['uptitle'] = $title;
//		if(isset($pagination))
//		{
//			$item['pagination'] = $pagination;
//		}
//		if(!$ibid)
//		{
//			// LISTUP TEMPLATE
//			$result.=rcms_parse_module_template($item['ibid'].'/listup.tpl',$item);
//			$ibid = $item['ibid'];
//		}
//		$i++;
//
//		if(!empty($item['tags']))
//		{
//			$tags = '';
//			$item['tags'] = explode(',',$item['tags']);
//			$tags_count=sizeof($item['tags']);
//			for ($s=0; $s<$tags_count; $s++)
//			{
//				$tags.='<a href="index.php?module=iblocks'.$url_filter.'&tag='.trim($item['tags'][$s]).'">'.trim($item['tags'][$s]).'</a> ';
//			}
//			$item['tags'] = $tags;
//		}
//		else
//		{
//			$item['tags'] = '<a href="#">'.__('Not tagged').'</a>';
//		}
//
//		$item['description'] = rcms_parse_text_by_mode($item['description'],$item['mode']);
//		$item['i'] = $item_i;
//		// ITEM-LIST TEMPLATE
//		if($own_tpls)
//		{
//			$result .= rcms_parse_module_template_mpath(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-list.tpl',$item);
//		}
//		else
//		{
//			$result.=rcms_parse_module_template($ibid.'/item-list.tpl',$item);
//		}
//
//		// ITEM-DELIMITER TEMPLATE
//		if(!empty($iblockdata['extopt']['delim_cond']))
//		{
//			if($i%$iblockdata['extopt']['delim_cond']==0 && $i<$system->config['perpage'])
//			{
//				$result.=rcms_parse_module_template($item['ibid'].'/listdelim.tpl',$item);
//			}
//		}
//	}
//	if($i == 0)
//	{
//		if($result=='' && $_GET['module']!=='index')
//		{
//			$result .= '<h2>'.__('No elements found').'</h2>';
//		}
//	}
//	// LISTDOWN TEMPLATE
//
//	$result.=rcms_parse_module_template($ibid.'/listdown.tpl',(isset($pagination) ? array('pagination' => $pagination) : array()));
//} 
if ($system->config['index_module'] != 'empty' && !empty($system->modules['main'][$module]))
{
	$my_module = $module;
	$module = $system->config['index_module'];
	include_once(MODULES_PATH . $module . '/index.php');
	$module = $my_module;
}

if(!empty($menu_points['index-menus'])){
	$system->setCurrentPoint($old_point);
}
$system->config['pagename'] = __('Index');
show_window(__('Index'), $result, (empty($align) ? 'left' : $align));
?>