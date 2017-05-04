<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */

class BoardModel extends \BH_Model{
	public $imageTable = '';
	public $bid = '';
	public function __Init(){
		$this->Key[] = 'seq';
		$this->Except = $this->Key;
		if(!strlen($this->bid)) $this->bid = \BH_Application::GetInstance()->TID;

		$this->table = TABLE_FIRST.'bbs_'.$this->bid;
		$this->imageTable = $this->table.'_images';

		if(!\DB::SQL()->TableExists($this->table)){
			Redirect(_URL.'/', '존재하지 않는 게시판입니다.');
		}

		$this->data['seq'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['seq']->AutoDecrement = true;
		$this->data['sort1'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['sort2'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['depth'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['muid'] = new \BH_ModelData(ModelType::String, false, '');
		$this->data['mlevel'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['target_mname'] = new \BH_ModelData(ModelType::String, false, '');
		$this->data['target_muid'] = new \BH_ModelData(ModelType::String, false, '');
		$this->data['first_seq'] = new \BH_ModelData(ModelType::Int, false, '');
		$this->data['first_member_is'] = new \BH_ModelData(ModelType::String, false, '');
		$this->data['first_member_is']->EnumValues = array('y'=>'회원','n'=>'비회원');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::Datetime, false, '등록일');
		$this->data['hit'] = new \BH_ModelData(ModelType::Int, false, '조회수');
		$this->data['recommend'] = new \BH_ModelData(ModelType::Int, false, '추천수');
		$this->data['reply_cnt'] = new \BH_ModelData(ModelType::Int, false, '댓글수');
		$this->data['delis'] = new \BH_ModelData(ModelType::String, false, '삭제여부');
		$this->data['delis']->DefaultValue = 'n';
		$this->data['htmlis'] = new \BH_ModelData(ModelType::String, false, 'HTML 여부');
		$this->data['htmlis']->DefaultValue = 'n';

		$this->data['manager'] = new \BH_ModelData(ModelType::String, false, '게시판관리자');
		$this->data['manager']->HtmlType = HTMLType::InputText;

		$this->data['notice'] = new \BH_ModelData(ModelType::Enum, false, '공지글');
		$this->data['notice']->HtmlType = HTMLType::InputRadio;
		$this->data['notice']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['notice']->DefaultValue = 'n';

		$this->data['category'] = new \BH_ModelData(ModelType::String, false, '분류');
		$this->data['category']->HtmlType = HTMLType::InputText;
		$this->data['category']->MaxLength = 128;

		$this->data['secret'] = new \BH_ModelData(ModelType::String, false, '비밀글');
		$this->data['secret']->HtmlType = HTMLType::InputRadio;
		$this->data['secret']->EnumValues = array('y'=>'사용','n'=>'사용안함');
		$this->data['secret']->DefaultValue = 'n';

		$this->data['mname'] = new \BH_ModelData(ModelType::String, true, '이름');
		$this->data['mname']->HtmlType = HTMLType::InputText;
		$this->data['mname']->MaxLength = 32;

		$this->data['pwd'] = new \BH_ModelData(ModelType::Password, true, '패스워드');
		$this->data['pwd']->HtmlType = HTMLType::InputPassword;
		$this->data['pwd']->MinLength = 6;
		$this->data['pwd']->MaxLength = 16;

		$this->data['subject'] = new \BH_ModelData(ModelType::String, true, '제목');
		$this->data['subject']->HtmlType = HTMLType::InputText;
		$this->data['subject']->MaxLength = 128;

		$this->data['content'] = new \BH_ModelData(ModelType::String, false, '내용');
		$this->data['content']->HtmlType = HTMLType::Textarea;

		$this->data['thumnail'] = new \BH_ModelData(ModelType::String, false, '섬네일이미지');
		$this->data['thumnail']->HtmlType = HTMLType::InputFile;

		$this->data['file1'] = new \BH_ModelData(ModelType::String, false, '파일#1');
		$this->data['file1']->HtmlType = HTMLType::InputFile;

		$this->data['file2'] = new \BH_ModelData(ModelType::String, false, '파일#2');
		$this->data['file2']->HtmlType = HTMLType::InputFile;

		$this->data['filenm1'] = new \BH_ModelData(ModelType::String, false, '파일명#1');
		$this->data['filenm2'] = new \BH_ModelData(ModelType::String, false, '파일명#2');

		if(method_exists($this, '__Init2')){
			$this->__Init2();
		}
	}

}
