<?php
// Класс для формирование ответов сервера
class Response {
    /* Ответ в случае ошибки
     * error_text - текст сообщения об ошибке
     * data - допольнительные данные
     */
    public static function _ERROR($error_text, $data = false){
        $arr = Array();
        $arr['success'] = false;
        if ($data){
            $arr['data'] = $data;
            $arr['total'] = count($data);
        }
        $arr['msg'] = $error_text;
        echo json_encode($arr); // формирование JSON
        die; // Завершение выполнения кода
    }
    /* Ответ в случае успешно завершенного действия
     * error_text - текст сообщения
     * data - допольнительные данные
     */
    public static function _SUCCESS($success_text, $data = false){
        $arr = Array();
        $arr['success'] = true;
        if ($data){
            $arr['data'] = $data;
            $arr['total'] = count($data);
        }
        $arr['msg'] = $success_text;
        echo json_encode($arr); // формирование JSON
        die;  // Завершение выполнения кода
    }
}
?>