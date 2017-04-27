<?php
class BH_Common
{
	public $Member;
	public $MainMenu = array();
	public $SubMenu = array();
	public $ActiveMenu = array();
	public $AdminMenu = array();

	public function __construct(){
		global $_BH_App;

		if($_BH_App->Controller != 'Mypage'){
			$_SESSION['MyInfoView'] = false;
			unset($_SESSION['MyInfoView']);
		}

		$this->MainMenu = $_BH_App->Router->MainMenu;
		$this->SubMenu = $_BH_App->Router->SubMenu;
		$this->ActiveMenu = $_BH_App->Router->ActiveMenu;
		$this->AdminMenu = array(
			'001' => array(
				'Category' => 'Config',
				'Name' => '사이트관리'
			),
			'001001' => array(
				'Category' => 'Config',
				'Name' => '환경설정'
			),
			'001002' => array(
				'Category' => 'BannerManager',
				'Name' => '배너관리'
			),
			'001003' => array(
				'Category' => 'PopupManager',
				'Name' => '팝업관리'
			),
			'002' => array(
				'Category' => 'BoardManager',
				'Name' => '게시판관리'
			),
			'003' => array(
				'Category' => 'ContentManager',
				'Name' => '컨텐츠관리'
			),
			'004' => array(
				'Category' => 'MenuManager',
				'Name' => '메뉴관리'
			),
			'005' => array(
				'Category' => 'Member',
				'Name' => '회원관리'
			)
		);
	}

	public function AdminAuth(){
		if(_MEMBERIS !== true || ($_SESSION['member']['level'] != _SADMIN_LEVEL  && $_SESSION['member']['level'] != _ADMIN_LEVEL)){
			if(_AJAXIS === true) JSON(false, _NO_AUTH.' 로그인하여 주세요.');
			else Redirect($GLOBALS['_BH_App']->CTRL->URLBase('Login'), _NO_AUTH.' 로그인하여 주세요.');
		}
		if($_SESSION['member']['level'] == _ADMIN_LEVEL){
			$AdminAuth = explode(',', $this->GetMember('admin_auth'));
			if(!in_array($GLOBALS['_BH_App']->CTRL->_Value['NowMenu'], $AdminAuth)){
				if(_AJAXIS === true) JSON(false, _NO_AUTH);
				else Redirect('-1', _NO_AUTH);
			}
		}
	}

	public function MemberAuth($level = 1){
		if(_MEMBERIS !== true){
			if(_AJAXIS === true) JSON(false, _NO_AUTH.' 로그인하여 주세요.');
			else Redirect($GLOBALS['_BH_App']->CTRL->URLBase('Login'), _NO_AUTH.' 로그인하여 주세요.');
		}
		if($_SESSION['member']['level'] < $level){
			if(_AJAXIS === true) JSON(false, _NO_AUTH);
			else Redirect('-1', _NO_AUTH);
		}
	}

	/**
	 * @param string $key
	 * @return array|bool|null
	 */
	public function GetMember($key = '')
	{
		// 원글 가져오기
		if(_MEMBERIS === true){
			if(!isset($this->Member) || !$this->Member){
				$dbGet = new \BH_DB_Get(TABLE_MEMBER);
				$dbGet->AddWhere('muid=' . SetDBInt($_SESSION['member']['muid']));
				$this->Member = $dbGet->Get();
			}
			if($key) return $this->Member[$key];
			return $this->Member;
		}else{
			return false;
		}
	}


	public static function Config($code, $key){
		// 설정불러오기
		if(!isset($GLOBALS['_BH_App']->CFG[$code])){
			$path = _DATADIR.'/CFG/'.$code.'.php';
			if(file_exists($path)){
				require_once $path;
			}else $GLOBALS['_BH_App']->CFG[$code] = array();
		}
		return isset($GLOBALS['_BH_App']->CFG[$code][$key]) ? $GLOBALS['_BH_App']->CFG[$code][$key] : null;
	}

	public static function SetConfig($code, $key, $val){
		$res = new \BH_Result();
		if(_DEVELOPERIS !== true){
			$res->result = false;
			$res->message = _WRONG_CONNECTED;
			return;
		}

		$dirPath = _DATADIR.'/CFG';
		if(!file_exists($dirPath) && !is_dir($dirPath)) mkdir($dirPath, 0700, true);

		if(!isset($GLOBALS['_BH_App']->CFG[$code])){
			$path = _DATADIR.'/CFG/'.$code.'.php';
			if(file_exists($path)){
				require_once $path;
			}else $GLOBALS['_BH_App']->CFG[$code] = array();
		}

		$GLOBALS['_BH_App']->CFG[$code][$key] = $val;
		$path = _DATADIR.'/CFG/'.$code.'.php';
		$txt = '<?php $GLOBALS[\'_BH_App\']->CFG = '.var_export($GLOBALS['_BH_App']->CFG, true).';';
		file_put_contents($path, $txt);
		$res->result = true;
		return $res;
	}

	/**
	 * 이미지 등록
	 */
	public function ContentImageUpate($tid, $keyValue, $content, $mode = 'write'){
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

	public function MenuConnect($bid, $type){
		$AdminAuth = explode(',', $this->GetMember('admin_auth'));
		if(in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL){
			if(strlen($_POST['select_menu'])){
				$selectmenu = implode(',', SetDBText(explode(',', $_POST['select_menu'])));
				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('category IN ('.$selectmenu.')');
				$mUpdate->SetData('type', SetDBText($type));
				$mUpdate->SetData('bid', SetDBText($bid));
				$mUpdate->Run();

				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('bid = '.SetDBText($bid));
				$mUpdate->AddWhere('category NOT IN ('.$selectmenu.')');
				$mUpdate->AddWhere('type='.SetDBText($type));
				$mUpdate->SetData('type', SetDBText('customize'));
				$mUpdate->SetData('bid', SetDBText(''));
				$mUpdate->Run();

			}else{
				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('bid = '.SetDBText($bid));
				$mUpdate->AddWhere('type='.SetDBText($type));
				$mUpdate->SetData('type', SetDBText('customize'));
				$mUpdate->SetData('bid', SetDBText(''));
				$mUpdate->Run();
			}
		}
	}

	public function GetBoardArticle($bid, $category = '', $limit = 10){
		// 리스트를 불러온다.
		$dbList = new \BH_DB_GetList(TABLE_FIRST.'bbs_'.$bid);
		$dbList->AddWhere('delis=\'n\'');
		$dbList->sort = 'sort1, sort2';
		$dbList->limit = $limit;
		if(strlen($category)){
			$dbList->AddWhere('category = '.SetDBText($category));
		}
		return $dbList;
	}

	public function GetBanner($category){
		$banner = new \BH_DB_GetList(TABLE_BANNER);
		$banner->AddWhere('begin_date <= \''.date('Y-m-d').'\'');
		$banner->AddWhere('end_date >= \''.date('Y-m-d').'\'');
		$banner->AddWhere('enabled = \'y\'');
		$banner->AddWhere('category = '.SetDBText($category));
		$mlevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		$banner->AddWhere('mlevel <= '.$mlevel);

		$data = array();
		while($row = $banner->Get()){
			$html = '';
			if($row['link_url']) $html .= '<a href="'.$row['link_url'].'"'.($row['new_window'] == 'y' ? ' target="_blank"' : '').'>';
			$html .= '<img src="'._UPLOAD_URL.$row['img'].'" alt="'.GetDBText($row['subject']).'">';
			if($row['link_url']) $html .= '</a>';
			$data[] = $html;
		}
		return $data;
	}


	public function GetPopup(){
		$banner = new \BH_DB_GetList(TABLE_POPUP);
		$banner->AddWhere('begin_date <= \''.date('Y-m-d').'\'');
		$banner->AddWhere('end_date >= \''.date('Y-m-d').'\'');
		$banner->AddWhere('enabled = \'y\'');
		$mlevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		$banner->AddWhere('mlevel <= '.$mlevel);

		$data = array();
		while($row = $banner->Get()){
			if($row['type'] == 'i'){
				$html = '';
				if($row['link_url']) $html .= '<a href="'.$row['link_url'].'"'.($row['new_window'] == 'y' ? ' target="_blank"' : '').'>';
				$html .= '<img src="'._UPLOAD_URL.$row['img'].'" alt="'.GetDBText($row['subject']).'">';
				if($row['link_url']) $html .= '</a>';
			}
			else $html = GetDBRaw(addslashes($row['contents']));

			$data[] = array('html' => $html, 'seq' => $row['seq'], 'width' => $row['width'], 'height' => $row['height']);
		}
		return $data;
	}
}