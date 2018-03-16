<?php
/**
 * Bang Hun.
 * 16.07.10
 */

use \BH_Common as CM;
use \BH_Application as App;

/**
 * Class MemberModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_muid
 * @property BH_ModelData $_mid
 * @property BH_ModelData $_pwd
 * @property BH_ModelData $_mname
 * @property BH_ModelData $_email
 * @property BH_ModelData $_cname
 * @property BH_ModelData $_nickname
 * @property BH_ModelData $_level
 * @property BH_ModelData $_address1
 * @property BH_ModelData $_address2
 * @property BH_ModelData $_zipcode
 * @property BH_ModelData $_tel
 * @property BH_ModelData $_phone
 * @property BH_ModelData $_reg_date
 * @property BH_ModelData $_approve
 */
class MemberModel extends \BH_Model{

	public function __Init(){
		$this->Key = array('muid');
		$this->Except = array('muid');
		$this->table = TABLE_MEMBER;

		$this->data['muid'] = new \BH_ModelData(ModelType::Int, false);
		$this->data['muid']->AutoDecrement = true;

		$this->data['mid'] = new \BH_ModelData(ModelType::String, false, '아이디');
		$this->data['mid']->MinLength = '4';
		$this->data['mid']->MaxLength = '16';

		$this->data['pwd'] = new \BH_ModelData(ModelType::String, true, '패스워드', HTMLType::InputPassword);
		$this->data['pwd']->MinLength = '8';
		$this->data['pwd']->MaxLength = '16';

		$this->data['mname'] = new \BH_ModelData(ModelType::String, false, '이름');
		$this->data['mname']->MaxLength = 20;

		$this->data['email'] = new \BH_ModelData(ModelType::String, true, '이메일', HTMLType::InputEmail);

		$this->data['cname'] = new \BH_ModelData(ModelType::String, false, '회사명');

		$this->data['nickname'] = new \BH_ModelData(ModelType::String, true, '닉네임');

		$this->data['level'] = new \BH_ModelData(ModelType::Int, true, '회원등급', HTMLType::Select);
		$this->data['level']->EnumValues = App::$SettingData['LevelArray'];

		$this->data['address1'] = new \BH_ModelData(ModelType::String, false, '주소');

		$this->data['address2'] = new \BH_ModelData(ModelType::String, false, '상세주소');

		$this->data['zipcode'] = new \BH_ModelData(ModelType::String, false, '우편번호');

		$this->data['tel'] = new \BH_ModelData(ModelType::String, false, '연락처', HTMLType::InputTel);

		$this->data['phone'] = new \BH_ModelData(ModelType::String, false, '휴대폰번호', HTMLType::InputTel);

		$this->data['reg_date'] = new \BH_ModelData(ModelType::Datetime, false, '등록일');

		$this->data['approve'] = new \BH_ModelData(ModelType::Enum, false, '승인여부', HTMLType::InputRadio);
		$this->data['approve']->EnumValues = array('y'=>'승인','n'=>'미승인');
	}

}
