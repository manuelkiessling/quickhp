<?php
include("config.inc.php");

$result = mysql_query("SELECT name, homedir FROM qp_users WHERE name = '$name' AND password = '$password'");
$a = mysql_fetch_array($result);
if ($a["name"] != "")
{
 srand((double)microtime()*1000000);
 $qp_sid = md5(rand(0,9999999));
 mysql_query("UPDATE qp_users SET sid = '$qp_sid' WHERE name = '$name'");
 header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_parameters["userdata_dir"].$a["homedir"]);
}
else
{
 header("Location: index.php?qp_error=".urlencode("Login failed."));
}
?>