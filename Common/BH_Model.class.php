<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class ModelType{
	const INT = 1;
	const STRING = 2;
	const FLOAT = 6;
	const DATETIME = 7;
	const DATE = 8;
	const ENUM = 9;
	const TEXT = 10;
}

class HTMLType{
	const TEXT = 'text';
	const NUMBER = 'number';
	const PASSWORD = 'password';
	const RADIO = 'radio';
	const CHECKBOX = 'checkbox';
	const EMAIL = 'email';
	const TEL = 'tel';
	const FILE = 'file';
	const FILE_WITH_NAME = 'filewithname';
	/**
	 * 아래 플러그인이 필요합니다.
	 * composer require blueimp/jquery-file-upload
	 */
	const FILE_JQUERY = 'jqueryfile';
	const FILE_IMAGE = 'imagefile';
	const FILE_IMAGE_ARRAY = 'imagefilearray';
	const SELECT = 'select';
	const TEXTAREA = 'textarea';
	const DATE = 'date';
	const DATE_PICKER = 'datepicker';
	const NUMBER_FORMAT = 'numberformat';
	const TEXT_ENG_ONLY = 'engonly';
	const TEXT_ENG_NUM = 'engnumonly';
	const TEXT_ENG_SPECIAL = 'engspecialonly';
}

class BH_ModelDataArray extends ArrayObject{
	private $parent;
	public function SetParent($obj){
		$this->parent = $obj;
	}

	public function offsetSet($index, $newval){
		if(!is_object($newval) || get_class($newval) !== 'BH_ModelData') PrintError('모델데이터만 등록가능합니다.');
		$newval->parent = $this->parent;
		$newval->keyName = $index;
		parent::offsetSet($index, $newval);
	}
}

class BH_ModelData
{
	public $type;
	public $required = false;
	public $displayName;
	public $modelErrorMsg;
	public $minLength = false;
	public $maxLength = false;
	public $minValue = false;
	public $maxValue = false;
	public $enumValues;
	public $value;
	public $defaultValue;
	public $htmlType;
	public $autoDecrement = false;
	public $valueIsQuery = false;
	public $blankIsNull = false;
	public $possibleExt;
	public $keyName = null;
	/**
	 * @var array
	 * @option string maxFileSize : InputFileJQuery 에서 파일 최대 용량
	 * @option array possibleExt : InputFileJQuery 에서 허용 확장자
	 */
	public $addOption = array();

	public $needIs = false;
	public $idFirst = 'MD_';

	public $dbExcept = false; // DB에 등록을 하지 않음. 자동증가 같은 경우
	public $postExcept = false; // POST로 넘어오는 값을 DB에 등록하지 않음

	/**
	 * @var BH_Model
	 */
	public $parent = null;

	public function __construct($type = ModelType::STRING, $displayName = '', $htmlType = HTMLType::TEXT){
		$this->type = $type;
		$this->displayName = $displayName;
		if($htmlType) $this->htmlType = $htmlType;
	}

	public function __debugInfo() {
		$res = array();
		foreach($this as $k => $v){
			if($k != 'parent') $res[$k] = $v;
		}
		return $res;
	}

	/**
	 * 값을 반환(enum은 해당 값을 반환)
	 *
	 * @return string
	 */
	public function Val(){
		if(!isset($this->value)) return '';
		if($this->type == ModelType::ENUM) return $this->GetEnumValues();
		return $this->value;
	}

	/**
	 * 값을 반환(enum은 해당 값을 반환, htmlspecialchars)
	 *
	 * @return string
	 */
	public function Safe(){
		if(!isset($this->value)) return '';
		if($this->type == ModelType::ENUM) return GetDBText($this->GetEnumValues());
		return GetDBText($this->value);
	}

	/**
	 * 실제 등록된 원본값을 반환
	 *
	 * @return string|null
	 */
	public function Txt(){
		return isset($this->value) ? $this->value : NULL;
	}

	/**
	 * 숫자 number_format 반환
	 *
	 * @return string
	 */
	public function Num(){
		return isset($this->value) ? number_format($this->value) : 0;
	}

	/**
	 * 값을 반환(html 태그 출력)
	 *
	 * @return string
	 */
	public function SafeRaw(){
		if(!isset($this->value)) return '';
		if($this->type == ModelType::ENUM) return GetDBRaw($this->GetEnumValues());
		return GetDBRaw($this->value);
	}

	/**
	 * 값을 반환(htmlspecialchars, nl2br)
	 *
	 * @return string
	 */
	public function SafeBr(){
		if(!isset($this->value)) return '';
		if($this->type == ModelType::ENUM) return nl2br(GetDBText($this->GetEnumValues()));
		return nl2br(GetDBText($this->value));
	}

	public function GetEnumValues($val = false){
		if($val === false) $val = $this->value;
		if($this->htmlType === HTMLType::CHECKBOX){
			$e = explode(',', $val);
			$t = array();
			foreach($e as $v){
				if(isset($this->enumValues[$v])) $t[] = $this->enumValues[$v];
			}
			return implode(', ', $t);
		}
		else if(isset($this->enumValues[$val])) return $this->enumValues[$val];
		else return '';
	}

	public function &SetValue($v){
		$this->value = trim($v);
		$this->needIs = true;
		return $this;
	}

	public function &SetRequired($bool = true){
		$this->required = $bool;
		return $this;
	}

	public function &SetMinLength($num){
		$this->minLength = $num;
		return $this;
	}

	public function &SetMaxLength($num){
		$this->maxLength = $num;
		return $this;
	}

	public function &SetMinValue($num){
		$this->minValue = $num;
		return $this;
	}

	public function &SetMaxValue($num){
		$this->maxValue = $num;
		return $this;
	}

	public function &SetEnumValues($array){
		$this->enumValues = $array;
		return $this;
	}

	public function &SetDefaultValue($val){
		$this->defaultValue = $val;
		return $this;
	}

	public function &SetType($type){
		$this->type = $type;
		return $this;
	}

	public function &SetDisplayName($str){
		$this->displayName = $str;
		return $this;
	}

	public function &SetModelErrorMsg($str){
		$this->modelErrorMsg = $str;
		return $this;
	}

	public function &SetHtmlType($str){
		$this->htmlType = $str;
		return $this;
	}

	public function &SetPossibleExt($arr){
		$this->possibleExt = $arr;
		return $this;
	}

	public function &SetAutoDecrement($bool = true){
		$this->autoDecrement = $bool;
		return $this;
	}

	public function &SetValueIsQuery($bool = true){
		$this->valueIsQuery = $bool;
		$this->needIs = true;
		return $this;
	}

	public function &SetBlankIsNull($bool = true){
		$this->blankIsNull = $bool;
		return $this;
	}

	public function &SetFileSize($mb){
		$this->maxFileSize = $mb;
		return $this;
	}

	public function &SetDBExcept($bool = true){
		$this->dbExcept = $bool;
		return $this;
	}

	public function &SetPostExcept($bool = true){
		$this->postExcept = $bool;
		return $this;
	}

	public function GetKeyName(){
		return $this->keyName;
	}

	public function &SetIdFirst($str){
		$this->idFirst = $str;
		return $this;
	}

	public function HtmlPrintLabel($htmlAttribute = array(), $callback = null){
		return _ModelFunc::HTMLPrintLabel($this, $this->GetKeyName(), $htmlAttribute, $callback, $this->idFirst);
	}

	public function HTMLPrintInput($htmlAttribute = array()){
		return _ModelFunc::HTMLPrintInput($this->GetKeyName(), $this, $htmlAttribute, $this->idFirst);
	}
}

/**
 * Class BH_Model
 * @property array $need
 * @property array $except
 */
class BH_Model{
	/**
	 * @var BH_ModelData[]
	 */
	public $data;
	public $table = '';
	public $key = array();
	// public $except = array();
	public $getKeys = array();
	public $showError = true;
	//public $need = array();
	public $uploadDir = '';
	protected $connName = '';
	private $dataExcept = array();
	public $isBHModel = true;
	public $naming = '';

	public function __construct($connName = ''){
		$this->data = new BH_ModelDataArray();
		$this->data->SetParent($this);
		$this->connName = ($connName === '') ? \DB::DefaultConnName : $connName;
		if(method_exists($this, '__Init')) $this->__Init();
		$this->uploadDir = '/modelData/' . $this->table . '/' . date('Ym') . '/';
		foreach($this->data as $k => $v) if(!isset($this->{'_'.$k})) $this->{'_'.$k} = $v;
		foreach($this->dataExcept as $v) if(isset($this->data[$v])) $this->data[$v]->dbExcept = true;
	}

	/**
	 * @param string $connName
	 * @return static
	 */
	public static function GetInstance($connName = ''){
		$static = new static($connName);
		return $static;
	}

	/**
	 * @param string $str
	 * @return static
	 */
	public function SetNaming($str){
		$this->naming = $str;
		return $this;
	}

	public function SetShowError($bool = true){
		$this->showError = $bool;
		return $this;
	}

	public function DataUnset(){
		foreach($this->data as $k => $v){
			unset($this->data[$k]);
		}
	}

	public function __set($name, $value){
		if(is_object($value) && get_class($value) === 'BH_ModelData' && $name[0] === '_'){
			$keyName = substr($name, 1);
			if(!isset($this->data[$keyName]) || is_null($this->data[$keyName])) $this->data[$keyName] = $value;
			$this->{$name} = $this->data[$keyName];
		}
		else if($name === 'need'){
			if(!is_array($value)) $value = array($value);
			call_user_func_array(array($this, 'SetNeedData'), $value);
		}
		else if($name === 'except'){
			if(!is_array($value)) $value = array($value);
			call_user_func_array(array($this, 'SetDBExcept'), $value);
		}
	}

	/**
	 * @param $name
	 * @return array|BH_ModelData
	 */
	public function __get($name){
		if($name === 'Need'){
			$res = array();
			foreach($this->data as $k => $v){
				if($v->needIs) $res[] = $k;
			}
			return $res;
		}
		else if($name[0] === '_' && isset($this->data[substr($name, 1)])){
			$this->{$name} = $this->data[substr($name, 1)];
			return $this->{$name};
		}
	}

	public function SetNeedData($str){
		$args = is_array($str) ? $str : func_get_args();
		for($i = 0, $i2 = sizeof($args); $i < $i2; $i++){
			if(isset($this->data[$args[$i]])) $this->data[$args[$i]]->needIs = true;
		}
	}

	public function SetDBExcept($str){
		$args = is_array($str) ? $str : func_get_args();
		for($i = 0, $i2 = sizeof($args); $i < $i2; $i++){
			if(isset($this->data[$args[$i]])) $this->data[$args[$i]]->dbExcept = true;
			else $this->dataExcept[]= $args[$i];
		}
	}

	/**
	 * 정의 된 키의 DB 선택
	 * @param $str
	 * @return $this
	 */
	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	/**
	 * 현재 DB의 정의 키
	 * @return string
	 */
	public function GetConnName(){
		return $this->connName;
	}

	/**
	 * 표시명 반환
	 * @param string $key
	 * @return null|string
	 */
	public function GetDisplayName($key){
		return isset($this->data[$key]->displayName) ? $this->data[$key]->displayName : NULL;
	}

	/**
	 * POST로 넘어온 값으로 데이타를 등록
	 * GetErrorMessage 메쏘드로 에러메세지 체크
	 * @param array|null $val
	 * @return BH_Result
	 */
	public function SetPostValues($val = null){
		if(is_array($val)) $ref =  &$val;
		else $ref = &$_POST;

		return _ModelFunc::SetPostValues($this, $ref);
	}

	/**
	 * POST로 넘어온 값으로 데이타를 등록
	 * GetErrorMessage 메쏘드로 에러메세지 체크
	 * @param array|null $val
	 * @return BH_Result
	 */
	public function SetPostValuesWithFile($val = null){
		if(is_array($val)) $ref =  &$val;
		else $ref = &$_POST;

		return _ModelFunc::SetPostValues($this, $ref, true);
	}

	/**
	 * BH_ModelData 등록 시 에러항목의 메세지를 불러옴
	 * @return array
	 */
	public function GetErrorMessage(){
		$ret = array();
		_ModelFunc::GetErrorMessage($this, $ret);
		return $ret;
	}

	/**
	 * DB에서 가져온 데이터를 모델에 등록
	 * @param array $Values
	 */
	public function SetArrayToData($Values){
		foreach($this->data as $k => $v){
			if(isset($Values[$k])) $this->data[$k]->value = $Values[$k];
			else{
				$this->data[$k]->value = null;
				$this->data[$k]->needIs = false;
			}
		}
	}

	/**
	 * DB에서 가져온 데이터를 모델에 등록
	 * @deprecated
	 * @param array $Values
	 */
	public function SetDBValues($Values){
		$this->SetArrayToData($Values);
	}

	/**
	 * @return static
	 */
	public function SetDBExceptAll(){
		foreach($this->data as $k => $v){
			$this->data[$k]->dbExcept = true;
		}
		return $this;
	}

	/**
	 * @return static
	 */
	public function CleanDataValue(){
		foreach($this->data as $k => $v){
			$this->data[$k]->value = null;
			$this->data[$k]->needIs = false;
		}
		return $this;
	}

	/**
	 * 데이타의 값 반환
	 * @param string $key
	 * @param bool $enumVal true일경우 Enum키의 값을 반환
	 * @return null|string
	 */
	public function GetValue($key, $enumVal = false){
		return isset($this->data[$key]->value) ? ($enumVal && $this->data[$key]->type == ModelType::ENUM ? $this->data[$key]->GetEnumValues() : $this->data[$key]->value) : NULL;
	}

	public function GetSafeValue($key, $enumVal = true){
		if(!isset($this->data[$key]->value)) return '';
		if($enumVal && $this->data[$key]->type == ModelType::ENUM) return GetDBText($this->data[$key]->GetEnumValues());
		return GetDBText($this->data[$key]->value);
	}

	public function GetSafeRawValue($key, $enumVal = true){
		if(!isset($this->data[$key]->value)) return '';
		if($enumVal && $this->data[$key]->type == ModelType::ENUM) return GetDBRaw($this->data[$key]->GetEnumValues());
		return GetDBRaw($this->data[$key]->value);
	}

	public function GetSafeBRValue($key, $enumVal = true){
		if(!isset($this->data[$key]->value)) return '';
		if($enumVal && $this->data[$key]->type == ModelType::ENUM) return nl2br(GetDBText($this->data[$key]->GetEnumValues()));
		return nl2br(GetDBText($this->data[$key]->value));
	}

	public function GetFileName($key, $n = 0){
		return _ModelFunc::GetFilePath($this->data[$key], 1, $n);
	}

	public function GetFilePath($key, $n = 0){
		return _ModelFunc::GetFilePath($this->data[$key], 0, $n);
	}

	public function GetFileNameByValue($value, $n = 0){
		return _ModelFunc::GetFilePathByValue($value, 1, $n);
	}

	public function GetFilePathByValue($value, $n = 0){
		return _ModelFunc::GetFilePathByValue($value, 0, $n);
	}

	/**
	 * 값 유효성 검사 후 할당
	 * @param array|string $key
	 * @param string $v
	 * @return true|string|array
	 */
	public function SetValue($key, $v = ''){
		if(is_array($key)){
			$r = array();
			foreach($key as $k2 => $v2){
				$res = $this->SetValue($k2, $v2);
				if($res !== true) $r[] = $res;
			}
			return sizeof($r) ? $r : true;
		}
		if(!isset($this->data[$key])) return str_replace('{key}', $key, BH_Application::$lang['MODEL_NOT_DEFINED_KEY']);

		$this->data[$key]->value = trim($v);
		$this->data[$key]->needIs = true;
		return true;
	}

	/**
	 * @param string $key
	 */
	public function SetFileValue($key){
		_ModelFunc::SetFileValue($this, $key);
	}

	/**
	 * 값을 쿼리문으로 등록
	 * @param $key
	 * @param $v
	 * @return bool
	 */
	public function SetQueryValue($key, $v){
		if(!isset($this->data[$key])) URLReplace('-1', 'No Key : ' . $key);

		$this->data[$key]->value = $v;
		$this->data[$key]->valueIsQuery = true;
		$this->data[$key]->needIs = true;
		return true;
	}

	/**
	 * 제외 키 등록
	 * @param array:string $ar
	 */
	public function AddExcept($ar){
		if(!is_array($ar)) $ar = func_get_args();
		foreach($ar as $v){
			if(isset($this->data[$v])) $this->data[$v]->dbExcept = true;
			else $this->dataExcept[]= $v;
		}
	}

	/**
	 * 데이타 값의 유효성 검사
	 * @param $key
	 * @return bool
	 */
	public function ValueCheck($key){
		return _ModelFunc::ValueCheck($this, $key);
	}


	/**
	 * 값의 성격이 올바른지 검사
	 * @param $key
	 * @return bool
	 */
	public function CheckType($key){
		return _ModelFunc::CheckType($key, $this->data[$key]);
	}

	/**
	 * 숫자형 값의 최대 최소 값 검사
	 * @param $key
	 * @return bool
	 */
	public function CheckValue($key){
		return _ModelFunc::CheckValue($key, $this->data[$key]);
	}

	/**
	 * 문자열 길이 최대 최소 검사
	 * @param $key
	 * @return bool
	 */
	public function CheckLength($key){
		return _ModelFunc::CheckLength($key, $this->data[$key]);
	}

	/**
	 * 필수 항목 검사
	 * @param $key
	 * @return bool
	 */
	public function CheckRequired($key){
		return _ModelFunc::CheckRequired($this, $key);
	}

	/**
	 * BH_ModelData에서 Enum 값을 출력
	 * @param string $name
	 * @param bool|string $value
	 *
	 * @return string
	 */
	public function HTMLPrintEnum($name, $value = false){
		if(!isset($this->data[$name])) return null;
		return $this->data[$name]->GetEnumValues($value);
	}

	/**
	 * BH_ModelData <label>출력
	 * @param string $name
	 * @param bool $htmlAttribute
	 * @param null|callable $callback
	 * @param string $firstIDName
	 *
	 * @return string
	 */
	public function HTMLPrintLabel($name, $htmlAttribute = false, $callback = null, $firstIDName = 'MD_'){
		return _ModelFunc::HTMLPrintLabel($this->data[$name], $name, $htmlAttribute, $callback, $firstIDName);
	}

	/**
	 * input, select textarea 출력
	 * @param string $name
	 * @param bool $htmlAttribute
	 * @param string $firstIDName
	 *
	 * @return string
	 */
	public function HTMLPrintInput($name, $htmlAttribute = false, $firstIDName = 'MD_'){
		return _ModelFunc::HTMLPrintInput($name, $this->data[$name], $htmlAttribute, $firstIDName);
	}

	/**
	 * 가지고 있는 BH_ModelData를 등록
	 * @return BH_InsertResult
	 * @param $test bool
	 */
	public function DBInsert($test = false){
		return _ModelFunc::DBInsert($this, $test);
	}

	/**
	 * 가지고 있는 BH_ModelData를 업데이트
	 * @return BH_Result
	 * @param $test bool
	 */
	public function DBUpdate($test = false){
		return _ModelFunc::DBUpdate($this, $test);
	}

	/**
	 * 키값에 해당하는 DB데이터를 한 행 가져온다.
	 * @return BH_Result
	 * @param  $keys string
	 */
	public function DBGet($keys){
		$keyData = is_array($keys) ? $keys : func_get_args();
		return _ModelFunc::DBGet($this, $keyData);
	}

	/**
	 * 불러올 키를 설정
	 * @param  array $keys
	 * @return $this
	 */
	public function DBGetKey($keys){
		$this->getKeys = is_array($keys) ? $keys : func_get_args();
		return $this;
	}

	/**
	 * 키값에 해당하는 DB데이터를 삭제
	 * @param array|string $keys
	 * @return BH_Result
	 */
	public function DBDelete($keys){
		$keyData = is_array($keys) ? $keys : func_get_args();
		return _ModelFunc::DBDelete($this, $keyData);
	}

	/**
	 * 키값에 해당하는 DB데이터를 파일과 함께 삭제
	 * @param array|string $keys
	 * @return BH_Result
	 */
	public function DBDeleteWithFile($keys){
		$keyData = is_array($keys) ? $keys : func_get_args();
		return _ModelFunc::DBDelete($this, $keyData, true);
	}

	public function Fetch($qry){
		$row = $qry->Get();
		if(!$row) return false;
		$this->SetArrayToData($row);
		return true;
	}
}

class _ModelFunc{
	public static function IsFileType($type){
		return in_array($type, array(HTMLType::FILE, HTMLType::FILE_WITH_NAME, HTMLType::FILE_IMAGE, HTMLType::FILE_IMAGE_ARRAY, HTMLType::FILE_JQUERY));
	}

	public static function SetPostValues(&$model, &$post, $withFile = false){
		$ret = new \BH_Result();
		$ret->result = true;
		foreach($model->data as $k => &$v){
			/** @var BH_ModelData $v */
			if($v->postExcept !== true && $v->dbExcept !== true && $v->autoDecrement !== true){
				if(isset($v->htmlType) && self::IsFileType($v->htmlType) && isset($_FILES[$k])){
					if($withFile) self::SetFileValue($model, $k);
				}
				else if(!isset($post[$k])){
					if($v->blankIsNull){
						$v->value = 'NULL';
						$v->valueIsQuery = true;
						$v->needIs = true;
					}
					else if($v->needIs && (!isset($v->value) || !strlen($v->value))){
						$ret->message = $v->modelErrorMsg = str_replace('{item}', $v->displayName, BH_Application::$lang['MODEL_NOT_DEFINED_ITEM']);
						$ret->result = false;
						return $ret;
					}
					else if($v->htmlType === HTMLType::CHECKBOX && $v->required) $v->needIs = true;
				}
				else{
					if($v->htmlType == HTMLType::FILE_IMAGE_ARRAY){
						$delFiles = Post('del_file_' . $k);
						if(!is_array($delFiles)) $delFiles = array();

						$values = array();
						if(!is_array($post[$k])){
							$ret->message = $v->modelErrorMsg = str_replace('{item}', $v->displayName, BH_Application::$lang['MODEL_NOT_MULTI_FILE_ITEM']);
							$ret->result = false;
							return $ret;
						}

						foreach($post[$k] as $path){
							if($path){
								$newpath = self::ReservedMoveFile($path, $model->uploadDir);
								if(is_string($newpath)){
									$values[]= $newpath;
									$v->__moveFile[]= array('source' => $path, 'dest' => $newpath);
									$v->needIs = true;
								}
								else if($newpath->result === -1){
									$ret->message = $v->modelErrorMsg = $v->displayName . ' - ' . $newpath->message;
									$ret->result = false;
									return $ret;
								}
							}
						}

						// 기존 파일
						if(strlen($v->value)){
							$p = explode(';', $v->value);
							$valuePath = array();
							foreach($p as $path){
								if(in_array($path, $delFiles)) $v->__deleteFile[] = $path;
								else $valuePath[]= $path;
							}
							$values = array_merge($values, $valuePath);
						}
						$v->value = implode(';', $values);
						$v->needIs = true;
					}

					else if(self::IsFileType($v->htmlType)){
						$fileUpIs = false;
						$m = explode('*', $post[$k]);
						$fPath = $m[0];

						$fName = '';
						if($v->htmlType === HTMLType::FILE_WITH_NAME || $v->htmlType === HTMLType::FILE || $v->htmlType === HTMLType::FILE_JQUERY){
							if(($v->htmlType === HTMLType::FILE_WITH_NAME || $v->htmlType === HTMLType::FILE_JQUERY) && isset($m[1]) && strlen($m[1])) $fName = '*' . $m[1];
						}

						if(strlen($fPath) && file_exists(\Paths::DirOfUpload() . $fPath)){
							$ext = explode('.', $fPath);
							$ext = strtolower(array_pop($ext));

							if(isset($v->addOption['possibleExt']) && is_array($v->addOption['possibleExt']) && sizeof($v->addOption['possibleExt'])){
								if(!in_array($ext, $v->addOption['possibleExt'])){
									$ret->message = $v->modelErrorMsg = str_replace('{item}', $v->displayName, BH_Application::$lang['MODEL_WRONG_FILE_TYPE']);
									$ret->result = false;
									return $ret;
								}
							}
							else if(!in_array($ext, BH_Application::$settingData['POSSIBLE_EXT'])){
								$ret->message = $v->modelErrorMsg = str_replace('{item}', $v->displayName, BH_Application::$lang['MODEL_WRONG_FILE_TYPE']);
								$ret->result = false;
								return $ret;
							}

							// 파일 용량검사
							if(isset($v->addOption['maxFileSize']) && $v->addOption['maxFileSize']){
								$s = preg_replace('/[^0-9\.]/', '', $v->addOption['maxFileSize']);
								$type = strtolower(substr($v->addOption['maxFileSize'], -2));

								if($type === 'mb') $s = $s * 1024 * 1024;
								else if($type === 'kb') $s = $s * 1024;

								if($s < filesize(\Paths::DirOfUpload() . $fPath)){
									$ret->message = $v->modelErrorMsg = str_replace('{item}', $v->displayName, BH_Application::$lang['MODEL_EXCEED_FILE_SIZE']);
									$ret->result = false;
									return $ret;
								}
							}

							$fileUpIs = true;

							// 파일명 변경
							if($v->htmlType === HTMLType::FILE_JQUERY){
								$tempPath = explode('/', $fPath);
								array_pop($tempPath);

								$newFileName = '';
								while($newFileName == '' || file_exists(\Paths::DirOfUpload() . implode('/', $tempPath) . '/' . $newFileName . '.' . $ext)) $newFileName = self::RandomFileName();
								$old = $fPath;
								$fPath = implode('/', $tempPath) . '/' . $newFileName . '.' . $ext;
								rename(\Paths::DirOfUpload() . $old, \Paths::DirOfUpload() . $fPath);
							}

							$newpath = self::ReservedMoveFile($fPath, $model->uploadDir);

							if(is_string($newpath)){
								$v->__moveFile[]= array('source' => $fPath, 'dest' => $newpath);
								// 기존 파일
								if(strlen($v->value)) $v->__deleteFile[]= $v->value;

								$v->value = $newpath.$fName;
								$v->needIs = true;
							}
							else{
								if($newpath->result === -1){
									$ret->message = $v->modelErrorMsg = $v->displayName . ' - ' . $newpath->message;
									$ret->result = false;
									return $ret;
								}
							}
						}

						if(!$fileUpIs && strlen($v->value) && Post('del_file_' . $k) == 'y'){
							$v->__deleteFile[]= $v->value;
							$v->value = '';
							$v->needIs = true;
						}
						if($v->required){
							$v->needIs = true;
						}
					}

					else if((isset($v->htmlType) || $v->required) && !self::IsFileType($v->htmlType)){
						if(is_array($post[$k])){
							if($v->htmlType === HTMLType::CHECKBOX){
								$v->value = implode(',', $post[$k]);
							}
							else{
								$ret->message = $v->modelErrorMsg = $v->displayName . BH_Application::$lang['MODEL_DO_NOT_ARRAY'];
								$ret->result = false;
							}
						}
						else if(!strlen($post[$k]) && $v->blankIsNull){
							$v->value = 'NULL';
							$v->valueIsQuery = true;
						}
						else{
							if($v->htmlType === HTMLType::NUMBER_FORMAT) $v->value = preg_replace('/[^0-9]/', '', $post[$k]);
							else $v->value = $post[$k];
						}
						$v->needIs = true;
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * @param $path
	 * @param $dir
	 * @return string|object
	 */
	private static function ReservedMoveFile($path, $dir){
		if(strpos($path, '..') !== false) return (object) array('result' => -1, 'message' => BH_Application::$lang['MODEL_WRONG_PATH']);

		$path = preg_replace('/[^0-9a-zA-Z\/\_\-\!\@\.]/', '', $path);
		if(file_exists(\Paths::DirOfUpload() . $path)){
			$upDir = $dir;
			$newpath = substr_replace($path, $upDir, 0, 6);
			if(!is_dir(\Paths::DirOfUpload() . $upDir)) mkdir(\Paths::DirOfUpload() . $upDir, 0777, true);

			return $newpath;
		}
		return (object) array('result' => -2, 'message' => BH_Application::$lang['MODEL_FILE_NOT_EXISTS']);
	}

	/**
	 * @param \BH_Model $model
	 */
	private static function FileProcess(&$model){
		foreach($model->data as $v){
			if(self::IsFileType($v->htmlType)){
				if(isset($v->__moveFile) && is_array($v->__moveFile)){
					foreach($v->__moveFile as $mv){
						@copy(\Paths::DirOfUpload() . $mv['source'], \Paths::DirOfUpload() . $mv['dest']);
						UnlinkImage(\Paths::DirOfUpload() . $mv['source']);
					}
				}
				if(isset($v->__deleteFile) && is_array($v->__deleteFile)){
					foreach($v->__deleteFile as $f){
						UnlinkImage(\Paths::DirOfUpload() . $f);
					}
				}
			}
		}
	}

	public static function GetErrorMessage(&$model, &$ret){
		foreach($model->data as $k=>$v){
			if($v->needIs !== true) continue;
			self::ValueCheck($model, $k);
			if($v->modelErrorMsg) $ret[] =$v->modelErrorMsg;
		}
	}

	public static function GetFilePath($data, $n, $n2){
		if(isset($data->value)){
			if(self::IsFileType($data->htmlType)){
				$p = explode(';', $data->value);
				if(isset($p[$n2])){
					$f = explode('*', $p[$n2]);
					if(isset($f[$n])) return $f[$n];
					else{
						$fn = explode('/', $f[0]);
						return end($fn);
					}
				}
				else return NULL;
			}
			else return $data->value;
		}

		else return NULL;
	}

	public static function GetFilePathByValue($value, $n, $n2){
		$p = explode(';', $value);
		if(isset($p[$n2])){
			$f = explode('*', $p[$n2]);
			if(isset($f[$n])) return $f[$n];
			else{
				$fn = explode('/', $f[0]);
				return end($fn);
			}
		}
		else return NULL;
	}

	/**
	 * @param BH_Model $model
	 * @param string $key
	 * @return bool
	 */
	public static function ValueCheck(&$model, $key){
		if($model->data[$key]->valueIsQuery || $model->data[$key]->dbExcept) return true;
		if(self::CheckRequired($model, $key) === false) return false;
		if(isset($model->data[$key]->value) && strlen($model->data[$key]->value)){
			if(self::CheckType($key, $model->data[$key]) === false) return false;
			if(self::CheckLength($key, $model->data[$key]) === false) return false;
			if(self::CheckValue($key, $model->data[$key]) === false) return false;
		}
		return true;
	}

	public static function CheckType($key, &$data){
		switch($data->type){
			case ModelType::INT:
				$val = preg_replace('/[^0-9\-]/','',$data->value);
				if($val != $data->value){
					$data->modelErrorMsg = str_replace('{item}', $data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), BH_Application::$lang['MODEL_ONLY_NUMBER']);
					return false;
				}
			break;
			case ModelType::FLOAT:
				$val = preg_replace('/[^0-9\.\-]/','',$data->value);
				if($val != $data->value){
					$data->modelErrorMsg = str_replace('{item}', $data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), BH_Application::$lang['MODEL_ONLY_NUMBER']);
					return false;
				}
			break;
			case ModelType::ENUM:
				$v = $data->value;
				if($data->htmlType == HTMLType::CHECKBOX){
					$temp = explode(',', $data->value);
					$v = trim($temp[0]);
				}
				if(!(isset($data->enumValues) && is_array($data->enumValues) && isset($data->enumValues[$v]))){
					$data->modelErrorMsg = str_replace('{item}', $data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), BH_Application::$lang['MODEL_NEED_VALUE']);
					return false;
				}
			break;
		}
		switch($data->htmlType){
			case HTMLType::EMAIL:
				if (!filter_var($data->value, FILTER_VALIDATE_EMAIL)) {
					$data->modelErrorMsg = str_replace('{item}', $data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), BH_Application::$lang['MODEL_VALUE_WRONG_TYPE']);
					return false;
				}
			break;
			case HTMLType::TEL:
				$val = preg_replace('/[^0-9\-\+\(\)\*\#]/','',$data->value);
				if($val != $data->value){
					$data->modelErrorMsg = str_replace('{item}', $data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), BH_Application::$lang['MODEL_VALUE_WRONG_TYPE']);
					return false;
				}
			break;
			case HTMLType::TEXT_ENG_ONLY:
				$val = preg_replace('/[^a-zA-Z]/','',$data->value);
				if($val != $data->value){
					$data->modelErrorMsg = str_replace('{item}', $data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), BH_Application::$lang['MODEL_ONLY_ENG']);
					return false;
				}
			break;
			case HTMLType::TEXT_ENG_NUM:
				if ( !ctype_alnum($data->value) ){
					$data->modelErrorMsg = str_replace('{item}', $data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), BH_Application::$lang['MODEL_ONLY_ENG_NUM']);
					return false;
				}
			break;
			case HTMLType::TEXT_ENG_SPECIAL:
				$val = preg_replace('/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>\'\"\?\-=\+_\:\;\[\]\{\}\/]/','',$data->value);
				if($val != $data->value){
					$data->modelErrorMsg = str_replace('{item}', $data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), BH_Application::$lang['MODEL_ONLY_ENG_NUM_SPECIAL']);
					return false;
				}
		}
		return true;
	}

	public static function CheckValue($key, &$data){
		if($data->type == ModelType::INT || $data->type == ModelType::FLOAT){
			if($data->minValue !== false && $data->minValue > $data->value){
				$data->modelErrorMsg = str_replace(array('{item}','{n}'), array($data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), $data->minValue),BH_Application::$lang['MODEL_OR_MORE']);
				return false;
			}
			if($data->maxValue !== false && $data->maxValue < $data->value){
				$data->modelErrorMsg = str_replace(array('{item}','{n}'), array($data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), $data->maxValue),BH_Application::$lang['MODEL_OR_LESS']);
				return false;
			}
		}
		return true;
	}

	public static function CheckLength($key, &$data){
		if($data->type == ModelType::STRING || $data->type == ModelType::TEXT){
			if($data->minLength !== false && $data->minLength > mb_strlen($data->value, 'UTF-8')){
				$data->modelErrorMsg = str_replace(array('{item}','{n}'), array($data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), $data->minLength),BH_Application::$lang['MODEL_OR_MORE_LENGTH']);
				return false;
			}
			if($data->maxLength !== false && $data->maxLength < mb_strlen($data->value, 'UTF-8')){
				$data->modelErrorMsg = str_replace(array('{item}','{n}'), array($data->displayName.(_DEVELOPERIS === true ? '('.$key.')' : ''), $data->maxLength),BH_Application::$lang['MODEL_OR_LESS_LENGTH']);
				return false;
			}
		}
		return true;
	}

	public static function CheckRequired(&$model, $key){
		if($model->data[$key]->required == false) return true;
		if(is_null($model->GetValue($key)) || !strlen($model->GetValue($key))){
			if(!$model->data[$key]->dbExcept && !$model->data[$key]->postExcept && $model->data[$key]->autoDecrement !== true){
				$model->data[$key]->modelErrorMsg = str_replace('{item}', $model->data[$key]->displayName, BH_Application::$lang['MODEL_REQUIRED']);
				return false;
			}
		}
		return true;
	}

	public static function HTMLPrintLabel(&$data, $name, $htmlAttribute, $callback, $firstIDName){
		$Attribute = '';
		if(is_array($htmlAttribute)){
			foreach($htmlAttribute as $k => $row){
				$Attribute .= ' '.$k.'="'.$row.'"';
			}
		}
		else if(is_string($htmlAttribute)) $Attribute = $htmlAttribute;

		if(is_callable($callback)){
			return '<label for="'.$firstIDName.$name.'" '.$Attribute.'>'.$callback($data->displayName).'</label>';
		}
		return '<label for="'.$firstIDName.$name.'" '.$Attribute.'>'.$data->displayName.'</label>';
	}

	public static function HTMLPrintInput($Name, &$data, $htmlAttribute = false, $firstIDName){
		$htmlType = strtolower($data->htmlType);
		$Attribute = '';
		$val = isset($data->value) ? $data->value : $data->defaultValue;

		if($htmlAttribute === false) $htmlAttribute = array();

		if(!isset($htmlAttribute['class'])) $htmlAttribute['class'] = '';

		if($data->minLength !== false) $Attribute .= ' data-minlength="'.$data->minLength.'"';

		if($data->maxLength !== false){
			$Attribute .= ' data-maxlength="'.$data->maxLength.'"';
			$Attribute .= ' maxlength="'.$data->maxLength.'"';
		}
		if($data->minValue !== false) $Attribute .= ' data-minvalue="'.$data->minValue.'"';

		if($data->maxValue !== false) $Attribute .= ' data-maxvalue="'.$data->maxValue.'"';

		if($data->required) $Attribute .= ' required="required"';

		// ModelType
		if($data->type == ModelType::INT && $data->htmlType != 'numberformat') $htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').'numberonly';

		// HTMLType
		if($data->htmlType == HTMLType::EMAIL) $htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').'email';
		else if($data->htmlType == HTMLType::TEL) $htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').'tel';
		else if($data->htmlType == HTMLType::DATE){
			$htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').'date';
			$htmlAttribute['maxlength'] = '10';
			$htmlAttribute['minlength'] = '10';
		}
		else if($data->htmlType == HTMLType::DATE_PICKER){
			$htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').'datePicker';
			$htmlAttribute['maxlength'] = '10';
			$htmlAttribute['minlength'] = '10';
		}
		else if($data->htmlType == HTMLType::NUMBER_FORMAT) $htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').HTMLType::NUMBER_FORMAT;
		else if($data->htmlType == HTMLType::TEXT_ENG_NUM) $htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').HTMLType::TEXT_ENG_NUM;
		else if($data->htmlType == HTMLType::TEXT_ENG_ONLY) $htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').HTMLType::TEXT_ENG_ONLY;
		else if($data->htmlType == HTMLType::TEXT_ENG_SPECIAL) $htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').HTMLType::TEXT_ENG_SPECIAL;
		else if(in_array($data->htmlType, array(HTMLType::FILE_IMAGE, HTMLType::FILE_IMAGE_ARRAY))) $htmlAttribute['class'] .= ($htmlAttribute['class'] ? ' ' : '').'fileUploadInput';

		foreach($htmlAttribute as $k => $row) $Attribute .= ' '.$k.'="'.$row.'"';

		$fileRequired = $data->required && !$data->value ? ' required' : '';
		switch($htmlType){
			case HTMLType::TEXT:
			case HTMLType::PASSWORD:
			case HTMLType::EMAIL:
			case HTMLType::TEL:
				return '<input type="'.$htmlType.'" name="'.$Name.'" id="'.$firstIDName.$Name.'" '.(isset($val) && $htmlType != HTMLType::PASSWORD ? 'value="'.GetDBText($val).'"' : '').' data-displayname="' . $data->displayName . '" '.$Attribute.'>';
			break;
			case HTMLType::NUMBER_FORMAT:
			case HTMLType::DATE_PICKER:
			case HTMLType::TEXT_ENG_NUM:
			case HTMLType::TEXT_ENG_ONLY:
			case HTMLType::TEXT_ENG_SPECIAL:
				return '<input type="text" name="'.$Name.'" id="'.$firstIDName.$Name.'" '.(isset($val) ? 'value="'.GetDBText($val).'"' : '').' data-displayname="' . $data->displayName . '" '.$Attribute.'>';
			break;
			case HTMLType::NUMBER:
				return '<input type="number" name="'.$Name.'" id="'.$firstIDName.$Name.'" '.(isset($val) ? 'value="'.GetDBText($val).'"' : '').' data-displayname="' . $data->displayName . '" '.$Attribute.'>';
			break;
			case HTMLType::DATE:
				return '<span class="dateInput"><input type="text" name="'.$Name.'" id="'.$firstIDName.$Name.'" '.(isset($val) ? 'value="'.GetDBText($val).'"' : '').' data-displayname="' . $data->displayName . '" '.$Attribute.'></span>';
			break;
			case HTMLType::FILE_JQUERY:
				if(!isset(BH_Application::$settingData['_JQUERY_FILE_UPLOAD'])){
					BH_Application::$settingData['_JQUERY_FILE_UPLOAD'] = true;
					BH_Application::JSAdd('/vendor/blueimp/jquery-file-upload/js/vendor/jquery.ui.widget.js', 150);
					BH_Application::JSAdd('/vendor/blueimp/jquery-file-upload/js/jquery.fileupload.js', 150);
					BH_Application::CSSAdd('/vendor/blueimp/jquery-file-upload/css/jquery.fileupload.css', 150);
					BH_Application::CSSAdd('/vendor/blueimp/jquery-file-upload/css/jquery.fileupload-ui.css', 150);
				}


				if(isset($data->addOption['maxFileSize'])) $Attribute .= ' data-max-size="' . $data->addOption['maxFileSize'] . '"';
				if(isset($data->addOption['possibleExt']) && is_array($data->addOption['possibleExt'])) $Attribute .= ' data-ext="' .  implode(',', $data->addOption['possibleExt']) . '"';

				$f = explode('*', $data->value);

				$h = '<div class="jqFileUploadArea"' . $Attribute . '>
				<input type="hidden" name="' . $Name . '" value="" id="MD_'.$firstIDName.$Name.'" class="fileUploadPath" data-displayname="' . $data->displayName . '"' . $fileRequired . '>
				<div style="padding-bottom:10px;">';
				if(strlen($data->value)) $h .= '<p><b class="upload_file_name">'.(isset($f[1]) ? GetDBText($f[1]) : '').'</b> <label class="checkbox"><input type="checkbox" name="del_file_'.$Name.'" value="y"><i></i><span> ' .BH_Application::$lang['DEL_FILE'] . '</span></label></p>';
				else $h .= '<p><b class="upload_file_name"></b></p>';
				$h .= '</div>
						<div style="display:block; width: 0; height: 0; overflow: hidden; opacity: 0; filter:alpha(0);">
							<input type="file" name="temp_upload_file" class="fileUploadInp">
						</div>
						<button type="button" class="mBtn fileUploadBtn">' . (isset($htmlAttribute['button']) ? $htmlAttribute['button'] : BH_Application::$lang['REG_FILE']) . '</button>
						<div class="progress progress-animated">
							<div class="bar"></div>
						</div>
					</div>';
				return $h;
			break;
			case HTMLType::FILE_WITH_NAME:
				$h = '<div class="fileUploadArea2"><input type="hidden" name="' . $Name . '" class="fileUploadInput" value="" data-displayname="' . $data->displayName . '"' . $fileRequired . '> <button type="button" class="fileUploadBtn sBtn"><i></i>' . (isset($htmlAttribute['button']) ? $htmlAttribute['button'] : BH_Application::$lang['ATTACH_FILE']) . '</button>';
				if(strlen($data->value)){
					$f = explode('*', $data->value);
					$h .= ' <p><span class="fileName">' . (isset($f[1]) ? GetDBText($f[1]) : '') . '</span> <label class="checkbox"><input type="checkbox" name="del_file_' . $Name . '" value="y"><i></i><span> ' . BH_Application::$lang['DEL_FILE'] . '</span></label></p>';
				}
				else{
					$h .= '<p></p>';
				}
				return $h . '</div><script>JCM.fileForm();</script>';
			break;
			case HTMLType::FILE:
				$h = '';
				if(strlen($data->value)){
					$h = ' <span class="uploadedFile"><label class="checkbox"><input type="checkbox" name="del_file_' . $Name . '" value="y"><i></i><span> ' . BH_Application::$lang['DEL_FILE'] . '</span></label></span>';
				}
				return $h . ' <input type="file" name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->displayName . '" '.$fileRequired.'>';
			break;
			case HTMLType::FILE_IMAGE:
				$h = '<div class="fileUploadArea"><input type="hidden" name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->displayName . '" '.$fileRequired.'>';
				$h .= '<span class="fileUploadImage">';
				if(strlen($data->value)){
					$h .= '<i style="background-image:url(' . Paths::UrlOfUpload() . $data->value . ')"></i>';
				}
				$h .= '</span>';
				if(strlen($data->value)) $h .= ' <label class="uploadedImgFile checkbox"><input type="checkbox" name="del_file_' . $Name . '" value="y"><i></i><span>' . BH_Application::$lang['DEL'] . '</span></label>';
				return $h . '<button type="button" class="fileUploadBtn sBtn"><span>' . BH_Application::$lang['REG_IMAGE'] . '</span></button></div><script>JCM.imageFileForm();</script>';
			break;
			case HTMLType::FILE_IMAGE_ARRAY:
				$h = '<div class="multiFileUploadArea">';
				if(strlen($data->value)){
					$p = explode(';', $data->value);
					foreach($p as $path){
						$h .= ' <span class="fileUploadImage"><i style="background-image:url(' . Paths::UrlOfUpload() . $path . ')"></i></span> <label class="uploadedImgFile checkbox"><input type="checkbox" name="del_file_' . $Name . '[]" value="' . $path . '"><i></i><span>' . BH_Application::$lang['DEL'] . '</span></label>';
					}
				}
				$h .= '<div class="fileUploadArea"><span class="fileUploadImage"></span><input type="hidden" name="'.$Name.'[]" data-displayname="' . $data->displayName . '" '.$Attribute.'><button type="button" class="fileUploadBtn sBtn"><span>' . BH_Application::$lang['REG_IMAGE'] . '</span></button><button type="button" class="fileUploadAreaAddBtn sBtn">' . BH_Application::$lang['ADD'] . '</button><button type="button" class="fileUploadAreaRmBtn sBtn">' . BH_Application::$lang['DEL'] . '</button></div>';
				return $h . '</div><script>JCM.imageFileForm();</script>';
			break;
			case HTMLType::TEXTAREA:
				return '<textarea name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->displayName . '" '.$Attribute.'>'.(isset($val) ? GetDBText($val) : '').'</textarea>';
			break;
			case HTMLType::RADIO:
			case HTMLType::CHECKBOX:
				$nm = $htmlType === HTMLType::CHECKBOX ? $Name . '[]' : $Name;
				$ret = '';
				if($htmlType === HTMLType::CHECKBOX && $data->required) $htmlAttribute['class'] .= ' checkboxRequired';
				$tempVal = $htmlType === HTMLType::CHECKBOX ? explode(',', $val) : array($val);
				if(isset($data->enumValues) && is_array($data->enumValues)){
					$i = 1;
					foreach($data->enumValues as $k=>$v){
						$checked = isset($val) && in_array($k, $tempVal) ? ' checked="checked"' : '';

						$ret .= '<label for="'.$firstIDName.$Name.'_'.$i.'" class="'.$htmlType.(isset($htmlAttribute['class']) ? ' ' . $htmlAttribute['class'] : '').'">
							<input type="'.$htmlType.'" name="'.$nm.'" id="'.$firstIDName.$Name.'_'.$i.'" value="'.$k.'" data-displayname="' . $data->displayName . '" '.($htmlType !== HTMLType::CHECKBOX ? $Attribute : '').$checked.'><i></i><span>'.$v.'</span>
							</label>';
						$i++;
					}
				}
				return $ret;
			break;
			case HTMLType::SELECT:
				$ret = '<select name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->displayName . '" '.$Attribute.'>';

				if(isset($data->enumValues) && is_array($data->enumValues)){
					foreach($data->enumValues as $k=>$v){
						$selected = isset($val) && $k == $val ? ' selected="selected"' : '';

						$ret .= '<option value="'.$k.'" '.$selected.'>'.GetDBText($v).'</option>';
					}
				}
				return $ret.'</select>';
			break;
		};
		return '';
	}

	private static function HasNeed(&$model){
		foreach($model->data as $v) if($v->needIs) return true;
		return false;
	}

	public static function DBInsert(&$model, $test = false){
		$dbInsert = new \BH_DB_Insert($model->table);
		$dbInsert->SetConnName($model->GetConnName())->SetShowError($model->showError);
		$result = new \BH_InsertResult();

		foreach($model->data as $k=>$v){
			if(!isset($v->value) && $v->needIs){
				$result->result = false;
				$result->message = 'ERROR#101';
				return $result;
			}
			else if(!isset($v->value) && !$v->needIs && $v->type === ModelType::TEXT && !$v->blankIsNull){
				$v->value = '';
				$v->needIs = true;
			}

			// 예외 패스, 셋이 없거나 셋에 있는것
			if(!$v->dbExcept && (!self::HasNeed($model) || $v->needIs)){
				if(isset($v->value)){
					if(in_array($k, $model->key) && $v->autoDecrement === true) continue;

					if(!$v->valueIsQuery && $v->htmlType == HTMLType::TEL) $v->value = preg_replace('/[^0-9\-\+\(\)\*\#]/','',$v->value);

					if($v->valueIsQuery) $dbInsert->data[$k] = $v->value;
					else if($v->type == ModelType::INT){
						if(!strlen($v->value) && !isset($v->defaultValue)) continue;
						if(!strlen($v->value) && isset($v->defaultValue)) $dbInsert->data[$k] = $v->defaultValue;
						else{
							$res = self::CheckInt($k, $v->value);
							if($res === true) $dbInsert->data[$k] = $v->value;
							else{
								$result->result = false;
								$result->message = $res;
							}
						}
					}
					else if($v->type == ModelType::FLOAT){
						if(!strlen($v->value) && !isset($v->defaultValue)) continue;
						if(!strlen($v->value) && isset($v->defaultValue)) $dbInsert->data[$k] = $v->defaultValue;
						else{
							$res = self::CheckFloat($k, $v->value);
							if($res === true) $dbInsert->data[$k] = $v->value;
							else{
								$result->result = false;
								$result->message = $res;
							}
						}
					}
					else if($v->htmlType == HTMLType::PASSWORD) $dbInsert->SetDataStr($k, _password_hash($v->value));
					else $dbInsert->SetDataStr($k, $v->value);
				}
			}
		}

		foreach($model->key as $k){
			if($model->data[$k]->autoDecrement === true) $dbInsert->decrement = $k;
		}
		if(_DEVELOPERIS === true) $dbInsert->test = $test;
		$result = $dbInsert->Run();
		if($result->result){
			self::FileProcess($model);
		}
		return $result;
	}

	public static function DBUpdate(&$model, $test = false){
		$result = new \BH_Result();

		$dbUpdate = new \BH_DB_Update($model->table);
		$dbUpdate->SetConnName($model->GetConnName())->SetShowError($model->showError);
		foreach($model->data as $k=>$v){
			if(!isset($v->value) && $v->needIs){
				$result->result = false;
				$result->message = 'ERROR';
				return $result;
			}

			// 예외와 키값 패스, 셋이 없거나 셋에 있는것
			if(!$v->dbExcept && (!self::HasNeed($model) || $v->needIs) && !in_array($k, $model->key)){
				if(isset($v->value)){
					if(in_array($k, $model->key) && $v->autoDecrement === true) continue;

					if(!$v->valueIsQuery && $v->htmlType == HTMLType::TEL) $v->value = preg_replace('/[^0-9\-\+\(\)\*\#]/','',$v->value);

					if($v->valueIsQuery) $dbUpdate->SetData($k, $v->value);
					else if($v->type == ModelType::INT){
						if(!strlen($v->value)) continue;
						$res = self::CheckInt($k, $v->value);
						if($res === true) $dbUpdate->SetDataNum($k, $v->value);
						else{
							$result->result = false;
							$result->message = $res;
						}
					}
					else if($v->type == ModelType::FLOAT){
						if(!strlen($v->value)) continue;
						$res = self::CheckFloat($k, $v->value);
						if($res === true) $dbUpdate->SetDataNum($k, $v->value);
						else{
							$result->result = false;
							$result->message = $res;
						}
					}
					else if($v->htmlType == HTMLType::PASSWORD) $dbUpdate->SetDataStr($k, _password_hash($v->value));
					else $dbUpdate->SetDataStr($k, $v->value);
				}
			}
		}
		foreach($model->key as $k){
			if(isset($model->data[$k]->value) && ($model->data[$k]->type === ModelType::STRING || strlen($model->data[$k]->value))) $dbUpdate->AddWhere($k.' = %s', $model->data[$k]->value);
			else{
				$result->message = 'Empty Key';
				$result->result = false;
				return $result;
			}
		}

		if(_DEVELOPERIS === true) $dbUpdate->SetTest($test);
		$result = $dbUpdate->Run();
		if($result->result){
			self::FileProcess($model);
		}

		return $result;
	}

	public static function DBGet(&$model, $keys){
		$res = new \BH_Result();

		if(!isset($model->key) || !is_array($model->key)){
			if(_DEVELOPERIS === true){
				echo BH_Application::$lang['MODEL_KEY_NOT_EXISTS'];
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keys) != sizeof($model->key)){
			if(_DEVELOPERIS === true){
				echo BH_Application::$lang['MODEL_KEY_LENGTH_NOT_MATCH'];
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}
		$dbGet = new \BH_DB_Get($model->table);
		$dbGet->SetConnName($model->GetConnName())->SetShowError($model->showError);
		if(sizeof($model->getKeys)) $dbGet->SetKey($model->getKeys);
		foreach($model->key as $k => $v) $dbGet->AddWhere($v.' = %s', trim($keys[$k]));
		$data = $dbGet->Get();

		if($data !== false){
			foreach($data as $k=>$v) if(isset($model->data[$k])) $model->data[$k]->value = $v;
			$res->result = true;
		}
		else $res->result = false;

		return $res;
	}

	public static function DBDelete(&$model, $keyData, $withFile = false){
		$res = new \BH_Result();

		if(!is_array($keyData)) $keyData = array($keyData);

		if(!isset($model->key) || !is_array($model->key)){
			if(_DEVELOPERIS === true){
				echo BH_Application::$lang['MODEL_KEY_NOT_EXISTS'];
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keyData) != sizeof($model->key)){
			if(_DEVELOPERIS === true){
				echo BH_Application::$lang['MODEL_KEY_LENGTH_NOT_MATCH'];
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}

		if(!sizeof($model->key)){
			$res->result = false;
			$res->message = 'ERROR#03';
			return $res;
		}

		if($withFile){
			$res = self::DBGet($model, $keyData);
			foreach($model->data as $data){
				if(self::IsFileType($data->htmlType) && strlen($data->value)){
					$p = explode(';', $data->value);
					foreach($p as $path){
						$pn = explode('*', $path);
						UnlinkImage(\Paths::DirOfUpload() . $pn[0]);
					}
				}
			}
		}

		$qry = DB::DeleteQryObj($model->table)->SetConnName($model->GetConnName())->SetShowError($model->showError);
		foreach($model->key as $k => $v){
			$qry->AddWhere($v.' = %s', trim($keyData[$k]));
		}



		$res->result = $qry->Run();
		return $res;
	}

	public static function CheckInt($k, $v){
		if(!strlen($v)){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']' . BH_Application::$lang['TXT_EMPTY_NUMBER']);
				else URLReplace('-1', '['.$k.']' . BH_Application::$lang['TXT_EMPTY_NUMBER']);
			}else return 'ERROR#102';
		}
		$val = ToInt($v);
		if($val != $v){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']' . BH_Application::$lang['TXT_ONLY_NUMBER_NOT_CHARACTER']);
				else URLReplace('-1', '['.$k.']' . BH_Application::$lang['TXT_ONLY_NUMBER_NOT_CHARACTER']);
			}else return 'ERROR#103';
		}
		return true;
	}

	public static function CheckFloat($k, $v){
		if(!strlen($v)){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']' . BH_Application::$lang['TXT_EMPTY_NUMBER']);
				else URLReplace('-1', '['.$k.']' . BH_Application::$lang['TXT_EMPTY_NUMBER']);
			}else return 'ERROR#112';
		}
		$val = ToFloat($v);
		if($val != $v){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']' . BH_Application::$lang['TXT_ONLY_FLOAT_NOT_CHARACTER']);
				else URLReplace('-1', '['.$k.']' . BH_Application::$lang['TXT_ONLY_FLOAT_NOT_CHARACTER']);
			}else return 'ERROR#113';
		}
		return true;
	}

	public static function SetFileValue(&$model, $key){
		$ext = in_array($model->data[$key]->htmlType, array(HTMLType::FILE_IMAGE, HTMLType::FILE_IMAGE_ARRAY)) ? BH_Application::$settingData['IMAGE_EXT'] : BH_Application::$settingData['POSSIBLE_EXT'];
		$value = array();
		if(isset($_FILES[$key])){
			$res = self::FileUploadArray($_FILES[$key], $ext, '/temp/');
			foreach($res as $k => $v){
				if(is_array($v)){
					$value[] = $v;
				}
				else if(is_string($v)){
					$model->data[$key]->modelErrorMsg = '[' . $model->data[$key]->displayName . ']' . $v;
				}
			}
		}

		// 단일 파일, 업로드 시 기존 파일 삭제
		if(in_array($model->data[$key]->htmlType, array(HTMLType::FILE_IMAGE, HTMLType::FILE_WITH_NAME, HTMLType::FILE, HTMLType::FILE_JQUERY))){
			if(isset($model->data[$key]->value) && strlen($model->data[$key]->value) && Post('del_file_' . $key) == 'y'){
				$temp = explode('*', $model->data[$key]->value);
				$model->data[$key]->__deleteFile[]= $temp[0];
				$model->data[$key]->value = '';
				$model->data[$key]->needIs = true;
			}

			if(sizeof($value)){
				$newpath = self::ReservedMoveFile($value[0]['file'], $model->uploadDir);

				if(is_string($newpath)){
					$model->data[$key]->__moveFile[]= array('source' => $value[0]['file'], 'dest' => $newpath);
					// 기존 파일
					if(isset($model->data[$key]->value) && strlen($model->data[$key]->value)){
						$temp = explode('*', $model->data[$key]->value);
						$model->data[$key]->__deleteFile[]= $temp[0];
					}

					$model->SetValue($key, $newpath . (($model->data[$key]->htmlType === HTMLType::FILE_WITH_NAME || $model->data[$key]->htmlType === HTMLType::FILE_JQUERY) ? '*' . $value[0]['original'] : ''));
				}
				else{
					if($newpath->result === -1){
						$model->data[$key]->modelErrorMsg = $model->data[$key]->displayName . ' - ' . $newpath->message;
					}
				}
			}
		}

		// 다중 파일, 선택 파일 삭제
		else if($model->data[$key]->htmlType == HTMLType::FILE_IMAGE_ARRAY){
			$deleteFiles = Post('del_file_' . $key);

			$valuePath = array();
			if(strlen($model->data[$key]->value)){
				$p = explode(';', $model->data[$key]->value);
				foreach($p as $k => $v){
					$f = explode('*', $v);
					if(is_array($deleteFiles) && in_array($f[0], $deleteFiles)) $model->data[$key]->__deleteFile[]=  $f[0];
					else $valuePath[] = $f[0];
				}

				foreach($value as $path){
					$newpath = self::ReservedMoveFile($path['file'], $model->uploadDir);
					if(is_string($newpath)){
						$model->data[$key]->__moveFile[]= array('source' => $path['file'], 'dest' => $newpath);
						$valuePath[] = $newpath;
					}
					else{
						if($newpath->result === -1){
							$model->data[$key]->modelErrorMsg = $model->data[$key]->displayName . ' - ' . $newpath->message;
						}
					}
				}
				$model->SetValue($key, implode(';', $valuePath));
			}
		}

	}

	public static function RandomFileName(){
		$t = microtime();
		$t = explode(' ',$t);
		$t2 = (double)($t[0]*1000000);
		$t3 = toBase(rand(0,3843), 36).toBase($t[1],36).toBase($t2,36);
		return $t3;
	}

	/**
	 * @param array $files : Post $_FILE Array Data
	 * @param array $possible_ext
	 * @param string $path
	 *
	 * @return array
	 */
	public static function FileUploadArray($files, $possible_ext = null, $path = '/data/'){
		$filedata = array();
		$res = array();
		if(isset($files['name'])){
			if(is_array($files['name'])){
				foreach($files as $k=>$v){
					foreach($v as $k2=>$v2){
						$filedata[$k2][$k] = $v2;
					}
				}
				//print_r($filedata);exit;
				foreach($filedata as $v){
					$res[]= self::FileUpload($v, $possible_ext, $path);
				}
			}else{
				$res[]= self::FileUpload($files, $possible_ext, $path);
			}
		}
		return $res;
	}

	/**
	 * @param array $files : Post $_FILE Data
	 * @param array $possible_ext
	 * @param string $path
	 *
	 * @return bool|string
	 */
	public static function FileUpload($files, $possible_ext = null, $path = '/data/'){
		if($files['name']){
			$ext = explode('.', $files['name']);
			$ext = strtolower($ext[sizeof($ext)-1]);

			if(in_array($ext, BH_Application::$settingData['noext'])) return BH_Application::$lang['MSG_IMPOSSIBLE_FILE'];
			else if(!in_array($ext, BH_Application::$settingData['POSSIBLE_EXT'])) return BH_Application::$lang['MSG_IMPOSSIBLE_FILE'];
			else if($possible_ext && !in_array($ext, $possible_ext)) return BH_Application::$lang['MSG_IMPOSSIBLE_FILE'];

			if($files['error'] ===  UPLOAD_ERR_INI_SIZE) return BH_Application::$lang['MSG_FILE_TOO_BIG'];
			if($files['error'] !==  UPLOAD_ERR_OK) return BH_Application::$lang['MSG_UPLOAD_ERROR'];

			if(!is_dir(\Paths::DirOfUpload().$path)) @mkdir(\Paths::DirOfUpload().$path, 0777, true);

			$newFileName = '';
			while($newFileName == '' || file_exists(\Paths::DirOfUpload().$path.$newFileName.'.'.$ext)) $newFileName = self::RandomFileName();


			copy($files['tmp_name'], \Paths::DirOfUpload().$path.$newFileName.'.'.$ext);
			$res['original'] = $files['name'];
			$res['path'] = $path;
			$res['name'] = $newFileName;
			$res['ext'] = $ext;
			$res['file'] = $path.$newFileName.'.'.$ext;
			return $res;
		}
		else return false;
	}

	public static function Thumbnail($source, $thumb, $width, $height = 0){
		// 썸네일의 넓이가 넘어오지 않으면 에러
		if (!$width)
			return -1;
		if (!$thumb)
			$thumb = $source;
		$size = getimagesize($source);
		if ($size[2] == 1)
			$source = imagecreatefromgif($source);
		else if ($size[2] == 2){
			// TODO : 이미지 크기가 너무 클 경우 오류 또는 원본 반환 ($size[0] x $size[1])

			$exif = @exif_read_data($source);
			$source = imagecreatefromjpeg($source);
			if(isset($exif['Orientation']) && !empty($exif['Orientation'])){
				switch($exif['Orientation']){
					case 8:
						$source = imagerotate($source, 90, 0);
						$temp = $size[0];
						$size[0] = $size[1];
						$size[1] = $temp;
					break;
					case 3:
						$source = imagerotate($source, 180, 0);
					break;
					case 6:
						$source = imagerotate($source, -90, 0);
						$temp = $size[0];
						$size[0] = $size[1];
						$size[1] = $temp;
					break;
				}
			}
		}
		else if ($size[2] == 3)
			$source = imagecreatefrompng($source);
		else
			return -2;
		// 썸네일 이미지 넓이 보다 원본이미지의 넓이가 작다면 그냥 원본이미지가 썸네일이 됨
		if ($width > $size[0]){
			$target = imagecreatetruecolor($size[0], $size[1]);
			if ($size[2] == 3) {
				imagealphablending($target , 0);
				imagesavealpha($target , 1);
			}
			imagecopyresampled($target, $source, 0, 0, 0, 0, $size[0], $size[1], $size[0], $size[1]);
		}
		else{
			// 썸네일 높이가 넘어왔다면 비율에 의해 이미지를 생성하지 않음
			if ($height){
				// 원본이미지를 썸네일로 복사
				// 1000x1500 -> 500x500 으로 복사되는 형식이므로 이미지가 일그러진다.
				$comp_height = $height;
			}
			else{
				// 원래 이미지와 썸네일 이미지와의 비율
				$rate = round($width / $size[0], 2); // 소수점 2자리 , 소수점 3자리에서 반올림됨
				// 비율에 의해 계산된 높이
				$comp_height = floor($size[1] * $rate); // 소수점 이하 버림
			}
			$target = imagecreatetruecolor($width, $comp_height);
			if ($size[2] == 3) {
				imagealphablending($target , 0);
				imagesavealpha($target , 1);
			}
			imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $comp_height, $size[0], $size[1]);
		}
		if ($size[2] == 3) {
			imagepng($target, $thumb, 9);
		} else {
			imagejpeg($target, $thumb, 90);
		}
		imagedestroy($target);
		@chmod($thumb, 0666); // 추후 삭제를 위하여 파일모드 변경
		return 1;
	}
}

class CfgEmptyClass
{
	public function __call($name, $arguments){
		if(class_exists('_ConfigMap')) _ConfigMap::{$name}();
		return _ConfigModel::GetInstance();
	}
}

BH_Application::$cfg = new CfgEmptyClass();

class _CfgData
{
	public $value = '';
	public $defaultValue = '';
	public $key = '';
	public $title = '';
	public $type = \HTMLType::TEXT;
	public $enumValues = array();

	/**
	 * @param string $k
	 * @return _CfgData
	 */
	public static function GetInstance($k = ''){
		$static = new static();
		$static->key = $k;
		return $static;
	}

	public function __toString(){
		return $this->value;
	}

	public function Val(){
		return ((is_string($this->value) && strlen($this->value)) || !is_string($this->value)) ? $this->value : $this->defaultValue;
	}

	public function IntVal(){
		return ((is_string($this->value) && strlen($this->value)) || !is_string($this->value)) ? ToInt($this->value) : $this->defaultValue;
	}

	/**
	 * @param string $k
	 * @return _CfgData
	 */
	public function SetKey($k){
		$this->key = $k;
		return $this;
	}

	/**
	 * @param string $t
	 * @return _CfgData
	 */
	public function SetTitle($t){
		$this->title = $t;
		return $this;
	}

	/**
	 * @param string $v
	 * @return _CfgData
	 */
	public function SetValue($v){
		$this->value = $v;
		return $this;
	}

	/**
	 * @param array $arr
	 * @return _CfgData
	 */
	public function SetEnumValues($arr){
		$this->enumValues = $arr;
		return $this;
	}

	/**
	 * @param string $v
	 * @return _CfgData
	 */
	public function SetDefaultValue($v){
		$this->defaultValue = $v;
		return $this;
	}

	/**
	 * @param string $t
	 * @return _CfgData
	 */
	public function SetType($t){
		$this->type = $t;
		return $this;
	}

	public function PrintInput($class = '', $attr = ''){
		$h = '';
		switch($this->type){
			case \HTMLType::TEXT:
				$h = '<input type="text" id="CFG_' . $this->key . '" name="' . $this->key .'" value="' .  GetDBText($this->Val()) . '" class="'. $class .'" ' . $attr .'>';
			break;
			case \HTMLType::TEL:
				$h = '<input type="tel" id="CFG_' . $this->key . '" name="' . $this->key .'" value="' .  GetDBText($this->Val()) . '" class="'. $class .'" ' . $attr .'>';
			break;
			case \HTMLType::EMAIL:
				$h = '<input type="email" id="CFG_' . $this->key . '" name="' . $this->key .'" value="' .  GetDBText($this->Val()) . '" class="'. $class .'" ' . $attr .'>';
			break;
			case \HTMLType::NUMBER:
				$h = '<input type="text" id="CFG_' . $this->key . '" name="' . $this->key .'" value="' .  GetDBText($this->Val()) . '" class="number '. $class .'" ' . $attr .'>';
			break;
			case \HTMLType::NUMBER_FORMAT:
				$h = '<input type="text" id="CFG_' . $this->key . '" name="' . $this->key .'" value="' .  GetDBText($this->Val()) . '" class="numberformat '. $class .'" ' . $attr .'>';
			break;
			case \HTMLType::TEXT_ENG_SPECIAL:
				$class .= ' ' . \HTMLType::TEXT_ENG_SPECIAL;
				$h = '<input type="text" id="CFG_' . $this->key . '" name="' . $this->key .'" value="' .  GetDBText($this->Val()) . '" class="'. $class .'" ' . $attr .'>';
			break;
			case \HTMLType::TEXTAREA:
				$h = '<textarea id="CFG_' . $this->key . '" name="' . $this->key .'">' .  GetDBText($this->Val()) . '</textarea>';
			break;
			case \HTMLType::FILE_IMAGE:
				$h = '<input type="hidden" name="file_field[]" value="'. $this->key . '">';
				if($this->value){
					$h .= '<img src="' . Paths::UrlOfUpload(). GetDBText($this->value) . '" style="max-width:100px; max-height:100px;">';
				}
				$h .= '<input type="file" name="' . $this->key .'" accept="image/*" ' . $attr . '> <label class="checkbox"><input type="checkbox" name="_delFile[]" value="' . GetDBText($this->value) . '"><i></i><span>' . BH_Application::$lang['DEL'] . '</span></label>';
			break;
			case \HTMLType::RADIO:
				$h = InputRadio($this->key, $this->enumValues, strlen($this->Val()) ? $this->Val() : $this->defaultValue, $class);
			break;
			case \HTMLType::CHECKBOX:
				$h = InputCheckbox($this->key, $this->enumValues, strlen($this->Val()) ? $this->Val() : $this->defaultValue, $class);
			break;
		}

		return $h;
	}

	public function PrintHidden(){
		return '<input type="hidden" id="CFG_' . $this->key . '" name="' . $this->key .'" value="' .  GetDBText($this->Val()) . '">';
	}
}

class _ConfigModel{
	protected $_code = '';

	/**
	 * @return static
	 */
	public static function GetInstance(){
		static $instance;
		if(!$instance) $instance = new static();
		return $instance;
	}

	protected function __construct(){ }

	private function __clone(){}

	public function __get($name){
		if(BH_Application::$showError) PrintError(BH_Application::$lang['C_MODEL_NO_PARAM']);
		return _CfgData::GetInstance($name);
	}

	protected function GetFileSetting(){
		if(!strlen($this->_code)){
			if(BH_Application::$showError) PrintError(BH_Application::$lang['C_MODEL_MISSING_CODE_NAME']);
			exit;
		}
		// 설정불러오기
		$path = \Paths::DirOfData().'/CFG/'.$this->_code.'.php';
		if(file_exists($path)){
			$data = file_get_contents($path);
			if(substr($data, 0, 15) == '<?php return;/*'){
				$temp = json_decode(substr($data, 15), true);
				foreach($temp as $k => $v){
					if(isset($this->{$k})) $this->{$k}->SetValue($v);
					else if(_DEVELOPERIS === true){
						$k = strtolower($k[0]) . substr($k, 1);
						if(isset($this->{$k})) $this->{$k}->SetValue($v);
					}
				}
			}
		}
	}

	/**
	 *
	 * @param $data
	 * @param null $files
	 * @return BH_Result
	 */
	public function DataWrite($data = array(), $files = null){
		if(BH_Application::$version === '') return;

		$fileNames = isset($data['file_field']) ? $data['file_field'] : array();

		if(!file_exists( \Paths::DirOfData().'/CFG') || !is_dir(\Paths::DirOfData().'/CFG')) mkdir(\Paths::DirOfData().'/CFG', 0755, true);
		foreach($data as $k => $v){
			if(!isset($this->{$k}) || $k === '_delFile') continue;
			$this->{$k}->value = $v;
			if($this->{$k}->type == \HTMLType::TEXTAREA) $this->{$k}->value = BH_Common::ContentImageUpdate('cfg.ct', array('content_cfg_' . $k), array('contents' => $this->{$k}->value), 'modify-cfg');
		}

		if(isset($data['_delFile']) && is_array($data['_delFile'])){
			foreach($data['_delFile'] as $v){
				preg_match('/([a-zA-Z0-9_]+)\[([0-9]*?)\]/', $v, $matches);
				if(isset($matches[2])){
					if(isset($this->{$matches[1]}[$matches[2]])){
						@unlink(\Paths::DirOfUpload().$this->{$matches[1]}[$matches[2]]->value);
						$this->{$matches[1]}[$matches[2]]->value = '';
					}

				}
				else{
					@unlink(\Paths::DirOfUpload().$this->{$v}->value);
					if(isset($this->{$v})) $this->{$v}->value = '';
				}
			}
		}

		if(!is_null($files)){
			foreach($files as $k => $file){
				if(!isset($this->{$k})) continue;
				if(in_array($k, $fileNames)){
					if(is_array($file['name'])){
						$fres_em = \_ModelFunc::FileUploadArray($file, null, '/CFG/files/');
						foreach($fres_em as $row){
							if(is_array($row)){
								$this->{$k}->value[] = $row['file'];
							}
						}
					}
					else{
						$fres_em = \_ModelFunc::FileUpload($file, null, '/CFG/files/');

						if(is_string($fres_em)) URLRedirect(-1, $fres_em);
						else if(is_array($fres_em)){
							if(strlen($this->{$k}->value)) @unlink(\Paths::DirOfUpload().$this->{$k}->value);
							$this->{$k}->value = $fres_em['file'];
							if(class_exists('\\PHP_ICO')){
								if($this->_code === 'Default' && $k == 'FaviconPng'){
									$temp = explode('.', $fres_em['file']);
									array_pop($temp);
									$pico = new \PHP_ICO(\Paths::DirOfUpload() . $fres_em['file'], array( array( 16, 16 ), array( 32, 32 ), array( 64, 64 ) ));
									$pico->save_ico(_DIR . '/favicon.ico');
								}
							}
						}
					}
				}
			}
		}

		$path = \Paths::DirOfData().'/CFG/'.$this->_code.'.php';
		$arr = get_object_vars($this);
		$saveData = array();
		foreach($arr as $k => $v){
			if($k[0] !== '_') $saveData[$k] = $v->value;
		}
		$txt = '<?php return;/*'.json_encode($saveData);
		file_put_contents($path, $txt);
		return BH_Result::Init(true);
	}

	public function GetCode(){
		return $this->_code;
	}
}