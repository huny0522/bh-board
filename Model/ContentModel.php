<?php
/**
 * Bang Hun.
 * 16.07.10
 */

use \BH_Common as CM;
use \BH_Application as App;

/**
 * Class ContentModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_subject
 * @property BH_ModelData $_category
 * @property BH_ModelData $_bid
 * @property BH_ModelData $_html
 * @property BH_ModelData $_layout
 * @property BH_ModelData $_hit
 * @property BH_ModelData $_read
 * @property BH_ModelData $_recommend
 * @property BH_ModelData $_oppose
 * @property BH_ModelData $_scrap
 * @property BH_ModelData $_reg_date
 * @property BH_ModelData $_subscribe
 */
class ContentModel extends \BH_Model
{

	public function __Init(){
		$this->key[] = 'bid';
		$this->table = TABLE_CONTENT;

		$this->data['subject'] = new \BH_ModelData(ModelType::STRING, '제목');
		$this->data['subject']->maxLength = 128;
		$this->data['subject']->required = true;

		$this->data['category'] = new \BH_ModelData(ModelType::STRING, '분류');
		$this->data['category']->maxLength = 128;
		$this->data['category']->required = true;

		$this->data['bid'] = new \BH_ModelData(ModelType::STRING, '아이디', HTMLType::TEXT_ENG_SPECIAL);
		$this->data['bid']->minLength = '1';
		$this->data['bid']->maxLength = '20';
		$this->data['bid']->required = true;

		$this->data['html'] = new \BH_ModelData(ModelType::STRING, '컨텐츠파일');
		$this->data['html']->minLength = '1';
		$this->data['html']->maxLength = '256';
		$this->data['html']->required = true;

		$this->data['layout'] = new \BH_ModelData(ModelType::STRING, '레이아웃');
		$this->data['layout']->minLength = '1';
		$this->data['layout']->maxLength = '50';

		$this->data['hit'] = new \BH_ModelData(ModelType::INT, '조회수');

		$this->data['read'] = new \BH_ModelData(ModelType::INT, '읽음수');
		$this->data['recommend'] = new \BH_ModelData(ModelType::INT, '추천수');
		$this->data['oppose'] = new \BH_ModelData(ModelType::INT, '반대수');
		$this->data['scrap'] = new \BH_ModelData(ModelType::INT, '스크랩수');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::DATETIME, '등록일');

		$this->data['subscribe'] = new \BH_ModelData(ModelType::INT, 'subscribe');
		$this->data['subscribe']->defaultValue = 0;
	} // __Init

	public function DBInsert($test = false){
		$this->_reg_date->SetValue(date('Y-m-d H:i:s'));
		return parent::DBInsert($test);
	}

	public function UpdateActionCount($type, $bid){
		$qry = DB::UpdateQryObj(TABLE_CONTENT);
		$str = $qry->StrToPDO('(SELECT COUNT(*) FROM %1 WHERE `bid` = %s AND `action_type` = %s)', TABLE_CONTENT_ACTION, $bid, $type);
		$qry->AddWhere('`bid` = %s', $bid)
			->SetData($type, $str)
			->Run();
	}

	public function _ReadAction($bid){
		if(_MEMBERIS === true){
			$res = DB::InsertQryObj(TABLE_CONTENT_ACTION)
				->SetDataNum('muid', $_SESSION['member']['muid'])
				->SetDataStr('action_type', 'read')
				->SetDataStr('bid', $bid)
				->SetDataStr('reg_date', date('Y-m-d H:i:s'))
				->SetOnDuplicateDataStr('reg_date', date('Y-m-d H:i:s'))
				->Run();
			if($res->result) $this->UpdateActionCount('read', $bid);
		}
	}

	public function GetMyActions($bid, $muid){
		$qry = DB::GetListQryObj(TABLE_CONTENT_ACTION)
			->AddWhere('bid = %s', $bid)
			->AddWhere('muid = %d', $muid)
			->SetKey('action_type, reg_date');
		$arr = array();
		while($row = $qry->Get()){
			$arr[$row['action_type']] = $row['reg_date'];
		}
		return $arr;
	}

	public function InsertAction($type, $bid){
		$res = DB::InsertQryObj(TABLE_CONTENT_ACTION)
			->SetDataNum('muid', $_SESSION['member']['muid'])
			->SetDataStr('action_type', $type)
			->SetDataStr('bid', $bid)
			->SetDataStr('reg_date', date('Y-m-d H:i:s'))
			->Run();

		if($res->result) $this->UpdateActionCount($type, $bid);
		return $res;
	}

	/**
	 * @param $type
	 * @param $bid
	 * @return BH_Result
	 */
	public function DeleteAction($type, $bid){
		if(in_array($type, array('recommend', 'oppose'))){
			$data = DB::GetQryObj(TABLE_CONTENT_ACTION)
				->AddWhere('muid = %d', $_SESSION['member']['muid'])
				->AddWhere('action_type = %s', $type)
				->AddWhere('bid = %s', $bid)
				->SetKey('`reg_date`')
				->Get();

			if(!$data) return BH_Result::Init(true);

			if($data['reg_date'] <= date('Y-m-d H:i:s', strtotime('-1 day', time()))){
				return BH_Result::Init(false, '하루가 지나 취소가 불가능합니다.');
			}
		}

		$res = DB::DeleteQryObj(TABLE_CONTENT_ACTION)
			->AddWhere('muid = %d', $_SESSION['member']['muid'])
			->AddWhere('action_type = %s', $type)
			->AddWhere('bid = %s', $bid)
			->Run();

		if($res) $this->UpdateActionCount($type, $bid);
		return BH_Result::Init($res);
	}

	public function ActionDuplicationCheck($type, $bid){
		$qry = DB::GetQryObj(TABLE_CONTENT_ACTION)
			->AddWhere('muid = %d', $_SESSION['member']['muid'])
			->AddWhere('bid = %s', $bid);

		// 아래 타입은 한가지만 가능
		$onlyOne = array('recommend', 'oppose');

		if(in_array($type, $onlyOne)) $qry->AddWhere('`action_type` IN (%s)', $onlyOne);
		else $qry->AddWhere('`action_type` = %s', $type);

		return $qry->Get();
	}

}
