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

class AsyncMgr
{
	function AsyncMgr($mkout = true)
	{
		if($mkout)
		{	
			print('<script type="text/javascript" src="modules/js/jquery.js"></script>
<script type="text/javascript" src="modules/js/ajaxfileupload.js"></script>');
		}
	}

	/**
 	* Prints JavaScript code for single image upload
 	*
 	* @param mixed $path
 	* @param string $idpart
 	* @param boolean $multi
 	* @param boolean $show_input
 	* @param array $loadicon
 	* @param boolean $dwidth
 	* @param string $resize
 	* @param boolean $wm
 	*/
	function printImgUpFormJS($path, $idpart = 'image', $multi = false, $show_input = true, $loadicon = array('load_stopped.gif', 'load_process.gif'), $dwidth=false, $resize=false, $wm=false)
	{
		if(is_array($path))
		{
			$parts = $path;
			$path = $parts[0];
			$ibid = $parts[1];
			$year = $parts[2];
			$month = $parts[3];
			$id = $parts[4];			
		}
		else 
		{
			$ibid = false;
			$year = false;
			$month = false;
			$id = false;
		}
		
		print('<script type="text/javascript">
var generalIdPart = "'.$idpart.'";
var generalId;
var generalFieldName;
var generalDivId;
var generalLoadIconId;
var generalButtonId;
var generalTemplate;
var generalTextareaId;


function rmUpFileLinkClick(id)
{
	$("#" + generalLoadIconId).attr("src","admin/'.$loadicon[1].'");
	generalId = id;
	$("#" + generalIdPart + generalId).attr("value","");
	var FileName = $("#rmUpFile" + id).attr("href");
	$.get("ajax.php",
	{
		module: "files",
		action: "drop",
		path: "'.$path.'",
		file: FileName
		'.($ibid ? ',ibid: "'.$ibid.'"' : '')."\r\n".($year ? ',year: "'.$year.'"' : '')."\r\n".($month ? ',month: "'.$month.'"' : '')."\r\n".($id ? ',id: "'.$id.'"' : '').'
	},
	onRmAjaxSuccess
	);
		
	'.(mb_strstr(getenv("HTTP_USER_AGENT"),"MSIE") ? '$("#" + generalIdPart + generalId).attr("value","");
		'.($show_input ? '$("#fileinp" + generalId).slideDown("fast");' : '').'
		$("#" + "opt" + generalId).slideUp("fast");
		$("#" + "display" + generalId).slideUp("fast");
		$("#" + generalLoadIconId).attr("src","admin/'.$loadicon[0].'");' : '').'
		
	return false;
}

function onRmAjaxSuccess(data)
{
	if(data == "success")
	{
		$("#" + generalIdPart + generalId).attr("value","");
		'.($show_input ? '$("#fileinp" + generalId).slideDown("fast");' : '').'
		$("#" + "opt" + generalId).slideUp("fast");
		$("#" + "display" + generalId).slideUp("fast");
		$("#" + generalLoadIconId).attr("src","admin/'.$loadicon[0].'");
	}
	else
	{
		alert(data);
	}
}');

if($multi)
{
	print('
function onGetImgDispPartSuccess(data)
{
	$("#" + generalDivId).append(data.display);
	'.($show_input ? '$("#" + "fileinp" + data.id).slideUp("fast");' : '').'
	$("#" + "opt" + data.id).slideDown("fast");
	$("#" + "display" + data.id).slideDown("fast");
	$("#" + generalIdPart + data.id).attr("value",data.fnamenp);
	$("#rmUpFile" + data.id).attr("href",data.fnamenp);
	//alert($("#" + generalFieldName).attr("innerHTML"));
	//$("#" + generalFieldName).html(\'<input type="file" value="" name="\' + generalFieldName + \'" id="\' + generalFieldName + \'" style="width: 180px;">\');
}');
}

print('
function uplButtonClick()
{
	$("#" + generalLoadIconId).attr("src","admin/'.$loadicon[1].'");
	$("#" + generalButtonId).attr("value","'.__('Uploading...').'");
}

function ajaxFileUpload(FieldName, DivId, LoadIconId, ButtonId, TextareaId, IdPart, Template, ThumbsSize)
{
	if(FieldName == undefined)
	{
		generalFieldName = "upFileField";
	}
	else
	{
		generalFieldName = FieldName;
	}
	if(DivId == undefined)
	{
		generalDivId = "intimages";
	}
	else
	{
		generalDivId = DivId;
	}
	if(LoadIconId == undefined)
	{
		generalLoadIconId = "loadicon";
	}
	else
	{
		generalLoadIconId = LoadIconId;
	}
	if(ButtonId == undefined)
	{
		generalButtonId = "uplButton";
	}
	else
	{
		generalButtonId = ButtonId;
	}
	if(TextareaId == undefined)
	{
		generalTextareaId = "description";
	}
	else
	{
		generalTextareaId = TextareaId;
	}
	if(IdPart == undefined)
	{
		generalIdPart = "'.$idpart.'";
	}
	else
	{
		generalIdPart = IdPart;
	}
	if(Template == undefined)
	{
		generalTemplate = "ibimage";
	}
	else
	{
		generalTemplate = Template;
	}
	
	if($("#" + generalFieldName).attr("value") == undefined)
	{
		alert("'.__('Please choose file to upload!').'");
		return false;
	}
	
	//alert("#" + generalIdPart + "_rwidth - " + $("#" + generalIdPart + "_rwidth").attr("value"));
	//alert("#" + generalIdPart + "_rheight - " + $("#" + generalIdPart + "_rheight").attr("value"));
	
	var mresize = "";
	');

	if(!$resize)
	{
		print('
	var resizeW = $("#" + generalIdPart + "_rwidth").attr("value");
	var resizeH = "0";
	if($("#" + generalIdPart + "_rheight").attr("value") != "auto")
	{
		resizeH = $("#" + generalIdPart + "_rheight").attr("value");
	}
	if(resizeW != "" && resizeH != "" && resizeW != undefined && resizeH != undefined)
	{
		mresize = "&resize=" + resizeW + "x" + resizeH;
	}
	//alert(mresize);
	$("#" + generalIdPart + "_rwidth").attr("value","");
	// HZ
	');
	}
	
	print('	
	uplButtonClick();
	
	$("#" + generalButtonId)
	.ajaxComplete(function(){
		$("#" + generalLoadIconId).attr("src","admin/'.$loadicon[0].'");
		$(this).attr("value","'.__('Upload').'");		
	});

	$.ajaxFileUpload
	(
	{
		url:\'ajax.php?module=files&action=upload&field=\' + generalFieldName + \'&idpart=\' + generalIdPart + \'&path='.$path.($ibid ? '&ibid='.$ibid : '').($year ? '&year='.$year : '').($month ? '&month='.$month : '').($id ? '&id='.$id : '').($resize ? '&resize='.$resize : '\' + mresize + \'').(mb_strstr(getenv("HTTP_USER_AGENT"),"MSIE") ? '&thumbs='.(isset($system->config['th_width']) ? $system->config['th_width'] : 100).'x'.(isset($system->config['th_height']) ? $system->config['th_height'] : 100) : '').($wm ? '&wm&wmpos=\' + $("#" + generalIdPart + "_wmpos").attr("value") + \'' : '').'\',
		secureuri:false,
		fileElementId: generalFieldName,
		dataType: \'json\',
		success: function (data, status)
		{
			if(typeof(data.error) != \'undefined\')
			{
				if(data.error != \'\')
				{
					alert(data.error);
				}
				else
				{
				');
				
		if($multi)
		{
			print('
			$.getJSON("ajax.php",
			{
				module: "files",
				action: "formfdpart",
				idpart: generalIdPart,
				tpl: 	generalTemplate,
				'.($dwidth ? 'dwidth:	"'.$dwidth.'",' : '').'
				file:	data.fnamenp,
				taid:	generalTextareaId,
				path:	"'.$path.'"
				'.($ibid ? ',ibid: "'.$ibid.'"' : '')."\r\n".($year ? ',year: "'.$year.'"' : '')."\r\n".($month ? ',month: "'.$month.'"' : '')."\r\n".($id ? ',id: "'.$id.'"' : '').'
			},
			onGetImgDispPartSuccess
			);
			');
		}
		else
		{
			print('
			$("#" + "display").html(\'<img '.($dwidth ? 'width="'.$dwidth.'"' : '').' src="\' + data.fname + \'">\');
			'.($show_input ? '$("#fileinp" + generalId).slideUp("fast");' : '').'
			$("#" + "opt").slideDown("fast");
			$("#" + "display").slideDown("fast");
			$("#" + generalIdPart).attr("value",data.fnamenp);
			$("#rmUpFile").attr("href",data.fnamenp);
			');
		}
					
		
		print('		}
			}
		},
		error: function (data, status, e)
		{
			alert(e);
		}
	}
	)
	return false;
}
</script>');
	}

	/**
 	* Prints JavaScript code for single image upload
 	*
 	* @param mixed $path
 	* @param string $idpart
 	* @param boolean $multi
 	* @param boolean $showhide_input
 	* @param array $loadicon
 	* @param boolean $wm
 	*/
	function printFileUpFormJs($path, $idpart = 'file', $multi = false, $showhide_input = true, $loadicon = array('load_stopped_3.gif', 'load_process_3.gif'), $wm=false)
	{
		/*
		
		*/
		
		if(is_array($path))
		{
			$parts = $path;
			$path = $parts[0];
			$ibid = $parts[1];
			$year = $parts[2];
			$month = $parts[3];
			$id = $parts[4];			
		}
		else 
		{
			$ibid = false;
			$year = false;
			$month = false;
			$id = false;
		}
		
		print('<script type="text/javascript">
var generalServId_f;
var generalId_f;
var generalIdPart_f;
var generalFieldName_f;
var generalDivId_f;
var generalLoadIconId_f;
var generalButtonId_f;
var generalTemplate_f;

function rmUpFileLinkClick_f(id)
{
	$("#" + generalLoadIconId_f).attr("src","admin/'.$loadicon[1].'");
	generalId_f = id;
	var FileName = $("#rmUpFile_f" + id).attr("href");
	$.get("ajax.php",
	{
		module: "files",
		action: "drop",
		path: "'.$path.'",
		file: FileName
		'.($ibid ? ',ibid: "'.$ibid.'"' : '')."\r\n".($year ? ',year: "'.$year.'"' : '')."\r\n".($month ? ',month: "'.$month.'"' : '')."\r\n".($id ? ',id: "'.$id.'"' : '').'
	},
	onRmAjaxSuccess_f
	);
		
	return false;
}

function onRmAjaxSuccess_f(data)
{
	if(data == "success")
	{
		$("#"+ generalIdPart_f + generalId_f).attr("value","");
		'.($showhide_input ? '$("#fileinp" + generalId_f).slideDown("fast");' : '').'
		$("#opt" + generalId_f).slideUp("fast");
		$("#display" + generalId_f).slideUp("fast");
		$("#" + generalLoadIconId_f).attr("src","admin/'.$loadicon[0].'");
	}
	else
	{
		alert(data);
	}
}');

if($multi)
{
	print('
function onGetFileDispPartSuccess_f(data)
{
	generalServId_f = data.id;
	$("#" + generalDivId_f).append(data.display);
	'.($showhide_input ? '$("#fileinp" + data.id).slideUp("fast");' : '').'
	$("#opt" + data.id).slideDown("fast");
	$("#display" + data.id).slideDown("fast");
	$("#" + generalIdPart_f + data.id).attr("value",data.fnamenp);
	$("#rmUpFile_f" + data.id).attr("href",data.fnamenp);
}');
}

print('
function uplButtonClick_f()
{
	$("#" + generalLoadIconId_f).attr("src","admin/'.$loadicon[1].'");
	$("#" + generalButtonId_f).attr("value","'.__('Uploading...').'");
}
		
function ajaxFileUpload_f(FieldName, DivId, LoadIconId, ButtonId, IdPart, Template, ThumbsSize)
{
	if(FieldName == undefined)
	{
		generalFieldName_f = "upFileField_f";
	}
	else
	{
		generalFieldName_f = FieldName;
	}
	if(DivId == undefined)
	{
		generalDivId_f = "intfiles";
	}
	else
	{
		generalDivId_f = DivId;
	}
	if(LoadIconId == undefined)
	{
		generalLoadIconId_f = "loadicon_f";
	}
	else
	{
		generalLoadIconId_f = LoadIconId;
	}
	if(ButtonId == undefined)
	{
		generalButtonId_f = "uplButton_f";
	}
	else
	{
		generalButtonId_f = ButtonId;
	}
	if(IdPart == undefined)
	{
		generalIdPart_f = "'.$idpart.'";
	}
	else
	{
		generalIdPart_f = IdPart;
	}
	if(Template == undefined)
	{
		generalTemplate_f = "ibfile";
	}
	else
	{
		generalTemplate_f = Template;
	}
	if(ThumbsSize == undefined)
	{
		generalThumbsSize_f = "";
	}
	else
	{
		generalThumbsSize_f = ThumbsSize;
	}
	
	if($("#" + generalFieldName_f).attr("value") == undefined)
	{
		alert("'.__('Please choose file to upload!').'");
		return false;
	}
	
	uplButtonClick_f();
	
	$("#" + generalButtonId_f)
	.ajaxComplete(function(){
		$("#" + generalLoadIconId_f).attr("src","admin/'.$loadicon[0].'");
		$(this).attr("value","'.__('Upload').'");		
	});

	$.ajaxFileUpload
	(
	{
		url:\'ajax.php?module=files&action=upload&field=\' + generalFieldName_f + \'&idpart=\' + generalIdPart_f + \'&mime&path='.$path.($ibid ? '&ibid='.$ibid : '').($year ? '&year='.$year : '').($month ? '&month='.$month : '').($id ? '&id='.$id : '').($wm ? '&wm' : '').'&thumbs=\' + generalThumbsSize_f + \''.'\',
		secureuri:false,
		fileElementId: generalFieldName_f,
		dataType: \'json\',
		success: function (data, status)
		{
			if(typeof(data.error) != \'undefined\')
			{
				if(data.error != \'\')
				{
					alert(data.error);
				}
				else
				{');
				
		if($multi)
		{
			print('
			$.getJSON("ajax.php",
			{
				module: "files",
				action:	"formfdpart",
				idpart: generalIdPart_f,
				tpl: 	generalTemplate_f,
				file:	data.fnamenp,
				type:	data.type,
				path:	"'.$path.'"
				'.($ibid ? ',ibid: "'.$ibid.'"' : '')."\r\n".($year ? ',year: "'.$year.'"' : '')."\r\n".($month ? ',month: "'.$month.'"' : '')."\r\n".($id ? ',id: "'.$id.'"' : '').'
			},
			onGetFileDispPartSuccess_f
			);
			');
		}
		else
		{
			// file display fix			
			print('
			$("#display").html(\''.__('Attached file').': <a href="\' + data.fname + \'" title="\' + data.fnamenp + \'">\' + data.fnamenp + \'</a>\');
			'.($showhide_input ? '$("#fileinp").slideUp("fast");' : '').'
			$("#opt").slideDown("fast");
			$("#display").slideDown("fast");
			$("#" + generalIdPart_f).attr("value",data.fnamenp);
			$("#rmUpFile_f").attr("href",data.fnamenp);
			');
		}
					
		
		print('		}
			}
		},
		error: function (data, status, e)
		{
			alert(e);
		}
	}
	)
	return false;
}
</script>');
	}
	
	/**
	 * Adds file upload row to the form
	 *
	 * @param string $desc
	 * @param InputForm $frm
	 * @param string $idpart
	 * @param string $name
	 * @param string $loadicon
	 */
	function addAddPart($desc, $frm, $idpart, $name, $loadicon = 'load_stopped.gif')
	{
		//$id = rcms_random_string(3);
		$frm->addrow($desc, '<div id="fileinp">'.$frm->file('upFileField','id="upFileField"').' '.$frm->button(__('Upload'),'id="uplButton" onclick="return ajaxFileUpload();"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/'.$loadicon.'" id="loadicon"></div><div id="display" style="display:none;"></div><div id="opt" style="display:none;"><a href="#" id="rmUpFile" onclick="return rmUpFileLinkClick(\'\');">'.__('Delete').'</a><input type="hidden" name="'.$name.'" id="'.$idpart.'"></div>');
	}
	
	/**
	 * Returns file display div
	 *
	 * @param string $id
	 * @param string $idpart
	 * @param string $tpl
	 * @param string $dvalue
	 * @param string $file
	 * @param string $att
	 * @param string $style
	 * @return string
	 */
	function addDispPart($id, $idpart, $tpl, $dvalue, $file, $att = '', $style='')
	{		
		return '<div id="display'.$id.'" style="'.$style.'">'.str_replace('{dvalue}', $dvalue, $tpl).'</div><div id="opt'.$id.'" style="'.$style.'"><a href="'.$file.'" id="rmUpFile'.$att.$id.'" onclick="return rmUpFileLinkClick'.$att.'(\''.$id.'\');">'.__('Delete').'</a><input type="hidden" value="'.$file.'" name="'.$idpart.'['.$id.']" id="'.$idpart.$id.'"></div>';
	}
	
	/**
	 * Adds file edit row to the form
	 *
	 * @param string $desc
	 * @param InputForm $frm
	 * @param string $idpart
	 * @param string $tpl
	 * @param string $dvalue
	 * @param string $hvalue
	 * @param string $name
	 * @param string $loadicon
	 */
	function addEditPart($desc, $frm, $idpart, $tpl, $dvalue, $hvalue, $name, $loadicon = 'load_stopped.gif')
	{
		$frm->addrow($desc,'<div id="fileinp" style="display:none;">'.$frm->file('upFileField','id="upFileField"').' '.$frm->button(__('Upload'),'id="uplButton" onclick="return ajaxFileUpload();"').'<img style="margin-bottom: -4px; margin-left: 2px;" src="admin/'.$loadicon.'" id="loadicon"></div><div id="display">'.str_replace('{dvalue}', $dvalue, $tpl).'</div><div id="opt"><a href="'.$hvalue.'" id="rmUpFile" onclick="return rmUpFileLinkClick(\'\');">'.__('Delete').'</a><input type="hidden" value="'.$hvalue.'" name="'.$name.'" id="'.$idpart.'"></div>');
	}
}
?>