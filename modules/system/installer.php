<?php
//	define('RCMS_ROOT_PATH', './');
        define('EXTENSIONS_PATH',    RCMS_ROOT_PATH . 'extensions/');
        define('SYSTEM_MODULES_PATH',RCMS_ROOT_PATH . 'modules/system/');
        define('ENGINE_PATH',        RCMS_ROOT_PATH . 'modules/engine/');
        define('MODULES_PATH',       RCMS_ROOT_PATH . 'modules/general/');
        define('CONFIG_PATH',        RCMS_ROOT_PATH . 'config/');
        define('DATA_PATH',     RCMS_ROOT_PATH . 'content/');
        
        define('RCMS_DB_CHARSET', 'utf8');
        define('RCMS_DB_COLLATE', 'utf8_general_ci');
//	echo SYSTEM_MODULES_PATH."hcms.php";
       // print_r(scandir("./"));
        require_once SYSTEM_MODULES_PATH."hcms.php";
    //    require_once SYSTEM_MODULES_PATH."system.php";
        
        function __($string){
                global $lang;
                if(!empty($lang['def'][$string])) {
                        return $lang['def'][$string];
                } else {
                        return $string;
                }
        }
        
	function xdb_init($server,$port,$username,$password)
	{
		$connect = mysql_connect($server.':'.$port,$username,$password);
                
		if(!$connect)
		{
			xdb_error();
			exit;
		}
		return $connect;
	}
	
	function check_modules(){
		$modules = array( // necessary modules
		'mod_rewrite',
		'mysql',
		'mbstring');
		$ret_arr = Array();
		$data = Array();
		$mods = apache_get_modules();
		$ret_arr['total'] = count($modules);
		for ($i=0;$i<$ret_arr['total'];$i++){
			$arr = Array();
			$arr['number'] = ($i+1);
			$arr['module'] = $modules[$i]; // name of module
			$arr['state'] = (in_array($modules[$i],$mods) || extension_loaded($modules[$i]))?1:0; // module's state
			$data[] = $arr;
		}
		$ret_arr['data'] = $data;
		return $ret_arr;
	}
        
        function remove_installer(){
            $ret_arr = Array();
            if (!rmdir(RCMS_ROOT_PATH."/resources")){
                $ret_arr['msg'] = __("Error in delete resources directory");
                $ret_arr['success'] = false;
                return $ret_arr;
            }
            if (!unlink(RCMS_ROOT_PATH."/app.js")){
                $ret_arr['msg'] = __("Error in delete app.js");
                $ret_arr['success'] = false;
                return $ret_arr;
            }
            $ret_arr['success'] = true;
            return $ret_arr;
        }
	
	function check_folder($url, $arr, $number){
		$files = scandir($url);
		array_shift($files); 
		array_shift($files);
		$l = count($files);
		for ($i=0;$i<$l;$i++){
			$new_url = $url.$files[$i];
			if (is_dir($new_url)){
				$a = Array();
				$a['number'] = $number;
				$a['url'] = mb_substr($new_url, 2);
				if (is_writable($new_url))
					$a['write'] = 1;
				else
					$a['write'] = 0;
				$arr[] = $a;
				$number++;
				$arr = check_folder($new_url,$arr,$number);
			}
		}
		return $arr;
	}
	
	function check_rights(){
		$ret_arr = Array();
		$arr = Array();
		$a = Array();
		$a['number'] = 1;
		$a['url'] =  mb_substr(DATA_PATH, 2);
		if (is_writable(DATA_PATH))
			$a['write'] = 1;
		else
			$a['write'] = 0;
		$arr[] = $a;
		
		$a['number'] = 2;
		$a['url'] = mb_substr(CONFIG_PATH, 2);
		if (is_writable(CONFIG_PATH))
			$a['write'] = 1;
		else
			$a['write'] = 0;
		$arr[] = $a;
		$ret_arr['data'] = check_folder(DATA_PATH,$arr,3);
		$ret_arr['total'] = count($ret_arr['data']); 
		return $ret_arr;
	}
	
	function db_install($Host,$Port,$User,$Password,$Create,$DBname,$Prefix,$iBlocksTables,$UsersTables,$Engine){
		$ret_arr = Array();
                $ret_arr['success'] = true;
                 
		$connect = xdb_init($Host,$Port,$User,$Password);
                
		if(!$connect){
                    $ret_arr['msg'] = __("Connection error");
                    $ret_arr['success'] = false;
                    return $ret_arr;
                }
                if (!mysql_query("SET AUTOCOMMIT=0;")) return false;
		if (!mysql_query("START TRANSACTION;")) return false;
               
		if($Create=='on'){
			if (!mysql_query("CREATE DATABASE `$DBname` CHARACTER SET ".RCMS_DB_CHARSET." COLLATE ".RCMS_DB_COLLATE.";")){
                            $ret_arr['msg'] = __("Choose another DB name");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }
			mysql_select_db($DBname);
		} else {
			if (!mysql_select_db($DBname) || !mysql_query ("ALTER DATABASE `$DBname` CHARACTER SET ".RCMS_DB_CHARSET." COLLATE ".RCMS_DB_COLLATE.";")){
                            $ret_arr['msg'] = __("Error in DB update");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }
		}
                
		if (!mysql_query ("SET NAMES '".RCMS_DB_CHARSET."';")){
                    $ret_arr['msg'] = __("Error in SET NAMES ".RCMS_DB_CHARSET);
                    $ret_arr['success'] = false;
                    return $ret_arr;
                }
		if ($iBlocksTables=='on'){
			$ibgroups=$Prefix.'ibgroups';
			if (!mysql_query("CREATE TABLE `$ibgroups`(
													  `ibgid` varchar(24) NOT NULL,
													  `title` tinytext NOT NULL,
													  `ibids` tinytext,
													  `ibgids` tinytext,
													  PRIMARY KEY  (`ibgid`)
													) ENGINE=".$Engine.";")){
                            $ret_arr['msg'] = __("Error in ibgroups table creation");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        } 
			$infoblocks = $Prefix.'iblocks';

			if (!mysql_query("CREATE TABLE `$infoblocks`(
														`ibid` varchar(24) NOT NULL,
													  `title` tinytext NOT NULL,
													  `description` tinytext,
													  `fields` text NOT NULL,
													  `extopt` varchar(1024),
													  PRIMARY KEY  (`ibid`),
													  UNIQUE KEY `ibid` (`ibid`)
													) ENGINE=".$Engine.";")){ 
                            $ret_arr['msg'] = __("Error in iblocks table creation");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }
                                                                                                        

			$containers = $Prefix.'ibcontainers';

			if (!mysql_query("CREATE TABLE `$containers`(
													  `contid` varchar(24) NOT NULL,
													  `ibid` varchar(24) NOT NULL,
													  `title` tinytext NOT NULL,
													  `description` tinytext,
													  `access` tinyint(4) default NULL,
													  `ordering` tinyint(4) unsigned default NULL,
													  `substitutions` text,
													  PRIMARY KEY  (`contid`),
													  UNIQUE KEY `contid` (`contid`)
													) ENGINE=".$Engine.";")){
                            $ret_arr['msg'] = __("Error in ibcontainers table creation");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }

			$categories = $Prefix.'ibcategories';
			
			if (!mysql_query("CREATE TABLE `$categories`(
														`catid` smallint(6) unsigned NOT NULL auto_increment,
													  `contid` varchar(24) default NULL,
													  `ibid` varchar(24) NOT NULL,
													  `title` tinytext NOT NULL,
													  `description` tinytext,
													  `access` tinyint(4) default NULL,
													  `icon` tinytext,
													  `last_item` mediumint(9) unsigned default NULL,
													  `itemscount` mediumint(9) unsigned default NULL,
													  PRIMARY KEY  (`catid`)
													) ENGINE=".$Engine.";")){
                            $ret_arr['msg'] = __("Error in ibcategories table creation");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }
                                                                                                        
			$ibitems = $Prefix.'ibitems';

			if (!mysql_query("CREATE TABLE `$ibitems` (
														`id` int(11) unsigned NOT NULL auto_increment,
													  `catid` smallint(6) unsigned NOT NULL,
													  `contid` varchar(24) NOT NULL,
													  `ibid` varchar(24) NOT NULL,
													  `comcount` smallint(6) unsigned default NULL,
													  `views` smallint(6) unsigned default NULL,
													  `idate` datetime NOT NULL,
													  `title` tinytext NOT NULL,
													  `description` text,
													  `idata` mediumtext NOT NULL,
													  `source` tinytext,
													  `uid` varchar(64) NOT NULL,
													  `tags` tinytext,
													  `hidenhigh` tinyint(2) unsigned default NULL,
													  `index1` mediumint(9) default NULL,
													  `index2` int(11) default NULL,
													  `index3` tinytext,
													  `index4` tinytext,
													  `index5` tinytext,
													  PRIMARY KEY  (`id`),
													  FULLTEXT KEY `idata` (`title`,`description`,`idata`)
													) ENGINE=".$Engine.";")){
                            $ret_arr['msg'] = __("Error in ibitems table creation");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }

			if (!mysql_query ( "INSERT INTO $infoblocks VALUES ('articles', 'Статьи', '', 'a:2:{s:7:\"fd_name\";a:0:{}s:5:\"fd_id\";a:0:{}}', 'a:5:{s:10:\"loadritems\";b:1;s:10:\"ritems_cnt\";s:1:\"3\";s:15:\"ritems_matchtag\";b:1;s:14:\"ritems_extcond\";s:0:\"\";s:10:\"ritems_sel\";s:0:\"\";}'),
			('exchange_docs', 'Кабинет пользователя', '', 'a:2:{s:7:\"fd_name\";a:0:{}s:5:\"fd_id\";a:0:{}}', 'a:5:{s:10:\"loadritems\";b:1;s:10:\"ritems_cnt\";s:1:\"3\";s:15:\"ritems_matchtag\";b:1;s:14:\"ritems_extcond\";s:0:\"\";s:10:\"ritems_sel\";s:0:\"\";}');" )){
                            $ret_arr['msg'] = __("Error in insert into table infoblocks");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }
			if (!mysql_query ( "INSERT INTO $containers VALUES ('articles', 'articles', 'Статьи', '', null, null, null), ('news', 'articles', 'Новости', '', null, null, null), ('static_pages', 'articles', 'Статические страницы', '', null, null, null), ('exchange_docs', 'exchange_docs', 'Почтовые документы', '', null, null, null);" )){
                            $ret_arr['msg'] = __("Error in insert into table containers");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }
			if (!mysql_query ( "INSERT INTO $categories VALUES (1, 'exchange_docs', 'exchange_docs', 'Общие', '', 0, '', 0, 0);")){
                            $ret_arr['msg'] = __("Error in insert into table categories");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }
		}
		if ($UsersTables){
			$users=$Prefix.'users';


			if (!mysql_query("CREATE TABLE `$users`(
													`uid` mediumint(9) unsigned NOT NULL auto_increment,
												  `username` char(32) NOT NULL,
												  `nickname` char(64) NOT NULL,
												  `password` char(40) NOT NULL,
												  `email` char(128) NOT NULL,
												  `ext` text COMMENT 'Serialized',
												  `hiding` tinyint(2) unsigned default NULL,
												  `level` smallint(6) unsigned default NULL,
												  `rights` tinytext,
												  `gids` tinytext COMMENT 'Serialized',
												  `friends` text COMMENT 'Serialized',
												  PRIMARY KEY  (`uid`),
												  UNIQUE KEY `uid` (`uid`),
												  UNIQUE KEY `login` (`username`),
												  UNIQUE KEY `email` (`email`),
												  UNIQUE KEY `nickname` (`nickname`),
												  UNIQUE KEY `username` (`username`)
												) ENGINE=".$Engine.";")){
                            $ret_arr['msg'] = __("Error in create users table");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }

			$ugcategories = $Prefix.'ugcategories';
			if (!mysql_query("CREATE TABLE `$ugcategories`(
											`catid` tinyint(4) unsigned NOT NULL auto_increment,
										  `title` tinytext NOT NULL,
										  `description` tinytext,
										  `level` smallint(6) unsigned default NULL,
										  `hidenhigh` tinyint(2) unsigned default NULL,
										  PRIMARY KEY  (`catid`),
										  UNIQUE KEY `catid` (`catid`)
										) ENGINE=".$Engine.";")){
                            $ret_arr['msg'] = __("Error in create ugcategories table");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }

			$usergroups = $Prefix.'usergroups';
		
			if (!mysql_query("CREATE TABLE `$usergroups`(
											`gid` smallint(6) unsigned NOT NULL auto_increment,
										  `catid` tinyint(4) unsigned NOT NULL,
										  `title` tinytext NOT NULL,
										  `description` tinytext,
										  `type` tinyint(2) unsigned default NULL,
										  `ext` text,
										  `level` smallint(6) unsigned default NULL,
										  `rights` tinytext,
										  PRIMARY KEY  (`gid`),
										  UNIQUE KEY `gid` (`gid`)
										) ENGINE=".$Engine.";")){
                            $ret_arr['msg'] = __("Error in create usergroups table");
                            $ret_arr['success'] = false;
                            return $ret_arr;
                        }

			$mail = $Prefix . 'imail';
			if (!mysql_query("CREATE TABLE IF NOT EXISTS `$mail` (
										  `id` int(11) NOT NULL AUTO_INCREMENT,
										  `uid_from` int(11) unsigned NOT NULL,
										  `uid_to` tinytext NOT NULL,
										  `to_gids` tinytext,
										  `date_time` char(10) NOT NULL,
										  `message` text NOT NULL,
										  `read` int(1) NOT NULL DEFAULT '0',
										  `blacklists` int(1) NOT NULL DEFAULT '0',
										  `basket` int(1) NOT NULL DEFAULT '0',
										  PRIMARY KEY (`id`)
										) ENGINE=".$Engine.";")){
                            $ret_arr['msg'] = __("Error in create imail table");
                            $ret_arr['success'] = false;
                            return $ret_arr;    
                        }
                                                                                
		}
		$result = (file_exists ( DATA_PATH.'users.cache.dat' ) ? unlink ( DATA_PATH.'users.cache.dat' ) : true);
		if (!mysql_query("COMMIT;")){
                    $ret_arr['msg'] = __("Error in commit transaction");
                    $ret_arr['success'] = false;
                    return $ret_arr;
                }
		@mysql_close ( $connect );
                makeconfig($Host,$Port,$User,$Password,$DBname,$Prefix);
		return $ret_arr;
	}
        
        function getTimezone(){
            $ret_arr = Array();
            $ret_arr['success'] = true;
            $rer_arr['data'] = Array();
            $rer_arr['d'] = 101;
            $tz = DateTimeZone::listIdentifiers();
            if (!$tz){
                $ret_arr['success'] = false;
                $ret_arr['msg'] = __("Couldn't get timezone list");
                return $ret_arr;
            }
        //    $rer_arr['data'] = $tz[$i];
            for ($i=0;$i<count($tz);$i++){
                $arr = Array();
                $arr['id'] = $i;
                $arr['val'] = __($tz[$i]); 
                $ret_arr['data'][] = $arr;
            }
            return $ret_arr;
        }
	
	function infb_install($mode,$editor_mode,$enable_simplified_menu,$timezone){
		$enable_simplified_menu = ($enable_simplified_menu=='on')?1:0;
		$arr = Array();
		$arr['save'] = 1;
		$arr['interface'] = Array();
		$arr['interface']['mode'] = $mode;
		$arr['interface']['editor_mode'] = $editor_mode;
		$arr['interface']['enable_simplified_menu'] = $enable_simplified_menu;
		$arr['gst_nick'] = '';
                if (!file_put_contents(CONFIG_PATH.'ibconfig.dat',serialize($arr))){
                    $ret_arr['success'] = false;
                    $ret_arr['msg'] = __("Unable to rewrite ".CONFIG_PATH.'ibconfig.dat');
                    return $ret_arr;
                }
   
                $ini_arr = @parse_ini_file(CONFIG_PATH.'config.ini');
             //   print_r($ini_arr);
                if (!$ini_arr){
                    $ret_arr['success'] = false;
                    $ret_arr['msg'] = __("Unable to parse ini file ".CONFIG_PATH.'config.ini');
                    return $ret_arr;
                }
                if (@file_exists ( CONFIG_PATH.'config.ini' )) {
			if (! @unlink ( CONFIG_PATH.'config.ini' )){
                                $ret_arr['success'] = false;
                                $ret_arr['msg'] = __("File ".CONFIG_PATH.'config.ini already exists and cannot be deleted!');
                                return $ret_arr;
                        }
		}
		$cf = @fopen ( CONFIG_PATH.'config.ini', "w" );
		if (! $cf){
                        $ret_arr['success'] = false;
                        $ret_arr['msg'] = __("Cannot write to file ".CONFIG_PATH.'config.ini. Please check access rules!');
                        return $ret_arr;
                }
                $tz = DateTimeZone::listIdentifiers();
		fwrite ( $cf, 'title = "'.$ini_arr['title'].'"
                            short_title = "'.$ini_arr['short_title'].'"
                            site_url = "'.$ini_arr['site_url'].'"
                            copyright = "'.$ini_arr['copyright'].'"
                            enable_rss = "'.$ini_arr['enable_rss'].'"
                            logging = "'.$ini_arr['logging'].'"
                            num_of_latest = "'.$ini_arr['num_of_latest'].'"
                            perpage = "'.$ini_arr['perpage'].'"
                            adm_perpage = "'.$ini_arr['adm_perpage'].'"
                            index_module = ""
                            wmh = "'.$ini_arr['wmh'].'"
                            th_width = "'.$ini_arr['th_width'].'"
                            th_height = "'.$ini_arr['th_height'].'"
                            wm_alpha_level = ""
                            watermark = ""
                            pr_flood = "'.$ini_arr['pr_flood'].'"
                            registered_accesslevel = "'.$ini_arr['registered_accesslevel'].'"
                            detect_lang = "'.$ini_arr['detect_lang'].'"
                            default_skin = "'.$ini_arr['default_skin'].'"
                            allowchskin = "'.$ini_arr['allowchskin'].'"
                            default_lang = "ru"
                            allowchlang = "'.$ini_arr['allowchlang'].'"
                            timezone="'.$tz[$timezone].'"
		');
		fclose($cf);
                return true;
	}
	
	function makeconfig($Host,$Port,$User,$Password,$DBname,$Prefix) {
		if (@file_exists ( CONFIG_PATH.'mysql.ini' )) {
			if (! @unlink ( CONFIG_PATH.'mysql.ini' ))
				die ( 'File <b>'.CONFIG_PATH.'mysql.ini</b> already exists and cannot be deleted!' );
		}
		$cf = @fopen ( CONFIG_PATH.'mysql.ini', "w" );
		if (! $cf)
			die ( 'Cannot write to file <b>config/mysql.ini</b>. Please check access rules!' );
		fwrite ( $cf, '# Хост сервака MySQL
		server = "' . $Host . '"
		# Порт
		port = "'.$Port.'" 
		# Имя пользователя MySQL
		username = "'.$User.'" 
		# Пароль пользователя
		password = "'.$Password.'" 
		# Название БД
		db = "'.$DBname.'" 
		# Префикс для таблиц
		prefix = "'.$Prefix.'" 
                # Кодировка БД
                db_charset="'.RCMS_DB_CHARSET.'"
		');
		fclose($cf);
		return;
	}
        
        
        function get_extensions(){ // returns list of extensions from extensions directory
            require_once SYSTEM_MODULES_PATH.'hcms.php';
            $ret_arr = array();
            $files = scandir(EXTENSIONS_PATH);
            array_shift($files); 
            array_shift($files);
            $l = count($files);
            $ret_arr['total'] = $l;
            $ret_arr['success'] = true;
            $ret_arr['extensions'] = Array();
            for ($i=0;$i<$l;$i++){
                $arr = Array();
                $ex = explode ('.', $files[$i]);
                if (strcmp(mb_strtolower($ex[count($ex)-1]),"json")==0){
                    $json = file_get_contents(EXTENSIONS_PATH . $files[$i]);
                    if ($json){
                        $obj = unpack_data($json);
                        $arr['url'] = $files[$i];
                        $arr['name'] = __(get_ext_name(EXTENSIONS_PATH . $files[$i]));
                        $arr['version'] = __($obj['version']);
                        $arr['description'] = __($obj['description']);
                        $ret_arr['extensions'][] = $arr;
                    }
                }
            }
            return $ret_arr;
        }
        
        function remove_extension($filename){ // remove extension from extensions directory by its filename
            require_once SYSTEM_MODULES_PATH.'filesystem.php';
            $files = scandir(EXTENSIONS_PATH);
            array_shift($files); 
            array_shift($files);
            $l = count($files);
            for ($i=0;$i<$l;$i++){
                if (strcmp($files[$i],$filename)==0){
                    rcms_delete_files(EXTENSIONS_PATH.$filename);
                    return true;
                }
            }
            return false;
        }
        
        function get_ext_name($url){ // parse url to return name of file without its extension
            $ex = explode ('/', $url);
            $name = explode (".", $ex[count($ex)-1]);
            return $name[count($name)-2];
        }
        
        function extensions_installation(){// process of extension installation
            require_once SYSTEM_MODULES_PATH.'filesystem.php';
            $files = scandir(EXTENSIONS_PATH);
            array_shift($files); // remove .
            array_shift($files); // remove ..
            $f_l = count($files);
            for ($i=0;$i<$f_l;$i++){ // loop by extensions
                $ex = explode ('.', $files[$i]);
                if (strcmp(mb_strtolower($ex[count($ex)-1]),"json")==0){
                    $file_url = EXTENSIONS_PATH . $files[$i];
                    $json = file_get_contents($file_url);
                    $json = unpack_data($json);
                    if (!$json)
                        return false;
                    $order = $json['order'];
             //       var_dump($order);
                    for ($i=0;$i<count($order);$i++){ // loop by order
                        switch($order[$i]){
                            case 'files': // module sources creation
                                foreach ($json['files'] as $url_module => $value) {
                                    if (!file_put_contents(RCMS_ROOT_PATH.$url_module, $value['text'])){
                                   //     echo "!put contents: ".RCMS_ROOT_PATH.$url_module;
                                        return false;
                                    }
                                    switch($value['rights']){ // set rights on module
                                        case 'r':
                                            @chmod(RCMS_ROOT_PATH.$url_module, 0444);
                                            break;
                                        case 're':
                                            @chmod(RCMS_ROOT_PATH.$url_module, 0555);
                                            break;
                                        case 'rwe':
                                            @chmod(RCMS_ROOT_PATH.$url_module, 0777);
                                            break;
                                        case 'rw':
                                            @chmod(RCMS_ROOT_PATH.$url_module, 0666);
                                            break;
                                        case 'we':
                                            @chmod(RCMS_ROOT_PATH.$url_module, 0333);
                                            break;   
                                    }
                                }
                                break;
                                case 'installer': // brocess of installation
                                    if (!file_put_contents(EXTENSIONS_PATH."install.php", $json['installer'])) // temp install script creation
                                        return false;
                                    require_once EXTENSIONS_PATH."install.php";
                                    eval("install_packet_".get_ext_name($file_url)."();");
                                    rcms_delete_files(EXTENSIONS_PATH."install.php");
                                break;
                                case 'sql': 
                                    $sql_arr = $json['sql'];
                                    require_once ENGINE_PATH."api.mysql.php";
                                    $sql = new MySQLDB();
                                    for ($q=0;$q<count($q);$q++){
                                        $sql->query($sql_arr[$q]);
                                    }
                                break;
                        }
                    }
               //     echo EXTENSIONS_PATH.$files[$i];
                    rcms_delete_files(EXTENSIONS_PATH.$files[$i]); // delete json-file of installed extension after intallation
                }
            }
            return true;
        }
        
        function upl_extension(){  
            require_once SYSTEM_MODULES_PATH.'filesystem.php';
            if (!file_exists(EXTENSIONS_PATH))
                rcms_mkdir(EXTENSIONS_PATH);
            if (!is_writable(EXTENSIONS_PATH))
                chmod(EXTENSIONS_PATH, 0777);
            $upl_file = EXTENSIONS_PATH . basename($_FILES['extension']['name']);
            $mime = $_FILES['extension']['type'];
            $ret_arr = Array();
            if (strcmp($mime,"application/x-gzip") === 0){
                $json = gzfile($_FILES['extension']['tmp_name']);
                $json = $json[0];
                if (!file_put_contents(EXTENSIONS_PATH.  get_ext_name($upl_file) . '.json', $json)){
                    $ret_arr['msg'] = __("Can not create json file");
                    $ret_arr['success'] = false;
                } else
              /*  $t = new tar();
                $t->openTAR($upl_file);
                var_dump($t->files);*/
                $ret_arr['success'] = true;
            } else {
                $ex = explode ('.', $upl_file);
                if (strcmp(mb_strtolower($ex[count($ex)-1]),"json")!=0){
                   $ret_arr['msg'] = __("It is not json file"); 
                   $ret_arr['success'] = false; 
                } else {
                    if (move_uploaded_file($_FILES['extension']['tmp_name'], $upl_file)) {
                        chmod($upl_file, 0777);
                        $ret_arr['success'] = true;
                    } else {
                        $ret_arr['msg'] = __("Can not move uploaded file"); 
                        $ret_arr['success'] = false; 
                    }
                }
            }
            return $ret_arr;
        }
?>
