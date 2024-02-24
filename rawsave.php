<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

if ($qp_current_dir == "")
{
 $qp_current_dir = $qp_input_dir;
}

if ($qp_file <> "")
{
 qp_unlink($qp_current_dir."/".$qp_file);
 $fp = @fopen($qp_parameters["fopen_ftpstring"]."/".$qp_current_dir."/".$qp_file, "w");
 fputs($fp, stripslashes($qp_content));
}
header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);

?>
