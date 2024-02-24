<?php

magic_quotes_runtime(0);
error_reporting(E_PARSE);

$qp_parameters["parseperturn"] = -1;
$qp_parameters["ftpperturn"] = -1;

$qp_parameters["textfield_rows"] = 35;
$qp_parameters["textfield_cols"] = 120;

$qp_parameters["version"] = "0.3";

$qp_parameters["userdata_dir"] = "userdata";
$qp_parameters["input_dir"] = $qp_parameters["userdata_dir"]."/input";
$qp_parameters["output_dir"] = $qp_parameters["userdata_dir"]."/output";
$qp_parameters["template_dir"] = $qp_parameters["userdata_dir"]."/templates";

$qp_parameters["document_tagname"] = "qp_document";
$qp_parameters["system_variables_tagname"] = "qp_system_variables";
$qp_parameters["user_variables_tagname"] = "qp_user_variables";
$qp_parameters["content_tagname"] = "qp_content";

$qp_parameters["ftp_server"] = "127.0.0.1";
$qp_parameters["ftp_user"] = "username";
$qp_parameters["ftp_password"] = "password";
$qp_parameters["ftp_root"] = "";
$qp_parameters["fopen_ftpstring"] = "ftp://".$qp_parameters["ftp_user"].":".$qp_parameters["ftp_password"]."@".$qp_parameters["ftp_server"]."/".$qp_parameters["ftp_root"];

$qp_parameters["check_xml"] = TRUE;

mysql_connect("localhost", "username", "password");
mysql_select_db("quickhp");

?>