<?php
if(BHCSS !== true) exit;

include 'common.bhcss.php';
$innerMaxWidth = 1200;
$footerHeight = 130;
$headerHeight = 60;

echo \BH\BHCss\BHCss::setResponseFontSizeByMin(100, 1000);
\BH\BHCss\BHCss::$variable['$colorA1'] = '#e6ecf2';
\BH\BHCss\BHCss::$variable['$colorA2'] = '#b8c2cc';
\BH\BHCss\BHCss::$variable['$colorA3'] = '#828b99';
\BH\BHCss\BHCss::$variable['$colorA4'] = '#3c4155';
\BH\BHCss\BHCss::$variable['$colorB1'] = '#713f73';
\BH\BHCss\BHCss::$variable['$colorB2'] = '#341133';
\BH\BHCss\BHCss::$variable['$colorC1'] = '#3de7a5';
\BH\BHCss\BHCss::$variable['$colorD1'] = '#c10';
\BH\BHCss\BHCss::$variable['$darkA1'] = '#40515e';
\BH\BHCss\BHCss::$variable['$darkB1'] = '#572c33';
\BH\BHCss\BHCss::$variable['$activeColorA1'] = \BH\BHCss\BHCss::$variable['$colorA3'];

\BH\BHCss\BHCss::$variable['$fontColorA1'] = '#585f7c';
\BH\BHCss\BHCss::$variable['$box'] = 'background:#fff; box-shadow:2px 2px 3px rgba(0,0,0,0.2);';
?>

<style type="text/scss">

	/* ---------------------------------------------------------------------
	*	Common
	* --------------------------------------------------------------------- */
	.colorTest{
		color:#e6ecf2; // $colorA1
		color:#b8c2cc; // $colorA2
		color:#828b99; // $colorA3
		color:#3c4155; // $colorA4
		color:#713f73; // $colorB1
		color:#341133; // $colorB2
		color:#3de7a5; // $colorC1
		color:#585f7c; // $fontColorA1
	}
	body{ font-size:13px; background:$colorA1; color:#222;}
	a.sBtn, button.sBtn{ display:inline-block; padding:3px 10px; background:#929599; color:#fff; vertical-align:middle; font-size:12px; line-height:16px;}
	a.mBtn, button.mBtn{ display:inline-block; min-width:100px; height:30px; padding:0 10px; text-align:center; background:#929599; color:#fff; vertical-align:middle; font-size:14px; line-height:18px;}
	a.bBtn, button.bBtn{ display:inline-block; min-width:200px; height:40px; text-align:center; padding:0 20px; background:#929599; color:#fff; vertical-align:middle; font-size:16px; line-height:24px;}
	a.sBtn:before, a.mBtn:before, a.bBtn:before{content:''; display:inline-block; width:0; height:100%; vertical-align:middle;}

	a.btn1, button.btn1{ background:#fff; color:#333; border:1px solid #ccc;}
	a.btn2, button.btn2{ background:$colorA4; color:#fff;}
	a.btn3, button.btn3{ background:$colorD1; color:#fff;}
	.left{ text-align:left !important;}
	.right{ text-align:right !important;}
	.center{ text-align:center !important;}

	input[type=email], input[type=text], input[type=password], input[type=tel], input[type=file], select, textarea {border-radius:0; height:30px; line-height:28px;}
	select{height:30px; line-height:28px;}

	.articleAction{
		~{padding-top:50px; text-align:center;}
		ul{display:inline-block; $cb--;}
		li{float:left;}
		a{display:inline-block; height:22px; width:70px; margin:0 5px; color:#fff; font-size:12px; background:$colorB1;}
		a:before{content:''; display:inline-block; width:0; height:100%; vertical-align:middle;}
		a span.num{font-weight:700;}
		a.already{ background:$colorB2;}
	}

	.leftBoardSearch, .left_btn{float:left; margin-top:20px; padding-bottom:20px;}
	.rightBoardSearch, .right_btn{ float:right; margin-top:20px; padding-bottom:20px;}
	.bottomBtn{ padding:30px 0; text-align:center;}
	.bottomBtn a + button, .bottomBtn button + a, .bottomBtn button + button, .bottomBtn a + a{margin-left:5px;}
	.moreViewBtn{ margin-top:20px; $tac;}
	.moreViewBtn a{$dib; width:300px; border:1px solid #ccc; line-height:30px; border-radius:5px; background:#f8f8f8;}

	.left_btn + .table, .right_btn + table{ clear:both;}
	.right_btn + .paging, .left_btn + .paging{ padding-top:0;}

	p.alert{ padding-bottom:20px; color:$colorD1;}

	.modalConfirm{ position:fixed; top:0; left:0; z-index:100; width:100%; height:100%; background:#999; background:rgba(0,0,0,0.1);}
	.modalConfirm form{ position:absolute; top:50%; left:50%; width:300px; padding:20px; margin:-80px 0 0 -150px; border:2px solid #333; background:#fff; text-align:center;}
	.modalConfirm p{ font-weight:700; padding-bottom:15px;}
	.modalConfirm .sPopBtns{ padding-top:10px;}
	.modalConfirm .sPopBtns button + *{margin-left:5px;}
	.modalConfirm .sPopBtns a + *{margin-left:5px;}

	.nothing{ text-align:center; color:#888; padding:20px 0;}

	span.secretDoc{position:relative; display:inline-block; width:16px; height:16px; overflow:hidden; vertical-align:middle;}
	span.secretDoc:before{content:''; display:block; width:10px; height:8px; margin:6px auto 5px; background:$colorA3; border-radius:2px;}
	span.secretDoc:after{content:''; position:absolute; top:1px; left:50%; display:block; width:5px; height:10px; border:1px solid $colorA3; border-radius:3px; transform:translate(-50%, 0);}
	span.newDoc{position:relative; display:inline-block; width:12px; height:14px; margin-left:3px; overflow:hidden; vertical-align:middle; border-radius:3px; background:#ff963f; color:#fff;}
	span.newDoc:before{content:'N'; display:block; height:100%; margin-bottom:5px; font-weight:800; font-size:10px; text-align:center; line-height:14px;}
	span.answerDoc{position:relative; display:inline-block; width:14px; height:14px; margin-right:3px; overflow:hidden; vertical-align:middle;}
	span.answerDoc:before{content:''; display:block; width:50%; height:50%; margin-bottom:100%; border:1px solid $colorA3; border-width:0 0 4px 1px; border-radius:50% 0 0 50%;}
	span.answerDoc:after{content:''; position:absolute; top:50%; right:0; display:block; border-left:8px solid $colorA3; border-top:5px solid transparent; border-bottom:5px solid transparent; transform:translate(0, -50%); margin-top:2px;}

	/* ---------------------------------------------------------------------
	*	Contents
	* --------------------------------------------------------------------- */
	#wrap{ width:100%; height:100%; min-width:<?= $innerMaxWidth + 20 ?>px;}
	#header{
		~{position:relative; z-index:2; background:#fff; box-shadow:0 2px 2px rgba(0, 0, 0, 0.05);}
		// #header:before{content:''; position:absolute; z-index:1; top:0; left:0; width:100%; height:24px; background:$colorA1;}
		#header_wrap{position:relative; width:<?= $innerMaxWidth ?>px; z-index:2; height:<?= $headerHeight ?>px; padding-left:200px; margin:0 auto;}
		h1{ position:absolute; top:0; left:0; height:100%; z-index:2; font-weight:800; font-size:18px; line-height:<?= $headerHeight ?>px; color:#555; background:#fff; text-align:center; width:200px;}
		h1 i{ margin-right:10px;}
		h1 i img{width:45px;}
		#gnb{
			~{height:24px; $cb--;}
			ul{float:right; height:100%; padding:1px 0; text-align:right; font-size:12px; $cb--;}
			li{ float:left; height:100%; display:inline-block; color:#fff; background:$colorA3; font-weight:700;}
			li.admin{background:$colorD1; color:#fff;}
			li a{display:block; height:100%; padding:0 10px;}
			li a:after{content:''; display:inline-block; width:0; height:100%; vertical-align:middle;}
			li + li{margin-left:1px;}
			li:hover{ color:#fff; background:$colorA2;}
			li.admin:hover{background:#f33; color:#fff; font-weight:700;}
		}

		#tnb{
			~{ width:100%; height:<?= $headerHeight - 24 ?>px; padding:3px 0; font-weight:700; font-size:15px;}
			ul{
				~{height:100%; $cb--;}
				> li{ position:relative; $fll; height:100%; width:20%;}
				> li > a{ display:block; height:100%; color:#333; text-align:center;}
				> li > a:after{content:''; display:inline-block; width:0; height:100%; vertical-align:middle;}
				> li:hover > a{background:$colorA1; color:#333;}
				//> li.active > a{background:#7a3b76; color:#fff;}
				> li.active > a{background:$activeColorA1; color:#fff;}
				ol{
					~{ position:absolute; left:0; top:100%; display:none; width:100%; line-height:1.2em; background:#fff; border:1px solid #fff; box-shadow:2px 2px 2px rgba(0,0,0,0.05);}
					li{ position:relative; font-size:11px; font-weight:700; color:#666; overflow:hidden;}
					li + li{margin-top:1px;}
					li a{display:block; height:100%; padding:5px 10px;}
					li:hover{background:$colorA1; color:#333;}
					li.active{ color:#fff; background:$activeColorA1;}
					li:first-child:before{ content:none;}
				} //ol
				> li:hover ol{display:block;}
			} //ul
		} //#tnb
	}

	#container{ padding:<?= $headerHeight ?>px 0 <?= $footerHeight ?>px; min-height:100%; width:<?= $innerMaxWidth ?>px; margin:-<?= $headerHeight ?>px auto 0;}
	#container_wrap{ position:relative; padding:0;}
	#container_wrap:after{ content:' '; display:block; clear:both;}

	#footer{
		~{margin-top:-<?= $footerHeight ?>px; height:<?= $footerHeight ?>px; background:#fff;}
		#footer_wrap{position:relative; width:<?= $innerMaxWidth ?>px; margin:0 auto;}
		#fnb{
			~{height:<?= $fnbHeight = 26 ?>px; background:$colorA3; color:#fff; font-size:13px;}
			ul{width:<?= $innerMaxWidth ?>px; margin:0 auto; text-align:center;}
			li{position:relative; display:inline-block; line-height:<?= $fnbHeight ?>px; padding:0 10px;}
			li:before{content:''; position:absolute; left:0; top:50%; height:10px; width:1px; margin-top:-5px; background:#ddd;}
			li:first-child:before{content:none;}
		}
		#footerInfo{
			~{position:relative; height:<?= $footerHeight - $fnbHeight ?>px; width:<?= $innerMaxWidth ?>px; margin:0 auto; padding:10px 0 0 240px; font-size:13px; color:#888; line-height:1.3;}
			h1{position:absolute; top:10px; left:0; width:200px; font-size:30px; font-weight:800; color:#333;}
			h1 img{max-width:100%;}
			dl{float:left;}
			dl + dl{margin-left:15px;}
			dt{display:inline-block;}
			dd{display:inline-block; font-weight:700;}
			dd:before{content:' : '; font-weight:400;}
			dl.address, dl.bNum{clear:left; margin-left:0;}
		}

		.copyright{clear:both; padding-top:10px; font-size:13px; color:#666;}
		.copyright b{font-weight:700; color:#333;}
	}

	#contents h2{ padding:7px 10px; font-size:16px; font-weight:700; background:$colorA1; color:$colorA4;}

	.BH_Popup{ position:absolute; top:0; left:0; z-index:10; border:1px solid #888;}
	.BH_PopupContent{ overflow-y:auto; overflow-x:hidden;}
	.BH_PopupContent img{max-width:100%; width:auto; height:auto;}
	.BH_PopupBtns{ line-height:21px; background:#333; color:white; font-size:12px;}
	.BH_PopupBtns a{cursor:pointer;}
	.BH_PopupBtns:after{ content:' '; display:block; clear:both;}
	.BH_PopupTodayClose{float:left; display:block; padding:5px 10px;}
	.BH_PopupClose{float:right; display:block; padding:5px 10px;}

	.guide{color:#999; font-size:11px;}
	ul.guide, p.guide{padding-top:5px;}
	i.requiredBullet{font-weight:700; color:#c10; float:left; margin-right:5px; transform:translate(0, -5px);}

	table.write{
		~{width:100%; table-layout:fixed; font-size:12px;}
		th{width:150px; padding:5px 10px; border:1px solid $colorA2; border-width:1px 0; text-align:left; background:$colorA1; color:$fontColorA1; height:40px; font-weight:700;}
		td{padding:5px 10px; border:1px solid $colorA1; border-width:1px 0; text-align:left;}
		label + label{margin-left:10px;}
		select + select{margin-left:10px;}
		p + p{margin-top:5px;}
		textarea{height:250px;}
	}

	table.list{
		~{width:100%; table-layout:fixed; font-size:0.9em;}
		th{width:100px; padding:5px 10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:center; background:#F2F3F5;}
		td{padding:10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:center;}
		a{text-decoration:underline;}
		img{max-width:200px; max-height:100px;}
	}

	table.view{
		~{width:100%; table-layout:fixed;}
		th{width:150px; padding:10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:left; background:#F2F3F5;}
		td{padding:10px; border:1px solid #D3D6DF; border-width:1px 0; text-align:left;}
	}

	h2 + .boardList,
	h2 + table.list{margin-top:20px;}

	.paging{
		~{clear:both; padding:20px; text-align:center;}
		span, a, strong{display:inline-block; padding:0 10px; border:1px solid #D3D6DF; color:#D3D6DF; line-height:30px; height:30px; overflow:hidden; font-size:13px;}
		a{color:inherit;}
		strong{background:#6EA9AF; color:white; border:0; font-size:16px;}
		.first, .prev, .prevp, .nextp, .next, .last{width:30px;}
		.first:before{content:'\f048'; display:block; height:100%; font-family:'FontAwesome';}
		.prev:before{content:'\f100'; display:block; height:100%; font-family:'FontAwesome';}
		.prevp:before{content:'\f104'; display:block; height:100%; font-family:'FontAwesome';}
		.nextp:before{content:'\f105'; display:block; height:100%; font-family:'FontAwesome';}
		.next:before{content:'\f101'; display:block; height:100%; font-family:'FontAwesome';}
		.last:before{content:'\f051'; display:block; height:100%; font-family:'FontAwesome';}
	}

	#contents.board{
		~{ padding:20px; width:970px; margin:20px auto; $box; $cb--;}
		.categoryTab{
			~{margin-top:20px; padding-bottom:10px;}
			ul:after{$cb;}
			li{float:left;}
			li a{display:block; padding:0 10px; font-size:13px; color:#333;}
			li.active a{font-weight:700; color:$colorD1;}
			li + li{border-left:1px solid #ccc;}
			li.parent a{color:#999;}
		}
		h2 + section.boardList{margin-top:20px;}
		h2 + #BoardWriteForm{margin-top:20px;}
	}

	.BoardView{
		header{
			~{ border-bottom:1px solid $colorA1;}
			div{ padding:40px 10px 10px; border-bottom:1px solid $colorA1; font-weight:700; font-size:17px;}
			ul{ padding:10px; font-size:0.87em; color:$colorA3;}
			ul:after{ content:' '; display:block; clear:both;}
			li{ float:right; margin-left:15px;}
			li.mname{ float:left; margin-left:0;}
		}
		.contents{ padding:20px 10px; min-height:300px;}
		.contents img{max-width:100% !important;}
		.links{$dt; border-bottom:1px solid $colorA1;}
		.links dt{$dtc; width:50px; color:$colorA2; font-weight:700; font-size:11px; vertical-align:middle; padding:5px 10px;}
		.links dd{$dtc; vertical-align:middle; padding:5px 10px;}
		.links a{font-weight:700; font-size:12px; text-decoration:underline;}
		div.image{padding-bottom:10px;}
	}

	.youtube{margin-bottom:10px;}

	#boardSelectModal .modal_wrap{height:auto;}

	ul.boardSelectForW{
		~{padding:20px;}
		li{padding:2px; text-align:center; font-weight:700; color:$colorA4;}
		li a{display:block; padding:20px; border:2px solid $colorA1;}
		li a:hover{background:$colorA4; border-color:$colorA3; color:#fff;}
	}

	.fileUploadArea2 span.fileName{display:inline-block; padding-left:5px; font-size:11px; font-weight:700; color:$colorA3;}
	.fileUploadArea2 .fileUploadBtn{background:$colorA3;}

	#Reply{ padding-top:50px;}
	#Reply h3{font-weight:700; padding-bottom:5px;}
	#Reply h3 span{font-weight:400; color:#888; font-size:12px;}
	.replyWrite{
		~{ margin-bottom:20px; padding-bottom:5px;}
		fieldset{padding:5px 0;border-top:1px solid $colorA2;}
		fieldset.user{$dt; background:$colorA1;}
		fieldset.user dl{$dtc;}
		fieldset.user dt{display:inline-block; width:100px; text-align:center; font-size:12px; font-weight:700; color:$colorA3;}
		fieldset.user dd{ display:inline-block;}
		fieldset.text{ position:relative; padding-right:150px;}
		fieldset.text textarea{ height:100px; margin:0;}
		fieldset.text .btn{ position:absolute; top:5px; right:0; width:140px; height:100px;}
		fieldset.text .btn button{ width:100%; height:100%; background:$colorA3; font-size:1.8em; color:#fff; border-radius:5px; text-align:center;}
		div.option{
			~{ padding:0; $cb--;}
			> span{float:left; width:80px;}
			.fileUploadArea2{padding:0; float:left;}
		}
	}

	.replyAnswer{
		fieldset.text{padding-right:0;}
	}

	.modifyForm{
		fieldset.text{padding-right:0;}
		form:after{$cb;}
		.pwdinp{float:left; width:70%; padding:0; border:0;}
		.pwdinp p{display:inline-block; font-size:11px; margin-right:5px; color:$colorA3;}
	}

	.replyDelete{
		~{position:absolute; top:50%; left:50%; padding:10px 20px; margin-bottom:0; border:1px solid $colorA1; background:rgba(255, 255, 255, 0.8); transform:translate(-50%, -50%); text-align:center;}
		fieldset{border:0;}
		fieldset.pwd{
			p{font-size:11px; margin-right:5px; color:$colorA3; line-height:15px; vertical-align:middle; font-weight:700;}
			span.pwdinp{display:block; padding-top:10px; vertical-align:middle;}
		}
		.btn{padding-top:10px;}
	}

	#replyListContents{
		~{ font-size:0.90em;}
		article{ position:relative; border-bottom:1px solid $colorA2;}
		article:first-child{border-top:1px solid $colorA2;}
		header{ padding:7px; background:$colorA1; $cb--;}
		header b{display:inline-block; font-weight:700; margin-right:5px;}
		.btns{float:right;}
		.btns a{ float:left; display:block; padding:2px 5px; background:$colorA4; color:#fff; font-size:0.9em; border-radius:2px;}
		.btns a + a{margin-left:5px;}

		.btns a.replyActionBtn{font-weight:400; border:1px solid $colorA4; line-height:11px; background:$colorA3; color:#fff;}
		.btns a.replyActionBtn.already{font-weight:700; background:$colorA4;}
		.btns a.replyReportActionBtn{background:$colorB1; border-color:$colorB2; color:#fff;}
		.btns a.replyReportActionBtn.already{background:$colorB2;}

		form .btn{ text-align:right;}
		form .btn button + button{margin-left:5px;}

		.comment{ padding:10px; line-height:1.5em;}
		.comment b{ color:#999; margin-right:10px;}

		a.pwdView{color:$colorB1; display:inline-block; margin-left:5px; font-weight:700;}
	}

	.repLayer{
		~{ position:fixed; top:0; left:0; z-index:100; width:100%; height:100%; background:#999; background:rgba(0,0,0,0.1);}
		form{ position:absolute; top:50%; left:50%; width:400px; padding:20px; margin:-80px 0 0 -150px; border:2px solid #333; background:#fff; text-align:center;}
		div.btn{ padding-top:10px;}
		div.btn button + button{margin-left:5px;}
		.targetContent{ text-align:left; font-size:0.9em;}
		textarea{ height:80px;}
		fieldset.user{ text-align:left;}
		fieldset.user dl, .repLayer fieldset.user dt, .repLayer fieldset.user dd{ display:inline-block;}
		fieldset.user dd input{ width:100px;}
		fieldset.pwd{ text-align:center;}
		fieldset.pwd p{ font-weight:700; padding:15px 0 5px;}
	}

	#contents.login2{
		~{ padding:100px 0;}
		#login_wrap2{
			~{ width:400px; padding:20px; margin:0 auto; $box;}
			fieldset{
				~{ padding-top:20px;}
				:after{ content:' '; display:block; clear:both;}
				label{ display:block; padding-left:10px; line-height:30px; font-weight:700; $cb--;}
				label span.tt{ float:left; display:block; width:33%; line-height:30px;}
				input{ float:left; width:67%;}
				label + label{margin-top:10px;}
			}
			#LoginRemember{ padding:20px 10px 0;}
			#LoginConfirm{ margin-top:30px; padding-top:20px; border-top:1px solid $colorA2;}
			#LoginConfirm button{ width:100%; margin:0; font-weight:700;}
			#link a{ width:100%; margin-top:5px; font-weight:700;}
		}
	}

	#contents.register{
		~{ width:800px; padding:20px; margin:50px auto; $box;}
		h3{padding:5px; font-weight:700; font-size:12px; color:#fff; background:$colorA3;}
		form{padding-top:20px;}
		fieldset > .txt{ height:200px; padding:10px; border:1px solid $colorA2; overflow-y:scroll;}
		fieldset > p.chk{ padding:5px; text-align:right; font-size:12px; color:#666; font-weight:700;}
		fieldset + fieldset{margin-top:20px;}
		form > p{padding:10px 0; margin-top:20px; border:1px solid $colorA2; border-width:1px 0; text-align:center; font-weight:700; font-size:13px;}
	}

	#contents.registerForm{
		~{ width:600px; margin:50px auto; padding:20px; $box;}
		p.alert{margin-top:20px;}
		table.write{margin-top:20px;}
		p.alert + table.write{margin-top:0;}
		input{
			~[type=text],~[type=password],~[type=tel],~[type=email],~[type=number]{width:60%;}
			+ button{margin-left:5px;}
		}
	}

	#contents.findIDPW{
		~{padding:50px 0; width:1000px; margin:0 auto;}
		.findWrap{
			~{float:left; width:48%; padding:20px; $box;}
			~~ .findWrap{margin-left:4%;}
			> p{padding:30px 0; text-align:center; color:#999; font-size:13px;}
			form{
				fieldset{height:130px;}
				dl{$dt;}
				dl + dl{margin-top:10px;}
				dt{$dtc; width:150px; font-size:12px; font-weight:700;}
				dd{$dtc;}
				input{
					~[type=text],~[type=password],~[type=tel],~[type=email],~[type=number]{width:100%;}
				}
			}
		}
	}

	#lnb{
		~{ float:left; width:200px; padding:10px; margin-top:20px; $box;}
		h2{ height:120px; padding-top:48px; font-size:20px; background:$colorA4; text-align:center; color:#fff;}
		nav li a{display:block; padding:15px 10px; font-size:13px; color:$fontColorA1;}
		nav li.active, nav li:hover{background:$colorA1;}
		nav li + li{border-top:1px solid $colorA2;}
		+ #contents{ float:right; width:980px; padding:20px; margin-top:20px; $box;}
	}

	div.MyPageMain{ font-size:14px;}
	div.MyPageMain h3{margin-top:20px; padding:5px; font-weight:700; font-size:14px; color:$colorA3;}

	#PasswordForm{
		~{ padding:100px 0; text-align:center;}
		form{ width:400px; padding:3px 0; margin:0 auto;}
		p{ margin-bottom:30px; color:#888; font-weight:700;}
	}

	#WithdrawForm{
		~{ padding:20px 0;}
		p{ padding:20px; background:#eee;}
		ul{ padding:30px;}
		li{ padding:5px 0;}
		.reason_etc{ display:table; width:100%;}
		.reason_etc label{ display:table-cell; width:10%; margin:0;}
		.reason_etc span{ display:table-cell; width:90%;}
		.reason_etc span textarea{ margin:0; height:100px;}
	}

	section.boardList{
		~{font-size:13px;}
		ul{$dt;}
		li{$dtc; text-align:center; padding:10px 5px;}
		li.check{width:40px;}
		li.num{width:60px;}
		li.category{width:140px;}
		li.name{width:120px;}
		li.date{width:140px; font-size:12px;}
		> header{
			~{background:$colorA3; color:#fff;}
			li{padding:5px;}
		}
		.articles{
			li{border-bottom:1px solid $colorA1;}
			li.subject{text-align:left;}
		}
	}

	section.galleryBoardList{
		~{font-size:13px;}
		> header{
			~{padding:5px 10px; text-align:right;}
		}
		.noticeArticles{
			~{margin-top:10px;}
			> header{
				~{background:$colorA3; color:#fff;}
				li{padding:5px;}
			}

			ul{$dt;}
			li{$dtc; text-align:center; padding:10px 5px; border-bottom:1px solid #ccc;}
			li.check{width:40px;}
			li.subject{text-align:left; font-weight:700;}
			li.num{width:60px;}
			li.name{width:120px;}
			li.date{width:140px;}
		}
		.articles{
			~{padding-top:20px; $cb--;}
			article{float:left; width:24.4%; margin-left:0.8%; border:1px solid #ccc; padding:5px;}
			article:nth-child(4n+1){clear:left; margin-left:0;}
			article:nth-child(n+5){margin-top:10px;}
			ul{position:relative; $cb--;}
			li.check{position:absolute; top:0; left:0;}
			li.thumb img{width:100%;}
			li.thumb i{display:block; height:0; padding-bottom:75%; background:no-repeat center center; background-size:cover;}
			li.subject{text-align:left; font-weight:700; margin-top:5px; line-height:16px; max-height:32px; overflow:hidden;}
			li.name{padding-top:5px;}
			li.hit{padding-top:5px; font-size:12px;}
			li.hit:before{content:'조회수 : ';}
			li.recommend{float:left; width:50%; padding-top:5px; font-size:12px; font-weight:700;}
			li.recommend:before{content:'추천 : ';}
			li.date{float:left; width:50%; padding-top:5px; font-size:11px; color:#888; text-align:right;}
		}
	}

	.leftSysBtn{padding-top:10px; $cb--;}
	.leftSysBtn > *{margin:0 2px;}

	/* 모달창 */
	.modal_layer{
		~{position:fixed; top:0; left:0; z-index:5000; display:none;  width:100%; height:100%; background:rgba(0,0,0,0.3);}
		.modal_wrap{position:fixed; top:50%; left:50%; z-index:2; width:400px; height:300px; padding-top:50px; max-width:90%; max-height:90%; background:#F7F7F7; transform:translate(-50%, -50%);}
		.modal_header{
			~{position:absolute; top:0; left:0; width:100%; height:50px; background:#2C3E50;}
			h1{padding:0 80px 0 20px; height:100%; font-size:16px; color:white; overflow:hidden; line-height:50px;}
			.close{position:absolute; top:16px; left:100%; margin-left:-38px; line-height:16px; font-size:18px; color:white; cursor:pointer; text-align:center;}
			.close{margin-left:-35px; font-size:24px;}
			.close i{
				~{font-size:16px;}
				:before,:after{background:#fff;}
			}
		}

		.submit_btn button{height:40px; font-size:14px; width:100px;}
		.modal_contents{height:100%; overflow-y:auto;}
		.modal_contents .modal_inner{padding:20px;}
	}

	#contents.main{
		~{width:970px; margin:0 auto; padding:20px 0;}
		section > header{position:relative; padding:7px 10px; background:$colorA1; line-height:20px;}
		section > header a.more{position:absolute; top:7px; right:10px; font-size:12px; font-weight:700; color:$colorA3; text-decoration:underline;}
		section > header a.more i{display:inline-block; width:6px; height:6px; margin-left:3px; border:1px solid $colorA3; border-width:1px 1px 0 0; vertical-align:middle; transform:rotate(45deg) translate(0, -1px);}
		section > header h2{padding:0; background:none;}

		.boardArticles{
			~{padding:10px; $box; color:$fontColorA1;}
			.articles{
				li{border-bottom:1px solid $colorA1;}
				a{$dt; vertical-align:middle;}
				a > * > b{display:inline-block; line-height:20px; height:20px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;}
				div.title{$dtc; padding:7px 10px; font-weight:700;}
				div.title > b{max-width:85%;}
				div.name{$dtc; width:100px; padding:7px 5px; text-align:center;}
				div.date{$dtc; width:90px; padding:7px 10px; text-align:right; font-size:11px; color:$colorA3;}
			}
		}
		.galleryArticles{
			~{padding:10px 10px 20px; $box;}
			.articles{
				~{margin-top:10px;}
				:after{$cb;}
				li{position:relative; float:left; width:24.4%; margin-left:0.8%; border:1px solid #ccc; padding:5px;}
				li:nth-child(4n+1){clear:left; margin-left:0;}
				.thumb img{width:100%;}
				.thumb i{display:block; height:0; padding-bottom:75%; background:no-repeat center center; background-size:cover;}
				.info{position:absolute; top:0; left:0; z-index:1; display:none; width:100%; height:100%; padding:20px; background:rgba(0,0,0,0.5); color:#fff; line-height:16px;}
				div.title{font-weight:700; min-height:48px;}
				div.date{font-size:11px; color:#ccc;}
				li:hover .info{display:block;}

			}
		}

		#mainNotice{float:left; width:49%; margin-bottom:20px; height:250px;}

		#mainFreeBoard{float:right; width:49%; margin-bottom:20px; height:250px;}

		#mainGalleryBoard{clear:both;}
	}

	#MemberWriteForm{
		~{margin-top:20px;}
	}

	#contents.terms{
		~{$box; width:970px; padding:20px; margin:20px auto;}
		> div.text{margin-top:10px; padding:10px; min-height:300px; border:1px solid $colorA2;}
	}

	#contents.introduce{
		~{$box; width:970px; padding:20px; margin:20px auto;}
	}

	/* ---------------------------------------------------------------
	 *			메세지
	 --------------------------------------------------------------- */
	.read{color:#6086b1;}
	.notRead{color:#c33;}
	
	#messageModal .modal_contents{position:relative; padding-bottom:84px;}
	#messageModal .modal_wrap{height:450px;}

	#messageChatWrap{
		~{position:relative; height:100%;}
		> div{position:absolute; bottom:0; left:0; width:100%; max-height:100%; padding:20px 20px 0; overflow-y:auto;}
		> div:after{content:''; display:block; height:20px; clear:both;}
		button.beforeReadBtn{background:rgba(255,255,255,0.6); color:#000; width:100%; height:28px; text-align:center; font-size:12px;}
		article{position:relative; clear:both; float:left; padding-top:10px; max-width:90%;}
		article.myMsg{float:right; text-align:right;}
		article div.msg{padding:10px 15px; background:#f2be1f; border-radius:10px; font-weight:700; text-align:left;}
		article div.notRead{margin-top:5px; font-size:11px; font-weight:700; color:#666;}
		article div.date{padding:5px 5px 0; font-size:11px; text-align:right; color:rgba(0,0,0,0.5);}
		article div.img{margin-bottom:5px; border-radius:5px; overflow:hidden;}
		article div.img img{max-width:200px; max-height:200px;}
		article div.file{display:inline-block; margin-bottom:5px; padding:4px 10px; background:#ddd; border-radius:3px; text-decoration:underline; font-weight:700; font-size:12px;}
	}
	#messageWriteWrap{
		~{position:absolute; bottom:0; left:0; width:100%; height:83px; padding-top:24px; background:#fff;}
		form{height:100%;}
		textarea{float:left; width:80%; height:100%; border:0;}
		button[type=submit]{float:right; width:50px; height:50px; margin:5px 5px 0 0; background:#333; color:#fff;}
		.fileUploadArea2{position:absolute; top:0; left:0; width:100%; clear:both; padding:0; border:1px solid #ccc; border-width:1px 0; background:#fff;}
		.fileUploadArea2 .fileName{line-height:22px;}
		.fileUploadArea2 button{height:22px; background:#666;}
	}

	.messageView{
		~{padding:20px 20px 0;}
		> header{
			~{line-height:22px; font-size:14px; border:2px solid $colorA3; background:#fff;}
			> div{$dt; padding:5px 0;}
			> div + div{border-top:1px solid $colorA2;}
			dl{$dtc; vertical-align:middle;}
			dt{display:inline-block; padding:0 15px; font-weight:700; color:#999;}
			dd{position:relative; display:inline-block; padding:0 15px; font-weight:700;}
			dd:before{content:''; position:absolute; top:50%; left:0; width:1px; height:10px; margin-top:-5px; background:rgba(0,0,0,0.2);}
			dl.readDate{display:block;}
		}
		.cont{margin-top:10px; height:280px; border:2px solid $colorA3; overflow-y:auto; background:#fff;}
		.cont .img{text-align:center;}
		.cont img{max-width:100%; max-height:200px;}
		.cont > table{width:100%; table-layout:fixed;}
		.cont > table > tr > td,
		.cont > table > tbody > tr > td{padding:5px 10px; line-height:30px;}
	}

	.messageWrite{
		~{padding:20px;}
		table.write{
			~{background:#fff;}
			th{width:20%; text-align:center;}
		}
	}

	#userMenuPopup{
		~{box-shadow:2px 2px 2px rgba(0,0,0,0.2);}
		li{padding:2px;}
		li + li{border-top:1px solid #ddd;}
		button{height:22px; width:80px; font-size:12px;}
		button:hover{background:#999; color:#fff;}
	}
</style>
