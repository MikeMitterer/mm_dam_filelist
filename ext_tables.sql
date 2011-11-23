#
# Table structure for table 'tt_content'
#
# Old-Version 
#CREATE TABLE tt_content (
#	tx_mmdamfilelist_mm_dam_category blob NOT NULL
#);


#
# Table structure for table 'tx_dam'
#
CREATE TABLE tx_dam (
    tx_mmdamfilelis_address_uid int(11) DEFAULT '0' NOT NULL,
	tx_mmdamfilelist_altpreview tinytext NOT NULL,
	tx_mmdamfilelist_oldfilehash tinytext NOT NULL,
);

#
# Table structure for table 'tx_mmdamfilelist_additionalinfo'
#
CREATE TABLE tx_mmdamfilelist_additionalinfo (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,

    uniqueid tinytext NOT NULL,
	feuser tinytext NOT NULL,
	filemd5 tinytext NOT NULL,
	src tinytext NOT NULL,
	target tinytext NOT NULL,
	damid tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);