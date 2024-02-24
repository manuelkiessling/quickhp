<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

if ($qp_current_dir == "")
{
 $qp_current_dir = $qp_input_dir;
}

function startElement($parser, $name, $attrs)
{
 GLOBAL $current_open_tag,
        $qp_parameters;
 $current_open_tag = $name;
 if ($name == $qp_parameters["document_tagname"])
 {
  echo "<b>".$name."</b>\n<br>\n";
  while (list ($key, $val) = each ($attrs))
  {
   echo $key."<br>\n<input size=\"30\" type=\"text\" name=\"".$name."_".$key."\" value=\"".$val."\"><br><br>\n";
  }
  echo "<br><br>\n";
 }
 if ($name == $qp_parameters["system_variables_tagname"])
 {
  echo "<b>".$name."</b>\n<br>\n";
  while (list ($key, $val) = each ($attrs))
  {
   echo "<input type=\"hidden\" name=\"".$qp_system_variables_tagname."_".$key."\" value=\"".$val."\">";
   echo "<i>".$key."</i>: ".$val."<br>\n";
  }
  echo "<br><br>\n";
 }
 if ($name == $qp_parameters["user_variables_tagname"])
 {
  echo "<b>".$name."</b>\n<br>\n";
  while (list ($key, $val) = each ($attrs))
  {
   echo $key."<br>\n<input type=\"text\" name=\"".$name."_".$key."\" value=\"".$val."\"><br><br>\n";
  }
  echo "<br><br>\n";
 }
}

function endElement($parser, $name)
{
 GLOBAL $current_open_tag;
 $current_open_tag = "";
}


if (!($fp = fopen($qp_current_dir."/".$qp_file, "r")))
{
 die("could not open XML input");
}

qp_copy($qp_current_dir."/".$qp_file, $qp_current_dir."/".$qp_file.".".date("Ymd-His").".qp.bak");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
 <head>
  <title>
   XMLEdit - QuickHP <?php echo $qp_parameters["version"]; ?>
  </title>
  <link rel="stylesheet" type="text/css" href="css/main.css">
 </head>
 <body>
  <form action="xmlsave.php" method="POST">
   <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
   <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
   <table class="tab" align="left" width="33%">
    <tr>
     <td class="tab-head" width="30%">
      Edit file
     </td>
     <td width="70%">&nbsp;</td>
    </tr>
    <tr>
     <td class="tab-body" width="100%" colspan="2">
      <?php
      $data = fread($fp, filesize($qp_current_dir."/".$qp_file));
      if ($qp_parameters["check_xml"] == TRUE)
      {
       $xmlisvalid = qp_xml_check($data);
       if ($xmlisvalid <> "TRUE")
       {
        echo "<br><font color=\"#FF0000\">XML Check: $xmlisvalid</font><br><br>";
       }
      }
      
      $xml_parser = xml_parser_create();
      xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
      xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
      xml_set_element_handler($xml_parser, "startElement", "endElement");
      
      $i = 0;
      $noend = TRUE;
      while($noend)
      {
       if (strstr($data, "<".$qp_parameters["content_tagname"].$i." "))
       {
        preg_match("<".$qp_parameters["content_tagname"].$i." descr=\"(.*)?\" qp_dummy=\"\">", $data, $a);
        $qp_content_descr[$i] = $a[1];
        ereg("<".$qp_parameters["content_tagname"].$i." descr=\"".$qp_content_descr[$i]."\" qp_dummy=\"\">(.*)</".$qp_parameters["content_tagname"].$i.">", $data, $a);
        $qp_content[$i] = $a[1];
        $data = str_replace("<".$qp_parameters["content_tagname"].$i." descr=\"".$qp_content_descr[$i]."\" qp_dummy=\"\">".$qp_content[$i]."</".$qp_parameters["content_tagname"].$i.">", "", $data);
       }
       else
       {
        $noend = FALSE;
       }
       $i++;
      }
      if (!xml_parse($xml_parser, $data))
      {
       die(sprintf("XML error: %s at line %d",
       xml_error_string(xml_get_error_code($xml_parser)),
       xml_get_current_line_number($xml_parser)));
      }
      xml_parser_free($xml_parser);
      fclose($fp);
      
      reset($qp_content);
      foreach($qp_content as $key => $value)
      {
       echo "<input type=\"hidden\" name=\"qp_content_descr[".$key."]\" value=\"".$qp_content_descr[$key]."\">\n";
       echo "<b>".$qp_content_descr[$key]."</b><br>\n<textarea name=\"qp_content[".$key."]\" rows=".$qp_parameters["textfield_rows"]." cols=".$qp_parameters["textfield_cols"]." wrap=\"off\">".trim(htmlentities($value, ENT_QUOTES))."</textarea>\n<br>\n<br>\n";
      }
      
      ?>
      <br>
      <br>
      Filename: <input type="text" name="qp_file" value="<?php echo $qp_file; ?>">
     </td>
    </tr>
    <tr>
     <td class="tab-foot" width="100%" colspan="2" align="right">
      <input type="submit" value="Save file">
     </td>
    </tr>
   </table>
  </form>
 </body>
</html>