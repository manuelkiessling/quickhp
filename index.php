<?php

include("config.inc.php");
include("functions.inc.php");

?>
<html>
 <head>
  <title>
   Login - QuickHP <?php echo $qp_parameters["version"]; ?>
  </title>
  <link rel="stylesheet" type="text/css" href="css/main.css">
 </head>
 <body>
  <center>
   <br>
   <br>
   <br>
   <p class="error">
    <?php echo $error; ?>
   </p>
   <form action="login.php" method="POST">
    <font face="Arial" size="2">
     Name (Area):
    </font>
    <br>
    <select name="name">
     <?php
     $result = mysql_query("SELECT name FROM qp_users ORDER BY name");
     while($a = mysql_fetch_array($result))
     {
      echo "<option value=\"".$a["name"]."\">".$a["name"]."</option>";
     }
     ?>
    </select>
    <br>
    <br>
    <font face="Arial" size="2">
     Passwort:
    </font>
    <br>
    <input type="password" name="password">
    <br>
    <br>
    <input type="submit" value="Einloggen">
   </form>
  </center>
 </body>
</html>
