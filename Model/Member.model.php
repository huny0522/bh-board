<?php
/**
 *
 * Bang Hun.
 * 16.07.10
 *
 */
class MemberModel extends BH_Model{

	public function __Init(){
		$this->Key = array('muid');
		$this->Except = array('muid');
		$this->table = TABLE_MEMBER;

		$this->InitModelData('muid', ModelTypeString, false);
		$this->data['muid']->AutoDecrement = true;

		$this->InitModelData('mid', ModelTypeString, false, '아이디');
		$this->data['mid']->HtmlType = HTMLInputText;
		$this->data['mid']->MinLength = '4';
		$this->data['mid']->MaxLength = '16';

		$this->InitModelData('pwd', ModelTypePassword, true, '패스워드');
		$this->data['pwd']->HtmlType = HTMLInputPassword;
		$this->data['pwd']->MinLength = '8';
		$this->data['pwd']->MaxLength = '16';

		$this->InitModelData('mname', ModelTypeString, false, '이름');
		$this->data['mname']->HtmlType = HTMLInputText;
		$this->data['mname']->MaxLength = 20;

		$this->InitModelData('email', ModelTypeString, true, '이메일');
		$this->data['email']->HtmlType = HTMLInputText;

		$this->InitModelData('cname', ModelTypeString, false, '회사명');
		$this->data['cname']->HtmlType = HTMLInputText;

		$this->InitModelData('nickname', ModelTypeString, true, '닉네임');
		$this->data['nickname']->HtmlType = HTMLInputText;

		$this->InitModelData('level', ModelTypeInt, true, '회원등급');
		$this->data['level']->HtmlType = HTMLSelect;
		$this->data['level']->EnumValues = $GLOBALS['_LevelArray'];

		$this->InitModelData('address1', ModelTypeString, false, '주소');
		$this->data['address1']->HtmlType = HTMLInputText;

		$this->InitModelData('address2', ModelTypeString, false, '상세주소');
		$this->data['address2']->HtmlType = HTMLInputText;

		$this->InitModelData('zipcode', ModelTypeString, false, '우편번호');
		$this->data['zipcode']->HtmlType = HTMLInputText;

		$this->InitModelData('tel', ModelTypeString, false, '연락처');
		$this->data['tel']->HtmlType = HTMLInputText;

		$this->InitModelData('phone', ModelTypeString, false, '휴대폰번호');
		$this->data['phone']->HtmlType = HTMLInputText;

		$this->InitModelData('reg_date', ModelTypeDatetime, false, '등록일');

		$this->InitModelData('approve', ModelTypeEnum, false, '승인여부');
		$this->data['approve']->HtmlType = HTMLInputRadio;
		$this->data['approve']->EnumValues = array('y'=>'승인','n'=>'미승인');
	}

}
