<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CF;
use \BH_Application as App;

class MemberModel extends \BH_Model{

	public function __Init(){
		$this->Key = array('muid');
		$this->Except = array('muid');
		$this->table = TABLE_MEMBER;

		$this->data['muid'] = new \BH_ModelData(ModelType::String, false);
		$this->data['muid']->AutoDecrement = true;

		$this->data['mid'] = new \BH_ModelData(ModelType::String, false, '아이디');
		$this->data['mid']->HtmlType = HTMLType::InputText;
		$this->data['mid']->MinLength = '4';
		$this->data['mid']->MaxLength = '16';

		$this->data['pwd'] = new \BH_ModelData(ModelType::Password, true, '패스워드');
		$this->data['pwd']->HtmlType = HTMLType::InputPassword;
		$this->data['pwd']->MinLength = '8';
		$this->data['pwd']->MaxLength = '16';

		$this->data['mname'] = new \BH_ModelData(ModelType::String, false, '이름');
		$this->data['mname']->HtmlType = HTMLType::InputText;
		$this->data['mname']->MaxLength = 20;

		$this->data['email'] = new \BH_ModelData(ModelType::String, true, '이메일');
		$this->data['email']->HtmlType = HTMLType::InputText;

		$this->data['cname'] = new \BH_ModelData(ModelType::String, false, '회사명');
		$this->data['cname']->HtmlType = HTMLType::InputText;

		$this->data['nickname'] = new \BH_ModelData(ModelType::String, true, '닉네임');
		$this->data['nickname']->HtmlType = HTMLType::InputText;

		$this->data['level'] = new \BH_ModelData(ModelType::Int, true, '회원등급');
		$this->data['level']->HtmlType = HTMLType::Select;
		$this->data['level']->EnumValues = $GLOBALS['_LevelArray'];

		$this->data['address1'] = new \BH_ModelData(ModelType::String, false, '주소');
		$this->data['address1']->HtmlType = HTMLType::InputText;

		$this->data['address2'] = new \BH_ModelData(ModelType::String, false, '상세주소');
		$this->data['address2']->HtmlType = HTMLType::InputText;

		$this->data['zipcode'] = new \BH_ModelData(ModelType::String, false, '우편번호');
		$this->data['zipcode']->HtmlType = HTMLType::InputText;

		$this->data['tel'] = new \BH_ModelData(ModelType::String, false, '연락처');
		$this->data['tel']->HtmlType = HTMLType::InputText;

		$this->data['phone'] = new \BH_ModelData(ModelType::String, false, '휴대폰번호');
		$this->data['phone']->HtmlType = HTMLType::InputText;

		$this->data['reg_date'] = new \BH_ModelData(ModelType::Datetime, false, '등록일');

		$this->data['approve'] = new \BH_ModelData(ModelType::Enum, false, '승인여부');
		$this->data['approve']->HtmlType = HTMLType::InputRadio;
		$this->data['approve']->EnumValues = array('y'=>'승인','n'=>'미승인');
	}

}
