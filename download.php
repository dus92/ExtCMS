<?php
require_once ('./modules/engine/api.mysql.php');
require_once ('./modules/system/hcms.php');
if (!empty($_GET['att_file']))
{
	//$att_file = vf($_GET['att_file'], 4);
    $att_file = $_GET['att_file'];    
	if(file_exists($att_file))
	{
		header('Content-Disposition: attachment; filename='.mb_basename($att_file));
		header('Content-type: application/octet-stream');
		readfile($att_file);        
	}
	else
	{
		header('HTTP/1.0 404 Not Found');
		die('404 Not found'); // TODO: readfile 404.html
	}
}
else
{
	header('HTTP/1.0 400 Bad Request');
	die('400 Bad Request'); // TODO: readfile 400.html
}
?>