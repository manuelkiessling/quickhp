<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

set_time_limit(0);

$qp_searchstring = str_replace("\\\\\"", "\"", $qp_searchstring);
$qp_searchstring = str_replace("\\\"", "\"", $qp_searchstring);
$qp_replacestring = str_replace("\\\\\"", "\"", $qp_replacestring);
$qp_replacestring = str_replace("\\\"", "\"", $qp_replacestring);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
 <head>
  <title>
   replace - QuickHP <?php echo $qp_parameters["version"]; ?>
  </title>
  <link rel="stylesheet" type="text/css" href="css/main.css">
 </head>
 <body>
  <table class="tab" align="left" width="33%">
   <tr>
    <td class="tab-head" width="30%">
     Replace
    </td>
    <td width="70%">&nbsp;</td>
   </tr>
   <tr>
    <td class="tab-body" width="100%" colspan="2">
     <?php
     $files = recurse_dir($qp_current_dir, 1, 0);
     while (list ($key, $val) = each ($files))
     {
      if (!eregi(".qp.bak", $val))
      {
       $fp = fopen($val, "r");
       $content = fread($fp, filesize($val));
       fclose($fp);
       if (ereg($qp_searchstring, $content))
       {
        $content = str_replace($qp_searchstring, $qp_replacestring, $content);
        qp_ftp_put($content, $val);
        echo $val."<br>\n";
       }
      }
     }
     ?>
    </td>
   </tr>
  </table>
 </body>
</html>
