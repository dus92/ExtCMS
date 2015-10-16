<?php
define('RCMS_ROOT_PATH', './');
require_once(RCMS_ROOT_PATH . 'common.php');
require_once(SYSTEM_MODULES_PATH . 'hxml.php');

if(!empty($system->config['enable_hxml']))
{
	header('Content-Type: text/xml');
	
	// watch for $_GET keys
	global $system;
	$iblockitem = new iBlockItem();
	if(isset($_GET['catid']))
	{
		$catid = vf($_GET['catid'],3);
		$catdata = $iblock->ReadSingleCategoryData($catid,array('title'));
		$hxml_feed->title = $system->config['short_title'] . ' - ' . $catdata['title'];
		$hxml_feed->description = __('Feed for category').' "'.$catdata['title'].'"';
		$iblockitem->BeginiBlockItemsListRead($catid, '*', 10, 'idate','desc');
	}
	else if(isset($_GET['contid']))
	{
		$contid = vf($_GET['contid'],4);
		$hxml_feed->title = $system->config['short_title'] . ' - ' . $system->containers[$contid]['title'];
		$hxml_feed->description = __('Feed for container').' "'.$system->containers[$contid]['title'].'"';
		$iblockitem->BeginiBlockItemsListRead(array('contid' => $contid), '*', 10, 'idate','desc');
	}
	else if(isset($_GET['ibid']))
	{
		$ibid = vf($_GET['ibid'],4);
		$hxml_feed->title = $system->config['short_title'] . ' - ' . $system->iblocks[$ibid]['title'];
		$hxml_feed->description = __('Feed for iblock').' "'.$system->iblocks[$ibid]['title'].'"';
		$iblockitem->BeginiBlockItemsListRead(array('ibid' => $ibid), '*', 10, 'idate','desc');		
	}

	while($item = $iblockitem->Read())
	{
		$hxml_feed->addItem($item['title'],
		hcms_htmlsecure($item['description']),
		$system->url . '/?module=iblocks&amp;action=show&amp;item=' . $item['id'],
		strtotime($item['idate']));
	}

	$feed->showFeed();
}
?>