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
$rss_cfg = rcms_parse_ini_file(CONFIG_PATH . 'rss_aggregator.ini', true);

$rss = new lastRSS();
$rss->cache_time = $rss_cfg['config']['cache_time'];
$rss->cache_dir = DATA_PATH . 'rss_cache';
$rss->items_limit = $system->config['num_of_latest'];
if(function_exists('iconv')){
	$rss->default_cp = $system->config['encoding'];
	$rss->cp = $system->config['encoding'];
}
$rss->stripHTML = true;

if(!empty($rss_cfg['feeds'])){
	foreach ($rss_cfg['feeds'] as $feed_url){
		if($feed = $rss->Get($feed_url)){
			$i = 2;
			$result = '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
			foreach($feed['items'] as $id => $item) {
				if(empty($item['title'])){
					$item['title'] = $item['description'];
				}
				$item['title'] = mb_substr($item['title'], 0, $rss_cfg['config']['max_title_length']) . ((mb_strlen($item['title']) > $rss_cfg['config']['max_title_length']) ? '...' : '');
				$item['description'] = (empty($item['description']) ? '' : mb_substr($item['description'], 0, $rss_cfg['config']['max_desc_length']) . ((mb_strlen($item['description']) > $rss_cfg['config']['max_desc_length']) ? '...' : ''));
				$result .= '<tr><td class="row' . $i . '"><a href="' . $rss->unhtmlentities($item['link']) . '"><abbr title="' . $item['description'] . '">' . $item['title'] . '</abbr></a></td></tr>';
				$i++;
				if($i > 3) $i = 2;
			}
			$result .= '</table>';
			$title = (!empty($feed['link']) ? '<a href="' . $feed['link'] . '">' . (!empty($feed['title']) ? $feed['title'] : __('RSS Feed')) . '</a>' : (!empty($feed['title']) ? $feed['title'] : __('RSS Feed')));
			show_window($title, $result);
		}
	}
}
?>