<?php
/**
 * Bang Hun.
 * 16.07.10
 */
use \BH_Common as CM;
use \BH_Application as App;
class BH_Category{
	const ROOT_CATEGORY_CODE = '_ROOT';

	/** @var MenuModel */
	public $model;
	public $Name;

	public function __Init(){
		$this->model = App::InitModel('Menu');
	}

	public function Index(){
		App::View($this, $this->model, $this->model->GetChild());
	}

	public function Write(){
		App::$Layout = null;
		if(!isset($_GET['category']) || $_GET['category'] == '') exit;
		$data = $this->model->DBGet($_GET['category']);
		JSON(true, '', App::GetView($this, $this->model, $data));
	}

	public function PostWrite(){
		if(!isset($_POST['category']) || $_POST['category'] == '') exit;

		$this->model->DBGet($_POST['category']);
		$this->model->Need = array('title', 'type',  'controller', 'enabled');
		$res = $this->model->SetPostValues();
		if(!$res->result) JSON(false, $res->message);
		else{
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
			$dbGet = new \BH_DB_Get($this->model->table);
			if($_POST['parent'] === ''){
				$dbGet->AddKey('MAX(category) as category');
				$dbGet->AddWhere('LENGTH(category) = %s', $this->model->CategoryLength);
			}else{
				$dbGet->AddKey('MAX(category) as category');
				$dbGet->AddWhere('LEFT(category, %d) = %s', strlen($_POST['parent']), $_POST['parent']);
				$dbGet->AddWhere('LENGTH(category) = %d', strlen($_POST['parent']) + $this->model->CategoryLength);
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

			$dbGet = new \BH_DB_Get($this->model->table);
			$dbGet->AddWhere('category = %s', $_POST['parent']);
			$dbGet->AddKey('parent_enabled');
			$dbGet->AddKey('enabled');
			$parent = $dbGet->Get();
			$this->model->SetValue('parent_enabled', $parent['parent_enabled'] == 'n' || $parent['enabled'] == 'n' ? 'n' : 'y');

			$res = $this->model->DBInsert();

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
		$res = $this->model->DBUpdate();
		JSON($res->result, $res->message);
	}

	public function PostModifySort(){
		$data = $this->model->DBGet($_POST['category']);
		if($data->result){
			$sort = SetDBInt($_POST['sort']);
			$parentWhere = StrToSql('LEFT(category,%d) = %s', strlen($_POST['parent']), $_POST['parent']);
			$parentWhere .= StrToSql(' AND LENGTH(category) = %d', strlen($_POST['parent']) + $this->model->CategoryLength);

			$res = false;
			if($sort < $this->model->GetValue('sort')){
				$qry = new \BH_DB_Update($this->model->table);
				$qry->SetData('sort', 'sort + 1');
				$qry->AddWhere('sort >= %d', $sort);
				$qry->AddWhere('sort < %d', $this->model->GetValue('sort'));
				$qry->AddWhere($parentWhere);
				$result = $qry->Run();
				$res = $result->result;
			}
			else if($sort > $this->model->GetValue('sort')){
				$qry = new \BH_DB_Update($this->model->table);
				$qry->SetData('sort', 'sort - 1');
				$qry->AddWhere('sort <= %d', $sort);
				$qry->AddWhere('sort > %d', $this->model->GetValue('sort'));
				$qry->AddWhere($parentWhere);
				$result = $qry->Run();
				$res = $result->result;
			}

			if($res){
				$qry = new \BH_DB_Update($this->model->table);
				$qry->SetDataNum('sort', $sort);
				$qry->AddWhere('category = %s', $_POST['category']);
				$qry->AddWhere($parentWhere);
				$result = $qry->Run();
				$res = $result->result;
			}
			JSON($res);
		}else JSON(true, '', $data);
	}

	// 메뉴삭제
	// 하위메뉴까지 삭제
	public function PostDeleteMenu(){
		$dbGet = new \BH_DB_Get($this->model->table);
		$dbGet->SetKey(array('category', 'sort'));
		$dbGet->AddWhere('category = %s', $_POST['category']);
		$data = $dbGet->Get();

		$parent = substr($data['category'], 0, strlen($data['category']) - $this->model->CategoryLength);

		if($data !== false && $data){
			$qry = new \BH_DB_Delete($this->model->table);
			$qry->AddWhere('LEFT(category, %d) = %s', strlen($_POST['category']), $_POST['category']);
			$res = $qry->Run();
			if($res){
				$qry = new \BH_DB_Update($this->model->table);
				$qry->SetData('sort', 'sort - 1');
				$qry->AddWhere('LEFT(category, %d) = %s', strlen($parent), $parent);
				$qry->AddWhere('LENGTH(category) = %d', strlen($parent) + $this->model->CategoryLength);
				$qry->AddWhere('sort > %d', $data['sort']);
				$result = $qry->Run();
				$res = $result->result;
			}
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

			if(!$res->result) JSON(false, 'ERROR#102');
			else JSON(true, '', array('enabled' => $enabled));
		}
	}
}