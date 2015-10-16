<?php
if(!defined('HIDE_NAVIGATION') || !HIDE_NAVIGATION){
    show_window(__('Navigation'), rcms_parse_menu(' - <a href="{link}">{title}</a><br />'));
}
?>