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
$MODULES [$category] [0] = __ ( 'Contests' );
if ($system->checkForRight ( 'CONTESTS-MANAGER' )) {	    
    $MODULES [$category][1] ['index'] = __ ( 'Manage contests' );
    $MODULES [$category][1] ['settings'] = __ ( 'Settings' );
}
?>