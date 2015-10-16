<?php
//require_once 'config.php';

// Класс для соединения с БД
class Connection{
    // Получение PDO-объекта для соединения с БД кафедры или false в случае ошибки
    public static function getConnection(){
        global $auth, $conf;
        if ($auth->isAuthorized()) // Пользователь должен быть авторизован
            return new PDO('mysql:host='.$_SESSION['DB']['host'].';dbname=department_'.$_SESSION['USER']['DepartmentID'],$_SESSION['DB']['login'],$_SESSION['DB']['password']);
        return false;
    }
    // Получение PDO-объекта для соединения с БД или false в случае ошибки
    public static function getAdminConnection(){
        global $conf;
        //получение данных о соединении из файла mysql.ini
        $conf = parse_ini_file(CONFIG_PATH . 'mysql.ini', true);        
                
        try {
            $pdo = new PDO('mysql:host='.$conf['server'].';dbname='.$conf['db'],$conf['username'],$conf['password']);
            return $pdo;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Атрибуты объекта PDOStatement
    public static function getDriverOptions(){
        return array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY);
    }
}
?>