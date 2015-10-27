<?php
include_once('sql.php');
	
class rmbdJson extends rmbdSql 
{	
	private	$format='json';
	private $files=array();
	public	$action='';
	private $configDir='/home/project/bknpro/config/';
	private $logDir='/home/project/bknpro/logs/console/';
	protected $tmpDir='/home/project/bknpro/tmp/';
	protected $imageDir='/home/project/bknpro/images/';
	private $fileDir='/home/project/bknpro/files/';
	private $fileMainDir='/home/project/bknpro/files/main/';
	private $service='/home/project/bknpro/service';
	private $reportDir='reports/';
	private $photoSizes=array('big'=>1280,'preview'=>500,'small'=>90);
	public $no_utf8_check=false;
	private $email4error='semeon@yandex.ru, bknprofi-error@lugovov.ru';
	
	public function handleError($message,$ext='',$email=false) 
	{
		header('Content-Type: text/html; charset=utf-8');
		
		$this->logWrite($message.$ext, false);
		
		foreach($this->files as $file){
			@unlink($file);
		}
		if ($email){
			$this->mailError($message,$ext);
			$message.='<br/>Информация об ошибке отправлена разработчикам и будет исправлена в ближайшее время.';
		}
		if ($this->format) die('{"success":false, "msg":'.$this->getJSON($message).'}'); else die($message);		
	}
	private function mailError($message,$text=''){
		        
		$from = 'error-'.$this->config['db']['database'].'@pro.bkn.ru';
		$to = $this->email4error;
		$subj = 'Ошибка в процедуре у '.$_SERVER['SERVER_NAME'];
		$params=array();
		foreach($this->paramsArr as $k=>$p)
			$params[]=htmlspecialchars($k.' = "'.$p.'"');
		$text = $message."<br/><b>".$this->lastQuery."</b><br/><br/>Текст ошибки: <b>".$text."</b><br/><br/>Процедура: ".htmlspecialchars($this->procName)."<br/>Входные параметры:<pre>".implode("\r\n",$params)."</pre><br/><br/>Стек вызова:<br/><pre>".$this->debug_trace('<br/>').'</pre>';
		$un        = strtoupper(uniqid(time()));
		$head      = "From: {$from}\n";
		$head     .= "Subject: $subj\n";
		$head     .= "X-Mailer: bknProfiMailer\n";
		$head     .= "Reply-To: {$from}\n";
		$head     .= "Mime-Version: 1.0\n";
		$head     .= "Content-Type:multipart/mixed;";
		$head     .= "boundary=\"----------".$un."\"\n\n";
		$zag       = "------------".$un."\nContent-Type:text/html;\n";
		$zag      .= "Content-Transfer-Encoding: 8bit\n\n$text\n\n";
		foreach($this->files as $filename){
			if (filesize($filename)==0) continue;
			$f         = fopen($filename,"rb");
			$zag      .= "------------".$un."\n";
			$zag      .= "Content-Type: application/octet-stream;";
			$zag      .= "name=\"".basename($filename).".gz\"\n";
			$zag      .= "Content-Transfer-Encoding:base64\n";
			$zag      .= "Content-Disposition:attachment;";
			$zag      .= "filename=\"".basename($filename).".gz\"\n\n";
			$zag      .= chunk_split(base64_encode(gzencode(fread($f,filesize($filename)),9)))."\n";
			fclose($f);
		}
		@mail("$to", "$subj", $zag, $head);

	}
	public function __construct()
    {

		ini_set('display_errors', false);
		if (array_key_exists('ORG_NAME',$_SERVER)){
			$dir=$_SERVER['ORG_NAME'];
			$this->imageDir.=$dir.'/';
			$this->fileDir.=$dir.'/';
		}else{
			if (preg_match('#^data\.(dev\.|test\.)?([a-z\-0-9]+)\.([a-z\-0-9]+\.)?(pro\.bkn\.ru|rmbd\.ru)#i',$_SERVER['SERVER_NAME'],$match)){
				$dir=$match[2];
				$this->imageDir=$_SERVER['DOCUMENT_ROOT'].'/images/';
				$this->fileDir=$_SERVER['DOCUMENT_ROOT'].'/images/';
				// $this->reportDir=$_SERVER['DOCUMENT_ROOT'].'/reports/';
			}else{
				// Тут бы ругаться.
				echo 'No input file specified. Unknown service.';
				exit;
			}
		}
		$this->reportDir=dirname(__FILE__).'/reports/';
		//$dir = str_replace(array('/rmbd/','/data'),'',$_SERVER['DOCUMENT_ROOT']);	
			
		require_once($this->configDir.'/mbdConfig.php');
		require_once($this->configDir.'/'.$dir.'.php');
		
		$config['log_fileName'] = $this->logDir.'/_'.$dir.'.txt';//имя файла для логов
//		$config['log_fileName']='/home/project/bknpro/logs/console/_'.$dir.'.txt';
		
		$config['dir_img']= $this->imageDir;//'/home/project/bknpro/images/'.$dir.'/';//str_replace('/data','',$_SERVER['DOCUMENT_ROOT']).'/images/';				
		// $config['path_reports']= $this->reportDir;//'/home/project/bknpro/tmp/';//str_replace('/data','/console',$_SERVER['DOCUMENT_ROOT']).'/reports/';				
		
		$this->config=$config;
			
		$this->getParams('POST');	

		$this->action=$this->getCommand('Action');//Проверяем наличие действия
		
		if (!method_exists($this, $this->action)) $this->handleError('Неизвестное действие: "'. $this->action . '"','',true); 
		
		$this->procName=$this->getCommand('Name');//Проверяем наличие имени процедуры
		
		if($this->action=='app') 
		{
			$this->appName=$this->procName;
			$this->procName='moduleAllowed';
			$this->paramsArr['name']=$this->appName;
		}
		$this->procNameOrig=$this->procName;
		$this->procName="_p_".$this->procName.'_'.$this->action;

		if($this->action=='rotatePhoto') 
		{
			$this->procName="_p_objectsphotostemp_rotate";
			$this->action="save";
			// $this->rotatePhoto();
		}
		
		if($this->procName=='_p_report_get') $this->action="report";		
		
		//Проверяем наличие сессии
		if(!in_array($this->procName,array('_p_authorization_get','_p_changepassword_get','_p_files_get')) && $this->paramValueGet('sessionId')=='') $this->handleError('Не удалось получить сессию');
		
		
		if($this->action=='card_xls') $this->procName = "_p_card_view";
		
		
        //Подключаемся к БД
		$this->connect($this->config['db']);
		$func=$this->action.'_before';
		$this->$func();
		
		$this->getProcParams();		
    }
	
	/*function CheckData($in){
		if(is_object($in)){
			$in = (array)$in;
		}
		if(is_array($in)){
			foreach ($in as $k => $v) {
				if(is_array($in[$k]) or is_object($in[$k])){
					$in[$k] = CheckData($in[$k]);
				}else{
					if(!is_utf8($in[$k])){
							$in[$k] =  utf8_encode($in[$k]);
					}
				}
			}
		return $in;
		}
		if(!is_utf8($in)){
			return utf8_encode($in);
		}else{
			return($in);
		}
	}*/
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
		
		for($i=0; $i<count($data[0]); $i++)
		{
			if($result!='') $result.=",\n";			
			$result.="'".$data[0][$i]['name']."': Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/catalog/store', extraParams:{name:'".$data[0][$i]['name']."'}})";
		}
		
		if($result!='') $result="{\n".$result."\n}";
		
		$result="Ext.define('model_catalogs', {
		extend: 'Ext.data.Model',
		fields: [
			{name:'id', type:'string'},
			{name:'name', type:'string'},
			{name:'shortname', type:'string'},
			{name:'filter', type:'string'}
		]
});\n var store_catalogs_objstaff = [];
store_catalogs_objstaff['s'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'s'}});
store_catalogs_objstaff['z'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'z'}});
store_catalogs_objstaff['r'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'r'}});
store_catalogs_objstaff['p'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'p'}});
store_catalogs_objstaff['c'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'c'}});
store_catalogs_objstaff['f'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'f'}});
store_catalogs_objstaff['d'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'d'}});
store_catalogs_objstaff['b'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'b'}});
store_catalogs_objstaff['m'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/mediacoststaff/store'});
store_catalogs_objstaff['cc'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'cc'}});
store_catalogs_objstaff['dm'] = Ext.create('rmbd.data.Store',{model:'model_catalogs',url:'data.php/objectsstaff/store', extraParams:{prefix:'dm'}});
\n  var store_catalogs = new Object(".$result.");\n";
//_p__store


		for($i=0; $i<count($data[3]); $i++)
		{
			if($result!='') $result.="\n";			
			$result.=$data[3][$i]['catalog'];
		}

		if($result!='') $result.="\n";		
		
		/*
		for($i=0; $i<count($data[1]); $i++)
		{
			foreach($data[1][$i] as $row=>$value)
			{	
				
			}
		}
		*/
		
		$result .= 'var rmbdDateBirthData = '.$this->getJSON($data[1]).';';	
		
		$user='';
		
		foreach ($data[2][0] as $key => $value) 
		{
			if ($user!=''){$user.=",";}
			
			$user.='"'.$key.'":"'.$value.'"';									
		}
		
		$result.=' var rmbdUser = new Object({'.$user.'});'."\r\n";
		
		
		for($i=0; $i<count($data[4]); $i++)
		{
			$menuData[]=array(
						'id' => $data[4][$i]['id'],
						'group' => $data[4][$i]['group'],
						'caption' => $data[4][$i]['caption'],
						'name' => $data[4][$i]['name'],
						'icon' => $data[4][$i]['icon'],
						'menuName' => $data[4][$i]['menuName'],
						'menuIcon' => $data[4][$i]['menuIcon']
					);
					
		}
		
		$result .= 'var rmbdMenuData = '.$this->getJSON($menuData).';';
		
		
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
	
					if($this->procName=='_p_objectsphotostemp_photoadd' || $this->procName=='_p_objectsphotos_photoadd')
					{
						$path=$this->config['dir_img'];
						$path_wm = $path.'company_logo/';
						$wm_big = 'watermark_big.png';
						$wm_preview = 'watermark_preview.png';
						$wmPosition = $row['wmPosition'];
						$wmOpacity = $row['wmOpacity'];
						
						$path_big = $this->checkPath($path, $row['#prefix'].'_big');
						$path_preview = $this->checkPath($path, $row['#prefix'].'_preview');
						$path_small = $this->checkPath($path, $row['#prefix'].'_small');						
						$path_orig = $this->checkPath($path, $row['#prefix'].'_orig');
						
						$fileName=$row['#fileName'];
						
						$sFileName=$this->tmpDir.$fileName;
							
						$this->files[]=$sFileName;
						if(!file_put_contents($sFileName, utf8_decode($this->paramsArr['PhotoAdd']))) $this->handleError('Не удалось сохранить файл ');
						
						require_once(dirname(__FILE__).'/images.php');
						$im=new rmbdImages();
						$im->setHandle($this);
						$params=array('source'=>$sFileName);
						if($row['#prefix'] != 'b'&&file_exists($path_wm.$wm_big)){
							$params['watermark']=array(
								'opacity'=>$row['wmOpacity'],
								'position'=>$row['wmPosition'],
								'rotate'=>$row['wmRotate'],
								'file'=>$path_wm.$wm_big
							);
						}
						$params['dest']=array(
							array('file'=>$path_orig.$fileName,'max'=>$this->photoSizes['big'],'water'=>array('size'=>500,'write'=>false)),
							array('file'=>$path_big.$fileName,'max'=>$this->photoSizes['big'],'water'=>array('size'=>500,'write'=>true)),
							array('file'=>$path_preview.$fileName,'max'=>$this->photoSizes['preview'],'water'=>array('size'=>200,'write'=>true)),
							array('file'=>$path_small.$fileName,'max'=>$this->photoSizes['small'],'water'=>array('size'=>200,'write'=>false))
						);
						
						$im->processImage($params);
			
					
						
						$this->files[]=$path_orig.$fileName;
						// $this->imageResize($path_orig, $fileName, 1280, 1280, 100);
						
						$this->files[]=$path_big.$fileName;						
						// $this->imageResize($path_big, $fileName, 1280, 1280, 100);
						
						$this->files[]=$path_preview.$fileName;						
						// $this->imageResize($path_preview, $fileName, 500, 500, 100);
						//$im = imagecreatefromjpeg($path_preview.$fileName);
						
						// if($row['#prefix'] != 'b'){
							// if(file_exists($path_wm.$wm_big)) $this->ImageAddLogo($path_big.$fileName, $path_wm.$wm_big, $wmPosition, $wmOpacity);
							// if(file_exists($path_wm.$wm_preview)) $this->ImageAddLogo($path_preview.$fileName, $path_wm.$wm_preview, $wmPosition, $wmOpacity);
						// }
						//imagejpeg($im, $path_preview.$fileName);
						
						$this->files[]=$path_small.$fileName;						
						// $this->imageResize($path_small, $fileName, 90, 90, 70);
						
						unlink($sFileName);
						
						//Получаем параметры загруженного (в папку _big) изображения (ширина-высота, тип и пр.)
						list($width, $height, $type, $attr) = getimagesize($path_big.$fileName);
		
						$this->paramsArr['id']=$row['#id'];
						
						$this->paramsArr['width']=$width;
						$this->paramsArr['height']=$height;
						$this->paramsArr['md5']=md5_file($path_big.$fileName);
						$this->paramsArr['modify']=filemtime($path_big.$fileName);
						
						if($this->procName=='_p_objectsphotostemp_photoadd') 
							$this->procName="_p_objectsphotostemp_size";
						else {
							$this->procName="_p_objectsphotos_size";
							$this->paramsArr['prefix']=$row['#prefix'];
						}
						
						$tempdb=$this->db;
						$this->connect($this->config['db']);
						$this->getProcParams();		
						$error=null;
						$query = $this->queryProc();
						if ($result2 = $this->db->query($query))
						{
							$row = $result2->fetch_assoc();
							if ($row&&array_key_exists('success',$row)&&$row['success']==0)
								$error=$row['msg'];
							$result2->close();
						}
						while($this->db->next_result()) $this->db->store_result();		
						$this->db->close();
						$this->db=$tempdb;
						if ($error!==null)
							$this->handleError($error);
						
					}
					if($this->procName=='_p_company_save')
					{
						$files = glob($this->config['dir_img'].'company_logo/*');
						foreach($files as $file) {
							if($file == $this->config['dir_img'].$row['filename']){
								copy($file,$this->config['dir_img'].'company_logo/current_logo.jpg' );
								//rename($file,$this->config['dir_img'].'company_logo/current_logo.jpg'); 
							}
						//	else unlink($file);
						}
						
						if($row['companyFileName'] == null || empty($row['companyFileName'])){
							$filePath = $this->config['dir_img'].'company_logo/current_logo.jpg';
							if(file_exists($filePath)){
								unlink($filePath);
							}
						}
						
						$files = glob($this->config['dir_img'].'company_logo/*');
						foreach($files as $file) {
							//if($file != $this->config['dir_img'].'company_logo/current_logo.jpg') unlink($file); 
						}
					}
					
					if($this->procName=='_p_settings_save'){
						$files = glob($this->config['dir_img'].'company_logo/*');
						$path = $this->config['dir_img'].'company_logo/';
						foreach($files as $file) {
							if($file == $this->config['dir_img'].'company_logo/'.$row['filename']){
								$fileName = $row['filename'];
							//	$rotate = $row['wmRotate'];
								
/*								switch($rotate){
									case 0:
										$degree = 0;
										break;
									case 1:
										$degree = -30;
										break;
									case 2:
										$degree = 30;
										break;
								}
									
								$f = str_replace('.png', '_rotate.png', $fileName);
								if($rotate>0) copy($path.$fileName, $path.$f);
								$this->onRotate($path, $path, $degree==0 ? $fileName : $f, $degree, 500, 100);
	*/							
								//copy($path.$fileName, $path.$f);
/*								if($rotate>0)
									copy($path.$f,$path.'watermark.png');
								else*/
								
								copy($path.$fileName,$path.'watermark.png');
								if(!$this->img_resize($path.'watermark.png', $path.'watermark_big.png', 500, 500, 100)) $this->handleError('Не удалось сохранить файл watermark_big.png');
								if(!$this->img_resize($path.'watermark.png', $path.'watermark_preview.png', 200, 200, 100)) $this->handleError('Не удалось сохранить файл watermark_preview.png');
							}
						}

						if($row['filename'] == null || empty($row['filename'])){
							$wm_arr = array('watermark.png', 'watermark_big.png', 'watermark_preview.png');
							for($i=0; $i<count($wm_arr); $i++){
								if(file_exists($path.$wm_arr[$i])){
									unlink($path.$wm_arr[$i]);
								}
							}
						}
					}
					
					if($this->procName=='_p_staff_save' )
					{					
						$this->connect($this->config['db']);

						$query = 
						"
						  CREATE EVENT `event".md5($this->paramsArr['sessionId'].rand(0, 999999))."`
						  ON SCHEDULE AT CURRENT_TIMESTAMP DO 
						  BEGIN
							call p_staff_refresh();
						  END
						";
						
						if (!$this->db->query($query)) $this->handleError('Не удалось выполнить команду',$query,true);
			
					}
		
					
					if($this->procName=='_p_staffphoto_photoadd' /*|| $this->procName=='_p_company_photoadd'*/)
					{										
						$path=$this->config['dir_img'].$row['#prefix']."/";							
										
						if(!file_exists($path))						
						if(!mkdir($path, 0770, true)) $this->handleError('Не удалось создать каталог для файлов',$path,true);
												
						$fileName=$row['#fileName'];	
						
						$sFileName=$this->tmpDir.$fileName;
						
						if(!file_put_contents($sFileName, utf8_decode($this->paramsArr['PhotoAdd']))) $this->handleError('Не удалось сохранить файл',$this->paramsArr['PhotoAdd'],true);
						
						if($this->procName=='_p_staffphoto_photoadd') 
						{
							if(!$this->img_resize($sFileName, $path.$fileName, 150, 200, 100)) $this->handleError('Не удалось сохранить файл','resize '.$path.$fileName,true);
							
						}
						else 
						{
							if(!$this->img_resize($sFileName, $path.$fileName, 300, 300, 100)) $this->handleError('Не удалось сохранить файл','resize '.$path.$fileName,true);
						}
						
						unlink($sFileName);
						
						$tmp['fileName'] = $fileName;	
											
						die($this->getJSON(array('success' => true, 'data' => $tmp)));
					}
					
					if($this->procName=='_p_objectsfilestemp_fileadd' || $this->procName=='_p_nbfiles_fileadd' || 
					   $this->procName=='_p_taskmessage_fileadd' || $this->procName=='_p_news_fileadd')
					{
						if($this->procName=='_p_taskmessage_fileadd')
							$path=$this->fileMainDir.$row['#prefix'].'_file/';
						else
							$path=$this->fileDir.$row['#prefix'].'_file/';
												
						// Проверяем, существуют ли папка хранилища, куда будут класться файлы		
						if(!file_exists($path))						
						if(!mkdir($path, 0775, true)) $this->handleError('Не удалось создать каталог для файлов',$path,true);
												
						$fileName=$path.$row['#fileName'];	
						
						if(!file_put_contents($fileName, utf8_decode($this->paramsArr['PhotoAdd']))) $this->handleError('Не удалось сохранить файл ',$this->paramsArr['PhotoAdd'],true);				
					}
					
					if($this->procName=='_p_objectsfilestemp_get')
					{
						$path=$this->fileDir.$row['#prefix'].'_file/';

						$file = file_get_contents($path.$row['#fileName']);
					
						$tmp['file']=utf8_encode($file);
						
						$tmp['fileName']=md5(rand(0, 999999)).'.'.$row['#ext'];
					}
					
					if($this->procName=='_p_problem_save')//Процедура отправки сообщения разработчикам
					{				
						include_once('problem.php');
						
						if(!problemSend($row, $this->paramsArr)) $this->handleError('Не удалось отправить сообщение.');					
					}

					if($this->procName=='_p_objectspReserve_save')//Процедура отправки сообщения о бронировании объекта сотрудникам, закреплённым за этим объектом
					{				
						include_once('reserve.php');
						
						if(!reserveSend($row, $this->paramsArr)) $this->handleError('Не удалось отправить сообщение.');
					}
					
					// if($this->procName == '_p_objectsphotostemp_rotate')
					// {
						// $tempFileName = $row['tempFileName']; //временное имя файла
						// $fileNameNew = $row['newFileName']; //новое имя файла
						// $fileName = $row['fileName']; //новое имя файла
						
						// $path=$this->imageDir.$this->paramsArr['prefix'];
					
						// $path_big = $path.'_big/'.$tempFileName;
						// $path_big_new = $path.'_big/'.$fileName;
						// if(!file_exists($path_big))$this->handleError('cannot find big file');
						// if(!file_exists($path_big_new)) $this->handleError('Не удалось найти файл '.basename($path_big_new));
						// unlink($path_big_new);
						// if(!rename($path_big,$path_big_new))$this->handleError('cannot rename big file');
						
						// $path_preview = $path.'_preview/'.$tempFileName;
						// $path_preview_new = $path.'_preview/'.$fileName;
						// if(!file_exists($path_preview))$this->handleError('cannot find preview file');
						// if(!file_exists($path_preview_new)) $this->handleError('Не удалось найти файл '.basename($path_preview_new));
						// unlink($path_preview_new);
						// if(!rename($path_preview,$path_preview_new))$this->handleError('cannot rename preview file');
						
						// $path_small = $path.'_small/'.$tempFileName;
						// $path_small_new = $path.'_small/'.$fileName;
						// if(!file_exists($path_small))$this->handleError('cannot find small file');
						// if(!file_exists($path_small_new)) $this->handleError('Не удалось найти файл '.basename($path_small_new));
						// unlink($path_small_new);
						// if(!rename($path_small,$path_small_new))$this->handleError('cannot rename small file');
					// }
					
					if($this->procName == '_p_objectsb_save' || $this->procName == '_p_objectsc_save' || $this->procName == '_p_objectsf_save' || $this->procName == '_p_objectsp_save' ||
					   $this->procName == '_p_objectsr_save' || $this->procName == '_p_objectss_save' || $this->procName == '_p_objectsz_save' || $this->procName == '_p_objectsnComplex_save'){
						if($row['photos'] != ''){
							$photos_arr = explode(',', $row['photos']);
							$rotate_arr = explode(',', $row['rotate']);
							$id_arr = explode(',', $row['idPhotos']);
							$path = $this->imageDir.$row['prefix'];
							require_once(dirname(__FILE__).'/images.php');
							$im=new rmbdImages();
							$im->setHandle($this);
							$params=array();
							$path_wm = $this->config['dir_img'].'company_logo/';
							$wm_big = 'watermark_big.png';
							if($row['prefix'] != 'b'&&file_exists($path_wm.$wm_big)){
								$params['watermark']=array(
									'opacity'=>$row['wmOpacity'],
									'position'=>$row['wmPosition'],
									'rotate'=>$row['wmRotate'],
									'file'=>$path_wm.$wm_big
								);
							}

							$path_orig = $this->checkPath($path, '_orig');
							$path_big = $this->checkPath($path, '_big');
							$path_preview = $this->checkPath($path, '_preview');
							$path_small = $this->checkPath($path, '_small');
							$processed=array();
							for($i=0; $i<count($photos_arr); $i++){
								$p=$params;
								$fileName = $photos_arr[$i];
								$rotate = $rotate_arr[$i]%4;

								$degree = (360-$rotate*90)%360;

																
								if(!file_exists($path_orig.$fileName))
									copy($path_big.$fileName, $path_orig.$fileName);

								$p['source']=$path_orig.$fileName;
								$p['dest']=array(
									array('file'=>$path_big.$fileName,'degree'=>$degree,'max'=>$this->photoSizes['big'],'water'=>array('size'=>500,'write'=>true)),
									array('file'=>$path_preview.$fileName,'degree'=>$degree,'max'=>$this->photoSizes['preview'],'water'=>array('size'=>200,'write'=>true)),
									array('file'=>$path_small.$fileName,'degree'=>$degree,'max'=>$this->photoSizes['small'],'water'=>array('size'=>200,'write'=>false))
								);
								if ($degree!=0)
									$p['dest'][]=array('file'=>$path_orig.str_replace('.jpg', '_rotate.jpg', $fileName),'degree'=>$degree,'max'=>$this->photoSizes['big'],'water'=>array('size'=>500,'write'=>false));
								
								$im->processImage($p);
								echo '/* '.var_export($p,true).'*/';
/*								
								
								$this->onRotate($path_orig, $path_big, $fileName, $degree, $this->photoSizes['big'], 100);
								$this->onRotate($path_orig, $path_preview, $fileName, $degree, $this->photoSizes['preview'], 100);
								$this->onRotate($path_orig, $path_small, $fileName, $degree, $this->photoSizes['small'], 70);
								
								if($row['prefix'] !== 'b'){
									$path_wm = $this->config['dir_img'].'company_logo/';
									$wm_big = 'watermark_big.png';
									$wm_preview = 'watermark_preview.png';
									$wmPosition = $row['wmPosition'];
									$wmOpacity = $row['wmOpacity'];
									
									if(file_exists($path_wm.$wm_big)) $this->ImageAddLogo($path_big.$fileName, $path_wm.$wm_big, $wmPosition, $wmOpacity);
									if(file_exists($path_wm.$wm_preview)) $this->ImageAddLogo($path_preview.$fileName, $path_wm.$wm_preview, $wmPosition, $wmOpacity);
								}
								
								$f = str_replace('.jpg', '_rotate.jpg', $fileName);
								copy($path_orig.$fileName, $path_orig.$f);
								$this->onRotate($path_orig, $path_orig, $f, $degree, $this->photoSizes['big'], 100);
*/

								list($width, $height, $type, $attr) = getimagesize($path_big.$fileName);
								$processed[]=array(
									'id'=>$id_arr[$i],
									'width'=>$width,
									'height'=>$height,
									'md5'=>md5_file($path_big.$fileName),
									'modify'=>filemtime($path_big.$fileName)
								);
							}
							
							$this->procName="_p_objectsphotos_size";
						
							$tempdb=$this->db;
							$this->connect($this->config['db']);
							foreach($processed as $p){
								
								$this->paramsArr['id']=$p['id'];
								$this->paramsArr['prefix']=$row['prefix'];
								$this->paramsArr['width']=$p['width'];
								$this->paramsArr['height']=$p['height'];
								$this->paramsArr['md5']=$p['md5'];
								$this->paramsArr['modify']=$p['modify'];
								
								$this->getProcParams();		
								$query = $this->queryProc();
								if ($result2 = $this->db->query($query))
								{
									$row = $result2->fetch_assoc();
									if ($row&&array_key_exists('success',$row)&&$row['success']==0)
										$this->handleError($row['msg']);
									$result2->close();
								}
								while($this->db->next_result()) $this->db->store_result();	
							}							
							$this->db->close();
							$this->db=$tempdb;

						}
					}

					foreach ($row as $key => $value) 
					{
						if (substr($key,0,1)!='#' && $this->is_utf8($value)) $tmp[$key]=$value;	
					}
					
					$row = $tmp;
					
					if(count($row)>0) echo $this->getJSON(array('success' => true, 'data'    => $row));
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
	// public function call_p_mediaformat_save_before()//Функция выводит карточки: объектов, сотрудников, фирмы
	// {
		// xvnxcvn
		// $data=$this->queryOpen(true, false, false);
	// }
	
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
	
	public function report()//Функция получения отчетов
	{	
		if ($this->procName=='_p_get_report')
		{
			$data=$this->queryOpen(false, false, false);
			
			$fileName=$this->reportDir.$data[0]['reportName'];
			
			if (file_exists($fileName)) die(file_get_contents($fileName));
		}
		else
		{
			$data=$this->queryOpen(false, false, false);
						
			if($data[0]['mbd']==1) die('{"success": true, "mbd":1}');
			
			$this->procName=$data[0]['procName'];
			
			$data_source_name=$data[0]['sourceName'];
			
			$this->paramsArr['isReport']=1;
			
			$this->getProcParams();
			
			$query = $this->queryProc(false);
			
			include_once('report.php');
		
			$xml = new rmbdReport();
			
			die($xml->get($this->db, $query,$data_source_name));
		}
	}
	public function form()//Функция получает модули
	{
		if($this->procName=="_p_objects_form")
		{
			$this->getProcParams();
			include_once("modules/objectEditCard.php");
			formLoad($this->paramsArr);
		}
	}
	public function app()//Функция получает модули
	{
		$data=$this->queryOpen(false, false, false);
		$error=true;
		if($data[0]['success'])
		{
			if (is_numeric($data[0]['success']))
			{
				if ($data[0]['success']==1)
				{
					$error=false;
					switch($this->appName)
					{
						case 'martb2c':
						case 'martb2r':
						case 'martb2f':
						case 'martb2p':
						case 'martb2s':
						case 'martb2z':
						case 'martb2n':
							$filename='modules/mbd.php';
							break;
						//case 'b2c':
						//case 'b2r':
						//case 'b2f':
						//case 'b2p':
						//case 'b2s':
						//case 'b2z':
						case 'b2b':
							$filename='modules/objects.php';
							break;	
						default:
							$filename="modules/$this->appName.php";
					}

					include_once($filename);
					/* минификатор запуск */
					if (!function_exists('minify')){
						global $serviceDir;
						$serviceDir=$this->service;
						function minify($text){
							global $serviceDir;
							if (file_exists($serviceDir.'/minify/JSMin.php')){
								require_once($serviceDir.'/minify/JSMin.php');
								return JSMin::minify($text);
							}else
								return $text;
						}
					}
					ob_start('minify');
					/* /минификатор запуск. конец */

					switch($this->appName)
					{			
						//case 'b2c':
						//case 'b2r':
						//case 'b2f':
						//case 'b2p':
						//case 'b2s':
						//case 'b2z':
						case 'b2b':
							$prefix=substr($this->appName, 2, 2);	
							$this->paramsSql="'".$this->paramsArr['sessionId']."','".$prefix."'" ;
							$this->procName='p_objects_from';
							
							$items=$this->queryOpen(false, false, false);
							include_once('items.php');
							$Items = new rmbdItems();
							//appLoad($prefix, $data[0]['fields'],$data[0]['columns'],$data[0]['filters'],$Items->getTree($items), explode(',',$data[0]['right']));
							$data[0]['prefix']= $prefix;
							$data[0]['form']= $Items->getTree($items);
							appLoad($data[0]);							
							break;	
						case 'martb2c':
						case 'martb2r':
						case 'martb2f':
						case 'martb2p':
						case 'martb2s':
						case 'martb2z':
						case 'martb2n':
							//$prefix= str_replace("martb2", "m", $this->appName);
							//appLoad($prefix, $data[0]['fields'],$data[0]['columns'],$data[0]['filters'], null);
							$data[0]['prefix']= str_replace("martb2", "m", $this->appName);
							appLoad($data[0]);
							break;
						default:
							appLoad($data[0]);
					}
					ob_end_flush(); # Выполняем минификацию
				}
			}
		}
		if ($error){
			if($data[0]['msg'])
				$this->handleError($data[0]['msg']);
			else
				$this->handleError("Произошла непредвиденная ошибка",'',true);
		}
	}
	public function storeTree()//Функция строит дерево
	{		
		include_once('tree.php');
		
		$data = $this->queryOpen(false, false, false);
		
		new rmbdTree($data);
	}	
	private function deleteInactive(&$rubr){
		foreach($rubr as $key=>&$d){					// Посмотрим что пришло
			if (array_key_exists('cls',$d)&&$d['cls'] == 'node-rubr'){				// Рубрика?
				$this->deleteInactive($d['children']);	// Провести зачистку детей
				if (!array_key_exists('active',$d))		// И не активна, т.к. даже аттрибута нет?
					unset($rubr[$key]);					// Стоит избавиться от рубрики.
			}
		}
	}	
	public function storeTree2()//Функция строит дерево для модуля - Рекламные контакты
	{
		$data = $this->queryOpen(true, false, false);
		
		$exporters=array();
		$rubr=array();
		
		foreach($data[0] as &$d) // Площадки
		{
			$d['iconCls'] = 'exporter';
			$d['cls'] = 'node-exporter';
			$d['leaf'] = true;	
			$exporters[$d['id']]=$d;
		}
		
		foreach($data[1] as &$d) // Рубрики
		{
			$d['cls'] = 'node-rubr';
			$d['leaf'] = true;
			$d['children']=array();
			$rubr[$d['id']]=$d; 
		}
		
		foreach($data[2] as &$b) // Блоки
		{
			$b['expandable'] = false;
			$b['leaf'] = true;
			$rubr[$b['rubr']]['children'][]=$b;
			$rubr[$b['rubr']]['leaf'] = false;
			$rubr[$b['rubr']]['active']	= true;
		}
		
		foreach($rubr as $key=>$d)
		{
			if ($d['parentId'])
			{
				$rubr[$d['parentId']]['children'][]=&$rubr[$key];
				$rubr[$d['parentId']]['leaf'] = false;	
				if(array_key_exists('active',$d)&&$d['active']) $rubr[$d['parentId']]['active'] = true;	
			}
		}
		foreach($rubr as $key=>&$d)
		{
			if (!$d['parentId'])
			{
				 $this->deleteInactive($d['children']);		// Сначала зачистим детей от пустых.
				 if(array_key_exists('children',$d)&&$d['children'])
				{
					$exporters[$d['exporter']]['children'][]=&$rubr[$key];
					$exporters[$d['exporter']]['leaf'] = false;	
				}
			}
		}
		
		$result=array();
		
		foreach($exporters as &$d){
			
			if(array_key_exists('children',$d)&&$d['children']&&array_key_exists('name',$d))
				$result[]=$d;
		}
		die($this->getJSON($result));
	}
	public function storeTree3()//Функция строит дерево для модуля - Цены на рекламу
	{
		$data = $this->queryOpen(true, false, false);
		// echo "/* ".htmlspecialchars(var_export($data,true)).' */ ';
		$exporters=array();
		$rubr=array();
		
		foreach($data[0] as &$d)
		{
			$d['iconCls'] = 'exporter';
			$d['cls'] = 'node-exporter';
			$d['leaf'] = true;	
			$exporters[$d['id']]=$d;
		}
		
		foreach($data[1] as &$d)
		{
			$d['cls'] = 'node-rubr';
			$d['leaf'] = true;	
			$rubr[$d['exporter'].$d['id']]=$d;
		}
		
		foreach($data[2] as &$b)
		{
			$b['expandable'] = false;
			$b['leaf'] = true;
			$rubr[$b['cCompany_siteId'].$b['rubr']]['children'][]=$b;
			$rubr[$b['cCompany_siteId'].$b['rubr']]['leaf'] = false;
		}
		
		foreach($rubr as $key=>$d)
		{
			if ($d['parentId'])
			{
				$rubr[$d['exporter'].$d['parentId']]['children'][]=&$rubr[$key];
				$rubr[$d['exporter'].$d['parentId']]['leaf'] = false;	
				if(array_key_exists('active',$d)&&$d['active']) $rubr[$d['exporter'].$d['parentId']]['active'] = true;	
			}
		}		
		//echo "/* ".htmlspecialchars(var_export(array($exporters,$rubr),true)).' */ ';
		
		foreach($rubr as $key=>&$d){
			if(array_key_exists('children',$d)&&$d['children']){
				//$this->deleteInactive($d['children']);		// Сначала зачистим детей от пустых.
				if (!$d['parentId']){
					$exporters[$d['exporter']]['children'][]=&$rubr[$key];
					$exporters[$d['exporter']]['leaf'] = false;	
				}
			}
		}
		
		$result=array();
		
		foreach($exporters as &$d){
			
			if(array_key_exists('children',$d)&&$d['children']&&array_key_exists('name',$d))
			$result[]=$d;
		}

		die($this->getJSON($result,phpversion()>='5.4'?JSON_UNESCAPED_UNICODE:0));		
	}
	
	private function getElements($a,$level=0){ // Возвращает преобразованный массив объектов для сторетрии4
		global $counterid;
		$r=array();
		// Выводим элементы отбрасывая ключи массива.
		foreach($a as $id=>&$v){
			// Если у нашего блока есть поля issue, то отробим его и блоки.
			if (array_key_exists('issues',$v))
				unset($v['issues']);
			if (array_key_exists('blocks',$v))
				unset($v['blocks']);
			if (array_key_exists('rid',$v)||array_key_exists('bid',$v)||array_key_exists('eid',$v)||array_key_exists('iid',$v))
				$v['id']='u'.($counterid++);
			// Если количество объектов в блоке больше нуля
			if (array_key_exists('count',$v)&&$v['count']>0){
				if ($level==2||($level>2&&count($v['children'])==1))
					$v['expanded']=true;
				if ($level>1)
					usort($v['children'],function($a,$b){ return $a['name']>$b['name'];});
				$v['children']=$this->getElements($v['children'],$level+1);
				$v['name'].=', объектов: '.$v['count'];
				$r[]=$v;
			}else{
				if (!array_key_exists('children',$v))
					$r[]=$v;
			}
		}
		return $r;
	}
	
	public function storeTree4() //Функция строит дерево для модуля - Сформированная реклама
	{
		$data = $this->queryOpen(true, false, false);
		$result=array();

		
		$exporters=array();
		$issues=array();
		$rubr=array();
		$blockTypes=array();
		
		if (count($data[4])<$data[5][0]['total'])
			$result[-1]=array('name'=>'<span style="color:red;font-weight:bolder;">Отображается только последние '.count($data[4]).' объектов из '.$data[5][0]['total'].'. Уточните фильтры</span>','iconCls'=>'icon-error','order'=>-100,'leaf'=>true);			
			else if($data[0][0]['order'] == -100 && $data[0][0]['status'] == 4){ //если фильтры не заданы и статус 'Выгружена'
				$result[-1]=array('name'=>$data[0][0]['name'],'iconCls'=>$data[0][0]['iconCls'],'order'=>-100,'leaf'=>true);
				$result=$this->getElements($result);

				die($this->getJSON($result,phpversion()>='5.4'?JSON_UNESCAPED_UNICODE:0));
			}
			else if($data[5][0]['total'] == 0){
				$result[-1]=array('name'=>'<span style="color:red;font-weight:bolder;">Объектов не найдено','iconCls'=>'icon-error','order'=>-100,'leaf'=>true);
				$result=$this->getElements($result);

				die($this->getJSON($result,phpversion()>='5.4'?JSON_UNESCAPED_UNICODE:0));
			}
				
		
		foreach($data[0] as &$d) // Площадки
		{
			$d['iconCls'] = 'noicon';
			$d['cls'] = 'node-exporter';
			$d['leaf'] = false;
			$exporters[$d['id']]=$d;
			$d['count']=0;
			$d['children']=array();
			$d['issues']=array();
			$result[$d['eid']]=$d;
		}
		
		foreach($data[1] as &$d) // Выпуски
		{
			$d['leaf'] = false;
			$issue[$d['iid']]=$d;
		}
		
		foreach($data[2] as &$d) // Рубрики
		{
			$d['leaf'] = false;
			$rubr[$d['rid']]=$d;
		}
/*		foreach($rubr as &$d){ // Дерево рубрик
			$rubr[$d['parent']]['children'][$d['rid']]=&d;
		}
	*/	
		foreach($data[3] as &$d) // Типы блоков
		{
			$d['leaf'] = false;
			$blocks[$d['bid']]=$d; 
		}
/*		Отключаем код Никиты
		$i = 0;
		foreach($data[4] as &$b) // Объекты
		{
			$b['expandable'] = false;
			$b['leaf'] = true;
		
			$exporters[$b['exporter']]['children'][] = $issues[$b['issue']];
			$issues[$b['issue']]['children'][] = $rubr[$b['rubr']];
			$rubr[$b['rubr']]['children'][] = $blockTypes[$b['blocktype']];
			$blockTypes[$b['blocktype']]['children'][] = $b;
			
			$exporters[$b['exporter']]['leaf']=false;
			$issues[$b['issue']]['leaf']=false;
			$rubr[$b['rubr']]['leaf']=false;
			$blockTypes[$b['blocktype']]['leaf']=false;
			
			if($i == 50) break;
			$i++;
		}
		*/
		/* Включаем код Александра2 */
		// $i=0;
			foreach($data[4] as &$o){
			// if($i > $limit) break;
			// $i++;
			$o['expandable'] = false;
			$o['leaf'] = true;
			$cnt=array();
			$name='';
			if ($o['issue']){
				if (!array_key_exists($o['issue'],$issue))
					continue;
				if	(!array_key_exists($o['issue'],$result[$issue[$o['issue']]['exporter']]['issues'])){
					$root=&$issue[$o['issue']];
					$result[$issue[$o['issue']]['exporter']]['issues'][$o['issue']]=array('root'=>$root);
				}
				$is=&$result[$issue[$o['issue']]['exporter']]['issues'][$o['issue']];
				$name=$o['issue'];
			}else{
				$name=$o['from'].'-'.$o['to'];
				if (!array_key_exists($name,$result[$blocks[$o['blocktype']]['exporter']]['issues'])){
					$root=array('name'=>$name,'obj'=>array(),'status'=>-1,'count'=>0,'children'=>array());
					$root=&$root;
					$result[$blocks[$o['blocktype']]['exporter']]['issues'][$name]=array('root'=>$root);
				}
				$is=&$result[$blocks[$o['blocktype']]['exporter']]['issues'][$name];
			}
			$is['root']['count']++;
			$t=$o['issue']?'i'.$o['issue']:'b'.$o['blocktype'];

			if (!array_key_exists('b'.$o['blocktype'],$is)){
				$is['b'.$o['blocktype']]=$blocks[$o['blocktype']];
			}
			$is['b'.$o['blocktype']]['children'][]=$o;
			$is['b'.$o['blocktype']]['count']++;
			$b=&$is['b'.$o['blocktype']];
			if (!array_key_exists('r'.$b['rubr'],$is)){
				$is['r'.$b['rubr']]=$rubr[$b['rubr']];
//				$c=&$b['children'];
//				$is['r'.$b['rubr']]['children']=$c;
			}
			$is['r'.$b['rubr']]['count']++;
			$is['r'.$b['rubr']]['children'][$b['id']]=$b;
			$r=&$is['r'.$b['rubr']];
			while ($r['parent']>0){
				if (!array_key_exists('r'.$r['parent'],$is)){
					$is['r'.$r['parent']]=$rubr[$r['parent']];
					$is['r'.$r['parent']]['children']['r'.$r['id']]=&$r;
				}
				$is['r'.$r['parent']]['children']['r'.$r['id']]=$r;
				$is['r'.$r['parent']]['count']++;
				$r=&$is['r'.$r['parent']];
			}
			$is['root']['children']['r'.$r['id']]=$r;
//			if ($result[$r['exporter']]['count']==0)
			
			$result[$r['exporter']]['children'][$name]=$is['root'];
			$result[$r['exporter']]['count']++;
		}
		/*
		
		foreach($data[1] as &$d) // Выпуски
		{
			$d['leaf'] = true;
			$d['children']=array();
			$issues[$d['id']]=$d;
			if($exporters[$d['exporter']]) $exporters[$d['exporter']]['children'][]=$d;
		}
		
		foreach($data[2] as &$d) // Рубрики
		{
			$d['leaf'] = true;
			$d['children']=array();
			$rubr[$d['id']]=$d;
		}
		
		foreach($data[3] as &$d) // Типы блоков
		{
			$d['leaf'] = true;
			$d['children']=array();
			$blockTypes[$d['id']]=$d; 
			if($rubr[$d['rubr']]) $rubr[$d['rubr']]['children'][]=$d;
		}
		
		foreach($data[4] as &$b) // Объекты
		{
			$b['expandable'] = false;
			$b['leaf'] = true;
			$blockTypes[$b['blocktype']]['children'][]=$b;
			$blockTypes[$b['blocktype']]['leaf'] = false;
			$blockTypes[$b['blocktype']]['active']	= true;
		}
		
		foreach($rubr as $key=>$d) //Строим иерархию рубрик
		{
			if ($d['parent'])
			{
				$rubr[$d['parent']]['children'][]=&$rubr[$key];
				$rubr[$d['parent']]['leaf'] = false;
			}
		}
		
		foreach($rubr as $key=>&$d) //shit just doesn`t work
		{
			if(array_key_exists('children',$d)&&$d['children'])
			{
				if (!$d['parent'])
				{
					foreach($issues AS $iKey => $iValue)
					{
						if($iValue['exporter'] == $d['exporter'])
							$issuies[$iKey]['children'][] = &$rubr[$key];
							$issuies[$iKey]['leaf'] = false;
					}
				}
			}
		}
		
		*/
		/*
		$result=array();
		
		foreach($exporters as &$d)
		{
			if(array_key_exists('children',$d)&&$d['children'])
				$result[]=$d;
		}*/

        if(isset($a['order'])&&isset($b['order']))
			usort($result,function($a,$b){
	                if(isset($a['order'])&&isset($b['order'])) {
	                    return $a['order'] > $b['order'];
	                }
			});
		$result=$this->getElements($result);
//		var_dump($result);
		// echo $this->getJSON($result);	
		die($this->getJSON($result,phpversion()>='5.4'?JSON_UNESCAPED_UNICODE:0));
	}
	
	/***********************************************************************************
	Функция img_resize(): генерация thumbnails
	Параметры:
	  $src             - имя исходного файла
	  $dest            - имя генерируемого файла (тип файла будет JPEG)
	  $width, $height  - максимальные ширина и высота генерируемого изображения, в пикселях (по ссылке!)
	Необязательные параметры:
	  $quality         - качество генерируемого JPEG, по умолчанию - максимальное (100)
	***********************************************************************************/
	protected function img_resize($src, $dest, $width, $height, $quality=100) 
	{
	  if(!file_exists($src)) $this->handleError('Ошибка конвертации - исходный файля не найден'); 
	  $size=getimagesize($src);
	  if($size===false) $this->handleError('Ошибка конвертации - не удалось получить параметры файла');

	  // Определяем исходный формат по MIME-информации и выбираем соответствующую imagecreatefrom-функцию.
	  $format=strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
	  $icfunc="imagecreatefrom".$format;
	  if(!function_exists($icfunc)) $this->handleError('Ошибка конвертации - не существует подходящей функции преобразования');

	  // Определяем необходимость преобразования размера
	  if( $width<$size[0] || $height<$size[1] )
		$ratio = min($width/$size[0],$height/$size[1]);
	  else
		$ratio=1;
		

	  $width=floor($size[0]*$ratio);
	  $height=floor($size[1]*$ratio);
	  $isrc=$icfunc($src);
	  $idest=imagecreatetruecolor($width,$height);
	  if($format=='png'){
		imagesavealpha($idest, true);
		$trans_colour = imagecolorallocatealpha($idest, 0, 0, 0, 127);
		imagefill($idest, 0, 0, $trans_colour);
	  }
	  imagecopyresampled($idest,$isrc,0,0,0,0,$width,$height,$size[0],$size[1]);
	  
	  imagedestroy($isrc);
	  if($dest === null){
		return $idest;
	  }
	  
	  $createimgfunc = "image".$format;
	  if(!function_exists($createimgfunc)) $this->handleError('Ошибка конвертации - не существует подходящей функции преобразования1');
  	  if($format=='png'){
		$createimgfunc($idest,$dest,0);
	  }else{
		$createimgfunc($idest,$dest,$quality);
	  }
	  //imagejpeg($idest,$dest,$quality);
	  chmod($dest,0666);	  
	  imagedestroy($idest);
	  
	  return true; // успешно
	}
	public function rotatePhoto()//Поворот фотографий
	{
		$fileName = $this->paramsArr['fileName']; //собственно имя файла
		$fileNameNew = $this->paramsArr['sessionId'].'_'.$this->paramsArr['id'].'_tempfile.jpg'; //новое имя файла
		$degree  = $this->paramsArr['degree']; // Угол поворота изображения
		$rotate = $this->paramsArr['rotate'];
		
		if($degree==270)
			$rotate++;
		else
			$rotate--;
			
		if(($rotate%4)>0)
			$degree = ($rotate%4)*270;
		else if(($rotate%4)<0)
			$degree = ($rotate%4)*90;
		else
			$degree = 0;
		
		if($degree != 0){
		
			$path=$this->imageDir.$this->paramsArr['prefix'];

			$path_big = $path.'_big/'.$fileName;
			$path_big_new = $path.'_big/'.$fileNameNew;
			if(!file_exists($path_big))$this->handleError('cannot find big file');
			if(!copy($path_big,$path_big_new))$this->handleError('cannot copy big file');
			
			$path_preview = $path.'_preview/'.$fileName;
			$path_preview_new = $path.'_preview/'.$fileNameNew;
			if(!file_exists($path_preview))$this->handleError('cannot find preview file');
			if(!copy($path_preview,$path_preview_new))$this->handleError('cannot copy preview file');
			
			$path_small = $path.'_small/'.$fileName;
			$path_small_new = $path.'_small/'.$fileNameNew;
			if(!file_exists($path_small))$this->handleError('cannot find small file');	
			if(!copy($path_small,$path_small_new))$this->handleError('cannot copy small file');
		
			$this->img_rotate($path_big_new, $degree, 100);
			$this->img_rotate($path_preview_new, $degree, 100);
			$this->img_rotate($path_small_new, $degree, 70);
						
			//Получаем параметры загруженного (в папку _big) изображения (ширина-высота, тип и пр.)
			list($width, $height, $type, $attr) = getimagesize($path_big_new);
			
			$this->paramsArr['newFile']=$fileNameNew;
			$this->paramsArr['width']=$width;
			$this->paramsArr['height']=$height;
			$this->paramsArr['md5']=md5_file($path_big_new);
			$this->paramsArr['rotate'] = $rotate;
		
		}
	
		/*
	
	    $fileName = $this->paramsArr['fileName']; //собственно имя файла
		
		$degree  = $this->paramsArr['degree']; // Угол поворота изображения
		
		
		$path=$this->config['dir_img'].$this->paramsArr['prefix'];
	
		$path_big = $path.'_big/'.$fileName;
		if(!file_exists($path_big))$this->handleError('Не удалось найти большой файл');
		
		$path_preview = $path.'_preview/'.$fileName;
		if(!file_exists($path_preview))$this->handleError('Не удалось найти просмотровый файл');
		
		$path_small = $path.'_small/'.$fileName;
		if(!file_exists($path_small))$this->handleError('Не удалось найти маленький файл');	
	
		$this->img_rotate($path_big, $degree, 100);
		$this->img_rotate($path_preview, $degree, 100);
		$this->img_rotate($path_small, $degree, 70);
		
		
		//Получаем параметры загруженного (в папку _big) изображения (ширина-высота, тип и пр.)
		list($width, $height, $type, $attr) = getimagesize($path_big);
		
		$this->paramsArr['width']=$width;
		$this->paramsArr['height']=$height;
		$this->paramsArr['md5']=md5_file($path_big);
		
		*/
	}
	private function img_rotate($fileName, $degree, $quality=100, $im=null)//Поворот фотографии
	{
		if(!file_exists($fileName)) $this->handleError('Ошибка конвертации - исходный файля не найден'); 
		$size=getimagesize($fileName);
		if($size===false) $this->handleError('Ошибка конвертации - не удалось получить параметры файла');
		$format=strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
		$icfunc="imagecreatefrom".$format;
	  
		if(!function_exists($icfunc)) $this->handleError('Ошибка конвертации - не существует подходящей функции преобразования');
		if(!file_exists($fileName)) $this->handleError('Ошибка - исходный файл не найден'); 
		if ($im===null){
			$im=$icfunc($fileName);
			//$im=imagecreatefromjpeg($fileName);
		}
		if($im)
		{
			if($format=='png'){
				//imagesavealpha($idest, true);
				$trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127);
				//imagefill($idest, 0, 0, $trans_colour);
			}
			$im1 = imagerotate($im, $degree, $format=='png'?$trans_colour:0, 1);
			imagealphablending( $im1, false );
			imagesavealpha( $im1, true );

			imagedestroy($im);
			
			if($im1)
			{
				$createimgfunc = "image".$format;
				if(!function_exists($createimgfunc)) $this->handleError('Ошибка конвертации - не существует подходящей функции преобразования');
				$createimgfunc($im1,$fileName, $format=='png' ? 0 : $quality);
				
				//imagejpeg($im1,$fileName, $quality);
				imagedestroy($im1);
			}
			else $this->handleError('Ошибка - при конвертации'); 
		}
		else $this->handleError('Ошибка - при конвертации'); 
	  
		return true; // успешно
	}
	public function printPasscard() //Функция для печати пропуска сотрудника
	{
		$data = $this->queryOpen(false, false, false);
		$data[0]['filePath'] = $this->imageDir;
		$data[0]['tmpPath'] = $this->tmpDir;
		$data[0]['fileName'] = str_replace(array('{','}'),'',$this->paramsArr['sessionId']).'_'.time().'.pdf';
		$data[0]['becauseWhyNot'] = 'Действителен до:';
		$row=$data[0];
		
		include_once('staffpasscard.php');
		
		die($this->getJSON(array('success'=>1,'fileName'=>$row['fileName'])));
	}

	public function storeTree5()//Функция строит дерево для модуля - Цены на рекламу
	{
		$data = $this->queryOpen(true, false, false);
		

		$exporters=array();
		$rubr=array();
		
		foreach($data[0] as &$d)
		{
			$d['iconCls'] = 'exporter';
			$d['cls'] = 'node-exporter';
			$d['leaf'] = true;	
			$exporters[$d['id']]=$d;
		}
		
		foreach($data[1] as &$d)
		{
			$d['cls'] = 'node-rubr';
			$d['leaf'] = true;	
			$d['expanded'] = true;
			$rubr[$d['id']]=$d;
			$rubr['active']=false;
		}
		
		foreach($rubr as $key=>$d)
		{
			$rubr[$d['parentId']]['children'][]=&$rubr[$key];
			$rubr[$d['parentId']]['leaf'] = false;	
		}
		
		
		foreach($data[2] as &$b)
		{
			$b['expandable'] = false;
			$b['leaf'] = true;
			$rubr[$b['parentId']]['children'][]=$b;
			$rubr[$b['parentId']]['leaf'] = false;
			$p=$b['parentId'];
			while (array_key_exists($p,$rubr)){
				$rubr[$p]['active']=true;
				if (array_key_exists('parentId',$rubr[$p])&&$rubr[$p]['parentId']!=$p)
					$p=$rubr[$p]['parentId'];
				else break;
			}
		}
	
		$result=array();
		
		foreach($rubr as $key=>$b)
		{
			if ($b['active']==false)
				continue;
			if (array_key_exists($key,$exporters)){
				$d=&$exporters[$key];
				if ($d['id']==$key) //$b['parentId']
				{	
					foreach($b['children'] as $r)
						$d['children'][]=$r;
					$d['leaf'] = false;	
					// break;
				}
				$result[]=&$d;
			}
		}
			
		die($this->getJSON($result,phpversion()>='5.4'?JSON_UNESCAPED_UNICODE:0));		
	}	

		
	public function storeTree6()//Функция строит дерево для модуля - Цены на рекламу
	{
		$data = $this->queryOpen(true, false, false);
		
		
		$reports=array();
		
		foreach($data[0] as &$d)
		{
			$d['iconCls'] = 'icon-so_obj_card';
			$d['cls'] = 'node-report';
			$d['leaf'] = false;	
			//$reports[$d['id']]=$d;
			
			$children = array();
			$i=0;
			foreach($data[1] as $c)
			{
				if($d['id']==$c['parentId']) 
				{
					if($i==0) $c['name'] = 'сортировка по агенту';
					else  $c['name'] = 'сортировка по подразделению';
					$c['leaf'] = true;
					$c['iconCls'] = 'icon-so_report';					
					$children[]=$c;
					$i++;
				}
				
			}	
			
			$d['children']=$children;
			
			$reports[]=$d;
		}
		
		foreach($data[1] as &$d)
		{
			//$reports[$d['parentId']]['children'][]=$d;
		}
		/*
		foreach($rubr as $key=>$d)
		{
			$rubr[$d['parentId']]['children'][]=&$rubr[$key];
			$rubr[$d['parentId']]['leaf'] = false;	
		}
		
		
		foreach($data[2] as &$b)
		{
			$b['expandable'] = false;
			$b['leaf'] = true;
			$rubr[$b['parentId']]['children'][]=$b;
			$rubr[$b['parentId']]['leaf'] = false;
			$p=$b['parentId'];
			while (array_key_exists($p,$rubr)){
				$rubr[$p]['active']=true;
				if (array_key_exists('parentId',$rubr[$p])&&$rubr[$p]['parentId']!=$p)
					$p=$rubr[$p]['parentId'];
				else break;
			}
		}
	
		$result=array();
		
		foreach($rubr as $key=>$b)
		{
			if ($b['active']==false)
				continue;
			if (array_key_exists($key,$exporters)){
				$d=&$exporters[$key];
				if ($d['id']==$key) //$b['parentId']
				{	
					foreach($b['children'] as $r)
						$d['children'][]=$r;
					$d['leaf'] = false;	
					// break;
				}
				$result[]=&$d;
			}
		}
			
		*/
		die($this->getJSON($reports));		
	}
	
	public function storeTree7()//Функция строит дерево для компонента поиска по застройщику/жк
	{
		$data = $this->queryOpen(true, false, false);
		
		$dev=array();
		
		foreach($data[0] as &$d)
		{
			$d['leaf'] = true;	
			$dev[$d['id']]=$d;
		}
		
		foreach($data[1] as &$b)
		{
			$b['expandable'] = false;
			$b['leaf'] = true;
			if(array_key_exists($b['parentId'],$dev)) {
				$dev[$b['parentId']]['children'][]=$b;
				$dev[$b['parentId']]['leaf'] = false;
			}
		}
		
		$result=array();
		
		foreach($dev as &$d){
			
			if(!$d['leaf']) $result[]=$d;
		}

		die($this->getJSON($result));		
	}
	public function storeTree10()//Функция строит дерево для компонента поиска по справочнику
	{
		$data = $this->queryOpen(true, false, false);
		
		$dev=array();
		
		foreach($data[0] as &$d)
		{
			$d['leaf'] = true;	
			$dev[$d['id']]=$d;
		}
		
		foreach($data[0] as &$b)
		{
			$b['expandable'] = false;
			$b['leaf'] = true;
			if(array_key_exists($b['parentId'],$dev)) {
				$dev[$b['parentId']]['children'][]=$b;
				$dev[$b['parentId']]['leaf'] = false;
			}
		}
		
		$result=array();
		
		foreach($dev as &$d){
			
			if(!$d['leaf']) $result[]=$d;
		}

		
		die($this->getJSON($result));		
	}
	
	public function storeTree11()//Функция строит дерево (рубрики по заданной площадке)  для модуля - Выгрузка файлов
								 //Функция строит дерево по застройщикам/ЖК/Дом/Корпус (для модуля Новостройки "мои объекты")
	{
		$data = $this->queryOpen(true, false, false);
		$rubr=array();
		
		foreach($data[0] as &$d){
			$d['leaf'] = true;
			$rubr[$d['id']]=$d;
		}
		
		foreach($rubr as $key=>$d){			
			
			if ($d['parentId']){
				$rubr[$d['parentId']]['children'][]=&$rubr[$key];
				$rubr[$d['parentId']]['leaf'] = false;				
			}			
		}
		
		$result=array();
		
		foreach($rubr as $d){
			if (!$d['parentId'])
			//if(array_key_exists('children',$d)&&$d['children'])
				$result[]=$d;
		}

		die($this->getJSON($result,phpversion()>='5.4'?JSON_UNESCAPED_UNICODE:0));	
	}
	
	public function queue() // Функция запускает выполнение очереди подачи рекламы
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
					$cmd='/usr/bin/php '.$_SERVER['DOCUMENT_ROOT'].'/adverts/fill.php '.$row['id'].' '.$_SERVER['ORG_NAME'].
						(array_key_exists('requireUpdate',$this->paramsArr)?' 1,'.$this->paramsArr['requireUpdate']:'').
						' 2>&1 >'.$this->logDir.'/fill/'.$_SERVER['ORG_NAME'].'-'.$row['id'].'.log & echo !$';
					$row['cmd']=$cmd;
					$descriptorspec = array(
						0 => array("pipe", "r"),  // stdin - канал, из которого дочерний процесс будет читать
						1 => array("pipe", "w"),  // stdout - канал, в который дочерний процесс будет записывать 
						2 => array("pipe", "a") // stderr - файл для записи
					);
					$process=proc_open(/*'nice -n 19 '.*/$cmd,$descriptorspec,$pipes);
					if (is_resource($process)){
						$info=proc_get_status($process);
						$row['pid']=$info['pid'];
					}else{
						$this->handleError('Не удаётся запустить заполнение полей. ');
					}
					
				}
				else
				{
					if($row['msg']) $this->handleError($row['msg']);
					else $this->handleError('Ошибка при получении данных');
				}
				foreach ($row as $key => $value) 
				{
					if (substr($key,0,1)=='#' || !$this->is_utf8($value)) unset($row[$key]);	
				}
				
				// $row = $tmp;

				if(count($row)>0) echo $this->getJSON(array('success' => true, 'data'    => $row));
				else echo $this->getJSON(array('success' => true));

			}
			else $this->handleError('Неверное значение результата');
	
			$result->close();			
		}
		else  
		{
			$this->handleError('Не удалось выполнить команду');
			
			$this->logWrite("Не удалось выполнить команду - Errormessage: %s\n".$this->db->error);
		}
			
		while($this->db->next_result()) $this->db->store_result();		
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
	
	public function imageResize($path, $fileName, $width, $height, $quality=100,$image=false){
		$image= $this->img_resize($this->tmpDir.$fileName, ($image ? null : $path.$fileName), $width, $height, $quality);
		if(!$image)	
			$this->handleError('Не удалось сохранить файл '.$fileName);
		return $image;
	}
	
	public function onRotate($path_orig, $path, $fileName, $degree, $size, $quality){
		if(!file_exists($path_orig.$fileName))$this->handleError('cannot find orig file');
		
		copy($path_orig.$fileName, $this->tmpDir.$fileName);
		
		
		if($degree != 0){
			$resized = $this->imageResize($path, $fileName, $size, $size, $quality, true);
			$this->img_rotate($path.$fileName, $degree, 100, $resized);
		}
		else
			$this->imageResize($path, $fileName, $size, $size, $quality);
			
		unlink($this->tmpDir.$fileName);
	}
	
	public function ImageAddLogo($imagePath, $wm, $position = 0, $opacity = 70){
        $im = imagecreatefromjpeg($imagePath);
		
		if (is_string($wm)){
            $logoimage=imagecreatefrompng($wm);
        }else
			$logoimage=$wm;
			
		imagecolorallocatealpha($logoimage, 0, 0, 0, 127);
		imagealphablending($im, 1);
		imagealphablending($logoimage, 1);
		imagesavealpha($im,1);
		imagesavealpha( $logoimage, true );
		
        $marge_right = 10;
        $marge_bottom = 10;
        $dx=$sx = imagesx($logoimage);
        $dy=$sy = imagesy($logoimage);
		
		switch($position){
			case 0: //center
				$wmX = round(imagesx($im)/2 - $dx/2);
				$wmY = round(imagesy($im)/2 - $dy/2);
				break;
			case 1: //left top
				$wmX = $marge_right;
				$wmY = $marge_bottom;
				break;
			case 2: //right top
				$wmX = round(imagesx($im) - $dx)-$marge_right;
				$wmY = $marge_bottom;
				break;
			case 3: //left bottom
				$wmX = $marge_right;
				$wmY = round(imagesy($im) - $dy)-$marge_bottom;
				break;
			case 4: //right bottom
				$wmX = round(imagesx($im) - $dx)-$marge_right;
				$wmY = round(imagesy($im) - $dy)-$marge_bottom;
				break;
		}
				
	// Copy the stamp image onto our photo using the margin offsets and the photo
	// width to calculate positioning of the stamp.
		//imagecopyresampled($im, $logoimage, round(imagesx($im) - $dx - $marge_right), round(imagesy($im) - $dy - $marge_bottom), 0,0,round($dx),round($dy), $sx,$sy);
		$this->imagecopymerge_alpha($im, $logoimage, $wmX, $wmY, 0, 0, $dx, $dy, $opacity);
		imagejpeg($im, $imagePath);
		imagedestroy($im);
		imagedestroy($logoimage);
	//      imagecopy($im, $logoimage, , , 0, 0, $sx, $sy);
	}
	
	public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        // creating a cut resource 
        $cut = imagecreatetruecolor($src_w, $src_h); 

        // copying relevant section from background to the cut resource 
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h); 
        
        // copying relevant section from watermark to the cut resource 
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h); 
        
        // insert cut resource to destination image 
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct); 
    }
	
	private function sendEvent($data){
		$content = http_build_query($data);

		$params = array('http' => array(
			'method' => 'POST',
			'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
					 . "Content-Length: " . strlen($content) . "\r\n",
			'content' => $content,
			'timeout' => 600
		));
		
		$ctx = stream_context_create();

		stream_context_set_option($ctx, $params);
		try
		{
			$fp = fopen('http://server4.pro.bkn.ru/sendEvent', "rb", false, $ctx);
		} 
		catch (Exception $e) 
		{
			// echo '/* Не удалось получить данные. '.$e->getMessage() .'*/';
			// $this->log_write('Не удалось получить данные. '.$e->getMessage());
			// return false;
		}

		if (!$fp) 
		{
			// echo '/* Не удалось подключиться.*/';
			// $this->log_write('Не удалось получить данные.');
			// return false;
		}

		$response = stream_get_contents($fp);		
		
		if ($response === false) 
		{
			// echo '/* Не удалось получить ответ.*/';
			// $this->log_write('Не удалось получить данные.');
			// return false;
		}
		
		// return $response;	
	}
}	
?>