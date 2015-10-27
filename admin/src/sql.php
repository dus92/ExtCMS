<?php
class rmbdSql
{
	protected $config;
	
	protected $db=false;
		
	protected $procName;
	protected $procNameOrig;
	
	protected $paramsArr; //Массив полученных параметров	
	
	protected $paramsSql = '';//Параметры для процедуры sql
	protected $lastQuery = '';// Последний запрос к БД 
		
	private $limit;//Ограничение кол-во объектов для вывода 
	private $parser=null;
	
	public function getParams($method = 'GET', $limit=50)
	{		
		if ($method == 'POST') $this->getPOST(); 
		if ($method == 'GET')  $this->getGET();
		
		$this->limit = $limit;
	}
	public function logWrite($msg, $exit=true)
	{		
		if($this->config['log'])
		{
			date_default_timezone_set('Europe/Moscow');
			
			$date = date('Y-m-d H:i:s');
		
			file_put_contents($this->config['log_fileName'], $date.'  '.$msg."\r\n", FILE_APPEND);			
		}
		if($exit) die();
	}
	public function connect($config)//Подключаемся к БД
	{		
        $this->db = new mysqli($config['server'], $config['username'], $config['password'], $config['database'], $config['port']);
							   
        if ($this->db->connect_error) $this->logWrite('Connect Error ('.$this->db->connect_errno.')'.$this->db->connect_error);
		
		$this->db->set_charset("utf8");	
	}
	public function getProcParams() //Получаем параметры процедуры
	{
		$this->paramsSql = '';
		
		$query="SELECT name, type, valueMin, valueMax, length, required, `default` FROM s_procparams WHERE procName='$this->procName';";

		if ($result = $this->db->query($query))
		{		
			while($row = $result->fetch_assoc()) $this->paramGet($row['name'], $row['type'], $row['valueMin'], $row['valueMax'], $row['length'], $row['required'], $row['default']);
			
			$result->close();			
		}
		else $this->logWrite("Errormessage: ".$this->db->error);
	}
	public function queryProc($pager = false)
	{		
		$query = "CALL ".$this->procName."(".$this->paramsSql.");";
		$this->lastQuery=$query;
		$this->logWrite($query, false);
		
		return $query;
	}
	public function __destruct()
    {
		if($this->db) {
			while($this->db->next_result()) $this->db->store_result();
			$this->db->close();	
		}
		
		$this->db=false;
    }	
	
	public function __call($method,$params){
		if (file_exists(dirname(__FILE__).'/php/'.$this->procNameOrig.'_'.$method.'.php')){
			include(dirname(__FILE__).'/php/'.$this->procNameOrig.'_'.$method.'.php');
		}
	}
	
	public function getCommand($name, $maxlen=50, $msg='Не задана команда -') 
	{
		$result='';
		
		$value = $this->paramValueGet($name);
		
		if ($value=='') $this->logWrite($msg.' "'.$name.'"');
		else
		{					
			$value=mysql_escape_string(htmlspecialchars(strip_tags($value)));			
		
			$result=mb_substr($value, 0, $maxlen, 'utf-8');
		}	
		
		return $result;
	}
	private function getPOST() 
	{
		$vars = array();	
		
		$pairs = explode("&", file_get_contents("php://input"));
		foreach ($pairs as $pair) 
		{
			$nv = explode("=", $pair);

			$name = urldecode($nv[0]);
			$value = urldecode($nv[1]);

			if (($name=='group' || $name=='sort') && json_decode($value))#Если грид шлет группировку или сортировку
			{					
				$arr=json_decode($value, true);
				
				if (count ($arr)>0)
				{		
					$value='';
					
					for ($i =0; $i < count ($arr); $i++) {
						
						$desc=false;
						
						foreach ($arr[$i] as $key => $val) 
						{	
							if ($key=='property') 
							{
								if ($value!='') 
								{
									$value=$value.',';
								}
								
								$value=$value.$val;
							}
							if ($key=='direction' && $val=='DESC') $desc=true;
						}
						
						if($desc) $value=$value.' desc';
					}
					
					if ($value!='')	$vars[$name] = $value;
				}
			}
			else $vars[$name] = $value;
		}
		
		$this->paramsArr=$vars;
	}
	private function getGET() 
	{
		$vars = array();	
		
		foreach($_GET as $name => $value) $vars[$name] = $value;
 
		$this->paramsArr=$vars;
	}
	protected function paramValueGet($name) 
	{
		$result='';
		
		if(isset($this->paramsArr[$name])) $result = $this->paramsArr[$name];		
					
		return trim($result);			
	}
	private function paramAdd($value) 
	{
		if ($this->paramsSql!='') $this->paramsSql.=',';
			
		$this->paramsSql.=$value;					
	}
	protected function pagerGet() 
	{
		$start	= $this->paramValueGet('start');
		$limit	= $this->paramValueGet('limit');
			
		if($start=='' || is_numeric($start)==false) $start=0; 
			
		if($limit=='' || is_numeric($limit)==false) $limit=$this->limit; 
		
		if ($start<0)
			$start=0;
		
		if($limit>$this->limit) $limit=$this->limit; 
		
		$this->paramsArr['start']=$start;
		$this->paramsArr['limit']=$limit;	
	}
	protected function paramGet($name, $type, $valueMin, $valueMax, $maxlen, $required, $default) 
	{
		$result='Null';
		
		$parTypeName='';
		$value = $this->paramValueGet($name);	
		
		if ($value!='')
		{		
			switch(trim($type))
			{			
				case 'date':
					$parTypeName='дата';
					
					$value=trim(mb_substr($value, 0, 10, 'utf-8'));
					
					if (strlen($value)==10)
					{
						$arr=explode ("-" , $value);
						
						if (count($arr)==3)
						{	
							$y=(int)$arr[0];
							$m=(int)$arr[1];
							$d=(int)$arr[2];
							
							if ($d>=1 && $d<=31 && $m>=1 && $m<=12 && $y>=1900 && $y<=2100 && checkdate($m, $d, $y)) $result="'$y-$m-$d'";
						}			
					}
					break;	
				case 'datetime':
					$parTypeName='';
					break;	
				case 'longtext':
					$parTypeName='текстового';
					
					$value= preg_replace('#<(\\\\/)?(b|i|font|u|a|br|span|div|img|hr|script|style)(\s.*?)?(\\\\/)?>#','',$value);
					$value= rtrim($value, '\\');
			
					$value= mysql_escape_string($value);	
					
					if ($required==true && mb_strlen($value,'utf-8')==0) 
					$this->handleError("Неверная длина $parTypeName значения параметра '$name'",'',true);
				
					$result="'".mb_substr($value, 0, $maxlen, 'utf-8')."'";							
					
					if ($default=='') $default="''";	
					
					break;				
				case 'text':
					$parTypeName='текстового';
					
					if($this->parser===null){
						require_once('html.php');
						$this->parser=new htmlParser();
						$this->parser->initAllow();
					}
					$value=$this->parser->parse($value);
					// $value= strip_tags($value,'<b><i><font><u><a><br><blockquote>');

					$value= rtrim($value, '\\');
			
					$value= mysql_escape_string($value);	
					
					if ($required==true && mb_strlen($value,'utf-8')>$maxlen) 
					$this->handleError("Неверная длина $parTypeName значения параметра '$name'",'',true);
				
					$result="'".mb_substr($value, 0, $maxlen, 'utf-8')."'";							
					
					if ($default=='') $default="''";	
					
					break;	
				case 'string':
					$parTypeName='строчного';
					$value=str_replace("'",'`', $value);
					$value=str_replace('"','`', $value);
						
					$value= rtrim($value, '\\');
			
					$value==mysql_escape_string(htmlspecialchars(strip_tags($value)));		
					
					if ($required==true && mb_strlen($value,'utf-8')>$maxlen) 
					$this->handleError("Неверная длина $parTypeName значения параметра '$name'",'',true);
				
					$result="'".mb_substr($value, 0, $maxlen, 'utf-8')."'";	
					
					if ($default=='') $default="''";
					break;	
				case 'number':
					
					$parTypeName='числового';
					
					$value= rtrim($value, '\\');
			
					$value=str_replace(',','.',$value);
					
					if($maxlen==1)
					{						
						if (is_numeric($value))
						{		
							if((int)$value==1)$result=1;	
							if((int)$value==0)$result='0';	
						}
						else
						{
							if($value=='true')$result=1;
							if($value=='false')$result='0';
						}						
							
						if ($default=='true') $default=1;
						if ($default=='false' || $default=='0') $default='0';
					}
					else
					{
						if (is_numeric($value))
						{							
							if ($required==true && mb_strlen($value,'utf-8')>$maxlen) 
							$this->handleError("Неверная длина $parTypeName значения параметра '$name $value'",'',true);
							
							if ($value>=$valueMin && $value<=$valueMax) $result=$value;						
						}
					}
					break;
				default:
					$this->handleError("Получен параметр неизвестного типа",'',true);
			}
		}
		
		
		if ($default!='Null' && $result=='Null') $result=$default;
		
		if ($required==true && $result=='Null') 
		$this->handleError("Неверное $parTypeName значения параметра '$name' $type $value",'',true);
		
		$this->paramAdd($result); 
				
		return $result;
	}
	public function debug_trace($delim="\r\n"){
		$text=array();
		$d=debug_backtrace();
		foreach($d as $l){
			$line='';
			if (array_key_exists('file',$l))
				$line.=$l['file'];
			if (array_key_exists('line',$l))
				$line.=':'.$l['line'];
			$line.=' ';
			if (array_key_exists('class',$l))
				$line.=$l['class'].$l['type'];
			$line.=$l['function'].'(';
			$args=array();
			foreach($l['args'] as $a){
				if (is_int($a))
					$args[]=$a;
				else
				if (is_array($a))
					$args[]='array['.count($a).']';
				else
				if (is_object($a))
					$args[]='object['.get_class($a).']';
				else
				if ($a===NULL)
					$args[]='NULL';
				else
					$args[]='"'.addslashes($a).'"';
			}
			$line.=implode(', ',$args).');';
			$text[]=$line;
		}
		return implode($delim,$text);
	}
}	
?>