<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

function write_tag($tagname, $tagarray)
{
 GLOBAL $qp_parameters;
 $return .= "<".$tagname."\n";
 while (list ($key, $val) = each ($tagarray))
 {
  if (substr($key, 0, strlen($tagname)) == $tagname)
  {
   $return .= " ".substr($key, strlen($tagname)+1)."=\"".stripslashes($val)."\"\n";
  }
 }
 if ($tagname == $qp_parameters["document_tagname"])
 {
  $return .= ">\n";
 }
 else
 {
  $return .= "/>\n";
 }
 return $return;
}

if ($qp_current_dir == "")
{
 $qp_current_dir = $qp_input_dir;
}

if ($qp_file <> "")
{
 qp_unlink($qp_current_dir."/".$qp_file);
 $vals = $HTTP_POST_VARS;
 $content = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
 $content .= write_tag($qp_parameters["document_tagname"], $vals);
 
 if ($vals[$qp_parameters["system_variables_tagname"]."_editor_name"] == "")
 {
  $vals[$qp_parameters["system_variables_tagname"]."_editor_name"] = $qp_user["name"];
 }
 if ($vals[$qp_parameters["system_variables_tagname"]."_editor_mail"] == "")
 {
  $vals[$qp_parameters["system_variables_tagname"]."_editor_mail"] = $qp_user["email"];
 }
 if ($vals[$qp_parameters["system_variables_tagname"]."_date_created"] == "")
 {
  $vals[$qp_parameters["system_variables_tagname"]."_date_created"] = date("Y-m-d");
 }
 $vals[$qp_parameters["system_variables_tagname"]."_date_updated"] = date("Y-m-d");
 if ($vals[$qp_parameters["system_variables_tagname"]."_time_created"] == "")
 {
  $vals[$qp_parameters["system_variables_tagname"]."_time_created"] = date("H:i:s");
 }
 $vals[$qp_parameters["system_variables_tagname"]."_time_updated"] = date("H:i:s");
 
 $content .= write_tag($qp_parameters["system_variables_tagname"], $vals);
 $content .= write_tag($qp_parameters["user_variables_tagname"], $vals);
 foreach($qp_content as $key => $value)
 {
  $content .= "<".$qp_parameters["content_tagname"].$key." descr=\"".$qp_content_descr[$key]."\" qp_dummy=\"\">".stripslashes($value)."</".$qp_parameters["content_tagname"].$key.">\n";
 }
 $content .= "</".$qp_parameters["document_tagname"].">\n";
 qp_ftp_put($content, $qp_current_dir."/".$qp_file);
}
header("Location: browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir);

?>
