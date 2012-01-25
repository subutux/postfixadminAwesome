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
 * @version $Id: setup.php 969 2011-02-19 20:10:22Z christian_boltz $ 
 * @license GNU GPL v2 or later. 
 * 
 * File: setup.php
 * Used to help ensure a server is setup appropriately during installation/setup.
 * After setup, it should be renamed or removed.
 *
 * Template File: -none-
 *
 * Template Variables: -none-
 *
 * Form POST \ GET Variables: -none-
 */

define('POSTFIXADMIN', 1); # by defining it here, common.php will not start a session.

require_once(dirname(__FILE__).'/common.php'); # make sure correct common.php is used.

$CONF['show_header_text'] = 'NO';
$CONF['theme_logo'] = 'images/logo-default.png';
$CONF['theme_css'] = 'css/default.css';
require($incpath.'/templates/header.php');
?>

<div class='setup'>
<h2>Postfix Admin Setup Checker</h2>

<p>Running software:
<ul>
<?php
//
// Check for availablilty functions
//
$f_phpversion = function_exists ("phpversion");
$f_apache_get_version = function_exists ("apache_get_version");
$f_get_magic_quotes_gpc = function_exists ("get_magic_quotes_gpc");
$f_mysql_connect = function_exists ("mysql_connect");
$f_mysqli_connect = function_exists ("mysqli_connect");
$f_pg_connect = function_exists ("pg_connect");
$f_session_start = function_exists ("session_start");
$f_preg_match = function_exists ("preg_match");
$f_mb_encode_mimeheader = function_exists ("mb_encode_mimeheader");
$f_imap_open = function_exists ("imap_open");

$file_config = file_exists (realpath ("./config.inc.php"));

$error = 0;

//
// Check for PHP version
//
if ($f_phpversion == 1)
{
    if (phpversion() < 5) {
        print "<li><b>Error: Depends on: PHP v5</b><br /></li>\n";
        $error += 1;
    }
    if (phpversion() >= 5) { 
        $phpversion = 5;
        print "<li>PHP version " . phpversion () . "</li>\n";
    }
}
else
{
    print "<li><b>Unable to check for PHP version. (missing function: phpversion())</b></li>\n";
}

//
// Check for Apache version
//
if ($f_apache_get_version == 1)
{
    print "<li>" . apache_get_version() . "</li>\n";
}
else
{
    # not running on Apache.
    # However postfixadmin _is_ running, so obviously we are on a supported webserver ;-))
    # No need to confuse the user with a warning.
}

print "</ul>";
print "<p>Checking for dependencies:\n";
print "<ul>\n";

//
// Check for Magic Quotes
//
if ($f_get_magic_quotes_gpc == 1)
{
    if (get_magic_quotes_gpc () == 0)
    {
        print "<li>Magic Quotes: Disabled - OK</li>\n";
    }
    else
    {
        print "<li><b>Warning: Magic Quotes: ON (internal workaround used)</b></li>\n";   
    }
}
else
{
    print "<li><b>Unable to check for Magic Quotes. (missing function: get_magic_quotes_gpc())</b></li>\n";
}

//
// Check for config.inc.php
//
$config_loaded = 0;
if ($file_config == 1)
{
    print "<li>Depends on: presence config.inc.php - OK</li>\n";
    require_once($incpath.'/config.inc.php');
    $config_loaded = 1;

    require($incpath.'/config.inc.php');
    if(isset($CONF['configured'])) {
        if($CONF['configured'] === TRUE) {
            print "<li>Checking \$CONF['configured'] - OK\n";
        } else {
            print "<li><b>Warning: \$CONF['configured'] is 'false'.<br>\n";
            print "You must edit your config.inc.php and change this to true (this indicates you've created the database and user)</b>\n";
        }
    }
}
else
{
    print "<li><b>Error: Depends on: presence config.inc.php - NOT FOUND</b><br /></li>\n";
    print "Create the file, and edit as appropriate (e.g. select database type etc)<br />";
    print "For example:<br />\n";
    print "<code><pre>cp config.inc.php.sample config.inc.php</pre></code>\n";
    $error =+ 1;
}

//
// Check if there is support for at least 1 database
//
if (($f_mysql_connect == 0) and ($f_mysqli_connect == 0) and ($f_pg_connect == 0))
{
    print "<li><b>Error: There is no database support in your PHP setup</b><br />\n";
    print "To install MySQL 3.23 or 4.0 support on FreeBSD:<br />\n";
    print "<pre>% cd /usr/ports/databases/php$phpversion-mysql/\n";
    print "% make clean install\n";
    print " - or with portupgrade -\n";
    print "% portinstall php$phpversion-mysql</pre>\n";
    if ($phpversion >= 5)
    {
        print "To install MySQL 4.1 support on FreeBSD:<br />\n";
        print "<pre>% cd /usr/ports/databases/php5-mysqli/\n";
        print "% make clean install\n";
        print " - or with portupgrade -\n";
        print "% portinstall php5-mysqli</pre>\n";
    }
    print "To install PostgreSQL support on FreeBSD:<br />\n";
    print "<pre>% cd /usr/ports/databases/php$phpversion-pgsql/\n";
    print "% make clean install\n";
    print " - or with portupgrade -\n";
    print "% portinstall php$phpversion-pgsql</pre></li>\n";
    $error =+ 1;
}
//
// MySQL 3.23, 4.0 functions
//
if ($f_mysql_connect == 1)
{
    print "<li>Depends on: MySQL 3.23, 4.0 - OK</li>\n";
}

//
// MySQL 4.1 functions
//
if ($phpversion >= 5)
{
    if ($f_mysqli_connect == 1)
    {
        print "<li>Depends on: MySQL 4.1 - OK\n";
        if ( !($config_loaded && $CONF['database_type'] == 'mysqli') ) {
            print "(change the database_type to 'mysqli' in config.inc.php!!)\n";
        }
        print "</li>";
    }
}

//
// PostgreSQL functions
//
if ($f_pg_connect == 1)
{
    print "<li>Depends on: PostgreSQL - OK \n";
    if ( !($config_loaded && $CONF['database_type'] == 'pgsql') ) {
        print "(change the database_type to 'pgsql' in config.inc.php!!)\n";
    }
    print "</li>";
}

//
// Database connection
//
if ($config_loaded) {
    list ($link, $error_text) = db_connect(TRUE);
    if ($error_text == "") {
        print "<li>Testing database connection - OK - {$CONF['database_type']}://{$CONF['database_user']}:xxxxx@{$CONF['database_host']}/{$CONF['database_name']}</li>";
    } else {
        print "<li><b>Error: Can't connect to database</b><br />\n";
        print "Please edit the \$CONF['database_*'] parameters in config.inc.php.\n";
        print "$error_text</li>\n";
        $error ++;
    } 
}

//
// Session functions
//
if ($f_session_start == 1)
{
    print "<li>Depends on: session - OK</li>\n";
}
else
{
    print "<li><b>Error: Depends on: session - NOT FOUND</b><br />\n";
    print "To install session support on FreeBSD:<br />\n";
    print "<pre>% cd /usr/ports/www/php$phpversion-session/\n";
    print "% make clean install\n";
    print " - or with portupgrade -\n";
    print "% portinstall php$phpversion-session</pre></li>\n";
    $error =+ 1;
}

//
// PCRE functions
//
if ($f_preg_match == 1)
{
    print "<li>Depends on: pcre - OK</li>\n";
}
else
{
    print "<li><b>Error: Depends on: pcre - NOT FOUND</b><br />\n";
    print "To install pcre support on FreeBSD:<br />\n";
    print "<pre>% cd /usr/ports/devel/php$phpversion-pcre/\n";
    print "% make clean install\n";
    print " - or with portupgrade -\n";
    print "% portinstall php$phpversion-pcre</pre></li>\n";
    $error =+ 1;
}

//
// Multibyte functions
//
if ( $f_mb_encode_mimeheader == 1 )
{
    print "<li>Depends on: multibyte string - OK</li>\n";
}
else
{
    print "<li><b>Error: Depends on: multibyte string - NOT FOUND</b><br />\n";
    print "To install multibyte string support, install php$phpversion-mbstring</li>\n";
    $error =+ 1;
}


//
// Imap functions
//
if ( $f_imap_open == 1)
{
    print "<li>Depends on: IMAP functions - OK</li>\n";
}
else
{
    print "<li><b>Warning: Depends on: IMAP functions - NOT FOUND</b><br />\n";
    print "To install IMAP support, install php$phpversion-imap<br />\n";
    print "Without IMAP support, you won't be able to create subfolders when creating mailboxes.</li>\n";
    #   $error =+ 1;
}






print "</ul>";

if ($error != 0)
{
    print "<p><b>Please fix the errors listed above.</b></p>";
}
else
{
    print "<p>Everything seems fine... attempting to create/update database structure</p>\n";
    require_once($incpath.'/upgrade.php');

    $pAdminCreate_admin_username_text = $PALANG['pAdminCreate_admin_username_text'];
    $pAdminCreate_admin_password_text = "";
    $tUsername = '';
    $tMessage = '';
    $lostpw_error = 0;

    $setuppw = "";
    if (isset($CONF['setup_password'])) $setuppw = $CONF['setup_password'];

    if (safepost("form") == "setuppw") {
        # "setup password" form submitted
        if (safepost('setup_password') != safepost('setup_password2')) {
            $tMessage = "The two passwords differ!";
            $lostpw_error = 1;
        } else {
            list ($lostpw_error, $lostpw_result) = check_setup_password(safepost('setup_password'), 1);
            $tMessage = $lostpw_result;
            $setuppw = "changed";
        }
    } elseif (safepost("form") == "createadmin") {
        # "create admin" form submitted
        list ($pw_check_error, $pw_check_result) = check_setup_password(safepost('setup_password'));
        if ($pw_check_result != 'pass_OK') {
            $error += 1;
            $tMessage = $pw_check_result;
        }

        if($error == 0 && $pw_check_result == 'pass_OK') {
            if (isset ($_POST['fUsername'])) $fUsername = escape_string ($_POST['fUsername']);
            if (isset ($_POST['fPassword'])) $fPassword = escape_string ($_POST['fPassword']);
            if (isset ($_POST['fPassword2'])) $fPassword2 = escape_string ($_POST['fPassword2']);

            // XXX need to ensure domains table includes an 'ALL' entry.
            $table_domain = table_by_key('domain');
            $r = db_query("SELECT * FROM $table_domain WHERE domain = 'ALL'");
            if($r['rows'] == 0) {
                db_insert('domain', array('domain' => 'ALL')); // all other fields should default through the schema.
            }

            list ($error, $tMessage, $pAdminCreate_admin_username_text, $pAdminCreate_admin_password_text) = create_admin($fUsername, $fPassword, $fPassword2, array('ALL'), TRUE);
            if ($error != 0) {
                if (isset ($_POST['fUsername'])) $tUsername = escape_string ($_POST['fUsername']);
            }
        }
    } 

    if ( ($setuppw == "" || $setuppw == "changeme" || safeget("lostpw") == 1 || $lostpw_error != 0) /* && $_SERVER['REQUEST_METHOD'] != "POST" */ ) {
# show "create setup password" form
    ?>

<div class="standout"><?php print $tMessage; ?></div>
<div id="edit_form">
<form name="setuppw" method="post" action="setup.php">
<input type="hidden" name="form" value="setuppw" />
<table>
      <td colspan="3"><h3>Change setup password</h3></td>
   </tr>
   <tr>
      <td>Setup password</td>
      <td><input class="flat" type="password" name="setup_password" value="" /></td>
      <td></td>
   </tr>
   <tr>
      <td>Setup password (again)</td>
      <td><input class="flat" type="password" name="setup_password2" value="" /></td>
      <td></td>
   </tr>
   <tr>
      <td colspan="3" class="hlp_center"><input class="button" type="submit" name="submit" value="Generate password hash" /></td>
   </tr>
</table>
</form>
</div>

<?php

    } elseif ($_SERVER['REQUEST_METHOD'] == "GET" || $error != 0 || $lostpw_error == 0) {
        ?>

<div class="standout"><?php print $tMessage; ?></div>
<div id="edit_form">
<form name="create_admin" method="post">
<input type="hidden" name="form" value="createadmin" />
<table>
      <td colspan="3"><h3>Create superadmin account</h3></td>
   </tr>
   <tr>
      <td>Setup password</td>
      <td><input class="flat" type="password" name="setup_password" value="" /></td>
      <td><a href="setup.php?lostpw=1">Lost password?</a></td>
   </tr>
   <tr>
      <td><?php print $PALANG['pAdminCreate_admin_username'] . ":"; ?></td>
      <td><input class="flat" type="text" name="fUsername" value="<?php print $tUsername; ?>" /></td>
      <td><?php print $pAdminCreate_admin_username_text; ?></td>
   </tr>
   <tr>
      <td><?php print $PALANG['pAdminCreate_admin_password'] . ":"; ?></td>
      <td><input class="flat" type="password" name="fPassword" /></td>
      <td><?php print $pAdminCreate_admin_password_text; ?></td>
   </tr>
   <tr>
      <td><?php print $PALANG['pAdminCreate_admin_password2'] . ":"; ?></td>
      <td><input class="flat" type="password" name="fPassword2" /></td>
      <td>&nbsp;</td>
   </tr>
   <tr>
      <td colspan="3" class="hlp_center"><input class="button" type="submit" name="submit" value="<?php print $PALANG['pAdminCreate_admin_button']; ?>" /></td>
   </tr>
</table>
</form>
</div>

<?php
    }

    print "<b>Since version 2.3 there is no requirement to delete setup.php!</b><br />\n";
    print "<b>Check the config.inc.php file for any other settings that you might need to change!<br />\n";
}
?>
</div>
</body>
</html>
<?php

function generate_setup_password_salt() {
    $salt = time() . '*' . $_SERVER['REMOTE_ADDR'] . '*' . mt_rand(0,60000);
    $salt = md5($salt);
    return $salt;
}

function encrypt_setup_password($password, $salt) {
    return $salt . ':' . sha1($salt . ':' . $password);
}


/*
    returns: array(
        'error' => 0 (or 1),
        'message => text
    )
*/
function check_setup_password($password, $lostpw_mode = 0) {
    global $CONF;
    $error = 1; # be pessimistic

    $setuppw = "";
    if (isset($CONF['setup_password'])) $setuppw = $CONF['setup_password'];

    list($confsalt, $confpass, $trash) = explode(':', $setuppw . '::');
    $pass = encrypt_setup_password($password, $confsalt);

    if ($password == "" ) { # no password specified?
        $result = "Setup password must be specified<br />If you didn't set up a setup password yet, enter the password you want to use.";
    } elseif (strlen($password) < $CONF['min_password_length']) { # password too short?
        $result = "The setup password you entered is too short. Please choose a better one.";
    } elseif ($pass == $setuppw && $lostpw_mode == 0) { # correct passsword (and not asking for a new password)
        $result = "pass_OK";
        $error = 0;
    } else {
        $pass = encrypt_setup_password($password, generate_setup_password_salt());
        $result = "";
        if ($lostpw_mode == 1) {
            $error = 0; # non-matching password is expected when the user asks for a new password
        } else {
            $result = '<p><b>Setup password not specified correctly</b></p>';
        }
        $result .= '<p>If you want to use the password you entered as setup password, edit config.inc.php and set</p>';
        $result .= "<pre>\$CONF['setup_password'] = '$pass';</pre>";
    }
    return array ($error, $result);
}

/* vim: set expandtab softtabstop=4 tabstop=4 shiftwidth=4: */
?>
