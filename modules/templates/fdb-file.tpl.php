<table border="0" cellpadding="2" cellspacing="1" width="100%" class="grayborder">
<tr>
    <th align="center" colspan="3"><?=__($tpldata['name'])?></th>
</tr>
<tr>
<?php if(!empty($tpldata['desc'])) { ?>
    <td align="left" valign="top" class="row3" nowrap="nowrap"><?=__('Description')?>: </td>
    <td align="left" valign="top" class="row3" width="100%"><?=rcms_parse_text_by_mode($tpldata['desc'], 'text')?></td>
<?php }
else { ?>
<td colspan="2">
<?php } ?>
<td rowspan="7" align="center" valign="middle" class="row3">
<?php
if(empty($tpldata['preview']))
   	print('<img style="margin: 2px; pading: 2px;" title="Preview" src="'.PREVIEWS_PATH.'nopreview.jpg">');
else if(mb_substr(PREVIEWS_PATH.$tpldata['preview'],-3)=='avi')
	print('<embed height="240" width="320" scale="exactfit" type="application/x-mplayer2" src="'.PREVIEWS_PATH.$tpldata['preview'].'"></embed>');
else
	print('<img style="margin: 2px; pading: 2px;" title="Preview" src="'.PREVIEWS_PATH.$tpldata['preview'].'">');

?>
    </td>
</tr>
<tr>
    <td align="left" valign="top" class="row3" nowrap="nowrap"><?=__('Downloads count')?></td>
    <td align="left" valign="top" class="row3" width="100%"><?=(int)@$tpldata['count']?></td>
</tr>
<tr>
    <td align="left" valign="top" class="row3" nowrap="nowrap"><?=__('Size of file')?></td>
    <td align="left" valign="top" class="row3" width="100%"><?=($tpldata['size'] > 1000000 ? round($tpldata['size']/1000000,1).' Mb' : ($tpldata['size'] > 1000 ? round($tpldata['size']/1000,1).' Kb' : ($tpldata['size']).' b'))?></td>
</tr>
<tr>
    <td align="left" valign="top" class="row3" nowrap="nowrap"><?=__('Author')?></td>
    <td align="left" valign="top" class="row3" width="100%"><?=$tpldata['author']?></td>
</tr>
<tr>
    <td align="left" valign="top" class="row3" nowrap="nowrap"><?=__('Platform')?></td>
    <td align="left" valign="top" class="row3" width="100%"><?=@$tpldata['platform']?></td>
</tr>
<tr>
    <td align="left" valign="top" class="row3" nowrap="nowrap"><?=__('License')?></td>
    <td align="left" valign="top" class="row3" width="100%"><?=@$tpldata['license']?></td>
</tr>
<tr>
    <td align="left" valign="top" class="row3" nowrap="nowrap"><?=__('Date')?></td>
    <td align="left" valign="top" class="row3" width="100%"><?=rcms_format_time('d F Y H:i:s', $tpldata['date'])?></td>
</tr>
<tr>
   	<th align="center" colspan="3">
   	    <a href="<?=$tpldata['down_url']?>" target="_blank">
   	        <?=__('Download!')?>
   	    </a>
   	</th>
</tr>
</table>