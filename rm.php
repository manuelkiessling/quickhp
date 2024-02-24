<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

if (is_dir($qp_current_dir."/".$qp_file))
{
 if ($qp_really == "yes")
 {
  if (!qp_rmdir($qp_current_dir."/".$qp_file))
  {
   echo "failed to delete dir $qp_file...<br>\n";
  }
 }
 else
 {
  ?>
  <body bgcolor="#FF0000">
   <h1>
    Really?
   </h1>
   <a href="<?php echo "rm.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir."&qp_file=".$qp_file."&qp_really=yes"; ?>">YES</a>
   <br>
   <br>
   <a href="<?php echo "browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir; ?>">NO, I'm lame.</a>
  </body>
  <?php
  die();
 }
}
else
{
 if (!qp_unlink($qp_current_dir."/".$qp_file))
 {
  echo "failed to delete file $qp_file...<br>\n";
 }
}

header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);

?>