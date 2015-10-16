<?php
/**
 * Returns server script path
 *
 * @return string
 */
function hcms_get_location()
{
	$str=getenv('SCRIPT_FILENAME');
	return mb_substr($str,0,mb_strrpos($str,'/')).'/';
}

/**
 * Returns web script path
 *
 * @return string
 */
function hcms_get_weblocation()
{
	$str='http://'.getenv('HTTP_HOST').getenv('REQUEST_URI');
	return mb_substr($str,0,mb_strrpos($str,'/')).'/';
}

/**
 * Returns pagination html code from the needed parameters
 *
 * @param integer $total 
 * @param integer $perpage
 * @param integer $current
 * @param string $link
 * @return string
 */
function hcms_npagination($total, $perpage, $current, $link, $curenviroment='{current}', $delimiter = ' ', $arrows = false)
{
	$result = '';
	$pagesnum = ceil($total/$perpage);

	$end_result = '';
	$s=0;
	for($i=0; $i<$pagesnum; $i++)
	{
		$k = $i*$perpage;

		if($i==$current/$perpage-1) // prev
		{
			if($arrows)
			{
				$result='<a href="'.$link.'&amp;next='.($i*$perpage).'">'.$arrows[0].'</a>'.$delimiter.$result;
			}
		}

		if($i==$current/$perpage+1) // next
		{
			if($arrows)
			{
				$end_result='<a href="'.$link.'&amp;next='.($i*$perpage).'">'.$arrows[1].'</a>';
			}
		}

		if(($k < $current-$perpage && $i >= 3 ) || ($k > $current+$perpage && $i <= $pagesnum-4))
		{
			if($s==0 || ($i==$pagesnum-4 && $current>4*$perpage))
			{
				$result .= str_replace('{current}', '...', $curenviroment).$delimiter;
				$s++;
			}
			continue;
		}

		if($i!=$current/$perpage || $current===false)
		{
			$result.='<a href="'.$link.'&amp;next='.($i*$perpage).'">'.($i+1).'</a>'.$delimiter;
		}
		else
		{
			$result.= str_replace('{current}', ($i+1), $curenviroment).$delimiter;
		}
	}
	return $result.$end_result;
}

function hcms_filesize($file, $round=2)
{
	$filesize = filesize($file);
	if ($filesize >= 1073741824)
	{
		$filesize = number_format(($filesize / 1073741824),$round) . ' ' . __('Gbytes');
	}
	else if ($filesize >= 1048576)
	{
		$filesize = number_format(($filesize / 1048576),$round) . ' ' . __('Mbytes');
	}
	else if ($filesize >= 1024)
	{
		$filesize = number_format(($filesize / 1024),$round) . ' ' . __('Kbytes');
	}
	else if ($filesize >= 0)
	{
		$filesize = $filesize . ' ' . __('bytes');
	}
	else
	{
		$filesize = '0 ' . __('bytes');
	}
	return $filesize;
}

function hcms_htmlsecure($str)
{
	return htmlspecialchars($str,null,'utf-8'); // TODO: encoding from cfg
}

function hcms_hash($str)
{
	if(RCMS_COMPABILITY)
	{
		return md5($str);
	}
	else
	{
		return sha1($str);
	}
}

function hcms_debug($data)
{
	global $system;
	if($system->root)
	{
		print('<div style="width: 960px; margin: 4px; padding: 4px; border: 2px solid red; text-align: center;"><h4>This block will not be shown to non-administrators</h4><textarea rows="7" cols="130">');
		print_r($data);
		print('</textarea></div>');
	}
}

function hcms_clean_array($input, $mode = 'int')
{
	if($mode === 'wide')
	{
		return array_filter($input, 'hcms_clean_array_w_wide');
	}
	else if($mode === 'text')
	{
		return array_filter($input, 'hcms_clean_array_w_text');
	}
	else
	{
		return array_filter($input, 'hcms_clean_array_w_int');
	}
}

function hcms_clean_array_w_text($item)
{
	return preg_match("/[".__('a-zA-Z')."0-9\* _\.-]/",$item);
}

function hcms_clean_array_w_wide($item)
{
	return preg_match("/[a-z0-9A-Z_\.-]/",$item);
}

function hcms_clean_array_w_int($item)
{
	return preg_match("/[0-9]/",$item);
}

function hcms_trim_array_w(&$item)
{
	$item = trim($item);
}

if (!function_exists('json_encode')) {
	function json_encode($value)
	{
		if (is_int($value)) {
			return (string)$value;
		} elseif (is_string($value)) {
			$value = str_replace(array('\\', '/', '"', "\r", "\n", "\b", "\f", "\t"),
			array('\\\\', '\/', '\"', '\r', '\n', '\b', '\f', '\t'), $value);
			$convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
			$result = "";
			for ($i = mb_strlen($value) - 1; $i >= 0; $i--) {
				$mb_char = mb_substr($value, $i, 1);
				if (mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)) {
					$result = sprintf("\\u%04x", $match[1]) . $result;
				} else {
					$result = $mb_char . $result;
				}
			}
			return '"' . $result . '"';
		} elseif (is_float($value)) {
			return str_replace(",", ".", $value);
		} elseif (is_null($value)) {
			return 'null';
		} elseif (is_bool($value)) {
			return $value ? 'true' : 'false';
		} elseif (is_array($value)) {
			$with_keys = false;
			$n = count($value);
			for ($i = 0, reset($value); $i < $n; $i++, next($value)) {
				if (key($value) !== $i) {
					$with_keys = true;
					break;
				}
			}
		} elseif (is_object($value)) {
			$with_keys = true;
		} else {
			return '';
		}
		$result = array();
		if ($with_keys) {
			foreach ($value as $key => $v) {
				$result[] = json_encode((string)$key) . ':' . json_encode($v);
			}
			return '{' . implode(',', $result) . '}';
		} else {
			foreach ($value as $key => $v) {
				$result[] = json_encode($v);
			}
			return '[' . implode(',', $result) . ']';
		}
	}
}

function pack_data($data)
{
	return json_encode($data);	
}

function unpack_data($string)
{
	return json_decode($string, true);
}

/**
 * Multi-byte Unserialize
 *
 * UTF-8 will screw up a serialized string
 *
 * @param string
 * @return string
 */
function mb_unserialize($string) 
{
	if(@$data = unserialize($string))
	{
		return $data;
	}
	else
	{
		$string = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $string);
		return unserialize($string);
	}
}

/**
 * Multi-byte basename
 *
 * from php.net
 *
 * @param string
 * @return string
 */
function mb_basename($file)
{
	return end(explode('/',$file));
}
?>