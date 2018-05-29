<?php
/**
 * Bang Hun.
 * 16.07.10
 */

use \BH_Common as CM;
use \BH_Application as App;

class BH_HtmlCreate
{
	public static function CreateController($ControllerName, $ModelName, $TableName){
		if(_DEVELOPERIS !== true) return;
		$create = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' && _FILE_PUT_GUIDE === true);

		$table = str_replace('\'', '', $TableName);
		if(Post('const') == 'y'){
			if(!defined($TableName)){
				URLRedirect(-1, $TableName . ' 상수가 정의되지 않았습니다.');
			}
			$table = constant($TableName);
		}
		if(!DB::SQL()->TableExists($table)){
			URLRedirect(-1, '테이블이 존재하지 않습니다.');
		}

		$path = _CONTROLLERDIR . (strlen($_POST['sub_dir']) ? '/' . $_POST['sub_dir'] : '') . '/' . $ControllerName . '.php';
		$ndir = strlen($_POST['sub_dir']) ? '\\' . str_replace('/', '\\', $_POST['sub_dir']) : '';

		$ModelValueName = strtolower(substr($ModelName, 0, 1)) . substr($ModelName, 1).'Model';

		$text = "<?php
namespace Controller{$ndir};

use \\BH_Application as App;
use \\BH_Common as CM;
use \\DB as DB;

class {$ControllerName}{
	/* @var \\{$ModelName}Model */
	public \${$ModelValueName};

	public function __construct(){
		\$this->{$ModelValueName} = new \\{$ModelName}Model();
	}

	public function __Init(){
		if(_DEVELOPERIS === true) \\BH_HtmlCreate::Create('{$ControllerName}', '{$ModelName}');
	}

	public function Index(){
		\$this->{$ModelValueName}->GetSetPageListQry()
			->SetArticleCount(10)
			->SetPage(isset(\$_GET['page']) ? \$_GET['page'] : 0)
			->SetPageUrl(App::URLAction().App::GetFollowQuery('page'))
			->Run();

		App::View();
	}

	public function View(){
		\$this->_ModelSet(App::\$ID);
		App::View();
	}

	public function Write(){
		App::View();
	}

	public function Modify(){
		\$this->_ModelSet(App::\$ID);
		App::View('Write');
	}

	public function PostWrite(\$seq = null){
		if(!is_null(\$seq)) \$this->_ModelSet(\$seq);
		\$this->{$ModelValueName}->SetPostValues();
		\$err = \$this->{$ModelValueName}->GetErrorMessage();
		if(sizeof(\$err)){
			App::\$Data['error'] = \$err[0];
			App::View('Write');
			return;
		}
		if(is_null(\$seq)) \$res = \$this->{$ModelValueName}->DBInsert();
		else \$res = \$this->{$ModelValueName}->DBUpdate();
		
		if(!\$res->result) {
			App::\$Data['error'] = \$res->message ? \$res->message : 'Query Error';
			App::View('Write');
			return;
		}
		else{
			if(is_null(\$seq)) URLReplace(App::URLAction().App::GetFollowQuery());
			else URLReplace(App::URLAction('View/'.\$seq).App::GetFollowQuery());
		}
	}

	public function PostModify(){
		\$this->PostWrite(App::\$ID);
	}

	public function PostDelete(){
		\$res = \$this->{$ModelValueName}->DBDelete(App::\$ID);

		if(\$res->result){
			URLReplace(App::URLAction('').App::GetFollowQuery());
		}
		else{
			URLReplace(App::URLAction('View/'.App::\$ID).App::GetFollowQuery(), \$res->message ? \$res->message : 'Query Error');
		}
	}

	private function _ModelSet(\$seq){
		if(!strlen(\$seq)) URLReplace(-1, _MSG_WRONG_CONNECTED);
		\$res = \$this->{$ModelValueName}->DBGet(\$seq);
		if(!\$res->result) URLReplace(-1, \$res->message ? \$res->message : _MSG_NO_ARTICLE);
	}
}";
		if($create){
			file_put_contents($path, $text);
		}
		else{
			if(!file_exists($path)){
				$path = '/Controller/' . ($_POST['sub_dir'] ? $_POST['sub_dir'] . '/' : '') . $ControllerName . '.php';
				echo '<b>' . $path . '파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">' . ($text) . '</textarea>';
			}
		}

		$modelText = "<?php
/**
 * Class {$ModelName}Model
 * @property \BH_ModelData[] \$data
 */
class {$ModelName}Model extends \\BH_Model{

	public function __Init(){
		\$this->table = {$TableName};
	}// __Init

}";

		$modelPath = '/Model/' . $ModelName . 'Model.php';
		if($create){
			file_put_contents(_DIR . $modelPath, self::ModifyModel($modelText));
			URLRedirect($_POST['controller_url']);
		}
		else{
			echo '<br><br><b>' . $modelPath . '파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">' . (self::ModifyModel($modelText)) . '</textarea>';
			echo '<br><br><a href="' . $_POST['controller_url'] . '">완료</a>';
		}
	}

	public static function ModifyModel($f){
		if(_DEVELOPERIS !== true) return;
		$oClass = new ReflectionClass('ModelType');
		$mTypeKeys = array_keys($oClass->getConstants());
		$oClass = new ReflectionClass('HTMLType');
		$hTypeKeys = array_keys($oClass->getConstants());
		/*$modelPath = _MODELDIR.'/'.$ModelName.'.model.php';
		if(!file_exists($modelPath)) $modelPath = _DATADIR.'/'.$ModelName.'.model.php';
		if(!file_exists($modelPath)) exit;
		$f = file_get_contents($modelPath);*/
		$pattern = '/\$this\-\>table\s*=\s*[\'|\"](.*?)[\'|\"]\;/i';
		preg_match($pattern, $f, $matches);
		$TableName = '';
		if(!sizeof($matches) || $matches[1] == ''){
			$pattern = '/\$this\-\>table\s*=\s*(.*?)\;/i';
			preg_match($pattern, $f, $matches);
			if(sizeof($matches) > 1 && $matches[1]) $TableName = constant($matches[1]);
		}
		else $TableName = $matches[1];

		if(!$TableName) return;

		$pattern = '/function\s+__Init\s*\(\s*\)\s*\{\s*(.*?)\s*\}\s*\/\/\s*__Init/is';
		preg_match($pattern, $f, $fn_matches);
		$initFuncText = '';
		if(sizeof($fn_matches) > 1 && $fn_matches[1]) $initFuncText = str_replace(chr(9), '', $fn_matches[1]);

		$qry = \DB::SQL()->Query('SHOW FULL COLUMNS FROM ' . $TableName);
		$primaryKey = array();
		//$tData = array();
		$propertyDoc = '';
		while($row = \DB::SQL()->Fetch($qry)){
			//$tData[$row['Field']] = $row;
			$findIs = preg_match('/\$this\-\>data\[\'' . $row['Field'] . '\'\]\s*=\s*new\s*[\\\]*BH_ModelData/is', $initFuncText);
			$propertyDoc .= " * @property \\BH_ModelData \$_{$row['Field']}\n";
			if(strtolower($row['Key']) == 'pri') $primaryKey[] = "'{$row['Field']}'";

			if(!$findIs){

				$modelType = '';
				$htmlType = '';
				$cmt = '';
				$enumValues = array();

				$comment = explode(';', $row['Comment']);
				foreach($comment as $v){
					$v = trim($v);
					if(in_array($v, $mTypeKeys)) $modelType = 'ModelType::'.$v;
					else if(in_array($v, $hTypeKeys)) $htmlType = ', HTMLType::'.$v;
					else if(substr($v, 0, 1) == '[' && substr($v, -1) == ']'){
						$temp = explode(',', substr($v, 1, -1));
						foreach($temp as $v2){
							$temp2 = explode(':', $v2);
							if(sizeof($temp2) > 1) $enumValues[$temp2[0]] = $temp2[1];
						}
					}
					else $cmt = $v;
				}

				$addOption = '';
				$type = strtolower($row['Type']);
				if(strpos($type, 'int(') !== false){
					if($modelType === '') $modelType = 'ModelType::Int';
					$addOption .= chr(10) . '$this->data[\'' . $row['Field'] . '\']->DefaultValue = ' . (int)$row['Default'] . ';';
				}
				else if(strpos($type, 'date') !== false){
					if($modelType === '') $modelType = 'ModelType::Date';
					if($htmlType === '') $htmlType = ', HTMLType::InputDatePicker';
				}
				else if(strpos($type, 'datetime') !== false){
					if($modelType === '') $modelType = 'ModelType::Datetime';
					if($htmlType === '') $htmlType = ', HTMLType::InputDatePicker';
				}
				else if(strpos($type, 'enum(') !== false){
					if($modelType === '') $modelType = 'ModelType::Enum';
					if($htmlType === '') $htmlType = ', HTMLType::Select';
					preg_match('/\((.*?)\)/', $row['Type'], $matches);
					$enum = explode(',', $matches[1]);
					$enum_t = array();
					foreach($enum as $v){
						$v2 = substr($v, 1, -1);
						$enum_t[] = $v . ' => ' . (isset($enumValues[$v2]) ? '"'.$enumValues[$v2].'"' : $v);
					}
					$addOption .= chr(10) . '$this->data[\'' . $row['Field'] . '\']->EnumValues = array(' . implode(',', $enum_t) . ');';
					$addOption .= chr(10) . '$this->data[\'' . $row['Field'] . '\']->DefaultValue = \'' . $row['Default'] . '\';';
				}
				else if(strpos($type, 'varchar(') !== false){
					preg_match('/\(([0-9]*?)\)/', $type, $matches);
					$addOption .= chr(10) . '$this->data[\'' . $row['Field'] . '\']->MaxLength = \'' . $matches[1] . '\';';
				}
				else if(strpos($row['Type'], 'text') !== false){
					if($htmlType === '') $htmlType = ', HTMLType::Textarea';
				}

				if($modelType === '') $modelType = 'ModelType::String';

				$initFuncText .= chr(10) . chr(10) . '$this->data[\'' . $row['Field'] . '\'] = new \\BH_ModelData(' . $modelType . ', false, \'' . ($cmt ? $cmt : $row['Field']) . '\'' . $htmlType . ');' . $addOption;
			}

		}
		//$f = preg_replace("/\s*\*\s*\[\@property\]\s*\n/is", PHP_EOL . $propertyDoc, $f);

		$pattern = '/\$this\-\>Key\s*=\s*array/i';
		preg_match($pattern, $initFuncText, $matches);
		$pattern = '/\$this\-\>Key\s*\[\s*\]/';
		preg_match($pattern, $initFuncText, $matches2);
		if(!sizeof($matches) && !sizeof($matches2)){
			$initFuncText = '$this->Key = array(' . implode(',', $primaryKey) . ');' . chr(10) . $initFuncText;
		}

		$initFuncText = str_replace(chr(11), '', $initFuncText);
		$initFuncText = str_replace(chr(10), chr(10) . chr(9) . chr(9), $initFuncText);
		$initFuncText = str_replace(array("\t\t\n", "\t\t\r"), array("\n", "\r"), $initFuncText);
		$pattern = '/function\s+__Init\s*\(\s*\)\s*\{\s*(.*?)\s*\}\s*\/\/\s*__Init/is';
		$res = preg_replace($pattern, 'function __Init(){' . chr(10) . chr(9) . chr(9) . $initFuncText . chr(10) . chr(9) . '} // __Init', $f);

		return $res;
	}

	/**
	 * @param $path : HTML 이 위치할 패스
	 * @param $model : 모델명(모델명이 TestModel 일 경우 Test)
	 */
	public static function Create($path, $model){
		if(_DEVELOPERIS !== true) return;
		$create = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' && _FILE_PUT_GUIDE === true);

		$path = '/' . $path;
		if(App::$NativeDir) $path = '/' . App::$NativeDir . $path;
		if(file_exists(_SKINDIR . $path) && is_dir(_SKINDIR . $path)) return;

		$IndexHtml = self::Index($path . '/Index.html', $model);
		$ViewHtml = self::View($path . '/View.html', $model);
		$WriteHtml = self::Write($path . '/Write.html', $model);

		if($create){
			@mkdir(_SKINDIR . $path, 0777, true);
			file_put_contents(_SKINDIR . $path . '/Index.html', $IndexHtml);
			file_put_contents(_SKINDIR . $path . '/View.html', $ViewHtml);
			file_put_contents(_SKINDIR . $path . '/Write.html', $WriteHtml);
			URLReplace(App::URLAction());
		}
		else{
			$path = _SKINURL . '/' . (App::$NativeDir ? App::$NativeDir . '/' : '') . App::$ControllerName . '/';
			echo '<b>' . $path . 'Index.html 파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">' . (GetDBText($IndexHtml)) . '</textarea>';
			echo '<br><br>';
			echo '<b>' . $path . 'View.html 파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">' . (GetDBText($ViewHtml)) . '</textarea>';
			echo '<br><br>';
			echo '<b>' . $path . 'Write.html 파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">' . (GetDBText($WriteHtml)) . '</textarea>';
			echo '<br><br>';
		}
		exit;
	}

	/**
	 * @param string $path
	 * @param string $model
	 *
	 * @return string
	 */
	public static function View($path, $model){
		if(_DEVELOPERIS !== true) return;

		$classname = $model . 'Model';
		/* @var $modelClass Model */
		$modelClass = App::InitModel($model);
		if(!file_exists(_SKINDIR . $path)){

			$a = explode('/', _SKINDIR . $path);
			$filename = array_pop($a);
			$path2 = implode('/', $a) . '/';

			//if(!is_dir($path2)) mkdir($path2, 0777, true);
			$temp = explode('/', $path);
			array_pop($temp);
			$temp = implode('\\', $temp);

			$ModelValueName = strtolower(substr($classname, 0, 1)) . substr($classname, 1);

			$html = "<?php if(_BH_ !== true) exit;
			
use \\BH_Application as App;
use \\BH_Common as CM;

/**
 * @var \\Controller{$temp} \$Ctrl
 * @var \\{$classname} \$Model
 */
 \$Model = &\$Ctrl->{$ModelValueName};
 ?>
 
 ";
			$html .= '<table class="view">' . chr(10);
			foreach($modelClass->data as $k => $row){

				$html .= '<tr>' . chr(10) . '	<th><?mt(\'' . $k . '\') ?></th>' . chr(10);
				if(isset($row->EnumValues) && is_array($row->EnumValues)) $html .= '	<td><?menum(\'' . $k . '\') ?></td>' . chr(10);
				else $html .= '	<td>' . chr(10) . '		<?mv(\'' . $k . '\') ?>' . chr(10) . '	</td>' . chr(10);
				$html .= '</tr>' . chr(10);
			}
			$html .= '</table>' . chr(10);
			$html .= '<div class="bottomBtn"><a href="<?a. \'\' ?><?fq. \'\' ?>" class="bBtn">리스트</a><a href="<?a. \'Modify/\'.App::$ID ?><?fq. \'\' ?>" class="bBtn">수정</a><a href="#" id="deleteArticle" class="bBtn">삭제</a><a href="#" class="backbtn bBtn">뒤로</a></div>' . chr(10);
			$html .= '<div id="deleteForm" class="modalConfirm hidden">' . chr(10) . chr(9) . '<form id="delForm" name="delForm" method="post" action="<?a. \'Delete/\'.App::$ID ?><?fq. \'\' ?>">' . chr(10);

			$html .= chr(9) . chr(9) . '<p>정말 삭제하시겠습니까?</p>' . chr(10) . chr(9) . chr(9) . '<div class="sPopBtns">' . chr(10) . chr(9) . chr(9) . chr(9) . '<button type="submit" class="sBtn btn2">삭제하기</button>' . chr(10) . chr(9) . chr(9) . chr(9) . '<button type="reset" class="sBtn btn2">취소</button>' . chr(10) . chr(9) . chr(9) . '</div>' . chr(10) . chr(9) . '</form>' . chr(10) . '</div>' . chr(10);
			$html .= '<script>' . chr(9) . '$(\'#deleteArticle\').on(\'click\', function(e){' . chr(10) . chr(9) . chr(9) . 'e.preventDefault();' . chr(10) . chr(9) . chr(9) . '$(\'#deleteForm\').show();' . chr(10) . chr(9) . '});' . chr(10) . chr(9) . '$(\'#deleteForm button[type=reset]\').on(\'click\', function(e){' . chr(10) . chr(9) . chr(9) . 'e.preventDefault();' . chr(10) . chr(9) . chr(9) . '$(\'#deleteForm\').hide();' . chr(10) . chr(9) . '});' . chr(10) . '</script>';
			return $html;
			/*file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);*/
		}
	}

	public static function Write($path, $model){
		if(_DEVELOPERIS !== true) return;

		$classname = $model . 'Model';
		/* @var $modelClass BH_Model */
		$modelClass = App::InitModel($model);
		if(!file_exists(_SKINDIR . $path)){

			$a = explode('/', _SKINDIR . $path);
			$filename = array_pop($a);
			$path2 = implode('/', $a) . '/';

			$temp = explode('/', $path);
			array_pop($temp);
			$temp = implode('\\', $temp);

			//if(!is_dir($path2)) mkdir($path2, 0777, true);
			$ModelValueName = strtolower(substr($classname, 0, 1)) . substr($classname, 1);

			$html = "<?php if(_BH_ !== true) exit;

use \\BH_Application as App;
use \\BH_Common as CM;

/**
 * @var \\Controller{$temp} \$Ctrl
 * @var \\{$classname} \$Model
 */
\$Model = &\$Ctrl->{$ModelValueName};
?>

";
			$html .= '<form name="' . $model . 'WriteForm" id="' . $model . 'WriteForm" method="post" action="<?a. App::$Action.\'/\'.App::$ID ?><?fq. \'\' ?>">' . chr(10);

			$html .= chr(10) . '	<table class="write">' . chr(10);
			foreach($modelClass->data as $k => $row){
				$html .= '		<tr>' . chr(10) . '			<th>';
				if($row->Required) $html .= '<i class="requiredBullet" title="필수항목">*</i> ';
				$html .= '<?mt(\'' . $k . '\') ?></th>' . chr(10);
				$html .= '			<td>' . chr(10);
				$html .= '				<?minp(\'' . $k . '\') ?>' . chr(10);
				$guide = '';
				if($row->MaxLength !== false){
					$guide .= '					<li>';
					if($row->MinLength !== false) $guide .= $row->MinLength . '자 이상, ';
					$guide .= $row->MaxLength . '자 이하로 입력하여주세요.</li>' . chr(10);
				}
				else if($row->MinLength !== false){
					$guide .= '					<li>' . $row->MinLength . '자 이상 입력하여주세요.</li>' . chr(10);
				}
				if($row->MaxValue !== false){
					$guide .= '					<li>';
					if($row->MaxValue !== false) $guide .= $row->MinValue . ' 이상, ';
					$guide .= $row->MaxValue . ' 이하의 값을 입력하여주세요.</li>' . chr(10);
				}
				else if($row->MinValue !== false){
					$guide .= '					<li>' . $row->MinValue . ' 이상의 값을 입력하여주세요.</li>' . chr(10);
				}
				if($row->HtmlType == HTMLType::InputEng){
					$guide .= '					<li>영문만 입력하여 주세요.</li>' . chr(10);
				}
				if($row->Type == HTMLType::InputEngNum){
					$guide .= '					<li>영문과 숫자만 입력하여 주세요.</li>' . chr(10);
				}
				if($guide) $html .= '				<ul class="guide">' . chr(10) . $guide . '				</ul>' . chr(10);

				$html .= '			</td>' . chr(10) . '		</tr>' . chr(10);
			}
			$html .= '	</table>' . chr(10) . chr(10);
			$html .= '	<div class="bottomBtn">' . chr(10) . '		<button type="submit" class="bBtn"><?php echo App::$Action == \'Modify\' ? \'수정\' : \'등록\'; ?></button>' . chr(10) . '		<button type="reset" class="bBtn">취소</button>' . chr(10) . '		<a href="#" class="backbtn bBtn">뒤로</a>' . chr(10) . '	</div>' . chr(10);
			$html .= '</form>' . chr(10) . chr(10);
			$html .= chr(60) . 'script>' . chr(10) . '	$(document).on(\'submit\', \'#' . $model . 'WriteForm\', function(e){' . chr(10) . '		var res = $(this).validCheck();' . chr(10) . '		if(!res){' . chr(10) . '			e.preventDefault(); ' . chr(10) . '			return false; ' . chr(10) . '		} ' . chr(10) . '	});' . chr(10) . '</script>' . chr(10);
			return $html;
			/*file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);*/
		}
	}

	public static function Index($path, $model){
		if(_DEVELOPERIS !== true) return;

		$classname = $model . 'Model';
		/* @var $modelClass BH_Model */
		$modelClass = App::InitModel($model);
		if(!file_exists(_SKINDIR . $path)){

			$a = explode('/', _SKINDIR . $path);
			$filename = array_pop($a);
			$path2 = implode('/', $a) . '/';

			$temp = explode('/', $path);
			array_pop($temp);
			$temp = implode('\\', $temp);

			$ModelValueName = strtolower(substr($classname, 0, 1)) . substr($classname, 1);

			//키값
			$html = "<?php if(_BH_ !== true) exit;

use \\BH_Application as App;
use \\BH_Common as CM;

/**
 * @var \\Controller{$temp} \$Ctrl
 * @var \\{$classname} \$Model
 */
\$Model = &\$Ctrl->{$ModelValueName};

/**
 * @var \BH_DB_GetListWithPage \$Data
 */
\$Data = &\$Model->QryObj();
?>

";
			$html .= '<?php if($Data->result && $Data->totalRecord){ ?>' . chr(10);
			$html .= '<table class="list">' . chr(10);
			$html .= '<thead>' . chr(10);
			$html .= '<tr>' . chr(10);
			$n = 0;
			$html .= '	<th>번호</th>' . chr(10);
			foreach($modelClass->data as $k => $row){
				$html .= '	<th><?mt(\'' . $k . '\') ?></th>' . chr(10);
				$n++;
			}
			$html .= '	<th></th>' . chr(10);
			$html .= '</tr>' . chr(10);
			$html .= '</thead>' . chr(10);
			$html .= '<tbody>' . chr(10);
			$html .= '<?php while($row = $Data->Get()){?>' . chr(10);
			$html .= '<tr>' . chr(10);
			$html .= '	<td><?e. $Data->beginNum-- ?></td>' . chr(10);
			foreach($modelClass->data as $k => $row){
				if(isset($row->EnumValues) && is_array($row->EnumValues)) $html .= '	<td><?menum(\'' . $k . '\', $row[\'' . $k . '\']) ?></td>' . chr(10);
				else $html .= '	<td><?v. $row[\'' . $k . '\']; ?></td>' . chr(10);
			}
			$keys = array();
			foreach($modelClass->Key as $k){
				$keys[] = '$row[\'' . $k . '\']';
			}
			$html .= '	<td><a href="<?a. \'View/\'.' . implode('.\'/\'.', $keys) . ' ?><?fn. \'\' ?>">상세보기</a></td>' . chr(10);

			$html .= '</tr>' . chr(10);
			$html .= '<?php } ?>' . chr(10);
			$html .= '</tbody>' . chr(10);
			$html .= '</table>' . chr(10) . chr(10);
			$html .= '<?php } else{ ?>' . chr(10);
			$html .= '<p class="nothing">등록된 게시물이 없습니다.</p>' . chr(10);
			$html .= '<?php } ?>' . chr(10);
			$html .= '<div class="left_btn"><a href="<?a. \'Write\' ?><?fq. \'\' ?>" class="mBtn">글쓰기</a></div>' . chr(10);
			$html .= '<?e. $Data->GetPageHtml() ?>' . chr(10);
			return $html;
			/*file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);*/
		}
	}
}

