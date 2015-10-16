<h2><?=$tpldata['title']?></h2>
<div class="up">
  	<span class="time"><?=rcms_format_time("d F Y",strtotime($tpldata['idate']))?></span>
    <cite><?=$tpldata['description']?></cite>
</div>
<div class="main">
  	<?=$tpldata['itext']?>
</div>