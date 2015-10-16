<?php
if($module == 'iblocks')
{
	// watch for $_GET keys 
	global $system;	
	$iblockitem = new iBlockItem();
	if(isset($_GET['catid']))
	{
		$catid = vf($_GET['catid'],3);
		$catdata = $iblock->ReadSingleCategoryData($catid,array('title'));
		$feed->title = $system->config['short_title'] . ' - ' . $catdata['title'];
		$feed->description = __('Feed for category').' "'.$catdata['title'].'"';
		$iblockitem->BeginiBlockItemsListRead($catid, array('id','title','description','idate'), 10, 'idate','desc');
	}
	else if(isset($_GET['contid']))
	{
		$contid = vf($_GET['contid'],4);
		$feed->title = $system->config['short_title'] . ' - ' . $system->containers[$contid]['title'];
		$feed->description = __('Feed for container').' "'.$system->containers[$contid]['title'].'"';
		$iblockitem->BeginiBlockItemsListRead(array('contid' => $contid), array('id','title','description','idate'), 10, 'idate','desc');
	}
	else if(isset($_GET['ibid']))
	{
		$ibid = vf($_GET['ibid'],4);
		$feed->title = $system->config['short_title'] . ' - ' . $system->iblocks[$ibid]['title'];
		$feed->description = __('Feed for iblock').' "'.$system->iblocks[$ibid]['title'].'"';
		$iblockitem->BeginiBlockItemsListRead(array('ibid' => $ibid), array('id','title','description','idate'), 10, 'idate','desc');
	}
	
	while($item = $iblockitem->Read())
	{
		$feed->addItem($item['title'],
                    htmlspecialchars($item['description'],null,'cp1251'),
                    $system->url . '/?module=iblocks&amp;action=show&amp;item=' . $item['id'],
                    strtotime($item['idate']));
	}
}
else 
{
	// iblocks@<ibid>
	$pointer = explode('@', $module);
	$ibid = vf($pointer[1],4);
	$iblockitem = new iBlockItem();
	$iblockitem->BeginiBlockItemsListRead(array('ibid' => $ibid), array('id','title','description','idate'), 10, 'idate','desc');
	while($item = $iblockitem->Read())
	{
		$feed->addItem($item['title'],
                    hcms_htmlsecure($item['description']),
                    $system->url . '/?module=iblocks&amp;action=show&amp;item=' . $item['id'],
                    strtotime($item['idate']));
	}
}
?>