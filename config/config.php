<?php
//require_once('/.bknconfig/mbdConfig.php');

$config = array();

//$config['dir']=str_replace(array('/rmbd/','/data'),'',$_SERVER['DOCUMENT_ROOT']);

//require_once('/.bknconfig/'.$config['dir'].'.php');

//Межагентская база
$config['server'] = 'localhost';
$config['database'] = 'db_extcms';
$config['username'] = 'root';
$config['password'] = 'root';
$config['port'] = 3306;

// полный путь к каталогу файлового хранилища
$config['temp_dir'] = str_replace('/data','',$_SERVER['DOCUMENT_ROOT']).'/images/tmp/'; 
$config['img_dir'] = str_replace('/data','',$_SERVER['DOCUMENT_ROOT']).'/images/';
$config['log'] = ADMIN_PATH.'logs/';

function logWrite($msg)
{
	global $config;

	if($config['log'])
	{
		date_default_timezone_set('Europe/Moscow');
		
		$date = date('Y-m-d H:i:s');
	
		file_put_contents($config['log'].'log.txt', $date.'  '.$msg."\r\n", FILE_APPEND);
	}
}
