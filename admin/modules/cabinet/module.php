<?php
// //////////////////////////////////////////////////////////////////////////////
// Copyright (C) ReloadCMS Development Team //
// http://reloadcms.sf.net //
// //
// This program is distributed in the hope that it will be useful, //
// but WITHOUT ANY WARRANTY, without even the implied warranty of //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. //
// //
// This product released under GNU General Public License v2 //
// //////////////////////////////////////////////////////////////////////////////
$MODULES [$category] [0] = __ ( 'cabinet' );
if ($system->checkForRight ( '-any-' )) {	
    //$MODULES [$category][1] ['letters'] = '<input type="hidden" value="'.__ ( 'letters' ).'" />';
    $MODULES [$category][1] ['index'] = __ ( 'Write letter' );
    $MODULES [$category][1] [''] = '<span id="int_mail">'.__('Internal mail').'</span>';
    $MODULES [$category][1] ['input'] = '<span class="int_mail_sub">&bull; '.__ ( 'Input' ).'</span>';
    $MODULES [$category][1] ['output'] = '<span class="int_mail_sub">&bull; '.__ ( 'Output' ).'</span>';
    $MODULES [$category][1] ['blacklists'] = '<span class="int_mail_sub">&bull; '.__ ( 'Blacklists' ).'</span>';
    $MODULES [$category][1] ['basket'] = '<span class="int_mail_sub">&bull; '.__ ( 'Basket' ).'</span>';
            
    $MODULES [$category][1] ['contacts'] = __ ( 'contacts' );
    $MODULES [$category][1] ['files'] = __ ( 'files' );
}
?>