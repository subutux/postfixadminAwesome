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
 * @version $Id: password.php 837 2010-06-22 22:14:03Z christian_boltz $ 
 * @license GNU GPL v2 or later. 
 * 
 * File: password.php
 * Used by users to change their mailbox (and login) password.
 * Template File: users_password.php
 *
 * Template Variables:
 *
 * tMessage
 *
 * Form POST \ GET Variables:
 *
 * fPassword_current
 * fPassword
 * fPassword2
 */

require_once('../common.php');

authentication_require_role('user');
$username = authentication_get_username();

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
    if(isset($_POST['fCancel'])) {
        header("Location: main.php");
        exit(0);
    }

    $fPassword_current = $_POST['fPassword_current'];
    $fPassword = $_POST['fPassword'];
    $fPassword2 = $_POST['fPassword2'];

    $error = 0;
    if(strlen($fPassword) < $CONF['min_password_length']) {
        $error += 1;
        flash_error(sprintf($PALANG['pPasswordTooShort'], $CONF['min_password_length']));
    }
    if(!UserHandler::login($username, $fPassword_current)) {
        $error += 1;
        $pPassword_password_current_text = $PALANG['pPassword_password_current_text_error'];
    }
    if (empty ($fPassword) or ($fPassword != $fPassword2))
    {
        $error += 1;
        $pPassword_password_text = $PALANG['pPassword_password_text_error'];
    }

    if ($error == 0)
    {
        $uh = new UserHandler($username);
        if($uh->change_pass($fPassword_current, $fPassword)) {
            flash_info($PALANG['pPassword_result_success']);
            header("Location: main.php");
            exit(0);
        }
        else
        {
            $tMessage = $PALANG['pPassword_result_error'];
        }
    }
}

include ("../templates/header.php");
include ("../templates/users_menu.php");
include ("../templates/users_password.php");
include ("../templates/footer.php");

/* vim: set expandtab softtabstop=4 tabstop=4 shiftwidth=4: */
?>
