<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

use \BH_Common as CM;
use \BH_Application as App;

class BoardManagerModel extends \BH_Model{

	/** @var  \BH_ModelData[] */
	public $data;

	public function __Init(){
		$this->Key[] = 'bid';
		$this->table = TABLE_BOARD_MNG;

		$this->data['subject'] = new \BH_ModelData(ModelType::String, true, '제목');
		$this->data['subject']->MaxLength = 128;

		$this->data['bid'] = new \BH_ModelData(ModelType::String, true, '게시판아이디', HTMLType::InputEngNum);
		$this->data['bid']->MinLength = '1';
		$this->data['bid']->MaxLength = '20';

		$this->data['manager'] = new \BH_ModelData(ModelType::String, false, '게시판관리자');

		$this->data['skin'] = new \BH_ModelData(ModelType::String, false, '스킨', HTMLType::InputEngNum);
		$this->data['skin']->MinLength = '1';
		$this->data['skin']->MaxLength = '20';

		$this->data['reply_skin'] = new \BH_ModelData(ModelType::String, false, '댓글스킨', HTMLType::InputEngNum);
		$this->data['reply_skin']->MinLength = '1';
		$this->data['reply_skin']->MaxLength = '20';

		$this->data['category'] = new \BH_ModelData(ModelType::String, false, '분류');
		$this->data['category']->MaxLength = 256;

		$this->data['article_count'] = new \BH_ModelData(ModelType::Int, false, '게시물수');
		$this->data['article_count']->MinValue = 1;
		$this->data['article_count']->MaxValue = 100;
		$this->data['article_count']->DefaultValue = 10;

		$this->data['reply_count'] = new \BH_ModelData(ModelType::Int, false, '댓글게시물수');
		$this->data['reply_count']->MinValue = 1;
		$this->data['reply_count']->MaxValue = 100;
		$this->data['reply_count']->DefaultValue = 10;

		$this->data['auth_list_level'] = new \BH_ModelData(ModelType::Int, false, '목록권한', HTMLType::Select);
		$this->data['auth_list_level']->EnumValues = App::$SettingData['LevelArray'];
		$this->data['auth_list_level']->DefaultValue = 0;

		$this->data['auth_write_level'] = new \BH_ModelData(ModelType::Int, false, '쓰기권한', HTMLType::Select);
		$this->data['auth_write_level']->EnumValues = App::$SettingData['LevelArray'];
		$this->data['auth_write_level']->DefaultValue = 0;

		$this->data['auth_view_level'] = new \BH_ModelData(ModelType::Int, false, '보기권한', HTMLType::Select);
		$this->data['auth_view_level']->EnumValues = App::$SettingData['LevelArray'];
		$this->data['auth_view_level']->DefaultValue = 0;

		$this->data['auth_reply_level'] = new \BH_ModelData(ModelType::String, false, '댓글쓰기권한', HTMLType::Select);
		$this->data['auth_reply_level']->EnumValues = App::$SettingData['LevelArray'];
		$this->data['auth_reply_level']->DefaultValue = 0;

		$this->data['auth_answer_level'] = new \BH_ModelData(ModelType::String, false, '답변쓰기권한', HTMLType::Select);
		$this->data['auth_answer_level']->EnumValues = App::$SettingData['LevelArray'];
		$this->data['auth_answer_level']->DefaultValue = 0;

		$this->data['use_reply'] = new \BH_ModelData(ModelType::Enum, false, '댓글사용', HTMLType::InputRadio);
		$this->data['use_reply']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_reply']->DefaultValue = 'y';

		$this->data['list_in_view'] = new \BH_ModelData(ModelType::Enum, false, '상세페이지에 리스트표시', HTMLType::InputRadio);
		$this->data['list_in_view']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['list_in_view']->DefaultValue = 'y';

		$this->data['man_to_man'] = new \BH_ModelData(ModelType::Enum, false, '1:1게시판 사용여부', HTMLType::InputRadio);
		$this->data['man_to_man']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['man_to_man']->DefaultValue = 'n';

		$this->data['layout'] = new \BH_ModelData(ModelType::String, false, '레이아웃',HTMLType::InputEngSpecial);
		$this->data['layout']->MinLength = 1;
		$this->data['layout']->MaxLength = 50;


		$this->data['new_view_day'] = new \BH_ModelData(ModelType::Int, false, '새글표시기간');
		$this->data['new_view_day']->MinValue = 1;
		$this->data['new_view_day']->MaxValue = 50;
		$this->data['new_view_day']->DefaultValue = 1;

		$this->data['attach_type'] = new \BH_ModelData(ModelType::Enum, false, '업로드 가능파일', HTMLType::InputRadio);
		$this->data['attach_type']->EnumValues = array('normal' => '기본','image' => '이미지');
		$this->data['attach_type']->DefaultValue = 'normal';

		$this->data['reg_date'] = new \BH_ModelData(ModelType::Datetime, false, '등록일');
	}

	public function CreateTableBoard($board_nm){
		$sql = "CREATE TABLE `{$board_nm}` (
					`seq` INT(10) NOT NULL DEFAULT '0',
					`sort1` INT(10) NOT NULL DEFAULT '0',
					`sort2` INT(10) NOT NULL DEFAULT '0',
					`depth` TINYINT(1) NOT NULL DEFAULT '0',
					`notice` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT '공지여부',
					`category` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '카테고리',
					`muid` INT(10) NULL DEFAULT NULL COMMENT '작성자 uid',
					`mlevel` TINYINT(1) NOT NULL DEFAULT '0',
					`secret` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT '비밀글 여부',
					`mname` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '작성자명',
					`pwd` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '패스워드(비회원용)',
					`subject` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '제목',
					`content` TEXT NOT NULL COMMENT '내용',
					`first_seq` INT(10) NULL DEFAULT NULL,
					`first_member_is` ENUM('y','n') NOT NULL DEFAULT 'n',
					`target_muid` INT(10) NULL DEFAULT NULL,
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
		$res = \DB::SQL()->Query($sql);
		return $res;
	}

	public function CreateTableReply($reply_nm){
		$sql = "CREATE TABLE `{$reply_nm}` (
					`seq` INT(10) NOT NULL DEFAULT '0',
					`sort1` INT(11) NOT NULL DEFAULT '0',
					`sort2` INT(11) NOT NULL DEFAULT '0',
					`article_seq` INT(10) NOT NULL DEFAULT '0',
					`depth` TINYINT(1) NOT NULL DEFAULT '0',
					`secret` ENUM('y','n') NOT NULL DEFAULT 'n',
					`mlevel` TINYINT(1) NOT NULL DEFAULT '0',
					`muid` INT(10) NULL DEFAULT NULL,
					`mname` VARCHAR(64) NOT NULL DEFAULT '',
					`pwd` VARCHAR(64) NOT NULL DEFAULT '',
					`comment` TEXT NOT NULL,
					`first_seq` INT(11) NULL DEFAULT NULL,
					`first_member_is` ENUM('y','n') NOT NULL DEFAULT 'n',
					`target_muid` INT(10) NULL DEFAULT NULL,
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
		$res = \DB::SQL()->Query($sql);
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
		$res = \DB::SQL()->Query($sql);
		return $res;
	}
}
