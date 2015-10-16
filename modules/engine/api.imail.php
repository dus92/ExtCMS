<?php

require_once ('api.datamng.php');

class iMail extends DataMng {
	var $result;
	var $query;
    var $imconfig;
	
	function __construct() {
		$this->DataMng ();
//		$this->db = new MySQLDB ();
	}
	
	function iMailQuery($id, $idkey, $table, $selection = '*') {
		$this->setId ( $id, $idkey );
		$this->setWorkTable ( $this->prefix . $table );
		$this->BeginDataRead ( $selection );
		return $this->Read ();
	}
	
//	function iMailBeginRead($id = '', $idkey = '', $table, $selection = '*', $limitation = 30, $ordering = '', $desc = '') {
//		// $where = $this->ParseFilter($filter,'contid');
//		$this->setId ( $id, $idkey );
//		$where = (empty ( $this->id ) || empty ( $this->idkey ) ? '' : "WHERE $this->idkey = '$this->id'");
//		$work_table = $this->prefix . $table;
//		$this->BeginRawDataRead ( "SELECT " . ($selection == '*' ? '*' : implode ( ',', $selection )) . " FROM $work_table $where $ordering $desc " . ($limitation ? 'LIMIT ' . (is_array ( $limitation ) ? $limitation [0] . ',' . $limitation [1] : $limitation) : '') . ';' );
//	}
	
	function iMailReadArray($query) {
		$this->result = mysql_fetch_array ( $query );
		return $this->result;
	}
	function iMailSelect($id, $idkey, $table = 'imail', $selection = '*', $ordering = '', $desc = '') {
		$this->setId ( $id, $idkey );
		$where = (empty ( $this->id ) || empty ( $this->idkey ) ? '' : "WHERE $this->idkey = '$this->id'");
		$work_table = $this->prefix . $table;
		$select = "SELECT " . ($selection == '*' ? '*' : implode ( ',', $selection )) . " FROM $work_table $where $ordering $desc";
		$this->query = $this->db->query ( $select );
		return $this->query;
	}
	
    function iMailBeginRead($rows = '*', $compare = '=', $and = '', $ordering = '', $desc = '', $limitation = false) {
		$rows = ($rows == '*' ? '*' : @implode ( ',', $rows ));
		$where = (empty ( $this->id ) || empty ( $this->idkey ) ? '' : "WHERE $this->idkey $compare '$this->id' ");
		return $this->db->ExecuteReader ( "SELECT $rows FROM $this->table " . $where . "$and $ordering $desc " . ($limitation ? 'LIMIT ' . (is_array ( $limitation ) ? $limitation [0] . ',' . $limitation [1] : $limitation) : '') . ";" );
	}
	
	function iMailDropData($idkey = '', $id = '', $table = '') {
		if ($idkey == '' || $id == '') {
			$idkey = $this->idkey;
			$id = $this->id;
		}
		
		if ($table == '') {
			$table = $this->table;
		}
		
		return $this->db->ExecuteNonQuery ( "DELETE FROM $table WHERE $idkey = '$id';" );
	}
    
    
    function iMailNumRows() {
		$this->query = $this->query==null?$this->GetLastResultId():$this->query;
        return mysql_num_rows ( $this->query );
	}
    
    //upload images for slideshow and small circles
    function iMailUploadImg()
    {
        $imconfig = @unpack_data(file_get_contents(CONFIG_PATH.'imconfig.dat'));
        $circles_img = array($imconfig['about_image'], $imconfig['technology_image'], $imconfig['opportunities_image'], $imconfig['projects_image'], $imconfig['news_image']);
        $circles_class = array('.about_inner', '.technology_inner', '.opportunities_inner', '.projects_inner', '.news_inner');
        $circles_tpl_class = array('.tpl_about1_inner', '.tpl_technology1_inner', '.tpl_opportunities1_inner', '.tpl_projects1_inner', '.tpl_news1_inner');
        $slideshow_img = array($imconfig['img_main4'], $imconfig['img_main3'], $imconfig['img_main2'], $imconfig['img_main1'], $imconfig['img_main5']);
        //hcms_debug($imconfig);
        print('
        <script type="text/javascript">
            jQuery(document).ready(function() 
            {');
        
        for ($i=0;$i<sizeof($slideshow_img);$i++)
        {
            print('$("#slideshow").append("<li><img src='.$slideshow_img[$i].' width=392 height=377 border=0  /></li>");');    
        }
        
        for ($i=0;$i<sizeof($circles_img);$i++)
        {
            print('
                $(".my_class'.$circles_class[$i].'").css("backgroundImage","url('.$circles_img[$i].')");
                $(".my_class'.$circles_tpl_class[$i].'").css("backgroundImage","url('.$circles_img[$i].')");
            ');
        }
                
        print('
            $("#div_fon").append("<img src='.$imconfig['background'].' width=100% height=100% />");        
            jQuery("#slideshow").fadeSlideShow(); });             
            </script>            
        ');
    }
}

?>