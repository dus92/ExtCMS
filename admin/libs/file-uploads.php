<?php
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) ReloadCMS Development Team                                 //
//   http://reloadcms.sf.net                                                  //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////

function fupload_array($files, $dir = FILES_PATH, $preg = ''){
    if(!empty($files)){
        $total = sizeof($files['name']);
        for ($i = 0; $i < $total; $i++){
            if(!$files['error'][$i] && (empty($preg) || preg_match($preg, $files['name'][$i]))){
                if(!move_uploaded_file($files['tmp_name'][$i], $dir . preg_replace("/[^0-9a-zA-Z_\.]/",rcms_random_string('1'),$files['name'][$i]))) return false;
            }
        }
        return true;
    } else return true;
}

function fupload_get_list($dir = FILES_PATH, $regexpskip = 0){
    $sd = rcms_scandir($dir);
    $i = 0;
    $return = array();
    foreach ($sd as $file){
    	if(preg_match('/'.$regexpskip.'/', $file))
    		continue;
        $return[$i]['name'] = $file;
        $return[$i]['size'] = filesize($dir . $file);
        $return[$i]['mtime'] = filemtime($dir . $file);
        $i++;
    }
    return $return;
}

function fupload_delete($file, $dir = FILES_PATH){
    if(is_file($dir . $file)) {
        rcms_delete_files($dir . $file);
        return true;
    } else return false;
}
?>