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
 * @version $Id: edit-vacation.php 987 2011-03-06 21:30:55Z christian_boltz $ 
 * @license GNU GPL v2 or later. 
 * 
 * File: edit-vacation.php 
 * Responsible for allowing users to update their vacation status.
 *
 * Template File: edit-vacation.php
 *
 * Template Variables:
 *
 * tUseremail
 * tMessage
 * tSubject
 * tBody
 *
 * Form POST \ GET Variables:
 *
 * fUsername
 * fDomain
 * fCanceltarget
 * fChange
 * fBack
 * fQuota
 * fActive
 */

require_once('common.php');

if($CONF['vacation'] == 'NO') { 
   header("Location: " . $CONF['postfix_admin_url'] . "/list-virtual.php");
   exit(0);
}

$SESSID_USERNAME = authentication_get_username();
$tmp = preg_split ('/@/', $SESSID_USERNAME);
$USERID_DOMAIN = $tmp[1];

// only allow admins to change someone else's 'stuff'
if(authentication_has_role('admin')) {
   if (isset($_GET['username'])) $fUsername = escape_string ($_GET['username']);
   if (isset($_GET['domain'])) $fDomain = escape_string ($_GET['domain']);
}
else {
   $fUsername = $SESSID_USERNAME;
   $fDomain = $USERID_DOMAIN;
}
list (/*NULL*/, $domain) = explode('@', $fUsername);

$vacation_domain = $CONF['vacation_domain'];
$vacation_goto = preg_replace('/@/', '#', $fUsername);
$vacation_goto = $vacation_goto . '@' . $vacation_domain;

$fCanceltarget = $CONF['postfix_admin_url'] . "/list-virtual.php?domain=$fDomain";

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

   $result = db_query("SELECT * FROM $table_vacation WHERE email='$fUsername'");
   if ($result['rows'] == 1)
   {
      $row = db_array($result['result']);
      $tMessage = '';
      $tSubject = $row['subject'];
      $tBody = $row['body'];
   }

   $tUseremail = $fUsername;
   $tDomain = $fDomain;
   if ($tSubject == '') { $tSubject = html_entity_decode($PALANG['pUsersVacation_subject_text'], ENT_QUOTES, 'UTF-8'); }
   if ($tBody == '') { $tBody = html_entity_decode($PALANG['pUsersVacation_body_text'], ENT_QUOTES, 'UTF-8'); }

}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{

   $tSubject   = safepost('fSubject');
   $fSubject   = escape_string ($tSubject);
   $tBody      = safepost('fBody');
   $fBody      = escape_string ($tBody);
   $fChange    = escape_string (safepost('fChange'));
   $fBack      = escape_string (safepost('fBack'));

   if(authentication_has_role('admin') && isset($_GET['domain'])) {
      $fDomain = escape_string ($_GET['domain']);
   }
   else {
      $fDomain = $USERID_DOMAIN;
   }
   if(authentication_has_role('admin') && isset ($_GET['username'])) {
      $fUsername = escape_string($_GET['username']);
   }
   else {
      $fUsername = authentication_get_username();
   }

   $tUseremail = $fUsername;
   if ($tSubject == '') { $tSubject = html_entity_decode($PALANG['pUsersVacation_subject_text'], ENT_QUOTES, 'UTF-8'); }
   if ($tBody == '') { $tBody = html_entity_decode($PALANG['pUsersVacation_body_text'], ENT_QUOTES, 'UTF-8'); }

   //if change, remove old one, then perhaps set new one
   if (!empty ($fBack) || !empty ($fChange))
   {
      //if we find an existing vacation entry, disable it
      $result = db_query("SELECT * FROM $table_vacation WHERE email='$fUsername'");
      if ($result['rows'] == 1)
      {
         $db_false = db_get_boolean(false);
         // retain vacation message if possible - i.e disable vacation away-ness.
         $result = db_query ("UPDATE $table_vacation SET active = '$db_false' WHERE email='$fUsername'");
         $result = db_query("DELETE FROM $table_vacation_notification WHERE on_vacation='$fUsername'");

         $result = db_query ("SELECT * FROM $table_alias WHERE address='$fUsername'");
         if ($result['rows'] == 1)
         {
            $row = db_array ($result['result']);
            $goto = $row['goto'];
            //only one of these will do something, first handles address at beginning and middle, second at end
            $goto= preg_replace ( "/$vacation_goto,/", '', $goto);
            $goto= preg_replace ( "/,$vacation_goto/", '', $goto);
            $goto= preg_replace ( "/$vacation_goto/", '', $goto);
            if($goto == '') {
               $sql = "DELETE FROM $table_alias WHERE address = '$fUsername'";
            }
            else {
               $sql = "UPDATE $table_alias SET goto='$goto',modified=NOW() WHERE address='$fUsername'";
            }
            $result = db_query($sql);
            if ($result['rows'] != 1)
            {
               $error = 1;
            }
            db_log($SESSID_USERNAME, $domain, 'edit_alias', "$fUsername -> $goto");
         }
      }
   }


   //Set the vacation data for $fUsername
   if (!empty ($fChange))
   {
      $goto = '';
      $result = db_query ("SELECT * FROM $table_alias WHERE address='$fUsername'");
      if ($result['rows'] == 1)
      {
         $row = db_array ($result['result']);
         $goto = $row['goto'];
      }
      $Active = db_get_boolean(True);
      $notActive = db_get_boolean(False);
      // I don't think we need to care if the vacation entry is inactive or active.. as long as we don't try and
      // insert a duplicate
      $result = db_query("SELECT * FROM $table_vacation WHERE email = '$fUsername'");
      if($result['rows'] == 1) {
          $result = db_query("UPDATE $table_vacation SET active = '$Active', subject = '$fSubject', body = '$fBody', created = NOW() WHERE email = '$fUsername'");
      }
      else {
          $result = db_query ("INSERT INTO $table_vacation (email,subject,body,domain,created,active) VALUES ('$fUsername','$fSubject','$fBody','$fDomain',NOW(),'$Active')");
      }

      if ($result['rows'] != 1)
      {
         $error = 1;
      }
      if($goto == '') {
         $goto = $vacation_goto;
         $sql = "INSERT INTO $table_alias (goto, address, domain, modified) VALUES ('$goto', '$fUsername', '$fDomain', NOW())";
      }
      else {
         $goto = $goto . "," . $vacation_goto;
         $sql = "UPDATE $table_alias SET goto='$goto',modified=NOW() WHERE address='$fUsername'";
      }
      $result = db_query ($sql);
      if ($result['rows'] != 1)
      {
         $error = 1;
      }
      db_log($SESSID_USERNAME, $domain, 'edit_alias', "$fUsername -> $goto");
   }
}

if($error == 0) {
   if(!empty ($fBack)) {
      $tMessage = $PALANG['pVacation_result_removed'];
   }
   if(!empty($fChange)) {
      $tMessage= $PALANG['pVacation_result_added'];   
   }
}
else {
   $tMessage = $PALANG['pVacation_result_error'];
}

include ("templates/header.php");
include ("templates/menu.php");
include ("templates/edit-vacation.php");
include ("templates/footer.php");
/* vim: set expandtab softtabstop=3 tabstop=3 shiftwidth=3: */
?>
