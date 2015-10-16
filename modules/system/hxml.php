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
define('HXML_GENERATOR', 'Hakkah ~ CMS ' . RCMS_VERSION_A . '.'  . RCMS_VERSION_B);

class hxml_feed{
    var $title = '';
    var $url = '';
    var $description = '';
    var $encoding = '';
    var $language = '';
    var $copyright = '';
    var $generator = HXML_GENERATOR;
    var $items = array();
    
    
    function hxml_feed($title, $url, $description, $encoding, $language, $copyright){
        $this->title = $title;
        $this->url = $url;
        $this->description = $description;
        $this->encoding = $encoding;
        $this->language = $language;
        $this->copyright = $copyright;
    }
    
    function addItem($item){
        $this->items[] = $item;
    }
    
    function showFeed(){
        echo "<?xml version=\"1.0\" encoding=\"{$this->encoding}\" ?>\r\n";
        echo "<hxml version=\"1.0\">\n";
        echo "\t<channel>\n";
        echo "\t\t<title>{$this->title}</title>\n";
        echo "\t\t<link>{$this->url}</link>\n";
        echo "\t\t<description>{$this->description}</description>\n";
        echo "\t\t<language>{$this->language}</language>\n";
        echo "\t\t<copyright>{$this->copyright}</copyright>\n";
        echo "\t\t<lastBuildDate>" . date('r') . "</lastBuildDate>\n";
        echo "\t\t<generator>{$this->generator}</generator>\n";
        foreach ($this->items as $item){
            echo "\t\t<item>\n";
            $keys = array_keys($item); // ���������� �����
            $count = sizeof($keys); // ������� ������
            for($i=0; $i<$count; $i++)
            {
            	echo "\t\t\t<".$keys[$i].'>'.$item[$keys[$i]].'</'.$keys[$i].">\n";	// ��������� � xml
            }
            echo "\t\t</item>\n";
        }
        echo "\t</channel>\n";
        echo "</hxml>\n";
    }
}
?>