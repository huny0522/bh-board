<?php
/**
 * Bang Hun.
 * 16.07.10
 */

require_once _DIR.'/Model/Menu.model.php';

class MenuManagerController extends BH_Controller{

	/**
	 * @var MenuModel
	 */
	public $model;

	public function __Init(){
		$this->_Value['NowMenu'] = '004';
		$this->Common->AdminAuth();

		$this->Layout = '_Admin';
		$this->model = new MenuModel();
	}


	public function Index(){
		$data = $this->model->GetChild();
		$this->_View($this->model, $data);
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
		if(!$res->result){
			echo json_encode(array('result' => false, 'message' => $res->message));
		}
		else{
			if(isset($_POST['bid'])) $this->model->SetValue('bid', $_POST['bid']);
			$res = $this->model->DBUpdate();

			$dt = $this->model->GetParent($this->model->GetValue('category'));

			$this->model->SetChildEnabled($_POST['category'], $_POST['enabled'] == 'y' && (!$dt || ($dt['enabled'] == 'y' && $dt['parent_enabled'] == 'y')) ? 'y' : 'n');
			echo json_encode($res);
		}

	}

	public function PostGetBidList(){
		$dbGetList = new BH_DB_GetList();
		if($_POST['type'] == 'board') $dbGetList->table = TABLE_BOARD_MNG;
		else if($_POST['type'] == 'content') $dbGetList->table = TABLE_CONTENT;
		else {
			echo json_encode(array('result' => false, 'message' => 'ERROR #1'));
			exit;
		}

		$dbGetList->SetKey(array('subject', 'bid'));
		$res = array();
		while($row = $dbGetList->Get()){
			$res[] = $row;
		}
		echo json_encode($res);
	}

	public function PostInsertMenu()
	{
		$this->model->Need = array('title', 'sort', 'enabled');
		$res = $this->model->SetPostValues();
		if(!$res->result){
			echo json_encode(array('result' => false, 'message' => $res->message));
		}
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
			$res->id = $_POST['parent'].$newCategory;
			echo json_encode(array('result'=>$res->result, 'data' => $res));
		}
	}

	public function PostGetChild()
	{
		$res = $this->model->GetChild($_POST['parent']);
		echo json_encode(array('result'=>true, 'data' => $res->GetRows()));
	}

	public function PostModifyTitle(){
		$data = $this->model->DBGet($_POST['category']);
		$this->model->SetDBValues($data);
		$this->model->Need = array('title');
		$res = $this->model->SetPostValues();
		if(!$res->result){
			echo json_encode(array('result' => false, 'message' => $res->message));
			exit;
		}
		$res = $this->model->DBUpdate();
		echo json_encode($res);
	}

	public function PostModifySort(){
		$data = $this->model->DBGet($_POST['category']);
		if($data->result){
			$sort = SetDBInt($_POST['sort']);
			$parentWhere = 'LEFT(category,'.strlen($_POST['parent']).') = '.SetDBText($_POST['parent']);
			$parentWhere .= ' AND LENGTH(category) = '.(strlen($_POST['parent']) + $this->model->CategoryLength);

			$res = false;
			if($sort < $this->model->GetValue('sort')){
				$sql = 'UPDATE '.$this->model->table.' SET sort = sort + 1 WHERE '.$parentWhere.' AND sort >= '.$sort.' AND sort < '.$this->model->GetValue('sort');
				$res = SqlQuery($sql);
			}else if($sort > $this->model->GetValue('sort')){
				$sql = 'UPDATE '.$this->model->table.' SET sort = sort - 1 WHERE '.$parentWhere.' AND sort <= '.$sort.' AND sort > '.$this->model->GetValue('sort');
				$res = SqlQuery($sql);
			}
			if($res){
				$sql = 'UPDATE '.$this->model->table.' SET sort = '.$sort.' WHERE '.$parentWhere.' AND category = '.SetDBText($_POST['category']);
				$res = SqlQuery($sql);
			}
			echo json_encode(array('result' => $res));
		}else{
			echo json_encode($data);
		}
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
			$sql = 'DELETE FROM '.$this->model->table.' WHERE LEFT(category, '.strlen($_POST['category']).') = '.SetDBText($_POST['category']);

			$res = SqlQuery($sql);
			if($res){
				$sql = 'UPDATE '.$this->model->table.' SET sort = sort - 1 WHERE LEFT(category, '.strlen($parent).') = '.SetDBText($parent).' AND LENGTH(category) = '.(strlen($parent) + $this->model->CategoryLength).' AND sort > '.$data['sort'];
				$res = SqlQuery($sql);
			}
			echo json_encode(array('result' => $res));
		}

		else echo json_encode(array('result' => false));
	}

	public function PostToggleOnOff(){
		$res = $this->model->DBGet($_POST['category']);
		if(!$res->result){
			echo json_encode(array('result' => false, 'message' => 'ERROR#101'));
		}else{
			$enabled = $this->model->GetValue('enabled') == 'y' ? 'n' : 'y';
			$this->model->SetValue('enabled', $enabled);
			$res = $this->model->DBUpdate();
			if(strlen($this->model->GetValue('category')) == $this->model->CategoryLength) $dt = null;
			else $dt = $this->model->GetParent($this->model->GetValue('category'));
			$this->model->SetChildEnabled($this->model->GetValue('category'), $enabled == 'y' && (!$dt || ($dt['enabled'] == 'y' && $dt['parent_enabled'] == 'y')) ? 'y' : 'n');
			if(!$res->result){
				echo json_encode(array('result' => false, 'message' => 'ERROR#102'));
			}else{
				echo json_encode(array('result' => true, 'data' => array('enabled' => $enabled)));
			}
		}
	}
}