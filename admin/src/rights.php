<?php

class Rights
{
   //формирование списка доступных модулей для отображения в меню админки
   //TODO: доработать обработку ошибки 
   public function getModules()
   {
      global $sys, $log, $user, $MODULES;
      $ret_arr = array();
      $navigation = array();
      $j = 0;
      
      foreach($MODULES as $category => $blockdata) {
        //$navigation[$category] = array();
        
        if(!empty($blockdata[1]) && is_array($blockdata[1])) {
            $navigation[$j]['group_title'] = $blockdata[0];
            
            foreach($blockdata[1] as $module => $title) {
                if(is_array($title)){
                    $count = sizeof($title);
                    for($i=0; $i<$count; $i++){
                        $navigation[$j][$module][$i] = $title[$i][1];  
                    }
                }
                else{
                    $navigation[$j][$module] = $title;
                }
            }
            $j++;
        }elseif($blockdata[0] === @$blockdata[1]){
            $navigation[$j]['group_title'] = $blockdata[0];
            $j++;
        }
      }
      return $navigation;
      
      //Response::_ERROR("Неверный логин или пароль");
    
    }
}

?>