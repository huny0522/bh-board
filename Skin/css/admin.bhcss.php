<?php
include 'common.css.php';

use BH\BHCss\BHCss;

BHCss::$variable['$w'] = '1200px';
BHCss::$variable['$w2'] = '1400px';
BHCss::$variable['$sidew'] = '200px';
BHCss::$variable['$c1'] = 'rgb(120, 140, 160)';
BHCss::$variable['$c2'] = 'rgb(90, 115, 140)';
BHCss::$variable['$c3'] = 'rgb(40, 60, 80)';
BHCss::$variable['$c4'] = 'rgb(10, 25, 40)';
?>
<style>
	/* ---------------------------------------------
	*	adminStyle.css LIST
	* 0. Commeon
	* 1. Header & Footer
	* 2. Contents
	* 3. Login
	* 4. Member Auth Modal
	--------------------------------------------- */

	/* ---------------------------------------------
	* 0. Commeon
	--------------------------------------------- */
	html, body{ font-size:13px;}
	body{ height:100%; overflow-y:hidden;}
	body:before{ content:' '; position:fixed; top:0; left:0; z-index:-1; width:100%; height:100%; background:#f9f9f9; background:linear-gradient(rgb(153, 153, 153), rgb(102, 102, 102));}
	label + label{ margin-left:10px;}
	textarea{ width:100%;}
	input.date{ width:80px;}
	.requiredBullet{color:red;}
	.bottomBtn{padding:20px 0 40px; text-align:center;}
	.sBtn, .mBtn, .bBtn{display:inline-block; padding:0 10px; margin:0 5px; background:rgb(120, 140, 160); background:radial-gradient(rgb(120, 140, 160), rgb(90, 115, 140)); color:#fff; font-weight:bold; box-shadow:0 0 5px rgba(0,0,0,0.1); $vam; $fw7; $tac; white-space:nowrap;}
	button.bBtn[type=submit]{ background:rgb(51, 71, 91); background:radial-gradient(rgb(40, 60, 80), rgb(10, 25, 40)); color:#fff;}
	.sBtn{ height:21px; font-size:11px; border-radius:2px; padding:0 5px; background:radial-gradient(rgb(256, 256, 256), rgb(256, 256, 256), rgb(245, 245, 245)); color:rgb(40, 60, 80); border:1px solid rgb(180, 180, 180); box-shadow:0 0 3px rgba(0,0,0,0.05);}
	a.sBtn{ line-height:19px; color:rgb(40, 60, 80);}
	.mBtn{ height:26px; min-width:60px; font-size:12px; border-radius:3px;}
	a.mBtn{ line-height:26px; color:#fff;}
	.bBtn{ height:35px; min-width:120px; font-size:15px; border-radius:5px;}
	a.bBtn{ line-height:35px; color:#fff;}

	.paging{clear:both; padding:20px; text-align:center;}
	.paging span, .paging a, .paging strong{display:inline-block; padding:0 10px; border:1px solid #D3D6DF; color:#D3D6DF; line-height:30px; height:30px; overflow:hidden; font-size:13px;}
	.paging a{color:inherit;}
	.paging strong{background:#6EA9AF; color:white; border:0; font-size:16px;}
	.paging .first, .paging .prev, .paging .prevp, .paging .nextp, .paging .next, .paging .last{width:30px;}
	.paging .first:before{content:'\f048'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .prev:before{content:'\f100'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .prevp:before{content:'\f104'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .nextp:before{content:'\f105'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .next:before{content:'\f101'; display:block; height:100%; font-family:'FontAwesome';}
	.paging .last:before{content:'\f051'; display:block; height:100%; font-family:'FontAwesome';}

	.alert, .error{color:#e00; padding-bottom:10px;}

	p.nothing{ padding:50px 0; $tac; $fz:13px; color:#888;}

	.modalConfirm{ position:fixed; top:0; left:0; z-index:100; width:100%; height:100%; background:#999; background:rgba(0,0,0,0.1);}
	.modalConfirm form{ position:absolute; top:50%; left:50%; width:300px; padding:20px; margin:-80px 0 0 -150px; border:2px solid #333; background:#fff; text-align:center;}
	.modalConfirm p{ font-weight:700; padding-bottom:15px;}

	/* ---------------------------------------------
	* 1. Header & Footer
	--------------------------------------------- */
	#wrap{ height:100%; padding-top:80px; color:#243854; min-width:$w;}
	#header{position:fixed; top:0; left:0; width:100%; z-index:15; background:#112233; background:linear-gradient(90deg, rgb(17, 34, 51), rgb(45, 93, 138)); color:white; box-shadow:0 3px 4px rgba(0,0,0,0.2);}
	#header:before{ content:' '; position:absolute; top:50px; left:0; z-index:1; height:30px; width:100%; background:rgba(0, 0, 0, 0.5); box-sizing:border-box;}
	#header_wrap{ position:relative; padding-top:50px; width:$w2; margin:0 auto; transition:0.3s;}
	#header h1{position:absolute; top:0; left:0; padding:0 10px; font-family: 'Consolas', 'Verdana'; font-size:28px; color:#fff; line-height:50px; text-shadow:0 0 3px rgba(255,255,255,0.7);}
	#header h1 img{vertical-align:top;}
	#header h1 span{display:inline-block; vertical-align:top;}
	#gnb{position:absolute; top:0; right:0; padding:10px 15px;}
	#gnb ul:after{content:' '; display: block; clear:both;}
	#gnb li{float:left; margin-left:10px; color:#EEE; padding:0 5px; text-shadow:1px 1px 2px rgba(0,0,0,0.3); font-weight:bold;}
	#gnb li:first-child{margin-left:0;}
	#gnb li.logout a:before{font-family:'FontAwesome'; content:'\f011'; margin-right:5px; color:white; font-weight:normal;}
	#gnb li.home a:before{font-family:'FontAwesome'; content:'\f015'; margin-right:5px; color:white; font-weight:normal;}

	#tnb{
		{ position:relative; z-index:5; $fw7; padding-left:20px;}
		ul{position:relative; margin:0 auto;}
		ul:after{content:' '; display:block; clear:both;}
		ul > li{float:left; border-left:1px solid rgba(186, 229, 255, 0.2); color:#E8EAEE; line-height:30px;}
		ul > li a{display:block; width:100%; height:100%; padding:0 15px;}
		ul > li.Active > span a{background:#f4f4f4; color:#243854; font-weight:bold;}
		ul > li:first-child{border-left:0;}

		:hover li.Active ol{ $dn; }
		:hover li:hover ol{ $db; }
		ol{position:absolute; top:27px; margin-left:-5px; display:none; z-index:3; border:1px solid rgba(0, 0, 0, 0.2); border-top:0; background:#fff; box-shadow:2px 2px 2px rgba(0, 0, 0, 0.2); min-width:120px;}
		ol > li{ padding:1px; color:#333; line-height:28px;}
		ol > li:hover{ background:#f6fafd;; color:rgb(45, 93, 138); }
		ol > li.Active{ color:#fff; $fw7; }
		ol > li.Active a{ background:rgb(45, 93, 138);}
	}

	#wrap #container{ height:100%; overflow-x:hidden; overflow-y:scroll;}
	#wrap #container_wrap{ $pr; min-height:100%; width:$w2; padding:40px 30px 40px 250px; background:#f8f8f8; background:radial-gradient(rgb(255, 255, 255), rgb(245, 245, 245), rgb(240, 240, 240), rgb(230, 230, 230)); margin:0 auto; transition:0.3s; overflow-x:hidden;}
	#wrap.sideHidden #container_wrap{ width:$w; padding-left:50px;}
	#wrap.sideHidden #header_wrap{ width:$w;}

	#sideMenu{
		{ position:absolute; top:0; left:0; z-index:1; width:200px; height:100%; background:rgb(233, 240, 253); background:linear-gradient(180deg, rgb(34, 34, 34), rgb(68, 68, 68)); color:#fff; transition:transform 0.3s; $fw7; box-shadow:2px 0 3px rgba(0,0,0,0.4);}
		h2{$tac; line-height:100px; font-size:18px; background:#111; background:linear-gradient(0deg, rgb(51, 51, 51), rgb(85, 85, 85)); margin:0;}
		ul{ padding-top:10px;}
		li{ border-bottom:1px solid rgba(255, 255, 255, 0.07);}
		li a{$db; width:100%; height:100%; padding:15px 20px; color:#aaa;}
		li a:hover{ color:#fff;}
		li.Active a{ color:#fff; font-size:15px; background:rgba(0,0,0, 0.3);}
		#sideClose{ position:absolute; top:10px; left:100%; display:block; width:15px; line-height:30px; text-align:center; vertical-align:middle; border-radius:0 4px 4px 0; background:rgb(85, 85, 85); box-shadow:2px 0 3px rgba(0,0,0,0.4);}
		#sideClose i{transition:0.7s;}

	}
	#wrap.sideHidden #sideMenu{
		{ transform:translate3d(-200px, 0, 0);}
		#sideClose i{transform:rotate(180deg);}
	}

	/* ---------------------------------------------
	* 2. Contents
	--------------------------------------------- */
	h2{font-size:23px; margin-bottom:15px; padding-left:5px; $fw4;}

	#BHCategory{float:left;}
	#BHMenuConfig{float:right; width:500px;}

	table{
		{box-shadow:0 0 4px rgba(0,0,0,0.1);}
		.guide{ color:#89a; font-size:12px; line-height:1.5em;}
	}

	table.write{
		{ width:100%; table-layout:fixed; border-top:2px solid $c3; font-size:12px;}
		th{ width:150px; padding:15px 10px; border:1px solid rgb(215, 220, 225); border-width:1px 0; text-align:left; background:rgb(240, 245, 250); color:$c2;}
		td{padding:5px 10px; border:1px solid rgb(220, 225, 230); border-width:1px 0; text-align:left; background:#fff;}
	}

	table.list{
		{ width:100%; font-size:12px;}
		thead tr{ background:rgb(50, 70, 80); background:linear-gradient(0deg, rgb(10, 25, 40), rgb(20, 40, 60));}
		th{padding:10px; text-align:center; color:rgb(220, 230, 240);}
		td{padding:15px 10px; border:1px solid rgb(220, 225, 230); border-width:1px 0; text-align:center; background:#fff;}
		a{text-decoration:underline;}
		img{max-width:200px; max-height:100px;}
	}


	table.view{width:100%; table-layout:fixed; font-size:12px; border-top:2px solid $c3; font-size:12px;}
	table.view th{width:150px; padding:15px 10px; border:1px solid rgb(215, 220, 225); border-width:1px 0; text-align:left; background:rgb(240, 245, 250);}
	table.view td{padding:15px 10px; border:1px solid rgb(220, 225, 230); border-width:1px 0; text-align:left; background:#fff;}

	.boardSearch{padding:10px 0; line-height:36px;}
	.boardSearch label{ padding-left:10px; font-weight:700; font-size:13px;}
	.boardSearch label + span{ padding-right:10px;}
	.boardSearch button[type=submit]{margin:0; line-height:1.1;}

	.BoardView{ clear:both;}
	.BoardView header{ border-top:2px solid #555; border-bottom:1px solid #ccc; background:#fff;}
	.BoardView header div{ padding:1.0rem; border-bottom:1px solid #ddd; background:rgb(240, 245, 250);}
	.BoardView header ul{ padding:1.0rem; font-size:0.87em;}
	.BoardView header ul:after{ content:' '; display:block; clear:both;}
	.BoardView header li{ float:right; margin-left:1.5rem;}
	.BoardView header li.mname{ float:left; margin-right:0;}
	.BoardView .contents{ padding:2.0rem 1.0rem;}
	.BoardView .contents img{max-width:100% !important;}

	//.left_btn{float:left; padding:20px 0;}

	.leftBoardSearch, .left_btn{float:left; margin-top:20px; padding:0; padding-bottom:20px;}
	.rightBoardSearch, .right_btn{ float:right; margin-top:20px; padding-bottom:20px;}
	.bottomBtn{ margin-top:20px; text-align:center;}
	.moreViewBtn{ margin-top:20px; $tac;}
	.moreViewBtn a{$dib; width:300px; border:1px solid #ccc; line-height:30px; border-radius:5px; background:#f8f8f8;}

	.left_btn + .table, .right_btn + table{ clear:both;}
	.right_btn + .paging, .left_btn + .paging{ padding-top:0;}
	h2 + .right_btn{ margin-top:-20px;}

	#Reply{ padding-top:50px;}
	#replyWrite{ margin-bottom:20px; padding:10px; border:1px solid #ddd; background:#fafafa;}
	#replyWrite fieldset.user dl,
	#replyWrite fieldset.user dt,
	#replyWrite fieldset.user dd{ display:inline-block;}
	#replyWrite fieldset.text{ position:relative; padding-right:150px;}
	#replyWrite fieldset.text textarea{ height:100px; margin:0;}
	#replyWrite fieldset.text .btn{ position:absolute; top:0; right:0; width:140px; height:100%;}
	#replyWrite fieldset.text .btn button{ width:100%; height:100%; background:#555; font-size:1.8em; color:#fff; border-radius:0.5rem; text-align:center;}
	#replyWrite div.option{ padding:5px 0 0;}

	#replyListContents{ border-top:2px solid #555; font-size:0.90em;}
	#replyListContents article{ border-bottom:1px solid #ddd;}
	#replyListContents header{ padding:0.7rem; background:#f4f4f4;}
	#replyListContents .btns{float:right;}
	#replyListContents .btns a{ display:inline-block; padding:2px 5px; background:#555; color:#fff; font-size:0.9em; border-radius:2px;}
	#replyListContents .comment{ padding:1.0rem; line-height:1.5em;}
	#replyListContents .comment b{ color:#999; margin-right:10px;}

	.repLayer{ position:fixed; top:0; left:0; z-index:100; width:100%; height:100%; background:#999; background:rgba(0,0,0,0.1);}
	.repLayer form{ position:absolute; top:50%; left:50%; width:40.0rem; padding:2.0rem; margin:-8.0rem 0 0 -15.0rem; border:2px solid #333; background:#fff; text-align:center;}
	.repLayer div.btn{ padding-top:1.0rem;}
	.repLayer .targetContent{ text-align:left; font-size:0.9em;}
	.repLayer textarea{ height:8.0rem;}
	.repLayer fieldset.user{ text-align:left;}
	.repLayer fieldset.user dl, .repLayer fieldset.user dt, .repLayer fieldset.user dd{ display:inline-block;}
	.repLayer fieldset.user dd input{ width:100px;}
	.repLayer fieldset.pwd{ text-align:center;}
	.repLayer fieldset.pwd p{ font-weight:700; padding:1.5rem 0 0.5rem;}

	/* 모달창 */
	.modal_layer{position:fixed; top:0; left:0; z-index:5000; display:none;  width:100%; height:100%; background:rgba(0,0,0,0.3) url('../images/bg01.png');}
	.modal_layer .modal_wrap{position:fixed; top:50%; left:50%; z-index:2; width:400px; height:300px; padding-top:50px; max-width:90%; max-height:90%; background:#F7F7F7;}
	.modal_layer .modal_header{position:absolute; top:0; left:0; width:100%; height:50px; background:#2C3E50;}
	.modal_layer .modal_header h1{padding:0 80px 0 20px; height:100%; font-size:16px; color:white; overflow:hidden; line-height:50px;}
	.modal_layer .modal_header .close_modal_btn{position:absolute; top:16px; left:100%; margin-left:-38px; line-height:16px; font-size:18px; color:white; cursor:pointer;}
	.modal_layer .submit_btn button{height:40px; font-size:14px; width:100px;}
	.modal_layer .modal_contents{height:100%; overflow-y:auto;}

	#loading_layer{position:fixed; top:0; left:0; z-index:6000; display:none;  width:100%; height:100%; background:rgba(0,0,0,0.1);}

	fieldset h3{ padding:20px 0 10px 10px; font-size:14px; color:#8391a1;}
	fieldset{ padding-bottom:30px;}
	/* ---------------------------------------------
	* 3. Login
	--------------------------------------------- */
	#contents.login{ position:fixed; top:0; left:0; width:100%; height:100%; background:#ccc url('../images/bg01.png');}
	#login_wrap{ position:fixed; top:50%; left:50%; width:600px; height:300px; margin:-150px 0 0 -300px; border:2px solid #0E5579; background:#fff; overflow:hidden; border-radius:15px; box-shadow:2px 2px 4px rgba(0,0,0,0.3);}
	#login_wrap h2{ padding:10px 20px; background:#0E5579; background:#0E5579; font-weight:800; color:#fff; font-size:21px;}
	#login_wrap form{ padding:20px 100px; font-size:16px;}
	#login_wrap fieldset{ float:left; width:70%; line-height:26px;}
	#login_wrap fieldset:after{ content:' '; display:block; clear:both;}
	#login_wrap fieldset label{ clear:left; float:left; display:block; width:40%; padding:5px 0; margin:0;}
	#login_wrap fieldset span{ display:block; float:left; width:60%; padding:5px 0; text-align:right;}
	#login_wrap fieldset input{margin:0; width:100%;}
	#login_wrap #LoginConfirm{ float:right; width:30%; padding:5px 0;}
	#login_wrap #LoginConfirm button{ height:70px; width:100%; background:#333;}
	#login_wrap #LoginRemember{ clear:both; padding:10px 0 20px;}
	#login_wrap #LoginRemember i{ position:relative; display:inline-block; width:16px; height:16px; border:1px solid #888; border-radius:3px; vertical-align:middle; margin-right:5px; box-shadow:1px 1px 2px rgba(0,0,0,0.2);}
	#login_wrap #link{ border-top:1px solid #ddd; padding-top:20px; text-align:center;}
	#login_wrap #link a{ display:inline-block; padding:5px 10px; text-decoration:underline; color:#888;}
	#login_wrap #RemberIDBtn.active i:before{ content:' '; position:absolute; top:50%; left:50%; width:12px; height:12px; margin:-6px 0 0 -6px; background:#422487; border-radius:2px;}

	/* ---------------------------------------------
	* 4. Member Auth Modal
	--------------------------------------------- */
	#authContents{padding:20px;}
	#authContents form > ul > li > p{ padding:5px 10px; margin-bottom:10px; border:1px solid #ddd; background:#f4f4f4;}
	#authContents form > ul > li > ul{ margin:10px;}
	#authContents form > ul > li > ul li{ display:inline-block;}
	div.opt{ padding:5px; text-align:right;}

	.menuSelectArea .menuSelect{ margin-bottom:5px;}
	a.menuSelectAddBtn{ margin-top:5px;}
</style>