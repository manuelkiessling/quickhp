<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

if ($qp_filetype <> "raw")
{
 qp_copy($qp_parameters["template_dir"]."/".$qp_filetype.".qtf", $qp_current_dir."/".$qp_file);
}
else
{
 $fpt = fopen($qp_parameters["fopen_ftpstring"]."/".$qp_current_dir."/".$qp_file, "w");
 fclose($fpt);
}

header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);

?>