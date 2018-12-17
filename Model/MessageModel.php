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
		$this->data['reg_date'] = new \BH_ModelData(ModelType::DATETIME, '등록일');
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
}
