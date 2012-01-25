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
 * @version $Id: backup.php 736 2009-10-24 00:11:09Z christian_boltz $ 
 * @license GNU GPL v2 or later. 
 * 
 * File: backup.php
 * Used to save all settings - but only works for MySQL databases.
 * Template File: -none-
 *
 * Template Variables: -none-
 *
 * Form POST \ GET Variables: -none-
 */

require_once('common.php');

authentication_require_role('global-admin');

(($CONF['backup'] == 'NO') ? header("Location: " . $CONF['postfix_admin_url'] . "/main.php") && exit : '1');

// TODO: make backup supported for postgres
if ('pgsql'==$CONF['database_type'])
{
    print '<p>Sorry: Backup is currently not supported for your DBMS.</p>';
}
/*
	SELECT attnum,attname,typname,atttypmod-4,attnotnull,atthasdef,adsrc
	AS def FROM pg_attribute,pg_class,pg_type,pg_attrdef
	WHERE pg_class.oid=attrelid AND pg_type.oid=atttypid
	AND attnum>0 AND pg_class.oid=adrelid AND adnum=attnum AND atthasdef='t' AND lower(relname)='admin'
	UNION SELECT attnum,attname,typname,atttypmod-4,attnotnull,atthasdef,''
	AS def FROM pg_attribute,pg_class,pg_type
	WHERE pg_class.oid=attrelid
	AND pg_type.oid=atttypid
	AND attnum>0
	AND atthasdef='f'
	AND lower(relname)='admin'
$db = $_GET['db'];
$cmd = "pg_dump -c -D -f /tix/miner/miner.sql -F p -N -U postgres $db";
$res = `$cmd`;
// Alternate: $res = shell_exec($cmd);
echo $res; 
*/

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   umask (077);
   $path = (ini_get('upload_tmp_dir') != '') ? ini_get('upload_tmp_dir') : '/tmp';
   $filename = "postfixadmin-" . date ("Ymd") . "-" . getmypid() . ".sql";
   $backup = $path . DIRECTORY_SEPARATOR . $filename;

   $header = "#\n# Postfix Admin $version\n# Date: " . date ("D M j G:i:s T Y") . "\n#\n";

   if (!$fh = fopen ($backup, 'w'))
   {
      $tMessage = "<div class=\"error_msg\">Cannot open file ($backup)</div>";
      include ("templates/header.php");
      include ("templates/menu.php");
      include ("templates/message.php");
      include ("templates/footer.php");
   } 
   else
   {
      fwrite ($fh, $header);
      
      $tables = array(
         'admin',
         'alias',
         'alias_domain',
         'config',
         'domain',
         'domain_admins',
         'fetchmail',
         'log',
         'mailbox',
         'quota',
         'quota2',
         'vacation',
         'vacation_notification'
      );

      for ($i = 0 ; $i < sizeof ($tables) ; ++$i)
      {
         $result = db_query ("SHOW CREATE TABLE " . table_by_key($tables[$i]));
         if ($result['rows'] > 0)
         {
            while ($row = db_array ($result['result']))
            {
               fwrite ($fh, "$row[1];\n\n");
            }
         }
      }   

      for ($i = 0 ; $i < sizeof ($tables) ; ++$i)
      {
         $result = db_query ("SELECT * FROM " . table_by_key($tables[$i]));
         if ($result['rows'] > 0)
         {
            while ($row = db_assoc ($result['result']))
            {
               foreach ($row as $key=>$val)
               {
                  $fields[] = $key;
                  $values[] = $val;
               }

               fwrite ($fh, "INSERT INTO ". $tables[$i] . " (". implode (',',$fields) . ") VALUES ('" . implode ('\',\'',$values) . "');\n");
               $fields = "";
               $values = "";
            }
         }
      }
   }
   header ("Content-Type: text/plain");
   header ("Content-Disposition: attachment; filename=\"$filename\"");
   header ("Content-Transfer-Encoding: binary");
   header ("Content-Length: " . filesize("$backup"));
   header ("Content-Description: Postfix Admin");
   $download_backup = fopen ("$backup", "r");
   unlink ("$backup");
   fpassthru ($download_backup);
}
/* vim: set expandtab softtabstop=3 tabstop=3 shiftwidth=3: */
?>
