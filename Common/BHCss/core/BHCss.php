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
	public static $passFiles = array();
	public static $convDirMessage = array();

	public static $patterns = array();
	public static $replace = array();

	public static $responseMinWidth = 320;
	public static $responseMaxWidth = 600;
	public static $responseMinFontSize = 100;
	public static $responseMaxFontSize = 375;
	public static $responseUsingCalc = false;
	public static $responseStepUnit = 200;

	/**
	 * @var array['callback' => function,'pattern' => '']
	 */
	public static $callbackPatterns = array();

	private function __construct(){

	}

	private function __clone(){

	}

	public static function setResponseFontSizeByMin($minPixel = 0, $screenMaxWidth = 0, $screenMinWidth = 0){
		if($minPixel) self::$responseMinFontSize = $minPixel;
		if($screenMaxWidth) self::$responseMaxWidth = $screenMaxWidth;
		if($screenMinWidth) self::$responseMinWidth = $screenMinWidth;

		self::$responseMaxFontSize = (self::$responseMaxWidth / self::$responseMinWidth) * self::$responseMinFontSize;
		$css = 'html{font-size:calc(100vw / ' .(self::$responseMinWidth/self::$responseMinFontSize). ');}
			@media (min-width:' . self::$responseMaxWidth . 'px){
				html{font-size:' . self::$responseMaxFontSize . 'px;}
			}
			@media (max-width:' . self::$responseMinWidth . 'px){
				html{font-size:'. self::$responseMinFontSize . 'px;}
			}';
		return $css;
	}

	public static function setResponseFontSizeByMax($maxPixel = 0, $screenMaxWidth = 0, $screenMinWidth = 0){
		if($maxPixel) self::$responseMaxFontSize = $maxPixel;
		if($screenMaxWidth) self::$responseMaxWidth = $screenMaxWidth;
		if($screenMinWidth) self::$responseMinWidth = $screenMinWidth;

		self::$responseMinFontSize = (self::$responseMinWidth / self::$responseMaxWidth) * self::$responseMaxFontSize;
		$css = 'html{font-size:calc(100vw / ' .(self::$responseMinWidth/self::$responseMinFontSize). ');}
			@media (min-width:' . self::$responseMaxWidth . 'px){
				html{font-size:' . self::$responseMaxFontSize . 'px;}
			}
			@media (max-width:' . self::$responseMinWidth . 'px){
				html{font-size:'. self::$responseMinFontSize . 'px;}
			}';
		return $css;
	}

	public static function px2rem($pixel){
		return (floor(($pixel/self::$responseMaxFontSize) * 1000) / 1000).'rem';
	}

	public static function calc($max, $min, $minusIs = false){
		$x = 99999;
		$choice = false;

		for($b = 0; $b <= $max; $b+= 0.01){
			$temp = abs(($max - ($b*(self::$responseMaxFontSize/100))) - ($min - ($b*(self::$responseMinFontSize/100))));
			if($temp < $x){
				$x = $temp;
				$choice = $b;
			}
			else if($choice !== false){
				// echo 'test{' . $choice .'}' . PHP_EOL;
				break;
			}
		}

		$base = floor(($max - ($choice * (self::$responseMaxFontSize/100))) * 10000);
		$rem = $choice / self::$responseMinFontSize;


		if($minusIs) $res = 'calc(((' . $base . 'px + ' . floor($rem * 10000) . 'rem) * (-1)) / 10000)';
		else $res = 'calc((' . $base . 'px + ' . floor($rem * 10000) . 'rem) / 10000)';
		return $res;
	}

	private static $responseData = array();

	public static function setResponseData($selector, &$data){
		$pattern = '/([a-zA-Z0-9\-\_]+?)\s*\:([^\:\;]*?)(c|cc)\=(\-*)\((\s*[0-9\.\-]+\s*\,\s*[0-9\.\-].*?)\)([^\;\}]*)/i';
		while(preg_match($pattern, $data, $match)){

			if($match[3] == 'cc' || self::$responseUsingCalc){
				$temp = $match[0];
				$pattern2 = '/(c|cc)\=(\-*)\((\s*[0-9\.\-]+\s*\,\s*[0-9\.\-].*?)\)/i';
				while(preg_match($pattern2, $temp, $match2)){
					$vals = explode(',', $match2[3]);
					$max = trim($vals[0]);
					$min = trim($vals[1]);
					$mp = trim($match2[2]);

					$temp = preg_replace($pattern2, self::calc($max, $min, $mp == '-'), $temp, 1);;
				}
				$data = preg_replace($pattern, $temp, $data, 1);
				// $data = preg_replace($pattern, $match[1] . ':' . $match[2] . self::calc($max, $min, $match[4] == '-'), $data, 1);
			}
			else{
				$temp = $match[0];
				self::$responseData[$selector][] = $temp;
				$pattern2 = '/c\=(\-*)\((\s*[0-9\.\-]+\s*\,\s*[0-9\.\-].*?)\)/i';
				while(preg_match($pattern2, $temp, $match2)){
					$vals = explode(',', $match2[2]);
					$max = trim($vals[0]);
					$maxWidth = isset($vals[2]) ? preg_replace('/[^0-9\.\-]/', '', $vals[2]) : '';
					$minWidth = isset($vals[3]) ? preg_replace('/[^0-9\.\-]/', '', $vals[3]) : '';

					if($maxWidth || $minWidth) $temp = preg_replace($pattern2, '', $temp, 1);

					else $temp = preg_replace($pattern2, $match2[1] . $max . 'px' . ' ', $temp, 1);
				}
				$temp_e = explode(':', $temp);
				if(sizeof($temp_e) < 2 || trim($temp_e[1]) == '') $data = preg_replace($pattern, '', $data, 1);
				else $data = preg_replace($pattern, $temp, $data, 1);
			}
		}
	}

	public static function printResponseData(){
		$pattern = '/c\=(\-*)\((\s*[0-9\.\-]+\s*\,\s*[0-9\.\-].*?)\)/i';
		$css = PHP_EOL;
		$s = self::$responseMaxWidth;
		while($s > self::$responseMinWidth){
			$s2 = $s - self::$responseStepUnit;

			$minMedia = '';
			if($s2 <= self::$responseMinWidth) $s2 = self::$responseMinWidth;
			else $minMedia = ' and (min-width:'.$s2.'px)';
			$css .= '@media(max-width:'.($s-1).'px)'. $minMedia .'{'.PHP_EOL;
			foreach(self::$responseData as $selector => $v){
				$cssInner = '';
				foreach($v as $v2){

					while(preg_match($pattern, $v2, $match)){
						$vals = explode(',', $match[2]);
						$max = trim($vals[0]);
						$min = trim($vals[1]);
						$maxWidth = isset($vals[2]) ? preg_replace('/[^0-9]/', '', $vals[2]) : '';
						$minWidth = isset($vals[3]) ? preg_replace('/[^0-9]/', '', $vals[3]) : '';

						if(($minWidth && $minWidth >= $s) || ($maxWidth && $maxWidth < $s)){
							$v2 = '';
							continue;
						}

						$px = floor((($max - $min) * (($s2 - self::$responseMinWidth) / (self::$responseMaxWidth - self::$responseMinWidth))) + $min);

						$v2 = preg_replace($pattern, $match[1] . $px . 'px' . ' ', $v2, 1);
					}
					$cssInner .= $v2 . ';';
				}

				if($cssInner){
					$css .= "\t".$selector.'{';
					$css .= $cssInner;
					$css .= '}'.PHP_EOL;
				}
			}
			$css .= '}'.PHP_EOL;

			$s -= self::$responseStepUnit;
		}
		self::$responseData = array();
		self::$cssBody .= $css;
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

		self::printResponseData();

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
	public static function conv($path, $target = '', $parentCheckDisable = false){
		if(!$parentCheckDisable){
			$data = file_get_contents($path);
			preg_match_all('/\/\/\s*parent\s*\:\s*(.*?)\.bhcss\.php/', $data, $matches);
			if($matches && $matches[1]){

				$targets = array();
				$sources = array();

				foreach($matches[1] as $v){
					$t = 0;
					$source2 = explode('/', $path);
					array_pop($source2);
					$source2 = implode('/', $source2) . '/' . $v . '.bhcss.php';
					if($target){
						$targetPath = explode('/', $target);
						array_pop($targetPath);
						$targetPath .= '/' . $v . '.bhcss.php';
					}
					else $targetPath = '';
					if(file_exists($source2)){
						$sources[] = $source2;
						$targets[] = $targetPath;
						self::conv($source2, $targetPath);
					}
				}
				return (object) array('result' => 'parent convert', 'message' => array('source' => $sources, 'target' => $targets));
			}
		}

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

		self::printResponseData();

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

				$sel = implode(',', $s);
				self::setResponseData($sel, $node->data);
				$txt .= $sel . '{' . $node->data . '}';
				if(sizeof($after)){
					$temp = $s;
					foreach($temp as $k => $v){
						$temp[$k] = trim($v) . ':after';
					}
					$sel = implode(',', $temp);
					$css = implode(';', $after);
					self::setResponseData($sel, $css);
					$txt .= $sel . '{' . $css . '}';
				}
				if(sizeof($before)){
					$temp = $s;
					foreach($temp as $k => $v){
						$temp[$k] = trim($v) . ':before';
					}
					$sel = implode(',', $temp);
					$css = implode(';', $before);
					self::setResponseData($sel, $css);
					$txt .= $sel . '{' . $css . '}';
				}
			}
			else if(strlen($node->selector)){
				self::setResponseData($node->selector, $node->data);
				$txt .= $node->selector . '{' . $node->data . '}';
				if(sizeof($after)){
					$css = implode(';', $after);
					self::setResponseData($node->selector . ':after', $css);
					$txt .= $node->selector . ':after' . '{' . $css . '}';
				}
				if(sizeof($before)){
					$css = implode(';', $before);
					self::setResponseData($node->selector . ':before', $css);
					$txt .= $node->selector . ':before' . '{' . $css . '}';
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
			'/(-[a-zA-Z\-]+-transition)\s*[:]\s*(.*?);\s*/',
			'/(-[a-zA-Z\-]+-transform)\s*[:]\s*(.*?);\s*/',
			'/(-[a-zA-Z\-]+-border-radius)\s*[:]\s*(.*?);\s*/',
			'/(-[a-zA-Z\-]+-box-shadow)\s*[:]\s*(.*?);\s*/',
			'/(-[a-zA-Z\-]+-box-sizing)\s*[:]\s*(.*?);\s*/',
			'/(-[a-zA-Z\-]+-background-size)\s*[:]\s*(.*?);\s*/',
			'/(-[a-zA-Z\-]+-text-overflow)\s*[:]\s*(.*?);\s*/',
			'/([a-zA-Z\-]+?)\s*[:]\s*\-(webkit\-|moz\-|o\-)(linear\-|radial\-)gradient\s*\((.*?)\)\s*;/',
		), '', self::$cssBody);


		self::$cssBody = preg_replace(self::$patterns, self::$replace, self::$cssBody);
		if(sizeof(self::$callbackPatterns)){
			foreach(self::$callbackPatterns as $v){
				if(isset($v['pattern']) && isset($v['callback']))
					self::$cssBody = preg_replace_callback($v['pattern'], $v['callback'], self::$cssBody);
			}
		}
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

BHCss::$patterns = array(
	'/(border-radius)\s*[:]\s*(.*?)([\}|;])/',
	'/([^-])(transition)\s*[:]\s*(.*?)([\}|;])/',
	'/([^-])(transform)\s*[:]\s*(.*?)([\}|;])/',
	'/(box-shadow)\s*[:]\s*(.*?)([\}|;])/',
	'/(box-sizing)\s*[:]\s*(.*?)([\}|;])/',
	'/(background-size)\s*[:]\s*(.*?)([\}|;])/',
	'/(text-overflow)\s*[:]\s*(.*?)([\}|;])/',
	'/([a-zA-Z]+?)\s*[:]\s*(linear\-|radial\-)gradient\s*\((.*?)\)\s*([\}|;])/',
	'/([a-zA-Z\.\+\~\#\(\)\-\_\s\:]+?)\s*\:+\s*placeholder\s*\{(.*?)\}/',
	'/(\r\n){3,}/is',
	'/\{\r\n+/',
	'/\}\s*\}/is',
	'/url\(([\"|\'])(\/html\/)/',
	'/\}(\S)/',
	'/\;\s*\}/',
	'/\;\s*\;/',
	'/px\s*\;/',
);

BHCss::$replace = array(
	'border-radius:$2; -webkit-border-radius:$2; -moz-border-radius:$2$3',
	'$1-moz-transition:$3; -webkit-transition:$3; -ms-transition:$3; -o-transition:$3; transition:$3$4',
	'$1-moz-transform:$3; -webkit-transform:$3; -ms-transform:$3; -o-transform:$3; transform:$3$4',
	'-webkit-box-shadow:$2; -moz-box-shadow:$2; box-shadow:$2$3',
	'-webkit-box-sizing:$2; -moz-box-sizing:$2; box-sizing:$2$3',
	'-webkit-background-size:$2; background-size:$2$3',
	'-ms-text-overflow:$2; text-overflow:$2$3',
	'$1:-webkit-$2gradient($3); $1:-moz-$2gradient($3); $1:-o-$2gradient($3); $1:$2gradient($3)$4',
	'$1::placeholder{$2}$1::-ms-input-placeholder{$2}$1::-webkit-input-placeholder{$2}',
	"\r\n",
	"{\r\n",
	"}\r\n}",
	'url($1../',
	"}\r\n$1",
	';}',
	';',
	'px;',
);