<?php
if(BHCSS !== true) exit;

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
?>
<style type="text/scss">
	@charset "utf-8";
	
	html, body, div, span, applet, object, iframe,
	h1, h2, h3, h4, h5, h6, p, blockquote, pre,
	a, abbr, acronym, address, big, cite, code,
	del, dfn, em, img, ins, kbd, q, s, samp,
	small, strike, strong, sub, sup, tt, var,
	b, u, i, center,
	dl, dt, dd, ol, ul, li,
	fieldset, form, label, legend,
	table, caption, tbody, tfoot, thead, tr, th, td,
	article, aside, canvas, details, embed,
	figure, figcaption, footer, header,
	menu, nav, output, ruby, section, summary,
	time, mark, audio, video {
		margin: 0; padding: 0; border: 0; font-size: inherit; font-weight:inherit; color:inherit; vertical-align: baseline;
		box-sizing:border-box; -webkit-tap-highlight-color:rgba(102, 102, 102, 0.3);
	}
	html, body{width:100%; height:100%; word-break:break-all;}
	body{line-height:1.1; font-size:14px; font-family:'Nanum Gothic'; color:#333; overflow-y:scroll; background:#fff;}
	ol, ul{list-style: none;}
	table{border-collapse: collapse;border-spacing: 0;}
	table caption{display:none;}
	table th, table td{vertical-align:middle;}
	a, a:hover, a:link, a:focus{text-decoration: none; color:inherit;}

	/*FORM RESET*/
	input{border:0; box-sizing:border-box; vertical-align:middle;}
	input[type=email], input[type=text], input[type=password], input[type=tel], input[type=file], select, textarea {margin:0; padding:0; border: 1px solid #ddd; font-family:'Nanum Gothic'; font-size:inherit; resize:none; vertical-align:middle; border-radius:5px; height:26px; padding:0 4px; line-height:24px;}
	select{height:26px; line-height:24px; box-sizing:border-box;}
	input, img{vertical-align:middle;}
	input[type=file]{border:0; padding:0;}
	input[type=button], input[type=submit], button{padding:0; color:inherit; font-family:'Nanum Gothic'; border:0; background:none; vertical-align:middle; cursor:pointer; font-weight:inherit;}
	label{font-family:'Nanum Gothic';}
	textarea{ width:100%; height:100px; box-sizing:border-box; padding:4px; line-height:1.3;}
	.hidden, .hide{display:none;}

	#_BH_RefreshBtn{ position:fixed; bottom:10px; right:10px; z-index:9999; display:block; height:30px; line-height:30px; padding:0 15px; font-size:12px; background:rgba(0,0,0,0.5); color:#fff; border-radius:15px;}

	.w10p{ width:10%;}
	.w20p{ width:20%;}
	.w30p{ width:30%;}
	.w40p{ width:40%;}
	.w50p{ width:50%;}
	.w60p{ width:60%;}
	.w70p{ width:70%;}
	.w80p{ width:80%;}
	.w90p{ width:90%;}
	.w100p{ width:100%;}

	.w5p{ width:5%;}
	.w15p{ width:15%;}
	.w25p{ width:25%;}
	.w35p{ width:35%;}
	.w45p{ width:45%;}
	.w55p{ width:55%;}
	.w65p{ width:65%;}
	.w75p{ width:75%;}
	.w85p{ width:85%;}
	.w95p{ width:95%;}

	.left{ text-align:left !important;}
	.right{ text-align:right !important;}
	.center{ text-align:center !important;}


	// 이메일
	.dateInput{
		{ $pr; $dib;}
		input.date, input.mdate{ $pr; z-index:2; background:none; $tal; margin:0; padding:0 4px; font-size:12px; font-family:'굴림체'; color:#333; width:85px; $fw7; $vam;}
		.before{ $pa; top:50%; left:0; z-index:1; width:100%; height:100%; padding:0 4px; margin-top:-11px; line-height:22px; border:1px solid transparent; box-sizing:border-box; font-size:12px; color:#ddd; font-family:'굴림체'; $fw7;}
		.before span{ color:#fff; opacity:0;}
	}

	// 로딩레이어
	.loading_layer{
		{ position:fixed; top:0; left:0; z-index:5000; width:100%; height:100%; background:rgba(0,0,0,0.0); color:rgba(255, 255, 255, 0.0); $fz:14px; $tac;}
		p{ $pa; top:45%; left:0; width:100%; margin-top:55px; font-family:'Verdana'; $fw7;}
	}

	// 커스텀 체크박스
	label.checkbox{
		{$pr; $dib; line-height:16px; $vam;}
		input{$pa; top:0; left:0; opacity:0; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0, 0, 0, 0);}
		input + span{
			{$pr; $db; width:100%; height:100%; padding-left:21px;}
			:before{ content:' '; $pa; top:0; left:0; $db; width:14px; height:14px; border:1px solid #aaa; background:#fff; color:#c00; $tac; line-height:14px; $fz:14px;}
		}
		input:checked + span:before{ content:'\2714'; $fw4;}
	}

	// 커스텀 라디오박스
	label.radio{
		{$pr; $dib; line-height:16px; $vam;}
		input{$pa; top:0; left:0; opacity:0; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0, 0, 0, 0);}
		input + span{
			{$pr; $db; width:100%; height:100%; padding-left:21px;}
			:before{ content:' '; $pa; top:0; left:0; $db; width:14px; height:14px; border:1px solid #aaa; background:#fff; color:#c00; $tac; line-height:14px; $fz:14px; border-radius:100%;}
		}
		input:checked + span:after{ content:' '; $pa; top:3px; left:3px; $db; width:10px; height:10px; background:#666; border-radius:100%;}
	}

	// 커스텀 셀렉트
	.selectBox{
		{$dib; $pr; border:1px solid #d6d6d6; background:#fff; cursor:pointer; border-radius:5px; $fz:12px; overflow:hidden; $vam;}
		:before, :after{ content:''; $pa; top:0; right:0; width:30px; height:100%; background:#f8f8f8; $tac;}
		:after{ content:'\2039'; top:50%; height:auto; line-height:20px; margin-top:-10px; background:none; color:#999; font-size:13px; transform:rotate(-90deg) scale(0.5,1); $fw8;}
		span.selected{ $pa; top:0; left:0; $db; height:100%; width:100%; padding:0 35px 0 10px; $fw3; color:#222; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; $fz:inherit;}
		span.selected:before{ content:' '; $dib; width:0; height:100%; $vam;}
		select{ $pr; z-index:2; opacity:0; -ms-filter:alpha(opacity=0); margin:0; padding:0 35px 0 10px; border:0; color:#333; $fz:inherit;}
	}

	// 컨펌, 경고 메세지 모달
	.MessageModal{
		{ position:fixed; top:0; left:0; z-index:6000; width:100%; height:100%; min-width:320px; background:rgba(0,0,0,0.5); font-size:13px; color:#333;}
		.MessageModalWrap{
			{ position:absolute; top:50%; left:50%; min-width:250px; margin:-50px 0 0 -125px; max-width:500px; background:#fff; box-shadow:2px 2px 3px rgba(0, 0, 0, 0.3); border-radius:5px; overflow:hidden;}
			> header{ padding:7px; font-weight:700; background:#999; color:#fff;}
			> div.text{ padding:10px; min-height:70px;}
			> footer{ padding:7px 15px; text-align:right; background:#f6f6f6;}
			> footer a{ $dib; margin-left:4px; $fw7; padding:2px 5px;}
			> footer a:focus{ background:#ddd;}
		}
	}

	span.uploadedFile{$dib; padding:3px 5px; border:1px solid #ccc; background:#eee; border-radius:3px;}
	.fileUploadArea{padding:10px 0;}
	.fileUploadArea + .fileUploadArea{padding-top:0;}
	.fileUploadImage{$dib; $vam;}
	.fileUploadImage img{$db; max-width:100px; max-height:100px;}
	.fileUploadImage i{$db; width:100px; height:100px; background-repeat:no-repeat; background-size:contain; background-position:center center;}
	.fileUploadArea2{padding:10px 0;}
	.fileUploadArea2 + .fileUploadArea2{padding-top:0;}
	.fileUploadArea2 p{$dib;}
</style>