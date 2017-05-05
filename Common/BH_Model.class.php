<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CF;
use \BH_Application as App;

abstract class BH_Model{
	/**
	 * @var BH_ModelData[]
	 */
	public $data = array();
	public $table = '';
	public $Key = array();
	public $Except = array();
	public $Need = array();

	public function __construct(){
		$this->__Init();
	}

	abstract public function __Init();

	public function GetDisplayName($key){
		return isset($this->data[$key]->DisplayName) ? $this->data[$key]->DisplayName : NULL;
	}

	/**
	 * POST로 넘어온 값으로 데이타를 등록
	 * GetErrorMessage 메쏘드로 에러메세지 체크
	 * @return BH_Result
	 */
	public function SetPostValues(){
		return _ModelFunc::SetPostValues($this->data, $this->Except, $this->Need);
	}

	/**
	 * BH_ModelData 등록 시 에러항목의 메세지를 불러옴
	 * @return array
	 */
	public function GetErrorMessage(){
		$ret = array();
		foreach($this->data as $k=>$v){
			$this->ValueCheck($k);
			if($v->ModelErrorMsg) $ret[] =$v->ModelErrorMsg;
		}
		return $ret;
	}

	/**
	 * 데이타를 등록
	 * @param array $Values
	 */
	public function SetDBValues($Values){
		foreach($Values as $k=>$v){
			if(isset($this->data[$k])) $this->data[$k]->Value = $v;
		}
	}

	public function GetValue($key){
		return isset($this->data[$key]->Value) ? $this->data[$key]->Value : NULL;
	}

	/**
	 * 값 유효성 검사 후 할당
	 * @param $key
	 * @param $v
	 * @return bool
	 */
	public function SetValue($key, $v){
		if(!isset($this->data[$key])) return $key.' 키값이 정의되어 있지 않습니다.';

		if(isset($v) && strlen($v)){
			$this->data[$key]->Value = trim($v);
			$this->Need[] = $key;
		}
		return true;
	}

	/**
	 * 값을 쿼리문으로 등록
	 * @param $key
	 * @param $v
	 * @return bool
	 */
	public function SetQueryValue($key, $v){
		if(!isset($this->data[$key])) Redirect('-1', 'No Key : ' . $key);

		if(!isset($v)) return true;

		$this->data[$key]->Value = $v;
		$this->data[$key]->ValueIsQuery = true;
		$this->Need[] = $key;
		return true;
	}

	public function AddExcept($ar){
		if(is_string($ar)){
			$this->Except[] = $ar;
			return;
		}
		foreach($ar as $v){
			$this->Except[] = $v;
		}
	}

	public function ValueCheck($key){
		if(in_array($key, $this->Except)) return true;
		if($this->data[$key]->ValueIsQuery) return true;
		if($this->CheckRequired($key) === false) return false;
		if(isset($this->data[$key]->Value) && strlen($this->data[$key]->Value)){
			if(_ModelFunc::CheckType($key, $this->data[$key]) === false) return false;
			if(_ModelFunc::CheckLength($key, $this->data[$key]) === false) return false;
			if(_ModelFunc::CheckValue($key, $this->data[$key]) === false) return false;
		}
		return true;
	}


	/**
	 * 값의 성격이 올바른지 검사
	 * @param $key
	 * @return bool
	 */
	public function CheckType($key){
		return _ModelFunc::CheckType($key, $this->data[$key]);
	}

	public function CheckValue($key){
		return _ModelFunc::CheckValue($key, $this->data[$key]);
	}

	public function CheckLength($key){
		return _ModelFunc::CheckLength($key, $this->data[$key]);
	}

	public function CheckRequired($key){
		if($this->data[$key]->Required == false) return true;
		if(is_null($this->GetValue($key)) || !strlen($this->GetValue($key))){
			if(!in_array($key, $this->Except) && $this->data[$key]->AutoDecrement !== true){
				$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.' 항목은 필수항목입니다.';
				return false;
			}
		}
		return true;
	}

	/**
	 * BH_ModelData에서 Enum 값을 출력
	 * @param string $Name
	 * @param bool|string $Value
	 *
	 * @return string
	 */
	public function HTMLPrintEnum($Name, $Value = false){
		if($Value === false) $Value = $this->GetValue($Name);
		return isset($this->data[$Name]->EnumValues[$Value]) ? $this->data[$Name]->EnumValues[$Value] : null;
	}

	/**
	 * BH_ModelData <label>출력
	 * @param string $Name
	 * @param bool $HtmlAttribute
	 *
	 * @return string
	 */
	public function HTMLPrintLabel($Name, $HtmlAttribute = false){
		$Attribute = '';
		if($HtmlAttribute === false) $HtmlAttribute = array();
		foreach($HtmlAttribute as $k => $row){
			$Attribute .= ' '.$k.'="'.$row.'"';
		}
		return '<label for="MD_'.$Name.'" '.$Attribute.'>'.$this->data[$Name]->DisplayName.'</label>';
	}

	/**
	 * input, select textarea 출력
	 * @param string $Name
	 * @param bool $HtmlAttribute
	 *
	 * @return string
	 */
	public function HTMLPrintInput($Name, $HtmlAttribute = false){
		return _ModelFunc::HTMLPrintInput($Name, $this->data[$Name], $HtmlAttribute);
	}

	/**
	 * 가지고 있는 BH_ModelData를 등록
	 * @return BH_InsertResult
	 * @param $test bool
	 */
	public function DBInsert($test = false){
		return _ModelFunc::DBInsert($this->table, $this->data, $this->Except, $this->Key, $this->Need, $test);
	}

	/**
	 * 가지고 있는 BH_ModelData를 업데이트
	 * @return BH_Result
	 * @param $test bool
	 */
	public function DBUpdate($test = false){
		return _ModelFunc::DBUpdate($this->table, $this->data, $this->Except, $this->Key, $this->Need, $test);
	}

	/**
	 * 키값에 해당하는 DB데이터를 한 행 가져온다.
	 * @return BH_Result
	 * @param  $keys string
	 */
	public function DBGet($keys){
		$keyData = func_get_args();

		$res = _ModelFunc::DBGet($keyData, $this->Key, $this->table);
		if($res->result === false) return $res;

		$this->SetDBValues($res->result);
		$res->result = true;
		return $res;
	}

	/**
	 * 키값에 해당하는 DB데이터를 한 행 가져온다.
	 * @param array $keyData
	 * @return BH_Result
	 */
	public function DBDelete($keyData){
		return _ModelFunc::DBDelete($keyData, $this->Key, $this->table);
	}
}
