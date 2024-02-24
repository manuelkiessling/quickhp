<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

if (!qp_mkdir ($qp_current_dir."/".$qp_dir))
{
 echo "failed to create $qp_dir...<br>\n";
}

header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);

?>