<?php

set_time_limit(0);
include("config.inc.php");
include("functions.inc.php");

if (!eregi("http://", $qp_file))
{
 $qp_user = qp_get_userdata($qp_sid);
}

function replace_tags($tags, $text)
{
 reset($tags);
 while (list ($keyt, $valt) = each ($tags))
 {
  $text = str_replace("{".$keyt."}", $valt, $text);
 }
 reset($tags);
 while (list ($keyt, $valt) = each ($tags))
 {
  $text = str_replace("{".$keyt."}", $valt, $text);
 }
 return $text;
}

function startElement($parser, $name, $attrs)
{
 GLOBAL $tags, $current_open_tag, $qp_parameters;
 $current_open_tag = $name;
 if ($name == $qp_parameters["document_tagname"])
 {
  while (list ($key, $val) = each ($attrs))
  {
   $tags[$key] = $val;
  }
 }
 if ($name == $qp_parameters["system_variables_tagname"])
 {
  while (list ($key, $val) = each ($attrs))
  {
   $tags[$key] = $val;
  }
 }
 if ($name == $qp_parameters["user_variables_tagname"])
 {
  while (list ($key, $val) = each ($attrs))
  {
   $tags[$key] = $val;
  }
 }
}

function endElement($parser, $name)
{
 GLOBAL $current_open_tag;
 $current_open_tag = "";
}

function variables_startElement($parser, $name, $attrs)
{
 GLOBAL $variables_tags;
 while (list ($key, $val) = each ($attrs))
 {
  $variables_tags[$key] = $val;
 }
}

function variables_endElement($parser, $name)
{
 //nothing
}

if (!eregi("http://", $qp_file))
{
 ?>
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
 <html>
  <head>
   <title>
    Parsing - QuickHP <?php echo $qp_parameters["version"]; ?>
   </title>
   <link rel="stylesheet" type="text/css" href="css/main.css">
  </head>
  <body>
 <?php
}
// Einlesen der verschiedenen Parsertemplates
$fp = fopen($qp_parameters["template_dir"]."/".$template."/dynamic_template.qpf", "r");
$dynamic_template = fread($fp, filesize($qp_parameters["template_dir"]."/".$template."/dynamic_template.qpf"));
fclose($fp);
$fp = fopen($qp_parameters["template_dir"]."/".$template."/transitional_template.qpf", "r");
$transitional_template = fread($fp, filesize($qp_parameters["template_dir"]."/".$template."/transitional_template.qpf"));
fclose($fp);
$fp = fopen($qp_parameters["template_dir"]."/".$template."/valid_template.qpf", "r");
$valid_template = fread($fp, filesize($qp_parameters["template_dir"]."/".$template."/valid_template.qpf"));
fclose($fp);
$fp = fopen($qp_parameters["template_dir"]."/".$template."/config.qcf", "r");
$config = fread($fp, filesize($qp_parameters["template_dir"]."/".$template."/config.qcf"));
fclose($fp);
$fp = fopen($qp_parameters["template_dir"]."/".$template."/variables.qvf", "r");
$variables = fread($fp, filesize($qp_parameters["template_dir"]."/".$template."/variables.qvf"));
fclose($fp);

// Parse changed files or all?
if ($mod == "yes")
{
 $fp = fopen($qp_parameters["template_dir"]."/lastmodified".$template, "r");
 $saved_lastmodified = fread($fp, filesize($qp_parameters["template_dir"]."/lastmodified".$template));
 fclose($fp);
}
else
{
 $saved_lastmodified = 0;
}
$lastmodified = $saved_lastmodified;

// config einlesen
ereg("<previewroot>(.*)</previewroot>", $config, $a);
$previewroot = $a[1];
ereg("<validsuffix>(.*)</validsuffix>", $config, $a);
$validsuffix = $a[1];
ereg("<transitionalsuffix>(.*)</transitionalsuffix>", $config, $a);
$transitionalsuffix = $a[1];
ereg("<dynamicsuffix>(.*)</dynamicsuffix>", $config, $a);
$dynamicsuffix = $a[1];
ereg("<parsescript>(.*)</parsescript>", $config, $a);
$parsescript = $a[1];
ereg("<targethttproot>(.*)</targethttproot>", $config, $a);
$targethttproot = $a[1];
ereg("<trimrootpath>(.*)</trimrootpath>", $config, $a);
$trimrootpath = $a[1];
if ($trimrootpath == "") $trimrootpath = 0;

//Defaultcontentinhalte einlesen
$i = 0;
$noend = TRUE;
while($noend)
{
 if (@is_file($qp_parameters["template_dir"]."/".$template."/default_content".$i.".qdf"))
 {
  $fp = fopen($qp_parameters["template_dir"]."/".$template."/default_content".$i.".qdf", "r");
  $default_content[$i] = fread($fp, filesize($qp_parameters["template_dir"]."/".$template."/default_content".$i.".qdf"));
  fclose($fp);
 }
 else
 {
  $noend = FALSE;
 }
 $i++;
}

// variablen einlesen
$xml_parser = xml_parser_create();
xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
xml_set_element_handler($xml_parser, "variables_startElement", "variables_endElement");
if (!xml_parse($xml_parser, $variables))
{
 die(sprintf("XML error: %s in file $val at line %d",
 xml_error_string(xml_get_error_code($xml_parser)),
 xml_get_current_line_number($xml_parser)));
}
xml_parser_free($xml_parser);

$istargetroot_ftp = TRUE;
$targetroot_ftp = array();
$targetserver_ftp = array();
$targetuser_ftp = array();
$targetpassword_ftp = array();
$i = 1;
while ($istargetroot_ftp == TRUE)
{
 if (ereg("<targetroot_ftp".$i.">", $config))
 {
  ereg("<targetroot_ftp".$i.">(.*)</targetroot_ftp".$i.">", $config, $a);
  $targetroot_ftp[$i] = $a[1];
  ereg("<targetserver_ftp".$i.">(.*)</targetserver_ftp".$i.">", $config, $a);
  $targetserver_ftp[$i] = $a[1];
  ereg("<targetuser_ftp".$i.">(.*)</targetuser_ftp".$i.">", $config, $a);
  $targetuser_ftp[$i] = $a[1];
  ereg("<targetpassword_ftp".$i.">(.*)</targetpassword_ftp".$i.">", $config, $a);
  $targetpassword_ftp[$i] = $a[1];
 }
 else
 {
  $istargetroot_ftp = FALSE;
 }
 $i++;
}

if ($qp_dir == "")
{
 $files = recurse_dir($qp_parameters["input_dir"], 1, 0);
}
else
{
 $files = recurse_dir($qp_dir, 1, 0);
}

$parseperturncheck = 1;
if ($qp_file == "")
{
 $start_parsing = TRUE;
}

//loeschen des vorschauverzeichnisses
if ($qp_file == "")
{
 if (is_dir($qp_parameters["output_dir"]."/".$previewroot))
 {
  qp_rmdir($qp_parameters["output_dir"]."/".$previewroot);
 }
 qp_mkdir($qp_parameters["output_dir"]."/".$previewroot);
}
else
{
 $files = "null";
 if (!eregi("http://", $qp_file))
 {
  $files = array(0 => $qp_current_dir."/".$qp_file);
 }
 else
 {
  $files = array(0 => $qp_file);
 }
 $start_parsing = TRUE;
}

reset ($files);
while (list ($key, $val) = each ($files))
{
 if (!eregi(".qp.bak", $val))
 {
  if (($start_parsing == TRUE))
  {
   if (!eregi("http://", $val))
   {
    $this_lastmodified = filemtime($val);
    if ($this_lastmodified > $lastmodified) $lastmodified = $this_lastmodified;
   }
   if ($this_lastmodified > $saved_lastmodified XOR eregi("http://", $val))
   {
    $tags["qp_timestamp_parsed"] = date("Y-m-d H:i:s");
    
    $filename_s = basename($val);
    $dirname_s = dirname($val);
    $dirname_t = str_replace($qp_parameters["input_dir"], $qp_parameters["output_dir"]."/".$previewroot, $dirname_s);
    if (!eregi("http://", $val))
    {
     $depths = explode("/", $val);
     $depth = count($depths) - 2;
     $tags["qp_rootpath"] = "";
     for($i = 1; $i < $depth - $trimrootpath; $i++)
     {
      $tags["qp_rootpath"] .= "../";
     }
    }
    else
    {
     $tags["qp_rootpath"] = $qp_root;
    }
    if (!eregi("http://", $val))
    {
     if(!@is_dir($dirname_t))
     {
      force_dir($dirname_t);
     }
     $tags["qp_selfpath"] = str_replace($qp_parameters["input_dir"]."/", "", $val);
    }
    else
    {
     $tags["qp_selfpath"] = ereg_replace("\?(.*)", "",str_replace($targethttproot."/", "", $val));
    }
    
    // Keine XML Datei -> einfach kopieren
    if (substr($val, -3) <> "xml" AND !eregi("http://", $val))
    {
     $fp = fopen ($val, "r");
     $tmp = fread($fp, filesize($val));
     fclose($fp);
     qp_ftp_put($tmp, $dirname_t."/".$filename_s);
     echo "Copy: <a href=\"".$dirname_t."/".$filename_s."\">".$val."</a><br>\n";
    }
    
    // Dynamische XML Datei -> via Dynamictemplate parsen
    if (substr($val, -5) == ".dxml")
    {
     $fp2 = fopen($val, "r");
     $data = fread($fp2, filesize($val));
     fclose($fp2);
     $this_dynamic_template = str_replace("{content}", $data, $dynamic_template);
     $this_dynamic_template = str_replace("{template}", $template, $this_dynamic_template);
     $this_dynamic_template = str_replace("{root}", $tags["qp_rootpath"], $this_dynamic_template);
     $this_dynamic_template = str_replace("{script}", $parsescript, $this_dynamic_template);
     @qp_unlink($dirname_t."/".str_replace(".dxml", ".".$dynamicsuffix, $filename_s));
     $filename_t = str_replace(".dxml", ".".$dynamicsuffix, $filename_s);
     qp_ftp_put($this_dynamic_template, $dirname_t."/".$filename_t);
     echo "Parse (dynamic): <a href=\"".$dirname_t."/".$filename_t."\">".$val."</a><br>\n";
    }
    
    // Echte XML-Datei oder entfernte dynamische echte XML Datei -> via XSLT parsen
    if (substr($val, -4) == ".xml" OR eregi("http://", $val))
    {
     $xml_parser = xml_parser_create();
     xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
     xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
     xml_set_element_handler($xml_parser, "startElement", "endElement");
     $fp = fopen($val, "r");
     if (!eregi("http://", $val))
     {
      $data = fread($fp, filesize($val));
     }
     else
     {
      $data = fread($fp, 99999999);
     }
     fclose($fp);

     /* FIXME: this is WAY too slow...
     $i = 0;
     $noend = TRUE;
     while($noend)
     {
      if (strstr($data, "<".$qp_parameters["content_tagname"].$i." "))
      {
       ereg("<".$qp_parameters["content_tagname"].$i." descr=\"(.*)\" qp_dummy=\"\">(.*)</".$qp_parameters["content_tagname"].$i.">", $data, $a);
       if ($a[2] == "")
       {
        $data = ereg_replace("<".$qp_parameters["content_tagname"].$i." descr=\"(.*)\" qp_dummy=\"\">(.*)</".$qp_parameters["content_tagname"].$i.">",
                             "<".$qp_parameters["content_tagname"].$i." descr=\"".$a[1]."\" qp_dummy=\"\">".$default_content[$i]."</".$qp_parameters["content_tagname"].$i.">",
                             $data);
       }
      }
      else
      {
       $noend = FALSE;
      }
      $i++;
     }
     */

     
     $xmlfile = $data;
     
     if (!xml_parse($xml_parser, $data))
     {
      die(sprintf("XML error: %s in file $val at line %d",
      xml_error_string(xml_get_error_code($xml_parser)),
      xml_get_current_line_number($xml_parser)));
     }
     xml_parser_free($xml_parser);
     
     $this_valid_template = $valid_template;
    
     $filename_t = str_replace(".xml", ".".$validsuffix, $filename_s);
     $tags["qp_selfpath"] = str_replace(".xml", ".".$validsuffix, $tags["qp_selfpath"]);
    
     $this_valid_template = replace_tags($tags, $this_valid_template);
     $this_valid_template = replace_tags($variables_tags, $this_valid_template);
    
     $arguments = array("/_xml" => $xmlfile, "/_xsl" => $this_valid_template);
     $xh = xslt_create();
     $xslresult = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
     if (!$xslresult)
     {
      die(sprintf("Cannot process XSLT document [%d]: %s", xslt_errno($xh), xslt_error($xh)));
     }
     xslt_free($xh);


     $xslresult = replace_tags($tags, $xslresult);
     $xslresult = replace_tags($variables_tags, $xslresult);
     
     $xslresult = str_replace(".xml", ".".$validsuffix, $xslresult);
     $xslresult = str_replace(".txml", ".".$transitionalsuffix, $xslresult);
     $xslresult = str_replace(".dxml", ".".$dynamicsuffix, $xslresult);
    
     if (!eregi("http://", $val))
     {
      qp_unlink($dirname_t."/".$filename_t);
      qp_ftp_put($xslresult, $dirname_t."/".$filename_t);
      if (!eregi("http://", $val))
      {
       echo "Parse (valid): <a href=\"".$dirname_t."/".$filename_t."\">".$val."</a><br>\n";
      }
      if ($parseperturncheck == $parseperturn)
      {
       echo "<a href=parse.php?qp_sid=".$qp_sid."&template=".$template."&qp_file=".urlencode($val).">Parse the next $parseperturn files...</a><br><br>";
       break;
      }
      $parseperturncheck++;
     }
     else
     {
      echo $xslresult;
      die();
     }
    }
    
    // Transitional XML Datei -> via Transitionaltemplate parsen
    if (substr($val, -5) == ".txml")
    {
     $xml_parser = xml_parser_create();
     xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
     xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
     xml_set_element_handler($xml_parser, "startElement", "endElement");
     $fp = fopen($val, "r");
     $data = fread($fp, filesize($val));
     fclose($fp);
     
     $i = 0;
     $noend = TRUE;
     while($noend)
     {
      if (strstr($data, "<".$qp_parameters["content_tagname"].$i." "))
      {
       preg_match("<".$qp_parameters["content_tagname"].$i." descr=\"(.*)?\" qp_dummy=\"\">", $data, $a);
       $qp_tmp_content_descr[$i] = $a[1];
       ereg("<".$qp_parameters["content_tagname"].$i." descr=\"".$qp_tmp_content_descr[$i]."\" qp_dummy=\"\">(.*)</".$qp_parameters["content_tagname"].$i.">", $data, $a);
    
       $tmp = $qp_parameters["content_tagname"].$i;
       if ($a[1] == "")
       {
        $tags[$tmp] = $default_content[$i];
       }
       else
       {
        $tags[$tmp] = $a[1];
       }
       $data = str_replace("<".$qp_parameters["content_tagname"].$i." descr=\"".$qp_tmp_content_descr[$i]."\" qp_dummy=\"\">".$a[1]."</".$qp_parameters["content_tagname"].$i.">", "", $data);
      }
      else
      {
       $noend = FALSE;
      }
      $i++;
     }
    
     if (!xml_parse($xml_parser, $data))
     {
      die(sprintf("XML error: %s in file $val at line %d",
      xml_error_string(xml_get_error_code($xml_parser)),
      xml_get_current_line_number($xml_parser)));
     }
    
     $this_transitional_template = $transitional_template;
     
     $filename_t = str_replace(".txml", ".".$transitionalsuffix, $filename_s);
     $tags["qp_selfpath"] = str_replace(".txml", ".".$transitionalsuffix, $tags["qp_selfpath"]);
    
     $this_transitional_template = replace_tags($tags, $this_transitional_template);
     $this_transitional_template = replace_tags($variables_tags, $this_transitional_template);
     
     $this_transitional_template = str_replace(".xml", ".".$validtsuffix, $this_transitional_template);
     $this_transitional_template = str_replace(".txml", ".".$transitionalsuffix, $this_transitional_template);
     $this_transitional_template = str_replace(".dxml", ".".$dynamicsuffix, $this_transitional_template);

     qp_ftp_put($this_transitional_template, $dirname_t."/".$filename_t);
     echo "Parse (transitional): <a href=\"".$dirname_t."/".$filename_t."\">".$val."</a><br>\n";
     if ($parseperturncheck == $parseperturn)
     {
      echo "<a href=parse.php?qp_sid=".$qp_sid."&template=".$template."&qp_file=".urlencode($val).">Parse the next $parseperturn files...</a><br><br>";
      break;
     }
     $parseperturncheck++;
    }
   }
  }
  if ($val == $qp_file)
  {
   $start_parsing = TRUE;
  }
 }
}

if ($lastmodified > $saved_lastmodified AND $mod == "yes")
{
 qp_ftp_put($lastmodified, $qp_parameters["template_dir"]."/lastmodified".$template);
}

?>

<br><br><a href="<?php echo $qp_parameters["output_dir"]."/".$previewroot."/"; ?>">Take a look at the preview files</a><br><br>

<?php
if ($qp_user["flag_ftpallowed"] == 1)
{
 while (list ($key, $val) = each ($targetroot_ftp))
 {
  ?>
  <form action="copyparsedftp.php" method="post">
   <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
   <input type="hidden" name="sourceroot" value="<?php echo $qp_parameters["output_dir"]."/".$previewroot; ?>">
   <input type="hidden" name="targetroot_ftp" value="<?php echo $val; ?>">
   <input type="hidden" name="targetserver_ftp" value="<?php echo $targetserver_ftp[$key]; ?>">
   <input type="hidden" name="targetuser_ftp" value="<?php echo $targetuser_ftp[$key]; ?>">
   <input type="hidden" name="targetpassword_ftp" value="<?php echo $targetpassword_ftp[$key]; ?>">
   <input type="submit" value="FTP to <?php echo $targetserver_ftp[$key]; ?>">
   <input type="radio" name="only_ascii" value=1> FTP only ASCII files
  </form>
  <?php
 }
}
?>

 </body>
</html>