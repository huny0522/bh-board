<?php
/**
 * Bang Hun.
 * 16.07.10
 */
define('QRY_GET', 1);
define('QRY_LIST', 2);
define('QRY_PAGE_LIST', 3);
define('QRY_INSERT', 4);
define('QRY_UPDATE', 5);
define('QRY_DELETE', 6);

class ModelType{
	const Int = 1;
	const String = 2;
	const Float = 6;
	const Datetime = 7;
	const Date = 8;
	const Enum = 9;
}

class HTMLType{
	const InputText = 'text';
	const InputPassword = 'password';
	const InputRadio = 'radio';
	const InputCheckbox = 'checkbox';
	const InputEmail = 'email';
	const InputTel = 'tel';
	const InputFile = 'file';
	const InputFileWithName = 'filewithname';
	const InputImageFile = 'imagefile';
	const InputImageFileArray = 'imagefilearray';
	const Select = 'select';
	const Textarea = 'textarea';
	const InputDate = 'date';
	const InputDatePicker = 'datepicker';
	const NumberFormat = 'numberformat';
	const InputEng = 'engonly';
	const InputEngNum = 'engnumonly';
	const InputEngSpecial = 'engspecialonly';
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
	public $BlankIsNull = false;

	public function __construct($Type = ModelType::String, $Required = false, $DisplayName = '', $HtmlType = HTMLType::InputText){
		$this->Type = $Type;
		$this->Required = $Required;
		$this->DisplayName = $DisplayName;
		if($HtmlType) $this->HtmlType = $HtmlType;
	}

	public function __toString(){
		return $this->GetValue();
	}

	/**
	 * @param int $Type
	 * @param string $DisplayName
	 * @param string $HtmlType
	 * @return BH_ModelData
	 */
	public static function GetInstance($Type = ModelType::String, $DisplayName = '', $HtmlType = HTMLType::InputText){
		return new self($Type, false, $DisplayName, $HtmlType);
	}

	/**
	 * 데이타의 값 반환
	 * @return null|string
	 */
	public function GetValue(){
		return isset($this->Value) ? ($this->Type == ModelType::Enum && isset($this->EnumValues[$this->Value]) ? $this->EnumValues[$this->Value] : $this->Value) : NULL;
	}

	public function GetSafeValue(){
		return GetDBText($this->GetValue());
	}

	public function GetSafeRawValue(){
		return GetDBRaw($this->GetValue());
	}

	public function GetSafeBRValue(){
		return nl2br(GetDBText($this->GetValue()));
	}

	/**
	 * 데이타의 실제 값 반환
	 * @return null|string
	 */
	public function GetNativeValue(){
		return isset($this->Value) ? $this->Value : NULL;
	}

	public function &SetValue($v){
		$this->Value = trim($v);
		return $this;
	}

	public function &SetRequired($bool = true){
		$this->Required = $bool;
		return $this;
	}

	public function &SetMinLength($num){
		$this->MinLength = $num;
		return $this;
	}

	public function &SetMaxLength($num){
		$this->MaxLength = $num;
		return $this;
	}

	public function &SetMinValue($num){
		$this->MinValue = $num;
		return $this;
	}

	public function &SetMaxValue($num){
		$this->MaxValue = $num;
		return $this;
	}

	public function &SetEnumValues($array){
		$this->EnumValues = $array;
		return $this;
	}

	public function &SetDefaultValue($val){
		$this->DefaultValue = $val;
		return $this;
	}

	public function &SetType($type){
		$this->Type = $type;
		return $this;
	}

	public function &SetDisplayName($str){
		$this->DisplayName = $str;
		return $this;
	}

	public function &SetModelErrorMsg($str){
		$this->ModelErrorMsg = $str;
		return $this;
	}

	public function &SetHtmlType($str){
		$this->HtmlType = $str;
		return $this;
	}

	public function &SetAutoDecrement($bool = true){
		$this->AutoDecrement = $bool;
		return $this;
	}

	public function &SetValueIsQuery($bool = true){
		$this->ValueIsQuery = $bool;
		return $this;
	}

	public function &SetBlankIsNull($bool = true){
		$this->BlankIsNull = $bool;
		return $this;
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
	/**
	 * @var BH_DB_Get|BH_DB_GetListWithPage|BH_DB_GetList|BH_DB_Insert|BH_DB_Update|BH_DB_Delete
	 */
	public $qry = null;
	protected $listPosition = -1;
	protected $connName = '';
	/**
	 * @var BH_Model
	 */
	public $parent = null;

	public function __construct(){
		$this->connName = \DB::DefaultConnName;
		if(method_exists($this, '__Init')) $this->__Init();
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
	 * @param BH_Model $model
	 * @param string $tableNaming
	 * @param string $on
	 * @return $this
	 */
	public function &LeftJoin(&$model, $tableNaming, $on){
		$args = func_get_args();
		array_unshift($args, "LEFT");
		$args[1] = &$model;
		_ModelFunc::_Join($this, $args);
		return $this;
	}

	/**
	 * @param BH_Model $model
	 * @param string $tableNaming
	 * @param string $on
	 * @return $this
	 */
	public function &RightJoin(&$model, $tableNaming, $on){
		$args = func_get_args();
		array_unshift($args, "RIGHT");
		$args[1] = &$model;
		_ModelFunc::_Join($this, $args);
		return $this;
	}

	/**
	 * @param BH_Model $model
	 * @param string $tableNaming
	 * @param string $on
	 * @return $this
	 */
	public function &InnerJoin(&$model, $tableNaming, $on){
		$args = func_get_args();
		array_unshift($args, "INNER");
		$args[1] = &$model;
		_ModelFunc::_Join($this, $args);
		return $this;
	}

	/**
	 * @param BH_Model $model
	 * @param string $tableNaming
	 * @param string $on
	 * @return $this
	 */
	public function &OuterJoin(&$model, $tableNaming, $on){
		$args = func_get_args();
		array_unshift($args, "OUTER");
		$args[1] = &$model;
		_ModelFunc::_Join($this, $args);
		return $this;
	}

	/**
	 * @param int $type
	 * @param string $tableNaming
	 * @return $this
	 */
	public function &SetQry($type, $tableNaming = ''){
		_ModelFunc::SetQry($this, $type, $tableNaming);
		return $this;
	}

	/**
	 * @param string $tableNaming
	 * @return BH_DB_Get
	 */
	public function &GetSetQry($tableNaming = ''){
		_ModelFunc::SetQry($this, QRY_GET, $tableNaming);
		return $this->qry;
	}

	/**
	 * @param string $tableNaming
	 * @return BH_DB_GetList
	 */
	public function &GetSetListQry($tableNaming = ''){
		_ModelFunc::SetQry($this, QRY_LIST, $tableNaming);
		return $this->qry;
	}

	/**
	 * @param string $tableNaming
	 * @return BH_DB_GetListWithPage
	 */
	public function &GetSetPageListQry($tableNaming = ''){
		_ModelFunc::SetQry($this, QRY_PAGE_LIST, $tableNaming);
		return $this->qry;
	}

	/**
	 * @param string $tableNaming
	 * @return BH_DB_Insert
	 */
	public function &GetSetInsertQry($tableNaming = ''){
		_ModelFunc::SetQry($this, QRY_INSERT, $tableNaming);
		return $this->qry;
	}

	/**
	 * @param string $tableNaming
	 * @return BH_DB_Update
	 */
	public function &GetSetUpdateQry($tableNaming = ''){
		_ModelFunc::SetQry($this, QRY_UPDATE, $tableNaming);
		return $this->qry;
	}

	/**
	 * @param string $tableNaming
	 * @return BH_DB_Delete
	 */
	public function &GetSetDeleteQry($tableNaming = ''){
		_ModelFunc::SetQry($this, QRY_DELETE, $tableNaming);
		return $this->qry;
	}

	/**
	 * @return bool
	 */
	public function QryNext(){
		if(!$this->qry->drawRowsIs) $this->qry->DrawRows();
		$this->listPosition++;
		return isset($this->qry->data[$this->listPosition]);
	}

	public function QryDraw(){
		$this->qry->DrawRows();
	}

	/**
	 * @param int $pos
	 * @return bool
	 */
	public function QryPosition($pos){
		if(!$this->qry->drawRowsIs) $this->qry->DrawRows();
		$this->listPosition = $pos;
		return isset($this->qry->data[$this->listPosition]);
	}

	/**
	 * @param string $key
	 * @param null|string $dataKey
	 * @return mixed
	 */
	public function QryValue($key, $dataKey = null){
		return _ModelFunc::QryValueByModel($this, $key, $dataKey);
	}

	/**
	 * @param string $key
	 * @param null|string $dataKey
	 * @return mixed
	 */
	public function QrySafeValue($key, $dataKey = null){
		return GetDBText(_ModelFunc::QryValueByModel($this, $key, $dataKey));
	}

	/**
	 * @param string $key
	 * @param null|string $dataKey
	 * @return mixed
	 */
	public function QrySafeRawValue($key, $dataKey = null){
		return GetDBRaw(_ModelFunc::QryValueByModel($this, $key, $dataKey));
	}

	/**
	 * @param string $key
	 * @param null|string $dataKey
	 * @return mixed
	 */
	public function QrySafeBRValue($key, $dataKey = null){
		return nl2br(GetDBText(_ModelFunc::QryValueByModel($this, $key, $dataKey)));
	}

	/**
	 * @return bool|string
	 */
	public function TotalRecord(){
		if(!$this->qry->drawRowsIs) $this->qry->DrawRows();
		if($this->qry->result) return $this->qry->totalRecord;
		else return false;
	}

	/**
	 * 표시명 반환
	 * @param string $key
	 * @return null|string
	 */
	public function GetDisplayName($key){
		return isset($this->data[$key]->DisplayName) ? $this->data[$key]->DisplayName : NULL;
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
	 * 데이타를 등록
	 * @param array $Values
	 */
	public function SetDBValues($Values){
		foreach($Values as $k=>$v) if(isset($this->data[$k])) $this->data[$k]->Value = $v;
	}

	/**
	 * 데이타의 값 반환
	 * @param string $key
	 * @param bool $enumVal true일경우 Enum키의 값을 반환
	 * @return null|string
	 */
	public function GetValue($key, $enumVal = false){
		return isset($this->data[$key]->Value) ? (($enumVal && $this->data[$key]->Type == ModelType::Enum && isset($this->data[$key]->EnumValues[$this->data[$key]->Value])) ? $this->data[$key]->EnumValues[$this->data[$key]->Value] : $this->data[$key]->Value) : NULL;
	}

	public function GetSafeValue($key, $enumVal = true){
		return GetDBText($this->GetValue($key, $enumVal));
	}

	public function GetSafeRawValue($key, $enumVal = true){
		return GetDBRaw($this->GetValue($key, $enumVal));
	}

	public function GetSafeBRValue($key, $enumVal = true){
		return nl2br(GetDBText($this->GetValue($key, $enumVal)));
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
	 * @param $key
	 * @param $v
	 * @return bool
	 */
	public function SetValue($key, $v){
		return _ModelFunc::SetValue($this, $key, $v);
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
		return _ModelFunc::SetQueryValue($this, $key, $v);
	}

	/**
	 * 제외 키 등록
	 * @param array $ar
	 */
	public function AddExcept($ar){
		_ModelFunc::AddExcept($this, $ar);
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
	 * @param string $firstIDName
	 *
	 * @return string
	 */
	public function HTMLPrintLabel($Name, $HtmlAttribute = false, $firstIDName = 'MD_'){
		return _ModelFunc::HTMLPrintLabel($this, $Name, $HtmlAttribute, $firstIDName);
	}

	/**
	 * input, select textarea 출력
	 * @param string $Name
	 * @param bool $HtmlAttribute
	 * @param string $firstIDName
	 *
	 * @return string
	 */
	public function HTMLPrintInput($Name, $HtmlAttribute = false, $firstIDName = 'MD_'){
		return _ModelFunc::HTMLPrintInput($Name, $this->data[$Name], $HtmlAttribute, $firstIDName);
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
}

class _ModelFunc{
	public static function SetQry(&$model, $type, $tableNaming){
		if($type === QRY_LIST) $model->qry = new BH_DB_GetList();
		else if($type === QRY_PAGE_LIST) $model->qry = new BH_DB_GetListWithPage();
		else if($type === QRY_INSERT) $model->qry = new BH_DB_Insert();
		else if($type === QRY_UPDATE) $model->qry = new BH_DB_Update();
		else if($type === QRY_DELETE) $model->qry = new BH_DB_Delete();
		else $model->qry = new BH_DB_Get();

		$model->qry->AddTable('`' . $model->table . '`' . (strlen($tableNaming) ? ' `' . $tableNaming . '`' : ''));
	}
	public static function _Join(&$model, $args){
		$args[1]->parent = &$model;
		$n = array_values(array_slice($args, 3));
		$txt = $model->qry->StrToPDO($n);
		$model->qry->AddTable('%1 JOIN `%1` `%1` ON %1', $args[0], $args[1]->table, $args[2], $txt);
		return true;
	}

	public static function QryValueByModel(&$nowModel, $key, $dataKey = null){
		$refModel = &$nowModel;
		while(!is_null($nowModel->parent)) $nowModel = &$nowModel->parent;

		if(is_null($dataKey)) $dataKey = $key;
		if(!isset($nowModel->qry->data[$nowModel->listPosition][$key])) return null;
		if($dataKey === false) return $nowModel->qry->data[$nowModel->listPosition][$key];
		if(isset($refModel->data[$dataKey]) && $refModel->data[$dataKey]->Type == ModelType::Enum){
			if(isset($refModel->data[$dataKey]->EnumValues[$nowModel->qry->data[$nowModel->listPosition][$key]])) return $refModel->data[$dataKey]->EnumValues[$nowModel->qry->data[$nowModel->listPosition][$key]];
			return null;
		}
		return $nowModel->qry->data[$nowModel->listPosition][$key];
	}

	public static function IsFileType($type){
		return in_array($type, array(HTMLType::InputFile, HTMLType::InputFileWithName, HTMLType::InputImageFile, HTMLType::InputImageFileArray));
	}

	public static function SetPostValues(&$model, &$post, $withFile = false){
		$ret = new \BH_Result();
		$ret->result = true;
		foreach($model->data as $k => &$v){
			if(!in_array($k, $model->Except) && $v->AutoDecrement !== true){
				if(isset($v->HtmlType) && self::IsFileType($v->HtmlType) && isset($_FILES[$k])){
					if($withFile) self::SetFileValue($model, $k);
				}
				else if(!isset($post[$k])){
					if($v->BlankIsNull){
						$v->Value = 'NULL';
						$v->ValueIsQuery = true;
						$model->Need[] = $k;
					}
					else if(isset($model->Need) && in_array($k, $model->Need)){
						$ret->message = $v->ModelErrorMsg = $v->DisplayName.' 항목이 정의되지 않았습니다.';
						$ret->result = false;
						return $ret;
					}
				}
				else{
					if($v->HtmlType == HTMLType::InputImageFileArray){
						$delFiles = Post('del_file_' . $k);
						if(!is_array($delFiles)) $delFiles = array();

						$values = array();
						if(!is_array($post[$k])){
							$ret->message = $v->ModelErrorMsg = $v->DisplayName.'항목이 다중 파일 형식이 아닙니다.';
							$ret->result = false;
							return $ret;
						}

						foreach($post[$k] as $path){
							if($path){
								$newpath = self::ReservedMoveFile($path, $model->table);
								if(is_string($newpath)){
									$values[]= $newpath;
									$v->__moveFile[]= array('source' => $path, 'dest' => $newpath);
									$model->Need[] = $k;
								}
								else if($newpath->result === -1){
									$ret->message = $v->ModelErrorMsg = $v->DisplayName . '항목에 ' . $newpath->message;
									$ret->result = false;
									return $ret;
								}
							}
						}

						// 기존 파일
						if(strlen($v->Value)){
							$p = explode(';', $v->Value);
							$valuePath = array();
							foreach($p as $path){
								if(in_array($path, $delFiles)) $v->__deleteFile[] = $path;
								else $valuePath[]= $path;
							}
							$values = array_merge($values, $valuePath);
						}
						$v->Value = implode(';', $values);
						$model->Need[] = $k;
					}

					else if($v->HtmlType == HTMLType::InputImageFile){
						$fileUpIs = false;
						if(strlen($post[$k])){
							$fileUpIs = true;
							$newpath = self::ReservedMoveFile($post[$k], $model->table);

							if(is_string($newpath)){
								$v->__moveFile[]= array('source' => $post[$k], 'dest' => $newpath);
								// 기존 파일
								if(strlen($v->Value)) $v->__deleteFile[]= $v->Value;

								$v->Value = $newpath;
								$model->Need[] = $k;
							}
							else{
								if($newpath->result === -1){
									$ret->message = $v->ModelErrorMsg = $v->DisplayName . '항목에 ' . $newpath->message;
									$ret->result = false;
									return $ret;
								}
							}
						}

						if(!$fileUpIs && strlen($v->Value) && Post('del_file_' . $k) == 'y'){
							$v->__deleteFile[]= $v->Value;
							$v->Value = '';
							$model->Need[] = $k;
						}
					}

					else if((isset($v->HtmlType) || $v->Required) && !self::IsFileType($v->HtmlType)){
						if(!strlen($post[$k]) && $v->BlankIsNull){
							$v->Value = 'NULL';
							$v->ValueIsQuery = true;
						}
						else{
							if($v->HtmlType === HTMLType::NumberFormat) $v->Value = preg_replace('/[^0-9]/', '', $post[$k]);
							else $v->Value = $post[$k];
						}
						$model->Need[] = $k;
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
		if(strpos($path, '..') !== false) return (object) array('result' => -1, 'message' => '잘못된 파일경로가 탐지었습니다.');

		$path = preg_replace('/[^0-9a-zA-Z\/\_\-\!\@\.]/', '', $path);
		if(file_exists(_UPLOAD_DIR . $path)){
			$upDir = '/modelData/' . $dir . '/' . date('Ym') . '/';
			$newpath = substr_replace($path, $upDir, 0, 6);
			if(!is_dir(_UPLOAD_DIR . $upDir)) mkdir(_UPLOAD_DIR . $upDir, 0777, true);

			return $newpath;
		}
		return (object) array('result' => -2, 'message' => '업로드한 파일이 존재하지 않습니다.');
	}

	/**
	 * @param \BH_Model $model
	 */
	private static function FileProcess(&$model){
		foreach($model->data as $v){
			if(self::IsFileType($v->HtmlType)){
				if(isset($v->__moveFile) && is_array($v->__moveFile)){
					foreach($v->__moveFile as $mv){
						@copy(_UPLOAD_DIR . $mv['source'], _UPLOAD_DIR . $mv['dest']);
						UnlinkImage(_UPLOAD_DIR . $mv['source']);
					}
				}
				if(isset($v->__deleteFile) && is_array($v->__deleteFile)){
					foreach($v->__deleteFile as $f){
						UnlinkImage(_UPLOAD_DIR . $f);
					}
				}
			}
		}
	}

	public static function GetErrorMessage(&$model, &$ret){
		foreach($model->data as $k=>$v){
			self::ValueCheck($model, $k);
			if($v->ModelErrorMsg) $ret[] =$v->ModelErrorMsg;
		}
	}

	public static function SetValue(&$model, $key, $v){
		if(!isset($model->data[$key])) return $key.' 키값이 정의되어 있지 않습니다.';

		if(isset($v)){
			$model->data[$key]->Value = trim($v);
			$model->Need[] = $key;
		}
		return true;
	}

	public static function GetFilePath($data, $n, $n2){
		if(isset($data->Value)){
			if(self::IsFileType($data->HtmlType)){
				$p = explode(';', $data->Value);
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
			else return $data->Value;
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

	public static function SetQueryValue(&$model, $key, $v){
		if(!isset($model->data[$key])) URLReplace('-1', 'No Key : ' . $key);

		if(!isset($v)) return true;

		$model->data[$key]->Value = $v;
		$model->data[$key]->ValueIsQuery = true;
		$model->Need[] = $key;
		return true;
	}

	public static function AddExcept(&$model, $ar){
		if(is_string($ar)){
			$model->Except[] = $ar;
			return;
		}
		foreach($ar as $v) $model->Except[] = $v;
	}

	public static function ValueCheck(&$model, $key){
		if(in_array($key, $model->Except)) return true;
		if($model->data[$key]->ValueIsQuery) return true;
		if(self::CheckRequired($model, $key) === false) return false;
		if(isset($model->data[$key]->Value) && strlen($model->data[$key]->Value)){
			if(self::CheckType($key, $model->data[$key]) === false) return false;
			if(self::CheckLength($key, $model->data[$key]) === false) return false;
			if(self::CheckValue($key, $model->data[$key]) === false) return false;
		}
		return true;
	}

	public static function CheckType($key, &$data){
		switch($data->Type){
			case ModelType::Int:
				$val = preg_replace('/[^0-9\-]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 숫자만 입력 가능합니다.';
					return false;
				}
			break;
			case ModelType::Float:
				$val = preg_replace('/[^0-9\.\-]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 숫자만 입력 가능합니다.';
					return false;
				}
			break;
			case ModelType::Enum:
				if(!(isset($data->EnumValues) && is_array($data->EnumValues) && isset($data->EnumValues[$data->Value]))){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목에 값이 필요합니다.';
					return false;
				}
			break;
		}
		switch($data->HtmlType){
			case HTMLType::InputEmail:
				if (!filter_var($data->Value, FILTER_VALIDATE_EMAIL)) {
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 형식이 올바르지 않습니다.';
					return false;
				}
			break;
			case HTMLType::InputTel:
				$val = preg_replace('/[^0-9\-\*\#]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 형식이 올바르지 않습니다.';
					return false;
				}
			break;
			case HTMLType::InputEng:
				$val = preg_replace('/[^a-zA-Z]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문만 입력가능합니다.';
					return false;
				}
			break;
			case HTMLType::InputEngNum:
				if ( !ctype_alnum($data->Value) ){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문과 숫자만 입력가능합니다.';
					return false;
				}
			break;
			case HTMLType::InputEngSpecial:
				$val = preg_replace('/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>\'\"\?\-=\+_\:\;\[\]\{\}\/]/','',$data->Value);
				if($val != $data->Value){
					$data->ModelErrorMsg = $data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 영문과 숫자, 특수문자만 입력가능합니다.';
					return false;
				}
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
			if($Data->MinLength !== false && $Data->MinLength > mb_strlen($Data->Value, 'UTF-8')){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 '.$Data->MinLength.'자 이상 입력하여 주세요.';
				return false;
			}
			if($Data->MaxLength !== false && $Data->MaxLength < mb_strlen($Data->Value, 'UTF-8')){
				$Data->ModelErrorMsg = $Data->DisplayName.(_DEVELOPERIS === true ? '('.$key.')' : '').' 항목은 '.$Data->MaxLength.'자 이하 입력하여 주세요.';
				return false;
			}
		}
		return true;
	}

	public static function CheckRequired(&$model, $key){
		if($model->data[$key]->Required == false) return true;
		if(is_null($model->GetValue($key)) || !strlen($model->GetValue($key))){
			if(!in_array($key, $model->Except) && $model->data[$key]->AutoDecrement !== true){
				$model->data[$key]->ModelErrorMsg = $model->data[$key]->DisplayName.' 항목은 필수항목입니다.';
				return false;
			}
		}
		return true;
	}

	public static function HTMLPrintLabel(&$model, $Name, $HtmlAttribute, $firstIDName){
		$Attribute = '';
		if($HtmlAttribute === false) $HtmlAttribute = array();
		foreach($HtmlAttribute as $k => $row){
			$Attribute .= ' '.$k.'="'.$row.'"';
		}
		return '<label for="'.$firstIDName.$Name.'" '.$Attribute.'>'.$model->data[$Name]->DisplayName.'</label>';
	}

	public static function HTMLPrintInput($Name, &$data, $HtmlAttribute = false, $firstIDName){
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

		// ModelType
		if($data->Type == ModelType::Int && $data->HtmlType != 'numberformat') $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'numberonly';

		// HTMLType
		if($data->HtmlType == HTMLType::InputEmail) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'email';
		else if($data->HtmlType == HTMLType::InputTel) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'tel';
		else if($data->HtmlType == HTMLType::InputDate){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'date';
			$HtmlAttribute['maxlength'] = '10';
			$HtmlAttribute['minlength'] = '10';
		}
		else if($data->HtmlType == HTMLType::InputDatePicker){
			$HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'datePicker';
			$HtmlAttribute['maxlength'] = '10';
			$HtmlAttribute['minlength'] = '10';
		}
		else if($data->HtmlType == HTMLType::NumberFormat) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').HTMLType::NumberFormat;
		else if($data->HtmlType == HTMLType::InputEngNum) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').HTMLType::InputEngNum;
		else if($data->HtmlType == HTMLType::InputEng) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').HTMLType::InputEng;
		else if($data->HtmlType == HTMLType::InputEngSpecial) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').HTMLType::InputEngSpecial;
		else if(in_array($data->HtmlType, array(HTMLType::InputImageFile, HTMLType::InputImageFileArray))) $HtmlAttribute['class'] .= ($HtmlAttribute['class'] ? ' ' : '').'fileUploadInput';

		foreach($HtmlAttribute as $k => $row) $Attribute .= ' '.$k.'="'.$row.'"';

		switch($htmlType){
			case HTMLType::InputText:
			case HTMLType::InputPassword:
			case HTMLType::InputEmail:
			case HTMLType::InputTel:
				return '<input type="'.$htmlType.'" name="'.$Name.'" id="'.$firstIDName.$Name.'" '.(isset($val) && $htmlType != HTMLType::InputPassword ? 'value="'.GetDBText($val).'"' : '').' data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLType::NumberFormat:
			case HTMLType::InputDatePicker:
			case HTMLType::InputEngNum:
			case HTMLType::InputEng:
			case HTMLType::InputEngSpecial:
				return '<input type="text" name="'.$Name.'" id="'.$firstIDName.$Name.'" '.(isset($val) ? 'value="'.GetDBText($val).'"' : '').' data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLType::InputDate:
				return '<span class="dateInput"><input type="text" name="'.$Name.'" id="'.$firstIDName.$Name.'" '.(isset($val) ? 'value="'.GetDBText($val).'"' : '').' data-displayname="' . $data->DisplayName . '" '.$Attribute.'></span>';
			break;
			case HTMLType::InputFileWithName:
				$h = '';
				if(strlen($data->Value)){
					$f = explode('*', $data->Value);
					$h = ' <span class="uploadedFile">' . (isset($f[1]) ? GetDBText($f[1]) : '') . ' <label class="checkbox"><input type="checkbox" name="del_file_' . $Name . '" value="y"><span> 파일삭제</span></label></span>';
				}
				return $h . ' <input type="file" name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLType::InputFile:
				$h = '';
				if(strlen($data->Value)){
					$h = ' <span class="uploadedFile"><label class="checkbox"><input type="checkbox" name="del_file_' . $Name . '" value="y"><span> 파일삭제</span></label></span>';
				}
				return $h . ' <input type="file" name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
			break;
			case HTMLType::InputImageFile:
				$h = '<div class="fileUploadArea"><input type="hidden" name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';
				$h .= '<span class="fileUploadImage">';
				if(strlen($data->Value)){
					$h .= '<i style="background-image:url(' . _UPLOAD_URL . $data->Value . ')"></i>';
				}
				$h .= '</span>';
				if(strlen($data->Value)) $h .= ' <label class="uploadedImgFile checkbox"><input type="checkbox" name="del_file_' . $Name . '" value="y"><span>삭제</span></label>';
				return $h . '<button type="button" class="fileUploadBtn sBtn"><span>이미지업로드</span></button></div><script>JCM.imageFileForm();</script>';
			break;
			case HTMLType::InputImageFileArray:
				$h = '<div class="multiFileUploadArea">';
				if(strlen($data->Value)){
					$p = explode(';', $data->Value);
					foreach($p as $path){
						$h .= ' <span class="fileUploadImage"><i style="background-image:url(' . _UPLOAD_URL . $path . ')"></i></span> <label class="uploadedImgFile checkbox"><input type="checkbox" name="del_file_' . $Name . '[]" value="' . $path . '"><span>삭제</span></label>';
					}
				}
				$h .= '<div class="fileUploadArea"><span class="fileUploadImage"></span><input type="hidden" name="'.$Name.'[]" data-displayname="' . $data->DisplayName . '" '.$Attribute.'><button type="button" class="fileUploadBtn sBtn"><span>이미지업로드</span></button><button type="button" class="fileUploadAreaAddBtn sBtn">추가</button><button type="button" class="fileUploadAreaRmBtn sBtn">삭제</button></div>';
				return $h . '</div><script>JCM.imageFileForm();</script>';
			break;
			case HTMLType::Textarea:
				return '<textarea name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>'.(isset($val) ? GetDBText($val) : '').'</textarea>';
			break;
			case HTMLType::InputRadio:
			case HTMLType::InputCheckbox:
				$ret = '';
				if(isset($data->EnumValues) && is_array($data->EnumValues)){
					$i = 1;
					foreach($data->EnumValues as $k=>$v){
						$checked = isset($val) && $k == $val ? ' checked="checked"' : '';

						$ret .= '<label for="'.$firstIDName.$Name.'_'.$i.'" class="'.$htmlType.'"><input type="'.$htmlType.'" name="'.$Name.'" id="'.$firstIDName.$Name.'_'.$i.'" value="'.$k.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.$checked.'> <span>'.$v.'</span></label>';
						$i++;
					}
				}
				return $ret;
			break;
			case HTMLType::Select:
				$ret = '<select name="'.$Name.'" id="'.$firstIDName.$Name.'" data-displayname="' . $data->DisplayName . '" '.$Attribute.'>';

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

	public static function DBInsert(&$model, $test = false){
		$dbInsert = new \BH_DB_Insert($model->table);
		$dbInsert->SetConnName($model->GetConnName());
		$result = new \BH_InsertResult();

		foreach($model->data as $k=>$v){
			if(!isset($v->Value) && in_array($k, $model->Need)){
				$result->result = false;
				$result->message = 'ERROR#101';
				return $result;
			}

			// 예외 패스, 셋이 없거나 셋에 있는것
			if((!in_array($k, $model->Except) && (!sizeof($model->Need) || in_array($k, $model->Need)))){
				if(isset($v->Value)){
					if(in_array($k, $model->Key) && $v->AutoDecrement === true) continue;

					if(!$v->ValueIsQuery && $v->HtmlType == HTMLType::InputTel) $v->Value = preg_replace('/[^0-9\-\*\#]/','',$v->Value);

					if($v->ValueIsQuery) $dbInsert->data[$k] = $v->Value;
					else if($v->Type == ModelType::Int){
						if(!strlen($v->Value) && !isset($v->DefaultValue)) continue;
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
						if(!strlen($v->Value) && !isset($v->DefaultValue)) continue;
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
					else if($v->HtmlType == HTMLType::InputPassword) $dbInsert->SetDataStr($k, _password_hash($v->Value));
					else $dbInsert->SetDataStr($k, $v->Value);
				}
			}
		}

		foreach($model->Key as $k){
			if($model->data[$k]->AutoDecrement === true) $dbInsert->decrement = $k;
			else if($model->data[$k]->Value) $dbInsert->AddWhere($k.'= %s', $model->data[$k]->Value);
		}
		if(!$dbInsert->decrement) $dbInsert->UnsetWhere();
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
		$dbUpdate->SetConnName($model->GetConnName());
		foreach($model->data as $k=>$v){
			if(!isset($v->Value) && in_array($k, $model->Need)){
				$result->result = false;
				$result->message = 'ERROR';
				return $result;
			}

			// 예외와 키값 패스, 셋이 없거나 셋에 있는것
			if(!in_array($k, $model->Except) && (!sizeof($model->Need) || in_array($k, $model->Need)) && !in_array($k, $model->Key)){
				if(isset($v->Value)){
					if(in_array($k, $model->Key) && $v->AutoDecrement === true) continue;

					if(!$v->ValueIsQuery && $v->HtmlType == HTMLType::InputTel) $v->Value = preg_replace('/[^0-9\-\*\#]/','',$v->Value);

					if($v->ValueIsQuery) $dbUpdate->SetData($k, $v->Value);
					else if($v->Type == ModelType::Int){
						if(!strlen($v->Value)) continue;
						$res = self::CheckInt($k, $v->Value);
						if($res === true) $dbUpdate->SetDataNum($k, $v->Value);
						else{
							$result->result = false;
							$result->message = $res;
						}
					}
					else if($v->Type == ModelType::Float){
						if(!strlen($v->Value)) continue;
						$res = self::CheckFloat($k, $v->Value);
						if($res === true) $dbUpdate->SetDataNum($k, $v->Value);
						else{
							$result->result = false;
							$result->message = $res;
						}
					}
					else if($v->HtmlType == HTMLType::InputPassword) $dbUpdate->SetDataStr($k, _password_hash($v->Value));
					else $dbUpdate->SetDataStr($k, $v->Value);
				}
			}
		}
		foreach($model->Key as $k){
			if(isset($model->data[$k]->Value) && strlen($model->data[$k]->Value)) $dbUpdate->AddWhere($k.' = %s', $model->data[$k]->Value);
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

		if(!isset($model->Key) || !is_array($model->Key)){
			if(_DEVELOPERIS === true){
				echo '키값이 존재하지 않습니다.';
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keys) != sizeof($model->Key)){
			if(_DEVELOPERIS === true){
				echo '모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.';
				exit;
			}
			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}
		$dbGet = new \BH_DB_Get($model->table);
		$dbGet->SetConnName($model->GetConnName());
		foreach($model->Key as $k => $v) $dbGet->AddWhere($v.' = %s', trim($keys[$k]));
		$data = $dbGet->Get();

		if($data !== false){
			foreach($data as $k=>$v) if(isset($model->data[$k])) $model->data[$k]->Value = $v;
			$res->result = true;
		}
		else $res->result = false;

		return $res;
	}

	public static function DBDelete(&$model, $keyData, $withFile = false){
		$res = new \BH_Result();

		if(!is_array($keyData)) $keyData = array($keyData);

		if(!isset($model->Key) || !is_array($model->Key)){
			if(_DEVELOPERIS === true){
				echo '키값이 존재하지 않습니다.';
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#01';
			return $res;
		}
		else if(sizeof($keyData) != sizeof($model->Key)){
			if(_DEVELOPERIS === true){
				echo '모델의 키의 길이와 인자값의 키의 길이가 동일하지 않습니다.';
				exit;
			}

			$res->result = false;
			$res->message = 'ERROR#02';
			return $res;
		}

		if(!sizeof($model->Key)){
			$res->result = false;
			$res->message = 'ERROR#03';
			return $res;
		}

		if($withFile){
			$res = self::DBGet($model, $keyData);
			foreach($model->data as $data){
				if(self::IsFileType($data->HtmlType) && strlen($data->Value)){
					$p = explode(';', $data->Value);
					foreach($p as $path){
						$pn = explode('*', $path);
						UnlinkImage(_UPLOAD_DIR . $pn[0]);
					}
				}
			}
		}

		$qry = DB::DeleteQryObj($model->table)->SetConnName($model->GetConnName());
		foreach($model->Key as $k => $v){
			$qry->AddWhere($v.' = %s', trim($keyData[$k]));
		}



		$res->result = $qry->Run();
		return $res;
	}

	public static function CheckInt($k, $v){
		if(!strlen($v)){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자값이 비어있습니다.');
				else URLReplace('-1', '['.$k.']숫자값이 비어있습니다.');
			}else return 'ERROR#102';
		}
		$val = ToInt($v);
		if($val != $v){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
				else URLReplace('-1', '['.$k.']숫자가 들아갈 항목에 문자가 들어갈 수 없습니다.');
			}else return 'ERROR#103';
		}
		return true;
	}

	public static function CheckFloat($k, $v){
		if(!strlen($v)){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자값이 비어있습니다.');
				else URLReplace('-1', '['.$k.']숫자값이 비어있습니다.');
			}else return 'ERROR#112';
		}
		$val = ToFloat($v);
		if($val != $v){
			if(_DEVELOPERIS === true){
				if(_AJAXIS === true) JSON(false, '['.$k.']숫자(소수)가 들아갈 항목에 문자가 들어갈 수 없습니다.');
				else URLReplace('-1', '['.$k.']숫자(소수)가 들아갈 항목에 문자가 들어갈 수 없습니다.');
			}else return 'ERROR#113';
		}
		return true;
	}

	public static function SetFileValue(&$model, $key){
		$ext = in_array($model->data[$key]->HtmlType, array(HTMLType::InputImageFile, HTMLType::InputImageFileArray)) ? BH_Application::$SettingData['IMAGE_EXT'] : BH_Application::$SettingData['POSSIBLE_EXT'];
		$value = array();
		if(isset($_FILES[$key])){
			$res = self::FileUploadArray($_FILES[$key], $ext, '/temp/');
			foreach($res as $k => $v){
				if(is_array($v)){
					$value[] = $v;
				}
				else if(is_string($v)){
					$model->data[$key]->ModelErrorMsg = '[' . $model->data[$key]->DisplayName . ']' . $v;
				}
			}
		}

		// 단일 파일, 업로드 시 기존 파일 삭제
		if(in_array($model->data[$key]->HtmlType, array(HTMLType::InputImageFile, HTMLType::InputFileWithName, HTMLType::InputFile))){
			if(isset($model->data[$key]->Value) && strlen($model->data[$key]->Value) && Post('del_file_' . $key) == 'y'){
				$temp = explode('*', $model->data[$key]->Value);
				$model->data[$key]->__deleteFile[]= $temp[0];
				$model->data[$key]->Value = '';
				$model->Need[] = $key;
			}

			if(sizeof($value)){
				$newpath = self::ReservedMoveFile($value[0]['file'], $model->table);

				if(is_string($newpath)){
					$model->data[$key]->__moveFile[]= array('source' => $value[0]['file'], 'dest' => $newpath);
					// 기존 파일
					if(isset($model->data[$key]->Value) && strlen($model->data[$key]->Value)){
						$temp = explode('*', $model->data[$key]->Value);
						$model->data[$key]->__deleteFile[]= $temp[0];
					}

					self::SetValue($model, $key, $newpath . (($model->data[$key]->HtmlType === HTMLType::InputFileWithName) ? '*' . $value[0]['original'] : ''));
				}
				else{
					if($newpath->result === -1){
						$model->data[$key]->ModelErrorMsg = $model->data[$key]->DisplayName . '항목에 ' . $newpath->message;
					}
				}
			}
		}

		// 다중 파일, 선택 파일 삭제
		else if($model->data[$key]->HtmlType == HTMLType::InputImageFileArray){
			$deleteFiles = Post('del_file_' . $key);

			$valuePath = array();
			if(strlen($model->data[$key]->Value)){
				$p = explode(';', $model->data[$key]->Value);
				foreach($p as $k => $v){
					$f = explode('*', $v);
					if(is_array($deleteFiles) && in_array($f[0], $deleteFiles)) $model->data[$key]->__deleteFile[]=  $f[0];
					else $valuePath[] = $f[0];
				}

				foreach($value as $path){
					$newpath = self::ReservedMoveFile($path['file'], $model->table);
					if(is_string($newpath)){
						$model->data[$key]->__moveFile[]= array('source' => $path['file'], 'dest' => $newpath);
						$valuePath[] = $newpath;
					}
					else{
						if($newpath->result === -1){
							$model->data[$key]->ModelErrorMsg = $model->data[$key]->DisplayName . '항목에 ' . $newpath->message;
						}
					}
				}
				self::SetValue($model, $key, implode(';', $valuePath));
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

			if(in_array($ext, BH_Application::$SettingData['noext'])) return _MSG_IMPOSSIBLE_FILE;
			else if(!in_array($ext, BH_Application::$SettingData['POSSIBLE_EXT'])) return _MSG_IMPOSSIBLE_FILE;
			else if($possible_ext && !in_array($ext, $possible_ext)) return _MSG_IMPOSSIBLE_FILE;

			if($files['error'] ===  UPLOAD_ERR_INI_SIZE) return _MSG_FILE_TOO_BIG;
			if($files['error'] !==  UPLOAD_ERR_OK) return _MSG_UPLOAD_ERROR;

			if(!is_dir(_UPLOAD_DIR.$path)) @mkdir(_UPLOAD_DIR.$path, 0777, true);

			$newFileName = '';
			while($newFileName == '' || file_exists(_UPLOAD_DIR.$path.$newFileName.'.'.$ext)) $newFileName = self::RandomFileName();


			copy($files['tmp_name'], _UPLOAD_DIR.$path.$newFileName.'.'.$ext);
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
		else if ($size[2] == 2)
			$source = imagecreatefromjpeg($source);
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

