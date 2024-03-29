<?php
if(!defined('_BH_') || _BH_ !== true || !defined('_IS_DEVELOPER_IP') || _IS_DEVELOPER_IP !== true) return;
$developerDefaultPassword = '12341234';
$adminDefaultPassword = '12341234';

$tables = array();

$qry = DB::SQL()->Query('SHOW TABLES');
while($row = DB::SQL()->Fetch($qry)){
	$tables[] = reset($row);
}

if(!in_array(TABLE_FRAMEWORK_SETTING, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_FRAMEWORK_SETTING."` (
			`key_name` VARBINARY(64) NOT NULL DEFAULT '',
			`data` TEXT NOT NULL,
			PRIMARY KEY (`key_name`)
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");
}
if(!in_array(TABLE_BANNER, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_BANNER."` (
			`seq` INT(11) NOT NULL AUTO_INCREMENT,
			`category` VARCHAR(20) NOT NULL DEFAULT '',
			`kind` VARCHAR(32) NOT NULL DEFAULT '',
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
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");
}

if(!in_array(TABLE_BOARD_MNG, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_BOARD_MNG."` (
			`bid` VARCHAR(20) NOT NULL DEFAULT '',
			`subid` VARCHAR(20) NOT NULL DEFAULT '',
			`group_name` VARCHAR(128) NOT NULL DEFAULT '',
			`manager` VARCHAR(128) NOT NULL DEFAULT '',
			`skin` VARCHAR(20) NOT NULL DEFAULT '',
			`reply_skin` VARCHAR(20) NOT NULL DEFAULT '',
			`subject` VARCHAR(128) NOT NULL DEFAULT '',
			`category` VARCHAR(256) NOT NULL DEFAULT '',
			`sub_category` TEXT NOT NULL,
			`use_sub_category` ENUM('y', 'n') NOT NULL DEFAULT 'n' COMMENT '세부 분류 사용여부',
			`article_count` TINYINT(2) NOT NULL DEFAULT '10',
			`reply_count` TINYINT(2) NOT NULL DEFAULT '10',
			`auth_list_level` TINYINT(2) NOT NULL DEFAULT '10',
			`auth_write_level` TINYINT(2) NOT NULL DEFAULT '10',
			`auth_view_level` TINYINT(2) NOT NULL DEFAULT '10',
			`auth_reply_level` TINYINT(2) NOT NULL DEFAULT '10',
			`auth_answer_level` TINYINT(2) NOT NULL DEFAULT '10',
			`use_reply` ENUM('y','n') NOT NULL DEFAULT 'n',
			`use_html` ENUM('y','n') NOT NULL DEFAULT 'y',
			`list_in_view` ENUM('y','n') NOT NULL DEFAULT 'n',
			`list_show_notice` ENUM('y','n') NOT NULL DEFAULT 'n',
			`layout` VARCHAR(50) NOT NULL DEFAULT '',
			`man_to_man` ENUM('y','n') NOT NULL DEFAULT 'n',
			`use_secret` ENUM('y','n') NOT NULL DEFAULT 'n',
			`new_view_day` TINYINT(2) NOT NULL DEFAULT '1',
			`attach_type` ENUM('normal','image') NOT NULL DEFAULT 'normal',
			`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
			PRIMARY KEY (`bid`,`subid`),
			INDEX `group_name` (`group_name`, `bid`,`subid`)
		)
		COMMENT='게시판 관리'
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");
}

if(!in_array(TABLE_CONTENT, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_CONTENT."` (
			`bid` VARCHAR(20) NOT NULL DEFAULT '',
			`category` VARCHAR(256) NOT NULL DEFAULT '',
			`subject` VARCHAR(128) NOT NULL DEFAULT '',
			`layout` VARCHAR(50) NOT NULL DEFAULT '',
			`html` VARCHAR(256) NOT NULL DEFAULT '',
			`hit` INT(11) NOT NULL DEFAULT '0',
			`recommend` INT(11) NOT NULL DEFAULT '0',
			`read` INT(11) NOT NULL DEFAULT '0',
			`oppose` INT(11) NOT NULL DEFAULT '0',
			`scrap` INT(11) NOT NULL DEFAULT '0',
			`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
			PRIMARY KEY (`bid`),
			INDEX `category` (`category`)
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");

	$contentsModel = new \ContentModel();
	$contentsModel->_bid->SetValue('introduce');
	$contentsModel->_category->SetValue('기본');
	$contentsModel->_subject->SetValue('회사소개');
	$contentsModel->_html->SetValue('Introduce');
	$contentsModel->_reg_date->SetValue(date('Y-m-d H:i:s'));
	$contentsModel->DBInsert();

	$contentsModel->_bid->SetValue('privacy');
	$contentsModel->_subject->SetValue('개인정보보호정책');
	$contentsModel->_html->SetValue('Privacy');
	$contentsModel->DBInsert();

	$contentsModel->_bid->SetValue('terms');
	$contentsModel->_subject->SetValue('이용약관');
	$contentsModel->_html->SetValue('Terms');
	$contentsModel->DBInsert();
}

if(!in_array(TABLE_IMAGES, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_IMAGES."` (
			`tid` VARCHAR(32) NOT NULL DEFAULT '',
			`article_seq` VARCHAR(64) NOT NULL DEFAULT '0',
			`seq` INT(10) NOT NULL DEFAULT '0',
			`image` VARCHAR(128) NOT NULL DEFAULT '',
			`imagename` VARCHAR(128) NOT NULL DEFAULT '',
			PRIMARY KEY (`tid`, `article_seq`, `seq`)
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");
}

if(!in_array(TABLE_MEMBER, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_MEMBER."` (
			`muid` INT(11) NOT NULL DEFAULT '0',
			`mid` VARCHAR(128) NULL DEFAULT NULL,
			`pwd` VARCHAR(64) NOT NULL DEFAULT '',
			`mname` VARCHAR(32) NOT NULL DEFAULT '',
			`cname` VARCHAR(32) NOT NULL DEFAULT '',
			`nickname` VARCHAR(32) NULL DEFAULT NULL,
			`level` TINYINT(2) NOT NULL DEFAULT '1',
			`address1` VARCHAR(128) NOT NULL DEFAULT '',
			`address2` VARCHAR(128) NOT NULL DEFAULT '',
			`zipcode` VARCHAR(12) NOT NULL DEFAULT '',
			`tel` VARCHAR(20) NOT NULL DEFAULT '',
			`phone` VARCHAR(20) NOT NULL DEFAULT '',
			`email` VARCHAR(128) NULL DEFAULT NULL,
			`photo1` VARCHAR(128) NOT NULL DEFAULT '',
			`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
			`login_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
			`approve` ENUM('y','n') NOT NULL DEFAULT 'n',
			`withdraw` ENUM('y','n') NOT NULL DEFAULT 'n',
			`admin_auth` TEXT NOT NULL,
			`pw_reset_code` VARCHAR(128) NOT NULL DEFAULT '',
			`email_code` VARCHAR(128) NOT NULL DEFAULT '',
			PRIMARY KEY (`muid`),
			UNIQUE INDEX `nickname` (`nickname`),
			UNIQUE INDEX `email` (`email`),
			UNIQUE INDEX `mid` (`mid`)
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");

	DB::SQL()->Query("INSERT INTO `".TABLE_MEMBER."` (`muid`, `mid`, `pwd`, `mname`, `cname`, `nickname`, `level`, `reg_date`, `approve`, `email`, `admin_auth`)
 			SELECT "._DBMAXINT.", 'admin', '"._password_hash($adminDefaultPassword)."', '관리자', '관리자', '관리자', 18, NOW(), 'y', 'admin@admin.com', '001,001001,001002,001003,002,003,005'");

	DB::SQL()->Query("INSERT INTO `".TABLE_MEMBER."` (`muid`, `mid`, `pwd`, `mname`, `cname`, `nickname`, `level`, `reg_date`, `approve`, `email`, `admin_auth`)
 			SELECT ".(_DBMAXINT - 1).", 'developer', '"._password_hash($developerDefaultPassword)."', '개발자', '개발자', '개발자', 20, NOW(), 'y', 'developer@admin.com', ''");
}

if(!in_array(TABLE_MENU, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_MENU."` (
			`category` VARBINARY(50) NOT NULL DEFAULT '0',
			`sort` INT(11) NOT NULL DEFAULT '0',
			`controller` VARCHAR(32) NOT NULL DEFAULT '',
			`title` VARCHAR(64) NOT NULL DEFAULT '0',
			`type` ENUM('customize','board','content') NOT NULL DEFAULT 'customize',
			`enabled` ENUM('y','n') NOT NULL DEFAULT 'y',
			`parent_enabled` ENUM('y','n') NOT NULL DEFAULT 'y',
			`bid` VARCHAR(20) NOT NULL DEFAULT '',
			`subid` VARCHAR(20) NOT NULL DEFAULT '',
			`addi_subid` VARCHAR(256) NOT NULL DEFAULT '',
			`board_category` VARCHAR(32) NOT NULL DEFAULT '',
			`board_sub_category` VARCHAR(256) NOT NULL DEFAULT '',
			`show_level` TINYINT(2) NOT NULL DEFAULT '0',
			`con_level` TINYINT(2) NOT NULL DEFAULT '0',
			`real_controller` VARCHAR(32) NOT NULL DEFAULT '',
			`default_action` VARCHAR(32) NOT NULL DEFAULT '',
			PRIMARY KEY (`category`),
			INDEX `controller` (`controller`),
			INDEX `enabled` (`enabled`),
			INDEX `parent_enabled` (`parent_enabled`),
			INDEX `sort` (`sort`)
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");

	DB::SQL()->Query("INSERT INTO `".TABLE_MENU."` (`category`, `sort`, `controller`, `title`, `type`, `enabled`, `parent_enabled`, `bid`) VALUES ('00000', 0, 'Home', 'Home', 'customize', 'y', 'y', '')");

	DB::SQL()->Query("INSERT INTO `".TABLE_MENU."` (`category`, `sort`, `controller`, `title`, `type`, `enabled`, `parent_enabled`, `bid`, `subid`) VALUES ('0000000000', 0, 'FreeBoard', '자유게시판', 'board', 'y', 'y', 'board', 'free_board')");

	DB::SQL()->Query("INSERT INTO `".TABLE_MENU."` (`category`, `sort`, `controller`, `title`, `type`, `enabled`, `parent_enabled`, `bid`, `subid`) VALUES ('0000000001', 0, 'Notice', '공지사항', 'board', 'y', 'y', 'board', 'notice')");

	DB::SQL()->Query("INSERT INTO `".TABLE_MENU."` (`category`, `sort`, `controller`, `title`, `type`, `enabled`, `parent_enabled`, `bid`, `subid`) VALUES ('0000000002', 0, 'Gallery', '갤러리게시판', 'board', 'y', 'y', 'board', 'gallery')");
}

if(!in_array(TABLE_POPUP, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_POPUP."` (
			`seq` INT(11) NOT NULL AUTO_INCREMENT,
			`category` VARCHAR(20) NOT NULL DEFAULT '',
			`kind` VARCHAR(32) NOT NULL,
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
			`pos_x` SMALLINT(6) NOT NULL DEFAULT '0',
			`pos_y` SMALLINT(6) NOT NULL DEFAULT '0',
			`sort` INT(6) NOT NULL DEFAULT '0',
			PRIMARY KEY (`seq`),
			INDEX `begin_date` (`begin_date`),
			INDEX `end_date` (`end_date`),
			INDEX `enabled` (`enabled`)
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");
}

if(!in_array(TABLE_WITHDRAW_MEMBER, $tables)){
	DB::SQL()->Query("CREATE TABLE `".TABLE_WITHDRAW_MEMBER."` (
			`muid` INT(11) NOT NULL DEFAULT '0',
			`mid` VARCHAR(128) NOT NULL DEFAULT '',
			`mname` VARCHAR(32) NOT NULL DEFAULT '',
			`cname` VARCHAR(32) NOT NULL DEFAULT '',
			`nickname` VARCHAR(32) NOT NULL DEFAULT '',
			`level` TINYINT(2) NOT NULL DEFAULT '1',
			`email` VARCHAR(128) NOT NULL DEFAULT '',
			`reason` TEXT NOT NULL,
			`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
			`w_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00' COMMENT '탈퇴일',
			PRIMARY KEY (`muid`)
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");
}

if(!in_array(TABLE_CONTENT_ACTION, $tables)){
	DB::SQL()->Query("CREATE TABLE `" . TABLE_CONTENT_ACTION . "` (
			`action_type` ENUM('read','recommend','oppose','scrap') NOT NULL DEFAULT 'read',
			`bid` VARCHAR(20) NOT NULL DEFAULT '',
			`muid` INT(11) NOT NULL DEFAULT '0',
			`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
			PRIMARY KEY (`action_type`, `bid`, `muid`),
			INDEX `bid` (`bid`, `muid`)
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB");
}

if(!in_array(TABLE_VISIT, $tables)){
	DB::SQL()->Query("CREATE TABLE `" . TABLE_VISIT . "` (
			`ip` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
			`dt` DATE NOT NULL DEFAULT '0000-01-01',
			`browser` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
			`os` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
			`device` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
			`uri` VARCHAR(256) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
			`visit` SMALLINT(6) NOT NULL DEFAULT '1',
			PRIMARY KEY (`ip`, `dt`)
		)
		COLLATE='utf8_unicode_ci'
		ENGINE=InnoDB");
}

if(!in_array(TABLE_VISIT_COUNTER, $tables)){
	DB::SQL()->Query("CREATE TABLE `" . TABLE_VISIT_COUNTER . "` (
			`d_y` SMALLINT(6) NOT NULL DEFAULT '0',
			`d_m` TINYINT(4) NOT NULL DEFAULT '-1',
			`d_d` TINYINT(4) NOT NULL DEFAULT '-1',
			`d_h` TINYINT(4) NOT NULL DEFAULT '-1',
			`d_w` TINYINT(4) NOT NULL DEFAULT '-1',
			`type` ENUM('browser','os','uri','device','total') NOT NULL DEFAULT 'browser' COLLATE 'utf8_unicode_ci',
			`type_detail` VARCHAR(256) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
			`type_etc` VARCHAR(256) NOT NULL DEFAULT '',
			`login` INT(11) NOT NULL DEFAULT '0',
			`visit` INT(11) NOT NULL DEFAULT '0',
			INDEX `d_h` (`d_h`, `d_y`, `d_m`, `d_d`),
			INDEX `d_w` (`d_w`, `d_y`, `d_m`),
			INDEX `date` (`d_y`, `d_m`, `d_d`, `d_h`)
		)
		COLLATE='utf8_unicode_ci'
		ENGINE=InnoDB");
}

if(!in_array(TABLE_MESSAGE, $tables)){
	DB::SQL()->Query("CREATE TABLE `" . TABLE_MESSAGE . "` (
					`seq` BIGINT(10) NOT NULL AUTO_INCREMENT,
					`muid` INT(10) NOT NULL DEFAULT '0',
					`target_muid` INT(10) NOT NULL DEFAULT '0',
					`comment` TEXT NOT NULL,
					`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
					`file` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일',
					`delis` ENUM('y','n') NOT NULL DEFAULT 'n',
					`target_delis` ENUM('y','n') NOT NULL DEFAULT 'n',
					`read_date` DATETIME DEFAULT NULL,
					`report` ENUM('y', 'n') NOT NULL DEFAULT 'n',
					PRIMARY KEY (`seq`),
					INDEX `muid` (`muid`),
					INDEX `target_muid` (`target_muid`)
				)
				COLLATE='utf8mb4_general_ci'
				ENGINE=InnoDB");
}

if(!in_array(TABLE_USER_BLOCK, $tables)){
	DB::SQL()->Query("CREATE TABLE `" . TABLE_USER_BLOCK . "` (
					`seq` BIGINT(10) NOT NULL AUTO_INCREMENT,
					`muid` INT(10) NOT NULL DEFAULT '0',
					`target_muid` INT(10) NOT NULL DEFAULT '0',
					`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
					PRIMARY KEY (`seq`),
					INDEX `muid` (`muid`),
					INDEX `target_muid` (`target_muid`)
				)
				COLLATE='utf8mb4_general_ci'
				ENGINE=InnoDB");
}

$bmModel = new \BoardManagerModel();

if(!in_array(TABLE_FIRST.'bbs_board', $tables)){
	$bmModel->CreateTableBoard(TABLE_FIRST.'bbs_board');

	$bmModel->_subid->SetValue('free_board');
	$bmModel->_bid->SetValue('board');
	$bmModel->_reg_date->SetValue(date('Y-m-d H:i:s'));
	$bmModel->_auth_answer_level->SetValue(1);
	$bmModel->_auth_list_level->SetValue(0);
	$bmModel->_auth_reply_level->SetValue(1);
	$bmModel->_auth_view_level->SetValue(0);
	$bmModel->_auth_write_level->SetValue(1);
	$bmModel->_layout->SetValue('_Board');
	$bmModel->_subject->SetValue('자유게시판');
	$bmModel->_group_name->SetValue('기본');
	$bmModel->DBInsert();

	$bmModel->_subid->SetValue('notice');
	$bmModel->_bid->SetValue('board');
	$bmModel->_reg_date->SetValue(date('Y-m-d H:i:s'));
	$bmModel->_auth_answer_level->SetValue(18);
	$bmModel->_auth_list_level->SetValue(0);
	$bmModel->_auth_reply_level->SetValue(1);
	$bmModel->_auth_view_level->SetValue(0);
	$bmModel->_auth_write_level->SetValue(18);
	$bmModel->_layout->SetValue('_Board');
	$bmModel->_subject->SetValue('공지사항');
	$bmModel->_group_name->SetValue('기본');
	$bmModel->DBInsert();

	$bmModel->_subid->SetValue('gallery');
	$bmModel->_bid->SetValue('board');
	$bmModel->_skin->SetValue('Gallery');
	$bmModel->_reg_date->SetValue(date('Y-m-d H:i:s'));
	$bmModel->_auth_answer_level->SetValue(1);
	$bmModel->_auth_list_level->SetValue(0);
	$bmModel->_auth_reply_level->SetValue(1);
	$bmModel->_auth_view_level->SetValue(0);
	$bmModel->_auth_write_level->SetValue(1);
	$bmModel->_layout->SetValue('_Board');
	$bmModel->_subject->SetValue('갤러리게시판');
	$bmModel->_group_name->SetValue('기본');
	$bmModel->DBInsert();
}

if(!in_array(TABLE_FIRST.'bbs_board_reply', $tables)){
	$bmModel->CreateTableReply(TABLE_FIRST.'bbs_board_reply');
}

if(!in_array(TABLE_FIRST.'bbs_board_images', $tables)){
	$bmModel->CreateTableImg(TABLE_FIRST.'bbs_board_images');
}

if(!in_array(TABLE_FIRST.'bbs_board_action', $tables)){
	$bmModel->CreateTableAction(TABLE_FIRST.'bbs_board_action');
}

$updateSql = array();

if(!DB::SQL()->ColumnExists(TABLE_MENU, 'show_level')){
	DB::SQL()->Query("ALTER TABLE `" . TABLE_MENU . "` ADD COLUMN `show_level` TINYINT(2) NOT NULL DEFAULT '0' AFTER `board_sub_category`,  ADD COLUMN `con_level` TINYINT(2) NOT NULL DEFAULT '0' AFTER `show_level`");
}

if(!DB::SQL()->ColumnExists(TABLE_MENU, 'real_controller')){
	DB::SQL()->Query("ALTER TABLE `" . TABLE_MENU . "` ADD COLUMN `real_controller` VARCHAR(32) NOT NULL DEFAULT '' AFTER `controller`, ADD COLUMN `default_action` VARCHAR(32) NOT NULL DEFAULT '' AFTER `real_controller`");
}

if(!DB::SQL()->ColumnExists(TABLE_VISIT_COUNTER, 'type_etc')){
	DB::SQL()->Query("ALTER TABLE `" . TABLE_VISIT_COUNTER . "` ADD COLUMN `type_etc` VARCHAR(256) NOT NULL DEFAULT '' AFTER `type_detail`");
}

$bmQry = DB::GetListQryObj(TABLE_BOARD_MNG)
	->SetKey('DISTINCT `bid`');
while($bmRow = $bmQry->Get()){
	if(!DB::SQL()->ColumnExists(TABLE_FIRST . "bbs_" . $bmRow['bid'], 'reply_top_recommend')){
		DB::SQL()->Query("ALTER TABLE `" . TABLE_FIRST . "bbs_" . $bmRow['bid'] . "` ADD COLUMN `reply_top_recommend` INT(10) NOT NULL DEFAULT '0' COMMENT '댓글 최고 추천수' AFTER `reply_cnt`, ADD COLUMN `reply_top_oppose` INT(10) NOT NULL DEFAULT '0' COMMENT '댓글 최고 반대수' AFTER `reply_top_recommend`, ADD COLUMN `reply_top_report` INT(10) NOT NULL DEFAULT '0' COMMENT '댓글 최고 신고수' AFTER `reply_top_oppose`");
	}
}

\Common\MenuHelp::GetInstance()->MenusToFile();

