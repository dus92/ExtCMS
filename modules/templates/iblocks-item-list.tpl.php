<div>
<h3><span class="date"><?=rcms_format_time("d F Y",strtotime($tpldata['idate']))?></span> <?=$tpldata['tags']?></h3>
<a style="font-weight:bold; font-size: 1.5em;" href="?module=iblocks&action=show&item=<?=$tpldata['id']?>"><?=$tpldata['title']?></a>
<p><?=$tpldata['description']?></p>
</div>