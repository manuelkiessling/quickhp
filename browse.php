<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
 <head>
  <title>
   Browser - QuickHP <?php echo $qp_parameters["version"]; ?>
  </title>
  <link rel="stylesheet" type="text/css" href="css/main.css">
 </head>
 <body>
 <?php

  if (   $qp_current_dir == ""
      OR substr($qp_current_dir, 0, strlen($qp_parameters["userdata_dir"].$qp_user["homedir"])) <> $qp_parameters["userdata_dir"].$qp_user["homedir"]
      OR ereg("\.\.", $qp_current_dir)
     )
  {
   $qp_current_dir = $qp_parameters["userdata_dir"].$qp_user["homedir"];
  }

  if ($qp_dir = @opendir($qp_current_dir))
  {
   ?>
   <table width="100%" border="0" cellspacing="0" cellpadding="4">
    <tr class="filelist-head">
     <td width="45%" align="left" valign="bottom">
     	<?php
     	if ($qp_show_bak <> "")
     	{
     	 ?>
     	 Showing history of
     	 <b>
     	  <?php
     	  echo $qp_show_bak."<br>\n";
     	  ?>
     	 </b>
     	 <?php
    	  echo "<a class=\"light\" href=\"browse.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir."\">Switch back to file listing</a>";
      }
      else
      {
     	 ?>
     	 Content of &nbsp;
     	 <b>
        <?php
        $qp_dirarray = explode("/", $qp_current_dir);
        while (list ($qp_key, $qp_val) = each ($qp_dirarray))
        {
         $qp_dirpath .= $qp_val."/";
         echo "<a class=\"light\" href=\"browse.php?qp_sid=".$qp_sid."&qp_current_dir=".urlencode(substr($qp_dirpath, 0, -1))."\">".$qp_val."</a> / ";
        }
        ?>
       </b>
       <?php
      }
      ?>
     </td>
     <td width="10%" align="right" valign="bottom">
      Size
     </td>
     <td with="5%" align="left" valign="bottom">
      Perm
     </td>
     <td with="10%" align="left" valign="bottom">
      Edit
     </td>
     <td with="5%" align="left" valign="bottom">
      Hist
     </td>
     <td with="5%" align="right" valign="bottom">
      Del
     </td>
     <td with="5%" align="right" valign="bottom">
      Copy
     </td>
     <td with="5%" align="right" valign="bottom">
      Parse
     </td>
    </tr>
   <?php
   $qp_dirs = array();
   $qp_files = array();
   while($qp_tmp_file = readdir($qp_dir))
   {
    if (is_dir($qp_current_dir."/".$qp_tmp_file))
    {
     $qp_dirs[] = $qp_tmp_file;
    }
    else
    {
     $qp_files[] = $qp_tmp_file;
    }
   }
   natsort($qp_dirs);
   natsort($qp_files);
   $qp_files = array_merge($qp_dirs, $qp_files);
   $qp_cur_rowclass = "filelist-row-light";
   while(list($qp_file_key, $qp_file) = each($qp_files))
   {
    if ($qp_file <> "." AND ($qp_show_bak == "" XOR eregi($qp_show_bak."(.*).qp.bak", $qp_file)))
    {
     echo "<tr class=\"".$qp_cur_rowclass."\">\n<td align=\"left\" valign=\"bottom\">\n";
     if ($qp_cur_rowclass == "filelist-row-light")
     {
      $qp_cur_rowclass = "filelist-row-dark";
     }
     else
     {
      $qp_cur_rowclass = "filelist-row-light";
     }
     if (($qp_file == ".."))
     {
      $qp_dirparts = explode("/", $qp_current_dir);
      for ($i = 0; $i < sizeof($qp_dirparts) - 1; $i++)
      { 
       $qp_nextdir = $qp_nextdir."/".$qp_dirparts[$i];
      }
      $qp_nextdir = substr($qp_nextdir, 1);
      if (!($qp_current_dir == $qp_parameters["userdata_dir"]))
      {
       echo "<b><a href=\"browse.php?qp_sid=".$qp_sid."&qp_current_dir=".urlencode($qp_nextdir)."\">".$qp_file."</a></b><br>\n";
      }
     }
     else if (is_dir($qp_current_dir."/".$qp_file))
     {
      echo "<b>[<a href=\"browse.php?qp_sid=".$qp_sid."&qp_current_dir=".urlencode($qp_current_dir."/".$qp_file)."\">".$qp_file."</a>]</b>\n";
     }
     else
     {
      if (substr($qp_file, -3) == "xml")
      {
       echo "<a href=\"xmledit.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir."&qp_file=".$qp_file."\">".$qp_file."</a>\n";
      }
      else
      {
       echo "<a href=\"rawedit.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir."&qp_file=".$qp_file."\">".$qp_file."</a>\n";
      }
     }
     echo "\n</td>\n<td align=\"right\" valign=\"bottom\">\n";
     echo filesize($qp_current_dir."/".$qp_file);
     echo "\n</td>\n<td align=\"left\" valign=\"bottom\">\n";
     echo qp_display_perms(fileperms($qp_current_dir."/".$qp_file));
     echo "\n</td>\n<td align=\"left\" valign=\"bottom\">\n";
     if ($qp_file <> ".." AND !is_dir($qp_current_dir."/".$qp_file))
     {
      echo "<a href=\"rawedit.php?qp_sid=".$qp_sid."&qp_file=".$qp_file."&qp_current_dir=".$qp_current_dir."\">RAW</a>\n&nbsp;&nbsp;";
      if (strstr($qp_file, "xml") <> FALSE)
      {
       echo "<a href=\"xmledit.php?qp_sid=".$qp_sid."&qp_current_dir=".$qp_current_dir."&qp_file=".$qp_file."\">XML</a>\n";
      }
     }
     echo "&nbsp";
     echo "\n</td>\n<td align=\"left\" valign=\"bottom\">\n";
     echo "<a href=\"browse.php?qp_sid=".$qp_sid."&qp_show_bak=".urlencode($qp_file)."&qp_current_dir=".urlencode($qp_current_dir)."\">Hist</a>\n";
     echo "\n</td>\n<td align=\"right\" valign=\"bottom\">\n";
     echo "<a href=\"rm.php?qp_sid=".$qp_sid."&qp_file=".urlencode($qp_file)."&qp_current_dir=".urlencode($qp_current_dir)."\">DEL</a>\n";
     echo "\n</td>\n<td align=\"right\" valign=\"bottom\">\n";
     echo "<a href=\"cp.php?qp_sid=".$qp_sid."&qp_file=".urlencode($qp_file)."&qp_current_dir=".urlencode($qp_current_dir)."\">CP</a>\n";
     echo "\n</td>\n<td align=\"right\" valign=\"bottom\">\n";
     echo "<a href=\"parse.php?qp_sid=".$qp_sid."&template=html.qpt&qp_file=".urlencode($qp_file)."&qp_current_dir=".urlencode($qp_current_dir)."\" target=\"_blank\">PRS</a>\n";
     echo "\n</td>\n</tr>\n";
    }
   }  
   echo "</table>\n";
   closedir($qp_dir);
  }
  
  ?>
  <hr size="1">
  <?php
  if ($qp_show_functions <> "no")
  {
   ?>
   <br>
   <br>
   <form action="createfile.php" method="POST">
    <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
    <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
    <table class="tab" width="50%">
     <tr>
      <td class="tab-head" width="30%">
       Create file
      </td>
      <td width="70%">&nbsp;</td>
     </tr>
     <tr>
      <td class="tab-body" width="100%" colspan="2">
       Name: <input type="text" name="qp_file">
       <br>
       Template:
       <select name="qp_filetype">
        <?php
         $qp_tpl_dir = @opendir($qp_parameters["template_dir"]);
         while($qp_tpl_file = readdir($qp_tpl_dir))
         {
          if (ereg(".qtf", $qp_tpl_file) AND !ereg(".qp.bak", $qp_tpl_file))
          {
           ?>
           <option value="<?php echo str_replace(".qtf", "", $qp_tpl_file); ?>"><?php echo ucfirst(str_replace(".qtf", "", $qp_tpl_file)); ?></option>
           <?php
          }
         }
        ?>
        <option value="raw">Raw file</option>
       </select>
      </td>
     </tr>
     <tr>
      <td class="tab-foot" width="100%" colspan="2" align="right">
       <input type="submit" value="Create">
      </td>
     </tr>
    </table>
   </form>
   
   <form action="createdir.php" method="POST">
    <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
    <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
    <table class="tab" width="50%">
     <tr>
      <td class="tab-head" width="30%">
       Create directory
      </td>
      <td width="70%">&nbsp;</td>
     </tr>
     <tr>
      <td class="tab-body" width="100%" colspan="2">
       Name: <input type="text" name="qp_dir">
      </td>
     </tr>
     <tr>
      <td class="tab-foot" width="100%" colspan="2" align="right">
       <input type="submit" value="Create">
      </td>
     </tr>
    </table>
   </form>
   
   <?php
   if ($upload <> "")
   {
    ?>
    <form action="uploadfiles.php" method="POST" enctype="multipart/form-data">
     <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
     <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
     <table class="tab" width="50%">
      <tr>
       <td class="tab-head" width="30%">
        Upload files
       </td>
       <td width="70%">&nbsp;</td>
      </tr>
      <tr>
       <td class="tab-body" width="100%" colspan="2">
        <?php 
        for($i = 0; $i < $upload; $i++)
        {
         ?>
         <br>
         <input type="file" name="userfile[]">
         <?php
        }
        ?>
       </td>
      </tr>
      <tr>
       <td class="tab-foot" width="100%" colspan="2" align="right">
        <input type="submit" value="Upload">
       </td>
      </tr>
     </table>
    </form>
    <?php
   }
   else
   {
    ?>
    <form method="POST">
     <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
     <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
     <table class="tab" width="50%">
      <tr>
       <td class="tab-head" width="30%">
        Upload files
       </td>
       <td width="70%">&nbsp;</td>
      </tr>
      <tr>
       <td class="tab-body" width="100%" colspan="2">
        I want to upload <input type="text" name="upload" size="2"> files.
       </td>
      </tr>
      <tr>
       <td class="tab-foot" width="100%" colspan="2" align="right">
        <input type="submit" value="Go">
       </td>
      </tr>
     </table>
    </form>
    <?php
   }
   ?>

   <form action="cpall.php" method="POST">
    <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
    <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
    <table class="tab" width="50%">
     <tr>
      <td class="tab-head" width="30%">
       Copy files
      </td>
      <td width="70%">&nbsp;</td>
     </tr>
     <tr>
      <td class="tab-body" width="100%" colspan="2">
       Copy all files in this directory to <?php echo $qp_parameters["userdata_dir"]; ?>/<input type="text" name="target">
      </td>
     </tr>
     <tr>
      <td class="tab-foot" width="100%" colspan="2" align="right">
       <input type="submit" value="Copy">
      </td>
     </tr>
    </table>
   </form>
   
   <form action="search.php" method="POST">
    <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
    <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
    <table class="tab" width="50%">
     <tr>
      <td class="tab-head" width="30%">
       Search
      </td>
      <td width="70%">&nbsp;</td>
     </tr>
     <tr>
      <td class="tab-body" width="100%" colspan="2">
       Search for
       <textarea name="qp_searchstring" rows="4" cols="20" wrap="off"></textarea>
      </td>
     </tr>
     <tr>
      <td class="tab-foot" width="100%" colspan="2" align="right">
       <input type="submit" value="Search">
      </td>
     </tr>
    </table>
   </form>

   <form action="replace.php" method="POST">
    <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
    <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
    <table class="tab" width="50%">
     <tr>
      <td class="tab-head" width="30%">
       Replace
      </td>
      <td width="70%">&nbsp;</td>
     </tr>
     <tr>
      <td class="tab-body" width="100%" colspan="2">
       Search for
       <textarea name="qp_searchstring" rows="4" cols="20" wrap="off"></textarea>
       <br>
       Replace with
       <textarea name="qp_replacestring" rows="4" cols="20" wrap="off"></textarea>
      </td>
     </tr>
     <tr>
      <td class="tab-foot" width="100%" colspan="2" align="right">
       <input type="submit" value="Replace">
      </td>
     </tr>
    </table>
   </form>
   
   <form action="parse.php" method="GET" target="parse_window">
    <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
    <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
    <table class="tab" width="50%">
     <tr>
      <td class="tab-head" width="30%">
       Parse files
      </td>
      <td width="70%">&nbsp;</td>
     </tr>
     <tr>
      <td class="tab-body" width="100%" colspan="2">
       Parsertemplate:
       <select name="template">
        <?php
         $qp_tpl_dir = @opendir($qp_parameters["template_dir"]);
         while($qp_tpl_file = readdir($qp_tpl_dir))
         {
          if (substr($qp_tpl_file, -4) == ".qpt" AND !ereg("lastmodified", $qp_tpl_file))
          {
           ?>
           <option value="<?php echo $qp_tpl_file; ?>"><?php echo ucfirst($qp_tpl_file); ?></option>
           <?php
          }
         }
        ?>
       </select>
       <br>
       <input type="checkbox" name="qp_dir" value="<?php echo $qp_current_dir; ?>"> Parse only this directory
       <input type="checkbox" name="mod" value="yes"> Parse only changed files
      </td>
     </tr>
     <tr>
      <td class="tab-foot" width="100%" colspan="2" align="right">
       <input type="submit" value="Parse">
      </td>
     </tr>
    </table>
   </form>
  
   <?php
  }
  ?>
 </body>
</html>
