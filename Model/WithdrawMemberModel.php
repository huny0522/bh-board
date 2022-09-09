<?php
use \BH_Application as App;
use \BH_Common as CM;
use \DB as DB;

/**
 * Class WithdrawMemberModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_muid
 * @property BH_ModelData $_mid
 * @property BH_ModelData $_mname
 * @property BH_ModelData $_cname
 * @property BH_ModelData $_nickname
 * @property BH_ModelData $_level
 * @property BH_ModelData $_email
 * @property BH_ModelData $_reason
 * @property BH_ModelData $_reg_date
 * @property BH_ModelData $_w_date
 */
class WithdrawMemberModel extends \BH_Model
{
	const TABLE = TABLE_WITHDRAW_MEMBER;
	public function __Init(){
		$this->key = array('muid');
		$this->table = TABLE_WITHDRAW_MEMBER;

		$this->data['muid'] = new \BH_ModelData(ModelType::INT, '고유값');
		$this->data['muid']->defaultValue = 0;

		$this->data['mid'] = new \BH_ModelData(ModelType::STRING, '아이디');
		$this->data['mid']->maxLength = '128';

		$this->data['mname'] = new \BH_ModelData(ModelType::STRING, '이름');
		$this->data['mname']->maxLength = '32';

		$this->data['cname'] = new \BH_ModelData(ModelType::STRING, '회사명');
		$this->data['cname']->maxLength = '32';

		$this->data['nickname'] = new \BH_ModelData(ModelType::STRING, '닉네임');
		$this->data['nickname']->maxLength = '32';

		$this->data['level'] = new \BH_ModelData(ModelType::INT, '레벨');
		$this->data['level']->defaultValue = 1;

		$this->data['email'] = new \BH_ModelData(ModelType::STRING, '이메일주소');
		$this->data['email']->maxLength = '128';

		$this->data['reason'] = new \BH_ModelData(ModelType::TEXT, '탈퇴사유', HTMLType::TEXTAREA);

		$this->data['reg_date'] = new \BH_ModelData(ModelType::DATETIME, '가입일', HTMLType::DATE_PICKER);

		$this->data['w_date'] = new \BH_ModelData(ModelType::DATETIME, '탈퇴일', HTMLType::DATE_PICKER);
	} // __Init

	public function DBInsert($test = false){
		$this->data['w_date']->SetValue(date('Y-m-d H:i:s'));
		return parent::DBInsert($test);
	}
}
