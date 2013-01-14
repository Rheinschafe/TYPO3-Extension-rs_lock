#
# Table structure for table 'sys_lock'
#
CREATE TABLE sys_lock (
  hash varchar(40) DEFAULT '' NOT NULL,
  created_at int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (hash),
  KEY created_date_time (created_at)
) ENGINE=InnoDB;