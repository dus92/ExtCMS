<?php
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) Hakkah ~ CMS Development Team                              //
//   http://hakkahcms.sourceforge.net                                         //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////

require_once('api.mysql.php');

/**
 * Data management abstract class
 *
 */
class DataMng
{
	/**
	 * MySQLDB class object
	 *
	 * @var MySQLDB
	 */
	var $db;
	/**
	 * Unique identifier value
	 *
	 * @var integer
	 */
	var $id;
	/**
	 * Unique identifier keystring
	 *
	 * @var string
	 */
	var $idkey;

	/**
	 * Table name to manipulate data
	 *
	 * @var string
	 */
	var $table;

	/**
	 * MySQL tables prefix
	 *
	 * @var string
	 */
	var $prefix;

	/**
	 * DataMng class constructor
	 *
	 * @param integer $id
	 * @param string $idkey
	 * @return DataMng
	 */
	function DataMng($id = 0, $idkey = '', $db = false)
	{
		if(!$db)
		{
			$this->db = new MySQLDB();
		}
		else
		{
			$this->db = $db;
		}
		$this->prefix = $this->db->db_config['prefix'];

		if($id!=0)
		{
			$this->id=$id;
		}
		if($idkey!='')
		{
			$this->idkey=$idkey;
		}
	}

	/**
	 * Sets table name needed for data manipulation
	 *
	 * @param string $table
	 */
	function setWorkTable($table)
	{
		$this->table = $table;
	}

	/**
	 * Adds data to selected row(s)
	 * associated array $data[keystring] = keyvalue;
	 * $quote = true leads to perfomance reduction in 3 times
	 *
	 * @param array $data
	 * @param boolean $quote
	 * @param boolean $multi_rows
	 * @return mysql_result
	 */
	function addData($data = array(), $quote = true, $multi_rows = false)
	{
		if($quote)
		{
			if($multi_rows)
			{
				// rows > 1
				$values = '(\''.implode("','",$data[0]).'\')';
				$cnt = sizeof($data);
				for($i=1; $i<$cnt; $i++)
				{
					$values .= ', ('.hcms_array_to_query($data[$i]).')';
				}

				return $this->db->ExecuteNonQuery("INSERT INTO $this->table VALUES $values;");
			}
			else
			{
				return $this->db->ExecuteNonQuery("INSERT INTO $this->table VALUES (".hcms_array_to_query($data).");");
			}
		}
		else
		{
			if($multi_rows)
			{
				// rows > 1
				$values = '('.implode(',',$data[0]).')';
				$cnt = sizeof($data);
				for($i=1; $i<$cnt; $i++)
				{
					$values .= ', ('.implode(',',$data[$i]).')';
				}

				return $this->db->ExecuteNonQuery("INSERT INTO $this->table VALUES $values;");
			}
			else
			{
				return $this->db->ExecuteNonQuery("INSERT INTO $this->table VALUES (".implode(',',$data).");");
			}
		}
	}

	/**
	 * Edits selected row
	 * associated array $data[key] = value;
	 *
	 * @param array $data
	 * @param bool $logic
	 */
	function editData($data = array(), $logic = false, $ids = false)
	{
		$keys=array_keys($data);
		$query = "UPDATE $this->table SET ";
		$count=sizeof($data);
		for($i=0; $i<$count; $i++)
		{
			$query.='`'.$keys[$i].'`='.($logic ? $data[$keys[$i]] : '\''.$data[$keys[$i]].'\'').'';
			if($i!=$count-1)
			{
				$query.=',';
			}
		}
		$query .= ($ids ? ' WHERE '.$this->idkey.'='.implode(' OR '.$this->idkey.'=', $ids) : " WHERE $this->idkey='$this->id';");
		$result = $this->db->ExecuteNonQuery($query);
		return ($result ? $result : (mysql_errno($this->db->connection) ? mysql_errno($this->db->connection) : true));
	}

	/**
	 * Updates/inserts new data in the table
	 * associated array $data[keystring] = keyvalue;
	 *
	 * @param array $data
	 */
	function updateData($data = array(), $keys = array(), $multi_rows = false, $quote = true)
	{
		foreach ($keys as $key)
		{
			$ustr[] = '`'.$key.'`=VALUES(`'.$key.'`)';
		}

		if($multi_rows)
		{
			// rows > 1

			if($quote)
			{
				$values = '(\''.implode("','",$data[0]).'\')';
				$cnt = sizeof($data);
				for($i=1; $i<$cnt; $i++)
				{
					$values .= ', ('.hcms_array_to_query($data[$i]).')';
				}
			}
			else
			{
				$values = '('.implode(',',$data[0]).')';
				$cnt = sizeof($data);
				for($i=1; $i<$cnt; $i++)
				{
					$values .= ', ('.implode(',',$data[$i]).')';
				}
			}

			//			hcms_debug("INSERT INTO $this->table VALUES $values".' ON DUPLICATE KEY UPDATE '.implode(', ',$ustr).';');
			return $this->db->ExecuteNonQuery("INSERT INTO $this->table VALUES $values".' ON DUPLICATE KEY UPDATE '.implode(', ',$ustr).';');
		}
		else
		{
			return $this->db->ExecuteNonQuery("INSERT INTO $this->table VALUES ('".implode("','",$data).'\') ON DUPLICATE KEY UPDATE '.implode(', ',$ustr).';');
		}
	}

	/**
	 * Drops selected row from needed table
	 *
	 * @param string $idkey
	 * @param mixed $id
	 * @param string $table
	 * @return MySQL result
	 */
	function dropData($idkey='', $id='', $table='', $extwhere='')
	{
		if($idkey=='' || $id=='')
		{
			$idkey = $this->idkey;
			$id = $this->id;
		}

		if($table=='')
		{
			$table=$this->table;
		}

		if(is_array($id))
		{
			return $this->db->ExecuteNonQuery("DELETE FROM $table WHERE ($idkey = ".implode(" OR $idkey = ", $id).')'.(!empty($extwhere) ? ' AND '.$extwhere : '').';');
		}
		else
		{
			return $this->db->ExecuteNonQuery("DELETE FROM $table WHERE ($idkey = '$id')".(!empty($extwhere) ? ' AND '.$extwhere : '').';');            
		}
	}

	/**
	 * Sets rowid
	 *
	 * @param integer $id
	 * @param string $idkey
	 */	function setId($id, $idkey)	
	{
		$this->id = $id;
		$this->idkey = $idkey;
	}

	/**
	 * Sets assoc/row
	 *
	 * @param boolean $assoc
	 */	function setAssoc($assoc)	
	{
		$this->db->assoc = $assoc;
	}

	/**
	 * Begins data reading from selected table
	 *
	 * @param array $keys
	 */
	function BeginDataRead($rows = '*', $limitation = false)
	{
		$rows = ($rows == '*' ? '*' : @implode(',',$rows));
		$where = (empty($this->id) || empty($this->idkey) ? '' : "WHERE $this->idkey = '$this->id'");
		return $this->db->ExecuteReader("SELECT $rows FROM $this->table ".$where." ".($limitation ? 'LIMIT '.(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation) : '').";");
	}

	/**
	 * MySQLDB::ExecuteReader() interface
	 *
	 * @param string $query
	 */
	function BeginRawDataRead($query)
	{
		return $this->db->ExecuteReader($query);
	}

	/**
	 * MySQLDB::Read() interface
	 *
	 * @return array
	 */
	function Read($resultid = false)
	{
		return $this->db->Read($resultid);
	}

	/**
	 * MySQLDB::escape() interface
	 *
	 * @param string $string
	 * @return string
	 */
	function escape($string)
	{
		return $this->db->escape($string);
	}


	// STATISTIC FUNCTIONS

	/**
	 * Returns current table next auto_increment value
	 *
	 * @return string
	 */
	function GetTableAINextValue()
	{
		$this->BeginRawDataRead("SHOW TABLE STATUS FROM `".$this->db->db_config['db']."` LIKE '$this->table'");
		$row = $this->Read();
		return $row['Auto_increment'];
	}

	/**
	 * Returns MySQLDB::last_query_num
	 *
	 * @return integer
	 */
	function GetQueries()
	{
		return $this->db->last_query_num;
	}

	/**
	 * Returns rows count from selected table
	 *
	 * @param string $idkey
	 * @param mixed $id
	 * @param string $table
	 * @return integer
	 */
	function GetRowCount($idkey='', $id='', $table='')
	{
		if(empty($table))
		{
			$table = $this->table;
		}
		if(empty($id) || empty($idkey))
		{
			$this->db->ExecuteReader("SELECT COUNT(*) FROM $table;");
			$result = $this->Read();
		}
		else
		{
			$this->db->ExecuteReader("SELECT COUNT(*) FROM $table WHERE $idkey='$id';");
			$result = $this->Read();
		}

		return $result['COUNT(*)'];
	}

	/**
	 * Link to mysql_affected_rows()
	 *
	 * @return integer
	 */
	function GetLQAffectedRows()
	{
		return mysql_affected_rows($this->db->connection);
	}

	/**
	 * Link to mysql_num_rows()
	 *
	 * @return integer
	 */
	function GetLQNumRows()
	{
		return mysql_num_rows($this->db->connection);
	}

	/**
	 * Returns last result id
	 *
	 * @return resource
	 */
	function GetLastResultId()
	{
		return $this->db->lastresult;
	}

	/**
	 * Returns WHERE clause from the input array, switching of its type
	 *
	 * @param mixed $filter
	 * @param string $idkey
	 * @param string $extwhere
	 * @return string
	 */
	function ParseFilter($filter,$idkey='',$extwhere=false)
	{
		/*
		ParseFilter:
		$filter ==
		- assoc array of arrays -> multiple id values used
		- assoc array -> idkey => id
		- id -> idkey => id
		$logic == AND || OR
		$extwhere == managed filter string
		*/

		if(is_array($filter))
		{
			$keys = array_keys($filter);
			$where = 'WHERE';
			$kcount = sizeof($keys);
			if(isset($filter['logic']))
			{
				$logic = $filter['logic'];
				unset($filter['logic']);
				$keys = array_keys($filter);
				$kcount = sizeof($keys);
				if(is_array($filter[$keys[0]]))
				{
					for($i=0; $i<$kcount; $i++)
					{
						$count = sizeof($filter[$keys[$i]]);
						for($s=0; $s<$count; $s++)
						{
							$where .= ' '.($s>0 ? $logic.' ' : '').$keys[$i].'=\''.$filter[$keys[$i]][$s].'\'';
						}
					}
					$where .= ($i>0 && $extwhere ? ' AND '.$extwhere : ' '.$extwhere);
				}
				else
				{
					for($i=0; $i<$kcount; $i++)
					{
						$where .= ' '.($i>0 ? $logic.' ' : '').$keys[$i].'=\''.$filter[$keys[$i]].'\'';
					}
					$where .= ($i>0 && $extwhere ? ' AND '.$extwhere : ' '.$extwhere);
				}
			}
			else
			{
				for($i=0; $i<$kcount; $i++)
				{
					$where .= ' '.($i>0 ? 'AND ' : '').$keys[$i].'=\''.$filter[$keys[$i]].'\'';
				}
				$where .= ($i>0 && $extwhere ? (mb_strpos($extwhere,'OR') === 0 ? ' '.$extwhere : ' AND '.$extwhere) : ' '.$extwhere);
			}
		}
		else
		{
			$where = ($filter ? "WHERE $idkey='$filter'".($extwhere ? ' AND '.$extwhere : ' '.$extwhere) : ($extwhere ? 'WHERE '.$extwhere : ''));
		}
		return $where;
	}
}

function hcms_array_to_query($data)
{
	$str = '';
	$cnt = sizeof($data);
	foreach ($data as $value)
	{
		$str .= is_numeric($value) ? $value.',' : (is_null($value) ? 'NULL,' : '\''.$value.'\',');
	}
	return mb_substr($str, 0, -1);
}
?>