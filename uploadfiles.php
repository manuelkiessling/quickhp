<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

set_time_limit(0);

for ($i = 0; $i < sizeof($userfile); $i++)
{
 if ($userfile_name[$i] <> "")
 {
  qp_copy($userfile[$i], $qp_current_dir."/".$userfile_name[$i]);
 }
}

header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);

?>