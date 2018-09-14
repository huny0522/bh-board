<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

use \BH_Common as CM;
use \BH_Application as App;

/**
 * Class BoardManagerModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_subject
 * @property BH_ModelData $_bid
 * @property BH_ModelData $_group_name
 * @property BH_ModelData $_subid
 * @property BH_ModelData $_manager
 * @property BH_ModelData $_skin
 * @property BH_ModelData $_reply_skin
 * @property BH_ModelData $_category
 * @property BH_ModelData $_sub_category
 * @property BH_ModelData $_use_sub_category
 * @property BH_ModelData $_article_count
 * @property BH_ModelData $_reply_count
 * @property BH_ModelData $_auth_list_level
 * @property BH_ModelData $_auth_write_level
 * @property BH_ModelData $_auth_view_level
 * @property BH_ModelData $_auth_reply_level
 * @property BH_ModelData $_auth_answer_level
 * @property BH_ModelData $_use_reply
 * @property BH_ModelData $_list_in_view
 * @property BH_ModelData $_list_show_notice
 * @property BH_ModelData $_man_to_man
 * @property BH_ModelData $_use_secret
 * @property BH_ModelData $_use_html
 * @property BH_ModelData $_layout
 * @property BH_ModelData $_new_view_day
 * @property BH_ModelData $_attach_type
 * @property BH_ModelData $_reg_date
 * @property BH_ModelData $_use_captcha
 */
class BoardManagerModel extends \BH_Model
{

	public function __Init(){
		$this->Key = array('bid', 'subid');
		$this->table = TABLE_BOARD_MNG;

		$this->data['subject'] = new \BH_ModelData(ModelType::String, true, '제목');
		$this->data['subject']->MaxLength = 128;

		$this->data['bid'] = new \BH_ModelData(ModelType::String, true, '게시판아이디', HTMLType::InputEngSpecial);
		$this->data['bid']->MinLength = '1';
		$this->data['bid']->MaxLength = '20';

		$this->data['group_name'] = new \BH_ModelData(ModelType::String, true, '그룹명');
		$this->data['group_name']->MaxLength = '128';

		$this->data['subid'] = new \BH_ModelData(ModelType::String, false, '게시판서브아이디', HTMLType::InputEngSpecial);
		$this->data['subid']->MaxLength = '20';

		$this->data['manager'] = new \BH_ModelData(ModelType::String, false, '게시판관리자');

		$this->data['skin'] = new \BH_ModelData(ModelType::String, false, '스킨', HTMLType::InputEngNum);
		$this->data['skin']->MinLength = '1';
		$this->data['skin']->MaxLength = '20';

		$this->data['reply_skin'] = new \BH_ModelData(ModelType::String, false, '댓글스킨', HTMLType::InputEngNum);
		$this->data['reply_skin']->MinLength = '1';
		$this->data['reply_skin']->MaxLength = '20';

		$this->data['category'] = new \BH_ModelData(ModelType::String, false, '분류');
		$this->data['category']->MaxLength = 256;

		$this->data['sub_category'] = new \BH_ModelData(ModelType::Text, false, '세부분류');

		$this->data['use_sub_category'] = new \BH_ModelData(ModelType::Enum, false, '세부분류 사용여부', HTMLType::InputRadio);
		$this->data['use_sub_category']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_sub_category']->DefaultValue = 'n';

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

		$this->data['list_show_notice'] = new \BH_ModelData(ModelType::Enum, false, '리스트내에 공지출력', HTMLType::InputRadio);
		$this->data['list_show_notice']->EnumValues = array('y'=>'출력','n'=>'출력안함');
		$this->data['list_show_notice']->DefaultValue = 'n';

		$this->data['man_to_man'] = new \BH_ModelData(ModelType::Enum, false, '1:1게시판 사용여부', HTMLType::InputRadio);
		$this->data['man_to_man']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['man_to_man']->DefaultValue = 'n';

		$this->data['use_secret'] = new \BH_ModelData(ModelType::Enum, false, '비밀글 사용여부', HTMLType::InputRadio);
		$this->data['use_secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_secret']->DefaultValue = 'n';

		$this->data['use_html'] = new \BH_ModelData(ModelType::Enum, false, 'HTML 에디터 사용여부', HTMLType::InputRadio);
		$this->data['use_html']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_html']->DefaultValue = 'y';

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

		$this->data['use_captcha'] = new \BH_ModelData(ModelType::Enum, false, 'use_captcha', HTMLType::Select);
		$this->data['use_captcha']->EnumValues = array('y' => 'y','n' => 'n');
		$this->data['use_captcha']->DefaultValue = 'y';
	} // __Init

	public function CreateTableBoard($board_nm){
		$sql = "CREATE TABLE `{$board_nm}` (
					`seq` BIGINT(10) NOT NULL AUTO_INCREMENT,
					`sort1` INT(10) NOT NULL DEFAULT '0',
					`sort2` INT(10) NOT NULL DEFAULT '0',
					`depth` TINYINT(1) NOT NULL DEFAULT '0',
					`notice` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT '공지여부',
					`category` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '카테고리',
					`sub_category` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '세부 분류',
					`subid` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '게시판 서브아이디',
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
					`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00' COMMENT '등록일',
					`hit` INT(10) NOT NULL DEFAULT '0' COMMENT '조회수',
					`read` INT(10) NOT NULL DEFAULT '0' COMMENT '읽음',
					`recommend` INT(10) NOT NULL DEFAULT '0' COMMENT '추천수',
					`oppose` INT(10) NOT NULL DEFAULT '0' COMMENT '반대수',
					`report` INT(10) NOT NULL DEFAULT '0' COMMENT '신고수',
					`scrap` INT(10) NOT NULL DEFAULT '0' COMMENT '스크랩수',
					`reply_cnt` INT(10) NOT NULL DEFAULT '0',
					`delis` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT '삭제여부',
					`htmlis` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT 'HTML태그사용',
					`thumbnail` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일',
					`file1` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일',
					`file2` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '파일',
					`youtube` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '유튜브',
					`link1` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '링크#1',
					`link2` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '링크#2',
					PRIMARY KEY (`seq`),
					INDEX `sort` (`subid`, `sort1`, `sort2`, `delis`),
					INDEX `category` (`subid`, `category`, `sort1`, `sort2`, `delis`),
					INDEX `category_sub` (`subid`, `category`, `sub_category`, `sort1`, `sort2`, `delis`)
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB";
		$res = \DB::SQL()->Query($sql);
		return $res;
	}

	public function CreateTableReply($reply_nm){
		$sql = "CREATE TABLE `{$reply_nm}` (
					`seq` BIGINT(10) NOT NULL AUTO_INCREMENT,
					`sort1` INT(11) NOT NULL DEFAULT '0',
					`sort2` INT(11) NOT NULL DEFAULT '0',
					`article_seq` BIGINT(10) NOT NULL DEFAULT '0',
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
					`recommend` INT(10) NOT NULL DEFAULT '0' COMMENT '추천수',
					`oppose` INT(10) NOT NULL DEFAULT '0' COMMENT '반대수',
					`report` INT(10) NOT NULL DEFAULT '0' COMMENT '신고수',
					PRIMARY KEY (`seq`),
					INDEX `sort` (`article_seq`, `sort1`, `sort2`, `delis`)
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB";
		$res = \DB::SQL()->Query($sql);
		return $res;
	}

	public function DBInsert($test = false){
		$this->_reg_date->SetValue(date('Y-m-d H:i:s'));
		return parent::DBInsert($test);
	}

	public function CreateTableImg($board_nm){
		$sql = "CREATE TABLE `{$board_nm}` (
					`article_seq` BIGINT(10) NOT NULL DEFAULT '0',
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

	public function CreateTableAction($board_nm){
		$sql = "CREATE TABLE `{$board_nm}` (
				`action_type` ENUM('read','recommend','oppose','report','rp_recommend','rp_oppose','rp_report','scrap') NOT NULL DEFAULT 'read',
				`article_seq` BIGINT(20) NOT NULL DEFAULT '0',
				`muid` INT(11) NOT NULL DEFAULT '0',
				`reg_date` DATETIME NOT NULL DEFAULT '0000-01-01 00:00:00',
				PRIMARY KEY (`action_type`, `article_seq`, `muid`)
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB";
		$res = \DB::SQL()->Query($sql);
		return $res;
	}

	public function GetSubCategory($category = ''){
		$data = json_decode($this->_sub_category->txt(), true);
		if(!is_array($data)) return array();

		if(!strlen($category)){
			$res = array();
			foreach($data as $v){
				$res[$v['category']] = $v['sub_category'];
			}
			return $res;
		}
		else{
			foreach($data as $v){
				if($v['category'] == $category) return $v['sub_category'];
			}
		}

		return array();
	}

	public static function GetList($except_bid, $except_subid){
		$qry = DB::GetListQryObj(TABLE_BOARD_MNG)
			->SetSort('`bid`, `subject`');
		$qry->AddWhere('`bid` <> %s OR (`bid` = %s AND `subid` <> %s)', $except_bid, $except_bid, $except_subid);
		return $qry->GetRows();
	}

	/**
	 * @return BH_DB_GetList
	 */
	public static function GetBoardListQry(){
		return DB::GetListQryObj(TABLE_BOARD_MNG. ' A')
			->AddTable('LEFT JOIN %1 B ON A.bid = B.bid AND A.subid = B.subid', TABLE_MENU)
			->SetKey('A.bid, A.subid, B.controller, A.subject, B.title, B.category')
			->AddWhere('B.parent_enabled = \'y\' AND B.enabled = \'y\' AND B.controller <> \'\'')
			->SetGroup('A.bid, A.subid');
	}
}
