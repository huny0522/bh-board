<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CF;
use \BH_Application as App;
class ModelType{
	const Int = 1;
	const String = 2;
	const Eng = 3;
	const EngNum = 4;
	const EngSpecial = 5;
	const Float = 6;
	const Datetime = 7;
	const Date = 8;
	const Enum = 9;
	const Password = 10;
}

class HTMLType{
	const InputText = 'text';
	const InputPassword = 'password';
	const InputRadio = 'radio';
	const InputCheckbox = 'checkbox';
	const InputFile = 'file';
	const Select = 'select';
	const Textarea = 'textarea';
}

class BH_ModelData{
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

	public function __construct($Type = ModelType::String, $Required = false, $DisplayName = '', $HtmlType = HTMLType::InputText){
		$this->Type = $Type;
		$this->Required = $Required;
		$this->DisplayName = $DisplayName;
		if($HtmlType) $this->HtmlType = $HtmlType;
	}
}

class BH_Model{
	/**
	 * @var BH_ModelData[]
	 */
	public $data = array();
	public $table = '';
	public $Key = array();
	public $Except = array();
	public $Need = array();
	protected $ConnName = '';

	protected function __construct(){
		$this->ConnName = \DB::DefaultConnName;
		if(method_exists($this, '__Init')) $this->__Init();
	}

	public function &SetConnName($str){
		$this->ConnName = $str;
		return $this;
	}

	public function GetConnName(){
		return $this->ConnName;
	}

	/**
	 * 모델 생성
	 * @return static
	 */
	public static function &Get(){
		static $Instance;
		if(isset($Instance)) return $Instance;

		$class = get_called_class();
		$Instance = new $class();
		return $Instance;
	}

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
		return _ModelFunc::DBInsert($this->ConnName, $this->table, $this->data, $this->Except, $this->Key, $this->Need, $test);
	}

	/**
	 * 가지고 있는 BH_ModelData를 업데이트
	 * @return BH_Result
	 * @param $test bool
	 */
	public function DBUpdate($test = false){
		return _ModelFunc::DBUpdate($this->ConnName, $this->table, $this->data, $this->Except, $this->Key, $this->Need, $test);
	}

	/**
	 * 키값에 해당하는 DB데이터를 한 행 가져온다.
	 * @return BH_Result
	 * @param  $keys string
	 */
	public function DBGet($keys){
		$keyData = is_array($keys) ? $keys : func_get_args();

		$res = _ModelFunc::DBGet($this->ConnName, $keyData, $this->Key, $this->table);
		if($res->result === false) return $res;

		$this->SetDBValues($res->result);
		$res->result = true;
		return $res;
	}

	/**
	 * 키값에 해당하는 DB데이터를 한 행 가져온다.
	 * @param array $keys
	 * @return BH_Result
	 */
	public function DBDelete($keys){
		$keyData = is_array($keys) ? $keys : func_get_args();
		return _ModelFunc::DBDelete($this->ConnName, $keyData, $this->Key, $this->table);
	}
}

class _ModelFunc{
	public static function SetPostValues(&$Data, $Except, &$Need){
		$ret = new \BH_Result();
		$ret->result = true;
		foreach($Data as $k => &$v){
			if(!in_array($k, $Except) && $v->AutoDecrement !== true){
				if(!isset($_POST[$k])){
					if(isset($Need) && in_array($k, $Need)){
						$ret->message = $v->ModelErrorMsg = $v->DisplayName.' 항목이 정의되지 않았습니다.';
						$ret->result = false;
						return $ret;
					}
				}
				else{
					if((isset($v->HtmlType) || $v->Required) && $v->HtmlType != HTMLType::InputFile){
						if(isset($_POST[$k])) $v->Value = $_POST[$k];
						$Need[] = $k;
					}
				}
			}
		}
		return $ret;
	}

	public static function CheckType($key, &$data){
		switch($data->Type){
			case ModelType::Int:
				$val = preg_replace('/[^Z0-9\-]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 숫자만 입력 가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelType::Float:
				$val = preg_replace('/[^Z0-9\.\-]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 숫자만 입력 가능합니다.';
					return false;
				}
			break;
			case ModelType::Enum:
				if(isset($data->EnumValues) && is_array($data->EnumValues) && isset($data->EnumValues[$data->Value])) return true;
				else{
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 값이 필요합니다.';
					return false;
				}
			break;
			case ModelType::Eng:
				$val = preg_replace('/[^a-zA-Z]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문만 입력가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelType::EngNum:
				if ( !ctype_alnum($data->Value) ){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문과 숫자만 입력가능합니다.';
					return false;
				}
				return true;
			break;
			case ModelType::EngSpecial:
				$val = preg_replace('/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>\'\"\?\-=\+_\:\;\[\]\{\}\/]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문과 숫자, 특수문자만 입력가능합니다.';
					return false;
				}
				return true;
		}
		return true;
	}

	public static function CheckValue($key, &$Data){
		if($Data->Type == ModelType::Int || $Data->Type == ModelType::Float){
			if($Data->MinValue !== false && $Data->MinValue > $Data->Value){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 '.$Data->MinValue.' 이상의 값을 입력하여 주세요.';
				return false;
			}
			if($Data->MaxValue !== false && $Data->MaxValue < $Data->Value){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 '.$Data->MaxValue.' 이하의 값을 입력하여 주세요.';
				return false;
			}
		}
		return true;
	}

	public static function CheckLength($key, &$Data){
		if($Data->Type == ModelType::String){
			if($Data->MinLength !== false && $Data->MinLength > strlen($Data->Value)){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 '.$Data->MinLength.'자 이상 입력하여 주세요.';
				return false;
			}
			if($Data->MaxLength !== false && $Data->MaxLength < strlen($Data->Value)){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 '.$Data->MaxLength.'자 이하 입력하여 주세요.';
				return false;
			}
		}
		return true;
	}

	public static function HTMLPrintInput($Name, $data, $HtmlAttribute = false){
		$htmlType = strtolower($data->HtmlType);
		$Attribute = '';
		$val = isset($data->Value) ? $data->Value : $data->DefaultValue;

		if($HtmlAttribute === false) $HtmlAttribute = array();

		if(!isset($HtmlAttribute['class'])) $HtmlAttribute['class'] = '';

		if($data->MinLength !== false) $Attribute .= ' data-minlength="'.$data->MinLength.'"';

		if($data->MaxLength !== false){
			$Attribute .= ' data-maxlength="'.$data->MaxLength.'"';
			$Attribute .= ' maxlength="'.$data->MaxLength.'"';
		}
		if($data->MinValue !== false) $Attribute .= ' data-minvalue="'.$data->MinValue.'"';

		if($data->MaxValue !== false) $Attribute .= ' data-maxvalue="'.$data->MaxValue.'"';

		if($data->Required) $Attribute .= ' required="required"';

		if($data->Type == ModelType::Int) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'numberonly';

		if($data->Type == ModelType::EngNum) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engnumonly';

		if($data->Type == ModelType::Eng) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engonly';

		if($data->Type == ModelType::EngSpecial) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'engspecialonly';

		if($data->Type == ModelType::Date || $data->Type == ModelType::Datetime){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'date';
			$HtmlAttribute['maxlength'] = '10';
			$HtmlAttribute['minlength'] = '10';
		}

		foreach($HtmlAttribute as $k => $row) $Attribute .= ' '.$k.'="'.$row.'"';

		switch($htmlType){
			case HTMLType::InputText:
			case HTMLType::InputPassword:
				return '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'" '.(isset($val) && $htmlType != HTMLType::InputPassword ? 'value="'.GetDBText($val).'"' : '').' data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLType::InputFile:
				return '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLType::Textarea:
				return '<textarea name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>'.(isset($val) ? GetDBText($val) : '').'</textarea>';
			break;
			case HTMLType::InputRadio:
			case HTMLType::InputCheckbox:
				$ret = '';
				if(isset($data->EnumValues) && is_array($data->EnumValues)){
					$i = 1;
					foreach($data->EnumValues as $k=>$v){
						$checked = isset($val) && $k == $val ? ' checked="checked"' : '';

						$ret .= '<input type="'.$htmlType.'" name="'.$Name.'" id="MD_'.$Name.'_'.$i.'" value="'.$k.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.$checked.'>';
						$ret .= '<label for="MD_'.$Name.'_'.$i.'">'.$v.'</label>';
						$i++;
					}
				}
				return $ret;
			break;
			case HTMLType::Select:
				$ret = '<select name="'.$Name.'" id="MD_'.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';

				if(isset($data->EnumValues) && is_array($data->EnumValues)){
					foreach($data->EnumValues as $k=>$v){
						$selected = isset($val) && $k == $val ? ' selected="selected"' : '';

						$ret .= '<option value="'.$k.'" '.$selected.'>'.GetDBText($v).'</option>';
					}
				}
				return $ret.'</select>';
			break;
		};
		return '';
	}

	public static function DBInsert($ConnName, $Table, $Data, $Except, $Key, $Need, $test = false){
		$dbInsert = new \BH_DB_Insert($Table);
		$dbInsert->SetConnName($ConnName);
		$result = new \BH_InsertResult();

		foreach($Data as $k=>$v){
			if(!isset($v->Value) && in_array($k, $Need)){
				$result->result = false;
				$result->message = 'ERROR#101';
				return $result;
			}

			// 예외 패스, 셋이 없거나 셋에 있는것
			if((!in_array($k, $Except) && (!sizeof($Need) || in_array($k, $Need)))){
				if(isset($v->Value)){
					if(in_array($k, $Key) && $v->AutoDecrement === true) continue;

					if($v->ValueIsQuery) $dbInsert->data[$k] = $v->Value;
					else if($v->Type == ModelType::Int){
						if(!strlen($v->Value) && isset($v->DefaultValue)) $dbInsert->data[$k] = $v->DefaultValue;
						else{
							$res = self::CheckInt($k, $v->Value);
							if($res === true) $dbInsert->data[$k] = $v->Value;
							else{
								$result->result = false;
								$result->message = $res;
							}
						}
					}
					else if($v->Type == ModelType::Float){
						if(!strlen($v->Value) && isset($v->DefaultValue)) $dbInsert->data[$k] = $v->DefaultValue;
						else{
							$res = self::CheckFloat($k, $v->Value);
							if($res === true) $dbInsert->data[$k] = $v->Value;
							else{
								$result->result = false;
								$result->message = $res;
							}
						}
					}
					else if($v->Type == ModelType::Password) $dbInsert->data[$k] = SetDBText(_password_hash($v->Value));
					else $dbInsert->data[$k] = SetDBText($v->Value);
				}
			}
		}

		foreach($Key as $k){
			if($Data[$k]->AutoDecrement === true) $dbInsert->decrement = $k;
			else if($Data[$k]->Value) $dbInsert->AddWhere($k.'='.SetDBText($Data[$k]->Value));
		}
		if(!$dbInsert->decrement) $dbInsert->UnsetWhere();
		if(_DEVELOPERIS === true) $dbInsert->test = $test;
		$result = $dbInsert->Run();
		return $result;
	}

	public static function DBUpdate($ConnName, $Table, $Data, $Except, $Key, $Need, $test = false){
		$result = new \BH_Result();

		$dbUpdate = new \BH_DB_Update($Table);
		$dbUpdate->SetConnName($ConnName);
		foreach($Data as $k=>$v){
			if(!isset($v->Value) && in_array($k, $Need)){
				$result->result = false;
				$result->message = 'ERROR';
				return $result;
			}

			// 예외와 키값 패스, 셋이 없거나 셋에 있는것
			if(!in_array($k, $Except) && (!sizeof($Need) || in_array($k, $Need)) && !in_array($k, $Key)){
				if(isset($v->Value)){
					if(in_array($k, $Key) && $v->AutoDecrement === true) continue;

					if($v->ValueIsQuery) $dbUpdate->data[$k] = $v->Value;
					else if($v->Type == ModelType::Int){
						$res = self::CheckInt($k, $v->Value);
						if($res === true) $dbUpdate->data[$k] = $v->Value;
						else{
							$result->result = false;
							$result->message = $res;
						}
					}
					else if($v->Type == ModelType::Float){
						$res = self::CheckFloat($k, $v->Value);
						if($res === true) $dbUpdate->data[$k] = $v->Value;
						else{
							$result->result = false;
							$result->message = $res;
						}
					}
					else if($v->Type == ModelType::Password) $dbUpdate->data[$k] = SetDBText(_password_hash($v->Value));
					else $dbUpdate->data[$k] = SetDBText($v->Value);
				}
			}
		}
		foreach($Key as $k){
			if(isset($Data[$k]->Value) && strlen($Data[$k]->Value)) $dbUpdate->AddWhere($k.'='.SetDBText($Data[$k]->Value));
			else{
				$result->message = 'Empty Key';
				$result->result = false;
				return $result;
			}
		}

		if(_DEVELOPERIS === true) $dbUpdate->test = $test;
		$result = $dbUpdate->Run();
		return $result;
	}

	public static function DBGet($ConnName, $keys, $modelKey, $table){
		$res = new \BH_Result();

		if(!isset($modelKey) || !is_array($modelKey)){
			if(_DEVELOPERIS === true){
				echo '키값이 존재하지 않습니다.';
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keys) != sizeof($modelKey)){
			if(_DEVELOPERIS === true){
				echo '모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.';
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}
		$dbGet = new \BH_DB_Get($table);
		$dbGet->SetConnName($ConnName);
		foreach($modelKey as $k => $v) $dbGet->AddWhere($v.' = '.SetDBTrimText($keys[$k]));
		$data = $dbGet->Get();

		if($data !== false) $res->result = $data;
		else $res->result = false;

		return $res;
	}

	public static function DBDelete($ConnName, $keyData, $ModelKey, $Table){
		$res = new \BH_Result();

		if(!is_array($keyData)) $keyData = array($keyData);

		if(!isset($ModelKey) || !is_array($ModelKey)){
			if(_DEVELOPERIS === true){
				echo '키값이 존재하지 않습니다.';
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keyData) != sizeof($ModelKey)){
			if(_DEVELOPERIS === true){
				echo '모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.';
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}
		$params['table'] = $Table;
		$params['where'] = array();
		foreach($ModelKey as $k => $v) $params['where'][] = $v.' = '.SetDBTrimText($keyData[$k]);

		if(!sizeof($params['where'])){
			$res->result = false;
			$res->message = 'ERROR#03';
			return $res;
		}

		$sql = 'DELETE FROM '.$params['table'].' WHERE '.implode(' AND ', $params['where']);
		$res->result = DB::SQL($ConnName)->Query($sql);
		\BH_DB_Cache::DelPath($ConnName, $params['table']);
		return $res;
	}

	public static function CheckInt($k, $v){
		if(!strlen($v)){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자값이 비어있습니다.');
				else Redirect('-1', '['.$k.']숫자값이 비어있습니다.');
			}else return 'ERROR#102';
		}
		$val = ToInt($v);
		if($val != $v){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
				else Redirect('-1', '['.$k.']숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
			}else return 'ERROR#103';
		}
		return true;
	}

	public static function CheckFloat($k, $v){
		if(!strlen($v)){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자값이 비어있습니다.');
				else Redirect('-1', '['.$k.']숫자값이 비어있습니다.');
			}else return 'ERROR#112';
		}
		$val = ToFloat($v);
		if($val != $v){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자(소수)가 들아갈 항목에 문자가 들어갈 수 없습니다.');
				else Redirect('-1', '['.$k.']숫자(소수)가 들아갈 항목에 문자가 들어갈 수 없습니다.');
			}else return 'ERROR#113';
		}
		return true;
	}
}

