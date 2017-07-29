<?php
if (BHCSS !== true) {
	exit;
}

use BH\BHCss\BHCss;

// 치환값들을 설정합니다.
BHCss::$variable['$cb'] = 'content:\' \'; display:block; clear:both';
BHCss::$variable['$db'] = 'display:block';
BHCss::$variable['$dn'] = 'display:none';
BHCss::$variable['$dt'] = 'display:table; width:100%; table-layout:fixed';
BHCss::$variable['$dt2'] = 'display:table; table-layout:fixed';
BHCss::$variable['$dtc'] = 'display:table-cell';
BHCss::$variable['$di'] = 'display:inline';
BHCss::$variable['$dib'] = 'display:inline-block';
BHCss::$variable['$pa'] = 'position:absolute';
BHCss::$variable['$pr'] = 'position:relative';
BHCss::$variable['$pf'] = 'position:fixed';
BHCss::$variable['$fw2'] = 'font-weight:200';
BHCss::$variable['$fw3'] = 'font-weight:300';
BHCss::$variable['$fw4'] = 'font-weight:400';
BHCss::$variable['$fw7'] = 'font-weight:700';
BHCss::$variable['$fw8'] = 'font-weight:800';
BHCss::$variable['$lh'] = 'line-height';
BHCss::$variable['$test'] = '#993322';
BHCss::$variable['$vam'] = 'vertical-align:middle';
BHCss::$variable['$vat'] = 'vertical-align:top';
BHCss::$variable['$vab'] = 'vertical-align:bottom';
BHCss::$variable['$tal'] = 'text-align:left';
BHCss::$variable['$tac'] = 'text-align:center';
BHCss::$variable['$tar'] = 'text-align:right';
BHCss::$variable['$fll'] = 'float:left';
BHCss::$variable['$flr'] = 'float:right;';
BHCss::$variable['$fz'] = 'font-size';
BHCss::$variable['$fw'] = 'font-weight';

// $cb-- 삽입시 :after 에 값이 삽입됩니다.
// $--cb 처럼 사용하면 :before 에 값이 삽입됩니다.
BHCss::$variable['$cb--'] = 'content:\' \'; display:block; clear:both';
?>

<style>
	// 일부 IDE는 <style type="text/scss"> 로 설정하면 문법오류가 나오지 않을 수 있습니다.
	@charset "UTF-8";

	// php 파일이기 때문에 php 문법을 자유롭게 사용 가능합니다.
	<?php for ($i = 5; $i < 100; $i += 5) { ?>
		.w<?= $i ?>p{ width:<?= $i ?>%;}
	<?php } ?>

	div{
		{ $pr; $dib; $cb--; }
		:before{ content:' > '; $dib; color:#fff; opacity:0;}
		// div.box 로 연결된 셀렉터를 만들어줍니다.
		~.box{$dn;}
		// div .box2 로 하위 셀렉터를 나타냅니다.
		.box2{$db;}
	}
	div.box2{
		:after{$cb; color:$test; border:1px solid #ddd;}
	}

</style>
<?php
// test2.bhcss.php 파일을 인클루드합니다.
BHCss::includeBHCss(__DIR__ . '/test2.bhcss.php');


