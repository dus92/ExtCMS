<?
$keys = array_keys($tpldata['photo']);
$img = dirname($tpldata['photo'][$keys[0]]).'/'.basename($tpldata['photo'][$keys[0]]);

?>

<div class="div_product">
    <div class="product_photo">
        <img src="<?=$img?>" border="0" />
    </div>
    <div class="product_general">
        <div class="product_title">
            <a href="production_<?=$tpldata['id']?>-<?=preg_replace("/[\.%]/", '', $tpldata['title'])?>.html"><?=$tpldata['title']?></a>
        </div>
        <div class="product_desc">
            <?=$tpldata['description']?>
        </div>        
    </div>        
</div>
<div style="clear: both;"></div>
<div class="product_more">
    <a href="production_<?=$tpldata['id']?>-<?=preg_replace("/[\.%]/", '', $tpldata['title'])?>.html">Далее</a>
</div>
<!--<hr style="background: gray;" />-->