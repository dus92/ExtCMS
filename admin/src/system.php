<?php
class System {
    public function check_necessary_params($required_params, $params=null){
        foreach ($required_params as $key => $value){
           // if (!isset($params[$value]))
            if (!isset($_REQUEST[$value]))
                Response::_ERROR ("Required parameter was not found: ".$value);
        }
    }
    
    public function is_email($email) {
	   return preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email);
    }
    public function access($rule){
        if (!isset($rule) || $rule != 1) {
            return false;
        }
        return true;
    }
    public function gen_salt(){
	return '$2a$8$'.substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(),mt_rand()))), 0, 22) . '$';
    }
    
    public function cout($info,$filename = "debug.txt") // запись в файл информации для отладки
    {
            $fp = fopen($filename, "a"); 
            $test = fwrite($fp,$info);
            fclose($fp);
    }
    
    public function emulate_mail($to, $subject, $message, $headers){
        $fp = fopen("emulate_mail.txt", "a"); 
        $test = fwrite($fp,$to."|||".$subject."|||".$message."|||".$headers."\r\n");
        fclose($fp);
        return (!$test)?false:true;
    }
}

?>