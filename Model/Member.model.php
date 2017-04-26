<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */
class MemberModel extends \BH_Model{

	public function __Init(){
		$this->Key = array('muid');
		$this->Except = array('muid');
		$this->table = TABLE_MEMBER;

		$this->InitModelData('muid', ModelType::String, false);
		$this->data['muid']->AutoDecrement = true;

		$this->InitModelData('mid', ModelType::String, false, '아이디');
		$this->data['mid']->HtmlType = HTMLType::InputText;
		$this->data['mid']->MinLength = '4';
		$this->data['mid']->MaxLength = '16';

		$this->InitModelData('pwd', ModelType::Password, true, '패스워드');
		$this->data['pwd']->HtmlType = HTMLType::InputPassword;
		$this->data['pwd']->MinLength = '8';
		$this->data['pwd']->MaxLength = '16';

		$this->InitModelData('mname', ModelType::String, false, '이름');
		$this->data['mname']->HtmlType = HTMLType::InputText;
		$this->data['mname']->MaxLength = 20;

		$this->InitModelData('email', ModelType::String, true, '이메일');
		$this->data['email']->HtmlType = HTMLType::InputText;

		$this->InitModelData('cname', ModelType::String, false, '회사명');
		$this->data['cname']->HtmlType = HTMLType::InputText;

		$this->InitModelData('nickname', ModelType::String, true, '닉네임');
		$this->data['nickname']->HtmlType = HTMLType::InputText;

		$this->InitModelData('level', ModelType::Int, true, '회원등급');
		$this->data['level']->HtmlType = HTMLType::Select;
		$this->data['level']->EnumValues = $GLOBALS['_LevelArray'];

		$this->InitModelData('address1', ModelType::String, false, '주소');
		$this->data['address1']->HtmlType = HTMLType::InputText;

		$this->InitModelData('address2', ModelType::String, false, '상세주소');
		$this->data['address2']->HtmlType = HTMLType::InputText;

		$this->InitModelData('zipcode', ModelType::String, false, '우편번호');
		$this->data['zipcode']->HtmlType = HTMLType::InputText;

		$this->InitModelData('tel', ModelType::String, false, '연락처');
		$this->data['tel']->HtmlType = HTMLType::InputText;

		$this->InitModelData('phone', ModelType::String, false, '휴대폰번호');
		$this->data['phone']->HtmlType = HTMLType::InputText;

		$this->InitModelData('reg_date', ModelType::Datetime, false, '등록일');

		$this->InitModelData('approve', ModelType::Enum, false, '승인여부');
		$this->data['approve']->HtmlType = HTMLType::InputRadio;
		$this->data['approve']->EnumValues = array('y'=>'승인','n'=>'미승인');
	}

}
