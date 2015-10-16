<div class="art_item">

<!--<h4 class="art_extinfo"><span class="date"><?=rcms_format_time("d F Y H:i",strtotime($tpldata['idate']))?></span> | <?=$tpldata['tags']?></h4>-->
<div class="new_title">
    
    <div style="float: left;">
        <a class="art_tlink" href="news_<?=$tpldata['id']?>-<?=$tpldata['title']?>.html"><?=$tpldata['title']?></a>
    </div>
    <div style="float: right; font-weight: bold;"><?=rcms_format_time("d F Y H:i",strtotime($tpldata['idate']))?></div>
    <div style="clear: both;"></div>
</div>
<p class="art_desc"><?=$tpldata['description']?></p>
</div>

