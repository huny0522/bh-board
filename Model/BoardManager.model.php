<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

class BoardManagerModel extends BH_Model{

	public function __Init(){
		$this->Key[] = 'bid';
		$this->table = TABLE_BOARD_MNG;

		$this->InitModelData('subject', ModelTypeString, true, '제목');
		$this->data['subject']->HtmlType = HTMLInputText;
		$this->data['subject']->MaxLength = 128;

		$this->InitModelData('bid', ModelTypeEngNum, true, '게시판아이디');
		$this->data['bid']->HtmlType = HTMLInputText;
		$this->data['bid']->MinLength = '1';
		$this->data['bid']->MaxLength = '20';

		$this->InitModelData('manager', ModelTypeString, false, '게시판관리자');
		$this->data['manager']->HtmlType = HTMLInputText;

		$this->InitModelData('skin', ModelTypeEngNum, false, '스킨');
		$this->data['skin']->HtmlType = HTMLInputText;
		$this->data['skin']->MinLength = '1';
		$this->data['skin']->MaxLength = '20';

		$this->InitModelData('reply_skin', ModelTypeEngNum, false, '댓글스킨');
		$this->data['reply_skin']->HtmlType = HTMLInputText;
		$this->data['reply_skin']->MinLength = '1';
		$this->data['reply_skin']->MaxLength = '20';

		$this->InitModelData('category', ModelTypeString, false, '분류');
		$this->data['category']->HtmlType = HTMLInputText;
		$this->data['category']->MaxLength = 256;

		$this->InitModelData('article_count', ModelTypeInt, false, '게시물수');
		$this->data['article_count']->HtmlType = HTMLInputText;
		$this->data['article_count']->MinValue = 1;
		$this->data['article_count']->MaxValue = 100;
		$this->data['article_count']->DefaultValue = 10;

		$this->InitModelData('reply_count', ModelTypeInt, false, '댓글게시물수');
		$this->data['reply_count']->HtmlType = HTMLInputText;
		$this->data['reply_count']->MinValue = 1;
		$this->data['reply_count']->MaxValue = 100;
		$this->data['reply_count']->DefaultValue = 10;

		$this->InitModelData('auth_list_level', ModelTypeInt, false, '목록보기권한');
		$this->data['auth_list_level']->HtmlType = HTMLSelect;
		$this->data['auth_list_level']->EnumValues = $GLOBALS['_LevelArray'];
		$this->data['auth_list_level']->DefaultValue = 0;

		$this->InitModelData('auth_write_level', ModelTypeInt, false, '쓰기권한');
		$this->data['auth_write_level']->HtmlType = HTMLSelect;
		$this->data['auth_write_level']->EnumValues = $GLOBALS['_LevelArray'];
		$this->data['auth_write_level']->DefaultValue = 0;

		$this->InitModelData('auth_view_level', ModelTypeInt, false, '보기권한');
		$this->data['auth_view_level']->HtmlType = HTMLSelect;
		$this->data['auth_view_level']->EnumValues = $GLOBALS['_LevelArray'];
		$this->data['auth_view_level']->DefaultValue = 0;

		$this->InitModelData('auth_reply_level', ModelTypeString, false, '댓글쓰기권한');
		$this->data['auth_reply_level']->HtmlType = HTMLSelect;
		$this->data['auth_reply_level']->EnumValues = $GLOBALS['_LevelArray'];
		$this->data['auth_reply_level']->DefaultValue = 0;

		$this->InitModelData('auth_answer_level', ModelTypeString, false, '답변쓰기권한');
		$this->data['auth_answer_level']->HtmlType = HTMLSelect;
		$this->data['auth_answer_level']->EnumValues = $GLOBALS['_LevelArray'];
		$this->data['auth_answer_level']->DefaultValue = 0;

		$this->InitModelData('use_reply', ModelTypeEnum, false, '댓글사용여부');
		$this->data['use_reply']->HtmlType = HTMLInputRadio;
		$this->data['use_reply']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_reply']->DefaultValue = 'y';

		$this->InitModelData('list_in_view', ModelTypeEnum, false, '상세페이지에 리스트표시');
		$this->data['list_in_view']->HtmlType = HTMLInputRadio;
		$this->data['list_in_view']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['list_in_view']->DefaultValue = 'y';

		$this->InitModelData('man_to_man', ModelTypeEnum, false, '1:1게시판 사용여부');
		$this->data['man_to_man']->HtmlType = HTMLInputRadio;
		$this->data['man_to_man']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['man_to_man']->DefaultValue = 'n';

		$this->InitModelData('layout', ModelTypeEngSpecial, false, '레이아웃');
		$this->data['layout']->HtmlType = HTMLInputText;
		$this->data['layout']->MinLength = 1;
		$this->data['layout']->MaxLength = 50;


		$this->InitModelData('new_view_day', ModelTypeInt, false, '새글표시기간');
		$this->data['new_view_day']->HtmlType = HTMLInputText;
		$this->data['new_view_day']->MinValue = 1;
		$this->data['new_view_day']->MaxValue = 50;
		$this->data['new_view_day']->DefaultValue = 1;

	}

	public function CreateTableBoard($board_nm){
		$sql = "CREATE TABLE `{$board_nm}` (
					`seq` INT(18) NOT NULL DEFAULT '0',
					`sort1` INT(10) NOT NULL DEFAULT '0',
					`sort2` INT(10) NOT NULL DEFAULT '0',
					`depth` TINYINT(1) NOT NULL DEFAULT '0',
					`notice` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT '공지여부',
					`category` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '카테고리',
					`muid` INT(18) NULL DEFAULT NULL COMMENT '작성자 uid',
					`mlevel` TINYINT(1) NOT NULL DEFAULT '0',
					`secret` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT '비밀글 여부',
					`mname` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '작성자명',
					`pwd` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '패스워드(비회원용)',
					`subject` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '제목',
					`content` TEXT NOT NULL COMMENT '내용',
					`first_seq` INT(18) NULL DEFAULT NULL,
					`first_member_is` ENUM('y','n') NOT NULL DEFAULT 'n',
					`target_muid` INT(20) NULL DEFAULT NULL,
					`target_mname` VARCHAR(32) NOT NULL DEFAULT '',
					`reg_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '등록일',
					`hit` INT(10) NOT NULL DEFAULT '0' COMMENT '조회수',
					`recommend` INT(10) NOT NULL DEFAULT '0' COMMENT '추천수',
					`reply_cnt` INT(10) NOT NULL DEFAULT '0',
					`delis` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT '삭제여부',
					`htmlis` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT 'HTML태그사용',
					`thumnail` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일',
					`file1` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일',
					`filenm1` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일명',
					`file2` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일',
					`filenm2` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일명',
					PRIMARY KEY (`seq`),
					INDEX `delis` (`delis`),
					INDEX `sort` (`sort1`, `sort2`),
					INDEX `category` (`category`)
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB";
		$res = SqlQuery($sql);
		return $res;
	}

	public function CreateTableReply($reply_nm){
		$sql = "CREATE TABLE `{$reply_nm}` (
					`seq` INT(18) NOT NULL DEFAULT '0',
					`sort1` INT(11) NOT NULL DEFAULT '0',
					`sort2` INT(11) NOT NULL DEFAULT '0',
					`article_seq` INT(20) NOT NULL DEFAULT '0',
					`depth` TINYINT(1) NOT NULL DEFAULT '0',
					`secret` ENUM('y','n') NOT NULL DEFAULT 'n',
					`mlevel` TINYINT(1) NOT NULL DEFAULT '0',
					`muid` INT(20) NULL DEFAULT NULL,
					`mname` VARCHAR(64) NOT NULL DEFAULT '',
					`pwd` VARCHAR(64) NOT NULL DEFAULT '',
					`comment` TEXT NOT NULL,
					`first_seq` INT(11) NULL DEFAULT NULL,
					`first_member_is` ENUM('y','n') NOT NULL DEFAULT 'n',
					`target_muid` INT(20) NULL DEFAULT NULL,
					`target_mname` VARCHAR(32) NOT NULL DEFAULT '',
					`reg_date` DATETIME NOT NULL,
					`file` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일',
					`delis` ENUM('y','n') NOT NULL DEFAULT 'n',
					PRIMARY KEY (`article_seq`, `seq`),
					INDEX `sort` (`sort1`, `sort2`),
					INDEX `delis` (`delis`)
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB";
		$res = SqlQuery($sql);
		return $res;
	}

	public function CreateTableImg($board_nm){
		$sql = "CREATE TABLE `{$board_nm}` (
					`article_seq` INT(10) NOT NULL DEFAULT '0',
					`seq` INT(10) NOT NULL DEFAULT '0',
					`image` VARCHAR(128) NOT NULL DEFAULT '',
					`imagename` VARCHAR(128) NOT NULL DEFAULT '',
					PRIMARY KEY (`article_seq`, `seq`)
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB";
		$res = SqlQuery($sql);
		return $res;
	}
}
