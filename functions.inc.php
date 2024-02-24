<?php

function qp_xmlcheck_startElement($parser, $name, $attrs)
{
 return true;
}

function qp_xmlcheck_endElement($parser, $name)
{
 return true;
}

function qp_xml_check($xmldata)
{
 $xml_parser = xml_parser_create();
 xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
 xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
 xml_set_element_handler($xml_parser, "qp_xmlcheck_startElement", "qp_xmlcheck_endElement");
 if (!xml_parse($xml_parser, $xmldata))
 {
  return(sprintf("XML error: %s in file $val at line %d",
  xml_error_string(xml_get_error_code($xml_parser)),
  xml_get_current_line_number($xml_parser)));
 }
 else
 {
  return "TRUE";
 }
 xml_parser_free($xml_parser);
}

function qp_get_userdata($qp_sid)
{
 $result = mysql_query("SELECT name, email, homedir, flag_ftpallowed FROM qp_users WHERE sid = '$qp_sid'");
 $a = mysql_fetch_array($result);
 if ($a["name"] == "")
 {
  die("<p class=\"error\">Your session is not valid.</p>\n");
 }
 else
 {
  return $a;
 }
}

function qp_display_perms($mode) 
{ 
 // thanks to pwalker@pwccanada.com
 /* Determine Type */ 
 if(($mode & 0xC000) === 0xC000) // Unix domain socket 
 $type = 's'; 
 elseif(($mode & 0x4000) === 0x4000) // Directory 
 $type = 'd'; 
 elseif(($mode & 0xA000) === 0xA000) // Symbolic link 
 $type = 'l'; 
 elseif(($mode & 0x8000) === 0x8000) // Regular file 
 $type = '-'; 
 elseif(($mode & 0x6000) === 0x6000) // Block special file 
 $type = 'b'; 
 elseif(($mode & 0x2000) === 0x2000) // Character special file 
 $type = 'c'; 
 elseif(($mode & 0x1000) === 0x1000) // Named pipe 
 $type = 'p'; 
 else // Unknown 
 $type = '?';
 
 /* Determine permissions */ 
 $owner["read"] = ($mode & 00400) ? 'r' : '-'; 
 $owner["write"] = ($mode & 00200) ? 'w' : '-'; 
 $owner["execute"] = ($mode & 00100) ? 'x' : '-'; 
 $group["read"] = ($mode & 00040) ? 'r' : '-'; 
 $group["write"] = ($mode & 00020) ? 'w' : '-'; 
 $group["execute"] = ($mode & 00010) ? 'x' : '-'; 
 $world["read"] = ($mode & 00004) ? 'r' : '-'; 
 $world["write"] = ($mode & 00002) ? 'w' : '-'; 
 $world["execute"] = ($mode & 00001) ? 'x' : '-'; 
 
 /* Adjust for SUID, SGID and sticky bit */ 
 if( $mode & 0x800 ) 
 $owner["execute"] = ($owner[execute]=='x') ? 's' : 'S'; 
 if( $mode & 0x400 ) 
 $group["execute"] = ($group[execute]=='x') ? 's' : 'S'; 
 if( $mode & 0x200 ) 
 $world["execute"] = ($world[execute]=='x') ? 't' : 'T'; 
 
 return $permstring = $type.$owner[read].$owner[write].$owner[execute].$group[read].$group[write].$group[execute].$world[read].$world[write].$world[execute]; 
} 


// this function returns all files in the passed directory - it's recursive when you set $recursive = 1 and lists dirs if $withdirs = 1
function recurse_dir($dirname, $recursive, $withdirs)
{ 
 $delim = (strstr($dirname,"/")) ? "/" : "\\"; 
 if($dirname[strlen($dirname)-1]!=$delim)
 {
  $dirname .= $delim;
 }

 $handle = opendir($dirname);

 while ($file = readdir($handle))
 {
  if($file=='.'||$file=='..')
  {
   continue;
  }
  if(is_dir($dirname.$file) && $recursive)
  {
   $x = recurse_dir($dirname.$file.$delim, 1, $withdirs);
   $result_array = array_merge($result_array, $x);
  }
  else
  {
   $result_array[]=$dirname.$file;
  }
 }
 closedir($handle);
 if ($withdirs == 1)
 {
  $result_array[] = substr($dirname, 0, strlen($dirname) - 1);
 }
 return $result_array;
}

function qp_ftp_put($content, $file)
{
 GLOBAL $qp_parameters;

 $ftp = new ftp();

 $ftp->debug = FALSE;

 if (!$ftp->ftp_connect($qp_parameters["ftp_server"]))
 {
 	die("Cannot connect\n");
 }

 if ($ftp->ftp_login($qp_parameters["ftp_user"], $qp_parameters["ftp_password"]))
 {
 	//echo "Login succeeded\n";
 } else {
 	$ftp->ftp_quit();
 	die("Login failed\n");
 }
 
 if ($pwd = $ftp->ftp_pwd())
 {
 	//echo "Current directory is ".$pwd."\n";
 } else {
 	$ftp->ftp_quit();
 	die("Error!!\n");
 }
 
 if ($sys = $ftp->ftp_systype())
 {
 	//echo "Remote system is ".$sys."\n";
 } else {
 	$ftp->ftp_quit();
 	die("Error!!\n");
 }

 $fp = fopen("tmp/ftp.tmp", "w");
 fputs ($fp, $content);
 fclose($fp);

 $remote_filename = $qp_parameters["ftp_root"]."/".$file;
 $local_filename = "tmp/ftp.tmp";

 if ($ftp->ftp_put($remote_filename, $local_filename))
 {
 	//echo $local_filename." has been uploaded as ".$remote_filename."\n";
 } else {
 	$ftp->ftp_quit();
 	die("Error!!\n");
 }

 $ftp->ftp_quit();
 unlink("tmp/ftp.tmp");
}

function qp_copy($source, $target)
{
 GLOBAL $qp_parameters;
 $fps = fopen($source, "r");
 $content = fread($fps, filesize($source));
 fclose($fps);
 @qp_unlink($target);
 $fpt = fopen($qp_parameters["fopen_ftpstring"]."/".$target, "w");
 fputs($fpt, $content);
 fclose($fpt);
 return TRUE;
}

function qp_unlink($path)
{
 GLOBAL $qp_parameters;
 $conn_id = ftp_connect($qp_parameters["ftp_server"]); 
 $login_result = ftp_login($conn_id, $qp_parameters["ftp_user"], $qp_parameters["ftp_password"]); 
 if((!$conn_id) || (!$login_result))
 {
  echo "Ftp-Verbindung nicht hergestellt!";
  echo "Verbindung mit {$qp_parameters["ftp_server"]} als Benutzer {$qp_parameters["ftp_user"]} nicht möglich"; 
  die;
 }
 if (is_file($path))
 {
  if (!@ftp_delete ($conn_id, $qp_parameters["ftp_root"]."/".$path))
  {
   //echo "failed to delete $path...<br>\n";
   ftp_quit($conn_id);
  }
 }
 else
 {
  if (!@ftp_rmdir ($conn_id, $qp_parameters["ftp_root"]."/".$path))
  {
   //echo "failed to delete $path...<br>\n";
  }
 }
 ftp_quit($conn_id);
 return TRUE;
}

function qp_rmdir($dir)
{
 $return = TRUE;
 $files = recurse_dir($dir, 1, 1);
 while (list ($key, $val) = each ($files))
 {
  if (!qp_unlink ($val))
  {
   $return = FALSE;
  }
 }
 return $return;
}

function qp_mkdir($dir)
{
 GLOBAL $qp_parameters;
 $conn_id = ftp_connect($qp_parameters["ftp_server"]); 
 $login_result = ftp_login($conn_id, $qp_parameters["ftp_user"], $qp_parameters["ftp_password"]);
 if((!$conn_id) || (!$login_result))
 {
  echo "Ftp-Verbindung nicht hergestellt!";
  echo "Verbindung mit {$qp_parameters["ftp_server"]} als Benutzer {$qp_parameters["ftp_user"]} nicht möglich";
  die; 
 }
 if (!@ftp_mkdir ($conn_id, $qp_parameters["ftp_root"]."/".$dir))
 {
  echo "failed to create $dir...<br>\n";
 }
 ftp_quit($conn_id);
 return TRUE;
}


// force the creation of a directory
function force_dir($path)
{ 
 if (strlen($path) == 0)
 {
  return 0; 
 } 
 elseif (@is_dir($path))
 { 
  return 1; // avoid 'xyz:\' problem.
 } 
 elseif (dirname($path) == $path)
 { 
  return 1; // avoid 'xyz:\' problem. 
 }
 return (force_dir(dirname($path)) AND qp_mkdir($path)); 
}


function analysedir($dirline) 
{ 
 global $systyp,$ftp_server,$stop; 

 if(ereg("([-dl])[rwxst-]{9}",substr($dirline,0,10)))
 { 
  $systyp = "UNIX";
 } 

 if(substr($dirline,0,5) == "total")
 { 
  $dirinfo[0] = -1; 
 }
 elseif($systyp=="Windows_NT")
 { 
  if(ereg("[-0-9]+ *[0-9:]+[PA]?M? +<DIR> {10}(.*)",$dirline,$regs))
  { 
   $dirinfo[0] = 1; 
   $dirinfo[1] = 0; 
   $dirinfo[2] = $regs[1]; 
  }
  elseif(ereg("[-0-9]+ *[0-9:]+[PA]?M? +([0-9]+) (.*)",$dirline,$regs))
  { 
   $dirinfo[0] = 0; 
   $dirinfo[1] = $regs[1]; 
   $dirinfo[2] = $regs[2]; 
  } 
 }
 elseif($systyp=="UNIX")
 { 
  if(ereg("([-d])[rwxst-]{9}.* ([0-9]*) [a-zA-Z]+ [0-9: ]*[0-9] (.+)",$dirline,$regs))
  { 
   if($regs[1]=="d") $dirinfo[0] = 1; 
   $dirinfo[1] = $regs[2]; 
   $dirinfo[2] = $regs[3]; 
  } 
 }

 if(($dirinfo[2]==".")||($dirinfo[2]=="..")) $dirinfo[0]=0; 

 // array -> 0 = switch, directory or not 
 // array -> 1 = filesize (if dir =0) 
 // array -> 2 = filename or dirname 

 return $dirinfo; 
} 

class ftp
{
	/* Public variables */
	var $debug;
	var $umask;
	var $timeout;

	/* Private variables */
	var $ftp_sock;
	var $ftp_resp;

	/* Constractor */
	function ftp()
	{
		$this->debug = FALSE;
		$this->umask = 0022;
		$this->timeout = 30;

		if (!defined("FTP_BINARY")) {
			define("FTP_BINARY", 1);
		}
		if (!defined("FTP_ASCII")) {
			define("FTP_ASCII", 0);
		}

		$this->ftp_resp = "";
	}

	/* Public functions */
	function ftp_connect($server, $port = 21)
	{
		$this->ftp_debug("Trying to ".$server.":".$port." ...\n");
		$this->ftp_sock = @fsockopen($server, $port, $errno, $errstr, $this->timeout);

		if (!$this->ftp_sock || !$this->ftp_ok()) {
			$this->ftp_debug("Cannot connect to remote host \"".$server.":".$port."\"\n");
			$this->ftp_debug("Error : ".$errstr." (".$errno.")\n");
			return FALSE;
		}
		$this->ftp_debug("Connected to remote host \"".$server.":".$port."\"\n");

		return TRUE;
	}

	function ftp_login($user, $pass)
	{
		$this->ftp_putcmd("USER", $user);
		if (!$this->ftp_ok()) {
			$this->ftp_debug("Error : USER command failed\n");
			return FALSE;
		}

		$this->ftp_putcmd("PASS", $pass);
		if (!$this->ftp_ok()) {
			$this->ftp_debug("Error : Authentication failed\n");
			return FALSE;
		}
		$this->ftp_debug("Authentication succeeded\n");

		return TRUE;
	}

	function ftp_pwd()
	{
		$this->ftp_putcmd("PWD");
		if (!$this->ftp_ok()) {
			return FALSE;
		}

		return ereg_replace("^[0-9]{3} \"(.+)\" .+\r\n", "\\1", $this->ftp_resp);
	}

	function ftp_size($pathname)
	{
		$this->ftp_putcmd("SIZE", $pathname);
		if (!$this->ftp_ok()) {
			return -1;
		}

		return ereg_replace("^[0-9]{3} ([0-9]+)\r\n", "\\1", $this->ftp_resp);
	}

	function ftp_mdtm($pathname)
	{
		$this->ftp_putcmd("MDTM", $pathname);
		if (!$this->ftp_ok()) {
			return -1;
		}
		$mdtm = ereg_replace("^[0-9]{3} ([0-9]+)\r\n", "\\1", $this->ftp_resp);
		$date = sscanf($mdtm, "%4d%2d%2d%2d%2d%2d");
		$timestamp = mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);

		return $timestamp;
	}

	function ftp_systype()
	{
		$this->ftp_putcmd("SYST");
		if (!$this->ftp_ok()) {
			return FALSE;
		}
		$DATA = explode(" ", $this->ftp_resp);

		return $DATA[1];
	}

	function ftp_cdup()
	{
		$this->ftp_putcmd("CDUP");
		return $this->ftp_ok();
	}

	function ftp_chdir($pathname)
	{
		$this->ftp_putcmd("CWD", $pathname);
		return $this->ftp_ok();
	}

	function ftp_delete($pathname)
	{
		$this->ftp_putcmd("DELE", $pathname);
		return $this->ftp_ok();
	}

	function ftp_rmdir($pathname)
	{
		$this->ftp_putcmd("RMD", $pathname);
		return $this->ftp_ok();
	}

	function ftp_mkdir($pathname)
	{
		$this->ftp_putcmd("MKD", $pathname);
		return $this->ftp_ok();
	}

	function ftp_file_exists($pathname)
	{
		if (!($remote_list = $this->ftp_nlist("-a"))) {
			$this->ftp_debug("Error : Cannot get remote file list\n");
			return -1;
		}
		
		reset($remote_list);
		while (list(,$value) = each($remote_list)) {
			if ($value == $pathname) {
				$this->ftp_debug("Remote file ".$pathname." exists\n");
				return 1;
			}
		}
		$this->ftp_debug("Remote file ".$pathname." does not exist\n");

		return 0;
	}

	function ftp_rename($from, $to)
	{
		$this->ftp_putcmd("RNFR", $from);
		if (!$this->ftp_ok()) {
			return FALSE;
		}
		$this->ftp_putcmd("RNTO", $to);

		return $this->ftp_ok();
	}

	function ftp_nlist($arg = "", $pathname = "")
	{
		if (!($string = $this->ftp_pasv())) {
			return FALSE;
		}

		if ($arg == "") {
			$nlst = "NLST";
		} else {
			$nlst = "NLST ".$arg;
		}
		$this->ftp_putcmd($nlst, $pathname);

		$sock_data = $this->ftp_open_data_connection($string);
		if (!$sock_data || !$this->ftp_ok()) {
			$this->ftp_debug("Cannot connect to remote host\n");
			return FALSE;
		}
		$this->ftp_debug("Connected to remote host\n");

		while (!feof($sock_data)) {
			$list[] = ereg_replace("[\r\n]", "", fgets($sock_data, 512));
		}
		$this->ftp_close_data_connection($sock_data);
		$this->ftp_debug(implode("\n", $list));

		if (!$this->ftp_ok()) {
			return FALSE;
		}

		return $list;
	}

	function ftp_rawlist($pathname = "")
	{
		if (!($string = $this->ftp_pasv())) {
			return FALSE;
		}

		$this->ftp_putcmd("LIST", $pathname);

		$sock_data = $this->ftp_open_data_connection($string);
		if (!$sock_data || !$this->ftp_ok()) {
			$this->ftp_debug("Cannot connect to remote host\n");
			return FALSE;
		}

		$this->ftp_debug("Connected to remote host\n");

		while (!feof($sock_data)) {
			$list[] = ereg_replace("[\r\n]", "", fgets($sock_data, 512));
		}
		$this->ftp_debug(implode("\n", $list));
		$this->ftp_close_data_connection($sock_data);

		if (!$this->ftp_ok()) {
			return FALSE;
		}

		return $list;
	}

	function ftp_get($localfile, $remotefile, $mode = 1)
	{
		umask($this->umask);

		if (@file_exists($localfile)) {
			$this->ftp_debug("Warning : local file will be overwritten\n");
		}

		$fp = @fopen($localfile, "w");
		if (!$fp) {
			$this->ftp_debug("Error : Cannot create \"".$localfile."\"");
			$this->ftp_debug("Error : GET failed\n");
			return FALSE;
		}

		if (!$this->ftp_type($mode)) {
			return FALSE;
		}

		if (!($string = $this->ftp_pasv())) {
			return FALSE;
		}

		$this->ftp_putcmd("RETR", $remotefile);

		$sock_data = $this->ftp_open_data_connection($string);
		if (!$sock_data || !$this->ftp_ok()) {
			$this->ftp_debug("Cannot connect to remote host\n");
			$this->ftp_debug("Error : GET failed\n");
			return FALSE;
		}
		$this->ftp_debug("Connected to remote host\n");
		$this->ftp_debug("Retrieving remote file \"".$remotefile."\" to local file \"".$localfile."\"\n");
		while (!feof($sock_data)) {
			fputs($fp, fread($sock_data, 4096));
		}
		fclose($fp);

		$this->ftp_close_data_connection($sock_data);

		return $this->ftp_ok();
	}

	function ftp_put($remotefile, $localfile, $mode = 1)
	{
		
		if (!@file_exists($localfile)) {
			$this->ftp_debug("Error : No such file or directory \"".$localfile."\"\n");
			$this->ftp_debug("Error : PUT failed\n");
			return FALSE;
		}

		$fp = @fopen($localfile, "r");
		if (!$fp) {
			$this->ftp_debug("Cannot read file \"".$localfile."\"\n");
			$this->ftp_debug("Error : PUT failed\n");
			return FALSE;
		}

		if (!$this->ftp_type($mode)) {
			return FALSE;
		}

		if (!($string = $this->ftp_pasv())) {
			return FALSE;
		}

		$this->ftp_putcmd("STOR", $remotefile);

		$sock_data = $this->ftp_open_data_connection($string);
		if (!$sock_data || !$this->ftp_ok()) {
			$this->ftp_debug("Cannot connect to remote host\n");
			$this->ftp_debug("Error : PUT failed\n");
			return FALSE;
		}
		$this->ftp_debug("Connected to remote host\n");
		$this->ftp_debug("Storing local file \"".$localfile."\" to remote file \"".$remotefile."\"\n");
		while (!feof($fp)) {
			fputs($sock_data, fread($fp, 4096));
		}
		fclose($fp);

		$this->ftp_close_data_connection($sock_data);

		return $this->ftp_ok();
	}

	function ftp_site($command)
	{
		$this->ftp_putcmd("SITE", $command);
		return $this->ftp_ok();
	}

	function ftp_quit()
	{
		$this->ftp_putcmd("QUIT");
		if (!$this->ftp_ok() || !fclose($this->ftp_sock)) {
			$this->ftp_debug("QUIT command failed\n");
			return FALSE;
		}
		$this->ftp_debug("Disconnected from remote host\n");
		return TRUE;
	}

	/* Private Functions */

	function ftp_type($mode)
	{
		if ($mode) {
			$type = "I"; //Binary mode
		} else {
			$type = "A"; //ASCII mode
		}
		$this->ftp_putcmd("TYPE", $type);

		return $this->ftp_ok();
	}

	function ftp_pasv()
	{
		$this->ftp_putcmd("PASV");
		if (!$this->ftp_ok()) {
			return FALSE;
		}

		return $this->ftp_resp;
	}

	function ftp_putcmd($cmd, $arg = "")
	{
		if ($arg != "") {
			$cmd = $cmd." ".$arg;
		}

		fputs($this->ftp_sock, $cmd."\r\n");
		$this->ftp_debug("> ".$cmd."\n");

		return TRUE;
	}

	function ftp_ok()
	{
		$this->ftp_resp = "";
		do {
			$res = fgets($this->ftp_sock, 512);
			$this->ftp_resp .= $res;
		} while (substr($res, 3, 1) != " ");

		$this->ftp_debug(str_replace("\r\n", "\n", $this->ftp_resp));

		if (!ereg("^[123]", $this->ftp_resp)) {
			return FALSE;
		}

		return TRUE;
	}

	function ftp_close_data_connection($sock)
	{
		$this->ftp_debug("Disconnected from remote host\n");
		return fclose($sock);
	}

	function ftp_open_data_connection($string)
	{
		$string = ereg_replace("^.+ \\(?([0-9]+,[0-9]+,[0-9]+,[0-9]+,[0-9]+,[0-9]+)\\)?.*\r\n$", "\\1", $string);
		$DATA   = explode(",", $string);
		$ipaddr = $DATA[0].".".$DATA[1].".".$DATA[2].".".$DATA[3];
		$port   = $DATA[4]*256 + $DATA[5];
		$this->ftp_debug("Trying to ".$ipaddr.":".$port." ...\n");
		$data_connection = @fsockopen($ipaddr, $port, $errno, $errstr);
		if (!$data_connection) {
			$this->ftp_debug("Error : Cannot open data connection to ".$ipaddr.":".$port."\n");
			$this->ftp_debug("Error : ".$errstr." (".$errno.")\n");
			return FALSE;
		}

		return $data_connection;
	}

	function ftp_debug($message = "")
	{
		if ($this->debug) {
			echo $message;
		}

		return TRUE;
	}
}

?>