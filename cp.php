<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

if ($target <> "")
{
 qp_copy($qp_current_dir."/".$qp_file, $qp_current_dir."/".$target);
 header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);
}
else
{
?>
<form method="POST">
<input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
<input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
<input type="hidden" name="qp_file" value="<?php echo $qp_file; ?>">
Copy
<b><?php echo $qp_current_dir."/".$qp_file; ?></b>
<br>
to
<b><?php echo $qp_current_dir; ?>/</b><input type="text" name="target" value="<?php echo $qp_file; ?>">
<input type="submit" value="Start copy">
</form>
<?php
}
?>