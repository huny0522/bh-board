<?php
class CssSetting
{
	public static $minPixel = 100;
	public static $maxPixel = 375;
	public static $screenMaxWidth = 1200;
	public static $screenMinWidth = 320;

	public static function HtmlFontSize($minPixel = 0, $screenMaxWidth = 0, $screenMinWidth = 0){
		if($minPixel) self::$minPixel = $minPixel;
		if($screenMaxWidth) self::$screenMaxWidth = $screenMaxWidth;
		if($screenMinWidth) self::$screenMinWidth = $screenMinWidth;

		self::$maxPixel = (self::$screenMaxWidth / self::$screenMinWidth) * self::$minPixel;
		$css = 'html{font-size:calc(100vw / ' .(self::$screenMinWidth/self::$minPixel). ');}
			@media (min-width:' . self::$screenMaxWidth . 'px){
				html{font-size:' . self::$maxPixel . 'px;}
			}
			@media (max-width:' . self::$screenMinWidth . 'px){
				html{font-size:'. self::$minPixel . 'px;}
			}';
		return $css;
	}

	public static function HtmlFontSizeByMax($maxPixel = 0, $screenMaxWidth = 0, $screenMinWidth = 0){
		if($maxPixel) self::$maxPixel = $maxPixel;
		if($screenMaxWidth) self::$screenMaxWidth = $screenMaxWidth;
		if($screenMinWidth) self::$screenMinWidth = $screenMinWidth;

		self::$minPixel = (self::$screenMinWidth / self::$screenMaxWidth) * self::$maxPixel;
		$css = 'html{font-size:calc(100vw / ' .(self::$screenMinWidth/self::$minPixel). ');}
			@media (min-width:' . self::$screenMaxWidth . 'px){
				html{font-size:' . self::$maxPixel . 'px;}
			}
			@media (max-width:' . self::$screenMinWidth . 'px){
				html{font-size:'. self::$minPixel . 'px;}
			}';
		return $css;
	}
}


function c($max, $min, $minusIs = false){
	// $a + ($b * 2.00) = $min
	// $a + ($b * 3.75) = $max
	$x = 99999;
	$choice = false;

	for($b = 0; $b <= $max; $b+= 0.01){
		$temp = abs(($max - ($b*(CssSetting::$maxPixel/100))) - ($min - ($b*(CssSetting::$minPixel/100))));
		if($temp < $x){
			$x = $temp;
			$choice = $b;
		}
		else if($choice !== false){
			// echo 'test{' . $choice .'}' . PHP_EOL;
			break;
		}
	}

	$base = floor(($max - ($choice * (CssSetting::$maxPixel/100))) * 10000) / 10000;
	$rem = $choice / CssSetting::$minPixel;


	if($minusIs) $res = 'calc((-' . $base . 'px) + (-' . (floor($rem * 100000) / 100000) . 'rem))';
	else $res = 'calc(' . $base . 'px + ' . (floor($rem * 100000) / 100000) . 'rem)';
	return $res;
}

function px2rem($pixel){
	return (floor(($pixel/CssSetting::$maxPixel) * 10000) / 10000).'rem';
}

\BH\BHCss\BHCss::$callbackPatterns[] = array(
	'pattern' => '/c\=\(\s*([0-9]+)\s*\,\s*([0-9]+)\s*\)/',
	'callback' => function($matches){
		return c($matches[1], $matches[2]);
	}
);