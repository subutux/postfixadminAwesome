<?php if( !defined('POSTFIXADMIN') ) die( "This file cannot be used standalone." ); ?>
<div id="edit_form">
<form name="alias" method="post">
<table>
   <tr>
      <td colspan="3"><h3><?php print $PALANG['pEdit_alias_welcome']; ?></h3></td>
   </tr>
   <tr>
      <td><?php print $PALANG['pEdit_alias_address'] . ":"; ?></td>
      <td><?php print $USERID_USERNAME; ?></td>
      <td>&nbsp;</td>
   </tr>
   <tr>
      <td><?php print $PALANG['pEdit_alias_goto'] . ":"; ?></td>
      <td><textarea class="flat" rows="4" cols="50" name="fGoto">
<?php

$just_forward = "YES";
if($tStoreAndForward) {
    $just_forward = "NO";
}

foreach($tGotoArray as $address) {
   print "$address\n";
}
?>
</textarea>
      </td>
      <td>&nbsp;</td>
   </tr>
   <tr>
      <td>&nbsp;</td>
      <td colspan="2">
         <input class="flat" type="radio" name="fForward_and_store" value="YES" <?php ($just_forward=="NO") ? print 'checked' : ''; ?> />
         <?php print $PALANG['pEdit_alias_forward_and_store']; ?><br />
         <input class="flat" type="radio" name="fForward_and_store" value="NO" <?php ($just_forward=="YES") ? print 'checked' : ''; ?> />
         <?php print $PALANG['pEdit_alias_forward_only']; ?>
      </td>
   </tr>
   <tr>
      <td colspan="3" class="hlp_center">
         <input class="button" type="submit" name="submit" value="<?php print $PALANG['pEdit_alias_button']; ?>">
         <input class="button" type="submit" name="fCancel" value="<?php print $PALANG['exit']; ?>" >
      </td>
   </tr>
   <tr>
      <td colspan="3" class="standout"><?php print $tMessage; ?></td>
   </tr>
</table>
</form>
</div>

<?php /* vim: set ft=php expandtab softtabstop=3 tabstop=3 shiftwidth=3: */ ?>
