<table border="0" cellpadding="1" cellspacing="1" width="100%" class="grayborder">
<tr>
    <th align="left" width="100%">
        [<?=rcms_format_time('H:i:s d.m.Y', $tpldata['time'], $system->user['tz'])?>]
        <?=__('Posted by')?> <?=user_create_link($tpldata['author_user'], $tpldata['author_nick'])?>
    </th>
    <th align="right">
        <?php if($system->checkForRight('ARTICLES-MODERATOR')){?>
        <form method="post" action="">
            <input type="hidden" name="cdelete" value="<?=$tpldata['id']?>" />
            <input type="submit" name="" value="X" />
        </form>
        <?php }?>
    </th>
</tr>
<tr>
    <td align="left" class="row3" colspan="2" style=""><?=$tpldata['text']?></td>
</tr>
</table>