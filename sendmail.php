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
 * @version $Id: sendmail.php 573 2009-03-11 22:38:15Z christian_boltz $ 
 * @license GNU GPL v2 or later. 
 * 
 * File: sendmail.php
 * Used to send an email to a user.
 * Template File: sendmail.php
 *
 * Template Variables:
 *
 * tMessage
 * tFrom
 * tSubject
 * tBody
 *
 * Form POST \ GET Variables:
 *
 * fTo
 * fSubject
 * fBody
 */

require_once('common.php');

authentication_require_role('admin');

(($CONF['sendmail'] == 'NO') ? header("Location: " . $CONF['postfix_admin_url'] . "/main.php") && exit : '1');

$SESSID_USERNAME = authentication_get_username();

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fTo = safepost('fTo');
   $fFrom = $SESSID_USERNAME;
   $fHeaders = "To: " . $fTo . "\n";
   $fHeaders .= "From: " . $fFrom . "\n";

   mb_internal_encoding("UTF-8");
   $fHeaders .= "Subject: " . mb_encode_mimeheader( safepost('fSubject'), 'UTF-8', 'Q') . "\n";
   $fHeaders .= "MIME-Version: 1.0\n";
   $fHeaders .= "Content-Type: text/plain; charset=utf-8\n";
   $fHeaders .= "Content-Transfer-Encoding: 8bit\n";
   $fHeaders .= "\n";

   $tBody = $_POST['fBody'];
   if (get_magic_quotes_gpc ())
   {
      $tBody = stripslashes($tBody);
   }
   $fHeaders .= $tBody;

   if (empty ($fTo) or !check_email ($fTo))
   {
      $error = 1;
      $tTo = escape_string ($_POST['fTo']);
      $tSubject = escape_string ($_POST['fSubject']);
      $tMessage = $PALANG['pSendmail_to_text_error'];
   }

   if ($error != 1)
   {
      if (!smtp_mail ($fTo, $fFrom, $fHeaders))
      {
         $tMessage .= $PALANG['pSendmail_result_error'];
      }
      else
      {
         $tMessage .= $PALANG['pSendmail_result_success'];
      }
   }
}

include ("./templates/header.php");
include ("./templates/menu.php");
include ("./templates/sendmail.php");
include ("./templates/footer.php");

/* vim: set expandtab softtabstop=3 tabstop=3 shiftwidth=3: */
?>
