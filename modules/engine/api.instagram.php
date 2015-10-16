<?php

require_once ('api.datamng.php');

class Instagram extends DataMng {
	var $result;
	var $query;
    var $imconfig;
	
	function __construct() {
		$this->DataMng ();
//		$this->db = new MySQLDB ();
	}
	
	function InstagramQuery($id, $idkey, $table, $selection = '*') {
		$this->setId ( $id, $idkey );
		$this->setWorkTable ( $this->prefix . $table );
		$this->BeginDataRead ( $selection );
		return $this->Read ();
	}			
	
    function InstagramBeginRead($rows = '*', $compare = '=', $and = '', $ordering = '', $desc = '', $limitation = false) {
		$rows = ($rows == '*' ? '*' : @implode ( ',', $rows ));
		$where = (empty ( $this->id ) || empty ( $this->idkey ) ? '' : "WHERE $this->idkey $compare '$this->id' ");
		return $this->db->ExecuteReader ( "SELECT $rows FROM $this->table " . $where . "$and $ordering $desc " . ($limitation ? 'LIMIT ' . (is_array ( $limitation ) ? $limitation [0] . ',' . $limitation [1] : $limitation) : '') . ";" );
	}
    function InstagramNumRows() {
		$this->query = $this->query==null?$this->GetLastResultId():$this->query;
        return mysql_num_rows ( $this->query );
	}
}

?>