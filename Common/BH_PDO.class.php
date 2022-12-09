<?php
/**
 * Bang Hun.
 * 16.07.10
 */

//namespace BH;

class DB{
	const DefaultConnName = 'MY';
	const BIND_NAME = ':BH_PARAMETER';
	/**
	 * @var self
	 */
	private static $instance;

	/** @var \PDO[] */
	private static $conn = array();
	private static $connName = '';
	private static $connectionInfo = array();
	private static $transactionCounter = array();

	public static $bindNum = 0;

	private function __construct(){
		require _DIR . '/Custom/db.info.php';
	}

	public function __destruct(){
		foreach(self::$conn as $k => &$v) $v = null;
	}

	public static function _destroy(){
		if(isset(self::$instance)){
			self::$instance = null;
		}
	}

	/**
	 * DB 컨넥션 및 인스턴스 반환
	 * @param string $connName
	 * @return $this
	 */
	public static function &SQL($connName = self::DefaultConnName){
		self::$connName = $connName === '' ? self::DefaultConnName : $connName;
		if (!isset(self::$instance) || is_null(self::$instance)) self::$instance = new self();

		if(!isset(self::$conn[self::$connName])){
			if(isset(self::$connectionInfo[self::$connName])){
				try {
					$opt = isset(self::$connectionInfo[self::$connName]['option']) ? isset(self::$connectionInfo[self::$connName]['dsn']) : null;
					if(isset(self::$connectionInfo[self::$connName]['dsn'])){
						self::$conn[self::$connName] = new PDO(self::$connectionInfo[self::$connName]['dsn'], self::$connectionInfo[self::$connName]['userName'], self::$connectionInfo[self::$connName]['userPassword'], $opt);
					}
					else{
						$dsn = 'mysql:host='.self::$connectionInfo[self::$connName]['hostName'] .
							';dbname='.self::$connectionInfo[self::$connName]['dbName'] .
							(isset(self::$connectionInfo[self::$connName]['port']) ? ';port=' . self::$connectionInfo[self::$connName]['port'] : '');
						self::$conn[self::$connName] = new PDO($dsn, self::$connectionInfo[self::$connName]['userName'], self::$connectionInfo[self::$connName]['userPassword'], $opt);
					}
				}
				catch(PDOException $e) {
					echo $e->getMessage();
					exit;
				}

				self::$conn[self::$connName]->exec("set names utf8mb4");
			}
			else{ echo('NOT_DEFINE_DB'); exit; }
		}
		return self::$instance;
	}

	/**
	 * @param string $connName
	 * @return PDO
	 */
	public static function &PDO($connName = self::DefaultConnName){
		self::SQL($connName);
		return self::$conn[self::$connName];
	}

	public static function BeginTransaction($connName = null){
		if(is_null($connName)) $connName = self::$connName;
		if(!isset(self::$transactionCounter[$connName]) || !self::$transactionCounter[$connName]){
			self::$transactionCounter[$connName] = 0;
			self::$conn[$connName]->beginTransaction();
		}
		self::$transactionCounter[$connName]++;
	}

	public static function Commit($connName = null){
		if(is_null($connName)) $connName = self::$connName;
		if(!isset(self::$transactionCounter[$connName])) self::$transactionCounter[$connName] = 1;
		self::$transactionCounter[$connName]--;
		if(!self::$transactionCounter[$connName]) return self::$conn[$connName]->commit();
		return false;
	}

	/**
	 * @param string $table
	 * @return bool
	 */
	public function TableExists($table){
		$exists = self::NumRows('SHOW TABLES LIKE \'' . $table . '\'');
		if($exists) return true;
		else return false;
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @return bool
	 */
	public function ColumnExists($table, $column){
		$exists = self::NumRows("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
		if($exists) return true;
		else return false;
	}

	/**
	 * @param string|PDOStatement $qry
	 * @return bool|int
	 */
	public function NumRows($qry){
		if(is_string($qry)) $qry = self::Query($qry);
		if($qry === false) return false;

		return $qry->rowCount();
	}

	public function Free($qry){
		if(is_bool($qry)) return;
		$qry->closeCursor();
	}

	/**
	 * @param string $str
	 * @param boolean $dieIs
	 * @return PDOStatement
	 */
	public function Query($str, $dieIs = true){
		if(is_array($str)) $res = self::StrToPDO($str);
		else{
			$args = func_get_args();
			$end = end($args);
			if($end === true || $end === false) $dieIs = array_pop($args);
			else $dieIs = true;
			$res = self::StrToPDO($args);
		}

		$qry = self::$conn[self::$connName]->prepare($res[0]);
		foreach($res[1] as $k => $v){
			$qry->bindParam($k, $v[0], $v[1]);
		}
		if(\BHG::$isDeveloper === true){
			$res = $qry->execute();
			if(!$res){
				if($dieIs) PrintError($qry->errorInfo());
				else return false;
			}
		}
		else $qry->execute() or ($dieIs ? die('QUERY ERROR') : false);
		return $qry;
	}

	/**
	 * @param string|PDOStatement $qry
	 * @return mixed
	 */
	public function Fetch($qry){
		if(!isset($qry) || $qry === false || empty($qry)){
			if(\BHG::$isDeveloper === true) echo 'FETCH ASSOC MESSAGE(DEBUG ON) : <b>query is empty( or null, false).</b><br>';
			return false;
		}
		$string_is = false;
		if(is_string($qry) || is_array($qry)){
			$qry = self::Query(is_array($qry) ? $qry : func_get_args());
			if($qry === false) return false;
			$string_is = true;
		}

		$r = $qry->fetch(PDO::FETCH_ASSOC);
		if($string_is) self::Free($qry);

		return $r;
	}

	/**
	 * @return PDO
	 */
	public static function &GetConn(){
		return self::$conn[self::$connName];
	}

	/**
	 * @param string $table
	 * @return \BH_DB_Get
	 */
	public static function &GetQryObj($table = ''){
		$instance = new BH_DB_Get();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	/**
	 * @param string $table
	 * @return \BH_DB_GetList
	 */
	public static function &GetListQryObj($table = ''){
		$instance = new BH_DB_GetList();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	/**
	 * @param string $table
	 * @return \BH_DB_GetListWithPage
	 */
	public static function &GetListPageQryObj($table = ''){
		$instance = new BH_DB_GetListWithPage();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	/**
	 * @param string $table
	 * @return \BH_DB_Update
	 */
	public static function &UpdateQryObj($table = ''){
		$instance = new BH_DB_Update();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	/**
	 * @param string $table
	 * @return \BH_DB_Insert
	 */
	public static function &InsertQryObj($table = ''){
		$instance = new BH_DB_Insert($table);
		return $instance;
	}

	/**
	 * @param string $table
	 * @return \BH_DB_Delete
	 */
	public static function &DeleteQryObj($table = ''){
		$instance = new BH_DB_Delete();
		call_user_func_array(array($instance, 'AddTable'), func_get_args());
		return $instance;
	}

	public static function StrToPDO($args){
		$validateOk = new \BH_Result();
		$validateOk->result = true;

		if(!is_array($args)) $args = func_get_args();

		$bindParam = array();

		$n = sizeof($args);
		if(!$n) return false;

		for($i = 0; $i < $n; $i++){
			if(is_object($args[$i]) && get_class($args[$i]) === 'BH_ModelData')
				$args[$i] = '`' . $args[$i]->parent->naming . '`.`' . $args[$i]->GetKeyName(). '`';

			else if(is_object($args[$i]) && isset($args[$i]->isBHModel))
				$args[$i] = '`' . $args[$i]->table . '` `' . $args[$i]->naming . '`';

		}

		if($n == 1) return array($args[0], $bindParam);
		else{
			$prn = 1;
			$ex = explode('%', $args[0]);
			$m = sizeof($ex);
			if($m < 2) return array($args[0], $bindParam);

			$str = array($ex[0]);

			for($i = 1; $i < $m; $i++){
				if(!strlen($ex[$i])){
					$str[] = '%';
					continue;
				}
				$ns = $ex[$i][0];

				if($ns === 's'){
					if(is_array($args[$prn])){
						$bindNames = array();
						foreach($args[$prn] as $row){
							$bindNames[] = $k = self::BIND_NAME.self::$bindNum.'X';
							$bindParam[$k] = array($row, \PDO::PARAM_STR);
							self::$bindNum++;
						}
						$str[] = implode(',', $bindNames);
					}
					else{
						$str[] = $t = self::BIND_NAME.self::$bindNum.'X';
						$bindParam[$t] = array($args[$prn], \PDO::PARAM_STR);
						self::$bindNum++;
					}
					$str[] = substr($ex[$i], 1);
					$prn++;
				}
				else if($ns === 'f'){
					$res = ValidateFloat($args[$prn]);
					if(!$res->result) $validateOk = $res;
					$str[] = is_array($args[$prn]) ? implode(',', $args[$prn]) : $args[$prn];
					$str[] = substr($ex[$i], 1);
					$prn++;
				}
				else if($ns === 'd'){
					$res = ValidateInt($args[$prn]);
					if(!$res->result) $validateOk = $res;
					$str[] = is_array($args[$prn]) ? implode(',', $args[$prn]) : $args[$prn];
					$str[] = substr($ex[$i], 1);
					$prn++;
				}
				else if($ns === '1'){
					if(is_object($args[$prn]) && (get_class($args[$prn]) === 'BH_DB_Get' || get_class($args[$prn]) === 'BH_DB_GetList')){
						$args[$prn]->QryToSql();
						$bindParam = array_merge($bindParam, $args[$prn]->bindParam);
						$str[] = $args[$prn]->sql;
					}
					else $str[] = is_array($args[$prn]) ? implode(',', $args[$prn]) : $args[$prn];
					$str[] = substr($ex[$i], 1);
					$prn++;
				}
				else{
					$str[] = '%';
					$str[] = $ex[$i];
				}
			}

			$sql = str_replace(array('%\s', '%\f', '%\d', '%\1'), array('%s', '%f', '%d', '%1'), implode($str));
			if($validateOk->result) return array($sql, $bindParam);
			else URLReplace(-1, $validateOk->message.(\BHG::$isDeveloper === true ? '['.$args[0].']' : ''));

		}
	}
}

class BH_DB_Get{
	public $pos = 0;
	public $table = array();
	public $noWhereTable = '';
	public $sql = '';
	public $showError = true;
	public $test = false;
	public $sort = '';
	public $group = '';

	private $isRunGet = false;

	/**
	 * @var PDOStatement
	 */
	public $query = null;
	protected $having = array();
	protected $where = array();
	protected $key = array();
	protected $connName = '';

	public $bindParam = array();

	public function  __construct($table = ''){
		if($table !== ''){
			$w = $this->StrToPDO(func_get_args());
			if($w !== false) $this->table[] = array('sql' => $w);
		}
		$this->connName = DB::DefaultConnName;
	}

	public function __destruct(){
		if($this->query) DB::Sql($this->connName)->Free($this->query);
	}

	public function Retry(){
		$this->isRunGet = false;
		return $this;
	}

	public function StrToPDO($args){
		$res = DB::StrToPDO(is_array($args) ? $args : func_get_args());
		if(sizeof($res[1])) $this->bindParam = $this->bindParam + $res[1];
		return $res[0];
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &AddTable($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->table[] = array('sql' => $w);
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $str
	 * @return $this
	 */
	public function &AddTableWithKey($key, $str){
		$args = func_get_args();
		array_shift($args);
		$w = $this->StrToPDO($args);
		if($w !== false) $this->table[$key] = array('sql' => $w);
		return $this;
	}

	public function RemoveTableByKey($key){
		unset($this->table[$key]);
		return $this;
	}

	/**
	 * @param string|callable $str
	 * @return $this
	 */
	public function &AddWhere($str){
		if(is_callable($str)) $this->where[] = '(' . $str($this) . ')';
		else{
			$w = $this->StrToPDO(func_get_args());
			if($w !== false) $this->where[] = '('.$w.')';
		}
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &AddHaving($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->having[] = '('.$w.')';
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetKey($str){
		$args = func_get_args();
		foreach($args as $k => $keys){
			if(is_string($keys)){
				if($k) $this->key[] = $keys;
				else $this->key = array($keys);
			}
			else if(is_array($keys)){
				if($k){ foreach($keys as $row) $this->key[] = $row; }
				else $this->key = $keys;
			}
		}
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &AddKey($str){
		$args = func_get_args();
		foreach($args as $keys){
			if(is_string($keys)) $this->key[] = $keys;
			else if(is_array($keys)){ foreach($keys as $row) $this->key[] = $row;}
		}
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &AddKeySql($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->key[] = $w;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetGroup($str){
		$sql = $this->StrToPDO(func_get_args());
		if($sql !== false) $this->group = $sql;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetSort($str){
		$this->sort = $this->StrToPDO(func_get_args());
		return $this;
	}

	protected function TableImplode($incNoWhere = true){
		if(is_array($this->table)){
			return implode(' ', array_map(function($ent) use ($incNoWhere){
				if($incNoWhere) return $ent['sql'];
				if(!isset($ent['noWhere']) || $ent['noWhere'] !== true) return $ent['sql'];
				return '';
			}, $this->table));
		}
		return $this->table;
	}

	public function QryToSql(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)) $where = ' WHERE ' . implode(' AND ', $this->where);
		$having = '';
		if(isset($this->having) && is_array($this->having) && sizeof($this->having)) $having = ' HAVING ' . implode(' AND ', $this->having);

		if(isset($this->key) && is_array($this->key) && sizeof($this->key)) $key = implode(',', $this->key);
		else $key = '*';


		$this->sql = 'SELECT {{space1}} '.$key.' {{space2}} FROM {{space3}} '.$this->TableImplode().' {{space4}} '.$where;
		if($this->group) $this->sql .= ' {{space5}} GROUP BY ' . $this->group;
		$this->sql .= $having;
		if($this->sort) $this->sql .= ' {{space6}} ORDER BY ' . $this->sort;
		$this->sql .= ' {{space7}} LIMIT ' . ToInt($this->pos) . ', 1';

		if(is_callable($func)) $func($this->sql);
		$this->sql = preg_replace('#{{space[0-9]+}}#s', '', $this->sql);

		$this->sql = trim($this->sql);
	}

	/**
	 * @var callable(&$sql) $func
	 * @return bool|array
	 */
	public function Get(){
		if($this->isRunGet) return false;
		$this->isRunGet = true;
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		if($this->test && \BHG::$isDeveloper === true){
			echo $this->GetTestSql($func);
			exit;
		}

		$this->QryToSql($func);

		$this->query = DB::PDO($this->connName)->prepare($this->sql);
		foreach($this->bindParam as $k => $v) if(strpos($this->sql, $k) !== false) $this->query->bindParam($k, $v[0], $v[1]);

		if($this->query->execute()){
			$row = $this->query->fetch(PDO::FETCH_ASSOC);
			if($row){
				$this->query->closeCursor();
				return $row;
			}
		}
		else if(\BHG::$isDeveloper === true && $this->showError && BH_Application::$showError) PrintError($this->query->errorInfo());
		return false;
	}

	public function PrintTest(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);
		if(\BHG::$isDeveloper === true) echo $this->GetTestSql($func);
	}

	public function GetTestSql(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$this->QryToSql($func);
		$sql = $this->sql;
		foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.str_replace("'", "\\'", $v[0]).'\'', $sql);
		return $sql;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetShowError($bool = true){
		$this->showError = $bool;
		return $this;
	}

	/**
	 * @param int $num
	 * @return $this
	 */
	public function &SetPos($num = 0){
		$this->pos = $num;
		return $this;
	}
}

class BH_DB_GetList extends BH_DB_Get{
	public $limit = '';
	protected $pointer = -1;

	public $result = false;
	public $data = array();
	public $drawRowsIs = false;
	private $runIs = false;

	/**
	 * @return $this
	 */
	public function &DrawRows(){
		if(!$this->runIs) $this->Run();
		$this->drawRowsIs = true;
		while($row = $this->Get()){
			$this->data[]= $row;
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function &GetRows(){
		if(!$this->runIs) $this->Run();

		$this->DrawRows();
		return $this->data;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetLimit($str){
		$sql = $this->StrToPDO(func_get_args());
		if($sql !== false) $this->limit = $sql;
		return $this;
	}

	public function QryToSql(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)){
			$where = ' WHERE ' . implode(' AND ', $this->where);
		}

		$having = '';
		if(isset($this->having) && is_array($this->having) && sizeof($this->having)){
			$having = ' HAVING ' . implode(' AND ', $this->having);
		}

		if(isset($this->key) && is_array($this->key) && sizeof($this->key)){
			$key = implode(',', $this->key);
		}
		else{
			$key = '*';
		}

		$this->sql = 'SELECT {{space1}} '.$key.' {{space2}} FROM {{space3}} '.$this->TableImplode().' {{space4}} '.$where;

		if($this->group) $this->sql .= ' {{space5}} GROUP BY ' . $this->group;
		$this->sql .= $having;
		if($this->sort) $this->sql .= ' {{space6}} ORDER BY ' . $this->sort;
		if($this->limit) $this->sql .= ' {{space7}} LIMIT ' . $this->limit;

		if(is_callable($func)) $func($this->sql);
		$this->sql = preg_replace('#{{space[0-9]+}}#s', '', $this->sql);

		$this->sql = trim($this->sql);
	}
	/**
	 * @var callable(&$sql) $func
	 * @return $this
	 */
	function &Run(){
		$this->runIs = true;

		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		if($this->test && \BHG::$isDeveloper === true){
			echo $this->GetTestSql($func);
			exit;
		}

		$this->QryToSql($func);

		$this->query = DB::PDO($this->connName)->prepare($this->sql);
		foreach($this->bindParam as $k => $v) if(strpos($this->sql, $k) !== false) $this->query->bindParam($k, $v[0], $v[1]);

		if($this->query->execute()) $this->result = true;
		else{
			if(\BHG::$isDeveloper === true && $this->showError && BH_Application::$showError) PrintError($this->query->errorInfo());
			$this->result = false;
		}

		return $this;
	}

	public function Get(){
		if(!$this->runIs) $this->Run();
		$res = $this->query ? $this->query->fetch(PDO::FETCH_ASSOC) : false;
		return $res;
	}
}

class BH_DB_GetListWithPage extends BH_DB_Get{
	public $articleCount = 10;
	public $limit = '';
	public $page = 1;
	public $pageCount = 10;
	public $pageUrl = '';
	public $CountKey = '';
	public $SubCountKey = array();
	public $drawRowsIs = false;
	private $runIs = false;

	// Result
	public $result = false;
	public $countResult = false;
	public $data = array();
	public $totalRecord = '';
	public $beginNum = '';
	public $pageHtml = '';
	public $rPageData = array();
	public $pageQueryParam = 'page';

	private $noGroupCount = false;

	/**
	 * @var array $pageData = ['articleCount' => 10, 'pageCount' => 10, 'page' => 10, 'pageUrl' => 10, 'totalRecord' => 10, 'img' => ['first' => 'First', 'prev' => 'Prev', 'back' => 'Back', 'forw' => 'Forward', 'next' => 'Next', 'last' => 'Last'], 'afterHtml' => '']
	 */
	public static $pageData = array();
	public static $pageNumberView = 10;
	/**
	 * @var bool
	 */

	public function __construct($table = ''){
		$this->pageCount = self::$pageNumberView;
		parent::__construct($table);
	}


	/**
	 * @param string $str
	 * @return $this
	 */
	public function &AddTable($str){
		$arr = func_get_args();
		$noWhere = false;
		if(is_bool(end($arr))) $noWhere = array_pop($arr);
		$w = $this->StrToPDO($arr);
		if($w !== false) $this->table[] = array('noWhere' => $noWhere, 'sql' => $w);
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetLimit($str){
		$sql = $this->StrToPDO(func_get_args());
		if($sql !== false) $this->limit = $sql;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetPage($str){
		$this->page = $str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetPageQueryName($str = 'page'){
		$this->pageQueryParam = $str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetPageUrl($str){
		$this->pageUrl = $str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetPageCount($str){
		$this->pageCount = (int)$str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetArticleCount($str){
		$this->articleCount = (int)$str;
		return $this;
	}

	/**
	 * @param string $asName
	 * @param string $str
	 * @return $this
	 */
	public function &SetSubCountKey($asName, $str){
		$this->SubCountKey[$asName]= $str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetCountKey($str){
		$this->CountKey = $str;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetNoGroupCount($bool = true){
		$this->noGroupCount = $bool;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function &DrawRows(){
		if(!$this->runIs) $this->Run();
		$this->drawRowsIs = true;
		while($row = $this->Get()){
			$this->data[]= $row;
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function &GetRows(){
		if(!$this->runIs) $this->Run();
		$this->DrawRows();
		return $this->data;
	}

	/**
	 * @var callable(&$sql) $func
	 * @return $this
	 */

	public function QryToSql(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		if($this->page < 1) $this->page = 1;
		$nowPage = $this->page - 1;

		$beginPage = $nowPage * $this->articleCount;

		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)){
			$where = ' WHERE ' . implode(' AND ', $this->where);
		}

		$having = '';
		if(isset($this->having) && is_array($this->having) && sizeof($this->having)){
			$having = ' HAVING ' . implode(' AND ', $this->having);
		}

		if(isset($this->key) && is_array($this->key) && sizeof($this->key)){
			$key = implode(',', $this->key);
		}
		else{
			$key = '*';
		}

		$subCnt_sql = '';
		$subCnt_sql2 = '';
		if(sizeof($this->SubCountKey)) foreach($this->SubCountKey as $k=>$v){
			$subCnt_sql .= ', '.$v.' as '.$k;
			$subCnt_sql2 .= ', COUNT('.$k.') as '.$k;
		}

		$sql_cnt = 'SELECT COUNT('.($this->CountKey ? $this->CountKey : '*').') as cnt'.$subCnt_sql.' FROM '.$this->TableImplode(false).' '.$where;
		$this->sql = 'SELECT {{space1}} '.$key.' {{space2}} FROM {{space3}} '.$this->TableImplode().' {{space4}} '.$where;
		if($this->group){
			$this->sql .= ' {{space5}} GROUP BY ' . $this->group;
			if(!$this->noGroupCount) $sql_cnt .= ' GROUP BY ' . $this->group;
		}

		$sql_cnt .= $having;
		$this->sql .= $having;

		if($this->sort)
			$this->sql .= ' {{space6}} ORDER BY ' . $this->sort;

		if($this->limit)
			$this->sql .= ' {{space7}} LIMIT ' . $this->limit;
		else if($this->articleCount)
			$this->sql .= ' {{space7}} LIMIT '.$beginPage.', ' . $this->articleCount;

		if(is_callable($func)) $func($this->sql);
		$this->sql = preg_replace('#{{space[0-9]+}}#s', '', $this->sql);

		$this->sql = trim($this->sql);

		return array(
			'subCnt_sql2' => $subCnt_sql2,
			'sql_cnt' => $sql_cnt,
			'nowPage' => $nowPage
		);
	}
	public function &Run(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$this->runIs = true;

		if($this->test && \BHG::$isDeveloper === true){
			echo $this->GetTestSql($func);
			exit;
		}

		$sqlData = $this->QryToSql($func);
		$subCnt_sql2 = $sqlData['subCnt_sql2'];
		$sql_cnt = $sqlData['sql_cnt'];
		$nowPage = $sqlData['nowPage'];

		$cntSql = $this->group && !$this->noGroupCount ? 'SELECT COUNT(*) as cnt'.$subCnt_sql2.' FROM ('.$sql_cnt.') AS x' : $sql_cnt;

		$qry = DB::PDO($this->connName)->prepare($cntSql);
		foreach($this->bindParam as $k => $v) if(strpos($cntSql, $k) !== false) $qry->bindParam($k, $v[0], $v[1]);
		if($qry->execute()){
			$this->countResult = $qry->fetch(PDO::FETCH_ASSOC);
			$totalRecord = $this->countResult['cnt']; //total값 구함
		}
		else $totalRecord = 0;

		$this->totalRecord = $totalRecord;
		$this->beginNum = $totalRecord - ($nowPage * $this->articleCount);

		$this->query = DB::PDO($this->connName)->prepare($this->sql);
		foreach($this->bindParam as $k => $v) if(strpos($this->sql, $k) !== false) $this->query->bindParam($k, $v[0], $v[1]);

		$pageData = self::$pageData;
		if($this->query->execute()){
			$pageData['articleCount'] = $this->articleCount;
			$pageData['pageCount'] = $this->pageCount;
			$pageData['page'] = $this->page ?: 1;
			$pageData['pageUrl'] = $this->pageUrl;
			$pageData['totalRecord'] = $totalRecord;

			$this->pageHtml = $this->SqlGetPage($pageData);
			$this->SetPageData($pageData);

			$this->result = true;
		}
		else{
			if(\BHG::$isDeveloper === true && $this->showError && BH_Application::$showError) PrintError($this->query->errorInfo());
			$this->result = false;
		}
		return $this;
	}

	public function GetTestSql(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$sqlData = $this->QryToSql($func);
		$subCnt_sql2 = $sqlData['subCnt_sql2'];
		$sql_cnt = $sqlData['sql_cnt'];
		$sql = $this->sql;
		if($this->group && !$this->noGroupCount) $sql_cnt = 'SELECT COUNT(*) as cnt'.$subCnt_sql2.' FROM ('.$sql_cnt.') AS x';
		foreach($this->bindParam as $k => $v) $sql_cnt = str_replace($k, '\''.str_replace("'", "\\'", $v[0]).'\'', $sql_cnt);
		foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.str_replace("'", "\\'", $v[0]).'\'', $sql);
		return $sql . ';' . PHP_EOL . PHP_EOL . $sql_cnt . ';';
	}

	/**
	 * @return bool
	 */
	public function GetCountResult(){
		if(!$this->runIs) $this->Run();
		return $this->countResult;
	}

	/**
	 * @return int
	 */
	public function GetTotalRecord(){
		if(!$this->runIs) $this->Run();
		return $this->totalRecord;
	}

	/**
	 * @return int
	 */
	public function GetBeginNum(){
		if(!$this->runIs) $this->Run();
		return $this->beginNum;
	}

	/**
	 * @return string
	 */
	public function &GetPageHtml(){
		if(!$this->runIs) $this->Run();
		return $this->pageHtml;
	}

	public function Get(){
		if(!$this->runIs) $this->Run();

		$res = $this->query ? $this->query->fetch(PDO::FETCH_ASSOC) : false;
		return $res;
	}

	/**
	 * @param array $pageParams = ['articleCount' => 10, 'pageCount' => 10, 'page' => 10, 'pageUrl' => 10, 'totalRecord' => 10, 'img' => ['first' => 'First', 'prev' => 'Prev', 'back' => 'Back', 'forw' => 'Forward', 'next' => 'Next', 'last' => 'Last'], 'afterHtml' => '']
	 * @return string
	 */
	public function SqlGetPage($pageParams){
		$linkfirst =(strpos($pageParams['pageUrl'], '?') === false) ? '?' . $this->pageQueryParam . '=' : '&' . $this->pageQueryParam . '=';

		if(!$pageParams['articleCount']) $pageParams['articleCount'] = 10;

		// 전체페이지
		if(!isset($pageParams['img']))
			$pageParams['img'] = array('first' => 'First', 'prev' => 'Prev', 'back' => 'Back', 'forw' => 'Forward', 'next' => 'Next', 'last' => 'Last');
		if(!isset($pageParams['img']['first'])) $pageParams['img']['first'] = 'First';
		if(!isset($pageParams['img']['prev'])) $pageParams['img']['prev'] = 'Prev';
		if(!isset($pageParams['img']['back'])) $pageParams['img']['back'] = 'Back';
		if(!isset($pageParams['img']['forw'])) $pageParams['img']['forw'] = 'Forward';
		if(!isset($pageParams['img']['next'])) $pageParams['img']['next'] = 'Next';
		if(!isset($pageParams['img']['last'])) $pageParams['img']['last'] = 'Last';
		if(!$pageParams['totalRecord'])
			return '';
		$nowPage = $pageParams['page'] - 1;

		$pageAll = ceil($pageParams['totalRecord'] / $pageParams['articleCount']);

		// 뷰첫페이지
		$pageFirst = $nowPage - ($nowPage % $pageParams['pageCount']) + 1;
		// 뷰라스트페이지
		$pageLast = $pageFirst + $pageParams['pageCount'] - 1;
		if($pageLast > $pageAll)
			$pageLast = $pageAll;

		// 첫페이지
		$tag = $pageParams['page'] > 1 ? 'a' : 'span';
		$pageHTML = ' <'.$tag.' class="first" href="' . $pageParams['pageUrl'] . $linkfirst. '1" data-page="1">' . $pageParams['img']['first'] . '</'.$tag.'> ';


		// 이전 페이지목록
		$tag = $pageFirst > 1 ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="prev" href="' . $pageParams['pageUrl'] . $linkfirst.  ($pageFirst - 1) . '" data-page="' . ($pageFirst - 1) . '">' . $pageParams['img']['prev'] . '</'.$tag.'> ';

		// 이전 페이지
		$tag = $pageParams['page'] > 1 ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="prevp" href="' . $pageParams['pageUrl'] . $linkfirst. ($pageParams['page'] - 1) . '" data-page="' . ($pageParams['page'] - 1) . '">' . $pageParams['img']['back'] . '</'.$tag.'> ';

		for($i = $pageFirst; $i <= $pageLast; $i++){
			$pageHTML .= ($i == $pageParams['page']) ? ' <strong>' . $i . '</strong> ' : ' <a href="' . $pageParams['pageUrl'] . $linkfirst. $i . '" data-page="' . $i . '">' . $i . '</a> ';
		}

		// 다음 페이지
		$tag = $pageParams['page'] < $pageAll ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="nextp" href="' . $pageParams['pageUrl'] . $linkfirst. ($pageParams['page'] + 1) . '" data-page="' . ($pageParams['page'] + 1) . '">' . $pageParams['img']['forw'] . '</'.$tag.'> ';

		// 다음 페이지목록
		$tag = $pageLast < $pageAll ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="next" href="' . $pageParams['pageUrl'] . $linkfirst. ($pageLast + 1) . '" data-page="' . ($pageLast + 1) . '">' . $pageParams['img']['next'] . '</'.$tag.'> ';

		// 끝 페이지
		$tag = $pageParams['page'] < $pageAll ? 'a' : 'span';
		$pageHTML .= ' <'.$tag.' class="last" href="' . $pageParams['pageUrl'] . $linkfirst. $pageAll . '" data-page="' . $pageAll . '">' . $pageParams['img']['last'] . '</'.$tag.'> ';

		return '<div class="paging">'.$pageHTML.'</div>' . (isset($pageParams['afterHtml']) ? $pageParams['afterHtml'] : '');
	}

	/**
	 * @param array $pageParams = ['articleCount' => 10, 'pageCount' => 10, 'page' => 10, 'pageUrl' => 10, 'totalRecord' => 10, 'img' => ['first' => 'First', 'prev' => 'Prev', 'back' => 'Back', 'forw' => 'Forward', 'next' => 'Next', 'last' => 'Last'], 'afterHtml' => '']
	 * @return array
	 */
	public function SetPageData($pageParams){
		if(!$pageParams['articleCount']) $pageParams['articleCount'] = 10;

		// 전체페이지
		if(!$pageParams['totalRecord']) return $this->rPageData;
		$nowPage = $pageParams['page'] - 1;

		$pageAll = ceil($pageParams['totalRecord'] / $pageParams['articleCount']);

		// 뷰첫페이지
		$pageFirst = $nowPage - ($nowPage % $pageParams['pageCount']) + 1;
		// 뷰라스트페이지
		$pageLast = $pageFirst + $pageParams['pageCount'] - 1;
		if($pageLast > $pageAll)
			$pageLast = $pageAll;

		// 첫페이지
		$this->rPageData[]= ['name' => 'first', 'page' => 1, 'isActive' => $pageParams['page'] > 1];
		// 이전 페이지목록
		$this->rPageData[]= ['name' => 'prev', 'page' => ($pageFirst - 1), 'isActive' => $pageFirst > 1];
		// 이전 페이지
		$this->rPageData[]= ['name' => 'prevp', 'page' => ($pageParams['page'] - 1), 'isActive' => $pageParams['page'] > 1];

		for($i = $pageFirst; $i <= $pageLast; $i++){
			$this->rPageData[] = ['name' => 'page', 'page' => $i, 'isActive' => $i != $pageParams['page']];
		}

		// 다음 페이지
		$this->rPageData[]= ['name' => 'nextp', 'page' => ($pageParams['page'] + 1), 'isActive' => $pageParams['page'] < $pageAll];
		// 다음 페이지목록
		$this->rPageData[]= ['name' => 'next', 'page' => ($pageLast + 1), 'isActive' => $pageLast < $pageAll];
		// 끝 페이지
		$this->rPageData[]= ['name' => 'last', 'page' => $pageAll, 'isActive' => $pageParams['page'] < $pageAll];
	}

	public function GetPageData(){
		return $this->rPageData;
	}
}

class BH_DB_Insert{
	public $table = '';
	public $decrement = '';
	public $data = array();
	public $sql = '';
	public $showError = true;
	public $test = false;
	public $MAXInt = _DBMAXINT;
	private $where = array();
	private $MultiNames = '';
	private $MultiValues = array();
	private $connName = '';
	private $duplicateData = array();
	private $otherKeys = array();
	private $isDecrement = true;

	protected $tableBindParam = array();
	protected $whereBindParam = array();
	protected $bindParam = array();

	public $query;

	public function  __construct($table = ''){
		if(is_object($table) && isset($table->isBHModel)) $this->table = $table->table;
		else if($table !== '') $this->table = $this->StrToPDO(func_get_args());
		$this->connName = DB::DefaultConnName;
	}

	public function StrToPDO($args){
		$res = DB::StrToPDO(is_array($args) ? $args : func_get_args());
		if(sizeof($res[1])) $this->bindParam = $this->bindParam + $res[1];
		return $res[0];
	}

	/**
	 * @param string $key
	 * @param string $val
	 * @return $this
	 */
	public function &SetOnDuplicateData($key, $val){
		$this->duplicateData[$key] = $val;
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $val
	 * @return $this
	 */
	public function &SetOnDuplicateDataStr($key, $val){
		$this->duplicateData[$key] = $this->StrToPDO('%s', $val);
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $val
	 * @return $this
	 */
	public function &SetOnDuplicateDataNum($key, $val){
		$this->duplicateData[$key] = SetDBFloat($val);
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &AddTable($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->table .= ' '.$w;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetDecrementKey($str){
		$this->decrement = $str;
		$this->isDecrement = true;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function SetIncrementKey($str){
		$this->decrement = $str;
		$this->isDecrement = false;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function SetIsDecrement($bool = true){
		$this->isDecrement = $bool;
		return $this;
	}

	/**
	 * @param string $key
	 * @param string|callable $val
	 * @return $this
	 */
	public function &SetData($key, $val){
		if(is_null($val)) $val = '';
		if(is_object($key) && get_class($key) === 'BH_ModelData') $key = '`' . $key->GetKeyName() .'`';
		if(is_callable($val)) $this->data[$key] = '('.$val($this).')';
		else $this->data[$key] = $val;
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $val
	 * @return $this
	 */
	public function &SetDataStr($key, $val){
		if(is_null($val)) $val = '';
		if(is_object($key) && get_class($key) === 'BH_ModelData') $key = '`' . $key->GetKeyName() .'`';
		$this->data[$key] = $this->StrToPDO('%s', $val);
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $val
	 * @return $this
	 */
	public function &SetDataNum($key, $val){
		if(is_null($val)) $val = '0';
		if(is_object($key) && get_class($key) === 'BH_ModelData') $key = '`' . $key->GetKeyName() .'`';
		$this->data[$key] = SetDBFloat($val);
		return $this;
	}

	public function SetOtherKeys($keys = array()){
		$this->otherKeys = $keys;
		return $this;
	}

	/**
	 * @param string $key
	 * @param array $otherKeys
	 * @return $this
	 */
	public function SetDataDecrement($key, $otherKeys){
		$this->SetDataIncrement($key, $otherKeys, true);
		return $this;
	}

	/**
	 * @param string $key
	 * @param array $otherKeys
	 * @param bool $isDecrement
	 * @return $this
	 */
	public function SetDataIncrement($key, $otherKeys, $isDecrement = false){
		$decWhere = array();
		foreach($otherKeys as $k => $v){
			if($k === $this->decrement) unset($otherKeys[$k]);
			else{
				$decWhere[] = $this->StrToPDO('`BHTMP`.`%1` = %s', $k, $v);
				$this->SetDataStr($k, $v);
			}
		}

		if(!sizeof($decWhere) && \BHG::$isDeveloper === true && $this->showError && BH_Application::$showError) PrintError('Set Increment(Decrement) - No Multi Key');

		$keySql = $this->StrToPDO('IFNULL(%1(`BHTMP`.`%1`), %1) + %1', ($isDecrement ? 'MIN' : 'MAX'), $key, ($isDecrement ? _DBMAXINT : 0), ($isDecrement ? '(-' . (sizeof($this->MultiValues) + 1). ')' : sizeof($this->MultiValues) + 1));
		$this->data[$key] = $this->StrToPDO('(SELECT %1 FROM `%1` `BHTMP` %1)', $keySql, $this->table, sizeof($decWhere) ? ' WHERE ' . implode(' AND ', $decWhere) : '');
		return $this;
	}

	/**
	 * @return $this
	 */
	public function &MultiAdd(){
		$temp = '';
		$names = '';
		$values = '';
		foreach($this->data as $k => $v){
			if(!$this->MultiNames) $names .= $temp . ($k[0] === '`' ? $k : '`' . $k . '`');
			$values .= $temp . $v;
			$temp = ',';
		}
		if(!$this->MultiNames) $this->MultiNames = $names;
		$this->MultiValues[]= $values;
		return $this;
	}

	private function MultiQryToSql(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		if($this->decrement) $keyRes = $this->GetKeySetting($this->isDecrement, $this->otherKeys);
		else $keyRes = array();

		foreach($this->MultiValues as $k => $v){
			$this->MultiValues[$k] = '(' . ($this->decrement ? ($this->isDecrement ? $keyRes['data']-- : $keyRes['data']++).', ' : '') . $v . ')';
		}

		if(sizeof($this->duplicateData)){
			$set = array();
			foreach($this->duplicateData as $k => $v) $set[]= '`' . $k . '` = ' . $v;
			$duplicateSql = 'ON DUPLICATE KEY UPDATE '.implode(', ', $set);
		}

		$this->sql = 'INSERT INTO ' . $this->table . '(' . ($this->decrement ? '`'.$this->decrement.'`, ' : '') . $this->MultiNames . ') VALUES '.implode(',', $this->MultiValues).(isset($duplicateSql) ? ' '.$duplicateSql : '');
		return array(
			'keyRes' => $keyRes
		);
	}

	private function QryToSql(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$temp = '';
		$names = '';
		$values = '';
		foreach($this->data as $k => $v){
			$names .= $temp . ($k[0] === '`' ? $k : '`' . $k . '`');
			$values .= $temp . $v;
			$temp = ',';
		}

		if(sizeof($this->duplicateData)){
			$set = array();
			foreach($this->duplicateData as $k => $v) $set[]= '`' . $k . '` = ' . $v;
			$duplicateSql = 'ON DUPLICATE KEY UPDATE '.implode(', ', $set);
		}

		if($this->decrement) $keyRes = $this->GetKeySetting($this->isDecrement, $this->otherKeys);
		else $keyRes = array();

		$this->sql = 'INSERT {{space1}} INTO {{space2}} `'.$this->table.'` {{space3}} ('.($this->decrement ? '`'.$this->decrement.'`, ' : '').$names.') {{space4}} VALUES ('.($this->decrement ? $keyRes['data'].', ' : '').$values.')'.(isset($duplicateSql) ? ' '.$duplicateSql : '');

		if(is_callable($func)) $func($this->sql);
		$this->sql = preg_replace('#{{space[0-9]+}}#s', '', $this->sql);
		return array(
			'keyRes' => $keyRes
		);
	}

	/**
	 * @return \BH_Result
	 */
	public function MultiRun(){
		$res = new \BH_Result();
		if(!sizeof($this->data)){
			$res->result = false;
			$res->message = BH_Application::$lang['NO_REGISTER_DATA'];
			return $res;
		}

		try{
			DB::BeginTransaction();


			if($this->test && \BHG::$isDeveloper === true){
				echo $this->GetTestMultiSql();
				exit;
			}
			$sqlData = $this->MultiQryToSql();
			$this->query = DB::PDO($this->connName)->prepare($this->sql);
			foreach($this->bindParam as $k => $v) if(strpos($this->sql, $k) !== false) $this->query->bindParam($k, $v[0], $v[1]);
			$res->result = $this->query->execute();
			if(!$res->result && (\BHG::$isDeveloper === true && $this->showError && BH_Application::$showError)) PrintError($this->query->errorInfo());

			if($res->result && $this->decrement) DB::SQL($this->connName)->Query('UPDATE '.TABLE_FRAMEWORK_SETTING.' SET `data` = \''.($sqlData['keyRes']['data']).'\' WHERE `key_name` = \''.$sqlData['keyRes']['keyName'].'\'');

			DB::Commit();
		}
		catch(\PDOException $e){
			DB::PDO($this->connName)->rollBack();
			PrintError($e->getMessage().'('.$e->getCode().')');
		}


		return $res;
	}

	/**
	 * @var callable(&$sql) $func
	 * @return \BH_InsertResult
	 */
	public function Run(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$res = new \BH_InsertResult();

		try{
			DB::BeginTransaction();



			if($this->test && \BHG::$isDeveloper === true){
				echo $this->GetTestSql();
				exit;
			}

			$sqlData = $this->QryToSql($func);

			$this->query = DB::PDO($this->connName)->prepare($this->sql);
			foreach($this->bindParam as $k => $v) if(strpos($this->sql, $k) !== false) $this->query->bindParam($k, $v[0], $v[1]);
			$res->result = $this->query->execute();
			if($res->result){
				if($this->decrement){
					$res->id = $sqlData['keyRes']['data'];
					DB::SQL($this->connName)->Query('UPDATE '.TABLE_FRAMEWORK_SETTING.' SET `data` = \''.($sqlData['keyRes']['data'] + ($this->isDecrement ? -1 : 1)).'\' WHERE `key_name` = \''.$sqlData['keyRes']['keyName'].'\'', true);
				}
				else $res->id = DB::PDO($this->connName)->lastInsertId();
			}
			else if(\BHG::$isDeveloper === true && $this->showError && BH_Application::$showError) PrintError($this->query->errorInfo());

			DB::Commit();
		}
		catch(\PDOException $e){
			DB::PDO($this->connName)->rollBack();
			PrintError($e->getMessage().'('.$e->getCode().')');
		}
		return $res;
	}

	public function PrintTest(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);
		if(\BHG::$isDeveloper === true) echo $this->GetTestSql($func);
	}

	public function PrintMultiTest(){
		if(\BHG::$isDeveloper === true) echo $this->GetTestMultiSql();
	}

	public function GetTestMultiSql(){
		$this->MultiQryToSql();
		$sql = $this->sql;
		foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.str_replace("'", "\\'", $v[0]).'\'', $sql);
		return $sql;
	}

	public function GetTestSql(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);
		$this->QryToSql($func);
		$sql = $this->sql;
		foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.str_replace("'", "\\'", $v[0]).'\'', $sql);
		return $sql;
	}

	private function GetKeySetting($isDecrement = true, $otherKeys = null){
		$minSubSqlWhere = array();
		$kn2 = array();
		if(is_array($otherKeys) && sizeof($otherKeys)){
			foreach($otherKeys as $k => $v){
				$minSubSqlWhere[] = '`' . $k . '` = ' . SetDBText($v);
				$kn2[] = $k;
			}
		}

		$kn = '_table_' . $this->table . '_key';
		if(sizeof($kn2)) $kn .= ':' . implode('^', $kn2);
		$keyQry = 'SELECT `data` FROM ' . TABLE_FRAMEWORK_SETTING . ' WHERE `key_name` = \'' . $kn . '\' FOR UPDATE';
		$keyRes = DB::SQL($this->connName)->Fetch($keyQry);

		$where = '';
		if(sizeof($minSubSqlWhere)) $where = ' WHERE ' . implode(' AND ', $minSubSqlWhere);

		$minSubSql = 'SELECT IF((SELECT `' . $this->decrement . '` FROM `' . $this->table . '` LIMIT 1), ' . ($isDecrement ? 'MIN(`' . $this->decrement . '`) - 1' : 'MAX(`' . $this->decrement . '`) + 1') . ', ' . ($isDecrement ? $this->MAXInt : 1) . ') as `data` FROM `' . $this->table . '`' . $where;

		$minInsSql = 'INSERT INTO ' . TABLE_FRAMEWORK_SETTING . '(`key_name`, `data`) VALUES (\'' . $kn . '\', (' . $minSubSql . ')) ON DUPLICATE KEY UPDATE `data` = (' . $minSubSql . ')';

		$preData = DB::SQL($this->connName)->Fetch($minSubSql);

		if(!$keyRes || $keyRes['data'] < $preData['data']){
			DB::SQL($this->connName)->Query($minInsSql, true);
			$keyRes = DB::SQL($this->connName)->Fetch($keyQry);
		}
		if(!$keyRes) PrintError('`' . $this->table . '` ' . BH_Application::$lang['ERROR_DECREMENT_KEY']);

		$keyRes['keyName'] = $kn;
		return $keyRes;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetShowError($bool = true){
		$this->showError = $bool;
		return $this;
	}
}

class BH_DB_Update{
	public $table = '';
	public $where = array();
	public $data = array();
	public $sql = '';
	public $showError = true;
	public $test = false;
	public $sort = '';
	private $connName = '';

	protected $bindParam = array();

	public $query;

	public function  __construct($table = ''){
		if($table !== '') $this->table = $this->StrToPDO(func_get_args());
		$this->connName = DB::DefaultConnName;
	}

	public function StrToPDO($args){
		$res = DB::StrToPDO(is_array($args) ? $args : func_get_args());
		if(sizeof($res[1])) $this->bindParam = $this->bindParam + $res[1];
		return $res[0];
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &AddTable($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->table .= ' '.$w;
		return $this;
	}

	/**
	 * @param string $key
	 * @param string|callable $val
	 * @return $this
	 */
	public function &SetData($key, $val){
		if(is_null($val)) $val = '';
		if(is_object($key) && get_class($key) === 'BH_ModelData') $key = '`' . $key->parent->naming . '`.`' . $key->GetKeyName() .'`';
		if(is_callable($val)) $this->data[$key] = '('.$val($this).')';
		else $this->data[$key] = $val;
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $val
	 * @return $this
	 */
	public function &SetDataStr($key, $val){
		if(is_null($val)) $val = '';
		if(is_object($key) && get_class($key) === 'BH_ModelData') $key = '`' . $key->parent->naming . '`.`' . $key->GetKeyName() .'`';
		$this->data[$key] = $this->StrToPDO('%s', $val);
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $val
	 * @return $this
	 */
	public function &SetDataNum($key, $val){
		if(is_null($val)) $val = '0';
		if(is_object($key) && get_class($key) === 'BH_ModelData') $key = '`' . $key->parent->naming . '`.`' . $key->GetKeyName() .'`';
		$this->data[$key] = SetDBFloat($val);
		return $this;
	}

	/**
	 * @param string|callable $str
	 * @return $this
	 */
	public function &AddWhere($str){
		if(is_callable($str)) $this->where[] = '(' . $str($this) . ')';
		else{
			$w = $this->StrToPDO(func_get_args());
			if($w !== false) $this->where[] = '('.$w.')';
		}
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetSort($str){
		$this->sort = $this->StrToPDO(func_get_args());
		return $this;
	}

	/**
	 * @var callable(&$sql) $func
	 * @return \BH_Result
	 */
	function Run(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$res = new \BH_Result();
		$temp = '';
		$set = '';
		foreach($this->data as $k => $v){
			$set .= $temp . ($k[0] === '`' ? $k : '`' . $k . '`') . ' = ' . $v;
			$temp = ',';
		}

		if(isset($this->where) && is_array($this->where) && sizeof($this->where)){
			$where = ' WHERE ' . implode(' AND ', $this->where);
		}
		else{
			$res->result = false;
			$res->message = \BHG::$isDeveloper === true ? BH_Application::$lang['NO_WHERE'] : 'ERROR #101';
			return $res;
		}

		$this->sql = 'UPDATE {{space1}} ' . $this->table . ' {{space2}} SET {{space3}} ' . $set . ' {{space4}} ' . $where;
		if($this->sort) $this->sql .= ' {{space5}} ORDER BY ' . $this->sort;

		if(is_callable($func)) $func($this->sql);
		$this->sql = preg_replace('#{{space[0-9]+}}#s', '', $this->sql);

		if($this->test && \BHG::$isDeveloper === true){
			echo $this->GetTestSql();
			exit;
		}
		$this->query = DB::PDO($this->connName)->prepare($this->sql);
		foreach($this->bindParam as $k => $v) if(strpos($this->sql, $k) !== false) $this->query->bindParam($k, $v[0], $v[1]);
		$res->result = $this->query->execute();
		if(!$res->result && (\BHG::$isDeveloper === true && $this->showError && BH_Application::$showError)) PrintError($this->query->errorInfo());
		return $res;
	}

	public function PrintTest(){
		if(\BHG::$isDeveloper === true) echo $this->GetTestSql();;
	}

	public function GetTestSql(){
		$sql = $this->sql;
		foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.str_replace("'", "\\'", $v[0]).'\'', $sql);
		return $sql;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetShowError($bool = true){
		$this->showError = $bool;
		return $this;
	}
}

class BH_DB_Delete{
	public $table = '';
	public $sql = '';
	public $showError = true;
	public $test = false;
	private $where = array();
	private $connName = '';

	protected $bindParam = array();

	public $query;

	public function  __construct($table = ''){
		if($table !== '') $this->table = $this->StrToPDO(func_get_args());
		$this->connName = DB::DefaultConnName;
	}

	public function StrToPDO($args){
		$res = DB::StrToPDO(is_array($args) ? $args : func_get_args());
		if(sizeof($res[1])) $this->bindParam = $this->bindParam + $res[1];
		return $res[0];
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &SetConnName($str){
		$this->connName = $str;
		return $this;
	}

	/**
	 * @param string $str
	 * @return $this
	 */
	public function &AddTable($str){
		$w = $this->StrToPDO(func_get_args());
		if($w !== false) $this->table .= ' '.$w;
		return $this;
	}

	/**
	 * @param string|callable $str
	 * @return $this
	 */
	public function &AddWhere($str){
		if(is_callable($str)) $this->where[] = '(' . $str($this) . ')';
		else{
			$w = $this->StrToPDO(func_get_args());
			if($w !== false) $this->where[] = '('.$w.')';
		}
		return $this;
	}

	/**
	 * @var callable(&$sql) $func
	 * @return bool
	 */
	function Run(){
		$func = null;
		if(func_num_args()) $func = func_get_arg(0);

		$where = '';
		if(isset($this->where) && is_array($this->where) && sizeof($this->where)) $where = ' WHERE ' . implode(' AND ', $this->where);

		$this->sql = 'DELETE {{space1}} FROM {{space2}} '.$this->table.' {{space3}} '.$where;

		if(is_callable($func)) $func($this->sql);
		$this->sql = preg_replace('#{{space[0-9]+}}#s', '', $this->sql);

		if($this->test && \BHG::$isDeveloper === true){
			echo $this->GetTestSql();
			exit;

		}

		$this->query = DB::PDO($this->connName)->prepare($this->sql);
		foreach($this->bindParam as $k => $v) if(strpos($this->sql, $k) !== false) $this->query->bindParam($k, $v[0], $v[1]);
		$res = $this->query->execute();
		if(!$res && (\BHG::$isDeveloper === true && $this->showError && BH_Application::$showError)) PrintError($this->query->errorInfo());
		return $res;
	}

	public function PrintTest(){
		if(\BHG::$isDeveloper === true) echo $this->GetTestSql();
	}

	public function GetTestSql(){
		$sql = $this->sql;
		foreach($this->bindParam as $k => $v) $sql = str_replace($k, '\''.str_replace("'", "\\'", $v[0]).'\'', $sql);
		return $sql;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetTest($bool = false){
		$this->test = $bool;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function &SetShowError($bool = true){
		$this->showError = $bool;
		return $this;
	}
}
