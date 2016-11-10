<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class BH_HtmlCreate
{

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
		if($GLOBALS['_BH_App']->SubDir) $path = '/'.$GLOBALS['_BH_App']->SubDir.$path;

		self::Index($path.'/Index.html', $model);
		self::View($path.'/View.html', $model);
		self::Write($path.'/Write.html', $model);
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

			if(!is_dir($path2)) mkdir($path2, 0777, true);

			$Key = array();
			foreach($modelClass->Key as $k){
				$Key[] = $k.'=<?v. $_GET[\''.$k.'\'] ?>';
			}

			$DeleteKey = array();
			foreach($modelClass->Key as $k){
				$DeleteKey[] = ' data-'.$k.'="<?v. $_GET[\''.$k.'\'] ?>"';
			}

			$html = '<?php if(_BH_ !== true) exit;' . chr(10) . '/**'.chr(10).'* @var $Model '.$classname.chr(10).' */' . chr(10) .'/**'.chr(10).'* @var $this BH_Controller'.chr(10).'*/' . chr(10) . '?>' . chr(10) . chr(10) . '<table class="view">' . chr(10);
			foreach($modelClass->data as $k => $row){

				$html .= '<tr>' . chr(10)
					. '	<th><?p. $Model->data[\'' . $k . '\']->DisplayName ?></th>' . chr(10);
				if(isset($row->EnumValues) && is_array($row->EnumValues)) $html .= '	<td><?p. $Model->HTMLPrintEnum(\''.$k.'\', $Model->GetValue(\''.$k.'\')) ?></td>'. chr(10);
				else $html .= '	<td>'.chr(10). '		<?v. $Model->GetValue(\'' . $k . '\') ?>'. chr(10) . '	</td>'.chr(10);
				$html .= '</tr>' . chr(10);
			}
			$html .= '</table>' . chr(10);
			$html .= '<div class="bottomBtn"><a href="<?a. \'\' ?><?fq. \'\' ?>" class="btn1">리스트</a><a href="<?a. \'Modify\' ?>'.(sizeof($Key) ? '?'.implode('&amp;', $Key).'<?fn. \'\' ?>' : '<?fq. \'\' ?>').'" class="btn1">수정</a><a href="#" id="deleteArticle"'.(sizeof($DeleteKey) ? implode($DeleteKey) : '').' class="btn1">삭제</a><a href="#" class="backbtn btn1">뒤로</a></div>' . chr(10);
			$html .= '<div id="deleteForm" class="hidden">'. chr(10)
				. chr(9).'<form id="delForm" name="delForm" method="post" action="<?a. \'Delete\' ?><?fq. \'\' ?>">'. chr(10);

			foreach($modelClass->Key as $k){
				$html .= chr(9). chr(9).'<input type="hidden" name="'.$k.'" value="<?v. $_GET[\''.$k.'\'] ?>">'. chr(10);
			}

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
			file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);
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

			if(!is_dir($path2)) mkdir($path2, 0777, true);


			$html = '<?php if(_BH_ !== true) exit;' . chr(10) .'/**'.chr(10).'* @var $Model '.$classname.chr(10).' */' . chr(10) . '/**'.chr(10).'* @var $this BH_Controller'.chr(10).'*/' . chr(10) .'?>' . chr(10) . chr(10);
			$html .= '<form name="'.$model.'WriteForm" id="'.$model.'WriteForm" method="post" action="<?a. $this->Action ?><?fq. \'\' ?>">'. chr(10);

			$html .= chr(10).'	<table class="write">' . chr(10);
			foreach($modelClass->data as $k => $row){
				$html .= '		<tr>' . chr(10)
					. '			<th>';
				if($row->Required) $html .= '<i class="requiredBullet" title="필수항목">*</i> ';
				$html .= '<?p. $Model->data[\'' . $k . '\']->DisplayName ?></th>' . chr(10);
				$html .= '			<td>' . chr(10);
				$html .= '				<?p. $Model->HTMLPrintInput(\'' . $k . '\') ?>' . chr(10);
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
				if($row->Type == ModelTypeEng){
					$guide .= '					<li>영문만 입력하여 주세요.</li>'.chr(10);
				}
				if($row->Type == ModelTypeEngNum){
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
			file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);
		}
	}

	public static function Index($path, $model){
		if(_DEVELOPERIS !== true) return;

		require_once _DIR . '/Model/' . $model . '.model.php';
		$classname = $model . 'Model';
		/** @var Model $modelClass */
		$modelClass = new $classname();
		if(!file_exists(_SKINDIR . $path)){

			$a = explode('/', _SKINDIR .$path);
			$filename = array_pop($a);
			$path2 = implode('/', $a).'/';

			if(!is_dir($path2)) mkdir($path2, 0777, true);

			//키값
			$addUrl = '';
			foreach($modelClass->Key as $v){
				$addUrl .= ($addUrl ? '&' : '?').$v.'=<?p. $row[\''.$v.'\'] ?>';
			}

			$html = '<?php if(_BH_ !== true) exit;' . chr(10) . '/**'.chr(10).'* @var $Model '.$classname.chr(10).' */' . chr(10) . '/**'.chr(10).'* @var $Data BH_DB_GetListWithPage'.chr(10).'*/' . chr(10) . '/**'.chr(10).'* @var $this BH_Controller'.chr(10).'*/' . chr(10) . '?>' . chr(10) . chr(10);
			$html .= '<table class="list">'.chr(10);
			$html .= '<thead>'. chr(10);
			$html .= '<tr>' . chr(10);
			$n = 0;
			$html .= '	<th>번호</th>' . chr(10);
			foreach($modelClass->data as $k => $row){
				$html .= '	<th><?p. $Model->data[\'' . $k . '\']->DisplayName ?></th>' . chr(10);
				$n ++;
			}
			$html .= '	<th></th>'. chr(10);
			$html .= '</tr>'. chr(10);
			$html .= '</thead>'. chr(10);
			$html .= '<tbody>'. chr(10);
			$html .= '<?php if($Data->result && $Data->totalRecord){ ?>'. chr(10);
			$html .= '<?php while($row = $Data->Get()){?>'. chr(10);
			$html .= '<tr>'. chr(10);
			$html .= '	<td><?p. $Data->beginNum-- ?></td>'. chr(10);
			foreach($modelClass->data as $k => $row){
				if(isset($row->EnumValues) && is_array($row->EnumValues)) $html .= '	<td><?p. $Model->HTMLPrintEnum(\''.$k.'\', $row[\''.$k.'\']) ?></td>'. chr(10);
			else $html .= '	<td><?v. $row[\''.$k.'\']; ?></td>'. chr(10);
			}
			$html .= '	<td><a href="<?a. \'View\' ?>'.$addUrl.'<?fn. \'\' ?>">상세보기</a></td>'. chr(10);

			$html .= '</tr>'. chr(10);
			$html .= '<?php } ?>'. chr(10);
			$html .= '<?php } else{ ?>'. chr(10);
			$html .= '<tr><td colspan="' . $n . '" class="nothing">등록된 게시물이 없습니다.</td></tr>'. chr(10);
			$html .= '<?php } ?>'. chr(10);
			$html .= '</tbody>'. chr(10);
			$html .= '</table>'.chr(10). chr(10);
			$html .= '<div class="left_btn"><a href="<?a. \'Write\' ?><?fq. \'\' ?>" class="btn2">글쓰기</a></div>'. chr(10);
			$html .= '<div class="paging"><?p. $Data->pageHtml ?></div>'. chr(10);
			file_put_contents(_SKINDIR . $path, $html);
			ReplaceHTMLFile(_SKINDIR . $path, _HTMLDIR . $path);
		}
	}
}

