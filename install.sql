CREATE TABLE qp_users (
  id int(11) NOT NULL auto_increment,
  name varchar(128) NOT NULL default '',
  email varchar(255) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  homedir text NOT NULL,
  sid varchar(32) NOT NULL default '',
  flag_ftpallowed tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY sid (sid),
  KEY sid_2 (sid)
) TYPE=MyISAM;

INSERT INTO qp_users VALUES (1,'Admin','you@yourdomain.tld','password','','',1);
