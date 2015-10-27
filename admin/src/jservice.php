<?php
class jService
{
	private $config;
	
	private $name = '';
	private $action = '';
	private $prefix = '';
	private $configDir = CONFIG_PATH;
	private $logDir = '/content/logs/';
	private $tmpDir='/home/project/bknpro/tmp/';
	private $imageDir='/home/project/bknpro/images/';
	private $fileDir='/home/project/bknpro/files/';
	private $mainFileDir='/home/project/bknpro/files/main/';
	
	public function __construct($method = 'POST')
    {
        ini_set('display_errors', false);		

//		if (array_key_exists('ORG_NAME',$_SERVER)){
//			$dir=$_SERVER['ORG_NAME'];
//			$this->imageDir.=$dir.'/';
//			$this->fileDir.=$dir.'/';
//		}else{
//			if (preg_match('#^console\.(dev\.|test\.)?([a-z\-0-9]+)\.([a-z\-0-9]+\.)?(pro\.bkn\.ru|rmbd\.ru)#i',$_SERVER['SERVER_NAME'],$match)){
//				$dir=$match[2];
//				$this->imageDir=$_SERVER['DOCUMENT_ROOT'].'/images/';
//				$this->fileDir=$_SERVER['DOCUMENT_ROOT'].'/images/';
//			}else{
//				// Тут бы ругаться.
//				echo 'No input file specified. Unknown service.';
//				exit;
//			}
//		}
		// $dir = str_replace(array('/rmbd/','/console'),'',$_SERVER['DOCUMENT_ROOT']);		
				
		require_once($this->configDir.'config.php');
		
		$config['log_fileName'] = $this->logDir.'json_log.txt';//имя файла для логов
		$config['dir_temp'] = $this->tmpDir; // str_replace('console','',$_SERVER['DOCUMENT_ROOT']).'images/tmp/';//Директория для временных файлов 
		
		
		$this->config=$config;
		$arr = explode('/', $this->_detect_uri());
        
		if(count($arr)<2) $this->handleError('Не удалось получить адрес');	
	
		$this->name=str_replace(array('/', '.'), '', $arr[0]);
		$this->action=str_replace(array('/', '.'), '', $arr[1]);
	
		if($this->name=='') $this->handleError('Не удалось получить Name');	
		if($this->action=='') $this->handleError('Не удалось получить Action');
		
		//$url_my = 'http://data.'.$_SERVER['HTTP_HOST'].'/index.php';//адрес json сервиса базы агентства
		
		//session_start();
		
		if(!array_key_exists('RMBDSESSION', $_SESSION)) $_SESSION['RMBDSESSION'] = '';
		
		$sessionId=$_SESSION['RMBDSESSION'];
		$url = RCMS_ROOT_PATH.'api.php';
						
		$params = array('sessionId'=>$sessionId,'Name'=>$this->name,'Action'=>$this->action);
		
		if ($method=='POST') $data = $this->getPOST();
		if ($method=='GET')  $data = $this->getGET();
		$params = array_merge($params, $data);
        //$response=$this->getContent($url, $params);
		
        //return die($response);
    }
	private function checkResult($response)
	{		
		$result = (array)json_decode($response, true);
			
		if(array_key_exists('success', $result))
		{
			if($result['success']==1) return $result;
		}
		return false;
	}
	public function logWrite($msg)
	{
		if($this->config['log'])
		{
			date_default_timezone_set('Europe/Moscow');
			
			$date = date('Y-m-d H:i:s');
			
			file_put_contents($this->config['log_fileName'], $date.'  '.$msg."\r\n", FILE_APPEND);
			
			//die($msg);
		}
	}
	public function handleError($message)//Функция вывода ошибки
	{
		$this->logWrite($message);
		
		die('{"success":false, "msg":'.json_encode($message).'}');
	}
	private function _detect_uri()//Функция разбора url
	{
		if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME'])) return '';

		$uri = $_SERVER['REQUEST_URI'];
		
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (strncmp($uri, '?/', 2) === 0)
		{
			$uri = substr($uri, 2);
		}
		$parts = preg_split('#\?#i', $uri, 2);
		$uri = $parts[0];
		if (isset($parts[1]))
		{
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		}
		// else
		// {
			// $_SERVER['QUERY_STRING'] = '';
			// $_GET = array();
		// }

		if ($uri == '/' || empty($uri))
		{
			return '/';
		}

		$uri = parse_url($uri, PHP_URL_PATH);

		// Do some final cleaning of the URI and return it
		return str_replace(array('//', '../'), '/', trim($uri, '/'));
	}
	private function getPOST() 
	{
		$vars = array();	

		foreach($_POST as $name => $value) 
		{
			$vars[$name] = $value;
			
			if($name=='prefix') $this->prefix=$value;
		}

		return $vars;
	}
	private function getGET() 
	{
		$vars = array();	
		
		foreach($_GET as $name => $value) 
		{
			$vars[$name] = $value;
			
			if($name=='prefix') $this->prefix=$value;
		}
 
		return $vars;
	}
	
	private function getContent($url, $params)//Функция получает контента
	{

		$content = http_build_query($params);

        $params = array('http' => array(
            'method' => 'POST',
            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
          . "Content-Length: " . strlen($content) . "\r\n",
            'content' => $content

        ));
		
        $ctx = stream_context_create();

        stream_context_set_option($ctx, $params);
		
        $fp = @fopen($url, "rb", false, $ctx);
		
        if (!$fp) $this->handleError('Не удалось получить данные от сервера1.');

        $response = stream_get_contents($fp);
		
		if ($response === false) $this->handleError('Не удалось получить данные от сервера2.');
								
		return $response;
	}
}
?>