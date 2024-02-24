<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

set_time_limit(0);

$qp_searchstring = str_replace("\\\\\"", "\"", $qp_searchstring);
$qp_searchstring = str_replace("\\\"", "\"", $qp_searchstring);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
 <head>
  <title>
   Search - QuickHP <?php echo $qp_parameters["version"]; ?>
  </title>
  <link rel="stylesheet" type="text/css" href="css/main.css">
 </head>
 <body>
  <table class="tab" align="left" width="33%">
   <tr>
    <td class="tab-head" width="30%">
     Search
    </td>
    <td width="70%">&nbsp;</td>
   </tr>
   <tr>
    <td class="tab-body" width="100%" colspan="2">
     <?php
     $files = recurse_dir($qp_current_dir, 1, 0);
     while (list ($key, $val) = each ($files))
     {
      if (!strstr($val, ".bak"))
      {
       $fp = fopen($val, "r");
       $content = fread($fp, filesize($val));
       if (strstr($content, $qp_searchstring))
       {
        if (substr($val, -3) == "xml")
        {
         echo "<a href=\"xmledit.php?qp_sid=".$qp_sid."&qp_current_dir=".dirname($val)."&qp_file=".basename($val)."&sid=".$sid."\">".$val."</a><br>";
        }
        else
        {
         echo "<a href=\"rawedit.php?qp_sid=".$qp_sid."&qp_current_dir=".dirname($val)."&qp_file=".basename($val)."&sid=".$sid."\">".$val."</a><br>";
        }
       }
       fclose($fp);
      }
     }
     ?>
    </td>
   </tr>
  </table>
 </body>
</html>
 