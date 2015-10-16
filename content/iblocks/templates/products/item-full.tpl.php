<?
$keys = array_keys($tpldata['photo']);
$img = dirname($tpldata['photo'][$keys[0]]).'/'.basename($tpldata['photo'][$keys[0]]);

?>

<div id="main_title"><?=$tpldata['title']?></div>
<div class="fproduct_main">
    <div class="fproduct_photo">
        <img src="<?=$img?>" width="200" border="0" />
    </div>
    <div class="fproduct_text">
        <?=$tpldata['itext']?>    
    </div>
</div>