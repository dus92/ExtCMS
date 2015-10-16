<?php
    define('RCMS_ROOT_PATH', './');
    require_once('common.php');
    
    $categories = rcms_scandir(ADMIN_PATH . 'modules', '', 'dir');
	$MODULES = array();
	foreach ($categories as $category){
		if(file_exists(ADMIN_PATH . 'modules/' . $category . '/module.php')){
			include_once(ADMIN_PATH . 'modules/' . $category . '/module.php');
		}
	}
    
    $userRights = &$system->rights;
    $root   = &$system->root;
    $files_arr = $_FILES;
    
    //require_once(SYSTEM_MODULES_PATH.'api.users.php');
    require_once(ADMIN_PATH.'src/response.php');
    require_once(ADMIN_PATH.'src/system.php');
    require(ADMIN_PATH.'src/connection.php'); 
    require_once(ADMIN_PATH.'src/auth.php');
    require_once(ADMIN_PATH.'src/rights.php');
    
    require_once(ADMIN_PATH.'src/general.php');
    
    
    //if (!isset($_REQUEST['act']))
      // Response::_ERROR ("Could not find action parameter");
    $action = $_REQUEST['act'];
    $sys = new System();    
    $auth = new Auth();
    $rights = new Rights();
    $general = new General();
    $user = new rcms_user();    
    
    switch ($action) {
        default:
            Response::_ERROR("Unknown action");
            break;

        case 'login': //авторизация пользователя           
            $params = array('username','password');
            $sys->check_necessary_params($params);            
            $auth->login($_REQUEST['username'],$_REQUEST['password'], isset($_REQUEST['remember'])?$_REQUEST['remember']:false);
            //Response::_SUCCESS("Пользователь успешно вошел в систему",$res);                        
            break;
        case 'logout': //выход из системы            
            $auth->logout();
            break;
        case 'getModules': //выход из системы            
            $res = $rights->getModules();
            Response::_SUCCESS("Получены доступные модули",$res);
            break;
        case 'getInfo': //получение общей информации о cms
            $res = $general->getInfo();
            Response::_SUCCESS("Получены общая информация о cms",$res);
            break;
        case 'sendRemarks': //отправка сообщения другим администраторам
            $params = array('remarks');
            $sys->check_necessary_params($params);
			$res = $general->sendRemarks($_REQUEST['remarks']);
            Response::_SUCCESS("Сообщение администраторам успешно отправлено");
            break;
   		case 'getSiteSettingsData': //настройка сайта
   			$res = $general->getSiteSettingsData();
   			Response::_SUCCESS("Получены данные по настройке сайта",$res);
		case 'filesUpload': //загрузка файлов на сервер
			$res = $general->filesUpload();
   			Response::_SUCCESS("Файлы успешно обработаны",$res);
   			break;
		case 'fileDelete': //удаление файлов
			$res = $general->fileDelete($_REQUEST['url']);
   			Response::_SUCCESS("Файл успешно удален",$res);
   			break;
		case 'saveSiteConfig': //сохранение параметров конфигурации сайта (config.ini)
			$res = $general->saveSiteConfig($_REQUEST);
			Response::_SUCCESS("Настройки для сайта успешно сохранены",$res);
			break;
    }
?>
