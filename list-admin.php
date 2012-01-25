<?php
/** 
 * Postfix Admin 
 * 
 * LICENSE 
 * This source file is subject to the GPL license that is bundled with  
 * this package in the file LICENSE.TXT. 
 * 
 * Further details on the project are available at : 
 *     http://www.postfixadmin.com or http://postfixadmin.sf.net 
 * 
 * @version $Id: list-admin.php 566 2009-02-15 15:02:26Z christian_boltz $ 
 * @license GNU GPL v2 or later. 
 * 
 * File: list-admin.php
 * Lists all administrators
 * Template File: list-admin.php
 *
 * Template Variables: -none-
 *
 * Form POST \ GET Variables: -none-
 */

require_once("common.php");

authentication_require_role('global-admin');

$list_admins = list_admins();
if ((is_array ($list_admins) and sizeof ($list_admins) > 0)) {
    for ($i = 0; $i < sizeof ($list_admins); $i++) {
        $admin_properties[$i] = get_admin_properties ($list_admins[$i]);
    }
}

include ("templates/header.php");
include ("templates/menu.php");
include ("templates/admin_list-admin.php");
include ("templates/footer.php");

/* vim: set expandtab softtabstop=4 tabstop=4 shiftwidth=4: */
?>
