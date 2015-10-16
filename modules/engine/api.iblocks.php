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

class iBlock extends DataMng
{
	function iBlock($system = false)
	{
		if(!$system)
		{
			global $system;
		}

		$this->DataMng();
		$this->db = new MySQLDB();

		// pre-load iblocks and containers data
		if(sizeof($system->ibgroups) == 0 || sizeof($system->iblocks) == 0 || sizeof($system->containers) == 0)
		{
			if(file_exists(IBLOCKS_PATH.'ibstruct.dat'))
			{
				$ibstruct = unpack_data(file_get_contents(IBLOCKS_PATH.'ibstruct.dat'));
			}
			else
			{
				$ibstruct = $this->CreateStructCache();
			}
		}

		if(sizeof($system->ibgroups) == 0 && isset($ibstruct['ibgroups']))
		{
			$system->ibgroups = $ibstruct['ibgroups'];
		}
		if(sizeof($system->iblocks) == 0 && isset($ibstruct['iblocks']))
		{
			$system->iblocks = $ibstruct['iblocks'];

			foreach ($ibstruct['iblock_rules'] as $right => $desc)
			{
				$system->rights_database[$right] = $desc;
			}
		}
		if(sizeof($system->containers) == 0 && isset($ibstruct['containers']))
		{
			$system->containers = $ibstruct['containers'];
		}

		if(sizeof($system->ibconfig) == 0)
		{
			$system->ibconfig = unpack_data(file_get_contents(CONFIG_PATH.'ibconfig.dat'));
		}
		// end pre-load

		// load lang strings
		global $lang;
		if(file_exists(IBLOCKS_PATH.'langs/'.$system->language.'.dat'))
		{
			if(!is_array($lang['def']))
			{
				$lang['def'] = array();
			}
			$lang['def'] = array_merge($lang['def'], unpack_data(file_get_contents(IBLOCKS_PATH.'langs/'.$system->language.'.dat')));
		}
	}

	function CreateStructCache()
	{
		global $system;

		$this->BeginiBGroupsListRead('*',false);
		while($ibgroup = $this->Read())
		{
			$ibgroup['title'] = $ibgroup['title'];
			$ibstruct['ibgroups'][$ibgroup['ibgid']] = $ibgroup;
		}

		$ibstruct['iblock_rules'] = array();
		$this->BeginiBlocksListRead('*',false);
		while($iblock = $this->Read())
		{
			$iblock['title'] = $iblock['title'];
			$iblock['description'] = $iblock['description'];
			$ibstruct['iblocks'][$iblock['ibid']] = $iblock;
			$upped_ibid = strtoupper($iblock['ibid']);
			$ibstruct['iblock_rules'] = array_merge($ibstruct['iblock_rules'], array('IBLOCKS-'.$upped_ibid.'-EDITOR' => '<b>'.$upped_ibid.'</b>:'.__('Right to post and edit iblock items'), 'IBLOCKS-'.$upped_ibid.'-POSTER' => '<b>'.$upped_ibid.'</b>: '.__('Right to post iblock items and edit its own items')));
		}

		$this->BeginContainersListRead(false,'*',false);
		while($container = $this->Read())
		{
			$container['title'] = $container['title'];
			$container['description'] = $container['description'];
			$ibstruct['containers'][$container['contid']] = $container;
		}

		if(!empty($system->ibconfig['interface']['enable_simplified_menu']))
		{
			// generate menu array
			$this->BuildSimplifiedMenuArray();
		}

		if(file_write_contents(IBLOCKS_PATH.'ibstruct.dat', pack_data($ibstruct)))
		{
			return $ibstruct;
		}
		else
		{
			return false;
		}
	}

	function BuildSimplifiedMenuArray()
	{
		global $system;
		$this->BeginCategoriesListRead(false,array('catid','contid','ibid','title'),false);
		while($category = $this->Read())
		{
			$category['title'] = __($category['title']);
			$categories[$category['catid']] = $category;
		}

		$menu_array = array();
		$count = sizeof($system->iblocks);
		$ibids = array_keys($system->iblocks);
		for($i=0; $i<$count; $i++)
		{
			if($ibids[$i] == 'static_pages')
			{
				continue;
			}
			$ib_conts = ib_filter_plarrays($system->containers,'ibid',$ibids[$i]);
			if(sizeof($ib_conts)==1)
			{
				$cont_keys = array_keys($ib_conts);
				$ib_cats = ib_filter_plarrays($categories,'ibid',$ibids[$i]);
				if(sizeof($ib_cats)==1)
				{
					// link to category
					$cat_keys = array_keys($ib_cats);
					array_push($menu_array,array('catid='.$cat_keys[0].'&contid='.$cont_keys[0].'&ibid='.$ibids[$i], $system->iblocks[$ibids[$i]]['title']));
				}
				else
				{
					array_push($menu_array,array('contid='.$cont_keys[0].'&ibid='.$ibids[$i], $system->iblocks[$ibids[$i]]['title']));
				}
			}
			else
			{
				array_push($menu_array,array('ibid='.$ibids[$i], $system->iblocks[$ibids[$i]]['title']));
			}
		}

		if(file_write_contents(IBLOCKS_PATH.'items_simplified_menu.dat', pack_data($menu_array)))
		{
			return $menu_array;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Begins categories list reading
	 *
	 * @param mixed $filter
	 * @param array $selection
	 * @param integer $limitation
	 * @param string $ordering
	 * @param string $desc
	 */
	function BeginCategoriesListRead($filter=false, $selection='*', $limitation=20, $ordering='title', $desc = '')
	{
		$where = $this->ParseFilter($filter,'contid');
		$table = $this->prefix.'ibcategories';
		$this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table $where ORDER BY '$ordering' $desc ".($limitation ? 'LIMIT '.(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation) : '').';');
	}

	/**
	 * Returns single category data
	 *
	 * @param integer $catid
	 * @param array $selection
	 * @return array
	 */
	function ReadSingleCategoryData($catid, $selection='*')
	{
		$this->setId($catid,'catid');

		$this->setWorkTable($this->prefix.'ibcategories');
		$this->BeginDataRead($selection);

		return $this->Read();
	}

	/**
	 * Begins containers list reading
	 *
	 * @param array $selection
	 * @param integer $limitation
	 * @param string $ordering
	 * @param string $desc
	 */
	function BeginContainersListRead($filter=false, $selection='*', $limitation=20, $ordering='title', $desc = '')
	{
		$where = $this->ParseFilter($filter,'ibid');
		$table = $this->prefix.'ibcontainers';
		$this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table $where ORDER BY $ordering $desc ".($limitation ? 'LIMIT '.(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation) : '').';');
	}

	/**
	 * Returns single container data
	 *
	 * @param string $contid
	 * @param array $selection
	 * @return array
	 */
	function ReadSingleContainerData($contid, $selection='*')
	{
		$this->setId($contid,'contid');

		$this->setWorkTable($this->prefix.'ibcontainers');
		$this->BeginDataRead($selection);

		return $this->Read();
	}

	/**
	 * Begins iblocks list reading
	 *
	 * @param array $selection
	 * @param integer $limitation
	 * @param string $ordering
	 * @param string $desc
	 */
	function BeginiBlocksListRead($selection='*', $limitation=20, $ordering='title', $desc = '')
	{
		$table = $this->prefix.'iblocks';
		$this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table ORDER BY $ordering $desc ".($limitation ? 'LIMIT '.(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation) : '').';');
	}

	/**
	 * Returns single iblock data
	 *
	 * @param string $contid
	 * @param array $selection
	 * @return array
	 */
	function ReadSingleiBlockData($ibid, $selection='*')
	{
		$this->setId($ibid,'ibid');

		$this->setWorkTable($this->prefix.'iblocks');
		$this->BeginDataRead($selection);

		return $this->Read();
	}

	/**
	 * Begins iblock groups list reading
	 *
	 * @param array $selection
	 * @param integer $limitation
	 * @param string $ordering
	 * @param string $desc
	 */
	function BeginiBGroupsListRead($selection='*', $limitation=20, $ordering='title', $desc = '')
	{
		$table = $this->prefix.'ibgroups';
		$this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table ORDER BY $ordering $desc ".($limitation ? 'LIMIT '.(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation) : '').';');
	}

	/**
	 * Returns single iblock group data
	 *
	 * @param string $contid
	 * @param array $selection
	 * @return array
	 */
	function ReadSingleiBGroupData($ibgid, $selection='*')
	{
		$this->setId($ibgid,'ibgid');

		$this->setWorkTable($this->prefix.'ibgroups');
		$this->BeginDataRead($selection);

		return $this->Read();
	}
}

class iBlockItem extends DataMng
{
	var $id;

	/**
	 * iBlockItem class constructor
	 *
	 * @return iBlockItem
	 */
	function iBlockItem()
	{
		$this->DataMng();
		$this->db = new MySQLDB(); // needed?
		$this->setWorkTable($this->prefix.'ibitems');
	}

	/**
	 * Begins infoblock items list reading
	 *
	 * @param mixed $filter
	 * @param array $selection
	 * @param mixed $limitation
	 * @param string $ordering
	 * @param string $desc
	 * @param string $filtering
	 * @return matching items count
	 */
	function BeginiBlockItemsListRead($filter=false, $selection='*', $limitation=20, $ordering='', $desc = '', $extwhere = false, $mcount=false)
	{
		$table = ($this->table == $this->prefix.'ibitems' ? $this->prefix.'ibitems' : $this->table);

		$where = $this->ParseFilter($filter,'catid',$extwhere);

		if($mcount)
		{
			$this->BeginRawDataRead("SELECT COUNT(*) FROM $table ".(!empty($where) ? $where : '').';');
			$cnt_opt = $mcount;
			$mcount = $this->Read();
			if($cnt_opt!==2) // count only // WTF????
			{
				$this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table $where ".(!empty($ordering) ? "ORDER BY $ordering" : '')." $desc LIMIT ".(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation).";");
			}
			return $mcount['COUNT(*)'];
		}
		else
		{
			return $this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table $where ".(!empty($ordering) ? "ORDER BY $ordering" : '')." $desc ".($limitation ? 'LIMIT '.(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation) : '').";");
		}
	}

	/**
	 * Reads single catalog item data
	 *
	 * @param integer $iid
	 * @param array $selection
	 * @return array
	 */
	function ReadSingleiBlockItemData($id, $selection='*', $tlang='')
	{
		$this->id = $id;
		$this->setId($this->id,'id');

		if(empty($tlang))
		{
			$this->setWorkTable($this->prefix.'ibitems');
			$this->BeginDataRead($selection);
			return $this->Read();
		}
		else
		{
			$table1 = $this->prefix.'ibitems_'.$tlang;
			$table2 = $this->prefix.'ibitems';
			// ignore selection
			$this->setAssoc(false);
			$this->BeginRawDataRead("SELECT * FROM $table2 LEFT JOIN $table1 ON $table1.id = '$id' OR ($table2.id = '$id' AND $table1.id = $table2.id);");
			$item = $this->Read();
			$this->setAssoc(true);
			if(!empty($item[26]))
			{
				return ib_make_itemkeys_native($item, 19);
			}
			else
			{
				return ib_make_itemkeys_native($item);
			}
		}
	}

	/**
	 * Begins associated by tag items list reading
	 *
	 * @param mixed $filter
	 * @param string $tag
	 * @param array $selection
	 * @param mixed $limitation
	 * @param string $ordering
	 * @param bool $strict
	 * @param string $desc
	 */
	function BeginiBIListReadByTag($filter=false, $tag=false, $selection='*', $limitation=3, $ordering='relevance', $desc='DESC', $extwhere=false)
	{
		$table = ($this->table == $this->prefix.'ibitems' ? $this->prefix.'ibitems' : $this->table);
		$where = $this->ParseFilter($filter,'contid',($tag ? "MATCH(title,idata,description) AGAINST ('+$tag' IN BOOLEAN MODE)".($extwhere ? 'AND '.$extwhere : '') : $extwhere));
		$this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection)).($tag ? ", MATCH(title,idata,description) AGAINST ('+$tag' IN BOOLEAN MODE) as relevance" : '')." FROM $table $where ORDER BY $ordering $desc LIMIT ".(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation).';');
	}

	function DropiBlockItems($filter)
	{
		$this->BeginiBlockItemsListRead($filter,array('id','ibid','idate'),false);
		while ($item = $this->Read())
		{
			$time = strtotime($item['idate']);
			@rcms_delete_files(GetPath('iblockimages',$item['ibid'],date("Y",$time),date("M",$time),$item['id']),true);
			@rcms_delete_files(GetPath('iblockfiles',$item['ibid'],date("Y",$time),date("M",$time),$item['id']),true);
		}

		$where = $this->ParseFilter($filter,'id');

		$table = ($this->table == $this->prefix.'ibitems' ? $this->prefix.'ibitems' : $this->table);
		if($this->BeginRawDataRead("DELETE FROM $table $where;") || mysql_errno($this->db->connection)==0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}


class iBlockComment extends DataMng
{
	/**
	 * iBlockComment class constructor
	 *
	 * @return iBlockComment
	 */
	function iBlockComment()
	{
		$this->DataMng();
		$this->db = new MySQLDB(); // needed?
		$this->setWorkTable($this->prefix.'ibcomments');
	}

	/**
	 * Begins infoblock item comments list reading
	 *
	 * @param integer $artid
	 * @param array $selection
	 * @param mixed $limitation
	 * @param string $ordering
	 * @param string $desc
	 * @param string $filtering
	 */
	function BeginiBlockCommentsListRead($artid=0, $selection='*', $limitation=20, $ordering='', $desc = '', $filtering=false)
	{
		$table = $this->prefix.'ibcomments';
		if($artid != 0 || $filtering)
		{
			$where = 'WHERE '.($artid!=0 ? (is_array($artid) ? $artid[0].'=\''.$artid[1].'\'' : 'artid='.$artid).(empty($filtering) ? '' : ' AND ') : '').($filtering ? $filtering : '');
		}

		//die("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table ".(!empty($where) ? $where : '').' '.(!empty($ordering) ? "ORDER BY $ordering" : '')." $desc LIMIT ".(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation).";");
		return $this->BeginRawDataRead("SELECT ".($selection == '*' ? '*' : implode(',',$selection))." FROM $table ".(!empty($where) ? $where : '').' '.(!empty($ordering) ? "ORDER BY $ordering" : '')." $desc LIMIT ".(is_array($limitation) ? $limitation[0].','.$limitation[1] : $limitation).";");
	}
}

function ib_checkpost($fields, $id, $ibid)
{
	$errs = 0;

	// check std variables
	$item = ib_checkstd();

	// files | rm missing
	$item['file'] = ib_checkfiles('iblockfiles',$ibid,'file',$item['idate'],$id);
	$item['image'] = ib_checkfiles('iblockimages',$ibid,'image',$item['idate'],$id);
	$item['image2'] = ib_checkfiles('iblockimages',$ibid,'image2',$item['idate'],$id);

	// custom iblock fields check
	$count = sizeof($fields['fd_id']);
	for($i=0; $i<$count; $i++)
	{
		if ($fields['fd_type'][$i] == 'file')
		{
			$item[$fields['fd_id'][$i]] = ib_checkfiles('iblockfiles',$ibid,$fields['fd_id'][$i],$item['idate'],$id);
			if($item[$fields['fd_id'][$i]] != array() && @$fields['fd_nec'][$i])
			{
				$errs++;
			}
		}
		else
		{
			if(isset($_POST[$fields['fd_id'][$i]]))
			{
				if($fields['fd_type'][$i] != 'textarea')
				{
					$item[$fields['fd_id'][$i]] = vf($_POST[$fields['fd_id'][$i]],5);
				}
				else
				{
					$item[$fields['fd_id'][$i]] = $_POST[$fields['fd_id'][$i]];
				}
			}
			else
			{
				if($fields['fd_nec'][$i])
				{
					$errs++;
				}
				else
				{
					$item[$fields['fd_id'][$i]] = '';
				}
			}
		}
		if(!empty($fields['fd_store'][$i]) && $fields['fd_store'][$i]!='idata' && $fields['fd_store'][$i]!='idate')
		{
			// indexed value
			$item['indexed'][$fields['fd_store'][$i]] = $item[$fields['fd_id'][$i]];
			unset($item[$fields['fd_id'][$i]]);
		}
		else if(!empty($fields['fd_store'][$i]) && $fields['fd_store'][$i]=='idate')
		{
			$item['idate'] = date("Y-m-d H:i:s",strtotime($item[$fields['fd_id'][$i]]));
		}
	}


	if($errs == 0)
	{
		return $item;
	}
	else
	{
		return $errs;
	}
}

function ib_checkstd()
{
	// text | locale | rm malicious
	$old_er = error_reporting(E_ERROR);
	$item['title'] = vf($_POST['title'],5);
	$item['source'] = vf($_POST['source'],5);
	$item['tags'] = vf($_POST['tags'],5);
	$item['keywords'] = vf($_POST['keywords'],5);
	$item['sdescription'] = vf($_POST['sdescription'],5);
	if($_POST['mode'] === 'html' || $_POST['mode'] === 'htmlbb')
	{
		$item['description'] = $_POST['description'];
		$item['itext'] = $_POST['itext'];
	}
	else
	{
		$item['description'] = vf($_POST['description'],5);
		$item['itext'] = vf($_POST['itext'],5);
	}
	$idate = strtotime($_POST['idate']);
	if(!$idate)
	{
		$item['idate'] = date("Y-m-d H:i:s");
	}
	else
	{
		$item['idate'] = date("Y-m-d H:i:s",$idate);
	}
	error_reporting($old_er);

	// options with fixed params
	if($_POST['comments'] == 0)
	{
		$item['comments'] = 0;
	}
	else
	{
		$item['comments'] = 1;
	}

	if($_POST['hidenhigh'] == 0) // hide/general/high visibility
	{
		$item['hidenhigh'] = 0;
	}
	else if($_POST['hidenhigh'] == 2)
	{
		$item['hidenhigh'] = 2;
	}
	else
	{
		$item['hidenhigh'] = 1;
	}

	if($_POST['mode'] == 'html')
	{
		$item['mode'] = 'html';
	}
	else if($_POST['mode'] == 'htmlbb')
	{
		$item['mode'] = 'htmlbb';
	}
	else
	{
		$item['mode'] = 'text';
	}

	return $item;
}

function ib_checkfiles($path, $ibid, $field_id, $idate, $id)
{
	$idate = strtotime($idate);
	$path = GetPath($path,$ibid,date("Y",$idate),date("M",$idate),$id);
	if(!empty($_POST[$field_id]) && is_array($_POST[$field_id]))
	{
		$true = array();
		$keys = array_keys($_POST[$field_id]);
		$count = sizeof($keys);
		for($i=0; $i<$count; $i++)
		{
			//print($path.$_POST[$field_id][$keys[$i]].': '.(file_exists($path.$_POST[$field_id][$keys[$i]]) ? 'exists' : 'missing').'; in_array: '.(in_array($path.$_POST[$field_id][$keys[$i]],$true) ? 'yes' : 'no').'<br>');
			if(!is_file($path.$_POST[$field_id][$keys[$i]]) || in_array($path.$_POST[$field_id][$keys[$i]],$true))
			{
				$true[$keys[$i]] = '';
			}
			else
			{
				$true[$keys[$i]] = $path.$_POST[$field_id][$keys[$i]];
			}
		}
		return array_filter($true);
	}
	else
	{
		return array();
	}
}

function GetPath($path, $ibid, $year = false, $month = false, $id = false)
{
	switch ($path)
	{
		case 'watermark':
			$path = IBLOCKS_PATH;
			break;
		case 'caticons':
			$path = IBLOCKS_PATH.'caticons/';
			break;
		case 'iblockimages':
			$ibid = vf($ibid,4);
			if(empty($ibid))
			{
				rcms_log_put('Hack attempt!',$system->user['username'],'Falcificated \'ibid\' variable');
				die(__('Error'));
			}
			$year = vf($year,3);
			if(empty($year))
			{
				rcms_log_put('Hack attempt!',$system->user['username'],'Falcificated \'year\' variable');
				die(__('Error'));
			}
			$month = vf($month,2);
			if(empty($month))
			{
				rcms_log_put('Hack attempt!',$system->user['username'],'Falcificated \'month\' variable');
				die(__('Error'));
			}
			$id = vf($id,1);
			if(empty($id))
			{
				rcms_log_put('Hack attempt!',$system->user['username'],'Falcificated \'id\' variable');
				die(__('Error'));
			}

			if(!file_exists(IBLOCKS_PATH.'images/'.$ibid))
			{
				mkdir(IBLOCKS_PATH.'images/'.$ibid);
			}
			if(!file_exists(IBLOCKS_PATH.'images/'.$ibid.'/'.$year))
			{
				mkdir(IBLOCKS_PATH.'images/'.$ibid.'/'.$year);
			}
			if(!file_exists(IBLOCKS_PATH.'images/'.$ibid.'/'.$year.'/'.$month))
			{
				mkdir(IBLOCKS_PATH.'images/'.$ibid.'/'.$year.'/'.$month);
			}
			if(!file_exists(IBLOCKS_PATH.'images/'.$ibid.'/'.$year.'/'.$month.'/'.$id))
			{
				mkdir(IBLOCKS_PATH.'images/'.$ibid.'/'.$year.'/'.$month.'/'.$id);
			}

			$path = IBLOCKS_PATH.'images/'.$ibid.'/'.$year.'/'.$month.'/'.$id.'/';
			break;
		case 'iblockfiles':
			$ibid = vf($ibid,4);
			if(empty($ibid))
			{
				rcms_log_put('Hack attempt!',$system->user['username'],'Falcificated \'ibid\' variable');
				die(__('Error'));
			}
			$year = vf($year,3);
			if(empty($year))
			{
				rcms_log_put('Hack attempt!',$system->user['username'],'Falcificated \'year\' variable');
				die(__('Error'));
			}
			$month = vf($month,2);
			if(empty($month))
			{
				rcms_log_put('Hack attempt!',$system->user['username'],'Falcificated \'month\' variable');
				die(__('Error'));
			}
			$id = vf($id,1);
			if(empty($id))
			{
				rcms_log_put('Hack attempt!',$system->user['username'],'Falcificated \'id\' variable');
				die(__('Error'));
			}

			if(!file_exists(IBLOCKS_PATH.'files/'.$ibid))
			{
				mkdir(IBLOCKS_PATH.'files/'.$ibid);
			}
			if(!file_exists(IBLOCKS_PATH.'files/'.$ibid.'/'.$year))
			{
				mkdir(IBLOCKS_PATH.'files/'.$ibid.'/'.$year);
			}
			if(!file_exists(IBLOCKS_PATH.'files/'.$ibid.'/'.$year.'/'.$month))
			{
				mkdir(IBLOCKS_PATH.'files/'.$ibid.'/'.$year.'/'.$month);
			}
			if(!file_exists(IBLOCKS_PATH.'files/'.$ibid.'/'.$year.'/'.$month.'/'.$id))
			{
				mkdir(IBLOCKS_PATH.'files/'.$ibid.'/'.$year.'/'.$month.'/'.$id);
			}

			$path = IBLOCKS_PATH.'files/'.$ibid.'/'.$year.'/'.$month.'/'.$id.'/';
			break;
		case 'stduploads':
			$path = DATA_PATH.'uploads/';
			break;
		case 'previews':
			$path = PREVIEWS_PATH;
			break;
		default:
			die(__('Error').': '.__('Unknown \'path\' constant'));
			break;
	}
	return $path;
}

function GetOrder($order)
{
	switch ($order)
	{
		case 'uid':
			return 'uid';
			break;
		case 'title':
			return 'title';
			break;
		case 'comcount':
			return 'comcount';
			break;
		case 'views':
			return 'views';
			break;
		case 'date':
		default:
			return 'idate';
			break;
	}
}

function ib_filter_store($store)
{
	return ($store != 'idata');
}

function ib_filter_plarrays($data, $key, $value)
{
	$keys = array_keys($data);
	$count = sizeof($keys);
	for($i=0; $i<$count; $i++)
	{
		if($data[$keys[$i]][$key] != $value)
		{
			unset($data[$keys[$i]]);
		}
	}
	return $data;
}

function ib_make_itemkeys_native($item, $offset = 0)
{
	$result['id'] = $item[0+$offset];
	$result['catid'] = $item[1+$offset];
	$result['contid'] = $item[2+$offset];
	$result['ibid'] = $item[3+$offset];
	$result['comcount'] = $item[4+$offset];
	$result['views'] = $item[5+$offset];
	$result['idate'] = $item[6+$offset];
	$result['title'] = $item[7+$offset];
	$result['description'] = $item[8+$offset];
	$result['idata'] = $item[9+$offset];
	$result['source'] = $item[10+$offset];
	$result['uid'] = $item[11+$offset];
	$result['tags'] = $item[12+$offset];
	$result['hidenhigh'] = $item[13+$offset];
	$result['index1'] = $item[14+$offset];
	$result['index2'] = $item[15+$offset];
	$result['index3'] = $item[16+$offset];
	$result['index4'] = $item[17+$offset];
	$result['index5'] = $item[18+$offset];
	unset($item);
	return $result;
}
?>