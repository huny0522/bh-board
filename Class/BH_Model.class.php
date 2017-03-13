<?php
/**
 * Bang Hun.
 * 16.07.10
 */

define('ModelTypeInt', 'int');
define('ModelTypeString', 'string');
define('ModelTypeEng', 'eng');
define('ModelTypeEngNum', 'engnum');
define('ModelTypeEngSpecial', 'engspecial');
define('ModelTypeFloat', 'float');
define('ModelTypeDatetime', 'datetime');
define('ModelTypeDate', 'date');
define('ModelTypeEnum', 'enum');
define('ModelTypePassword', 'password');

define('HTMLInputText', 'text');
define('HTMLInputPassword', 'password');
define('HTMLInputRadio', 'radio');
define('HTMLInputCheckbox', 'checkbox');
define('HTMLInputFile', 'file');
define('HTMLSelect', 'select');
define('HTMLTextarea', 'textarea');

class BH_Result{
	public $result = false;
	public $message = '';
}

class BH_InsertResult{
	public $result = false;
	public $id = null;
	public $message = '';
}

class BH_ModelData{
	public $Name;
	public $Type;
	public $Required = false;
	public $DisplayName;
	public $ModelErrorMsg;
	public $MinLength = false;
	public $MaxLength = false;
	public $MinValue = false;
	public $MaxValue = false;
	public $EnumValues;
	public $Value;
	public $DefaultValue;
	public $HtmlType;
	public $AutoDecrement = false;
	public $ValueIsQuery = false;
}

abstract class BH_Model{
	/**
	 * @var BH_ModelData[]
	 */
	public $data = array();
	public $table = '';
	public $Key = array();
	public $Except = array();
	public $Need = array();
	public $errorMessage = '';

	public function __construct(){
		$this->__Init();
	}

	abstract public function __Init();

	/**
	 * 모델데이타의 값을 생성
	 * @param string $key
	 * @param string $Type
	 * @param bool $Required
	 * @param string $DisplayName
	 * @param string $HtmlType
	 */
	public function InitModelData($key, $Type = '', $Required = false, $DisplayName = '', $HtmlType = ''){
		$this->data[$key] = new BH_ModelData();
		$this->data[$key]->Name = $key;
		$this->data[$key]->Type = $Type;
		$this->data[$key]->Required = $Required;
		$this->data[$key]->DisplayName = $DisplayName;
		if($HtmlType) $this->data[$key]->HtmlType = $HtmlType;
	}

	/**
	 * POST로 넘어온 값으로 데이타를 등록
	 * GetErrorMessage 메쏘드로 에러메세지 체크
	 * @return BH_Result
	 */
	public function SetPostValues(){
		$ret = new BH_Result();
		$ret->result = true;
		foreach($this->data as $k=>$v){
			if(!in_array($k, $this->Except) && $this->data[$k]->AutoDecrement !== true){
				if(!isset($_POST[$k])){
					if(isset($this->Need) && in_array($k, $this->Need)){
						$ret->message = $this->data[$k]->ModelErrorMsg = $this->data[$k]->DisplayName.' 항목이 정의되지 않았습니다.';
						$ret->result = false;
						return $ret;
					}
				}
				else{
					if((isset($this->data[$k]->HtmlType) || $this->data[$k]->Required) && $this->data[$k]->HtmlType != HTMLInputFile){
						// if(!isset($this->Set) || !sizeof($this->Set) || (sizeof($this->Set) && in_array($k, $this->Set))){
						if(isset($_POST[$k])) $this->data[$k]->Value = $_POST[$k];
						$this->Need[] = $k;
						// }
					}
				}
			}

		}

		return $ret;
	}

	/**
	 * BH_ModelData 등록 시 에러항목의 메세지를 불러옴
	 * @return array
	 */
	public function GetErrorMessage(){
		$ret = array();
		foreach($this->data as $k=>$v){
			$this->ValueCheck($k);
			if($this->data[$k]->ModelErrorMsg) $ret[] =$this->data[$k]->ModelErrorMsg;
		}
		return $ret;
	}

	/**
	 * 데이타를 등록
	 * @param array $Values
	 */
	public function SetDBValues($Values){
		foreach($Values as $k=>$v){
			if(isset($this->data[$k])){
				$this->data[$k]->Value = $v;
			}
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
		if(!isset($this->data[$key])){
			return $key.' 키값이 정의되어 있지 않습니다.';
		}
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
		if(!isset($this->data[$key])){
			Redirect('-1', 'No Key : ' . $key);
		}
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
		if($this->data[$key]->ValueIsQuery) return true;
		if($this->CheckRequired($key) === false) return false;
		if(isset($this->data[$key]->Value) && strlen($this->data[$key]->Value)){
			if($this->CheckType($key) === false) return false;
			if($this->CheckLength($key) === false) return false;
			if($this->CheckValue($key) === false) return false;
		}
	}


	/**
	 * 값의 성격이 올바른지 검사
	 * @param $key
	 * @return bool
	 */
	public function CheckType($key){
		switch($this->data[$key]->Type){
			case ModelTypeInt:
				$val = preg_replace('/[^Z0-9\-]/','',$this->data[$key]->Value);
				if($val != $this->data[$key]->Value){
					$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 숫자만 입력 가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelTypeFloat:
				$val = preg_replace('/[^Z0-9\.\-]/','',$this->data[$key]->Value);
				if($val != $this->data[$key]->Value){
					$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 숫자만 입력 가능합니다.';
					return false;
				}
			break;
			case ModelTypeEnum:
				if(isset($this->data[$key]->EnumValues) && is_array($this->data[$key]->EnumValues) && isset($this->data[$key]->EnumValues[$this->data[$key]->Value])){
					return true;
				}else{
					$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 값이 필요합니다.';
					return false;
				}
			break;
			case ModelTypeEng:
				$val = preg_replace('/[^a-zA-Z]/','',$this->data[$key]->Value);
				if($val != $this->data[$key]->Value){
					$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문만 입력가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelTypeEngNum:
				if ( !ctype_alnum($this->data[$key]->Value) ) {
					$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문과 숫자만 입력가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelTypeEngSpecial:
				$val = preg_replace('/[^a-zA-Z0-9~!@\#$%^&*\()\-=+_\']/','',$this->data[$key]->Value);
				if($val != $this->data[$key]->Value){
					$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문과 숫자, 특수문자만 입력가능합니다.';
					return false;
				}
				return true;
			default:
				return true;
			break;

		}
	}

	public function CheckValue($key){
		if($this->data[$key]->Type == ModelTypeInt || $this->data[$key]->Type == ModelTypeFloat){
			if($this->data[$key]->MinValue !== false && $this->data[$key]->MinValue > $this->data[$key]->Value){
				$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 '.$this->data[$key]->MinValue.' 이상의 값을 입력하여 주세요.';
				return false;
			}
			if($this->data[$key]->MaxValue !== false && $this->data[$key]->MaxValue < $this->data[$key]->Value){
				$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 '.$this->data[$key]->MaxValue.' 이하의 값을 입력하여 주세요.';
				return false;
			}
		}
		return true;
	}

	public function CheckLength($key){
		if($this->data[$key]->Type == ModelTypeString){
			if($this->data[$key]->MinLength !== false && $this->data[$key]->MinLength > strlen($this->data[$key]->Value)){
				$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 '.$this->data[$key]->MinLength.'자 이상 입력하여 주세요.';
				return false;
			}
			if($this->data[$key]->MaxLength !== false && $this->data[$key]->MaxLength < strlen($this->data[$key]->Value)){
				$this->data[$key]->ModelErrorMsg = $this->data[$key]->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 '.$this->data[$key]->MaxLength.'자 이하 입력하여 주세요.';
				return false;
			}
		}
		return true;
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
	 * @param string $Value
	 *
	 * @return string
	 */
	public function HTMLPrintEnum($Name, $Value = ''){
		if($Value == '') $Value = $this->GetValue($Name);
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
		$htmlType = strtolower($this->data[$Name]->HtmlType);
		$Attribute = '';
		$val = isset($this->data[$Name]->Value) ? $this->data[$Name]->Value : $this->data[$Name]->DefaultValue;

		if($HtmlAttribute === false) $HtmlAttribute = array();

		if(!isset($HtmlAttribute['class'])) $HtmlAttribute['class'] = '';

		if($this->data[$Name]->MinLength !== false){
			$Attribute .= ' data-minlength="'.$this->data[$Name]->MinLength.'"';
		}
		if($this->data[$Name]->MaxLength !== false){
			$Attribute .= ' data-maxlength="'.$this->data[$Name]->MaxLength.'"';
			$Attribute .= ' maxlength="'.$this->data[$Name]->MaxLength.'"';
		}
		if($this->data[$Name]->MinValue !== false){
			$Attribute .= ' data-minvalue="'.$this->data[$Name]->MinValue.'"';
		}
		if($this->data[$Name]->MaxValue !== false){
			$Attribute .= ' data-maxvalue="'.$this->data[$Name]->MaxValue.'"';
		}
		if($this->data[$Name]->Required){
			$Attribute .= ' required="required"';
		}
		if($this->data[$Name]->Type == ModelTypeInt){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'numberonly';
		}

		if($this->data[$Name]->Type == ModelTypeEngNum){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engnumonly';
		}

		if($this->data[$Name]->Type == ModelTypeEng){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engonly';
		}

		if($this->data[$Name]->Type == ModelTypeEngSpecial){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engspecialonly';
		}

		if($this->data[$Name]->Type == ModelTypeDate || $this->data[$Name]->Type == ModelTypeDatetime){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'date';
			$HtmlAttribute['readonly'] = 'readonly';
		}

		foreach($HtmlAttribute as $k => $row){
			$Attribute .= ' '.$k.'="'.$row.'"';
		}

		switch($htmlType){
			case HTMLInputText:
			case HTMLInputPassword:
				return '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'" '.(isset($val) && $htmlType != HTMLInputPassword ? 'value="'.$val.'"' : '').' data-displayname="' . $this->data[$Name]->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLInputFile:
				return '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $this->data[$Name]->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLTextarea:
				return '<textarea name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $this->data[$Name]->DisplayName . '" '.$Attribute.'>'.(isset($val) ? $val : '').'</textarea>';
			break;
			case HTMLInputRadio:
			case HTMLInputCheckbox:
				$ret = '';
				if(isset($this->data[$Name]->EnumValues) && is_array($this->data[$Name]->EnumValues)){
					$i = 1;
					foreach($this->data[$Name]->EnumValues as $k=>$v){
						$checked = isset($val) && $k == $val ? ' checked="checked"' : '';

						$ret .= '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'_'.$i.'" value="'.$k.'" data-displayname="' . $this->data[$Name]->DisplayName . '" '.$Attribute.$checked.'>';
						$ret .= '<label for="MD_'.$Name.'_'.$i.'">'.$v.'</label>';
						$i++;
					}
				}
				return $ret;
			break;
			case HTMLSelect:
				$ret = '<select name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $this->data[$Name]->DisplayName . '" '.$Attribute.'>';

				if(isset($this->data[$Name]->EnumValues) && is_array($this->data[$Name]->EnumValues)){
					foreach($this->data[$Name]->EnumValues as $k=>$v){
						$selected = isset($val) && $k == $val ? ' selected="selected"' : '';

						$ret .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
					}
				}
				return $ret.'</select>';
			break;
		};
	}

	/**
	 * 가지고 있는 BH_ModelData를 등록
	 * @return BH_InsertResult
	 */
	public function DBInsert(){
		$dbInsert = new BH_DB_Insert($this->table);
		$result = new BH_InsertResult();

		foreach($this->data as $k=>$v){
			if(!isset($v->Value) && in_array($k, $this->Need)){
				$result->result = false;
				$result->message = 'ERROR#101';
				return $result;
			}

			// 예외 패스, 셋이 없거나 셋에 있는것
			if((!in_array($k, $this->Except) && (!sizeof($this->Need) || in_array($k, $this->Need)))){
				if(isset($v->Value)){
					if(in_array($k, $this->Key) && $this->data[$k]->AutoDecrement === true){
						continue;
					}
					if($v->ValueIsQuery) $dbInsert->data[$k] = $v->Value;
					else if($v->Type == ModelTypeInt) $dbInsert->data[$k] = !strlen($v->Value) && isset($v->DefaultValue) ? $v->DefaultValue : SetDBInt($v->Value);
					else if($v->Type == ModelTypeFloat) $dbInsert->data[$k] = !strlen($v->Value) && isset($v->DefaultValue) ? $v->DefaultValue : SetDBFloat($v->Value);
					else if($v->Type == ModelTypePassword) $dbInsert->data[$k] = 'PASSWORD('.SetDBText($v->Value).')';
					else $dbInsert->data[$k] = SetDBText($v->Value);
				}
			}
		}

		foreach($this->Key as $k){
			if($this->data[$k]->AutoDecrement === true){
				$dbInsert->decrement = $k;
			}
			else if($this->data[$k]->Value) $dbInsert->AddWhere($k.'='.SetDBText($this->data[$k]->Value));
		}
		if(!$dbInsert->decrement) $dbInsert->UnsetWhere();
		//$params->test = true;
		$dbInsert->Run();
		$result->id = $dbInsert->id;
		$result->message = $dbInsert->message;
		$result->result = $dbInsert->result;
		return $result;
	}

	/**
	 * 가지고 있는 BH_ModelData를 업데이트
	 * @return BH_Result
	 */
	public function DBUpdate(){
		$result = new BH_Result();

		$dbUpdate = new BH_DB_Update($this->table);
		foreach($this->data as $k=>$v){
			if(!isset($v->Value) && in_array($k, $this->Need)){
				$result->result = false;
				$result->message = 'ERROR';
				return $result;
			}

			// 예외와 키값 패스, 셋이 없거나 셋에 있는것
			if(!in_array($k, $this->Except) && (!sizeof($this->Need) || in_array($k, $this->Need)) && !in_array($k, $this->Key)){
				if(isset($v->Value)){
					if(in_array($k, $this->Key) && $this->data[$k]->AutoDecrement === true){
						continue;
					}
					if($v->ValueIsQuery) $dbUpdate->data[$k] = $v->Value;
					else if($v->Type == ModelTypeInt) $dbUpdate->data[$k] = SetDBInt($v->Value);
					else if($v->Type == ModelTypeFloat) $dbUpdate->data[$k] = SetDBFloat($v->Value);
					else if($v->Type == ModelTypePassword) $dbUpdate->data[$k] = 'PASSWORD('.SetDBText($v->Value).')';
					else $dbUpdate->data[$k] = SetDBText($v->Value);
				}
			}
		}
		foreach($this->Key as $k){
			if(isset($this->data[$k]->Value) && strlen($this->data[$k]->Value)) $dbUpdate->AddWhere($k.'='.SetDBText($this->data[$k]->Value));
			else{
				$result->message = 'Empty Key';
				$result->result = false;
				return $result;
			}
		}
		// $dbUpdate->test = true;
		$dbUpdate->Run();
		$result->result = $dbUpdate->result;
		$result->message = $dbUpdate->message;
		return $result;
	}

	/**
	 * 키값에 해당하는 DB데이터를 한 행 가져온다.
	 * @param array $keyData
	 * @return BH_Result
	 */
	public function DBGet($keyData){
		$res = new BH_Result();

		if(!is_array($keyData)){
			$keyData = array($keyData);
		}
		if(!isset($this->Key) || !is_array($this->Key)){
			if(_DEVELOPERIS === true){
				echo '키값이 존재하지 않습니다.';
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keyData) != sizeof($this->Key)){
			if(_DEVELOPERIS === true){
				echo '모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.';
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}
		$dbGet = new BH_DB_Get($this->table);
		foreach($this->Key as $k => $v){
			$dbGet->AddWhere($v.' = '.SetDBTrimText($keyData[$k]));
		}
		//$dbGet->test = true;

		$data = $dbGet->Get();

		if($data !== false){
			$this->SetDBValues($data);
			$res->result = true;
		}else{
			$res->result = false;
		}
		return $res;
	}

	/**
	 * 키값에 해당하는 DB데이터를 한 행 가져온다.
	 * @param array $keyData
	 * @return BH_Result
	 */
	public function DBDelete($keyData){
		$res = new BH_Result();

		if(!is_array($keyData)){
			$keyData = array($keyData);
		}
		if(!isset($this->Key) || !is_array($this->Key)){
			if(_DEVELOPERIS === true){
				echo '키값이 존재하지 않습니다.';
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keyData) != sizeof($this->Key)){
			if(_DEVELOPERIS === true){
				echo '모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.';
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}
		$params['table'] = $this->table;
		$params['where'] = array();
		foreach($this->Key as $k => $v){
			$params['where'][] = $v.' = '.SetDBTrimText($keyData[$k]);
		}

		if(!sizeof($params['where'])){
			$res->result = false;
			$res->message = 'ERROR#03';
			return $res;
		}

		$sql = 'DELETE FROM '.$params['table'].' WHERE '.implode(' AND ', $params['where']);
		$res->result = SqlQuery($sql);
		return $res;
	}

}