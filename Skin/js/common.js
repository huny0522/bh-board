function Common($) {
	var _this = this;

	this.ie8 = false;
	this.ie9 = false;
	this.alertNumber = 0;

	this.Init = function(){
		this.ie8 = this.getInternetExplorerVersion() == 8 ? true : false;
		this.ie9 = this.getInternetExplorerVersion() == 9 ? true : false;

		$(window).resize(function () {
			_this.img_align();
		});

		$(document).ready(function () {
			_this.SetSelectBox();

			_this.img_align();

			$(document).on('click', '.tabMenu a', function (e) {
				e.preventDefault();
				var container = $(this).closest('.tabContainer');
				var li = container.find('.tabMenu li');
				var idx = li.index($(this).parent());
				li.eq(idx).addClass('on').siblings().removeClass('on');
				container.find('section').eq(idx).addClass('on').siblings('section').removeClass('on');
			});
		});

		$(document).on('click', '.backbtn, .hback a, a.hback', function (e) {
			e.preventDefault();
			history.back();
		});

		/* -------------------------------------------
		 *
		 *   Input Value Check
		 *
		 ------------------------------------------- */
		$(document).on('keyup', 'input.numberonly', function() {
			var val = this.value.replace(/[^0-9]/gi,'');
			if(this.value != val) this.value = val;
		});

		$(document).on('keyup', 'input.engonly', function() {
			var val = this.value.replace(/[^a-zA-Z]/gi,'');
			if(this.value != val) this.value = val;
		});

		$(document).on('keyup', 'input.engnumonly', function() {
			var val = this.value.replace(/[^a-zA-Z0-9]/gi,'');
			if(this.value != val) this.value = val;
		});

		$(document).on('keyup', 'input.tel', function() {
			var val = this.value.replace(/[^0-9\-\*\#]/gi,'');
			if(this.value != val) this.value = val;
		});

		$(document).on('keyup', 'input.engspecialonly', function() {
			var val = this.value.replace(/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>'\"\?\-=\+_\:\;\[\]\{\}\/]/gi,'');
			if(this.value != val) this.value = val;
		});

		/* -------------------------------------------
		 *
		 *   Modal
		 *
		 ------------------------------------------- */
		$(document).on('click', '.modal_layer', function (e) {
			_this.removeModal(this);
		});

		$(document).on('click', '.modal_wrap', function (e) {
			e.stopPropagation();
		});

		$(document).on('click', '.modal_layer .cancel, .modal_layer .close', function (e) {
			e.preventDefault();
			_this.removeModal($(this).closest('.modal_layer'));
		});

		this.EventLink();

	}; // Init

	/* -----------------------------------------------------------------------------------------
	 *
	 *       .event 와 메소드 연결
	 *       ex) <a href="#" class="event" e-click="common.test">Test</a>
	 *       속성 : e-click, e-submit, e-mousedown, e-mouseup, e-transition-end
	 *
	 ----------------------------------------------------------------------------------------- */
	this.EventLink = function(){
		// .event 의 data-action 속성의 값과 이름이 일치하는 함수를 연결
		$(document).on('click', '.event', function(e){
			if(this.hasAttribute('e-click')){
				e.preventDefault();
				_this._EventFunction(this, e, $(this).attr('e-click'));
			}
		});

		$(document).on('submit', '.event', function(e){
			if(this.hasAttribute('e-submit')){
				_this._EventFunction(this, e, $(this).attr('e-submit'));
			}
		});

		$(document).on('mousedown', '.event', function(e){
			if(this.hasAttribute('e-mousedown')){
				_this._EventFunction(this, e, $(this).attr('e-mousedown'));
			}
		});

		$(document).on('mouseup', '.event', function(e){
			if(this.hasAttribute('e-mouseup')){
				_this._EventFunction(this, e, $(this).attr('e-mouseup'));
			}
		});

		$(document).on('transitionend webkittransitionend otransitionend mstransitionend', '.event', function(){
			if(this.hasAttribute('e-transition-end')) _this._EventFunction(this, $(this).attr('e-transition-end'));
		});
	};

	this._EventFunction = function(element, e, data){
		var temp = data.split('.');
		var obj = null;
		for(var i=0, max = temp.length; i < max; i ++){
			if(obj == null){
				if(typeof(window[temp[i]]) != 'function' && typeof(window[temp[i]]) != 'object') return;
				obj = window[temp[i]];
			}
			else{
				if(typeof(obj[temp[i]]) != 'function' && typeof(obj[temp[i]]) != 'object') return;
				obj = obj[temp[i]];
			}
		}
		if(typeof(obj) == 'function') obj.call(element, e);
	};

	this.preload = function (imgs) {
		$(imgs).each(function () {
			$('<img/>')[0].src = this;
		});
	};

	this.val = function(v){
		if(typeof v != 'undefined') return v;
		return '';
	}

	this.getInternetExplorerVersion = function () {
		var rv = -1;
		if (navigator.appName == 'Microsoft Internet Explorer') {
			var ua = navigator.userAgent;
			var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
			if (re.exec(ua) != null)
				rv = parseFloat(RegExp.$1);
		}
		return rv;
	};

	this.loading = function () {
		$('body').append('<div class="loading_layer"><div class="loading_layer_wrap"><div class="animation"></div><p>Loading...</p></div></div>');
		if(typeof this.loadingAnimation == 'function') this.loadingAnimation($('.loading_layer .animation').last());
	};

	this.loading_end = function () {
		$('.loading_layer').eq(0).remove();
	};

	this.setComma = function (nStr) {
		nStr += '';
		var x = nStr.split('.');
		var x1 = x[0];
		var x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	};

	this.removeComma = function (nStr) {
		return nStr.replace(/,/g, '');
	};

	this.html2xt = function(str){
		str = str.replace(/\</ig, '&lt;');
		str = str.replace(/\>/ig, '&gt;');
		str = str.replace(/\'/ig, '&#39;');
		return str;
	}

	/**
	 * 폼 ajax 전송
	 */
	this.ajaxForm = function (formObj, success_func, fail_func) {
		_this.loading();

		$(formObj).ajaxSubmit({
			dataType: 'json',
			async : true,
			success: function (response, textStatus, xhr, form) {
				_this.loading_end();

				if(typeof response.message != 'undefined' && response.message != null && response.message.length) alert(response.message);
				if(typeof response.result != 'undefined' && response.result != null){
					if(response.result === true){
						if(typeof success_func != 'undefined') success_func(response.data);
					}else{
						if(typeof fail_func != 'undefined') fail_func(response.data);
					}
				}else{
					if(typeof success_func != 'undefined') success_func(response);
				}
			},
			error: function (xhr, textStatus, errorThrown) {
				_this.loading_end();
				alert(textStatus);
			},
			uploadProgress: function (event, position, total, percentComplete) {
				// uploadProgress
			}

		});

	};

	/**
	 * ajax
	 * @param ur 전송할 URL
	 */
	this._ajax = function (ur, dt, opt, success_func, fail_func) {
		var le = false;
		if (typeof(dt.loadingEnable) != 'undefined'){
			le = dt.loadingEnable;
			if(dt.loadingEnable == true) _this.loading();
			delete dt.loadingEnable;
		}


		$.ajax({
			type: (typeof opt.type != 'undefined' ? opt.type : 'post')
			, dataType: 'json'
			, url: ur
			, data: dt
			, async: true
			, success: function (response, textStatus, jqXHR) {
				if (le === true) _this.loading_end();

				if (typeof response.message != 'undefined' && response.message != null && response.message.length) alert(response.message);
				if(typeof response.result != 'undefined' && response.result != null){
					if(response.result === true){
						if (typeof success_func!= 'undefined') success_func(response.data);
					}else{
						if (typeof fail_func != 'undefined') fail_func(response.data);
					}
				}else{
					if (typeof success_func!= 'undefined') success_func(response);
				}
			}
			, error: function (jqXHR, textStatus, errorThrown) {
				if (le === true) _this.loading_end();
				alert(textStatus);
			}
		});
	};

	/**
	 * ajax post
	 */
	this.post = function (ur, dt, success_func, fail_func) {
		dt.loadingEnable = false;
		this._ajax(ur, dt, {type : 'post'}, success_func, fail_func);
	};

	/**
	 * ajax get
	 */
	this.get = function (ur, dt, success_func, fail_func) {
		dt.loadingEnable = false;
		this._ajax(ur, dt, {type : 'get'}, success_func, fail_func);
	};

	this.postWithLoading = function (ur, dt, success_func, fail_func) {
		dt.loadingEnable = true;
		this._ajax(ur, dt, {type : 'post'}, success_func, fail_func);
	};

	/**
	 * ajax get
	 */
	this.getWithLoading = function (ur, dt, success_func, fail_func) {
		dt.loadingEnable = true;
		this._ajax(ur, dt, {type : 'get'}, success_func, fail_func);
	};

	/**
	 * ajax를 보낸 후 모달창을 띄움(createModal)
	 */
	this.getModal = function (ur, dt, title, modal_id, w, h) {
		this._ajaxModal('get', ur, dt, title, modal_id, w, h);
	};

	this.postModal = function (ur, dt, title, modal_id, w, h) {
		this._ajaxModal('post', ur, dt, title, modal_id, w, h);
	};

	this._ajaxModal = function (type, ur, dt, title, modal_id, w, h) {
		dt.loadingEnable = true;
		if(type == 'get'){
			this.get(ur, dt, function(data){
				_this.createModal(title, modal_id, data, w, h);
			});
		}else{
			this.post(ur, dt, function(data){
				_this.createModal(title, modal_id, data, w, h);
			});
		}
	};

	this.removeModal = function (obj) {
		var modal = (typeof obj == 'undefined') ? $('.modal_layer:visible').last() : $(obj);
		if(!modal.length) return;

		if(modal.attr('data-close-type') == 'hidden') modal.hide();
		else modal.remove();
		$('body').css('overflow-y', $('body')[0].hasAttribute('data-ovy') ? $('body').attr('data-ovy') : 'auto');
	};

	this.createModal = function (title, modal_id, data, w, h) {
		if (!modal_id) modal_id = 'modal_layer';
		if (!w) w = 400;
		if (!h) h = 300;
		var html = '<div id="' + modal_id + '" class="modal_layer"><div class="modal_wrap">';
		if (title && title != '') html += '<div class="modal_header"><h1 class="modal_title">' + title + '</h1><p class="close_modal_btn"><i class="fa fa-close" title="닫기" onclick="common.removeModal(\'#' + modal_id + '\')"></i></p></div>';
		html += '<div class="modal_contents">' + data + '</div>';
		html += '</div></div>';
		$('body').append(html);
		$('#' + modal_id).children('.modal_wrap').css({
			'width': w + 'px'
			, 'height': h + 'px'
		});
		if (_this.ie8) {
			$('#' + modal_id).append('<div style="position:absolute; top:0; left:0; z-index:1; width:100%; height:100%; filter:alpha(opacity:70); background:black;" class="background"></div>');
		}
		$('#' + modal_id).css("display", "block");
		var box = $('#' + modal_id).children('.modal_wrap');
		box.css({
			'margin': '-' + (box.outerHeight() / 2) + 'px' + ' 0 0 -' + (box.outerWidth() / 2) + 'px'
		});
		if(!$('body')[0].hasAttribute('data-ovy')) $('body').attr('data-ovy', $('body').css('overflow-y'));
		$('body').css('overflow-y', 'hidden');
	};

	/*
	 *	이미지 정렬
	 *
	 *	data-opt Attribute
	 *	center, horizontal : 가로 중앙 정렬
	 *	middle, vertical : 세로 중앙 정렬
	 *	both: 가로, 세로 중앙 정렬
	 *
	 */
	this.img_align = function () {
		$('.align_img').each(function () {
			var layer = $(this);
			var opt = layer.attr('data-opt');
			var img = layer.find('img');

			if (layer.attr('data-load') == 'y') {
				if (opt == 'center' || opt == 'horizontal' || opt == 'both') {
					img.css({
						'margin-left': ((layer.width() - img.width()) / 2) + 'px'
					});
				}
				if (opt == 'middle' || opt == 'vertical' || opt == 'both') {
					img.css({
						'margin-top': ((layer.height() - img.height()) / 2) + 'px'
					});
				}
				return;
			}

			var tmpImg = new Image();
			layer.attr('data-load', 'y');
			tmpImg.onload = function () {
				if (opt == 'center' || opt == 'horizontal' || opt == 'both') {
					img.css({
						'margin-left': ((layer.width() - img.width()) / 2) + 'px'
					});
				}
				if (opt == 'middle' || opt == 'vertical' || opt == 'both') {
					img.css({
						'margin-top': ((layer.height() - img.height()) / 2) + 'px'
					});
				}
			};
			tmpImg.src = img.attr('src');
		});
	};

	this.img_align_reset = function(obj){
		if(obj.get(0).tagName == 'IMG') obj = obj.parent();
		obj.attr('data-load','');
		_this.img_align();
	};

	this.popPostCode = function (callback) {
		if (typeof daum == "undefined") {

			jQuery.getScript("http://dmaps.daum.net/map_js_init/postcode.v2.js").done(function (script, textStatus) {
				_this.popDaumPostCode(callback);
			});
		} else {
			_this.popDaumPostCode(callback);
		}
	};

	this.popDaumPostCode = function (callback) {
		if ($('#DaumPostCode').length) return;
		$('body').append('<div id="DaumPostCode"><div id="DaumPostCodeWrap"></div></div>');
		$('#DaumPostCode').css({
			'position': 'fixed',
			'z-index': '9998',
			'top': '0',
			'left': '0',
			'width': '100%',
			'height': '100%',
			'background': 'none',
			'background': 'rgba(0,0,0,0.4)'
		});

		$('#DaumPostCode').on('click touchstart', function (e) {
			$(this).remove();
		});

		$('#DaumPostCodeWrap').css({
			'position': 'fixed',
			'z-index': '9999',
			'top': '50%',
			'left': '50%',
			'width': '500px',
			'height': '500px',
			'margin-top': '-250px',
			'margin-left': '-250px',
			'background': 'white',
			'border': '5px solid black'
		});
		daum.postcode.load(function () {
			new daum.Postcode({
				oncomplete: function (data) {
					$('#DaumPostCode').remove();
					if(callback) callback(data);
				},
				width: '490px',
				height: '490px'
			}).embed($('#DaumPostCodeWrap')[0]);
		});
	};

	/* scrollTop */
	this.goTop = function () {
		$(window).scrollTop(0);
	};

	this.goBottom = function () {
		$(window).scrollTop($('body').prop('scrollHeight') + $('body').height());
	};

	this.getCookie = function(cname){
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(var i = 0; i <ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length,c.length);
			}
		}
		return "";
	};

	this.setCookie = function(cname, cvalue, exdays){
		var expires = '';
		if(exdays != null){
			var d = new Date();
			d.setTime(d.getTime() + (exdays*24*60*60*1000));
			expires = "expires="+ d.toUTCString();
		}
		document.cookie = cname + "=" + cvalue + "; path=/;" + expires;
	};

	this.replaceTag = function(tag) {
		var tagsToReplace = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;'
		};
		return tagsToReplace[tag] || tag;
	};

	this.safe_tags_replace = function(str) {
		if(!str || str == '') return '';
		return str.replace(/[&<>]/g, _this.replaceTag);
	};

	this.valCHeck = function(form){
		var f = $(form);
		var inp = f.find('input, select, textarea');
		var ret = true;
		inp.each(function(){
			if(ret) {

				if (this.hasAttribute('required')) {
					if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
						if (!f.find('input[name=' + $(this).attr('name') + ']:checked').length) {
							alert($(this).attr('data-displayname') + ' 항목을 선택하여 주세요.');
							$(this).focus();
							ret = false;
							return false;
						}
					}
					else if ($.trim($(this).val()) == '') {
						alert($(this).attr('data-displayname') + ' 항목을 입력하여 주세요.');
						$(this).focus();
						ret = false;
						return false;
					}
				}

				if(this.tagName == 'INPUT' && $(this).attr('type') != 'radio' && $(this).attr('type') != 'checkbox' && $.trim(this.value) != ''){
					if($(this).hasClass('engonly')){
						var val = this.value.replace(/[^a-zA-Z]/gi,'');
						if(val != this.value){
							alert($(this).attr('data-displayname') + ' 항목은 영문만 입력하여 주세요.');
							$(this).focus();
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('email')){
						var v = $.trim(this.value);
						if(v != '' && !_this.validateEmail(this.value)){
							alert($(this).attr('data-displayname') + ' 항목 형식이 올바르지 않습니다.');
							$(this).focus();
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('tel')){
						var val = this.value.replace(/[^0-9\-\*\#]/gi,'');
						if(val != this.value){
							alert($(this).attr('data-displayname') + ' 항목 형식이 올바르지 않습니다.');
							$(this).focus();
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('engnumonly')){
						var val = this.value.replace(/[^a-zA-Z0-9]/gi,'');
						if(val != this.value){
							alert($(this).attr('data-displayname') + ' 항목은 영문 또는 숫자만 입력하여 주세요.');
							$(this).focus();
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('numberonly')){
						var val = this.value.replace(/[^0-9]/gi,'');
						if(val != this.value){
							alert($(this).attr('data-displayname') + ' 항목은 숫자만 입력하여 주세요.');
							$(this).focus();
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('engspecialonly')) {
						var val = this.value.replace(/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>'\"\?\-=\+_\:\;\[\]\{\}\/]/gi,'');
						if(val != this.value){
							alert($(this).attr('data-displayname') + ' 항목은 영문 및 숫자, 특수문자만 입력하여 주세요.');
							$(this).focus();
							ret = false;
							return false;
						}
					}

					if(this.hasAttribute('data-minlength')){
						var len = parseInt($(this).attr('data-minlength'));
						if($(this).val().length < len){
							alert($(this).attr('data-displayname') + ' 항목은 ' + len + '자 이상으로 입력하여 주세요.');
							$(this).focus();
							ret = false;
							return false;
						}
					}
					if(this.hasAttribute('data-maxlength')){
						var len = parseInt($(this).attr('data-maxlength'));
						if($(this).val().length > len){
							alert($(this).attr('data-displayname') + ' 항목은 ' + len + '자 이하로 입력하여 주세요.');
							$(this).focus();
							ret = false;
							return false;
						}

					}
					if($(this).hasClass('numberonly') && this.hasAttribute('data-minvalue')){
						var min = parseInt($(this).attr('data-minvalue'));
						if(parseInt($(this).val()) < min){
							alert($(this).attr('data-displayname') + ' 항목의 최소값은 ' + min + '입니다.');
							$(this).focus();
							ret = false;
							return false;
						}

					}
					if($(this).hasClass('numberonly') && this.hasAttribute('data-maxvalue')){
						var max = parseInt($(this).attr('data-maxvalue'));
						if(parseInt($(this).val()) > max){
							alert($(this).attr('data-displayname') + ' 항목의 최대값은 ' + max + '입니다.');
							$(this).focus();
							ret = false;
							return false;
						}
					}

					if(this.hasAttribute('data-same') && this.tagName == 'INPUT'){
						var target = $(this).closest('form').find('input[name=' + $(this).attr('data-same') + ']');
						if(target.length){
							if($(this).val() != target.val()){
								alert(target.attr('data-displayname') + ' 값이 일치하지 않습니다.');
								target.focus();
								ret = false;
								return false;
							}
						}
					}

				}

			}
		});
		return ret;
	};

	this.todayPopupClose = function(seq) {
		_this.setCookie('todayClosePopup' + seq, 'y', 1);
		jQuery('#BH_Popup' + seq).hide();
	};

	this.popup = function(target, seq, top, left, width, height, data) {
		var ck = _this.getCookie('todayClosePopup' + seq);
		if (ck == 'y') return;
		//return;
		var html = '';
		html += '<div class="BH_Popup" id="BH_Popup' + seq + '" style="top:' + top + 'px; left:' + left + 'px;">'
			+ '<div class="BH_PopupContent" style="width:'+ width + 'px; height:'+ height + 'px;">' + data + '</div>'
			+ '<div class="BH_PopupBtns">'
			+ '<span class="BH_PopupTodayClose"><a onclick="common.todayPopupClose(' + seq + ');">오늘하루 이창 열지 않기</a></span>'
			+ '<span class="BH_PopupClose"><a onclick="jQuery(this).closest(\'.BH_Popup\').hide();">닫기</a></span>'
			+ '</div>'
			+ '</div>';
		$(target).append(html);
	};

	this.validateEmail = function(email) {
		email = $.trim(email);
		var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		return re.test(email);
	};

	/* -------------------------------------------
	 *
	 *   파일업로드
	 *
	 *   파일 업로드 영역 : .fileUploadArea
	 *   파일 업로드 file type input : input.fileUploadInput
	 *   파일 업로드 미리보기 : .fileUploadImage
	 *
	 ------------------------------------------- */
	this.imageFileForm = function(){
		if($('#_uploadImgFrm').length) return;
		var frm = '<form id="_uploadImgFrm" method="post" action="/Upload/ImageUpload/" enctype="multipart/form-data" style="display:block; width:0; height:0; opacity:0; overflow:hidden;">' +
			'<input type="file" name="Filedata" value="" data-sname="" id="_uploadImgInp" style="display:block; width:0; height:0; opacity:0;" />' +
			'</form>';
		$('body').append(frm);

		$(document).on('click','button.fileUploadBtn',function(e){
			e.preventDefault();
			$('#_uploadImgFrm').data({
				obj : $(this).closest('.fileUploadArea').find('input.fileUploadInput')[0]
			});
			$('#_uploadImgInp').click();
		});
		$(document).on('change', '#_uploadImgInp', function(e){
			e.preventDefault();
			$('#_uploadImgFrm').submit();
		});

		$(document).on('submit','#_uploadImgFrm',function(e){
			e.preventDefault();
			_this.ajaxForm(this, function(result){
				$('#_uploadImgFrm')[0].reset();
				var obj = $('#_uploadImgFrm').data().obj;
				$(obj).val(result.path);
				var img = $(obj).closest('.fileUploadArea').find('.fileUploadImage');
				if(img.length) img.html('<img src="' + result.uploadDir + result.path + '">');
			});
		});
	};

	// ==============================================================
	//
	//    Message Modal
	//

	this.MessageModalInit = function(){
		window.alert = function(msg) {
			_this.MessageModal(msg);
		};

		$(document).on('click', '.MessageModal footer a', function(e){
			e.preventDefault();
			var obj = $(this).data();
			if(typeof(obj.onclick) == 'function') obj.onclick(this);
			_this.CloseMsgModal(this);
		});
	};

	this.MessageModal = function(message, buttons, title){
		this.alertNumber++;
		if(typeof(title) == 'undefined') title = '알림';
		if(typeof(buttons) == 'undefined'){
			buttons = [{'text' : '확인'}];
		}

		var btns = '';
		for(var i = 0; i < buttons.length; i++){
			btns += '<a href="#">' + buttons[i].text + '</a>';
		}

		var html = '<div class="MessageModal" id="MessageModal' + this.alertNumber + '">' +
			'<div class="MessageModalWrap">' +
			'<header>' + title + '</header>' +
			'<div class="text">' + message + '</div>' +
			'<footer>' + btns + '</footer>' +
			'</div></div>';

		$('body').append(html);
		if(buttons.length == 1){
			$('.MessageModal footer a').last().focus();
		}

		for(var i = 0; i < buttons.length; i++){
			var func = buttons[i].onclick;
			$('#MessageModal' + this.alertNumber + ' footer a').eq(i).data({'onclick' : func});
		}

		_this.MoveMessageModal('#MessageModal' + _this.alertNumber);
		for(var i = 1; i < 11; i++){
			setTimeout(function(){
				_this.MoveMessageModal('#MessageModal' + _this.alertNumber);
			}, i*100);
		}
	};

	this.MoveMessageModal = function(obj){
		var MessageModalWrap = $(obj).find('.MessageModalWrap');
		MessageModalWrap.css({
			'margin-top' : '-' + (MessageModalWrap.outerHeight() / 2) + 'px',
			'margin-left' : '-' + (MessageModalWrap.outerWidth() / 2) + 'px'
		});
	}

	this.CloseMsgModal = function(obj){
		$(obj).closest('.MessageModal').remove();
	};

	// ==============================================================
	//
	//    For Custom Select Tag
	//

	this.SetSelectBox = function(){
		$('.selectBox select').each(function(){
			var selectTxtE = $(this).closest('.selectBox').find('.selected');
			var val = $(this).children('option:selected').text();
			if(!selectTxtE.length){
				$(this).before('<span class="selected"></span>');
				$(this).prev().text(val);
			}
			else selectTxtE.text(val);
		});

		$(document).off('change', '.selectBox select');
		$(document).on('change', '.selectBox select', function(e){
			var val = $(this).children('option:selected').text();
			$(this).closest('.selectBox').find('.selected').text(val);
		});
	};

	this.Init();

};

var common = new Common(jQuery);

/* -------------------------------------------
 *
 *   Input Date
 *   .dateInput .date
 *
 ------------------------------------------- */
function dateInputAll(){
	$('.dateInput .date, .dateInput .mdate').each(function(){
		dateInput(this);
	});
}

function dateInput(obj, e){
	if(!$(obj).siblings('.before').length) $(obj).before('<div class="before"></div>');
	var val = $(obj).val();
	var len = val.length;
	if(typeof(e) != 'undefined' && e.keyCode == 8){
		if(len == 4){
			e.preventDefault();
			val = val.substring(0, 3);
			len = 3;
			$(obj).val(val);
		}
		else if(len == 7){
			e.preventDefault();
			val = val.substring(0, 6);
			len = 6;
			$(obj).val(val);
		}
	}else{
		var n2 = $(obj).val().replace(/[^0-9]/gi, '');
		var n3 = n2;

		if(n2.length >= 5 && parseInt(n2.substring(4,5)) > 1){
			n3 = n2.substring(0, 4) + '1' + n2.substring(5, n2.length);
		}

		if(n2.length >= 7 && parseInt(n2.substring(6,7)) > 3){
			n3 = n2.substring(0, 6) + '3' + n2.substring(7, n2.length);
		}

		if(n2.length >= 6 && parseInt(n2.substring(4,6)) > 12){
			n3 = n2.substring(0,5);
		}

		if(n2.length >=8 && parseInt(n2.substring(6,8)) > 31){
			n3 = n2.substring(0,7);
		}

		if(n3.length >= 6){
			n3 = n3.substring(0, 4) + '-' + n3.substring(4, 6) + '-' + n3.substring(6, n2.length);
		}else if(n3.length >= 4){
			n3 = n3.substring(0, 4) + '-' + n3.substring(4, n2.length);
		}

		if(n3 != val){
			$(obj).val(n3);
			len = n3.length;
		}
	}

	if(len > 10) len = 10;
	var txt = '0000-00-00';
	var newTxt = '';
	for(var i = 0; i < 10; i++){
		if(i < len) newTxt += '<span>' + txt[i] + '</span>';
		else newTxt += txt[i];
	}
	$(obj).siblings('.before').html(newTxt);
}
$(document).on('keyup', '.dateInput input.date, .dateInput input.mdate', function(e){
	dateInput(this, e);
});


/* -------------------------------------------
 *
 *   Swiper
 *
 ------------------------------------------- */
$(window).load(function () {
	$('.swiper-container').each(function () {
		if ($(this).attr('data-auto-init') == '0') return;
		var opt = {};
		swiper_init($(this), opt);
	});
});

var swiperJsIs = false;
function swiper_init(obj, opt) {
	if(typeof(Swiper) == 'undefined'){
		if(!swiperJsIs){
			swiperJsIs = true;
			$('<link/>', {
				rel: 'stylesheet',
				type: 'text/css',
				href: '/Skin/css/idangerous.swiper.css'
			}).appendTo('head');

			$.getScript('/Skin/js/idangerous.swiper.js', function(){
				swiper_init(obj, opt);
			});
			return;
		}
		setTimeout(function(){
			swiper_init(obj, opt);
		}, 300);
		return;
	}
	var def_opt = {
		slidesPerView: '1',
		spaceBetween: 0,
		paginationClickable: true,
		calculateHeight: true,
		DOMAnimation: (common.ie8 || common.ie9) ? false : true
	};

	if(obj.attr('data-complete') == 'y'){
		return;
	}
	obj.attr('data-complete', 'y');

	if (obj.attr('data-loop') == '1') {
		opt.loop = true;
		opt.autoplayDisableOnInteraction = false;
	}
	if (obj.attr('data-init')) {
		opt.initialSlide = obj.attr('data-init');
	}
	if (obj.attr('data-auto-play')) {
		opt.autoplay = parseInt(obj.attr('data-auto-play'));
	}
	if (obj.attr('data-space')) {
		opt.spaceBetween = parseInt(obj.attr('data-space'));
	}
	if (obj.attr('data-per-view')) {
		opt.slidesPerView = parseFloat(obj.attr('data-per-view'));
	}
	if (obj.attr('data-center') == '1') {
		opt.centeredSlides = true;
	}

	var paging = obj.find('.swiper_paging');

	if (!paging.length) paging = obj.parent().children('.swiper_paging');
	if (paging.length) {
		if (paging.find('.pagination').length) opt.pagination = paging.find('.pagination')[0];
	}

	for (var attrname in opt) { def_opt[attrname] = opt[attrname]; }

	var mySwiper = obj.swiper(def_opt);
	if (paging.length && paging.find('.prev').length) {
		paging.find('.prev').click(function () {
			mySwiper.swipePrev();
		});
	}
	if (paging.length && paging.find('.next').length) {
		paging.find('.next').click(function () {
			mySwiper.swipeNext();
		});
	}
}

/* -------------------------------------------
 *
 *   Datepicker
 *
 ------------------------------------------- */
$.datepicker.regional.ko = { closeText: "닫기", prevText: "이전달", nextText: "다음 달", currentText: "오늘", monthNames: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"], monthNamesShort: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"], dayNames: ["일", "월", "화", "수", "목", "금", "토"], dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"], dayNamesMin: ["일", "월", "화", "수", "목", "금", " 토"], dateFormat: "yy-mm-dd", firstDay: 0, isRTL: false };
$.datepicker.setDefaults($.datepicker.regional.ko);

function datepicker(selector) {
	$(selector).datepicker({
		changeYear: true,
		changeMonth: true,
		showMonthAfterYear: true,
		dateFormat: 'yy-mm-dd',
		endDate: 'today',
		todayHighlight: true
	}).click(function () {
		$(this).datepicker('show');
	});
}

$(document).ready(function () {
	$('input.datePicker').not('.nopicker').each(function(){
		datepicker(this);
	});
	dateInputAll();
});


/* -------------------------------------------
 *
 *   smart editor 2 attach
 *
 ------------------------------------------- */
var oEditors = [];
var SE2LoadIs = false;
function SE2_paste(id, defaultfolder, hiddenimage){
	var scriptLoadIs = typeof(nhn) != 'undefined' && typeof(nhn.husky) != 'undefined' && typeof(nhn.husky.EZCreator) != 'undefined';
	if(scriptLoadIs){
		SE2LoadIs = true;
		spaste(id, defaultfolder, hiddenimage);
	}
	else{
		if(!SE2LoadIs){
			SE2LoadIs = true;
			$.getScript('/Common/smart_editor/js/HuskyEZCreator.js').done(function( s, Status ) {
				spaste(id, defaultfolder, hiddenimage);
			});
		}
		else{
			setTimeout(function(){
				SE2_paste(id, defaultfolder, hiddenimage);
			}, 200);
		}
	}


	function spaste(id, defaultfolder, hiddenimage){
		var imgbox = hiddenimage ? '' : '<div class="se2_add_img" data-sname="'+id+'">' +
			'<span><button class="upbtn">이미지첨부</button></span>' +
			'<div></div>' +
			'</div>';

		$('#'+id).after(imgbox);
		if(!$('#fileupfrm').length){
			var imgfrm = '<form id="fileupfrm" method="post" action="/Upload/ImageUpload/" enctype="multipart/form-data">' +
				'<input type="file" name="Filedata" value="" data-sname="" id="fileupinp" style="display:block; width:0; height:0;" />' +
				'</form>';
			$('body').append(imgfrm);
		}

		nhn.husky.EZCreator.createInIFrame({
			oAppRef: oEditors,
			elPlaceHolder: id,
			sSkinURI: defaultfolder + "/Common/smart_editor/SmartEditor2Skin.html",
			htParams : {
				bUseToolbar : true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
				bUseVerticalResizer : true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
				bUseModeChanger : true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
				//aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
				fOnBeforeUnload : function(){
					//alert("완료!");
				}
			}, //boolean
			fOnAppLoad : function(){
				var sDefaultFont = '나눔고딕';
				var nFontSize = 11;
				oEditors.getById[id].setDefaultFont(sDefaultFont, nFontSize);
				//예제 코드
			},
			fCreator: "createSEditor2"
		});
	}
}

$(document).on('click','.se2_add_img button.upbtn',function(e){
	e.preventDefault();
	$('#fileupinp').attr('data-sname', $(this).parents('.se2_add_img').attr('data-sname'));
	$('#fileupinp').click();
});

$(document).on('change', '#fileupinp', function(e){
	e.preventDefault();
	$('#fileupfrm').submit();
});

$(document).on('submit','#fileupfrm',function(e){
	e.preventDefault();
	common.ajaxForm(this, function(result){
		var hinp = '<input type="hidden" name="addimg[]" value="'+result.path+'|'+result.fname+'">';
		$('.se2_add_img div').append(hinp);
		$('#fileupfrm')[0].reset();
		var html = '<img src="' + result.uploadDir + result.path + '">';
		oEditors.getById[$('#fileupinp').attr('data-sname')].exec('PASTE_HTML', [html]);
	});
});

function SE2_update(id){
	oEditors.getById[id].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
}

/* -------------------------------------------
 *
 *   Check All
 *
 ------------------------------------------- */
$(document).on('click', '.checkAllArea input.checkAll', function(){
	var checked = this.checked;
	$(this).closest('.checkAllArea').find('input.checkItem').each(function(){
		this.checked = checked;
	});
});

$(document).on('click', '.checkAllArea input.checkItem', function(){
	var area = $(this).closest('.checkAllArea');
	if(area.find('input.checkItem:not(:checked)').length) area.find('input.checkAll')[0].checked = false;
	else area.find('input.checkAll')[0].checked = true;
});

/* -------------------------------------------
 *
 *   Image Preview
 *   file입력창 바로 전에 클래스 UploadImagePreview 가 있으면 이미지 미리보기
 *   ie10+
 *
 ------------------------------------------- */
$(document).on('change', '.UploadImagePreview input[type=file]', function () {
	if (common.ie8 || common.ie9) return;

	var img = $(this).closest('.UploadImagePreview').find('img.preview');
	if (img.length) {

		var reader = new FileReader();
		reader.onload = function (e) {
			img.attr('src', e.target.result);
		};

		reader.readAsDataURL(this.files[0]);
	}
});

$(document).ready(function () {
	$('.UploadImagePreview img.preview').each(function () {
		var obj = $(this);
		this.onerror = function () {
			obj.parent().hide();
		};
		this.oncomplete = function () {
			obj.parent().show();
		};
		this.src = obj.attr('src') == '' ? '#' : obj.attr('src');
	});

	if ($('.selectMail select[name=selectMail]').length) {
		$(document).on('change', '.selectMail select[name=selectMail]', function (e) {
			var inp = $(this).closest('.selectMail').find('input.emailAddr');
			if ($(this).val() == 'x') {
				inp.val('');
			} else if ($(this).val() != '') {
				inp.val($(this).val());
			}
		});
	}
});
