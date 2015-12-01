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
   
   
   ////////////////////////////////
   //модуль "Управление модулями"//
   ////////////////////////////////
   public function getManageModules(){
        global $system;
        $arr = array();
        $system->initialiseModules(true);
        if(!$disabled = @parse_ini_file(CONFIG_PATH . 'disable.ini')){
        	$disabled = array();
        }
        
        foreach ($system->modules as $type => $modules){
        	foreach ($modules as $module => $moduledata){
        		$moduledata['id'] = $module;
                $moduledata['checked'] = !empty($disabled[$module]);
                $arr[] = $moduledata;
                //$frm->addrow(__('Module') . ': ' . $moduledata['title'] . '<br><b>' . $moduledata['copyright'] . '</b>', $frm->checkbox('disable[' . $module . ']', '1', __('Disable'), !empty($disabled[$module])));
        	}
        }
        return $arr;
   }
   //включение/выключение модуля
   public function switchModuleState($moduleId, $disabled){
        if(!$disabled_arr = @parse_ini_file(CONFIG_PATH . 'disable.ini')){
            if($disabled == 1 && !write_ini_file(array($moduleId=>$disabled), CONFIG_PATH . 'disable.ini')){
                Response::_ERROR(__('Error occurred'));
            }
        }
        else{
            $isNew = true;
            foreach($disabled_arr as $key => $value){
                if($key == $moduleId && $disabled == 0){
                    unset($disabled_arr[$moduleId]);
                    $isNew = false;
                    break;
                }
            }
            if($isNew && $disabled == 1){
                $disabled_arr[$moduleId] = $disabled;
            }
            
            if(!write_ini_file($disabled_arr, CONFIG_PATH . 'disable.ini')){
                Response::_ERROR(__('Error occurred'));
            }
        }
   }
   /////////////////////////////
   //модуль "Панель навигации"//
   /////////////////////////////
   public function getNavigation(){
        $arr = array();
        $links = parse_ini_file(CONFIG_PATH . 'navigation.ini', true);
        
        $i = 0;
        foreach ($links as $link){
        	$tmp = explode(':', $link['url'], 2);
        	$checked = $tmp[0] == 'external';
        	if($checked){
        		$link['url'] = $tmp[1];
        	}
            $link['checked'] = $checked;
        	$i++;
            $arr[] = $link;
        }
        
//        foreach ($system->navmodifiers as $modifier => $options){
//        	$frm->addrow($modifier, call_user_func($system->navmodifiers[$modifier]['h']));
//        }
        
        return $arr;
        
   }
   public function saveNavigation($params){
        if(!empty($params['urls']) && !empty($params['names']) && is_array($params['urls']) && is_array($params['names'])){
        	if(sizeof($params['urls']) !== sizeof($params['names'])){
        		rcms_showAdminMessage(__('Error occurred'));
        	} else {
        		$result = array();
        		foreach ($params['urls'] as $i => $url) {
        			if(!empty($url)){
        				if(!empty($params['ext'][$i])) {
        					$ins['url'] = 'external:' . $url;
        				} else {
        					$ins['url'] = $url;
        				}
        				$ins['name'] = $params['names'][$i];
        				$result[] = $ins;
        			}
        		}
        		write_ini_file($result, CONFIG_PATH . 'navigation.ini', true) or rcms_showAdminMessage(__('Error occurred'));
        	}
        } elseif (!empty($params['addlink']) && !empty($params['addlink']['url'])) {
        	$links = parse_ini_file(CONFIG_PATH . 'navigation.ini', true);
        	$links[] = $params['addlink'];
        	write_ini_file($links, CONFIG_PATH . 'navigation.ini', true) or rcms_showAdminMessage(__('Error occurred'));
        }
   }
   ////////////////////////
   //модуль "Модули меню"//
   ////////////////////////
   //активные модули меню
   public function getCurrentMenus(){
        global $system, $skin;
        require_once(ADMIN_PATH . 'libs/ucm.php');
        
        $menus = parse_ini_file(CONFIG_PATH . 'menus.ini', true);
        include(SKIN_PATH . $system->skin . '/skin.php');
        $cur_arr = array();
        $i = 0;
        foreach ($menus as $column => $coldata){
            if(!empty($skin['menu_point'][$column])){
                $cur_arr[$i]['id'] = '/' . $column;
                $cur_arr[$i]['name'] = __('Column') . ': ' . $skin['menu_point'][$column];
                $cur_arr[$i]['expanded'] = false;
                $cur_arr[$i]['leaf'] = false;
                $cur_arr[$i]['isParent'] = true;
                $arr = array();
                foreach ($coldata as $menu){
                    if(mb_substr($menu, 0, 4) == 'ucm:' && is_readable(DF_PATH . mb_substr($menu, 4) . '.ucm')) {
                        $arr['id'] = $menu;
                        $arr['name'] = ' > ' . $menu;
                        $arr['leaf'] = true;
                        $arr['ucm'] = true;
                    } elseif (!empty($system->modules['menu'][$menu])) {
                        $arr['id'] = $menu;
                        $arr['name'] = ' > ' . $system->modules['menu'][$menu]['title'];
                        $arr['leaf'] = true;
                    }
                    $cur_arr[$i]['children'][] = $arr;
                }
                
                if(!array_key_exists('children', $cur_arr[$i]))
                    $cur_arr[$i]['leaf'] = true;
                                
                $i++;
            }
        }
        
        return $cur_arr;
   }
   //Не активные (неиспользуемые) модули меню
   public function getUnusedMenus(){
        global $system, $skin;
        require_once(ADMIN_PATH . 'libs/ucm.php');
        
        $menus = parse_ini_file(CONFIG_PATH . 'menus.ini', true);
        require_once(SKIN_PATH . $system->skin . '/skin.php');
        $current = array();
        $unused_arr = array();
        $i = 0;
        $usused = array();
        foreach ($menus as $column => $coldata){
            if(!empty($skin['menu_point'][$column])){
                $current['/' . $column] = __('Column') . ': ' . $skin['menu_point'][$column];
                foreach ($coldata as $menu){
                    if(mb_substr($menu, 0, 4) == 'ucm:' && is_readable(DF_PATH . mb_substr($menu, 4) . '.ucm')) {
                        $current[$menu] = ' > ' . $menu;
                    } elseif (!empty($system->modules['menu'][$menu])) {
                        $current[$menu] = ' > ' . $system->modules['menu'][$menu]['title'];
                    }
                }
            }
        }
        foreach ($skin['menu_point'] as $column => $text) {
            if(!isset($current['/' . $column])) {
                $unused_arr[$i]['id'] = '/' . $column;
                $unused_arr[$i]['name'] = __('Column') . ': ' . $text;
                $unused_arr[$i]['isParent'] = true;
                $i++;
            }
        }
        
        foreach ($system->modules['menu'] as $menu => $data) {
            if(!rcms_in_array_recursive(' > ' . $data['title'], $current)) {
                $unused_arr[$i]['id'] = $menu;
                $unused_arr[$i]['name'] = ' > ' . $data['title'];
                $i++;
            }
        }
        
        $ucms = ucm_list();
        foreach ($ucms as $menu=>$data) {
            if(!rcms_in_array_recursive(' > ucm:' . $menu, $current)) {
                $unused_arr[$i]['id'] = 'ucm:' . $menu;
                $unused_arr[$i]['name'] = ' > ucm:' . $menu;
                $unused_arr[$i]['ucm'] = true;
                $i++;
            }
        }
        
        return $unused_arr;
   }
   //Сохранение модулей меню
   public function saveCurrentMenus($menus){
        if(!empty($menus) && is_array($menus)){
            $content = '';
            $i = -1;
            
        	foreach ($menus as $element){
            	if(mb_substr($element, 0, 1) == '/') {
    	            $content .= '[' . mb_substr($element, 1) . "]\n";
        	        $i = 0;
    	        } elseif($i !== -1) {
                	$content .= $i . '=' . $element . "\n";
                	$i++;
            	}
        	}
            
            file_write_contents(CONFIG_PATH . 'menus.ini', $content);
        }
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


?>