<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

function this_rmdir($dir)
{
 $dirhandler = opendir($dir);
 while($file = readdir($dirhandler))
 if ($file <> "." AND $file <> "..")
 {
  if(is_file($dir."/".$file))
  {
   qp_unlink($dir."/".$file);
  }
  else if (is_dir($dir."/".$file))
  {
   this_rmdir($dir."/".$file);
   qp_rmdir($dir."/".$file);
  }
 }
}

this_rmdir($qp_current_dir);

header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);

?>
