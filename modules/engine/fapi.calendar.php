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

class calendar{
    var $_temp = array();
    var $_events = array();
    var $_highlight = array();
    
    function calendar($month, $year){
        global $system;
        $this->_temp['first_day_stamp'] = mktime(0, 0, 0, $month, 1, $year);
        $this->_temp['first_day_week_pos'] = date('w', $this->_temp['first_day_stamp']);
        $this->_temp['number_of_days'] = date('t', $this->_temp['first_day_stamp']);
    }
    
    function assignEvent($day, $link){
        $this->_events[(int)$day] = $link;
    }
    
    function highlightDay($day, $style = '!'){
        $this->_highlight[(int)$day] = $style;
    }
    
    function returnCalendar(){
        global $system;
        $return = '<table class="calend">';
        $return .= '<tbody>';
        $return .= '<tr>';
        $return .= rcms_date_localise('<th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th>');
        $return .= '</tr>';
        $days_showed = 1;
        $cwpos = $this->_temp['first_day_week_pos'];
        if($cwpos == 0) $cwpos = 7;
        while($days_showed <= $this->_temp['number_of_days']){
        	if(sizeof($this->_highlight) > 0)
        	{
        		$tmp = $days_showed+7-date("d");
        	}
        	else 
        	{
	        	$tmp = 8;
        	}
    	    $return .= '<tr '.(($tmp>0 && $tmp<=7) ? 'class="sel_th"' : '').'>';
            if($cwpos > 1) {
                $return .= '<td colspan="' . ($cwpos-1) . '">&nbsp;</td>';
            }
            $inc = 0;
            for ($i = $days_showed; $i < $days_showed + 7 && $i <= $this->_temp['number_of_days'] && $cwpos <= 7; $i++){
                $tdclass = '';
                if(!empty($this->_highlight[$i])) {
                    $tdclass = 'sel ';
                }
                if(empty($this->_events[$i])) {
                    $class = 'row2';
                    $tdclass .= 'row2';
                } else {
                    $class = 'row3';
                    $tdclass .= 'row3';
                }
                if(empty($this->_events[$i])) {
                    $return .= '<td class="' . $tdclass . '">' . (($i>=$days_showed+5 || $cwpos>5) ? '<font color="red">'.$i.'</font' : $i) . '</td>';
                } else {
                    $return .= '<td class="' . $tdclass . '"><a href="' . $this->_events[$i] . '"  class="' . $class . '">' . $i . '</a></td>';
                }
                $cwpos++;
                $inc++;
            }
            $days_showed = $days_showed + $inc;
            $cwpos = 0;
            if($days_showed >= $this->_temp['number_of_days'] && $inc<7)
            {
            	$return .= '<td colspan="'.(7-$inc).'"></td>';
            }
            $return .= '</tr>';
        }
        return $return;
    }
    
}
?>