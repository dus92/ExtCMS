<?php
include_once(CONFIG_PATH.'config.php');
include_once('sql.php');
	
class rmbdJson extends rmbdSql 
{	
	private	$format='json';
	private $files=array();
	public	$action='';
    public	$name='';
	private $configDir= CONFIG_PATH;
	private $logDir='/content/logs/';
	protected $tmpDir='';
	protected $imageDir='';
	private $fileDir='';		
	private $photoSizes=array('big'=>1280,'preview'=>500,'small'=>90);
	public $no_utf8_check=false;
	
	public function handleError($message,$ext='',$email=false) 
	{
		header('Content-Type: text/html; charset=utf-8');
		
		$this->logWrite($message.$ext, false);
		
		foreach($this->files as $file){
			@unlink($file);
		}
		if ($this->format) die('{"success":false, "msg":'.$this->getJSON($message).'}'); else die($message);		
	}
	public function __construct($method = 'POST')
    {        
        ini_set('display_errors', false);
        
        
  		////////////////////from jservice begin////////////////////
		
		$config['log_fileName'] = $this->logDir.'json_log.txt';//имя файла для логов
        $config['dir_img']= $this->imageDir;
				
		$this->config=$config;
		$arr = explode('/', $this->_detect_uri());
        
		if(count($arr)<2) $this->handleError('Не удалось получить адрес');	
	
		$this->name=str_replace(array('/', '.'), '', $arr[0]);
		$this->action=str_replace(array('/', '.'), '', $arr[1]);
	
		if($this->name=='') $this->handleError('Не удалось получить Name');	
		if($this->action=='') $this->handleError('Не удалось получить Action');
		
		if(!array_key_exists('RMBDSESSION', $_SESSION)) $_SESSION['RMBDSESSION'] = '';
		
		$sessionId=$_SESSION['RMBDSESSION'];
						
		$params = array('sessionId'=>$sessionId,'Name'=>$this->name,'Action'=>$this->action);
				
		$this->getParams($method);
        $this->paramsArr['Name'] = $this->name;
        $this->paramsArr['Action'] = $this->action;
        $params = array_merge($params, $this->paramsArr);
        
        ////////////////////from jservice end////////////////////

		$this->action=$this->getCommand('Action');//Проверяем наличие действия
		
		if (!method_exists($this, $this->action)) $this->handleError('Неизвестное действие: "'. $this->action . '"','',true); 
		
		$this->procName=$this->getCommand('Name');//Проверяем наличие имени процедуры
		
		$this->procNameOrig=$this->procName;
		$this->procName="_p_".$this->procName.'_'.$this->action;
		//Проверяем наличие сессии
		//if(!in_array($this->procName,array('_p_authorization_get','_p_changepassword_get','_p_files_get')) && $this->paramValueGet('sessionId')=='') $this->handleError('Не удалось получить сессию');
		
		
        //Подключаемся к БД
		//$this->connect($this->config['db']);
		$func=$this->action.'_before';
		$this->$func();
		
		//$this->getProcParams();		
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
    private function is_utf8($string) {
        //if ($this->no_utf8_check===true)
        	return true;
        // From w3.org/International/questions/qa-forms-utf-8.html
        return preg_match('%^(?:
        [\x09\x0A\x0D\x20-\x7E] # ASCII
        | [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
        | \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
        | \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
        | \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
        )*$%xs', $string);
    }
	protected function utf8fix($val){
		if (is_array($val)) # Если массив, то делаем это с каждым элементом
			return array_map(array(get_called_class(),'utf8fix'), $val);
		# ну а если не массив и на строчке выше не вышли, то преобразуем.
		return mb_convert_encoding($val, "UTF-8", "UTF-8");
	}
	protected function getJSON($data){
		//return json_encode($data, JSON_UNESCAPED_UNICODE);
		return json_encode($this->utf8fix($data), phpversion()>='5.4'?JSON_UNESCAPED_UNICODE:0);
	}
	public function queryOpen($multi=false, $pager = false, $jsonFormat = true, $nocheck = false)
	{			
		# $nocheck = не проверять ответ
		if($pager) $this->pagerGet();
		
		$query = $this->queryProc($pager);
		


		$data = array();

		$recordCount = 0;
		
		$success=false;
		
		if ($multi)
		{			
			if ($this->db->multi_query($query)) 
			{
				$i=0;
				
				do 
				{				
					$temp = array();
					
					if ($result = $this->db->store_result()) 
					{
						while ($row = $result->fetch_array(MYSQLI_ASSOC)) 
						{
							foreach ($row as $key => $value) 
							{
								if (substr($key,0,1)!='#') $tmp[$key]=$value;										
							}
							
							if($i==0)
							{							
								foreach ($row as $key => $value) 
								{
									if ($key=='success')
									{
										if (is_numeric($row['success']))
										{
											if ($row['success']==0)
											{
												if($row['msg']) $this->handleError($row['msg']);
												else $this->handleError('Ошибка при получении данных','',true);
											}
										}
										else $this->handleError('Неверное значение результата','',true);
									}									
								}									
							}
							
							if ($pager)
							{
								if ($i==0) 
								{
									$tmp = array();
																		
									foreach ($row as $key => $value) 
									{
										if (substr($key,0,1)!='#' && $this->is_utf8($value)) $tmp[$key]=$value;	
									}
									
									$data[]=$tmp;
								}
								if ($i==1 && $row['total']) $recordCount=$row['total'];						
							}
							else $temp[]=$row;	
							
						}
						$result->close();
						if($success==false) $success=true;
					}
					
					if (!$pager) $data[]=$temp;	
					
					$this->db->more_results();	

					$i++;							
					
				} while ($this->db->next_result());
			}
		}
		else
		{	
			if ($result = $this->db->query($query))
			{
				$recordCount = $result->num_rows;
			
				while($row = $result->fetch_assoc()) 
				{					
					if ($recordCount==1)
					{
						foreach ($row as $key => $value) 
						{
							if ($key=='success')
							{
								if (is_numeric($row['success']))
								{
									if ($row['success']==0)
									{
										if($row['msg']) $this->handleError($row['msg']);
										else $this->handleError('Ошибка при получении данных','',true);
									}
								}
								else $this->handleError('Неверное значение результата','',true);
							}
						}
					}	
							
					$tmp = array();
					if(count($data)==0)
						$this->save_after($row);
												
					foreach ($row as $key => $value) 
					{
						if (substr($key,0,1)!='#' && $this->is_utf8($value)) $tmp[$key]=$value;	
					}
					
					$data[] = $tmp;				
				}

				$result->close();	
				if($success==false) $success=true;				
			}			
			while($this->db->next_result()) $this->db->store_result();									
		}
		
		if(!$success && !$nocheck) $this->handleError('Не удалось выполнить команду ',$this->db->error,true); 

		$func=$this->action.'_after';
		$this->$func($data);
		
		if($jsonFormat)
		{
			if ($recordCount>0)	die('{"success": true, "total":"'.$recordCount.'","rows":'.$this->getJSON($data).'}');
			else die('{"success": true, "total":"0","rows":""}');
		}
		else return $data;			
	}
	public function store()//Функция получения store
	{			
		$this->queryOpen(false, false);
	}
	public function storePager()//Функция получения store  с постраничным просмотром
	{			
		$this->queryOpen(true, true);
	}
	public function catalogs()//Функция вывода справочников
	{			
		$result='';
		
		$data=$this->queryOpen(true, false, false);		
		
		die($result);
	}
	
	private function save_after($row){
		// echo '/* ';
		// var_dump(array_key_exists('#event_tokens',$row),!empty($row['#event_tokens']));
		// echo ' */';
		if (array_key_exists('#event_tokens',$row)&&!empty($row['#event_tokens'])){
			$this->sendEvent(array('channel'=>$row['#event_tokens'],'message'=>$row['#event_message']));
		}
	}
	public function save()//Функция сохранения
	{
		$query = $this->queryProc();
		

		if ($result = $this->db->query($query))
		{		
			$row = $result->fetch_assoc();
			
			if (is_numeric($row['success']))
			{
				if ($row['success']==1)
				{
					unset($row['success']);

					$this->save_after($row);
					
					$tmp = array();			
					$func=$this->action.'_after';
					$this->$func($row);

					foreach ($row as $key => $value) 
					{
						if (substr($key,0,1)!='#' && $this->is_utf8($value)) $tmp[$key]=$value;	
					}
					
					$row = $tmp;
					
					if(count($row)>0) echo $this->getJSON(array('success' => true, 'data' => $row));
					else echo $this->getJSON(array('success' => true));
				}
				else
				{
					if($row['msg']) $this->handleError($row['msg']);
					else $this->handleError('Ошибка при получении данных',$this->db->error,true);
				}
			}
			else $this->handleError('Неверное значение результата','',true);
	
			$result->close();			
		}
		else  
		{
			$this->handleError('Не удалось выполнить команду',$this->db->error,true);
			
			$this->logWrite("Не удалось выполнить команду - Errormessage: %s\n".$this->db->error);
		}
			
		while($this->db->next_result()) $this->db->store_result();		
	}
	public function card()//Функция получения карточки редактирования
	{
		$this->save();
	}
	public function add()
	{
		$this->save();
	}
	public function get()
	{
		$this->save();
	}
	public function del()
	{
		$this->save();
	}
	public function total()
	{
		$this->store();
	}
	public function photoadd()//Функция добавления фото
	{
		
		$this->save();
	}
	public function fileadd()//Функция добавления файлов(для комплексов новостроек)
	{
		$this->save();
	}
	
	public function view()//Функция выводит карточки: объектов, сотрудников, фирмы
	{
		$data=$this->queryOpen(true, false, false);
		
		switch($data[0][0]['cardType'])
		{			
			case 'firm':
					$record = array();
		
					foreach ($data[0][0] as $key => $value) $record[$key]=$value;	
					
					die('{"success": true, "data":'.$this->getJSON($record).'}');
				break;
			case 'client':
				$record = array();
				$contacts = array();
	
				foreach ($data[1][0] as $key => $value) $record[$key]=$value;
				foreach ($data[2] as $key => $value) $contacts[$key]=$value;
				
				die('{"success": true, "data":'.$this->getJSON($record).', "contacts":'.$this->getJSON($contacts).', "isObjectClient":'.$data[0][0]['isObjectClient'].'}');
			case 'object':
					$object = array();
					$photos = array();
					$files = array();
		
					foreach ($data[1][0] as $key => $value) $object[$key]=$value;
			
					foreach ($data[2] as $key => $value) if ($value!=null) $photos[$key]=$value;
					
					foreach ($data[3] as $key => $value) if ($value!=null) $files[$key]=$value;
					
					die('{"success": true, "object":'.$this->getJSON($object).', "photos":'.$this->getJSON($photos).', "files":'.$this->getJSON($files).'}');
				break;
			case 'staff':
					$record = array();
					$contacts = array();
					$boss = array();
		
					foreach ($data[1][0] as $key => $value) $record[$key]=$value;
					foreach ($data[2][0] as $key => $value) $record[$key]=$value;
					foreach ($data[3] as $key => $value) $contacts[$key]=$value;
					foreach ($data[4] as $key => $value) $boss[$key]=$value;
					
					die('{"success": true, "data":'.$this->getJSON($record).', "contacts":'.$this->getJSON($contacts).', "boss":'.$this->getJSON($boss).'}');
				break;	
		}	
	}
	public function storeTree()//Функция строит дерево
	{		
		include_once('tree.php');
		
		$data = $this->queryOpen(false, false, false);
		
		new rmbdTree($data);
	}

	public function data() // Функция получает набор данных.
	{
		$query = $this->queryOpen(true, false, false);
		// var_dump($query);
		echo $this->getJSON(array('success'=>true,'data'=>$query));
		exit;
	}
	
	public function checkPath($path, $folder){
		$path .= $folder.'/';	
		if(!file_exists($path))
		if(!mkdir($path, 0777, true)) $this->handleError('Не удалось создать каталог '.$folder);
		
		return $path;
	}
}	
?>