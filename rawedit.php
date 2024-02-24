<?php

include("config.inc.php");
include("functions.inc.php");

$qp_user = qp_get_userdata($qp_sid);

if ($qp_current_dir == "")
{
 $qp_current_dir = $qp_input_dir;
}

if (!($fp = fopen($qp_current_dir."/".$qp_file, "r")))
{
 die("could not open file");
}
else
{
 qp_copy($qp_current_dir."/".$qp_file, $qp_current_dir."/".$qp_file.".".date("Ymd-His").".qp.bak");
 $qp_content = fread($fp, filesize($qp_current_dir."/".$qp_file));
 ?>
 <form action="rawsave.php" method="POST">
  <input type="hidden" name="qp_sid" value="<?php echo $qp_sid; ?>">
  <input type="hidden" name="qp_current_dir" value="<?php echo $qp_current_dir; ?>">
  <textarea name="qp_content" rows="<?php echo $qp_parameters["textfield_rows"]; ?>" cols="<?php echo $qp_parameters["textfield_cols"]; ?>" wrap="off"><?php echo htmlentities($qp_content, ENT_QUOTES); ?></textarea>
  <br>
  Filename: <input type="text" name="qp_file" value="<?php echo $qp_file; ?>">
  <input type="submit" value="Save file">
 </form>
 <?php
}
?>