<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

$original_umask = umask();

function copy_dir($sourcedir, $targetdir)
{
 $sourcedirhandler = opendir($sourcedir);
 while($file = readdir($sourcedirhandler))
 if ($file <> "." AND $file <> "..")
 {
  if(is_file($sourcedir."/".$file))
  {
   qp_copy($sourcedir."/".$file, $targetdir."/".$file);
  }
  else if (is_dir($sourcedir."/".$file))
  {
   if(!is_dir($targetdir."/".$file))
   {
    qp_mkdir($targetdir."/".$file);
   }
   copy_dir($sourcedir."/".$file, $targetdir."/".$file);
  }
 }
}

copy_dir($qp_current_dir, $qp_parameters["userdata_dir"]."/".$target);

header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);

?>
