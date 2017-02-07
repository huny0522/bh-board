<?php
class BH_Common
{
	public $Member;
	public $MainMenu = array();
	public $SubMenu = array();
	public $ActiveMenu = array();
	public $CFG = array();
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
				$dbGet = new BH_DB_Get(TABLE_MEMBER);
				$dbGet->AddWhere('muid=' . SetDBInt($_SESSION['member']['muid']));
				$this->Member = $dbGet->Get();
			}
			if($key) return $this->Member[$key];
			return $this->Member;
		}else{
			return false;
		}
	}


	public function Config($code, $key){
		// 설정불러오기
		if(!isset($this->CFG[$code])){
			$path = _DIR.'/Common/CFG/'.$code.'.inc';
			if(!file_exists($path)) return null;
			$var = file_get_contents($path);
			$this->CFG[$code] = json_decode($var);
		}
		return isset($this->CFG[$code]->$key) ? $this->CFG[$code]->$key : null;
	}

	/**
	 * 이미지 등록
	 */
	public function ContentImageUpate($tid, $keyValue, $content, $mode = 'write'){
		$newcontent = $content['contents'];
		$maxImage = MAX_IMAGE_COUNT;
		$imageCount = 0;

		if($mode == 'modify'){
			$dbTid = SetDBText($tid);
			$dbKeyValue = SetDBText(implode('|',$keyValue));
			$dbGetList = new BH_DB_GetList(TABLE_IMAGES);
			$dbGetList->AddWhere('tid='.$dbTid);
			$dbGetList->AddWhere('article_seq='.$dbKeyValue);
			while($img = $dbGetList->Get()){
				if(strpos($content['contents'], $img['image']) === false){
					// 파일이 없으면 삭제
					@unlink(_UPLOAD_DIR.$img['image']);
					SqlQuery('DELETE FROM '.TABLE_IMAGES.' WHERE tid='.$dbTid.' AND article_seq='.$dbKeyValue.' AND seq='.$img['seq']);
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
					$dbInsert = new BH_DB_Insert(TABLE_IMAGES);
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
				$where = array();
				foreach($keyValue as $k=>$v){
					$where[] = $k.'='.SetDBText($v);
				}
				SqlQuery('UPDATE '.$tid.' SET '.$content['name'].' = '.SetDBText($newcontent).' WHERE '.implode('AND', $keyValue));
			}
		}

		require_once _LIBDIR.'/FileUpload.php';
		DeleteOldTempFiles(_UPLOAD_DIR.'/temp/', strtotime('-6 hours'));
		return true;
	}


	public function MenuConnect($bid, $type){
		$AdminAuth = explode(',', $this->GetMember('admin_auth'));
		if(in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL){
			if(strlen($_POST['select_menu'])){
				$selectmenu = implode(',', SetDBText(explode(',', $_POST['select_menu'])));
				$mUpdate = new BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('category IN ('.$selectmenu.')');
				$mUpdate->SetData('type', SetDBText($type));
				$mUpdate->SetData('bid', SetDBText($bid));
				$mUpdate->Run();

				$mUpdate = new BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('bid = '.SetDBText($bid));
				$mUpdate->AddWhere('category NOT IN ('.$selectmenu.')');
				$mUpdate->AddWhere('type='.SetDBText($type));
				$mUpdate->SetData('type', SetDBText('customize'));
				$mUpdate->SetData('bid', SetDBText(''));
				$mUpdate->Run();

			}else{
				$mUpdate = new BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('bid = '.SetDBText($bid));
				$mUpdate->AddWhere('type='.SetDBText($type));
				$mUpdate->SetData('type', SetDBText('customize'));
				$mUpdate->SetData('bid', SetDBText(''));
				$mUpdate->Run();
			}
		}
	}

	public function GetBoardArticle($bid, $category = ''){
		// 리스트를 불러온다.
		$dbList = new BH_DB_GetList(TABLE_FIRST.'bbs_'.$bid);
		$dbList->AddWhere('delis=\'n\'');
		$dbList->sort = 'sort1, sort2';
		if(strlen($category)){
			$dbList->AddWhere('category = '.SetDBText($category));
		}
		return $dbList;
	}

	public function GetBanner($category){
		$banner = new BH_DB_GetList(TABLE_BANNER);
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
		$banner = new BH_DB_GetList(TABLE_POPUP);
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