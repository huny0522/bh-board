<?php
use \BH_Application as App;
use \DB as DB;
class BH_Common
{
	/**
	 * @var array
	 */
	public static $member;
	/**
	 * @var array
	 */
	private static $admin;
	private static $otherMember = array();
	public static $blockUser;

	private static $fbSetIs = null;

	private function __construct(){
	}

	public static function AdminAuth($redirectUrl = ''){
		if($redirectUrl === '') $redirectUrl = App::URLBase('Login');
		if(\BHG::$isAdmin !== true || (\BHG::$session->admin->level->Get() != _SADMIN_LEVEL  && \BHG::$session->admin->level->Get() != _ADMIN_LEVEL)){
			if(_JSONIS === true) JSON(false, App::$lang['MSG_NO_AUTH'].' ' .App::$lang['MSG_NEED_LOGIN']);
			else URLReplace($redirectUrl, App::$lang['MSG_NO_AUTH'].' ' .App::$lang['MSG_NEED_LOGIN']);
		}
		if(\BHG::$session->admin->level->Get() == _ADMIN_LEVEL){
			$AdminAuth = explode(',', self::GetAdmin('admin_auth'));
			if(!in_array(App::$data['NowMenu'], $AdminAuth)){
				if(_JSONIS === true) JSON(false, App::$lang['MSG_NO_AUTH']);
				else URLReplace('-1', App::$lang['MSG_NO_AUTH']);
			}
		}
	}

	public static function GetAdminIs(){
		if(\BHG::$isAdmin === true && (\BHG::$session->admin->level->Get() == _SADMIN_LEVEL  || \BHG::$session->admin->level->Get() == _ADMIN_LEVEL)) return true;
		return false;
	}

	public static function MemberAuth($level = 1){
		if(\BHG::$isMember !== true){
			if(_JSONIS === true) JSON(false, App::$lang['MSG_NO_AUTH'].' ' .App::$lang['MSG_NEED_LOGIN'], array('needLogin' => true));
			else URLReplace(Paths::Url() . '/Login?r_url=' . urlencode($_SERVER['REQUEST_URI']), App::$lang['MSG_NO_AUTH'].' ' .App::$lang['MSG_NEED_LOGIN']);
		}
		if(\BHG::$session->member->level->Get() < $level){
			if(_JSONIS === true) JSON(false, App::$lang['MSG_NO_AUTH']);
			else URLReplace('-1', App::$lang['MSG_NO_AUTH']);
		}
	}

	/**
	 * @param string $key
	 * @return array|bool|null
	 */
	public static function GetMember($key = ''){
		// 원글 가져오기
		if(\BHG::$isMember === true){
			if(!isset(self::$member) || !self::$member){
				$dbGet = new \BH_DB_Get(TABLE_MEMBER);
				$dbGet->AddWhere('muid=' . SetDBInt(\BHG::$session->member->muid->Get()));
				$dbGet->SetKey('*', 'NULL as pwd');
				self::$member = $dbGet->Get();
			}
			if($key) return isset(self::$member[$key]) ? self::$member[$key] : '';
			return self::$member;
		}else{
			return false;
		}
	}

	/**
	 * @param string $key
	 * @return array|bool|null
	 */
	public static function GetAdmin($key = ''){
		// 원글 가져오기
		if(\BHG::$isAdmin === true){
			if(!isset(self::$admin) || !self::$admin){
				$dbGet = new \BH_DB_Get(TABLE_MEMBER);
				$dbGet->AddWhere('muid=' . SetDBInt(\BHG::$session->admin->muid->Get()));
				$dbGet->SetKey('*', 'NULL as pwd');
				self::$admin = $dbGet->Get();
			}
			if($key) return isset(self::$admin[$key]) ? self::$admin[$key] : '';
			return self::$admin;
		}else{
			return false;
		}
	}

	/**
	 * @param int $muid
	 * @param string $key
	 * @return array|bool
	 */
	public static function GetOtherMember($muid, $key = ''){
		// 원글 가져오기
		if(\BHG::$isMember === true){
			if(!isset(self::$otherMember[$muid]) || !self::$otherMember[$muid]){
				$dbGet = new \BH_DB_Get(TABLE_MEMBER);
				$dbGet->AddWhere('muid=' . SetDBInt($muid));
				$dbGet->SetKey('*', 'NULL as pwd');
				self::$otherMember[$muid] = $dbGet->Get();
			}
			if($key) return isset(self::$otherMember[$muid][$key]) ? self::$otherMember[$muid][$key] : '';
			return self::$otherMember[$muid];
		}else{
			return false;
		}
	}

	/**
	 * 로그인 한 회원의 차단회원들을 가져옴
	 *
	 * @return array
	 */
	public static function GetBlockUsers(){
		if(\BHG::$isMember !== true) return array();
		if(!isset(self::$blockUser)){
			$qry = DB::GetListQryObj(TABLE_USER_BLOCK . ' A')
				->AddWhere('`A`.`muid` = %d', \BHG::$session->member->muid->Get());
			$list = array();
			while($row = $qry->Get()){
				$list[] = $row['target_muid'];
			}
			self::$blockUser = $list;

		}
		return self::$blockUser;
	}

	/**
	 * @param int $muid
	 * @param string $name
	 * @return string
	 */
	public static function MemName($muid, $name){
		if(\BHG::$isMember !== true || \BHG::$session->member->muid->Get() == $muid) return GetDBText($name);
		else{
			$adtAttr = self::FirebaseSetIs() ? '' : ' data-no-chat="yes"';
			return '<a href="#" class="userPopupMenuBtn" data-id="' . $muid . '"' . $adtAttr . '>' . GetDBText($name) . '</a>';
		}
	}

	public static function FirebaseSetIs(){
		if(is_null(self::$fbSetIs)) self::$fbSetIs = (class_exists('Kreait\Firebase\Factory') && strlen(StrTrim(App::$cfg->Def()->firebaseWebConfig->Val())) > 0 && strlen(StrTrim(App::$cfg->Def()->googleServiceAccount->Val())) > 0);
		return self::$fbSetIs;
	}

	public static function StripSlashes($data){
		if(is_array($data)) return array_map('self::StripSlashes', $data);
		else return stripslashes($data);
	}

	public static function Config($code, $key){
		// 설정불러오기
		$class = 'Config'.$code;
		if(!class_exists($class)) return '';
		$cfg = $class::GetInstance();
		if(!isset($cfg->{$key})) return '';
		return $cfg->{$key}->value;
	}

	public static function SetConfig($code, $key, $val){
		// 설정불러오기
		$class = 'Config'.$code;
		if(!class_exists($class)) return '';
		$cfg = $class::GetInstance();

		return $cfg->DataWrite(array($key => $val));
	}

	public static function RefreshParam($beginMark = '?'){
		return $beginMark.'r='.App::$cfg->Sys()->refresh->value;
	}

	/**
	 * 이미지등록
	 * @param string $tid
	 * @param array $keyValue ['DB 칼럼' => seq]
	 * @param array $content [name : contents DB 칼럼, contents : 내용]
	 * @param string $mode
	 * @return mixed
	 */
	public static function ContentImageUpdate($tid, $keyValue, $content, $mode = 'write'){
		$newcontent = $content['contents'];
		$dbKeyValue = implode('|',$keyValue);
		$ex = explode('-', $mode);
		$mode = $ex[0];
		$cfgIs = (isset($ex[1]) && $ex[1] === 'cfg');

		if($mode == 'modify'){
			$dbGetList = new \BH_DB_GetList(TABLE_IMAGES);
			$dbGetList->AddWhere('tid=%s', $tid);
			$dbGetList->AddWhere('article_seq = %s', $dbKeyValue);
			while($img = $dbGetList->Get()){
				if(strpos($content['contents'], $img['image']) === false){
					// 파일이 없으면 삭제
					@unlink(\Paths::DirOfUpload().$img['image']);
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
		$dbGet->AddWhere('article_seq = %s', $dbKeyValue);
		$dbGet->SetKey('COUNT(*) as cnt');
		$cnt = $dbGet->Get();
		$imageCount = $cnt['cnt'];

		if(isset($_POST['addimg']) && is_array($_POST['addimg'])){
			$ym = date('ym');
			foreach($_POST['addimg'] as $img){
				$exp = explode('|', $img);
				if(sizeof($exp) < 2){
					$temp = explode('/', $exp[0]);
					$exp[1] = array_pop($temp);
				}

				if(strpos($content['contents'], $exp[0]) !== false){

					$newpath = str_replace('/temp/', '/image/' . $tid . '/' .$ym.'/', $exp[0]);
					$uploadDir = \Paths::DirOfUpload().'/image/' . $tid . '/' . $ym;
					if(!is_dir($uploadDir)){
						mkdir($uploadDir, 0777, true);
					}
					@copy(\Paths::DirOfUpload().$exp[0],\Paths::DirOfUpload().$newpath);
					$newcontent = str_replace($exp[0],$newpath, $newcontent);
					// 파일이 있으면 등록

					unset($dbInsert);
					$dbInsert = new \BH_DB_Insert(TABLE_IMAGES);
					$dbInsert->SetDataStr('image', $newpath);
					$dbInsert->SetDataStr('imagename', $exp[1]);
					$dbInsert->SetDataDecrement('seq', array('tid' => $tid, 'article_seq' => $dbKeyValue));
					//$params['test'] = true;
					$dbInsert->Run();
					$imageCount++;
				}
				@unlink(_DIR.$exp[0]);
			}

			if($newcontent != $content['contents'] && !$cfgIs){
				$qry = new \BH_DB_Update($tid);
				$qry->SetDataStr($content['name'], $newcontent);
				foreach($keyValue as $k=>$v){
					$qry->AddWhere('%1 = %s', $k, $v);
				}
				$qry->Run();
			}
		}

		DeleteOldTempFiles(\Paths::DirOfUpload().'/temp/', strtotime('-6 hours'));
		return $newcontent;
	}

	/**
	 * 메뉴 카테고리와 컨텐츠 또는 게시판을 연결
	 *
	 * @param $bid
	 * @param $type
	 * @param string $subid
	 */
	public static function MenuConnect($bid, $type, $subid = ''){
		$AdminAuth = explode(',', self::GetAdmin('admin_auth'));
		if(in_array('004', $AdminAuth) || \BHG::$session->admin->level->Get() == _SADMIN_LEVEL){
			if(StrLenPost('select_menu')){
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
		if((is_array($subid) && sizeof($subid)) || strlen((string)$subid)) $dbList->AddWhere('subid IN (%s)', is_array($subid) ? $subid : explode(',', $subid));
		$dbList->AddWhere('delis=\'n\'');

		$dbList->sort = 'sort1, sort2';
		$dbList->limit = $limit;
		if(strlen($category)){
			$dbList->AddWhere('category = %s', $category);
		}
		return $dbList;
	}

	/**
	 * 게시물 가져오기.
	 * ex) array('INSTR(mname, %s)', '홍길동')
	 *
	 * @param string $bid
	 * @param string $subid
	 * @param string $category
	 * @param int $limit
	 * @param callable(&BH_DB_GetList) $func
	 * @return array
	 */
	public static function GetBoardArticle($bid, $subid, $category = '', $limit = 10, $func = null){
		$qry = self::GetBoardArticleQuery($bid, $subid, $category, $limit);

		if(is_callable($func)) $func($qry);

		$data = array();
		$d = App::$cfg->Def()->newIconDay->Val() * 86400;

		while($row = $qry->Get()){
			if($row['secret'] == 'y') $row['subject'] = App::$lang['IS_SECRET_POST'];
			$row['replyCount'] = $row['reply_cnt'] ? '<span class="ReplyCount">['.$row['reply_cnt'].']</span>' : '';
			$newArticleIs = (time() - strtotime($row['reg_date']) < $d);
			$row['secretIcon']= $row['secret'] == 'y' ? '<span class="secretDoc">[' . App::$lang['SECRET_POST'] . ']</span> ' : '';
			$row['newtIcon'] = $newArticleIs ? '<span class="newDoc">[' . App::$lang['NEW_POST'] . ']</span> ' : '';
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

		$mlevel = \BHG::$isMember === true ? \BHG::$session->member->level->Get() : 0;
		$banner->AddWhere('mlevel <= '.$mlevel)
			->DrawRows();

		foreach($banner->data as $k => $row){
			if($row['type'] == 'i'){
				$html = '';
				if($row['link_url']) $html .= '<a href="'.$row['link_url'].'"'.($row['new_window'] == 'y' ? ' target="_blank"' : '').'>';
				$html .= '<img src="'.Paths::UrlOfUpload().$row['img'].'" alt="'.GetDBText($row['subject']).'">';
				if($row['link_url']) $html .= '</a>';
			}
			else $html = GetDBRaw(addslashes($row['contents']));

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

		$mlevel = \BHG::$isMember === true ? \BHG::$session->member->level->Get() : 0;
		$banner->AddWhere('mlevel <= '.$mlevel)
			->DrawRows();


		foreach($banner->data as $k => $row){
			if($row['type'] == 'i'){
				$html = '';
				if($row['link_url']) $html .= '<a href="'.$row['link_url'].'"'.($row['new_window'] == 'y' ? ' target="_blank"' : '').'>';
				$html .= '<img src="'.Paths::UrlOfUpload().$row['img'].'" alt="'.GetDBText($row['subject']).'">';
				if($row['link_url']) $html .= '</a>';
			}
			else $html = GetDBRaw($row['contents']);

			$banner->data[$k]['html'] = $html;
		}
		return $banner->data;
	}

	public static function Youtube($urlOrId, $width='100%', $height = '100%',  $opt = '?rel=0&amp;showinfo=0&amp;autohide=1&amp;autoplay=1'){
		$urlOrId = self::GetYoutubeId($urlOrId);
		if($urlOrId !== false) return '<div class="youtubeFrameWrap"><iframe src="https://www.youtube.com/embed/'. $urlOrId . $opt .'" frameborder="0" allowfullscreen style="width:' . $width . '; height:' . $height . ';" autohide="1"></iframe></div>';
		return '';
	}

	public static function GetYoutubeId($urlOrId){
		$urlOrId = trim($urlOrId);
		preg_match('/youtu\.be\/([a-zA-Z0-9\-\_]+)/', $urlOrId, $matches);
		if(is_array($matches) && sizeof($matches) > 1){
			$temp = explode('/', $matches[1]);
			return end($temp);
		}
		preg_match('/youtube\-nocookie\.com\/v\/([a-zA-Z0-9\-\_]+)/', $urlOrId, $matches);
		if(is_array($matches) && sizeof($matches) > 1){
			$temp = explode('/', $matches[1]);
			return end($temp);
		}

		preg_match('/youtube\.com\/(.*?)\/([a-zA-Z0-9\-\_]+)/', $urlOrId, $matches);
		if(is_array($matches) && sizeof($matches) > 2){
			$temp = explode('/', $matches[2]);
			return end($temp);
		}
		preg_match('/youtube\.com\/watch.*?v\=([a-zA-Z0-9\-\_]+)/', $urlOrId, $matches);
		if(is_array($matches) && sizeof($matches) > 1){
			$temp = explode('/', $matches[1]);
			return end($temp);
		}
		return $urlOrId;
	}

	public static function TinyMCEScript(){
		if(self::TinyMCEUseIs()) return 'tinyMCEHelper.tinyMCEPath = \'' . App::$settingData['tinyMCEPath'] . '\'; tinyMCEHelper.useTinyMce = true;';
		else return '';
	}

	public static function TinyMCEUseIs(){
		return (isset(App::$settingData['tinyMCEPath']) && strlen(App::$settingData['tinyMCEPath']) && file_exists(\Paths::Dir(App::$settingData['tinyMCEPath'])) && App::$cfg->Def()->htmlEditor->Val() == 'tinymce');
	}

	/**
	 * @param array|string.. $textArr
	 * @return string
	 */
	public static function Lang($textArr){
		if(!is_array($textArr)){
			$argn = func_num_args();
			if($argn === 1) return $textArr;
			else if($argn > 1) $textArr = func_get_args();
		}
		return $textArr[SELECT_LANG];
	}

	/* -------------------------------------------------
	 *
	 *       Category
	 *
	------------------------------------------------- */
	public static function _CategoryGetChild($table, $parent, $length){
		$dbGet = new \BH_DB_GetList($table);
		$dbGet->AddWhere('LEFT(category, %d) = %s', strlen((string)$parent), (string)$parent);
		$dbGet->AddWhere('LENGTH(category) = '.(strlen((string)$parent) + $length));
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
