<?php
/**
 * Bang Hun.
 * 16.07.10
 */

namespace Common;

use \BH_Common as CM;
use \BH_Application as App;
use \DB as DB;

class BH_Category{
	const ROOT_CATEGORY_CODE = '_ROOT';

	/* @var \MenuModel */
	public $model;
	public $Name;

	public function __construct(){
		$this->model = new \MenuModel();
	}

	protected function _MenuChangeAfter(){}

	public function Index(){
		App::View($this->model, $this->model->GetChild());
	}

	public function Write(){
		App::$layout = null;
		if(!isset($_GET['category']) || $_GET['category'] == '') exit;
		$data = $this->ModelDBGet();
		JSON(true, '', App::GetView($this->model, $data));
	}

	public function PostWrite(){
		if(!isset($_POST['category']) || $_POST['category'] == '') exit;

		$this->ModelDBGet();
		$this->model->need = array('title', 'type',  'controller', 'enabled');
		$res = $this->model->SetPostValues();
		if(!$res->result) JSON(false, $res->message);
		else{
			$res = $this->model->DBUpdate();
			if($res->result) $this->_MenuChangeAfter();
			$dt = $this->model->GetParent($this->model->GetValue('category'));
			$this->model->SetChildEnabled($_POST['category'], $_POST['enabled'] == 'y' && (!$dt || ($dt['enabled'] == 'y' && $dt['parent_enabled'] == 'y')) ? 'y' : 'n');
			JSON($res->result, $res->message);
		}

	}

	public function PostInsertMenu()
	{
		$this->model->need = array('title', 'sort', 'enabled');
		$res = $this->model->SetPostValues();
		if(!$res->result) JSON(false, $res->message);
		else {
			$res = $this->_InsertMenuModel(Post('parent'));
			JSON($res->result, '', $res);
		}
	}

	/**
	 * @param string $pCategory
	 * @return \BH_InsertResult
	 */
	public function _InsertMenuModel($pCategory){
		$dbGet = $this->SqlGetQry();
		if($pCategory === ''){
			$dbGet->AddKey('MAX(category) as category');
			$dbGet->AddWhere('LENGTH(category) = %s', $this->model->CategoryLength);
		}else{
			$dbGet->AddKey('MAX(category) as category');
			$dbGet->AddWhere('LEFT(category, %d) = %s', strlen($pCategory), $pCategory);
			$dbGet->AddWhere('LENGTH(category) = %d', strlen($pCategory) + $this->model->CategoryLength);
		}
		$ct = $dbGet->Get();
		if(!strlen($ct['category'])) $newCategory = sprintf('%0'.$this->model->CategoryLength.'d', 0);
		else{
			$newCategory = substr($ct['category'],strlen($pCategory), $this->model->CategoryLength);
			if(!strlen($newCategory)) $newCategory = sprintf('%0'.$this->model->CategoryLength.'d', 0);
			else{
				$newCategory = toBase(to10($newCategory) + 1);
				$newCategory = str_pad($newCategory, $this->model->CategoryLength, '0', STR_PAD_LEFT);
			}
		}
		$this->model->SetValue('category', $pCategory.$newCategory);

		$dbGet = $this->SqlGetQry();
		$dbGet->AddWhere('category = %s', $pCategory);
		$dbGet->AddKey('parent_enabled');
		$dbGet->AddKey('enabled');
		$parent = $dbGet->Get();
		$this->model->SetValue('parent_enabled', $parent['parent_enabled'] == 'n' || $parent['enabled'] == 'n' ? 'n' : 'y');

		$res = $this->model->DBInsert();
		if($res->result) $this->_MenuChangeAfter();

		$res->id = $pCategory.$newCategory;
		return $res;
	}

	public function PostGetChild(){
		$res = $this->model->GetChild($_POST['parent']);
		JSON(true, '', GetDBText($res));
	}

	public function PostModifyTitle(){
		$this->ModelDBGet();
		$this->model->need = array('title');
		if(isset($_POST['title'])) $_POST['title'] = preg_replace('/\|/is', '', $_POST['title']);
		$res = $this->model->SetPostValues();
		if(!$res->result){
			JSON(false, $res->message);
		}
		$res = $this->model->DBUpdate();
		if($res->result) $this->_MenuChangeAfter();
		JSON($res->result, $res->message);
	}

	public function PostModifySort(){
		$data = $this->ModelDBGet();
		if($data->result){
			$sort = SetDBInt($_POST['sort']);
			$parentWhere = StrToSql('LEFT(category,%d) = %s', strlen($_POST['parent']), $_POST['parent']);
			$parentWhere .= StrToSql(' AND LENGTH(category) = %d', strlen($_POST['parent']) + $this->model->CategoryLength);

			$res = false;
			if($sort < $this->model->GetValue('sort')){
				$qry = $this->SqlUpdateQry();
				$qry->SetData('sort', 'sort + 1');
				$qry->AddWhere('sort >= %d', $sort);
				$qry->AddWhere('sort < %d', $this->model->GetValue('sort'));
				$qry->AddWhere($parentWhere);
				$result = $qry->Run();
				$res = $result->result;
			}
			else if($sort > $this->model->GetValue('sort')){
				$qry = $this->SqlUpdateQry();
				$qry->SetData('sort', 'sort - 1');
				$qry->AddWhere('sort <= %d', $sort);
				$qry->AddWhere('sort > %d', $this->model->GetValue('sort'));
				$qry->AddWhere($parentWhere);
				$result = $qry->Run();
				$res = $result->result;
			}

			if($res){
				$qry = $this->SqlUpdateQry();
				$qry->SetDataNum('sort', $sort);
				$qry->AddWhere('category = %s', $_POST['category']);
				$qry->AddWhere($parentWhere);
				$result = $qry->Run();
				$res = $result->result;
				if($res) $this->_MenuChangeAfter();
			}
			JSON($res);
		}else JSON(true, '', $data);
	}

	// 메뉴삭제
	// 하위메뉴까지 삭제
	public function PostDeleteMenu(){
		$dbGet = $this->SqlGetQry();
		$dbGet->SetKey('category', 'sort');
		$dbGet->AddWhere('category = %s', $_POST['category']);
		$data = $dbGet->Get();

		$parent = substr($data['category'], 0, strlen($data['category']) - $this->model->CategoryLength);

		if($data !== false && $data){
			$qry = $this->SqlDeleteQry();
			$qry->AddWhere('LEFT(category, %d) = %s', strlen($_POST['category']), $_POST['category']);
			$res = $qry->Run();
			if($res){
				$qry = $this->SqlUpdateQry();
				$qry->SetData('sort', 'sort - 1');
				$qry->AddWhere('LEFT(category, %d) = %s', strlen($parent), $parent);
				$qry->AddWhere('LENGTH(category) = %d', strlen($parent) + $this->model->CategoryLength);
				$qry->AddWhere('sort > %d', $data['sort']);
				$result = $qry->Run();
				$res = $result->result;
				if($res) $this->_MenuChangeAfter();
			}
			JSON($res);
		}

		else JSON(false);
	}

	public function PostToggleOnOff(){
		$res = $this->ModelDBGet();
		if(!$res->result) JSON(false, 'ERROR#101');
		else{
			$enabled = $this->model->GetValue('enabled') == 'y' ? 'n' : 'y';
			$this->model->SetValue('enabled', $enabled);
			$res = $this->model->DBUpdate();
			if(strlen($this->model->GetValue('category')) == $this->model->CategoryLength) $dt = null;
			else $dt = $this->model->GetParent($this->model->GetValue('category'));
			$this->model->SetChildEnabled($this->model->GetValue('category'), $enabled == 'y' && (!$dt || ($dt['enabled'] == 'y' && $dt['parent_enabled'] == 'y')) ? 'y' : 'n');

			if($res->result) $this->_MenuChangeAfter();

			if(!$res->result) JSON(false, 'ERROR#102');
			else JSON(true, '', array('enabled' => $enabled));
		}
	}

	public function SqlGetQry(){
		return DB::GetQryObj($this->model->table);
	}

	public function SqlUpdateQry(){
		return DB::UpdateQryObj($this->model->table);
	}

	public function SqlDeleteQry(){
		return DB::DeleteQryObj($this->model->table);
	}

	public function ModelDBGet($ct = null){
		if(is_null($ct)) $category = _POSTIS === true ? Post('category') : Get('category');
		else $category = $ct;
		return $this->model->DBGet($category);
	}
}