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

require_once('api.datamng.php');

/**
 * Class for usergroups management
 *
 */
class UserGroup extends DataMng 
{
	function UserGroup()
	{
		$this->DataMng();
		$this->db = new MySQLDB();
	}
	
	/**
	 * Begins usergroups list reading
	 *
	 * @param mixed $filter
	 * @param mixed $selection
	 * @param mixed $limitation
	 * @param string $ordering
	 * @param string $desc
	 */
	function BeginUserGroupsListRead($filter=false, $selection='*', $limitation=20, $ordering='title', $desc = '')
	{
		$where = $this->ParseFilter($filter,'catid');
		$table = $this->prefix.'usergroups';
		$this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table $where ORDER BY '$ordering' $desc ".($limitation ? 'LIMIT '.(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation) : '').';');
	}
	
	/**
	 * Returns selected usergroup data
	 *
	 * @param integer $gid
	 * @param mixed $selection
	 * @return array
	 */
	function ReadSingleUserGroupData($gid, $selection='*')
	{
		$this->setId($gid,'gid');

		$this->setWorkTable($this->prefix.'usergroups');
		$this->BeginDataRead($selection);

		return $this->Read();
	}
	
	/**
	 * Begins categories list reading
	 *
	 * @param array $selection
	 * @param integer $limitation
	 * @param string $ordering
	 * @param string $desc
	 */
	function BeginUGCategoriesListRead($selection='*', $limitation=20, $ordering='title', $desc = '')
	{
		$table = $this->prefix.'ugcategories';
		$this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table ORDER BY '$ordering' $desc ".($limitation ? 'LIMIT '.(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation) : '').';');
	}

	/**
	 * Returns single category data
	 *
	 * @param integer $catid
	 * @param array $selection
	 * @return array
	 */
	function ReadSingleUGCategoryData($catid, $selection='*')
	{
		$this->setId($catid,'catid');

		$this->setWorkTable($this->prefix.'ugcategories');
		$this->BeginDataRead($selection);

		return $this->Read();
	}
}

?>