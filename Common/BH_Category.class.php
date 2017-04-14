<?php
/**
 * Bang Hun.
 * 16.07.10
 */

class BH_Category extends BH_Controller{
	const ROOT_CATEGORY_CODE = '_ROOT';

	/** @var MenuModel */
	public $model;
	public $Name;
	public $FilePath = _DATADIR.'/DBModTime.php';

	public function __Init(){
		require_once _DIR.'/Model/Menu.model.php';
		$this->model = new MenuModel();
	}

	public function Index(){
		$this->_View($this->model, $this->model->GetChild());
	}

	// 데이타 전체반환
	public static function _GetAllData($table){
		$qry = new BH_DB_GetList($table);
		$qry->sort = 'LENGTH(category), sort';
		$len = 0;
		$ct = array();
		while($row = $qry->Get()){
			if(!$len) $len = strlen($row['category']);
			if(strlen($row['category']) > $len){
				$ct[substr($row['category'], 0, -$len)][$row['category']] = $row;
			}
			else $ct[self::ROOT_CATEGORY_CODE][$row['category']] = $row;
		}
		return $ct;
	}

	// 데이타 전체 셋팅
	public static function _SetFile($table){
		$path = _DATADIR.'/category_'.$table.'.php';
		if(!file_exists($path) || _DBModifyIs($table)){
			$GLOBALS['_BH_App']->_Category[$table] = BH_Category::_GetAllData($table);

			$txt = '$GLOBALS[\'_BH_App\']->_Category[\''.$table.'\'] = '.var_export($GLOBALS['_BH_App']->_Category[$table], true);
			file_put_contents($path, '<?php'.chr(10).$txt.';');
		}
		else{
			require_once $path;
		}
	}

	public static function _GetSub($table, $category){
		$menu = array();
		if(isset($GLOBALS['_BH_App']->_Category[$table][$category])){
			foreach($GLOBALS['_BH_App']->_Category[$table][$category] as $row){
				if($row['enabled'] == 'y' && $row['parent_enabled'] == 'y') $menu[] = $row;
			}
		}
		return $menu;
	}

	public static function _GetRoot($table, $title = ''){
		foreach($GLOBALS['_BH_App']->_Category[$table][self::ROOT_CATEGORY_CODE] as $row){
			if($row['title'] == $title &&
				$row['enabled'] == 'y' &&
				$row['parent_enabled'] == 'y'
			) return $row;
		}
		return false;
	}

	public function Write(){
		unset($this->Layout);
		if(!isset($_GET['category']) || $_GET['category'] == '') exit;
		$data = $this->model->DBGet($_GET['category']);
		$this->_View($this->model, $data);
	}

	public function PostWrite(){
		if(!isset($_POST['category']) || $_POST['category'] == '') exit;

		$this->model->DBGet($_POST['category']);
		$this->model->Need = array('title', 'type',  'controller', 'enabled');
		$res = $this->model->SetPostValues();
		if(!$res->result) JSON(false, $res->message);
		else{
			_DBModTime($this->model->table);
			$res = $this->model->DBUpdate();

			$dt = $this->model->GetParent($this->model->GetValue('category'));

			$this->model->SetChildEnabled($_POST['category'], $_POST['enabled'] == 'y' && (!$dt || ($dt['enabled'] == 'y' && $dt['parent_enabled'] == 'y')) ? 'y' : 'n');
			JSON($res->result, $res->message);
		}

	}

	public function PostInsertMenu()
	{
		$this->model->Need = array('title', 'sort', 'enabled');
		$res = $this->model->SetPostValues();
		if(!$res->result) JSON(false, $res->message);
		else {
			$dbGet = new BH_DB_Get($this->model->table);
			if($_POST['parent'] === ''){
				$dbGet->AddKey('MAX(category) as category');
				$dbGet->AddWhere('LENGTH(category) = '.$this->model->CategoryLength);
			}else{
				$dbGet->AddKey('MAX(category) as category');
				$dbGet->AddWhere('LEFT(category, '.strlen($_POST['parent']).') = '.SetDBText($_POST['parent']));
				$dbGet->AddWhere('LENGTH(category) = '.(strlen($_POST['parent']) + $this->model->CategoryLength));
			}
			$ct = $dbGet->Get();
			if(!strlen($ct['category'])) $newCategory = sprintf('%0'.$this->model->CategoryLength.'d', 0);
			else{
				$newCategory = substr($ct['category'],strlen($_POST['parent']), $this->model->CategoryLength);
				if(!strlen($newCategory)) $newCategory = sprintf('%0'.$this->model->CategoryLength.'d', 0);
				else{
					$newCategory = toBase(to10($newCategory) + 1);
					$newCategory = str_pad($newCategory, $this->model->CategoryLength, '0', STR_PAD_LEFT);
				}
			}
			$this->model->SetValue('category', $_POST['parent'].$newCategory);

			$dbGet = new BH_DB_Get($this->model->table);
			$dbGet->AddWhere('category = '.SetDBText($_POST['parent']));
			$dbGet->AddKey('parent_enabled');
			$dbGet->AddKey('enabled');
			$parent = $dbGet->Get();
			$this->model->SetValue('parent_enabled', $parent['parent_enabled'] == 'n' || $parent['enabled'] == 'n' ? 'n' : 'y');

			$res = $this->model->DBInsert();

			_DBModTime($this->model->table);

			$res->id = $_POST['parent'].$newCategory;
			JSON($res->result, '', $res);
		}
	}

	public function PostGetChild(){
		$res = $this->model->GetChild($_POST['parent']);
		JSON(true, '',$res);
	}

	public function PostModifyTitle(){
		$data = $this->model->DBGet($_POST['category']);
		$this->model->SetDBValues($data);
		$this->model->Need = array('title');
		$res = $this->model->SetPostValues();
		if(!$res->result){
			JSON(false, $res->message);
		}
		_DBModTime($this->model->table);
		$res = $this->model->DBUpdate();
		JSON($res->result, $res->message);
	}

	public function PostModifySort(){
		$data = $this->model->DBGet($_POST['category']);
		if($data->result){
			$sort = SetDBInt($_POST['sort']);
			$parentWhere = 'LEFT(category,'.strlen($_POST['parent']).') = '.SetDBText($_POST['parent']);
			$parentWhere .= ' AND LENGTH(category) = '.(strlen($_POST['parent']) + $this->model->CategoryLength);

			$res = false;
			if($sort < $this->model->GetValue('sort')) $res = SqlQuery('UPDATE %1 SET sort = sort + 1 WHERE '.$parentWhere.' AND sort >= %d AND sort < %d', $this->model->table, $sort, $this->model->GetValue('sort'));
			else if($sort > $this->model->GetValue('sort')) $res = SqlQuery('UPDATE %1 SET sort = sort - 1 WHERE '.$parentWhere.' AND sort <= %d AND sort > %d', $this->model->table, $sort, $this->model->GetValue('sort'));

			if($res) $res = SqlQuery('UPDATE %1 SET sort = %d WHERE '.$parentWhere.' AND category = %s', $this->model->table, $sort, $_POST['category']);
			_DBModTime($this->model->table);
			JSON($res);
		}else JSON(true, '', $data);
	}

	// 메뉴삭제
	// 하위메뉴까지 삭제
	public function PostDeleteMenu(){
		$dbGet = new BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('category', 'sort'));
		$dbGet->AddWhere('category = '.SetDBText($_POST['category']));

		$data = $dbGet->Get();
		$parent = substr($data['category'], 0, strlen($data['category']) - $this->model->CategoryLength);

		if($data !== false && $data){
			$res = SqlQuery('DELETE FROM %1 WHERE LEFT(category, %d) = %s', $this->model->table, strlen($_POST['category']), $_POST['category']);
			if($res) $res = SqlQuery('UPDATE %1 SET sort = sort - 1 WHERE LEFT(category, %d) = %s AND LENGTH(category) = %d AND sort > %d', $this->model->table, strlen($parent), $parent, strlen($parent) + $this->model->CategoryLength, $data['sort']);
			_DBModTime($this->model->table);
			JSON($res);
		}

		else JSON(false);
	}

	public function PostToggleOnOff(){
		$res = $this->model->DBGet($_POST['category']);
		if(!$res->result) JSON(false, 'ERROR#101');
		else{
			$enabled = $this->model->GetValue('enabled') == 'y' ? 'n' : 'y';
			$this->model->SetValue('enabled', $enabled);
			$res = $this->model->DBUpdate();
			if(strlen($this->model->GetValue('category')) == $this->model->CategoryLength) $dt = null;
			else $dt = $this->model->GetParent($this->model->GetValue('category'));
			$this->model->SetChildEnabled($this->model->GetValue('category'), $enabled == 'y' && (!$dt || ($dt['enabled'] == 'y' && $dt['parent_enabled'] == 'y')) ? 'y' : 'n');

			_DBModTime($this->model->table);

			if(!$res->result) JSON(false, 'ERROR#102');
			else JSON(true, '', array('enabled' => $enabled));
		}
	}
}