<?php

$frm = new InputForm('','post',__('Submit'));
if(!empty($_POST['save']))
{
	$ibmenu_opt['ibid'] = vf($_POST['ibid'],4);
	$ibmenu_opt['newscount'] = vf($_POST['newscount'],3);
	$ibmenu_opt['daily_id'] = vf($_POST['daily_id'],3);
	$ibmenu_opt['daily_qid'] = vf($_POST['daily_qid'],3);

	if(file_write_contents(IBLOCKS_PATH.'ibmenu.dat',pack_data($ibmenu_opt)))
	{
		$frm->addmessage(__('Data saved'));
	}
}
// form
$ibmenu_opt = @unpack_data(file_get_contents((IBLOCKS_PATH.'ibmenu.dat')));

$frm->addbreak(__('Configure iBlocks menu'));
$frm->hidden('save',1);
$frm->addrow(__('iBlock id to get data'),$frm->text_box('ibid',@$ibmenu_opt['ibid']));
$frm->addrow(__('News display count'),$frm->text_box('newscount',@$ibmenu_opt['newscount']));
$frm->addrow(__('Daily news item id'),$frm->text_box('daily_id',@$ibmenu_opt['daily_id']));
$frm->addrow(__('Daily question item id'),$frm->text_box('daily_qid',@$ibmenu_opt['daily_qid']));
$frm->show();
?>