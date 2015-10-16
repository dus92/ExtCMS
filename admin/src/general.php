<?php
//Содержит api-функции для модулей раздела "Основное управление" + для главной страницы админ-панели 
class General
{
   /////////////////////////////////
   //главная страница админ-панели//
   /////////////////////////////////
   
   //получение общей информации о сервере, правах пользователя 
   public function getInfo()
   {
      global $userRights, $root, $system;
      $r = array();
      $ret_arr = array();
      
      if(!empty($userRights) || $root){ // проверка, есть ли права админа
          
          $r['title'] = __('Welcome to administration panel');
          
          if(!$root) {
            $i = 0;
            $r['rights'] = array();
            foreach ($userRights as $right => $right_desc) {                
                $rr = array();
                
                $rr['moduleName'] = $right;
                $rr['moduleDesc'] = $right_desc;
                $r['rights'][$i] = $rr;
                $i++;
            }
            
          } else {
    	       $r['rights'] = __('You have all rights on this site');
          }
          
          $r['text'] = array(); //массив, содержащий все текстовые названия
          $r['httpd'] = htmlspecialchars($_SERVER['SERVER_SOFTWARE']);
          $r['php'] = phpversion();
          $r['cms'] = RCMS_VERSION_A . '.'  . RCMS_VERSION_B. '.'  . RCMS_VERSION_C; 
          
          if($system->checkForRight('IBLOCKS-EDITOR')){
    	    $count = 0;
               
            $r['moderationCount'] = $count;
            $r['text']['moderation'] = __('article(s) awaits moderation'); 
          }
          
          if($system->checkForRight('SUPPORT')) {
            $count = sizeof(guestbook_get_msgs(null, true, false, DF_PATH . 'support.dat'));        
            
            $r['feedbackCount'] = $count;
            $r['text']['feedback'] = __('feedback requests in database');
          }
          $r['text']['rights'] = __('Rigts for current user');
          $r['text']['information'] = __('Information');
          $r['text']['leaveMsg'] = __('Here you can leave message for other administrators');
          $r['text']['send'] = __('Send');
          
          $r['remarks'] = file_get_contents(DATA_PATH . 'admin_remarks.txt');
          
          $ret_arr[0] = $r;
          return $ret_arr;
      }
      else{
        Response::_ERROR(__('You are not administrator of this site'));
      }
    
    }
    //отправка сообщения другим администраторам
    public function sendRemarks($remarks){
    	if(isset($remarks)){
     		file_write_contents(DATA_PATH . 'admin_remarks.txt', $remarks);
     		return true;
     	}
     	else{
     		Response::_ERROR(__('Отправлено пустое сообщение'));	
     	}
    }
    
    ///////////////////////////////////////////////////
   //обработка модулей раздела "Основное управление""//
   ///////////////////////////////////////////////////
   
   //Настройка сайта
   public function getSiteSettingsData(){
   		global $userRights, $root, $system;
      	$arr = array();
      	$ret_arr = array();
      	
      	if($system->checkForRight('GENERAL')){
	      	$system->config = parse_ini_file(CONFIG_PATH . 'config.ini');
			$config = &$system->config;
			
			$available_modules = array();
			$available_modules[''] = __('Latest news'); //''
			foreach ($system->modules['main'] as $module => $module_data){
				if($module !== 'index'){
					$available_modules[$module] = $module_data['title'];
				}
			}
			
			foreach ($config as $key => $val){
				$arr[$key] = $val;
			}
			//defult skin
			$dir = SKIN_PATH;
			$skins = rcms_scandir($dir);
			$skins_arr = array();
		    foreach ($skins as $skin){
		        if(is_dir($dir . $skin) && is_file($dir . $skin . '/skin_name.txt')){
		            $name = file_get_contents($dir . $skin . '/skin_name.txt');
		            $skins_arr[$skin] = $name;
		        }
		    }
			//default language
			$lang_arr = array();
			foreach ($system->data['languages'] as $lang_id => $lang_name){
		    	$lang_arr[$lang_id] = $lang_name;
		    }
			//timezone
			$tzs = DateTimeZone::listIdentifiers();
			$key = array_search($config['timezone'],$tzs);
			$arr['timezone'] = $key;
			$arr['tz'] = array();
			$arr['tz'] = $tzs;
			
			$arr['welcome_mesg'] = file_get_contents(DATA_PATH . 'intro.html');
			$arr['meta_tags'] = file_get_contents(DATA_PATH . 'meta_tags.html');
			
			$arr['available_modules'] = array();
			$arr['available_modules'] = $available_modules;
			
			$arr['skins'] = array();
			$arr['skins'] = $skins_arr;
			
			$arr['langs'] = array();
			$arr['langs'] = $lang_arr;
			  
			$arr['text'] = array();
			$arr['text']['generalParams'] = __('General params');
			$arr['text']['title'] = 		__('Your site\'s title');
			$arr['text']['short_title'] = 	__('Your site\'s short title');
			$arr['text']['hide_title'] = 	__('Do not show sitename in title');
			$arr['text']['site_url'] = 		__('Your site\'s URL') . '<br />' . __('Leave empty for autodetect');
			$arr['text']['copyright'] = 	__('Copyright for your content');
			$arr['text']['enable_rss'] = 	__('Enable RSS');
			$arr['text']['logging'] = 		__('Enable logging');
			$arr['text']['enable_ids'] = 	__('Enable IDS (logging must be enabled)');
			$arr['text']['num_of_latest'] = __('Number of element that will be considered as latest');
			$arr['text']['perpage'] = 		__('Number of elements per page');
			$arr['text']['adm_perpage'] = 	__('Number of items per page [admin panel]');
			$arr['text']['index_module'] = 	__('Module on index page');
			$arr['text']['wmh'] = 			__('Hide welcome message');
			$arr['text']['welcome_mesg'] = 	__('Text of Welcome message');
			$arr['text']['meta_tags'] = 	__('Additional meta tags for your site');
			
			$arr['text']['iboptions'] = 	__('iBlocks options');
			$arr['text']['imageSize'] = 	__('Thumbnails size');
			$arr['text']['enable_wms'] = 	__('Watermarks');
			$arr['text']['wm_alpha_level']= __('Alpha level (default 15)');
			$arr['text']['watermark'] = 	__('Watermark file');
			
			$arr['text']['interactionUser']=__('Interaction with user');
			$arr['text']['regconf'] = 		__('Disallow user selection of password in registration form');
			$arr['text']['pr_flood'] = 		__('Period when one password request can be acomplished (seconds)');
			$arr['text']['registered_accesslevel'] = __('Access level for registered users');
			$arr['text']['detect_lang'] = 	__('Try to detect user\'s language');
			$arr['text']['default_skin'] = 	__('Default skin');
			$arr['text']['allowchskin'] = 	__('Allow users to select skin');
			$arr['text']['default_lang'] = 	__('Default language');
			$arr['text']['allowchlang'] = 	__('Allow users to select language');
			$arr['text']['timezone'] = 	__('Default timezone');
			$arr['text']['send'] = 			__('Send');
			
			$ret_arr[] = $arr;
			return $ret_arr;
		}
		else{
			Response::_ERROR(__('У вас нет прав для работы с данным модулем'));
		}
   }
   
   public function saveSiteConfig($config){
   		if(!isset($config) || empty($config) || empty($config['nconfig']))
   			Response::_ERROR(__('Не удалось получить параметры для настройки сайта'));
   		
   		$nconfig = $config['nconfig'];
        
   		if(!empty($nconfig)){
   			//watermark
		   	if(!empty($nconfig['watermark'])){
				//$wm = preg_replace("/[^0-9a-zA-Z_\.]/",'',$nconfig['watermark']);
                $wm = $nconfig['watermark'];
				$nconfig['watermark'] = (file_exists($wm) ? $wm : '');
			}
			else{
				$nconfig['watermark'] = '';
			}
			
			$tzs = DateTimeZone::listIdentifiers();
			$nconfig['timezone'] = $tzs[(int)$nconfig['timezone']];
			write_ini_file($nconfig, CONFIG_PATH . 'config.ini');
		}
		if(isset($config['meta_tags'])) file_write_contents(DATA_PATH . 'meta_tags.html', $config['meta_tags']);
		if(isset($config['welcome_mesg'])) file_write_contents(DATA_PATH . 'intro.html', $config["welcome_mesg"]);
   }
   
   /////////////////////////////////////////////////
   ///////////////Работа с файлами//////////////////
   /////////////////////////////////////////////////
   
   //загрузка файлов
   public function filesUpload(){
    
    //TODO: разбить обработку файлов в зависимости от модуля (водяной знак, фотки, файлы ...  добавить параметр в ф-ю)
    
		$arr = array();
      	$ret_arr = array();
		$fileName = '';
		$mimeType = '';
		$fileSize = 0;
		
		/*
		 * If there is no file data, something wrong has happened. One possible reason is - the uploaded
		 * file size exceeds the maximum allowed POST or upload size.
		 */
		if (empty($_FILES)) {
		    Response::_ERROR(__('No file received'));
		}
		
		foreach ($_FILES as $fileName => $fileData) {
		    if ($fileData['error'] !== 0) {
		        Response::_ERROR(sprintf("Upload error '%d'", $fileData['error']));
		    }
		    
		    $fileName = htmlspecialchars($fileData['name']);
		    $mimeType = $fileData['type'];
		    $fileSize = $fileData['size'];
		    
		    $targetFile = IBLOCKS_PATH . $fileName;
		    		    
	        if (! move_uploaded_file($fileData['tmp_name'], $targetFile)) {
	            Response::_ERROR(__('Error saving uploaded file'));
	        }
		    
		    $arr['url'] = $targetFile;
		}
		return $arr;
		//Response::_ERROR(sprintf("[multipart] Uploaded %s, %s, %d byte(s)", $fileName, $mimeType, $fileSize));		
   }
   
   //удаление файлов
   public function fileDelete($path){
   		if (file_exists($path)){
   			unlink($path);	
   		}
   		else
   			Response::_ERROR(__('Error deleting file: file doesn\'t exist'));
   }
}


//if(!empty($_POST['nconfig']))
//{
//	if(!empty($_POST['nconfig']['watermark']))
//	{
//		$wm = IBLOCKS_PATH.preg_replace("/[^0-9a-zA-Z_\.]/",'',$_POST['nconfig']['watermark']);
//		$_POST['nconfig']['watermark'] = (file_exists($wm) ? $wm : '');
//	}
//	else
//	{
//		$_POST['nconfig']['watermark'] = '';
//	}
//	
//	$tzs = DateTimeZone::listIdentifiers();
//	$_POST['nconfig']['timezone'] = $tzs[(int)$_POST['nconfig']['timezone']];
//	write_ini_file($_POST['nconfig'], CONFIG_PATH . 'config.ini');
//}
//if(isset($_POST['meta_tags'])) file_write_contents(DATA_PATH . 'meta_tags.html', $_POST['meta_tags']);
//if(isset($_POST['welcome_mesg'])) file_write_contents(DATA_PATH . 'intro.html', $_POST["welcome_mesg"]);
//
//$system->config = parse_ini_file(CONFIG_PATH . 'config.ini');
//$config = &$system->config;
//
//$avaible_modules = array();
//$avaible_modules[''] = __('Latest news');
//foreach ($system->modules['main'] as $module => $module_data){
//	if($module !== 'index'){
//		$avaible_modules[$module] = $module_data['title'];
//	}
//}
//
//// Interface generation
//$frm =new InputForm ('', 'post', __('Submit'));
//$frm->addbreak(__('Site configuration'));
//$frm->addrow(__('Your site\'s title'), $frm->text_box("nconfig[title]", $config['title'], 40));
//$frm->addrow(__('Your site\'s short title'), $frm->text_box("nconfig[short_title]", @$config['short_title'], 40));
//$frm->addrow(__('Do not show sitename in title'), $frm->checkbox('nconfig[hide_title]', '1', '', @$config['hide_title']));
//$frm->addrow(__('Your site\'s URL') . '<br />' . __('Leave empty for autodetect'), $frm->text_box("nconfig[site_url]", $config['site_url'], 40));
//$frm->addrow(__('Copyright for your content'), $frm->text_box("nconfig[copyright]", @$config['copyright'], 60));
//$frm->addrow(__('Enable RSS'), $frm->checkbox('nconfig[enable_rss]', '1', '', @$config['enable_rss']));
//$frm->addrow(__('Enable logging'), $frm->checkbox('nconfig[logging]', '1', '', @$config['logging']));
//$frm->addrow(__('Enable IDS (logging must be enabled)'), $frm->checkbox('nconfig[enable_ids]', '1', '', @$config['enable_ids']));
//$frm->addrow(__('Number of element that will be considered as latest'), $frm->text_box('nconfig[num_of_latest]', @$config['num_of_latest']));
//$frm->addrow(__('Number of elements per page'), $frm->text_box('nconfig[perpage]', @$config['perpage']));
//$frm->addrow(__('Number of items per page [admin panel]'), $frm->text_box('nconfig[adm_perpage]', @$config['adm_perpage']));
//$frm->addrow(__('Module on index page'), $frm->select_tag('nconfig[index_module]', $avaible_modules, @$config['index_module']));
//$frm->addrow(__('Hide welcome message'), $frm->checkbox('nconfig[wmh]', '1', '', @$config['wmh']));
//$frm->addrow(__('Text of Welcome message'), $frm->textarea('welcome_mesg', file_get_contents(DATA_PATH . 'intro.html'), 80, 10), 'top');
//$frm->addrow(__('Additional meta tags for your site'), $frm->textarea('meta_tags', file_get_contents(DATA_PATH . 'meta_tags.html'), 80, 5), 'top');
//
//$asyncmgr = new AsyncMgr();
//$asyncmgr->printImgUpFormJS('watermark','image',false, true, array('load_stopped_3.gif','load_process_3.gif'),false);
//
//$frm->addbreak(__('iBlocks options'));
//$frm->addrow(__('Thumbnails size'), $frm->text_box('nconfig[th_width]', @$config['th_width']).' x '.$frm->text_box('nconfig[th_height]', @$config['th_height']));
//$frm->addrow(__('Watermarks'), $frm->checkbox('nconfig[enable_wms]', '1', '', @$config['enable_wms'], 'title="'.__('All images with suitable size will be watermarked').'"'));
//$frm->addrow(__('Alpha level (default 15)'), $frm->text_box('nconfig[wm_alpha_level]', @$config['wm_alpha_level']));
////$frm->addrow(__('Default position'), $frm->select_tag('nconfig[wm_position]', array(),@$config['wm_position']));
//if(file_exists(@$config['watermark']))
//{
//	$asyncmgr->addEditPart(__('Watermark file'),$frm,'image','<img src="{dvalue}">',$config['watermark'],basename($config['watermark']),'nconfig[watermark]','load_stopped_3.gif');
//}
//else
//{
//	$asyncmgr->addAddPart(__('Watermark file'),$frm,'image','nconfig[watermark]','load_stopped_3.gif');
//}
//$frm->addbreak(__('Interaction with user'));
//$frm->addrow(__('Disallow user selection of password in registration form'), $frm->checkbox('nconfig[regconf]', '1', '', @$config['regconf']));
//$frm->addrow(__('Period when one password request can be acomplished (seconds)'), $frm->text_box('nconfig[pr_flood]', @$config['pr_flood']));
//$frm->addrow(__('Access level for registered users'), $frm->text_box('nconfig[registered_accesslevel]', @$config['registered_accesslevel']));
//$frm->addrow(__('Try to detect user\'s language'), $frm->checkbox('nconfig[detect_lang]', '1', '', @$config['detect_lang']));
//$frm->addrow(__('Default skin'), user_skin_select(SKIN_PATH, 'nconfig[default_skin]', $config['default_skin']));
//$frm->addrow(__('Allow users to select skin'), $frm->checkbox('nconfig[allowchskin]', '1', '', @$config['allowchskin']));
//$frm->addrow(__('Default language'), user_lang_select('nconfig[default_lang]', $config['default_lang']));
//$frm->addrow(__('Allow users to select language'), $frm->checkbox('nconfig[allowchlang]', '1', '', @$config['allowchlang']));
////$frm->addrow(__('Default timezone'), user_tz_select((int)@$config['default_tz'], 'nconfig[default_tz]'));
//$tzs = DateTimeZone::listIdentifiers();
//$key = array_search($config['timezone'],$tzs);
//$frm->addrow(__('Default timezone'), $frm->select_tag('nconfig[timezone]', $tzs, $key));
//$frm->show();


?>