<!--<div>
<h3><span class="date"><?=rcms_format_time("d F Y",strtotime($tpldata['idate']))?></span> <?=$tpldata['tags']?></h3>
<a style="font-weight:bold; font-size: 1.5em;" href="?module=iblocks&action=show&item=<?=$tpldata['id']?>"><?=$tpldata['title']?></a>
<p><?=$tpldata['description']?></p>
</div>-->

<?
    $keys = array_keys($tpldata['photo']);
    $img = dirname($tpldata['photo'][$keys[0]]).'/'.basename($tpldata['photo'][$keys[0]]);
    
?>

<div class="good">
    <div class="g_img">
        <a href="<?=$img?>" class="highslide" onclick="return hs.expand(this)">
            <img src="<?=$img?>" alt="<?=$tpldata['title']?>" width="203" height="134" />
        </a>
        <div class="highslide-caption">
	       <?=$tpldata['title']?>
        </div>
    </div>
    <div class="g_title"><?=$tpldata['title']?></div>
</div>