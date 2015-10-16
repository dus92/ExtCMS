<?php
if(empty($_REQUEST['path'])) $_REQUEST['path'] = realpath('.');
$_REQUEST['path'] = str_replace('\\', '/', $_REQUEST['path']) . '/';
$url_inc = '?show=module&id=' . implode('.', $module);

if(!empty($_REQUEST['edit'])){
	if(!empty($_REQUEST['save'])) {
	    file_write_contents($_REQUEST['path'] . $_REQUEST['edit'], $_REQUEST['newcontent']);
	}
    $oldcontent = file_get_contents($_REQUEST['path'] . $_REQUEST['edit']);
    $editlink = $url_inc . '&edit=' . $_REQUEST['edit'] . '&path=' . $_REQUEST['path'];
    $backlink = $url_inc . '&path=' . $_REQUEST['path'];
?>
<table border='0' cellspacing='0' cellpadding='1' width="100%">
<tr>
    <th><?=__('File manager') . ' - ' .  __('Edit') . ' - ' . $_REQUEST['edit']?></th>
</tr>
<tr>
    <td class="row1">
        <a href="<?=$backlink?>"><?=__('Back')?></a>
	</td>
</tr>
<tr>
    <td class="row1" align="center">
        <form name="form1" method="post" action="<?=$editlink?>">
            <textarea name="newcontent" cols="45" rows="15" style="width: 100%"><?=hcms_htmlsecure($oldcontent)?></textarea>
            <input type="submit" name="save" value="<?=__('Submit')?>">
            <input type="submit" name="cancel" value="<?=__('Cancel')?>">
        </form>
    </td>
</tr>
</table>
<?php
} elseif(!empty($_REQUEST['rights'])){
	if(!empty($_REQUEST['save'])) {
	    rcms_chmod($_REQUEST['path'] . $_REQUEST['rights'], convert_rights_string($_REQUEST['rights_val']), @$_REQUEST['recursively']);
	}
	clearstatcache();
    $oldrights = get_rights_string($_REQUEST['path'] . $_REQUEST['rights'], true);
    $link = $url_inc . '&rights=' . $_REQUEST['rights'] . '&path=' . $_REQUEST['path'];
    $backlink = $url_inc . '&path=' . $_REQUEST['path'];
?>
<table border='0' cellspacing='0' cellpadding='1' width="100%">
<tr>
    <th><?=__('File manager') . ' - ' .  __('Rights') . ' - ' . $_REQUEST['rights']?></th>
</tr>
<tr>
    <td class="row1">
        <a href="<?=$backlink?>"><?=__('Back')?></a>
	</td>
</tr>
<tr>
    <td class="row1" align="center">
        <form name="form1" method="post" action="<?=$link?>">
            <input type="text" name="rights_val" value="<?=$oldrights?>"><br>
        <?php if (is_dir($_REQUEST['path'] . $_REQUEST['rights'])) {?>
            <input type="checkbox" name="recursively" value="1"> <?=__('Recursively')?><br>
        <?php } ?>
            <input type="submit" name="save" value="<?=__('Submit')?>">
        </form>
    </td>
</tr>
</table>
<?php
} else {
    $msg = '';
    if(!empty($_FILES['upload'])) {
        if(!empty($_FILES['upload']['name'])){
            $_FILES['upload']['name'] = str_replace('%', '', $_FILES['upload']['name']);
            if(!move_uploaded_file($_FILES['upload']['tmp_name'], $_REQUEST['path'] . $_FILES['upload']['name'])){
                $msg = __('Error occurred');
            }
        }
    } elseif(!empty($_REQUEST['delete'])) {
        if(!rcms_delete_files(($_REQUEST['path'] . $_REQUEST['delete']), true)) {
            $msg = __('Error occurred');
        }
	} elseif(!empty($_REQUEST['mkdir'])) {
        if(!rcms_mkdir($_REQUEST['path'] . $_REQUEST['dirname'])) {
            $msg = __('Error occurred');
        }
    }
?>
<table border='0' cellspacing='0' cellpadding='1' width="100%">
<tr>
    <th colspan="2"><?=__('File manager')?></th>
</tr>
<tr>
    <td class="row2">
        <form name="form1" method="post" action="<?=$url_inc?>">
        <input type="hidden" name="path" value="<?=$_REQUEST['path']?>">
        <input type="text" name="dirname" size="15">
        <input type="submit" name="mkdir" value="<?=__('Make directory')?>">
        </form>
    </td>
    <td class="row3">
        <form name="form1" method="post" action="<?=$url_inc?>" enctype="multipart/form-data">
        <input type="hidden" name="path" value="<?=$_REQUEST['path']?>">
        <input type="file" name="upload">
        <input type="submit" name="test" value="<?=__('Upload')?>">
        </form>
    </td>
</tr>
<tr>
    <td class="row3" colspan="2"><?=$msg?></td>
</tr>
</table>
<table border='0' cellspacing='1' cellpadding='1' width="100%">
<tr> 
    <th width="100%"><?=__('Filename')?></th>
    <th nowrap="nowrap"><?=__('Size of file')?></th>
    <th nowrap="nowrap"><?=__('Rights')?></th>
    <th colspan="3"></th>
</tr>
<?php
$elements = rcms_scandir($_REQUEST['path'], '', 'all', true);
$dirs = array();
$files = array();
foreach ($elements as $file){
    if(is_dir($_REQUEST['path'] . $file)){
        $dirs[] = $file;
    } else {
        $files[] = $file;
    }
}
natsort($dirs); natsort($files);
$elements = array_merge($dirs, $files);

foreach ($elements as $file){
    $filedata = stat($_REQUEST['path'] . $file);
    if(is_dir($_REQUEST['path'] . $file)){
        $link = $url_inc . '&path=' . realpath($_REQUEST['path'] . $file);
        $target = '';
        $style = 'row2';
    } else {
        $link = '';
        $target = '';
        $style = 'row1';
    }

    $edittext = '';
    if(!is_dir($_REQUEST['path'] . $file)){
        $dotpos = mb_strrpos($file, '.');
        $editlink = $url_inc . '&edit=' . $file . '&path=' . $_REQUEST['path'];
        $edittext = '<a href="' . $editlink . '">' . __('Edit') . '</a>';
    }
    
    if(!is_dir($_REQUEST['path'] . $file)){
        $alert = 'onClick="if(confirm(\'' . __('Are you sure you want to delete this file?') . '\')) document.location.href = \'' . $url_inc . '&delete=' . $file . '&path=' . $_REQUEST['path']  . '\'"';
    } else {
        $alert = 'onClick="if(confirm(\'' . __('Are you sure you want to delete this directory (recursively)?') . '\')) document.location.href = \'' . $url_inc . '&delete=' . $file . '&path=' . $_REQUEST['path']  . '\'"';
    }
    $deletelink = '<a href="#" ' . $alert . '>' . __('Delete') . '</a>';
    
    $rightstext = '<a href="' . $url_inc . '&rights=' . $file . '&path=' . $_REQUEST['path'] . '">' . get_rights_string($_REQUEST['path'] . $file) . '</a>';
?>
<tr> 
    <td class="<?=$style?>"><a href="<?=$link?>"><?=$file?></a></td>
    <td class="<?=$style?>"><?=$filedata[7]?></td>
    <td class="<?=$style?>"><?=$rightstext?></td>
    <td class="<?=$style?>"><?=$edittext?></td>
    <td class="<?=$style?>"><?=$deletelink?></td>
    <td class="<?=$style?>"></td>
</tr>
<?php
    }
}
?>
</table>