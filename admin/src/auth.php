<?php

class Auth
{
//    public $system;
//    public function __construct(){
//        $this->system = new rcms_system();
//        $this->system->initializeUser();
//    }
    public function isAdmin()
    {
        if (!$this->isAuthorized())
            return false;
        return strcmp($_SESSION['USER']['role_key'], "ADMIN") == 0 ? true : false;
    }
    public function isAuthorized()
    {
        return isset($_SESSION['USER']) && isset($_SESSION['USER']['DepartmentID']) &&
            isset($_SESSION['DB']) && isset($_SESSION['DB']['host']) && isset($_SESSION['DB']['login']) &&
            isset($_SESSION['DB']['password']);
    }
    public function getUserIP()
    {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        if (strlen($user_ip) == 3)
            $user_ip = '127.0.0.1';
        return $user_ip;
    }
    public function logout()
    {
        global $system;
        $system->logOutUser();
        setcookie('reloadcms_user');
        $_COOKIE['reloadcms_user'] = '';
        Response::_SUCCESS("Успешный выход из системы");
    }

    public function login($login, $password, $remember = false)
    {
        global $system;
        if ($system->logged_in)
            Response::_ERROR("Вы уже авторизованы");
        else {
            if ($system->logInUser($login, $password, $remember)) {
                Response::_SUCCESS("Пользователь вошел в систему");
            } else {
                Response::_ERROR("Неверный логин или пароль");
            }
        }
    }
}
