<?php
/**
 *
 * Bang Hun.
 * 18.11.28
 *
 */

use \BH_Common as CM;
use \BH_Application as App;

/**
 * Class MessageModel
 *
 * @property BH_ModelData[] $data
 * @property BH_ModelData $_seq
 * @property BH_ModelData $_muid
 * @property BH_ModelData $_target_muid
 * @property BH_ModelData $_reg_date
 * @property BH_ModelData $_file
 * @property BH_ModelData $_delis
 * @property BH_ModelData $_target_delis
 * @property BH_ModelData $_comment
 * @property BH_ModelData $_read_date
 * @property BH_ModelData $_report
 */
class MessageModel extends \BH_Model
{
	public function __Init(){
		$this->key= array('seq');
		$this->AddExcept('seq');
		$this->table = TABLE_MESSAGE;

		$this->data['seq'] = new \BH_ModelData(ModelType::INT, '고유값');
		$this->data['muid'] = new \BH_ModelData(ModelType::INT, '작성자 ID');
		$this->data['target_muid'] = new \BH_ModelData(ModelType::INT, '대상 ID');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::DATETIME, '발신일');
		$this->data['file'] = new \BH_ModelData(ModelType::STRING, '첨부파일', HTMLType::FILE_WITH_NAME);
		$this->data['delis'] = new \BH_ModelData(ModelType::STRING, '삭제여부');
		$this->data['delis']->defaultValue = 'n';
		$this->data['target_delis'] = new \BH_ModelData(ModelType::STRING, '대상 삭제여부');
		$this->data['target_delis']->defaultValue = 'n';

		$this->data['comment'] = new \BH_ModelData(ModelType::TEXT, '내용', HTMLType::TEXTAREA);

		$this->data['read_date'] = new \BH_ModelData(ModelType::DATETIME, '읽은 날짜', HTMLType::DATE_PICKER);

		$this->data['report'] = new \BH_ModelData(ModelType::ENUM, '신고여부', HTMLType::SELECT);
		$this->data['report']->enumValues = array('y' => '신고','n' => '미신고');
		$this->data['report']->defaultValue = 'n';
	} // __Init

	public function DBInsert($test = false){
		$this->_reg_date->SetValue(date('Y-m-d H:i:s'));
		return parent::DBInsert($test);
	}

	/**
	 * @param int $muid
	 * @return bool
	 */
	public function SendMessageIs($muid = null){
		if(is_null($muid)) $muid = $_SESSION['member']['muid'];
		return ($this->_muid->Val() == $muid);
	}

	/**
	 * @param int $muid
	 * @return bool
	 */
	public function GetTargetMuid($muid = null){
		if(is_null($muid)) $muid = $_SESSION['member']['muid'];
		return ($this->_muid->Val() == $muid ? $this->_target_muid->Val() : $this->_muid->Val());
	}

	/**
	 * 현재 데이터를 읽음상태로 변경
	 *
	 * @return $this
	 */
	public function SetRead(){
		DB::UpdateQryObj($this->table)
			->AddWhere('seq = %d', $this->_seq->value)
			->SetDataStr('read_date', date('Y-m-d H:i:s'))
			->Run();
		return $this;
	}

	/**
	 * 현재 데이터를 삭제
	 *
	 * @return BH_Result
	 */
	public function Del(){
		if(_MEMBERIS !== true) return BH_Result::Init(false, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
		if(!strlen($this->_seq->Val())){
			if(_DEVELOPERIS === true) PrintError('삭제할 데이터가 없습니다.');
			return BH_Result::Init(false, 'MESSAGE DELETE ERROR #01');
		}
		$qry = DB::UpdateQryObj($this->table)
			->AddWhere('seq = %d', $this->_seq->value);
		$qrySetIs = false;
		if($this->_muid->Val() === $_SESSION['member']['muid']){
			$qrySetIs = true;

			// 읽지 않은 메세지는 완전 삭제
			if(!$this->_read_date->Val()){
				if($this->_file->Val()) @unlink(\Paths::DirOfUpload() . $this->GetFilePath('file'));

				DB::DeleteQryObj($this->table)
					->AddWhere('seq = %d', $this->_seq->Val())
					->Run();
				return BH_Result::Init(true);
			}

			$qry->SetDataStr('delis', 'y');
		}
		if($this->_target_muid->Val() === $_SESSION['member']['muid']){
			$qrySetIs = true;
			$qry->SetDataStr('target_delis', 'y');
		}

		if(!$qrySetIs) return BH_Result::Init(false, App::$lang['MSG_WRONG_CONNECTED']);
		$qry->Run();
		return BH_Result::Init(true);
	}

	/**
	 * 대화목록
	 *
	 * @param int $muid
	 * @param int $pageNum
	 * @return BH_DB_GetListWithPage
	 */
	public static function GetListQry($muid = null, $pageNum = null){
		$qry = DB::GetListPageQryObj(TABLE_MESSAGE . ' `MSG`')
			->AddTable('LEFT JOIN %1 `M1` ON `M1`.`muid` = `MSG`.`muid`', TABLE_MEMBER)
			->AddTable('LEFT JOIN %1 `M2` ON `M2`.`muid` = `MSG`.`target_muid`', TABLE_MEMBER)
			->SetKey('`MSG`.*, `M1`.`nickname` as `sender_name`, `M2`.`nickname` as `receiver_name`')
			->SetPage($pageNum)
			->SetSort('`MSG`.`seq` DESC')
			->SetArticleCount(10);
		if(!is_null($muid)) $qry->AddWhere('(`MSG`.`muid` = %d AND `MSG`.`delis` = \'n\') OR (`MSG`.`target_muid` = %d AND `MSG`.`target_delis` = \'n\')', $muid, $muid);
		return $qry;
	}

	/**
	 * 대상 유저와의 메세지를 쳇 형식으로 가져올때 사용
	 *
	 * @param int $targetMuid
	 * @param int $articleNumber
	 * @param int $lastSeq
	 * @param bool $beforeIs
	 * @return BH_Result
	 */
	public static function GetChat($targetMuid, $articleNumber = 20, $lastSeq = null, $beforeIs = false){
		if(_MEMBERIS !== true) return BH_Result::Init(false, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
		$blockUsers = CM::GetBlockUsers();
		if(in_array($targetMuid, $blockUsers)) return BH_Result::Init(false, '차단된 유저입니다.');
		$qry = DB::GetListQryObj(TABLE_MESSAGE)
			->AddWhere('(`muid` = %d AND `delis` = \'n\') OR (`target_muid` = %d AND `target_delis` = \'n\')', $_SESSION['member']['muid'], $_SESSION['member']['muid'])
			->AddWhere('`muid` = %d OR `target_muid` = %d', $targetMuid, $targetMuid)
			->SetSort('`seq` DESC');
		if(!is_null($lastSeq)){
			if($beforeIs) $qry->AddWhere('`seq` < %d', $lastSeq)->SetLimit($articleNumber);
			else $qry->AddWhere('`seq` > %d', $lastSeq);
		}
		else $qry->SetLimit($articleNumber);
		return BH_Result::Init(true, '', $qry);
	}

	/**
	 * seq 값으로 읽음 상태로 변경
	 *
	 * @param array $seqArr
	 * @return BH_Result|void
	 */
	public static function UpdateReadArticles($seqArr){
		if(_MEMBERIS !== true) return BH_Result::Init(false, App::$lang['MSG_NEED_LOGIN'], _NEED_LOGIN);
		if(!sizeof($seqArr)) return;
		DB::UpdateQryObj(TABLE_MESSAGE)
			->AddWhere('`target_muid` = %d', $_SESSION['member']['muid'])
			->AddWhere('`seq` IN (%d)', $seqArr)
			->SetDataStr('read_date', date('Y-m-d H:i:s'))
			->Run();
	}
}
