<?php
include_once('json.php');

header('Content-Type: application/json; charset=utf-8');

try 
{
    $json = new rmbdJson();
} 
catch( Exception $e ) 
{
	die('{success:false, msg:'.json_encode($e->getMessage()).'}'); 
}

try 
{
    call_user_func(array($json,  $json->action));
} 
catch( Exception $e ) 
{
	$json->logWrite('Ошибка. '.$e->getMessage());
    die('{success:false, msg:'.json_encode($e->getMessage()).'}'); 
}

?>