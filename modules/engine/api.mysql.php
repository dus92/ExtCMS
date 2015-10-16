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

/**
 * Debug on/off
 *
 */
define("DEBUG",1);

/**
 * MySQL database working class
 *
 */
class MySQLDB
{
	var $connection;
	var $db_config = array();

	/**
	 * last query result id
	 *
	 * @var MySQL result
	 */
	var $lastresult;
	/**
	 * last query assoc value
	 *
	 * @var bool
	 */
	var $assoc = true;

	/**
	 * Initialises connection with MySQL database server and selects needed db
	 *
	 * @param resource $connection
	 * @return bool
	 */
	function MySQLDB($connection=false)
	{
		global $system;
		global $mysql_data;
		
		if($connection)
		{
			$this->connection = $connection;
		}
		else if(isset($mysql_data['connection']))
		{
			$this->connection = $mysql_data['connection']; // use existing connection link
			$this->db_config = $mysql_data['db_config'];
		}
		else
		{
			if(!($this->db_config = @parse_ini_file(CONFIG_PATH.'mysql.ini')))
			{
				glob_db_error(__('Cannot load mysql configuration'));
				return false;
			}

			if(!extension_loaded('mysql'))
			{
				glob_db_error(__('Unable to load module for database server "mysql": PHP mysql extension not available!'));
				return false;
			}

			$this->connection = @mysql_connect($this->db_config['server'], $this->db_config['username'], $this->db_config['password']);


			if(empty($this->connection))
			{
				glob_db_error(__('Unable to connect to database server!'));
				return false;
			}
			else
			{
				$mysql_data['connection'] = $this->connection; // save connection link, make global
				$mysql_data['db_config'] = $this->db_config;
			}

			if(!@mysql_select_db($this->db_config['db'], $this->connection))
			{
				$this->db_error();
				return false;
			}
			
			$mysql_data['qcache']['enable'] = true;
			$mysql_data['queries_used'] = 0;
		}
		
		// codepage fix
		mysql_query ("SET NAMES 'utf8'"); // TODO: encoding from cfg
		return true;
	}

	/**
	 * Executes query and returns result identifier
	 *
	 * @param string $query
	 * @return resource
	 */
	function query($query)
	{
		global $system;
		global $mysql_data;
		
		// use escape/vf function for input data.
		if(!empty($mysql_data['qcache'][$query]) && $mysql_data['qcache']['enable'])
		{
			$result = $mysql_data['qcache'][$query];
			if(is_resource($result) && mysql_num_rows($result) > 0)
			{
				mysql_data_seek($result, 0);
			}
		}
		else 
		{
			$result = @mysql_query($query, $this->connection) or $this->db_error(0, $query);
			$mysql_data['qcache'][$query] = $result;
			$mysql_data['queries_used']++;
		}
		return $result;
	}

	// нагло сперты названия из класса mysqlcommand (MySQL Connector 4 .NET):)
	/**
	 * Executes query and makes abstract data read available
	 *
	 * @param string $query
	 * @return integer
	 */
	function ExecuteReader($query)
	{
		$this->lastresult = $this->query($query);
		$result = mysql_affected_rows();
		return $result;
	}

	/**
	 * Link to query method
	 *
	 * @param string $query
	 * @return resource
	 */
	function ExecuteNonQuery($query)
	{
		$result = $this->query($query);
		return (mysql_affected_rows()==0 ? false : $result);
	}

	/**
	 * Returns array with from the current/needed query result
	 * 
	 * @param integer $resultid
	 * @return array
	 */
	function Read($resultid = false)
	{
		if($this->assoc)
		{
			$result = @mysql_fetch_assoc($resultid ? $resultid : $this->lastresult) or false;
		}
		else
		{
			$result = @mysql_fetch_row($resultid ? $resultid : $this->lastresult) or false;
		}
		return $result;
	}

	/**
	 * Returns one row from the current query result
	 *
	 * @param int $row
	 * @return string
	 */
	function ReadSingleRow($row)
	{
		return mysql_result($this->lastresult,$row) or false;
	}

	/**
 	* Prints MySQL error message; swithing DEBUG, prints MySQL error description or sends it to administrator
 	*
 	*/
	function db_error($show=0,$query='')
	{
		global $system;
		if(!in_array(mysql_errno(),array(1062,1065,1191))) // Errcodes in array are handled at another way :)
		{
			if(DEBUG==1 || $show==1)
			{
				$warning='<br><b>'.__('MySQL Error').':</b><br><i>';
				$warning.=mysql_errno().' : '.mysql_error().(empty($query) ? '</i>' : '<br>In query: <textarea cols="50" rows="7">'.$query.'</textarea></i>');
				glob_db_error($warning, false);
			}
			else
			{
				glob_db_error(__('Error occurred'), false);
				$message.=mysql_errno().':'.mysql_error()."\r\n";
				$message.=(empty($query) ? '' : "In query: \r\n".$query."\r\n");
				rcms_log_put('MySQL error',$system->user['username'],$message);
			}
		}
	}

	/**
	 * Escapes string to use in SQL query
	 *
	 * @param string $string
	 * @return string
	 */
	function escape($string)
	{
		if (!get_magic_quotes_gpc())
		{
			return mysql_real_escape_string($string,$this->connection);
		}
		else
		{
			return mysql_real_escape_string(stripslashes($string),$this->connection);
		}
	}

	/**
	 * Disconnects from database server
	 *
	 */
	function disconnect()
	{
		@mysql_close($this->connection);
	}
}

/**
 * Returns cutted down entry data
 * mode=(1->alpha,digits; 2->alpha; 3->digits; 4->alpha,digits,-_.,;  
 * 5->lang alpha,digits,punctuation; 6->phone; 7->alpha,digits,space; default->mysql_real_escape_string)
 *
 * @param string $data
 * @param int $mode
 * @return string
 */
function vf($data,$mode=0)
{
	global $mysql_data;
	switch ($mode)
	{
		case 1:
			return preg_replace("/[^a-z0-9A-Z]/",'',$data); // числа, буквы
			break;
		case 2:
			return preg_replace("/[^a-zA-Z]/",'',$data); // буквы
			break;
		case 3:
			return (int)$data; // числа
			break;
		case 4:
			return preg_replace("/[^a-z0-9A-Z_\,.-]/",'',$data); // числа, буквы, тире, прочерк, точка, запятая
			break;
		case 5:
			return str_replace('\'','\\\'',preg_replace("/[^ \"%+=&:;.,)(@!$?\r\n".__('a-zA-Z')."0-9_\[\]\-]/",'',$data)); // соотв. текущему языку алфавит + цифры и знаки препинания
			break;
		case 6:
			return preg_replace("/[^0-9)+(]/",'',$data); // телефон
			break;
		case 7:
			return preg_replace("/[^ =a-z0-9A-Z]/",'',$data); // числа, буквы, пробел
			break;
		default:
			return mysql_real_escape_string($data, $mysql_data['connection']); // блек-лист в крайнем случае.
			break;
	}
}

/**
 * Prints error to output
 *
 * @param string $errdesc
 * @param boolean $env
 */
function glob_db_error($errdesc, $env = true)
{
	global $system;
	if(($_SERVER['SCRIPT_NAME']!='index.php' || empty($system)))
	{
		if($env)
		{
			die('<span style="font-family: Verdana, sans-serif; font-size: 10pt; margin: 5px;"><b style="color: red;">Error:</b> '.$errdesc.'</span>');
		}
		else 
		{
			die($errdesc);
		}
	}
	else
	{
		show_error($errdesc);
	}
}
?>