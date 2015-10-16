<?php 
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) Hakkah~CMS Development Team                                //
//   http://hakkahcms.sourceforge.net                                         //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////

error_reporting(E_ERROR);
$result='';
$iblock = new iBlock();

if(isset($_REQUEST['action']))
{
	$action = vf($_REQUEST['action'],2);
}
else
{
	$action='';
}

switch ($action)
{
	case 'show':
		if(isset($_GET['item']))
		{
			$id = vf($_GET['item'],3);
			
			$iblockitem = new iBlockItem();
			$item = $iblockitem->ReadSingleiBlockItemData($id, '*', ($system->language === $system->config['default_lang'] ? '' : $system->language));

			if($item)
			{
				$iblockitem->setId($id,'id');
				$iblockitem->editData(array('views' => 'views+1'),true);
				$tpldata = array_merge(unpack_data($item['idata']),$item);
				unset($item['idata']);
				unset($tpldata['idata']);

				// indexed values
				$iblockdata = $system->iblocks[$tpldata['ibid']];
				$iblockdata['fields'] = unpack_data($iblockdata['fields']);
				if(isset($iblockdata['fields']['fd_store']))
				{
					$keys = array_keys(array_filter($iblockdata['fields']['fd_store'], "ib_filter_store"));
					$count = sizeof($keys);
					for($i=0; $i<$count; $i++)
					{
						$tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]] = $tpldata[$iblockdata['fields']['fd_store'][$keys[$i]]];
					}
				}
				
				// checkboxes, selectors, radios
				if(isset($iblockdata['fields']['fd_radio']))
				{
					$keys = array_keys(array_filter($iblockdata['fields']['fd_radio']));
					$count = sizeof($keys);
					for($i=0; $i<$count; $i++)
					{
						if(empty($tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]]) || $iblockdata['fields']['fd_type'][$keys[$i]]!='radio')
						{
							continue;
						}
						$values = explode("\n",$iblockdata['fields']['fd_radio'][$keys[$i]]);
						$vcount = sizeof($values);
						for($vi=0; $vi<$vcount; $vi++)
						{
							$values[$vi] = explode('-',$values[$vi],2);
							array_walk($values[$vi],'ib_trim');
							$rvalues[$values[$vi][0]] = $values[$vi][1];
						}
						$tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]] = $rvalues[$tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]]];
					}
					unset($values);
				}
				if(isset($iblockdata['fields']['fd_checkbox']))
				{
					$keys = array_keys(array_filter($iblockdata['fields']['fd_checkbox']));
					$count = sizeof($keys);
					for($i=0; $i<$count; $i++)
					{
						if(empty($tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]]) || $iblockdata['fields']['fd_type'][$keys[$i]]!='checkbox')
						{
							continue;
						}
						$tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]] = ($tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]] ? $iblockdata['fields']['fd_checkbox'][$keys[$i]] : 0);
					}
				}
				if(isset($iblockdata['fields']['fd_select']))
				{
					$keys = array_keys(array_filter($iblockdata['fields']['fd_select']));
					$count = sizeof($keys);
					for($i=0; $i<$count; $i++)
					{
						if(empty($tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]]) || $iblockdata['fields']['fd_type'][$keys[$i]]!='select')
						{
							continue;
						}
						$values = explode("\n",$iblockdata['fields']['fd_select'][$keys[$i]]);
						$vcount = sizeof($values);
						for($vi=0; $vi<$vcount; $vi++)
						{
							$values[$vi] = explode('-',$values[$vi],2);
							array_walk($values[$vi],'ib_trim');
							$rvalues[$values[$vi][0]] = $values[$vi][1];
						}
						$tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]] = $rvalues[$tpldata[$iblockdata['fields']['fd_id'][$keys[$i]]]];
					}
					unset($values);
				}
				// end

				$first_tag = false;
				if(!empty($tpldata['tags']))
				{
					$tags = '';
					$tpldata['tags'] = explode(',',$tpldata['tags']);
					if(!empty($tpldata['tags']))
					{
						$first_tag = trim($tpldata['tags'][0]);
					}
					$count=sizeof($tpldata['tags']);
					for ($s=0; $s<$count; $s++)
					{
						$tags.='<a href="index.php?module=iblocks&tag='.trim($tpldata['tags'][$s]).'">'.trim($tpldata['tags'][$s]).'</a> ';
					}
					$tpldata['tags'] = $tags;
				}
				else
				{
					$tpldata['tags'] = '<a href="#">'.__('Not tagged').'</a>';
				}

				// related items
				$iblockdata['extopt'] = unpack_data($iblockdata['extopt']);
				if(!empty($iblockdata['extopt']['loadritems']))
				{
					$selection = array('id','title','idate');
					if(!empty($iblockdata['extopt']['ritems_sel']))
					{
						$selection = array_merge($selection, unpack_data($iblockdata['extopt']['ritems_sel']));
					}

					// parse ritems ext condition
					$variables = array();
					if(preg_match_all("/{(.*?)}/", $iblockdata['extopt']['ritems_extcond'], $variables))
					{
						$variables = $variables[1];
						$ecv_count = sizeof($variables);
						for($vi=0; $vi<$ecv_count; $vi++)
						{
							$iblockdata['extopt']['ritems_extcond'] = str_replace('{'.$variables[$vi].'}', (isset($tpldata[$variables[$vi]]) ? '\''.$tpldata[$variables[$vi]].'\'' : 0), $iblockdata['extopt']['ritems_extcond']);
						}
					}

					$tpldata['relateditems'] = array();
					$tags_match = ($iblockdata['extopt']['ritems_matchtag'] ? $first_tag : false);
					$iblockitem->BeginiBIListReadByTag(array('contid' => $tpldata['contid']), $tags_match, $selection, $iblockdata['extopt']['ritems_cnt'],($tags_match ? 'relevance' : 'idate'),'DESC', $iblockdata['extopt']['ritems_extcond']); // index1={rooms_number}
					while($ritem=$iblockitem->Read())
					{
						array_push($tpldata['relateditems'], $ritem);
					}
				}
				// end

				$system->addInfoToHead('<meta name="Description" content="' . $tpldata['sdescription'] . '">' . "\n");
				$system->addInfoToHead('<meta name="Keywords" content="' . (isset($tpldata['keywords']) ? $tpldata['keywords'] : $tpldata['tags']) . '">' . "\n");

				// iblocks v1 compability
				if(empty($tpldata['itext']))
				{
					$tpldata['itext'] = @rcms_parse_text_by_mode($tpldata['text'],$tpldata['mode']);
				}
				else
				{
					$tpldata['itext'] = rcms_parse_text_by_mode($tpldata['itext'],$tpldata['mode']);
				}
				// end
				$tpldata['description'] = rcms_parse_text_by_mode($tpldata['description'],$tpldata['mode']);
				$tpldata['source'] = rcms_parse_text_by_mode($tpldata['source'],'text');
				$tpldata['poster_profile'] = $system->getUserData($tpldata['uid']);

				$contsubst = @unpack_data($system->containers[$tpldata['contid']]['substitutions']);
				if($contsubst['cont_tpls'] == 'own')
				{
					$result = rcms_parse_module_template_mpath(IBLOCKS_PATH.'cont_tpls/'.$tpldata['contid'].'/item-full.tpl',$tpldata);
				}
				else
				{
					$result = rcms_parse_module_template($tpldata['ibid'].'/item-full.tpl',$tpldata);
				}
				$title = $tpldata['title'];
			}
			else
			{
				$result = __('Item with id #').$id.' '.__('is not exist');
				$title = $result;
			}
		}
		else if(isset($_GET['contid']))
		{
			// container categories listing
			$contid = vf($_GET['contid'],4);
			$contdata = $system->containers[$contid];
			$iblock->BeginCategoriesListRead($contid);
			while($catdata = $iblock->Read())
			{
				$catdata['title'] = __($catdata['title']);
				$result .= rcms_parse_module_template($contdata['ibid'].'/cat-list.tpl', $catdata );
			}
			$title = $contdata['title'];
		}
		else if(isset($_GET['ibid']))
		{
			// iblock containers listing
			$ibid = vf($_GET['ibid'],4);
			$ibdata = $system->iblocks[$ibid];
			$containers = ib_filter_plarrays($system->containers, 'ibid', $ibid);
			$contnum = sizeof($containers);
			$contids = array_keys($containers);
			for($i=0; $i<$contnum; $i++)
			{
				$containers[$contids[$i]]['title'] = __($containers[$contids[$i]]['title']);
				$containers[$contids[$i]]['description'] = __($containers[$contids[$i]]['description']);
				$result .= rcms_parse_module_template($ibid.'/cont-list.tpl', $containers[$contids[$i]] );
			}
			//*/
			$title = __($ibdata['title']);
		}
		break;
	case 'list': // LIST IBLOCK ITEMS SWITCHING THE FILTER CRITERIAS
	default:
		$iblockitem = new iBlockItem();
		$contid = false;
		$ibid = false;
		$title = false;
		$own_tpls = false;
		if(isset($_GET['catid']))
		{
			// category items listing
			$catid = vf($_GET['catid'],3);
			$url_filter = '&amp;catid='.$catid; // for pagination
			// reading information about selected category for getting 'ibid' (for iblock templates and indexed item values usage) and 'title' values.
			$catdata = $iblock->ReadSingleCategoryData($catid,array('ibid','contid','title'));
			if($catdata)
			{
				$title = __($catdata['title']);
				$ibid = $catdata['ibid'];
				$contid = $catdata['contid'];
				$contsubst = unpack_data($system->containers[$contid]['substitutions']);
				$own_tpls = $contsubst['cont_tpls'] == 'own';
				$filter = $catid; // here filter is $catid, because catid is default filter criteria here
			}
			else
			{
				$result .= '<h2>'.__('Category with this identifier does not exist').'</h2>';
				break;
			}
		}
		else if(isset($_GET['contid']))
		{
			// container items listing
			$contid = vf($_GET['contid'],4);
			$url_filter = '&amp;contid='.$contid;
			if(isset($system->containers[$contid]))
			{
				$title = __($system->containers[$contid]['title']); // as upper
				$ibid = $system->containers[$contid]['ibid'];
				$contsubst = @unpack_data($system->containers[$contid]['substitutions']);
				$own_tpls = $contsubst['cont_tpls'] == 'own';
				$filter = array('contid' => $contid); // associated array -> here item's 'contid' must be $contid
			}
			else
			{
				$result .= '<h2>'.__('Container with this identifier does not exist').'</h2>';
				break;
			}
		}
		else if(isset($_GET['ibid']))
		{
			// iblock items listing
			$ibid = vf($_GET['ibid'],4);
			if(isset($system->iblocks[$ibid]))
			{
				$title = __($system->iblocks[$ibid]['title']); // as upper
				$url_filter = '&amp;ibid='.$ibid;
				$filter = array('ibid' => $ibid);
			}
			else
			{
				$result .= '<h2>'.__('Infoblock with this identifier does not exist').'</h2>';
				break;
			}
		}
		else if(isset($_GET['ibgid']))
		{
			// iblock group items listing
			$ibgid = vf($_GET['ibgid'],4);
			if(isset($system->ibgroups[$ibgid]))
			{
				$title = __($system->ibgroups[$ibgid]['title']);
				$url_filter = '&amp;ibgid='.$ibgid;
				$ibids = explode(',',$system->ibgroups[$ibgid]['ibids']); // getting iblock group iblocks listing
				$ibid = $ibids[0]; // indexed values options of this ibid will be used by default
				$filter = array('ibid' => $ibids, 'logic' => 'OR'); // here ibid may be one of the iblock group ibids ('logic' value is set to 'OR')
			}
			else
			{
				$result .= '<h2>'.__('Infoblock group with this identifier does not exist').'</h2>';
				break;
			}
		}
		else
		{
			$ibid = 'articles'; // default listing
			$title = $system->iblocks[$ibid]['title'];
			$url_filter = '&amp;ibid=articles';
			$filter = array('ibid' => 'articles');
		}
		
		// indexed values
		$count = 0;
		if($ibid)
		{
			if(!isset($system->iblocks[$ibid]))
			{
				return;
			}
			$iblockdata = $system->iblocks[$ibid];
			$iblockdata['fields'] = unpack_data($iblockdata['fields']); // read indexed values options
			$iblockdata['extopt'] = unpack_data($iblockdata['extopt']); // read extended values options
			if(isset($iblockdata['fields']['fd_store']))
			{
				$indexes = array_filter($iblockdata['fields']['fd_store'], "ib_filter_store"); // filter not needed keys
				$keys = array_keys($indexes);
				$count = sizeof($keys);
			}
		}
		if(isset($_REQUEST['next'])) // specifies offset
		{
			$next=vf($_REQUEST['next'],3);
		}
		else
		{
			$next = 0;
		}

		if(isset($_GET['order'])) // specifies ordering
		{
			$order = GetOrder($_GET['order']);
			$url_filter.='&order='.$order;
		}
		else
		{
			$order = 'idate';
		}

		if(isset($_GET['asc'])) // descending/ascending order :)
		{
			$desc = 'ASC';
			$url_filter.='&asc';
		}
		else
		{
			$desc = 'DESC';
		}

		if(isset($_GET['tag'])) // tags filter
		{
			$tag = preg_replace("/[^ ".__('a-zA-Z')."0-9\-]/",'',$_GET['tag']);
			$title = __('Items with tag').' "'.$tag.'"'; // titles for each container ???
		}

		$i=0;

		// Building query
		$extwhere = (!empty($tag) ? 'tags LIKE \'%'.$tag.'%\'' : '');
		if(isset($_GET['from']) && isset($_GET['until'])) // calendar date pick
		{
			$from = vf($_GET['from'],3);
			$until = vf($_GET['until'],3);
			$url_filter.='&from='.$from.'&until='.$until;
			$extwhere = (!empty($extwhere) ? ' AND UNIX_TIMESTAMP(idate) BETWEEN '.$from.' AND '.$until : 'UNIX_TIMESTAMP(idate) BETWEEN '.$from.' AND '.$until);
		}
		else if(isset($_GET['from']))
		{
			$from = vf($_GET['from'],3);
			$url_filter.='&from='.$from;
			$extwhere = (!empty($extwhere) ? ' AND UNIX_TIMESTAMP(idate) > '.$from : 'UNIX_TIMESTAMP(idate) > '.$from);
		}
		else if(isset($_GET['until']))
		{
			$until = vf($_GET['until'],3);
			$url_filter.='&until='.$until;
			$extwhere = (!empty($extwhere) ? ' AND UNIX_TIMESTAMP(idate) < '.$until : 'UNIX_TIMESTAMP(idate) < '.$until);
		}
		
		$iblockitem->setWorkTable($iblockitem->prefix.'ibitems'.($system->language === $system->config['default_lang'] ? '' : '_'.$system->language));
		$cnt = $iblockitem->BeginiBlockItemsListRead($filter, '*',(isset($next) ? array($next,$system->config['perpage']) : $system->config['perpage']),$order,$desc,$extwhere, true);
		if($cnt>$system->config['perpage'])
		{
			// making pagination
			$pagination = hcms_npagination($cnt, $system->config['perpage'], $next,'?module=iblocks'.$url_filter.(!empty($tag) ? '&amp;tag='.$tag : ''), '<span>{current}</span>');
		}
		
		$ibid = false;
		$item_i = 0;
		while ($item = $iblockitem->Read())
		{
			// extended item data stored in 'idata'
			$item = array_merge(unpack_data($item['idata']),$item);
			$item_i ++;

			// indexed values
			if(isset($iblockdata['fields']['fd_store']))
			{
				for($ibindex=0; $ibindex<$count; $ibindex++)
				{
					// assign the 'index*' value to corresponding extended iblock field
					$item[$iblockdata['fields']['fd_id'][$keys[$ibindex]]] = $item[$iblockdata['fields']['fd_store'][$keys[$ibindex]]];
				}
			}

			$item['uptitle'] = $title;
			if(isset($pagination))
			{
				$item['pagination'] = $pagination;
			}
			if(!$ibid)
			{
				// LISTUP TEMPLATE
				$result.=rcms_parse_module_template($item['ibid'].'/listup.tpl',$item);
				$ibid = $item['ibid'];
			}
			$i++;

			if(!empty($item['tags']))
			{
				$tags = '';
				$item['tags'] = explode(',',$item['tags']);
				$tags_count=sizeof($item['tags']);
				for ($s=0; $s<$tags_count; $s++)
				{
					$tags.='<a href="index.php?module=iblocks'.$url_filter.'&tag='.trim($item['tags'][$s]).'">'.trim($item['tags'][$s]).'</a> ';
				}
				$item['tags'] = $tags;
			}
			else
			{
				$item['tags'] = '<a href="#">'.__('Not tagged').'</a>';
			}

			$item['description'] = rcms_parse_text_by_mode($item['description'],$item['mode']);
			$item['i'] = $item_i;
			// ITEM-LIST TEMPLATE
			if($own_tpls)
			{
				$result .= rcms_parse_module_template_mpath(IBLOCKS_PATH.'cont_tpls/'.$contid.'/item-list.tpl',$item);
			}
			else
			{
				$result.=rcms_parse_module_template($ibid.'/item-list.tpl',$item);
			}

			// ITEM-DELIMITER TEMPLATE
			if(!empty($iblockdata['extopt']['delim_cond']))
			{
				if($i%$iblockdata['extopt']['delim_cond']==0 && $i<$system->config['perpage'])
				{
					$result.=rcms_parse_module_template($item['ibid'].'/listdelim.tpl',$item);
				}
			}
		}
		if($i == 0)
		{
			$result .= '<h2>'.__('No elements found').'</h2>';
		}
		// LISTDOWN TEMPLATE
		$result.=rcms_parse_module_template($ibid.'/listdown.tpl',(isset($pagination) ? array('pagination' => $pagination) : array()));
		break;
}

$title = (empty($title) ? __('Infoblocks') : $title);
$system->config['pagename'] = $title;
show_window($title, $result, (empty($align) ? 'left' : $align));
function ib_trim(&$value)
{
	$value = trim($value);
}
?>