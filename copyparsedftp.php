<?php

set_time_limit(0);

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
 <head>
  <title>
   FTP Copy - QuickHP <?php echo $qp_parameters["version"]; ?>
  </title>
  <link rel="stylesheet" type="text/css" href="css/main.css">
 </head>
 <body>

<?php

if (!$qp_user["flag_ftpallowed"] == 1) die("<p class=\"error\">You are not allowed to do FTP.</p>");

$conn_id = @ftp_connect("$targetserver_ftp"); 
$login_result = @ftp_login($conn_id, "$targetuser_ftp", "$targetpassword_ftp"); 
if ((!$conn_id) || (!$login_result))
{ 
 echo "<b>Ftp connection has failed!";
 echo "Attempted to connect to $targetserver_ftp for user $targetuser_ftp</b>"; 
 die; 
}
else
{
 echo "<b>Connected to $targetserver_ftp, for user $targetuser_ftp</b><br><br>";
}


$files = recurse_dir($sourceroot, 1, 0);

$ftpperturncheck = 1;
if ($qp_file == "")
{
 $start_parsing = TRUE;
}

reset ($files);
while (list ($key, $val) = each ($files))
{
 if ($start_parsing == TRUE)
 {
  //echo "$val<br><br>";
  $filename_s = basename($val);
  $dirname_s = dirname($val);
  $dirname_t = ereg_replace($sourceroot, $targetroot_ftp, $dirname_s);
  $dirs = explode("/", $dirname_t);
  reset($dirs);
  $completedir = ".";
  while (list ($keydirs, $valdirs) = each ($dirs))
  {
   $completedir .= "/".$valdirs;
   $completedir = str_replace(".//", "./", $completedir);
   @ftp_mkdir($conn_id, $completedir);
  }

  $ftp_mode = FTP_BINARY;
  if (strstr($filename_s, ".html") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".pl") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".cgi") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".php") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".phtml") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".htmls") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".htm") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".php") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".phps") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".css") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".js") <> FALSE) { $ftp_mode = FTP_ASCII; }
  if (strstr($filename_s, ".txt") <> FALSE) { $ftp_mode = FTP_ASCII; }
  
  if ($only_ascii == 1)
  {
   if ($ftp_mode == FTP_ASCII)
   {
    $upload = @ftp_put($conn_id, $dirname_t."/".$filename_s, $dirname_s."/".$filename_s, $ftp_mode);
    if (!$upload)
    { 
     echo "<br><b>Ftp upload to $dirname_t/$filename_s has failed!</b><br><br>";
    }
    else
    {
     echo "<br>Uploaded $dirname_s/$filename_s <b>as</b> $dirname_t/$filename_s<br><br>";
    }
   }
  }
  else
  {
   $upload = @ftp_put($conn_id, $dirname_t."/".$filename_s, $dirname_s."/".$filename_s, $ftp_mode);
   if (!$upload)
   { 
    echo "<br><b>Ftp upload to $dirname_t/$filename_s has failed!</b><br><br>";
   }
   else
   {
    echo "<br>Uploaded $dirname_s/$filename_s <b>as</b> $dirname_t/$filename_s<br><br>";
   }
   if ($ftpperturncheck == $qp_parameters["ftpperturn"])
   {
    ?>
    <form action="copyparsedftp.php" method="post">
     <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
     <input type="hidden" name="sourceroot" value="<?php echo $sourceroot; ?>">
     <input type="hidden" name="targetroot_ftp" value="<?php echo $targetroot_ftp; ?>">
     <input type="hidden" name="targetserver_ftp" value="<?php echo $targetserver_ftp; ?>">
     <input type="hidden" name="targetuser_ftp" value="<?php echo $targetuser_ftp; ?>">
     <input type="hidden" name="targetpassword_ftp" value="<?php echo $targetpassword_ftp; ?>">
     <input type="hidden" name="only_ascii" value="<?php echo $only_ascii; ?>">
     <input type="hidden" name="qp_file" value="<?php echo $val; ?>">
     <input type="submit" value="FTP the next <?php echo $qp_parameters["ftpperturn"]; ?> files to <?php echo $targetserver_ftp; ?>">
    </form>
    <?php
    break;
   }
   $ftpperturncheck++;
  }
 }
 if ($val == $qp_file)
 {
  $start_parsing = TRUE;
 }
}

ftp_quit($conn_id); 

?>

  <br>
  <br>
  <b>
   done
  </b>
 </body>
</html>