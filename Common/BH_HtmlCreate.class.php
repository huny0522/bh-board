<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class BH_HtmlCreate
{
	public static function CreateController($ControllerName, $ModelName, $TableName){
		if(_DEVELOPERIS !== true) return;
		$path = _CONTROLLERDIR.'/'.$GLOBALS['_BH_App']->NativeDir.'/'.$ControllerName.'.php';
		$modelPath = _MODELDIR.'/'.$ModelName.'.model.php';
		$text = "<?php

class {$ControllerName}Controller extends \\BH_Controller{
	/** @var {$ModelName} */
	public \$model;
	public function __Init(){
		require _MODELDIR.'/{$ModelName}.model.php';
		if(_DEVELOPERIS === true) \\BH_HtmlCreate::Create('{$ControllerName}', '{$ModelName}');
		\$this->model = new {$ModelName}Model();
	}

	public function Index(){
		\$qry = new \\BH_DB_GetListWithPage(\$this->model->table);
		\$qry->articleCount = 10;
		\$qry->page = \$_GET['page'];
		\$qry->pageUrl = \$this->URLAction().\$this->GetFollowQuery('page');
		\$qry->Run();

		\$this->_View(\$this->model, \$qry);
	}

	public function View(){
		\$this->_ModelSet();
		\$this->_View(\$this->model);
	}

	public function Write(){
		\$this->_View(\$this->model);
	}

	public function Modify(){
		\$this->_ModelSet();
		\$this->Html = 'Write';
		\$this->_View(\$this->model);
	}

	public function PostWrite(){
		\$this->model->SetPostValues();
		\$err = \$this->model->GetErrorMessage();
		if(sizeof(\$err)){
			\$this->_Value['error'] = \$err[0];
			\$this->_View(\$this->model);
			return;
		}
		\$res = \$this->model->DBInsert();
		if(!\$res->result) {
			\$this->_Value['error'] = \$res->message ? \$res->message : 'Query Error';
			\$this->_View(\$this->model);
			return;
		}
		else Redirect(\$this->URLAction().\$this->GetFollowQuery());
	}

	public function PostModify(){
		\$this->_ModelSet();
		\$this->model->SetPostValues();
		\$err = \$this->model->GetErrorMessage();
		if(sizeof(\$err)){
			\$this->_Value['error'] = \$err[0];
			\$this->_View(\$this->model);
			return;
		}
		\$res = \$this->model->DBUpdate();
		if(!\$res->result) {
			\$this->_Value['error'] = \$res->message ? \$res->message : 'Query Error';
			\$this->_View(\$this->model);
			return;
		}
		else Redirect(\$this->URLAction('View/'.\$this->ID).\$this->GetFollowQuery());
	}

	public function PostDelete(){
		\$res = \$this->model->DBDelete(\$this->ID);

		if(\$res->result){
			Redirect(\$this->URLAction('').\$this->GetFollowQuery());
		}
		else{
			Redirect(\$this->URLAction('View/'.\$this->ID).\$this->GetFollowQuery(), \$res->message ? \$res->message : 'Query Error');
		}
	}

	private function _ModelSet(){
		if(!strlen(\$this->ID)) Redirect(-1, _WRONG_CONNECTED);
		\$res = \$this->model->DBGet(\$this->ID);
		if(!\$res->result) Redirect(-1, \$res->message ? \$res->message : _NO_ARTICLE);
	}
}";
		if(!file_exists($path)){
			$path = '/Controller/'.($_POST['sub_dir'] ? $_POST['sub_dir'].'/' : '').$ControllerName.'.php';
			echo '<b>'.$path.'파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">'.($text).'</textarea>';
		}

		$modelText = "<?php

class {$ModelName}Model extends \\BH_Model{

	public function __Init(){
		\$this->table = {$TableName};
	}// __Init

}";
		$modelPath = '/Model/'.$ModelName.'.model.php';
		echo '<br><br><b>'.$modelPath.'파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">'.(self::ModifyModel($modelText)).'</textarea>';
		echo '<br><br><a href="'.$_POST['controller_url'].'">완료</a>';
	}

	public static function ModifyModel($f){
		if(_DEVELOPERIS !== true) return;
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
		}else $TableName = $matches[1];

		if(!$TableName) return;

		$pattern = '/function\s+__Init\s*\(\s*\)\s*\{\s*(.*?)\s*\}\s*\/\/\s*__Init/is';
		preg_match($pattern, $f, $fn_matches);
		$initFuncText = '';
		if(sizeof($fn_matches) > 1 && $fn_matches[1]) $initFuncText = str_replace(chr(9), '', $fn_matches[1]);

		$qry = SqlQuery('DESC '.$TableName);
		$primaryKey = array();
		//$tData = array();
		while($row = SqlFetch($qry)){
			//$tData[$row['Field']] = $row;
			$findIs = preg_match('/\$this\-\>InitModelData\(\s*\''.$row['Field'].'\'/is', $initFuncText, $matches);
			if(!$findIs) $findIs = preg_match('/\$this\-\>data\[\''.$row['Field'].'\'\]\s*=\s*new\s*BH_ModelData/is', $initFuncText);
			if(strtolower($row['Key']) == 'pri') $primaryKey[] = "'{$row['Field']}'";

			if(!$findIs){
				$modelType = 'ModelType::String';
				$htmlType = 'HTMLType::InputText';
				$addOption = '';
				$row['Type'] = strtolower($row['Type']);
				if(strpos($row['Type'], 'int(') !== false){
					$modelType = 'ModelType::Int';
					$addOption .= chr(10).'$this->data[\''.$row['Field'].'\']->DefaultValue = '.(int)$row['Default'].';';
				}
				else if(strpos($row['Type'], 'date') !== false){
					$modelType = 'ModelType::Date';
				}
				else if(strpos($row['Type'], 'datetime') !== false){
					$modelType = 'ModelType::Datetime';
				}
				else if(strpos($row['Type'], 'enum(') !== false){
					$modelType = 'ModelType::Enum';
					$htmlType = 'HTMLType::Select';
					preg_match('/\((.*?)\)/', $row['Type'], $matches);
					$enum = explode(',', $matches[1]);
					$enum_t = array();
					foreach($enum as $v){
						$enum_t[]= $v.' => '.$v;
					}
					$addOption .= chr(10).'$this->data[\''.$row['Field'].'\']->EnumValues = array('.implode(',', $enum_t).');';
					$addOption .= chr(10).'$this->data[\''.$row['Field'].'\']->DefaultValue = \''.$row['Default'].'\';';
				}
				else if(strpos($row['Type'], 'varchar(') !== false){
					preg_match('/\(([0-9]*?)\)/', $row['Type'], $matches);
					$addOption .= chr(10).'$this->data[\''.$row['Field'].'\']->MaxLength = \''.$matches[1].'\';';
				}
				else if(strpos($row['Type'], 'text') !== false){
					$htmlType = 'HTMLType::Textarea';
				}

				$initFuncText .= chr(10).chr(10).'$this->InitModelData(\''.$row['Field'].'\', '.$modelType.', false, \''.$row['Field'].'\', '.$htmlType.');'.$addOption;
			}

		}

		$pattern = '/\$this\-\>Key\s*=\s*array/i';
		preg_match($pattern, $initFuncText, $matches);
		if(!sizeof($matches)){
			$initFuncText = '$this->Key = array('.implode(',', $primaryKey).');'.chr(10).$initFuncText;
		}

		$initFuncText = str_replace(chr(11), '', $initFuncText);
		$initFuncText = str_replace(chr(10), chr(10).chr(9).chr(9), $initFuncText);
		$pattern = '/function\s+__Init\s*\(\s*\)\s*\{\s*(.*?)\s*\}\s*\/\/\s*__Init/is';
		$res = preg_replace($pattern, 'function __Init(){'.chr(10).chr(9).chr(9).$initFuncText.chr(10).chr(9).'} // __Init', $f);
		//echo $initFuncText;



		//file_put_contents($modelPath, $res);
		return $res;
		//print_r($tData);

		//echo $initFuncText;
	}

	/**
	 * @param $path : HTML 이 위치할 패스
	 * @param $model : 모델명(모델명이 TestModel 일 경우 Test)
	 */
	public static function Create($path, $model){
		/**
		 * @var $_BH_App BH_Application
		 */

		if(_DEVELOPERIS !== true) return;
		$path = '/'.$path;
		if($GLOBALS['_BH_App']->NativeDir) $path = '/'.$GLOBALS['_BH_App']->NativeDir.$path;
		if(file_exists(_SKINDIR.$path) && is_dir(_SKINDIR.$path)) return;

		$IndexHtml = self::Index($path.'/Index.html', $model);
		$ViewHtml = self::View($path.'/View.html', $model);
		$WriteHtml = self::Write($path.'/Write.html', $model);
		$path = _SKINURL.'/'.($GLOBALS['_BH_App']->NativeDir ? $GLOBALS['_BH_App']->NativeDir.'/' : '').$GLOBALS['_BH_App']->Controller.'/';
		echo '<b>'.$path.'Index.html 파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">'.(GetDBText($IndexHtml)).'</textarea>';
		echo '<br><br>';
		echo '<b>'.$path.'View.html 파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">'.(GetDBText($ViewHtml)).'</textarea>';
		echo '<br><br>';
		echo '<b>'.$path.'Write.html 파일에 아래 코드를 삽입하세요.</b><br><textarea cols="200" rows="30">'.(GetDBText($WriteHtml)).'</textarea>';
		echo '<br><br>';
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

		require_once _DIR . '/Model/' . $model . '.model.php';
		$classname = $model . 'Model';
		/** @var Model $modelClass */
		$modelClass = new $classname();
		if(!file_exists(_SKINDIR . $path)){

			$a = explode('/', _SKINDIR .$path);
			$filename = array_pop($a);
			$path2 = implode('/', $a).'/';

			//if(!is_dir($path2)) mkdir($path2, 0777, true);

			$html = '<?php if(_BH_ !== true) exit;' . chr(10) . '/**'.chr(10).'* @var $Model '.$classname.chr(10).' */' . chr(10) .'/**'.chr(10).'* @var $this BH_Controller'.chr(10).'*/' . chr(10) . '?>' . chr(10) . chr(10) . '<table class="view">' . chr(10);
			foreach($modelClass->data as $k => $row){

				$html .= '<tr>' . chr(10)
					. '	<th><?mt(\'' . $k . '\') ?></th>' . chr(10);
				if(isset($row->EnumValues) && is_array($row->EnumValues)) $html .= '	<td><?menum(\''.$k.'\') ?></td>'. chr(10);
				else $html .= '	<td>'.chr(10). '		<?mv(\'' . $k . '\') ?>'. chr(10) . '	</td>'.chr(10);
				$html .= '</tr>' . chr(10);
			}
			$html .= '</table>' . chr(10);
			$html .= '<div class="bottomBtn"><a href="<?a. \'\' ?><?fq. \'\' ?>" class="btn1">리스트</a><a href="<?a. \'Modify/\'.$this->ID ?><?fq. \'\' ?>" class="btn1">수정</a><a href="#" id="deleteArticle" class="btn1">삭제</a><a href="#" class="backbtn btn1">뒤로</a></div>' . chr(10);
			$html .= '<div id="deleteForm" class="hidden">'. chr(10)
				. chr(9).'<form id="delForm" name="delForm" method="post" action="<?a. \'Delete/\'.$this->ID ?><?fq. \'\' ?>">'. chr(10);

			$html .= chr(9). chr(9).'<p>정말 삭제하시겠습니까?</p>'.chr(10)
				. chr(9). chr(9).'<div class="sPopBtns">' . chr(10)
				. chr(9). chr(9).chr(9).'<button type="submit" class="btn2">삭제하기</button>' . chr(10)
				. chr(9). chr(9).chr(9).'<button type="reset" class="btn2">취소</button>' . chr(10)
				. chr(9).chr(9).'</div>'. chr(10)
				. chr(9).'</form>'. chr(10)
				.'</div>'. chr(10);
			$html .= '<script>'
				.chr(9).'$(\'#deleteArticle\').on(\'click\', function(e){' . chr(10)
				.chr(9).chr(9).'e.preventDefault();' . chr(10)
				.chr(9).chr(9).'$(\'#deleteForm\').show();' . chr(10)
				.chr(9).'});' . chr(10)
				.chr(9).'$(\'#deleteForm button[type=reset]\').on(\'click\', function(e){' . chr(10)
				.chr(9).chr(9).'e.preventDefault();' . chr(10)
				.chr(9).chr(9).'$(\'#deleteForm\').hide();' . chr(10)
				.chr(9).'});' . chr(10)
				.'</script>';
			return $html;
			/*file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);*/
		}
	}

	public static function Write($path, $model){
		if(_DEVELOPERIS !== true) return;

		require_once _DIR . '/Model/' . $model . '.model.php';
		$classname = $model . 'Model';
		/** @var Model $modelClass */
		$modelClass = new $classname();
		if(!file_exists(_SKINDIR . $path)){

			$a = explode('/', _SKINDIR .$path);
			$filename = array_pop($a);
			$path2 = implode('/', $a).'/';

			//if(!is_dir($path2)) mkdir($path2, 0777, true);


			$html = '<?php if(_BH_ !== true) exit;' . chr(10) .'/**'.chr(10).'* @var $Model '.$classname.chr(10).' */' . chr(10) . '/**'.chr(10).'* @var $this BH_Controller'.chr(10).'*/' . chr(10) .'?>' . chr(10) . chr(10);
			$html .= '<form name="'.$model.'WriteForm" id="'.$model.'WriteForm" method="post" action="<?a. $this->Action.\'/\'.$this->ID ?><?fq. \'\' ?>">'. chr(10);

			$html .= chr(10).'	<table class="write">' . chr(10);
			foreach($modelClass->data as $k => $row){
				$html .= '		<tr>' . chr(10)
					. '			<th>';
				if($row->Required) $html .= '<i class="requiredBullet" title="필수항목">*</i> ';
				$html .= '<?mt(\'' . $k . '\') ?></th>' . chr(10);
				$html .= '			<td>' . chr(10);
				$html .= '				<?minp(\'' . $k . '\') ?>' . chr(10);
				$guide = '';
				if($row->MaxLength !== false){
					$guide .= '					<li>';
					if($row->MinLength !== false) $guide .= $row->MinLength.'자 이상, ';
					$guide .= $row->MaxLength.'자 이하로 입력하여주세요.</li>'.chr(10);
				}else if($row->MinLength !== false){
					$guide .= '					<li>'.$row->MinLength.'자 이상 입력하여주세요.</li>'.chr(10);
				}
				if($row->MaxValue !== false){
					$guide .= '					<li>';
					if($row->MaxValue !== false) $guide .= $row->MinValue.' 이상, ';
					$guide .= $row->MaxValue.' 이하의 값을 입력하여주세요.</li>'.chr(10);
				}else if($row->MinValue !== false){
					$guide .= '					<li>'.$row->MinValue.' 이상의 값을 입력하여주세요.</li>'.chr(10);
				}
				if($row->Type == ModelType::Eng){
					$guide .= '					<li>영문만 입력하여 주세요.</li>'.chr(10);
				}
				if($row->Type == ModelType::EngNum){
					$guide .= '					<li>영문과 숫자만 입력하여 주세요.</li>'.chr(10);
				}
				if($guide) $html .= '				<ul class="guide">' . chr(10).$guide.'				</ul>'.chr(10);

				$html .= '			</td>' . chr(10)
					. '		</tr>' . chr(10);
			}
			$html .= '	</table>' . chr(10) . chr(10);
			$html .= '	<div class="bottomBtn">' . chr(10)
				.'		<button type="submit" class="btn1"><?php echo $this->Action == \'Modify\' ? \'수정\' : \'등록\'; ?></button>' . chr(10)
				.'		<button type="reset" class="btn1">취소</button>' . chr(10)
				.'		<a href="#" class="backbtn btn1">뒤로</a>'.chr(10)
				.'	</div>' . chr(10);
			$html .= '</form>' . chr(10). chr(10);
			$html .= chr(60).'script>' . chr(10)
				. '	$(document).on(\'submit\', \'#'.$model.'WriteForm\', function(e){' . chr(10)
				. '		var res = common.valCHeck(this);' . chr(10)
				. '		if(!res){' . chr(10)
				. '			e.preventDefault(); ' . chr(10)
				. '			return false; ' . chr(10)
				. '		} ' . chr(10)
				. '	});' . chr(10)
				. '</script>' . chr(10);
			return $html;
			/*file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);*/
		}
	}

	public static function Index($path, $model){
		if(_DEVELOPERIS !== true) return;

		require_once _DIR . '/Model/' . $model . '.model.php';
		$classname = $model . 'Model';
		/** @var BH_Model $modelClass */
		$modelClass = new $classname();
		if(!file_exists(_SKINDIR . $path)){

			$a = explode('/', _SKINDIR .$path);
			$filename = array_pop($a);
			$path2 = implode('/', $a).'/';

			//if(!is_dir($path2)) mkdir($path2, 0777, true);

			//키값
			$html = '<?php if(_BH_ !== true) exit;' . chr(10) . '/**'.chr(10).'* @var $Model '.$classname.chr(10).' */' . chr(10) . '/**'.chr(10).'* @var $Data BH_DB_GetListWithPage'.chr(10).'*/' . chr(10) . '/**'.chr(10).'* @var $this BH_Controller'.chr(10).'*/' . chr(10) . '?>' . chr(10) . chr(10);
			$html .= '<?php if($Data->result && $Data->totalRecord){ ?>'. chr(10);
			$html .= '<table class="list">'.chr(10);
			$html .= '<thead>'. chr(10);
			$html .= '<tr>' . chr(10);
			$n = 0;
			$html .= '	<th>번호</th>' . chr(10);
			foreach($modelClass->data as $k => $row){
				$html .= '	<th><?mt(\'' . $k . '\') ?></th>' . chr(10);
				$n ++;
			}
			$html .= '	<th></th>'. chr(10);
			$html .= '</tr>'. chr(10);
			$html .= '</thead>'. chr(10);
			$html .= '<tbody>'. chr(10);
			$html .= '<?php while($row = $Data->Get()){?>'. chr(10);
			$html .= '<tr>'. chr(10);
			$html .= '	<td><?p. $Data->beginNum-- ?></td>'. chr(10);
			foreach($modelClass->data as $k => $row){
				if(isset($row->EnumValues) && is_array($row->EnumValues)) $html .= '	<td><?menum(\''.$k.'\', $row[\''.$k.'\']) ?></td>'. chr(10);
				else $html .= '	<td><?v. $row[\''.$k.'\']; ?></td>'. chr(10);
			}
			$keys = array();
			foreach($modelClass->Key as $k){
				$keys[] = '$row[\''.$k.'\']';
			}
			$html .= '	<td><a href="<?a. \'View/\'.'.implode('.\'/\'.', $keys).' ?><?fn. \'\' ?>">상세보기</a></td>'. chr(10);

			$html .= '</tr>'. chr(10);
			$html .= '<?php } ?>'. chr(10);
			$html .= '</tbody>'. chr(10);
			$html .= '</table>'.chr(10). chr(10);
			$html .= '<?php } else{ ?>'. chr(10);
			$html .= '<p class="nothing">등록된 게시물이 없습니다.</p>'. chr(10);
			$html .= '<?php } ?>'. chr(10);
			$html .= '<div class="left_btn"><a href="<?a. \'Write\' ?><?fq. \'\' ?>" class="btn2">글쓰기</a></div>'. chr(10);
			$html .= '<div class="paging"><?p. $Data->pageHtml ?></div>'. chr(10);
			return $html;
			/*file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);*/
		}
	}
}

