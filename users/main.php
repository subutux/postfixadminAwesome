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
 * @version $Id: main.php 575 2009-03-13 20:48:24Z GingerDog $ 
 * @license GNU GPL v2 or later. 
 * 
 * File: main.php
 * 'Home page' for logged in users.
 * Template File: main.php
 *
 * Template Variables:
 *
 * tummVacationtext
 *
 * Form POST \ GET Variables: -none-
 */

require_once('../common.php');
authentication_require_role('user');
$USERID_USERNAME = authentication_get_username();

$vh = new VacationHandler($USERID_USERNAME);
if($vh->check_vacation()) {
   $tummVacationtext = $PALANG['pUsersMain_vacationSet'];
}
else
{
   $tummVacationtext = $PALANG['pUsersMain_vacation'];
}

include ("../templates/header.php");
include ("../templates/users_menu.php");
include ("../templates/users_main.php");
include ("../templates/footer.php");

/* vim: set expandtab softtabstop=3 tabstop=3 shiftwidth=3: */
?>
