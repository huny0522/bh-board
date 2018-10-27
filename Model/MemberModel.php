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
 * @property BH_ModelData $_withdraw
 * @property BH_ModelData $_admin_auth
 * @property BH_ModelData $_login_date
 * @property BH_ModelData $_photo1
 * @property BH_ModelData $_pw_reset_code
 * @property BH_ModelData $_email_code
 */
class MemberModel extends \BH_Model
{

	public function __Init(){
		$this->key = array('muid');
		$this->except = array('muid');
		$this->table = TABLE_MEMBER;

		$this->data['muid'] = new \BH_ModelData(ModelType::INT);
		$this->data['muid']->autoDecrement = true;

		$this->data['mid'] = new \BH_ModelData(ModelType::STRING, '아이디');
		$this->data['mid']->minLength = '4';
		$this->data['mid']->maxLength = '16';

		$this->data['pwd'] = new \BH_ModelData(ModelType::STRING, '패스워드', HTMLType::PASSWORD);
		$this->data['pwd']->minLength = '8';
		$this->data['pwd']->maxLength = '16';
		$this->data['pwd']->required = true;

		$this->data['mname'] = new \BH_ModelData(ModelType::STRING, '이름');
		$this->data['mname']->maxLength = 20;

		$this->data['email'] = new \BH_ModelData(ModelType::STRING, '이메일', HTMLType::EMAIL);
		$this->data['email']->required = true;

		$this->data['cname'] = new \BH_ModelData(ModelType::STRING, '회사명');

		$this->data['nickname'] = new \BH_ModelData(ModelType::STRING, '닉네임');
		$this->data['nickname']->required = true;

		$this->data['level'] = new \BH_ModelData(ModelType::INT, '회원등급', HTMLType::SELECT);
		$this->data['level']->enumValues = App::$settingData['LevelArray'];
		$this->data['level']->required = true;

		$this->data['address1'] = new \BH_ModelData(ModelType::STRING, '주소');

		$this->data['address2'] = new \BH_ModelData(ModelType::STRING, '상세주소');

		$this->data['zipcode'] = new \BH_ModelData(ModelType::STRING, '우편번호');

		$this->data['tel'] = new \BH_ModelData(ModelType::STRING, '연락처', HTMLType::TEL);

		$this->data['phone'] = new \BH_ModelData(ModelType::STRING, '휴대폰번호', HTMLType::TEL);

		$this->data['reg_date'] = new \BH_ModelData(ModelType::DATETIME, '등록일');

		$this->data['approve'] = new \BH_ModelData(ModelType::ENUM, '승인여부', HTMLType::RADIO);
		$this->data['approve']->enumValues = array('y'=>'승인','n'=>'미승인');

		$this->data['withdraw'] = new \BH_ModelData(ModelType::ENUM, '탈퇴여부', HTMLType::RADIO);
		$this->data['withdraw']->enumValues = array('y'=>'탈퇴','n'=>'가입');

		$this->data['admin_auth'] = new \BH_ModelData(ModelType::TEXT, 'admin_auth', HTMLType::TEXTAREA);

		$this->data['login_date'] = new \BH_ModelData(ModelType::DATE, '로그인날짜');

		$this->data['photo1'] = new \BH_ModelData(ModelType::STRING, '사진', HTMLType::FILE_IMAGE);

		$this->data['pw_reset_code'] = new \BH_ModelData(ModelType::STRING, '비밀번호 변경 코드');

		$this->data['email_code'] = new \BH_ModelData(ModelType::STRING, '이메일 인증 코드');
	} // __Init

}
