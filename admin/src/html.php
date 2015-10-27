<?php

//header('Content-type: text/plain');

//    var_dump(debug_backtrace());

// It will be called downloaded.pdf
//header('Content-Disposition: attachment; filename="downloaded.pdf"');

if (!class_exists('HtmlParser')){
class HtmlParser{

	var $allow;
	var $simpletag;
	var $skiptags;
	var $AllowProtocols;
	var $ReplaceTagsFrom, $ReplaceTagsTo;
	var $debug;
	
	function HtmlParser(){
		$this->debug=false;
		$this->ClearAllow();
		$this->simpletag=array('img','br','hr','input','param','embed','wbr','newline');
		$this->AllowProtocols=array('http','ftp','skype','about','https');
		$this->ReplaceTagsFrom=array();
		$this->ReplaceTagsTo=array();
		$this->AddReplace('/\<(\/?)b(\s[^\>]*?)?\>/i','<$1strong$2>');
		$this->AddReplace('/<('.implode('|',$this->simpletag).')(\s[^\>]*?)><\/\1>/i','<$1$2 />');
//		$this->AddReplace('/<param(\s[^\>]*>?)><\/param>/i','<param$1>');
		$this->skiptags=array('lj-raw','lj-cut','notag');
		$this->AddReplace('#<([^>]+)$#','');
//		$this->SetAllow($allow);
	}

	function Parse($text){
	
	global $user;
//	if ($user['id']==1)$this->debug=1;
	
	$text=preg_replace($this->ReplaceTagsFrom,$this->ReplaceTagsTo,$text);
	if ($this->debug)
		var_dump($text);
	$newtext='';
				
@preg_match_all('/(.*?)([^\<]*?)\<(\/?)([^\s]+?)((?:(?:\s+(?:[\w]+?)=("|\'|)(?:[^\6]*?)(?<!\\\\)\6)*?)(?:\s*)\/?)\>/sim',str_replace(array("\n","\r"),array('<newline/>',' '),$text.' <notag>'),$textdrop);// .'<br />'
	if ($this->debug)
		var_dump($textdrop);
//	if ($user['id']==1)var_dump($textdrop);
	$tags=array();
//if ($_SERVER['REMOTE_ADDR']=='127.0.0.1')var_dump($text,$textdrop);
	if(count($textdrop[0])==0)
		return htmlspecialchars($text);
	foreach($textdrop[0] as $id=>$line){
		$pretext=$textdrop[1][$id].$textdrop[2][$id];
		$closetag=$textdrop[3][$id]=='/';
		if ($this->debug)
			var_dump(strtolower($textdrop[4][$id]));
		$curtag=strtolower($textdrop[4][$id]);
		$tagparam=$textdrop[5][$id];
		if (in_array($curtag,$this->skiptags)){
			$newtext.=($pretext);
			continue;
		}
		if ($closetag){
			// TagClose
			if ((count($tags)>0)&&($tags[0]==$curtag)){
				$newtext.=($pretext).'</'.$curtag.'>';
				array_shift($tags);
				continue;
			}else{
				$newtext.=($pretext).'&lt;/'.$curtag.'&gt;';
				continue;
}
		}
		elseif (!isset($this->allow[$curtag])){
			$newtext.=($pretext).'&lt;'.($closetag?'/':'').$curtag.htmlspecialchars($tagparam).'&gt;';
		
		}else{	
			// TagOpen
			preg_match_all('/\s?([\w]+?)=(["\'])((?:[^\2]*?)(?<!\\\\))\2/',$tagparam,$params);
	if ($this->debug)
		var_dump('current: '.$curtag,$tagparam,$params);
//die();
    $param='';
//if ($_SERVER['REMOTE_ADDR']=='127.0.0.1')var_dump(array($curtag,$tagparam,$params));
foreach($params[0] as $pid=>$line){
	if ($this->debug)
		var_dump($line);
	if ($this->debug)
		var_dump($this->allow[$curtag],in_array(strtolower($params[1][$pid]),$this->allow[$curtag]));
	
	if (in_array(strtolower($params[1][$pid]),$this->allow[$curtag])){
		if ($params[2][$pid]=='')
			$params[2][$pid]='"';
		if (strtolower($params[1][$pid])=='href'){
			// Провека ссылок

			preg_match('/(?:(\w*?):)(.*)/',$params[3][$pid],$link);
//var_dump($link);
			if ((count($link)==0)||(in_array(strtolower($link[1]),$this->AllowProtocols)))
				$param.= ' '.$params[1][$pid].'='.$params[2][$pid].$params[3][$pid].$params[2][$pid];
		}else
			$param.= ' '.$params[1][$pid].'='.$params[2][$pid].$params[3][$pid].$params[2][$pid];
			
			
	}
}
	if ($this->debug)
		var_dump('current_end: '.$curtag,$param,array_key_exists($this->allow,$curtag));
		if (array_key_exists($curtag,$this->allow)){
				if (!in_array($curtag,$this->simpletag)){
					array_unshift($tags,$curtag);
					$newtext.=($pretext).'<'.$curtag.$param.'>';
				}else{
					$newtext.=($pretext).'<'.$curtag.$param.' />';
				}
				continue;
			}else
			$newtext.=($pretext).'&lt;'.($closetag?'/':'').$curtag.htmlspecialchars($tagparam).'&gt;';
		}
}
	foreach($tags as $tag)
		$newtext.='</'.$tag.'>';
	$newtext=str_replace('<newline />',"\n",$newtext);
	return $newtext;/*.'<!-- 
	'.htmlspecialchars($newtext).'
	
	
	'.htmlspecialchars($text).'
	 -->';*/
	}
	
	function ClearAllow(){
		$this->allow=array();
	}
	
	function InitAllow(){
		$this->SetAllow(array(
	'a'	=> array('href','target','style','class','id'),
	'img'	=> array('src','border','alt','title','style','class','width','height','align','id'),
	'br'	=> array('style','clear','class','id'),
	'hr'	=> array('style','clear','class','id'),
	'span'	=> array('style','class','id'),
	'div'	=> array('style','class','align','id'),
	'p'	=> array('style','title','class','id'),
	'ul'	=> array('style','class','id'),
	'code'	=> array('style','class','id'),
	'li'	=> array('style','class','id'),
	'ol'	=> array('style','class','id'),
	'sup'	=> array('style','class','id'),
	'sub'	=> array('style','class','id'),
	'b'	=> array('style','class','id'),
	'i'	=> array('style','class','id'),
	'u'	=> array('style','class','id'),
	's'	=> array('style','class','id'),
	'strike'	=>	array('class','style','id'),
	'em'	=> array('style','class','id'),
	'strong'	=> array('style','class','id'),
	'center'	=> array('style','class','id'),
	'form'	=>	array('style','class','action','method','target','id'),
	'h1'	=>	array('style','class','id'),
	'h2'	=>	array('style','class','id'),
	'h3'	=>	array('style','class','id'),
	'h4'	=>	array('style','class','id'),
	'h5'	=>	array('style','class','id'),
	'h6'	=>	array('style','class','id'),
	'input'	=>	array('type','value','name','style','class','readonly','disabled','id'),
	'label'	=>	array('style','class','id'),
	'object'	=>	array('classid','codebase','width','height','id'),
	'param'	=>	array('name','value'),
	'embed'	=>	array('type','height','width','wmode','src','flashvars'),
	'table' =>	array('class','style','cellpadding','cellspacing','border','id','bgcolor','width'),
	'tr'	=>	array('class','style','id'),
	'th'	=>	array('class','style','id','colspan'),
	'td'	=>	array('class','style','colspan','bgcolor'),
	'thead'	=>	array('class','style'),
	'tbody'	=>	array('class','style'),
	'tfoot'	=>	array('class','style'),
	'pre'	=>	array('class','style','id'),
	'blockquote'	=>	array('class','style','id'),
	'q'	=>	array('class','style','id'),
	'noindex'	=>	array('class','style','id'),
	'acronym'	=>	array('title','style','class','lang'),
	'dl'	=>	array('class','style','id'),
	'dt'	=>	array('class','style','id'),
	'dd'	=>	array('class','style','id'),
	'tt'	=>	array('class','style','id'),
	'cite'	=>	array('class','style','id'),
	'small'	=>	array('class','style','id'),
	'caption'	=>	array('class','style','id'),
	'font'	=>	array('color','face','size'),
	'newline' => array(),
	'textarea'	=>	array('class','style','id'),
	'iframe' => array('src','width','height')
	));
		//
	}

	function AddReplace($from,$to){
		$this->ReplaceTagsFrom[]	=$from;
		$this->ReplaceTagsTo[]		=$to;
	}

	function SetAllow($array){
		$this->allow=$array;
	}
}
} // if
/*
$html = new HtmlParser(array());
$html->InitAllow();

if (isset($_POST['text'])){
$text=stripslashes($_POST['text']);
$f=fopen('html.log','a');
fwrite($f,$text."\n\r\n\r",strlen($text)+4);
fclose($f);
}else

$text='<p title="<<text>>">Добро пожаловать в наш текст</p><p><a href="/traffic/"><div style="text:align:center;"><img src="/love/army.png"></div></a><br /><a href="javascript:alert(1);" style="text-decoration:overline;">Яваскрипт</a> на <a href="http://blog.lugavchik.ru/">странице</a><sup>привет</sup>';
$newtext=$html->Parse($text);
echo '<html><head><title>Тестирование</title></head><body><form method="post"><textarea style="width:100%;height:200px;" name="text">'.htmlspecialchars($text).'</textarea><input type="submit"></form><div style="width:80%;margin:1% 10%;border:1px solid silver;background-color:#eee;padding:5px;">'.$newtext.'</div><div style="width:80%;margin:1% 10%;border:1px solid silver;background-color:#eee;padding:5px;"><h2>Отображаемый код</h2>'.htmlspecialchars($newtext).'</div><div style="width:80%;margin:1% 10%;border:1px solid silver;background-color:#eee;padding:5px;"><h2>Оригинал</h2>'.$text.'</div></body></html>';
//echo $text;

}
*/

?>