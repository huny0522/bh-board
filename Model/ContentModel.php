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
 * @property BH_ModelData $_bid
 * @property BH_ModelData $_html
 * @property BH_ModelData $_layout
 * @property BH_ModelData $_hit
 * @property BH_ModelData $_recommend
 * @property BH_ModelData $_reg_date
 */
class ContentModel extends \BH_Model{

	public function __Init(){
		$this->Key[] = 'bid';
		$this->table = TABLE_CONTENT;

		$this->data['subject'] = new \BH_ModelData(ModelType::String, true, '제목');
		$this->data['subject']->MaxLength = 128;

		$this->data['bid'] = new \BH_ModelData(ModelType::String, true, '아이디', HTMLType::InputEngNum);
		$this->data['bid']->MinLength = '1';
		$this->data['bid']->MaxLength = '20';

		$this->data['html'] = new \BH_ModelData(ModelType::String, true, '컨텐츠파일');
		$this->data['html']->MinLength = '1';
		$this->data['html']->MaxLength = '20';

		$this->data['layout'] = new \BH_ModelData(ModelType::String, false, '레이아웃');
		$this->data['layout']->MinLength = '1';
		$this->data['layout']->MaxLength = '50';

		$this->data['hit'] = new \BH_ModelData(ModelType::Int, false, '조회수');

		$this->data['recommend'] = new \BH_ModelData(ModelType::Int, false, '추천수');
		$this->data['reg_date'] = new \BH_ModelData(ModelType::Datetime, false, '등록일');
	} // __Init

}
