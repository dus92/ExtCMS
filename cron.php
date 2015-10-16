<?php
define('RCMS_ROOT_PATH', './');
define('TASKS_PATH',       	 RCMS_ROOT_PATH . 'modules/tasks/');
require_once(RCMS_ROOT_PATH . 'modules/system/hcms.php');

if(file_exists(RCMS_ROOT_PATH.'config/tasks.dat.block'))
{
	if(filectime(RCMS_ROOT_PATH.'config/tasks.dat.block')+2 > time())
	{
		// wait
	}
	else
	{
		// ignore lock
		unlink(RCMS_ROOT_PATH.'config/tasks.dat.block');
	}
}
else
{

}
$config = unpack_data(file_get_contents(RCMS_ROOT_PATH.'config/tasks.dat'));

$tnum = sizeof($config['tname']);
$keys = array_keys($config['tname']);
for($i=0; $i<$tnum; $i++)
{
	if($config['tstate'][$keys[$i]] == '1' && $config['ttime'][$keys[$i]] == '* * * * *' && file_exists(TASKS_PATH.$config['tfile'][$keys[$i]]))
	{
		print('Performing task "'.$config['tname'][$keys[$i]].'"...');
		touch(RCMS_ROOT_PATH.'cron.dat');
		require_once(TASKS_PATH.$config['tfile'][$keys[$i]]);
		print("done\r\n");
	}
}
?>