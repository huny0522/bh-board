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
		$this->key = array('bid', 'subid');
		$this->table = TABLE_BOARD_MNG;

		$this->data['subject'] = new \BH_ModelData(ModelType::STRING, '제목');
		$this->data['subject']->maxLength = 128;
		$this->data['subject']->required = true;

		$this->data['bid'] = new \BH_ModelData(ModelType::STRING, '게시판아이디', HTMLType::TEXT_ENG_SPECIAL);
		$this->data['bid']->minLength = '1';
		$this->data['bid']->maxLength = '20';
		$this->data['bid']->required = true;

		$this->data['group_name'] = new \BH_ModelData(ModelType::STRING, '그룹명');
		$this->data['group_name']->maxLength = '128';
		$this->data['group_name']->required = true;

		$this->data['subid'] = new \BH_ModelData(ModelType::STRING, '게시판서브아이디', HTMLType::TEXT_ENG_SPECIAL);
		$this->data['subid']->maxLength = '20';

		$this->data['manager'] = new \BH_ModelData(ModelType::STRING, '게시판관리자');

		$this->data['skin'] = new \BH_ModelData(ModelType::STRING, '스킨', HTMLType::TEXT_ENG_NUM);
		$this->data['skin']->minLength = '1';
		$this->data['skin']->maxLength = '20';

		$this->data['reply_skin'] = new \BH_ModelData(ModelType::STRING, '댓글스킨', HTMLType::TEXT_ENG_NUM);
		$this->data['reply_skin']->minLength = '1';
		$this->data['reply_skin']->maxLength = '20';

		$this->data['category'] = new \BH_ModelData(ModelType::STRING, '분류');
		$this->data['category']->maxLength = 256;

		$this->data['sub_category'] = new \BH_ModelData(ModelType::TEXT, '세부분류');

		$this->data['use_sub_category'] = new \BH_ModelData(ModelType::ENUM, '세부분류 사용여부', HTMLType::RADIO);
		$this->data['use_sub_category']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_sub_category']->defaultValue = 'n';

		$this->data['article_count'] = new \BH_ModelData(ModelType::INT, '게시물수');
		$this->data['article_count']->minValue = 1;
		$this->data['article_count']->maxValue = 100;
		$this->data['article_count']->defaultValue = 10;

		$this->data['reply_count'] = new \BH_ModelData(ModelType::INT, '댓글게시물수');
		$this->data['reply_count']->minValue = 1;
		$this->data['reply_count']->maxValue = 100;
		$this->data['reply_count']->defaultValue = 10;

		$this->data['auth_list_level'] = new \BH_ModelData(ModelType::INT, '목록권한', HTMLType::SELECT);
		$this->data['auth_list_level']->enumValues = App::$settingData['LevelArray'];
		$this->data['auth_list_level']->defaultValue = 0;

		$this->data['auth_write_level'] = new \BH_ModelData(ModelType::INT, '쓰기권한', HTMLType::SELECT);
		$this->data['auth_write_level']->enumValues = App::$settingData['LevelArray'];
		$this->data['auth_write_level']->defaultValue = 0;

		$this->data['auth_view_level'] = new \BH_ModelData(ModelType::INT, '보기권한', HTMLType::SELECT);
		$this->data['auth_view_level']->enumValues = App::$settingData['LevelArray'];
		$this->data['auth_view_level']->defaultValue = 0;

		$this->data['auth_reply_level'] = new \BH_ModelData(ModelType::STRING, '댓글쓰기권한', HTMLType::SELECT);
		$this->data['auth_reply_level']->enumValues = App::$settingData['LevelArray'];
		$this->data['auth_reply_level']->defaultValue = 0;

		$this->data['auth_answer_level'] = new \BH_ModelData(ModelType::STRING, '답변쓰기권한', HTMLType::SELECT);
		$this->data['auth_answer_level']->enumValues = App::$settingData['LevelArray'];
		$this->data['auth_answer_level']->defaultValue = 0;

		$this->data['use_reply'] = new \BH_ModelData(ModelType::ENUM, '댓글사용', HTMLType::RADIO);
		$this->data['use_reply']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_reply']->defaultValue = 'y';

		$this->data['list_in_view'] = new \BH_ModelData(ModelType::ENUM, '상세페이지에 리스트표시', HTMLType::RADIO);
		$this->data['list_in_view']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['list_in_view']->defaultValue = 'y';

		$this->data['list_show_notice'] = new \BH_ModelData(ModelType::ENUM, '리스트내에 공지출력', HTMLType::RADIO);
		$this->data['list_show_notice']->enumValues = array('y'=>'출력','n'=>'출력안함');
		$this->data['list_show_notice']->defaultValue = 'n';

		$this->data['man_to_man'] = new \BH_ModelData(ModelType::ENUM, '1:1게시판 사용여부', HTMLType::RADIO);
		$this->data['man_to_man']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['man_to_man']->defaultValue = 'n';

		$this->data['use_secret'] = new \BH_ModelData(ModelType::ENUM, '비밀글 사용여부', HTMLType::RADIO);
		$this->data['use_secret']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_secret']->defaultValue = 'n';

		$this->data['use_html'] = new \BH_ModelData(ModelType::ENUM, 'HTML 에디터 사용여부', HTMLType::RADIO);
		$this->data['use_html']->enumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['use_html']->defaultValue = 'y';

		$this->data['layout'] = new \BH_ModelData(ModelType::STRING, '레이아웃',HTMLType::TEXT_ENG_SPECIAL);
		$this->data['layout']->minLength = 1;
		$this->data['layout']->maxLength = 50;


		$this->data['new_view_day'] = new \BH_ModelData(ModelType::INT, '새글표시기간');
		$this->data['new_view_day']->minValue = 1;
		$this->data['new_view_day']->maxValue = 50;
		$this->data['new_view_day']->defaultValue = 1;

		$this->data['attach_type'] = new \BH_ModelData(ModelType::ENUM, '업로드 가능파일', HTMLType::RADIO);
		$this->data['attach_type']->enumValues = array('normal' => '기본','image' => '이미지');
		$this->data['attach_type']->defaultValue = 'normal';

		$this->data['reg_date'] = new \BH_ModelData(ModelType::DATETIME, '등록일');

		$this->data['use_captcha'] = new \BH_ModelData(ModelType::ENUM, 'use_captcha', HTMLType::SELECT);
		$this->data['use_captcha']->enumValues = array('y' => 'y','n' => 'n');
		$this->data['use_captcha']->defaultValue = 'y';
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
					`email` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '이메일',
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
					
					`reply_top_recommend` INT(10) NOT NULL DEFAULT '0' COMMENT '댓글 최고 추천수',
					`reply_top_oppose` INT(10) NOT NULL DEFAULT '0' COMMENT '댓글 최고 반대수',
					`reply_top_report` INT(10) NOT NULL DEFAULT '0' COMMENT '댓글 최고 신고수',
					
					`delis` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT '삭제여부',
					`htmlis` ENUM('y','n') NOT NULL DEFAULT 'n' COMMENT 'HTML태그사용',
					`email_alarm` ENUM('y','n') NOT NULL DEFAULT 'y' COMMENT '이메일 알림 여부',
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
		$data = json_decode($this->_sub_category->Txt(), true);
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
