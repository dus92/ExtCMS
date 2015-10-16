<?php
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) ReloadCMS Development Team  http://reloadcms.com           //
//                 Greenray                    http://opensoft.110mb.com      //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////
$sel_ibid = 'calendar';
$iblockitem = new iBlockItem();

$current_year  = rcms_format_time('Y', mktime());     // Текущий год
$current_month = rcms_format_time('n', mktime());     // Текущий месяц
// Выбор года для отображения в календаре.
// Он не может быть больше текущего,
// а нижняя граница = $current_year - 6,
// т.е в поле выбора года будет отображаться диапазон 2003 - 2009.
if (!empty($_REQUEST['cal_year']) && $_REQUEST['cal_year'] >= $current_year - 6 && $_REQUEST['cal_year'] <= $current_year+1)
$selected_year = $_REQUEST['cal_year'];             // Выбор года. Если не сделан -
else $selected_year = $current_year;                  // текущий год.
if (!empty($_REQUEST['cal_month']) && $_REQUEST['cal_month'] >= 1 && $_REQUEST['cal_month'] <= 12)
$selected_month = $_REQUEST['cal_month'];           // Выбор месяца. Если не сделан -
else $selected_month = $current_month;                // текущий месяц.

$cur_month_ts = strtotime($selected_year.'-'.$selected_month.'-01');
// ahctung!!!
$prev_month_ts=$cur_month_ts-(3600*24*2);
$next_month_ts=$cur_month_ts+(3600*24*31);

$calendar = new calendar($selected_month, $selected_year);
$iblockitem->BeginiBlockItemsListRead(array('ibid' => $sel_ibid), array('id','idate'),false,'','','MONTH(idate)='.$selected_month.' AND YEAR(idate)='.$selected_year.'');
while($item = $iblockitem->Read())
{
	$time = strtotime($item['idate']);
	if ((rcms_format_time('n', $time) == $selected_month) && (rcms_format_time('Y', $time) == $selected_year)) {
		// Формируем ссылки на статьи только за выбранный месяц и год.
		// Если выбор не сделан - формируются ссылки только за текущие месяц и год.
		$calendar->assignEvent(rcms_format_time('d', $time), '?module=iblocks&ibid=calendar&from='.mktime(0, 0, 0, $selected_month, rcms_format_time('d', $time), $selected_year).'&until='.mktime(23, 59, 59, $selected_month, rcms_format_time('d', $time), $selected_year));
	}


}
$calendar->highlightDay(rcms_format_time('d', mktime()));
// Форма выбора месяца и года для отображения статей.
$date_pick = 
'<tr>
	<td colspan="7">
<script type="text/javascript">
$(document).ready(
function ()
{
	$("#next_month_link").click(function()
	{
		$.get("ajax.php",
		{
			module: "calendar",
			ibid: "'.$sel_ibid.'",
			cal_month: "'.date("m",$next_month_ts).'",
			cal_year: "'.date("Y",$next_month_ts).'"
		}, 
		OnReloadCalendarReqSuccess
		);
	});
	
	$("#prev_month_link").click(function()
	{
		$.get("ajax.php",
		{
			module: "calendar",
			ibid: "'.$sel_ibid.'",
			cal_month: "'.date("m",$prev_month_ts).'",
			cal_year: "'.date("Y",$prev_month_ts).'"	
		}, OnReloadCalendarReqSuccess);
	});
	
	function OnReloadCalendarReqSuccess(data)
	{
		$("#calendar_div").html(data);
	}
});
</script>
<a href="#" id="prev_month_link" onclick="return false;" class="link_all">&lt;&lt;&lt;</a> <strong>'.$lang['datetime'][date("F",$cur_month_ts)].'</strong> <a href="#" class="link_all" id="next_month_link" onclick="return false;">&gt;&gt;&gt;</a></td>
</tr>';

$result='<div style="padding-left:45px;">
              <h1>Календарь</h1><div style="display:inline" id="calendar_div">'.$calendar->returnCalendar().$date_pick.'</tbody></table>'.'</div>';

$iblockitem->BeginiBlockItemsListRead(array('ibid' => $sel_ibid),array('id','title','idate'),3,'idate','asc','idate BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 YEAR)');
if($iblockitem->GetLQAffectedRows()>0)
{
	$result.='<div class="news">';
	while($item = $iblockitem->Read())
	{
		$itimestamp = strtotime($item['idate']);
		$result.='<div class="data"> '.date("m/d",$itimestamp).'</div>
                <div class="div1">'.$item['title'].'</div>
                <div class="div2"><strong>'.date("H:i",$itimestamp).'</strong></div>';
	}
	$result.='<a href="?module=iblocks&ibid=calendar" class="link_all">&gt;&gt;&gt; <strong>ближайшие</strong></a> </div>';
}
$result.='</div>';

show_window(__('iBlocks calendar'), $result);
?>