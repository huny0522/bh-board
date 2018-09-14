<?php
use \BH_Application as App;
use \DB as DB;
class BH_Common
{
	public static $member;

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
			if(_AJAXIS === true) JSON(false, _MSG_NO_AUTH.' 로그인하여 주세요.', array('needLogin' => true));
			else URLReplace(_URL . '/Login', _MSG_NO_AUTH.' 로그인하여 주세요.');
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
			if(!isset(self::$member) || !self::$member){
				$dbGet = new \BH_DB_Get(TABLE_MEMBER);
				$dbGet->AddWhere('muid=' . SetDBInt($_SESSION['member']['muid']));
				$dbGet->SetKey('*', 'NULL as pwd');
				self::$member = $dbGet->Get();
			}
			if($key) return self::$member[$key];
			return self::$member;
		}else{
			return false;
		}
	}

	public static function StripSlashes($data){
		if(is_array($data)) return array_map('self::StripSlashes', $data);
		else return stripslashes($data);
	}

	public static function Config($code, $key){
		// 설정불러오기
		if(!isset(App::$CFG[$code])){
			$path = _DATADIR.'/CFG/'.$code.'.php';
			if(file_exists($path)){
				$data = file_get_contents($path);
				if(substr($data, 0, 15) == '<?php return;/*') App::$CFG[$code] = json_decode(substr($data, 15), true);
				else require $path;
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
				App::$CFG[$code] = json_decode(substr(file_get_contents($path), 15), true);
			}else App::$CFG[$code] = array();
		}

		App::$CFG[$code][$key] = $val;
		$path = _DATADIR.'/CFG/'.$code.'.php';
		$txt = '<?php return;/*'.json_encode(App::$CFG[$code]);
		file_put_contents($path, $txt);
		$res->result = true;
		return $res;
	}

	public static function RefreshParam($beginMark = '?'){
		return $beginMark.'r='.self::Config('Refresh', 'Refresh');
	}

	// 이미지 등록
	public static function ContentImageUpdate($tid, $keyValue, $content, $mode = 'write'){
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
		$dbGet->AddWhere('article_seq = %d', $dbKeyValue);
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

	/**
	 * 메뉴 카테고리와 컨텐츠 또는 게시판을 연결
	 *
	 * @param $bid
	 * @param $type
	 * @param string $subid
	 */
	public static function MenuConnect($bid, $type, $subid = ''){
		$AdminAuth = explode(',', self::GetMember('admin_auth'));
		if(in_array('004', $AdminAuth) || $_SESSION['member']['level'] == _SADMIN_LEVEL){
			if(strlen($_POST['select_menu'])){
				$selectmenu = explode(',', $_POST['select_menu']);
				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('category IN (%s)', $selectmenu);
				$mUpdate->SetDataStr('type', $type);
				$mUpdate->SetDataStr('bid', $bid);
				$mUpdate->SetDataStr('subid', $subid);
				$mUpdate->Run();

				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('bid = %s', $bid);
				$mUpdate->AddWhere('subid = %s', $subid);
				$mUpdate->AddWhere('category NOT IN (%s)', $selectmenu);
				$mUpdate->AddWhere('type = %s', $type);
				$mUpdate->SetDataStr('type', 'customize');
				$mUpdate->SetDataStr('bid', '');
				$mUpdate->SetDataStr('subid', '');
				$mUpdate->Run();

			}else{
				$mUpdate = new \BH_DB_Update(TABLE_MENU);
				$mUpdate->AddWhere('bid = %s', $bid);
				$mUpdate->AddWhere('subid = %s', $subid);
				$mUpdate->AddWhere('type = %s', $type);
				$mUpdate->SetDataStr('type', 'customize');
				$mUpdate->SetDataStr('bid', '');
				$mUpdate->SetDataStr('subid', '');
				$mUpdate->Run();
			}
		}
		\Common\MenuHelp::GetInstance()->MenusToFile();
	}

	/**
	 * 게시물 쿼리 가져오기.
	 * 5번째 인자부터는 배열로 where 구문을 호출할 수 있습니다.
	 * ex) array('INSTR(mname, %s)', '홍길동')
	 *
	 * @param string $bid
	 * @param string $subid
	 * @param string $category
	 * @param int $limit
	 * @return BH_DB_GetList
	 */
	public static function GetBoardArticleQuery($bid, $subid, $category = '', $limit = 10){
		// 리스트를 불러온다.
		$dbList = new \BH_DB_GetList(TABLE_FIRST.'bbs_'.$bid);
		$dbList->AddWhere('subid = %s', $subid);
		$dbList->AddWhere('delis=\'n\'');
		$n = func_num_args();
		if($n > 4){
			$args = func_get_args();
			for($i = 4; $i < $n; $i++) $dbList->AddWhere($args[$i]);
		}
		$dbList->sort = 'sort1, sort2';
		$dbList->limit = $limit;
		if(strlen($category)){
			$dbList->AddWhere('category = %s', $category);
		}
		return $dbList;
	}

	/**
	 * 게시물 가져오기.
	 * 5번째 인자부터는 배열로 where 구문을 호출할 수 있습니다.
	 * ex) array('INSTR(mname, %s)', '홍길동')
	 *
	 * @param string $bid
	 * @param string $subid
	 * @param string $category
	 * @param int $limit
	 * @return array
	 */
	public static function GetBoardArticle($bid, $subid, $category = '', $limit = 10){
		$qry = call_user_func_array('self::GetBoardArticleQuery', func_get_args());
		$data = array();
		$d = (self::Config('Default', 'NewIconDay')? self::Config('Default', 'NewIconDay') : 1) * 86400;

		while($row = $qry->Get()){
			if($row['secret'] == 'y') $row['subject'] = '비밀글입니다.';
			$row['replyCount'] = $row['reply_cnt'] ? '<span class="ReplyCount">['.$row['reply_cnt'].']</span>' : '';
			$newArticleIs = (time() - strtotime($row['reg_date']) < $d);
			$row['secretIcon']= $row['secret'] == 'y' ? '<span class="secretDoc">[비밀글]</span> ' : '';
			$row['newtIcon'] = $newArticleIs ? '<span class="newDoc">[새글]</span> ' : '';
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * 배너 가져오기
	 * @param string $category
	 * @param callable $queryFunction(\BH_DB_GetList)
	 * @return array
	 */
	public static function GetBanner($category, $queryFunction = null){
		$banner = new \BH_DB_GetList(TABLE_BANNER);
		$banner->AddWhere('begin_date <= \''.date('Y-m-d').'\'')
			->AddWhere('end_date >= \''.date('Y-m-d').'\'')
			->AddWhere('enabled = \'y\'')
			->AddWhere('category = %s', $category)
			->SetSort('sort DESC, seq ASC')
			->SetLimit(5);

		if(is_callable($queryFunction)) $queryFunction($banner);

		$mlevel = _MEMBERIS === true ? $_SESSION['member']['level'] : 0;
		$banner->AddWhere('mlevel <= '.$mlevel)
			->DrawRows();

		foreach($banner->data as $k => $row){
			$html = '';
			if($row['link_url']) $html .= '<a href="'.$row['link_url'].'"'.($row['new_window'] == 'y' ? ' target="_blank"' : '').'>';
			$html .= ($row['type'] == 'i') ? '<img src="'._UPLOAD_URL.$row['img'].'" alt="'.GetDBText($row['subject']).'">' : $row['contents'];
			if($row['link_url']) $html .= '</a>';
			$banner->data[$k]['html'] = $html;
		}

		return $banner->data;
	}

	/**
	 * 팝업 가져오기
	 * @param callable $queryFunction
	 * @return array
	 */
	public static function GetPopup($queryFunction = null){

		$banner = new \BH_DB_GetList(TABLE_POPUP);
		$banner->AddWhere('begin_date <= \''.date('Y-m-d').'\'')
			->AddWhere('end_date >= \''.date('Y-m-d').'\'')
			->AddWhere('enabled = \'y\'')
			->SetSort('sort DESC, seq ASC')
			->SetLimit(5);

		if(is_callable($queryFunction)) $queryFunction($banner);

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

	public static function Youtube($urlOrId, $width='100%', $height = '100%',  $opt = 'autoplay'){
		$urlOrId = self::GetYoutubeId($urlOrId);
		if($urlOrId !== false) return '<div class="youtubeFrameWrap"><iframe src="https://www.youtube.com/embed/'. $urlOrId . '?rel=0&amp;showinfo=0&amp;autohide=1" frameborder="0" allow="' . $opt .'; encrypted-media" allowfullscreen style="width:' . $width . '; height:' . $height . ';" autohide="1"></iframe></div>';
		return '';
	}

	public static function GetYoutubeId($urlOrId){
		$urlOrId = trim($urlOrId);
		preg_match('/youtu\.be\/([a-zA-Z0-9\-\_]+)/', $urlOrId, $matches);
		if(is_array($matches) && sizeof($matches) > 1){
			$temp = explode('/', $matches[1]);
			return end($temp);
		}

		preg_match('/youtube\.com\/embed\/([a-zA-Z0-9\-\_]+)/', $urlOrId, $matches);
		if(is_array($matches) && sizeof($matches) > 1){
			$temp = explode('/', $matches[1]);
			return end($temp);
		}
		preg_match('/youtube\.com\/watch.*?v\=([a-zA-Z0-9\-\_]+)/', $urlOrId, $matches);
		if(is_array($matches) && sizeof($matches) > 1){
			$temp = explode('/', $matches[1]);
			return end($temp);
		}
		return $urlOrId;
	}

	public static function SafeHtml(){

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

}