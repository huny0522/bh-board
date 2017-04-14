<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class InstallController extends \BH_Controller{
	public function __Init(){
		if($GLOBALS['_BH_App']->InstallIs) exit;
	}

	public function Index(){
		$sql = array();
		$sql[]="
CREATE TABLE `".TABLE_BANNER."` (
	`seq` INT(11) NOT NULL,
	`category` VARCHAR(20) NOT NULL DEFAULT '',
	`subject` VARCHAR(50) NOT NULL DEFAULT '',
	`img` VARCHAR(50) NOT NULL DEFAULT '',
	`contents` TEXT NOT NULL,
	`type` ENUM('i','c') NOT NULL DEFAULT 'i',
	`begin_date` DATE NOT NULL,
	`end_date` DATE NOT NULL,
	`enabled` ENUM('y','n') NOT NULL DEFAULT 'n',
	`new_window` ENUM('y','n') NOT NULL DEFAULT 'n',
	`link_url` VARCHAR(256) NOT NULL DEFAULT '',
	`mlevel` TINYINT(4) NOT NULL DEFAULT '0',
	`sort` INT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`seq`),
	INDEX `category` (`category`),
	INDEX `begin_date` (`begin_date`),
	INDEX `end_date` (`end_date`),
	INDEX `enabled` (`enabled`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";

		$sql[] = "
CREATE TABLE `".TABLE_BOARD_MNG."` (
	`bid` VARCHAR(20) NOT NULL,
	`manager` VARCHAR(128) NOT NULL,
	`skin` VARCHAR(20) NOT NULL,
	`reply_skin` VARCHAR(20) NOT NULL,
	`subject` VARCHAR(128) NOT NULL,
	`category` VARCHAR(256) NOT NULL,
	`article_count` TINYINT(2) NOT NULL DEFAULT '10',
	`reply_count` TINYINT(2) NOT NULL DEFAULT '10',
	`auth_list_level` TINYINT(2) NOT NULL DEFAULT '10',
	`auth_write_level` TINYINT(2) NOT NULL DEFAULT '10',
	`auth_view_level` TINYINT(2) NOT NULL DEFAULT '10',
	`auth_reply_level` TINYINT(2) NOT NULL DEFAULT '10',
	`auth_answer_level` TINYINT(2) NOT NULL DEFAULT '10',
	`use_reply` ENUM('y','n') NOT NULL DEFAULT 'n',
	`list_in_view` ENUM('y','n') NOT NULL DEFAULT 'n',
	`layout` VARCHAR(50) NOT NULL,
	`man_to_man` ENUM('y','n') NOT NULL DEFAULT 'n',
	`new_view_day` TINYINT(2) NOT NULL DEFAULT '1',
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`bid`)
)
COMMENT='게시판 관리'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";

		$sql[] = "
CREATE TABLE `".TABLE_CONTENT."` (
	`bid` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`subject` VARCHAR(128) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`layout` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`html` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`hit` INT(11) NOT NULL DEFAULT '0',
	`recommend` INT(11) NOT NULL DEFAULT '0',
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`bid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";

		$sql[]= "
CREATE TABLE `".TABLE_IMAGES."` (
	`tid` VARCHAR(32) NOT NULL DEFAULT '',
	`article_seq` VARCHAR(64) NOT NULL DEFAULT '0',
	`seq` INT(10) NOT NULL DEFAULT '0',
	`image` VARCHAR(128) NOT NULL DEFAULT '',
	`imagename` VARCHAR(128) NOT NULL DEFAULT '',
	PRIMARY KEY (`tid`, `article_seq`, `seq`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";

		$sql[] = "
CREATE TABLE `".TABLE_MEMBER."` (
	`muid` INT(11) NOT NULL,
	`mid` VARCHAR(128) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`pwd` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`mname` VARCHAR(32) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`cname` VARCHAR(32) NOT NULL DEFAULT '',
	`nickname` VARCHAR(32) NOT NULL DEFAULT '',
	`level` TINYINT(2) NOT NULL DEFAULT '1',
	`address1` VARCHAR(128) NOT NULL DEFAULT '',
	`address2` VARCHAR(128) NOT NULL DEFAULT '',
	`zipcode` VARCHAR(12) NOT NULL DEFAULT '',
	`tel` VARCHAR(20) NOT NULL DEFAULT '',
	`phone` VARCHAR(20) NOT NULL DEFAULT '',
	`email` VARCHAR(128) NOT NULL DEFAULT '',
	`reg_date` DATETIME NOT NULL,
	`approve` ENUM('y','n') NOT NULL DEFAULT 'n',
	`admin_auth` TEXT NOT NULL,
	PRIMARY KEY (`muid`),
	UNIQUE INDEX `nickname` (`nickname`),
	UNIQUE INDEX `email` (`email`),
	UNIQUE INDEX `mid` (`mid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";

		$sql[] = "
CREATE TABLE `".TABLE_MENU."` (
	`category` VARBINARY(50) NOT NULL DEFAULT '0',
	`sort` INT(11) NOT NULL DEFAULT '0',
	`controller` VARCHAR(32) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`title` VARCHAR(64) NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
	`type` ENUM('customize','board','content') NOT NULL DEFAULT 'customize' COLLATE 'utf8_general_ci',
	`enabled` ENUM('y','n') NOT NULL DEFAULT 'y' COLLATE 'utf8_general_ci',
	`parent_enabled` ENUM('y','n') NOT NULL DEFAULT 'y' COLLATE 'utf8_general_ci',
	`bid` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	PRIMARY KEY (`category`),
	INDEX `controller` (`controller`),
	INDEX `enabled` (`enabled`),
	INDEX `parent_enabled` (`parent_enabled`),
	INDEX `sort` (`sort`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";

		$sql[]= "
CREATE TABLE `".TABLE_POPUP."` (
	`seq` INT(11) NOT NULL,
	`subject` VARCHAR(50) NOT NULL DEFAULT '',
	`img` VARCHAR(50) NOT NULL DEFAULT '',
	`contents` TEXT NOT NULL,
	`type` ENUM('i','c') NOT NULL DEFAULT 'i',
	`begin_date` DATE NOT NULL,
	`end_date` DATE NOT NULL,
	`enabled` ENUM('y','n') NOT NULL DEFAULT 'n',
	`new_window` ENUM('y','n') NOT NULL DEFAULT 'n',
	`link_url` VARCHAR(256) NOT NULL DEFAULT '',
	`mlevel` TINYINT(4) NOT NULL DEFAULT '0',
	`width` SMALLINT(6) NOT NULL DEFAULT '0',
	`height` SMALLINT(6) NOT NULL DEFAULT '0',
	`sort` INT(6) NOT NULL DEFAULT '0',
	PRIMARY KEY (`seq`),
	INDEX `begin_date` (`begin_date`),
	INDEX `end_date` (`end_date`),
	INDEX `enabled` (`enabled`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";

		$sql[]= "
CREATE TABLE `".TABLE_WITHDRAW_MEMBER."` (
	`muid` INT(11) NOT NULL,
	`mid` VARCHAR(128) NOT NULL DEFAULT '',
	`mname` VARCHAR(32) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`cname` VARCHAR(32) NOT NULL DEFAULT '',
	`nickname` VARCHAR(32) NOT NULL DEFAULT '',
	`level` TINYINT(2) NOT NULL DEFAULT '1',
	`email` VARCHAR(128) NOT NULL DEFAULT '',
	`reason` TEXT NOT NULL,
	`reg_date` DATETIME NOT NULL,
	`w_date` DATETIME NOT NULL COMMENT '탈퇴일',
	PRIMARY KEY (`muid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";
		foreach($sql as $s){
			@SqlQuery($s);
		}


		$sql2[] = "INSERT INTO `".TABLE_MEMBER."` (`muid`, `mid`, `pwd`, `mname`, `cname`, `nickname`, `level`, `reg_date`, `approve`, `email`, `admin_auth`)
 			SELECT "._DBMAXINT.", 'admin', PASSWORD('12341234'), '관리자', '관리자', '관리자', 18, NOW(), 'y', 'admin@admin.com', '001,001001,001002,001003,002,003,005'";

		$sql2[] = "INSERT INTO `".TABLE_MEMBER."` (`muid`, `mid`, `pwd`, `mname`, `cname`, `nickname`, `level`, `reg_date`, `approve`, `email`)
 			SELECT ".(_DBMAXINT - 1).", 'developer', PASSWORD('12341234'), '개발자', '개발자', '개발자', 20, NOW(), 'y', 'developer@admin.com'";

		$sql2[] = "INSERT INTO `".TABLE_MENU."` (`category`, `sort`, `controller`, `title`, `type`, `enabled`, `parent_enabled`, `bid`) VALUES ('00000', 0, 'Home', 'Home', 'customize', 'y', 'y', '')";

		foreach($sql2 as $s){
			SqlQuery($s);
		}

		Redirect(_URL.'/');
	}
}

