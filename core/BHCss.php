<?php

/**
 * @author     Bang Hun <huny0522@gmail.com>
 * 17.07.29
 */

namespace BH\BHCss;

define('BHCSS', true);

require 'Node.php';

class BHCss{

	const COMMENT_STRING = '@charset CONVERT COMMENT STRING;';
	const NL = "\r\n";

	public static $fileExtension = '.bhcss.php';
	public static $variable = array();
	public static $modifyFilesTime = array();
	private static $cssBody = '';
	private static $comment = array();
	private static $node = null;
	private static $beforeVariables = array();
	private static $afterVariables = array();
	private static $paramVariable = array();
	private static $enableNL = true;
	private static $passFiles = array();
	public static $convDirMessage = array();

	private function __construct(){

	}

	private function __clone(){

	}

	public static function reset(){
		self::$passFiles = array();
		self::resetForParent();
	}

	private static function resetForParent(){
		self::$variable = array();
		self::$cssBody = '';
		self::$comment = array();
		self::$node = null;
		self::$beforeVariables = array();
		self::$afterVariables = array();
		self::$paramVariable = array();
		self::$enableNL = true;
	}

	// 파일 변경 시간 체크 후 컨버팅
	public static function convTimeCheck(&$beforeTime, $path, $target = ''){
		$path = str_replace('\\', '/', $path);
		if(!$target) $target = self::getTargetPath($path);

		if(in_array($path, self::$passFiles)) return null;

		if(file_exists($path)){
			$targetTime = filemtime($path);
			if($beforeTime != $targetTime){
				$res = self::conv($path, $target);
				if(is_null($res)){
					return null;
				}
				if($res->result === true){
					$beforeTime = $targetTime;
				}
				return $res;
			}
		}
		else{
			return self::conv($path, $target);
		}
		return null;
	}

	public static function convStyleText($text){
		self::$cssBody = $text;
		uksort(self::$variable, array('\BH\BHCss\BHCss', 'varSort'));

		self::extractComment();

		self::settingVariable();

		self::cssToNode();

		self::$cssBody = self::node2css(self::$node);

		self::convertVariable();

		self::crossCss();

		if(!self::$enableNL){
			self::$cssBody = str_replace(array(self::COMMENT_STRING, "\t", "\n", "\r"), '', self::$cssBody);
		}
		else{
			// 주석삽입
			foreach(self::$comment as $v){
				self::$cssBody = preg_replace('/' . str_replace(array('@', ';'), array("\\@", "\\;"), self::COMMENT_STRING) . '/', self::NL . self::NL . $v, self::$cssBody, 1);
			}
		}

		return self::$cssBody;
	}

	// 컨버팅
	public static function conv($path, $target = ''){
		$path = str_replace('\\', '/', $path);

		if(!$target) $target = self::getTargetPath($path);


		$result = (object) array('result' => false, 'message' => '');

		ob_start();
		include $path;
		self::$cssBody = str_replace("\n", "\r\n", preg_replace(array('/\r|\t/', '/(\<|\<\/)style(.*?)\>/'), '', ob_get_clean()));

		if(in_array($path, self::$passFiles)){
			$result->result = true;
			return $result;
		}

		self::$passFiles[] = $path;

		uksort(self::$variable, array('\BH\BHCss\BHCss', 'varSort'));

		$exp = explode('/', $target);
		array_pop($exp);
		$tempPath = implode('/', $exp);
		if(!file_exists($tempPath) || !is_dir($tempPath))
			mkdir($tempPath, 0755, true);

		self::extractComment();

		self::settingVariable();

		self::cssToNode();

		self::$cssBody = self::node2css(self::$node);

		self::convertVariable();

		self::crossCss();

		if(!self::$enableNL){
			self::$cssBody = str_replace(array(self::COMMENT_STRING, "\t", "\n", "\r"), '', self::$cssBody);
		}
		else{
			// 주석삽입
			foreach(self::$comment as $v){
				self::$cssBody = preg_replace('/' . str_replace(array('@', ';'), array("\\@", "\\;"), self::COMMENT_STRING) . '/', self::NL . self::NL . $v, self::$cssBody, 1);
			}
		}

		file_put_contents($target, self::$cssBody);
		chmod($target, 0777);

		$result->result = true;
		return $result;
	}

	/**
	 * 현재 파일이 수정될 경우 $parentPath에서 지정한 파일을 함께 렌더링합니다.
	 * @param string $thisPath 현재파일경로
	 * @param array $parentPath 부모파일경로
	 * @return boolean
	 */
	public static function callParent($thisPath, array $parentPath){
		$thisPath = str_replace('\\', '/', $thisPath);
		$dir = dirname($thisPath);

		if(in_array($thisPath, self::$passFiles)) return false;

		self::$passFiles[] = $thisPath;

		foreach($parentPath as $path){
			if(file_exists($dir . '/' . $path)) $path = $dir . '/' . $path;

			else if(file_exists(__DIR__ . '/' . $path)) $path = __DIR__ . '/' . $path;

			if(file_exists($path)){
				self::resetForParent();
				self::conv($path);
			}
		}
		return true;
	}

	// 인클루드
	public static function includeBHCss($path){
		self::$passFiles[] = $path;
		if(file_exists($path)) include $path;
	}

	// 주석 및 줄바꿈 표시
	public static function setNL($bool){
		self::$enableNL = $bool;
	}

	// css 변수 초기화
	private static function settingVariable(){
		foreach(self::$variable as $k => $v){
			if(substr($v, -1) === ';') $v = substr($v, 0, -1);

			if(substr($k, 0, 3) === '$--'){
				self::$beforeVariables[$k] = $v;
			}
			else if(substr($k, -2) === '--'){
				self::$afterVariables[$k] = $v;
			}
			else{
				self::$paramVariable[$k] = $v;
			}
		}
	}

	// css 변수 길이순 정렬
	private static function varSort($key1, $key2){
		$s1 = strlen($key1);
		$s2 = strlen($key2);
		if($s1 == $s2) return 0;

		return $s1 > $s2 ? -1 : 1;
	}

	// css 변수들을 값으로 변환
	private static function convertVariable(){
		$params = array();
		$values = array();

		foreach(self::$paramVariable as $k => $v){
			$params[] = '/\\' . $k . '/';
			$values[] = $v;
		}

		self::$cssBody = preg_replace($params, $values, self::$cssBody);
	}

	// 노드를 CSS로 변환
	private static function node2css(&$node, $group = array()){
		$txt = '';
		if(is_string($node->data)){
			$node->data = trim(preg_replace(array('/\r\n/is', '/\s+/is'), array(' ', ' '), $node->data));
			// ---------------------------------------------------------------
			// 변환

			$after = array();
			$before = array();
			foreach(self::$afterVariables as $k => $v){
				if(strpos($node->data, $k) !== false){
					$after[] = $v;
					$node->data = preg_replace('/' . str_replace(array('$', '-'), array('\$', '\-'), $k) . '\s*;*\s*/', '', $node->data);
				}
			}

			foreach(self::$beforeVariables as $k => $v){
				if(strpos($node->data, $k) !== false){
					$before[] = $v;
					$node->data = preg_replace('/' . str_replace(array('$', '-'), array('\$', '\-'), $k) . '\s*;*\s*/', '', $node->data);
				}
			}

			if(is_array($group) && sizeof($group)){
				$groups = '';
				foreach($group as $k => $v){
					$v = trim($v);
					if(!$k) $groups = $v;
					else
						$groups .= ($v[0] == '~' ? trim(substr($v, 1)) : ($v[0] == ':' ? '' : ' ') . $v);
				}

				$s = explode(',', $node->selector);
				foreach($s as $k => &$v){
					$v = trim($v);
					if(!strlen($v)) $v = $groups;
					else
						$v = $groups . ($v[0] == '~' ? trim(substr($v, 1)) : ($v[0] == ':' ? '' : ' ') . $v);
				}
				$txt .= implode(',', $s) . '{' . $node->data . '}';
				if(sizeof($after)){
					$temp = $s;
					foreach($temp as $k => $v){
						$temp[$k] = trim($v) . ':after';
					}
					$txt .= implode(',', $temp) . '{' . implode(';', $after) . '}';
				}
				if(sizeof($before)){
					$temp = $s;
					foreach($temp as $k => $v){
						$temp[$k] = trim($v) . ':before';
					}
					$txt .= implode(',', $temp) . '{' . implode(';', $before) . '}';
				}
			}
			else if(strlen($node->selector)){
				$txt .= $node->selector . '{' . $node->data . '}';
				if(sizeof($after)){
					$txt .= $node->selector . ':after' . '{' . implode(';', $after) . '}';
				}
				if(sizeof($before)){
					$txt .= $node->selector . ':after' . '{' . implode(';', $before) . '}';
				}
			}
			else{
				$txt .= $node->data;
			}
		}
		else if($node->data !== false){
			$group2 = $group;
			$node->selector = trim($node->selector);
			if($node->selector[0] != '@'){
				$group2[] = $node->selector;
				if($node->parent === false){
					$txt .= self::NL . self::NL;
				}
				$txt .= self::node2css($node->data, $group2);
			}
			else{
				$txt .= self::NL . self::NL . $node->selector . '{' . self::NL;
				$data = trim(self::node2css($node->data, $group2));
				$data = chr(9) . preg_replace('/\}\r\n*\s*/is', "}\r\n\t", $data);
				$txt .= $data;
				$txt .= self::NL . '}' . self::NL;
			}
		}
		if($node->next){
			$txt .= self::node2css($node->next, $group);
		}
		return $txt;
	}

	// CSS를 노드로 변환
	private static function cssToNode(){
		$at = array('@charset', '@import', '@namespace');

		self::$node = new Node();
		$txt = '';
		$flen = strlen(self::$cssBody);
		$p = &self::$node;

		for($i = 0; $i < $flen; $i++){
			foreach($at as $item){
				if(substr(self::$cssBody, $i, strlen($item)) == $item){
					$findEnd = strpos(self::$cssBody, ';', $i);
					if($findEnd !== false){
						if($p->data !== false){
							$p->setNext();
							$p = &$p->next;
						}
						$p->data = substr(self::$cssBody, $i, $findEnd - $i + 1);
						$i = $findEnd + 1;
					}
					else{
						if($p->data !== false){
							$p->setNext();
							$p = &$p->next;
						}
						$p->data = substr(self::$cssBody, $i, $flen - $i);
						$i = $flen;
					}
				}
			}
			if($i >= $flen) break;

			if(self::$cssBody[$i] == '{'){
				$findBegin = strpos(self::$cssBody, '{', $i + 1);
				$findEnd = strpos(self::$cssBody, '}', $i);

				if($findBegin > $findEnd || $findBegin === false){
					if($p->data !== false){
						$p->setNext();
						$p = &$p->next;
					}
					$p->selector = $txt;
					$p->data = substr(self::$cssBody, $i + 1, $findEnd - $i - 1);
					$i = $findEnd;
				}
				else{
					if($p->data !== false){
						$p->setNext();
						$p = &$p->next;
					}
					$p->selector = $txt;
					$p->setChild();
					$p = &$p->data;
				}
				$txt = '';
			}
			else if(self::$cssBody[$i] == '}'){
				if($p->parent !== false){
					$p = &$p->parent;
				}
			}
			else{
				$txt .= self::$cssBody[$i];
			}
		}
	}

	// 브라우저별 셀렉터 변환
	private static function crossCss(){
		self::$cssBody = preg_replace(array(
			'/(-\S+-transition)\s*[:]\s*(.*?);\s*/',
			'/(-\S+-transform)\s*[:]\s*(.*?);\s*/',
			'/(-\S+-border-radius)\s*[:]\s*(.*?);\s*/',
			'/(-\S+-box-shadow)\s*[:]\s*(.*?);\s*/',
			'/(-\S+-box-sizing)\s*[:]\s*(.*?);\s*/',
			'/(-\S+-background-size)\s*[:]\s*(.*?);\s*/',
			'/(-\S+-text-overflow)\s*[:]\s*(.*?);\s*/',
			'/([a-zA-Z]+?)\s*[:]\s*\-(webkit\-|moz\-|o\-)(linear\-|radial\-)gradient\s*\((.*?)\)\s*;/',
		), '', self::$cssBody);

		$patterns = array(
			'/(border-radius)\s*[:]\s*(.*?);/',
			'/([^-])(transition)\s*[:]\s*(.*?);/',
			'/([^-])(transform)\s*[:]\s*(.*?);/',
			'/(box-shadow)\s*[:]\s*(.*?);/',
			'/(box-sizing)\s*[:]\s*(.*?);/',
			'/(background-size)\s*[:]\s*(.*?);/',
			'/(text-overflow)\s*[:]\s*(.*?);/',
			'/([a-zA-Z]+?)\s*[:]\s*(linear\-|radial\-)gradient\s*\((.*?)\)\s*;/',
			'/(\r\n){3,}/is',
			'/\{\r\n+/',
			'/\}\s*\}/is',
			'/url\(([\"|\'])(\/html\/)/',
			'/\}(\S)/',
			'/\;\s*\}/',
		);

		$replace = array(
			'border-radius:$2; -webkit-border-radius:$2; -moz-border-radius:$2;',
			'$1-moz-transition:$3; -webkit-transition:$3; -ms-transition:$3; -o-transition:$3; transition:$3;',
			'$1-moz-transform:$3; -webkit-transform:$3; -ms-transform:$3; -o-transform:$3; transform:$3;',
			'-webkit-box-shadow:$2; -moz-box-shadow:$2; box-shadow:$2;',
			'-webkit-box-sizing:$2; -moz-box-sizing:$2; box-sizing:$2;',
			'-webkit-background-size:$2; background-size:$2;',
			'-ms-text-overflow:$2; text-overflow:$2;',
			'$1:-webkit-$2gradient($3); $1:-moz-$2gradient($3); $1:-o-$2gradient($3); $1:$2gradient($3);',
			"\r\n",
			"{\r\n",
			"}\r\n}",
			'url($1../',
			"}\r\n$1",
			';}',
		);

		self::$cssBody = preg_replace($patterns, $replace, self::$cssBody);
	}

	// 주석 추출
	private static function extractComment(){
		$pattern = '/(\/\*)(.*?)(\*\/)/is';
		$matches = array();
		preg_match_all($pattern, self::$cssBody, $matches);
		self::$comment = $matches[0];

		self::$cssBody = preg_replace($pattern, self::COMMENT_STRING, self::$cssBody);

		// 주석삭제
		self::$cssBody = preg_replace('/(([^http:|https:|url\(|url\(\']|\n)\/\/|^\/\/)(.*)/', '', self::$cssBody);
	}

	public static function getTargetPath($path){
		$temp = explode('/', $path);
		$filename = array_pop($temp);
		if(substr($filename, strlen(self::$fileExtension) * (-1)) == self::$fileExtension){
			return implode('/', $temp) . '/' . substr($filename, 0, strlen(self::$fileExtension) * (-1)) . '.css';
		}
		else{
			$temp2 = explode('.', $filename);
			array_pop($temp2);
			return implode('/', $temp) . '/' . implode('.', $temp2) . '.css';
		}
	}

	public static function convertBHCssDir($tempfile_path, $beginIs = true){
		if($beginIs)
			self::$convDirMessage = array('success' => array(), 'fail' => array());

		if(!is_dir($tempfile_path)) return;

		if($dh = opendir($tempfile_path)){
			while(($file = readdir($dh)) !== false){
				if($file != '.' && $file != '..'){
					$dest_path = $tempfile_path . '/' . $file;
					if(is_dir($dest_path)) self::convertBHCssDir($dest_path, false);
					else{
						if(substr($dest_path, strlen(BHCss::$fileExtension) * (-1)) == BHCss::$fileExtension){
							if(!isset(self::$modifyFilesTime[$dest_path]))
								self::$modifyFilesTime[$dest_path] = 0;

							BHCss::reset();
							$res = BHCss::convTimeCheck(self::$modifyFilesTime[$dest_path], $dest_path, $tempfile_path . '/' . substr($file, 0, strlen(BHCss::$fileExtension) * (-1)) . '.css');
							if(!is_null($res)){
								if($res->result){
									self::$convDirMessage['success'][] = $dest_path;
								}
								else{
									self::$convDirMessage['fail'][] = $dest_path;
								}
							}
						}
					}
				}
			}
			closedir($dh);
		}
	}

}
