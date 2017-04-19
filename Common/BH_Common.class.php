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
		if(!isset($GLOBALS['_BH_App']->CFG[$code])){
			$path = _DATADIR.'/CFG/'.$code.'.php';
			if(file_exists($path)){
				require_once $path;
			}else $GLOBALS['_BH_App']->CFG[$code] = array();
		}

		if(!isset($GLOBALS['_BH_App']->CFG[$code][$key])){
			$res->result = false;
			$res->message = '기본값이 설정되어 있지 않습니다.(관리자화면 환경설정에서 기본값을 설정하여 주세요)';
			return $res;
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
		$maxImage = MAX_IMAGE_COUNT;
		$imageCount = 0;

		if($mode == 'modify'){
			$dbKeyValue = SetDBText(implode('|',$keyValue));
			$dbGetList = new \BH_DB_GetList(TABLE_IMAGES);
			$dbGetList->AddWhere('tid=%s', $tid);
			$dbGetList->AddWhere('article_seq='.$dbKeyValue);
			while($img = $dbGetList->Get()){
				if(strpos($content['contents'], $img['image']) === false){
					// 파일이 없으면 삭제
					@unlink(_UPLOAD_DIR.$img['image']);
					$qry = new \BH_DB_Delete(TABLE_IMAGES);
					$qry->AddWhere('tid = %s', $tid);
					$qry->AddWhere('article_seq = %d', $dbKeyValue);
					$qry->AddWhere('seq = %d', $img['seq']);
					$qry->Run();
				}else $imageCount ++;
			}
		}

		if(isset($_POST['addimg']) && is_array($_POST['addimg'])){
			$ym = date('ym');
			foreach($_POST['addimg'] as $img){
				$exp = explode('|', $img);

				if(strpos($content['contents'], $exp[0]) !== false){
					if($imageCount >= $maxImage) break;
					$newpath = str_replace('/'._UPLOAD_DIRNAME.'/temp/', '/'._UPLOAD_DIRNAME.'/image/'.$ym.'/', $exp[0]);
					$uploadDir = _UPLOAD_DIR.'/image/'.$ym;
					if(!is_dir($uploadDir)){
						mkdir($uploadDir, 0777, true);
					}
					@copy(_DIR.$exp[0],_DIR.$newpath);
					$newcontent = str_replace($exp[0],$newpath, $newcontent);
					// 파일이 있으면 등록

					unset($dbInsert);
					$dbInsert = new \BH_DB_Insert(TABLE_IMAGES);
					$dbInsert->data['tid'] = SetDBText($tid);
					$dbInsert->data['article_seq'] = SetDBText(implode('|',$keyValue));
					$dbInsert->data['image'] = SetDBText($newpath);
					$dbInsert->data['imagename'] = SetDBText($exp[1]);
					$dbInsert->decrement = 'seq';
					//$params['test'] = true;
					$dbInsert->Run();
					$imageCount++;
				}
				@unlink(_DIR.$exp[0]);
			}

			if($newcontent != $content['contents']){
				$qry = new \BH_DB_Update($tid);
				$qry->SetData('%1 = %s', $content['name'], $newcontent);
				foreach($keyValue as $k=>$v){
					$qry->AddWhere('%1 = %s', $k, $v);
				}
				$qry->Run();
			}
		}

		require_once _COMMONDIR.'/FileUpload.php';
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

	public function Download($path, $fname){
		$temp = explode('/', $path);
		if(!$fname){
			$fname = $temp[sizeof($temp)-1];
		}

		unset($GLOBALS['_BH_App']->CTRL->Layout);

		ignore_user_abort(true);
		set_time_limit(0); // disable the time limit for this script


		if(strpos($path, '..') !== false){
			Redirect('-1', '경로오류');
		}
		$dl_file = filter_var($path, FILTER_SANITIZE_URL); // Remove (more) invalid characters
		$fullPath = _UPLOAD_DIR.$dl_file;

		if ($fd = fopen ($fullPath, "r")) {
			$fsize = filesize($fullPath);
			$path_parts = pathinfo($fullPath);
			$ext = strtolower($path_parts["extension"]);
			switch ($ext) {
				case "pdf":
					header("Content-type: application/pdf");
					header("Content-Disposition: attachment; filename=\"".$fname."\""); // use 'attachment' to force a file download
				break;
				// add more headers for other content types here
				default;
					header("Content-type: application/octet-stream");
					header( 'Content-Description: File Download' );
					header('Content-Disposition: attachment; filename="'.$fname.'"');
					header( 'Content-Transfer-Encoding: binary' );
				break;
			}
			header("Content-length: $fsize");
			header("Cache-control: private"); //use this to open files directly
			while(!feof($fd)) {
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}
		fclose ($fd);
		exit;
	}
}