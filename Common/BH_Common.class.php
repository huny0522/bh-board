<?php
use \BH_Application as App;
use \DB as DB;
class BH_Common
{
	public static $Member;

	private function __construct(){
	}

	public static function AdminAuth($redirectUrl = ''){
		if($redirectUrl === '') $redirectUrl = App::URLBase('Login');
		if(_MEMBERIS !== true || ($_SESSION['member']['level'] != _SADMIN_LEVEL  && $_SESSION['member']['level'] != _ADMIN_LEVEL)){
			if(_AJAXIS === true) JSON(false, _MSG_NO_AUTH.' 로그인하여 주세요.');
			else URLReplace($redirectUrl, _MSG_NO_AUTH.' 로그인하여 주세요.');
		}
		if($_SESSION['member']['level'] == _ADMIN_LEVEL){
			$AdminAuth = explode(',', self::GetMember('admin_auth'));
			if(!in_array(App::$Data['NowMenu'], $AdminAuth)){
				if(_AJAXIS === true) JSON(false, _MSG_NO_AUTH);
				else URLReplace('-1', _MSG_NO_AUTH);
			}
		}
	}

	public static function GetAdminIs(){
		if(_MEMBERIS === true && ($_SESSION['member']['level'] == _SADMIN_LEVEL  || $_SESSION['member']['level'] == _ADMIN_LEVEL)) return true;
		return false;
	}

	public static function MemberAuth($level = 1){
		if(_MEMBERIS !== true){
			if(_AJAXIS === true) JSON(false, _MSG_NO_AUTH.' 로그인하여 주세요.');
			else URLReplace(App::URLBase('Login'), _MSG_NO_AUTH.' 로그인하여 주세요.');
		}
		if($_SESSION['member']['level'] < $level){
			if(_AJAXIS === true) JSON(false, _MSG_NO_AUTH);
			else URLReplace('-1', _MSG_NO_AUTH);
		}
	}

	/**
	 * @param string $key
	 * @return array|bool|null
	 */
	public static function GetMember($key = ''){
		// 원글 가져오기
		if(_MEMBERIS === true){
			if(!isset(self::$Member) || !self::$Member){
				$dbGet = new \BH_DB_Get(TABLE_MEMBER);
				$dbGet->AddWhere('muid=' . SetDBInt($_SESSION['member']['muid']));
				$dbGet->SetKey('*', 'NULL as pwd');
				self::$Member = $dbGet->Get();
			}
			if($key) return self::$Member[$key];
			return self::$Member;
		}else{
			return false;
		}
	}

	public static function Config($code, $key){
		// 설정불러오기
		if(!isset(App::$CFG[$code])){
			$path = _DATADIR.'/CFG/'.$code.'.php';
			if(file_exists($path)){
				require_once $path;
			}else App::$CFG[$code] = array();
		}
		return isset(App::$CFG[$code][$key]) ? App::$CFG[$code][$key] : null;
	}

	public static function SetConfig($code, $key, $val){
		$res = new \BH_Result();
		if(_DEVELOPERIS !== true){
			$res->result = false;
			$res->message = _MSG_WRONG_CONNECTED;
			return;
		}

		$dirPath = _DATADIR.'/CFG';
		if(!file_exists($dirPath) && !is_dir($dirPath)) mkdir($dirPath, 0700, true);

		if(!isset(App::$CFG[$code])){
			$path = _DATADIR.'/CFG/'.$code.'.php';
			if(file_exists($path)){
				require_once $path;
			}else App::$CFG[$code] = array();
		}

		App::$CFG[$code][$key] = $val;
		$path = _DATADIR.'/CFG/'.$code.'.php';
		$txt = '<?php \BH_Application::$CFG[\''.$code.'\'] = '.var_export(App::$CFG[$code], true).';';
		file_put_contents($path, $txt);
		$res->result = true;
		return $res;
	}

	public static function RefreshParam($beginMark = '?'){
		return $beginMark.'r='.self::Config('Refresh', 'Refresh');
	}

	// 이미지 등록
	public static function ContentImageUpate($tid, $keyValue, $content, $mode = 'write'){
		$newcontent = $content['contents'];
		$maxImage = _MAX_IMAGE_COUNT;
		$dbKeyValue = implode('|',$keyValue);

		if($mode == 'modify'){
			$dbGetList = new \BH_DB_GetList(TABLE_IMAGES);
			$dbGetList->AddWhere('tid=%s', $tid);
			$dbGetList->AddWhere('article_seq = %s', $dbKeyValue);
			while($img = $dbGetList->Get()){
				if(strpos($content['contents'], $img['image']) === false){
					// 파일이 없으면 삭제
					@unlink(_UPLOAD_DIR.$img['image']);
					$qry = new \BH_DB_Delete(TABLE_IMAGES);
					$qry->AddWhere('tid = %s', $tid);
					$qry->AddWhere('article_seq = %s', $dbKeyValue);
					$qry->AddWhere('seq = %d', $img['seq']);
					$qry->Run();
				}
			}
		}

		$dbGet = new \BH_DB_Get(TABLE_IMAGES);
		$dbGet->AddWhere('tid = %s', $tid);
		$dbGet->SetKey('COUNT(*) as cnt');
		$cnt = $dbGet->Get();
		$imageCount = $cnt['cnt'];

		if(isset($_POST['addimg']) && is_array($_POST['addimg'])){
			$ym = date('ym');
			foreach($_POST['addimg'] as $img){
				$exp = explode('|', $img);

				if(strpos($content['contents'], $exp[0]) !== false){
					if($imageCount >= $maxImage){
						@unlink(_UPLOAD_DIR.$exp[0]);
						continue;
					}

					$newpath = str_replace('/temp/', '/image/'.$ym.'/', $exp[0]);
					$uploadDir = _UPLOAD_DIR.'/image/'.$ym;
					if(!is_dir($uploadDir)){
						mkdir($uploadDir, 0777, true);
					}
					@copy(_UPLOAD_DIR.$exp[0],_UPLOAD_DIR.$newpath);
					$newcontent = str_replace($exp[0],$newpath, $newcontent);
					// 파일이 있으면 등록

					unset($dbInsert);
					$dbInsert = new \BH_DB_Insert(TABLE_IMAGES);
					$dbInsert->SetDataStr('tid', $tid);
					$dbInsert->SetDataStr('article_seq', $dbKeyValue);
					$dbInsert->SetDataStr('image', $newpath);
					$dbInsert->SetDataStr('imagename', $exp[1]);
					$dbInsert->decrement = 'seq';
					$dbInsert->AddWhere('tid = %s', $tid);
					$dbInsert->AddWhere('article_seq = %s', $dbKeyValue);
					//$params['test'] = true;
					$dbInsert->Run();
					$imageCount++;
				}
				@unlink(_DIR.$exp[0]);
			}

			if($newcontent != $content['contents']){
				$qry = new \BH_DB_Update($tid);
				$qry->SetDataStr($content['name'], $newcontent);
				foreach($keyValue as $k=>$v){
					$qry->AddWhere('%1 = %s', $k, $v);
				}
				$qry->Run();
			}
		}

		DeleteOldTempFiles(_UPLOAD_DIR.'/temp/', strtotime('-6 hours'));
		return true;
	}

	// 메뉴 카테고리와 컨텐츠를 연결
	public static function MenuConnect($bid, $type){
		$AdminAuth = explode(',', self::GetMember('admin_auth'));
		if(in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL){
			if(strlen($_POST['select_menu'])){
				$selectmenu = explode(',', $_POST['select_menu']);
				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('category IN (%s)', $selectmenu);
				$mUpdate->SetDataStr('type', $type);
				$mUpdate->SetDataStr('bid', $bid);
				$mUpdate->Run();

				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('bid = %s', $bid);
				$mUpdate->AddWhere('category NOT IN (%s)', $selectmenu);
				$mUpdate->AddWhere('type = %s', $type);
				$mUpdate->SetDataStr('type', 'customize');
				$mUpdate->SetDataStr('bid', '');
				$mUpdate->Run();

			}else{
				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('bid = %s', $bid);
				$mUpdate->AddWhere('type = %s', $type);
				$mUpdate->SetDataStr('type', 'customize');
				$mUpdate->SetDataStr('bid', '');
				$mUpdate->Run();
			}
		}
	}

	// 게시물 가져오기
	public static function GetBoardArticle($bid, $category = '', $limit = 10){
		// 리스트를 불러온다.
		$dbList = new \BH_DB_GetList(TABLE_FIRST.'bbs_'.$bid);
		$dbList->AddWhere('delis=\'n\'');
		$n = func_num_args();
		if($n > 3){
			$args = func_get_args();
			for($i = 3; $i < $n; $i++) $dbList->AddWhere($args[$i]);
		}
		$dbList->sort = 'sort1, sort2';
		$dbList->limit = $limit;
		if(strlen($category)){
			$dbList->AddWhere('category = %s', $category);
		}
		return $dbList;
	}

	/**
	 * 배너 가져오기
	 * @param string $category
	 * @param int $number
	 * @param string $sort
	 * @return array
	 */
	public static function GetBanner($category, $number = 5, $sort = 'sort DESC, seq ASC'){
		$banner = new \BH_DB_GetList(TABLE_BANNER);
		$banner->AddWhere('begin_date <= \''.date('Y-m-d').'\'')
			->AddWhere('end_date >= \''.date('Y-m-d').'\'')
			->AddWhere('enabled = \'y\'')
			->AddWhere('category = %s', $category)
			->SetSort($sort)
			->SetLimit($number);

		$mlevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		$banner->AddWhere('mlevel <= '.$mlevel)
			->DrawRows();

		foreach($banner->data as $k => $row){
			$html = '';
			if($row['link_url']) $html .= '<a href="'.$row['link_url'].'"'.($row['new_window'] == 'y' ? ' target="_blank"' : '').'>';
			$html .= '<img src="'._UPLOAD_URL.$row['img'].'" alt="'.GetDBText($row['subject']).'">';
			if($row['link_url']) $html .= '</a>';
			$banner->data[$k]['html'] = $html;
		}

		return $banner->data;
	}

	/**
	 * 팝업 가져오기
	 * @param int $number
	 * @param string $sort
	 * @return array
	 */
	public static function GetPopup($number = 5, $sort = 'sort DESC, seq ASC'){

		$banner = new \BH_DB_GetList(TABLE_POPUP);
		$banner->AddWhere('begin_date <= \''.date('Y-m-d').'\'')
			->AddWhere('end_date >= \''.date('Y-m-d').'\'')
			->AddWhere('enabled = \'y\'')
			->SetSort($sort)
			->SetLimit($number);

		$mlevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		$banner->AddWhere('mlevel <= '.$mlevel)
			->DrawRows();


		foreach($banner->data as $k => $row){
			if($row['type'] == 'i'){
				$html = '';
				if($row['link_url']) $html .= '<a href="'.$row['link_url'].'"'.($row['new_window'] == 'y' ? ' target="_blank"' : '').'>';
				$html .= '<img src="'._UPLOAD_URL.$row['img'].'" alt="'.GetDBText($row['subject']).'">';
				if($row['link_url']) $html .= '</a>';
			}
			else $html = GetDBRaw(addslashes($row['contents']));

			$banner->data[$k]['html'] = $html;
		}
		return $banner->data;
	}

	/* -------------------------------------------------
	 *
	 *       Category
	 *
	------------------------------------------------- */
	public static function _CategoryGetChild($table, $parent, $length){
		$dbGet = new \BH_DB_GetList($table);
		$dbGet->AddWhere('LEFT(category, %d) = %s', strlen($parent), $parent);
		$dbGet->AddWhere('LENGTH(category) = '.(strlen($parent) + $length));
		$dbGet->sort = 'sort';
		return $dbGet->GetRows();
	}

	public static function _CategorySetChildEnable($table, $parent, $enabled){
		if(is_null($parent)) return;

		$dbUpdate = new \BH_DB_Update($table);
		$dbUpdate->AddWhere('LEFT(category, %d) = %s', strlen($parent), $parent);
		$dbUpdate->SetDataStr('parent_enabled', $enabled);
		$dbUpdate->Run();
	}

	public static function _CategoryGetParent($table, $category, $length){
		if(is_null($category)) return false;
		$parent = substr($category, 0, strlen($category) - $length);
		if(!$parent) return false;

		$dbGet = new \BH_DB_Get($table);
		$dbGet->AddWhere('category = %s', $parent);
		return $dbGet->Get();
	}

	/* -------------------------------------------------
	 *
	 *       Menu
	 *
	------------------------------------------------- */

	// App::$SettingData['MainMenu'], App::$SettingData['SubMenu'] 에 메인메뉴, 서브메뉴를 셋팅
	public static function _SetMenu($Title = 'Home'){
		if(isset(App::$SettingData['MainMenu']) && sizeof(App::$SettingData['MainMenu'])) return;
		$Menu = self::_GetRootMenu($Title);
		if($Menu){
			App::$SettingData['RootC'] = $Menu['category'];
			$menu = self::_GetSubMenu(App::$SettingData['RootC']);
			foreach($menu as $row){
				if(strlen($row['category']) == strlen(App::$SettingData['RootC']) + _CATEGORY_LENGTH) App::$SettingData['MainMenu'][] = $row;
				else App::$SettingData['SubMenu'][substr($row['category'], 0, strlen($row['category']) - _CATEGORY_LENGTH)][] = $row;
			}
		}
	}

	public static function _GetRootMenu($title = ''){
		$dbGet = DB::GetQryObj(TABLE_MENU)
			->AddWhere('LENGTH(category) = '._CATEGORY_LENGTH)
			->AddWhere('enabled = \'y\'')
			->AddWhere('parent_enabled = \'y\'');
		if($title) $dbGet->AddWhere('controller = %s', $title);
		return $dbGet->Get();
	}

	public static function _GetSubMenu($key){
		$dbGetList = DB::GetListQryObj(TABLE_MENU)
			->AddWhere('LEFT(category, %d) = %s', strlen($key), $key)
			->AddWhere('LENGTH(category) IN (%s)', array(strlen($key) + _CATEGORY_LENGTH, strlen($key) + _CATEGORY_LENGTH + _CATEGORY_LENGTH))
			->AddWhere('enabled = \'y\'')
			->AddWhere('parent_enabled = \'y\'')
			->SetSort('sort');
		$menu = array();
		while($row = $dbGetList->Get()) $menu[$row['category']] = $row;
		return $menu;
	}

	// 접근가능 메뉴인지 체크하고 라우팅함
	public static function _SetMenuRouter($url, $start = 1){
		if(!isset(App::$SettingData['RootC']) || !App::$SettingData['RootC']) return false;
		$cont = App::$SettingData['GetUrl'][$start];
		if(!$cont) $cont = _DEFAULT_CONTROLLER;

		$qry = DB::GetListQryObj(TABLE_MENU)
			->AddWhere('controller = %s', $cont)
			->AddWhere('LEFT(category, %d) = %s', strlen(App::$SettingData['RootC']), App::$SettingData['RootC'])
			->SetSort('LENGTH(category) DESC');

		$cnt = 0;
		while($row = $qry->Get()){
			if($row['parent_enabled'] == 'y' && $row['enabled'] == 'y'){
				App::$SettingData['ActiveMenu'] = $row;
				break;
			}
			$cnt ++;
		}

		if(!isset(App::$SettingData['ActiveMenu']) && $cnt){
			if(_DEVELOPERIS === true) URLReplace(-1, '접근이 불가능한 메뉴입니다.');
			URLReplace(-1);
		}

		if(isset(App::$SettingData['ActiveMenu'])){
			if(App::$SettingData['ActiveMenu']['type'] == 'board') App::$ControllerName = 'Board';
			else if(App::$SettingData['ActiveMenu']['type'] == 'content') App::$ControllerName = 'Contents';
			else App::$ControllerName = App::$SettingData['GetUrl'][$start];

			App::$TID = App::$SettingData['ActiveMenu']['bid'];
			App::$Action = App::$SettingData['GetUrl'][$start + 1];
			App::$ID = App::$SettingData['GetUrl'][$start + 2];
			App::$CtrlUrl = $url.'/'.App::$SettingData['GetUrl'][$start];
			return true;
		}
		return false;
	}

}