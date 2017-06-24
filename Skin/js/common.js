function getInternetExplorerVersion() {
	var rv = -1;
	if (navigator.appName == 'Microsoft Internet Explorer') {
		var ua = navigator.userAgent;
		var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
			rv = parseFloat(RegExp.$1);
	}
	return rv;
};

var transitionEnd = 'transitionend webkittransitionend otransitionend mstransitionend';

$(document).ready(function () {
	$('input.datePicker').not('.nopicker').each(function(){
		datepicker.call(this);
	});
	DateInputAll();

	_SelectBox.SetAll();
	_ImageAlign.alignAll();

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

	$(document).on('click', '.tabMenu a', function (e) {
		e.preventDefault();
		var container = $(this).closest('.tabContainer');
		var li = container.find('.tabMenu li');
		var idx = li.index($(this).parent());
		li.eq(idx).addClass('on').siblings().removeClass('on');
		container.find('section').eq(idx).addClass('on').siblings('section').removeClass('on');
	});
});

$(window).resize(function () {
	_ImageAlign.alignAll();
});

$(document).on('DOMNodeInserted', 'body', function(e){
	if($(e.target).hasClass('event')) _EventLink.EventCheck.call(e.target);
	$(e.target).find('.event').each(function(){
		_EventLink.EventCheck.call(this);
	});

	if($(e.target).hasClass('imgAlign')) _ImageAlign.align.call(e.target);
	$(e.target).find('.imgAlign').each(function(){
		_ImageAlign.align.call(this);
	});

	if($(e.target).hasClass('selectBox')) _SelectBox.Set.call($(e.target).find('select'));
	$(e.target).find('.selectBox select').each(function(){
		_SelectBox.Set.call(this);
	});

	if($(e.target).hasClass('dateInput') && $(e.target).find('input.date').length) DateInput.call($(e.target).find('input.date'));
	$(e.target).find('.dateInput input.date').each(function(){
		DateInput.call(this);
	});

	if(e.target.tagName === 'INPUT' && $(e.target).hasClass('datePicker') && !$(e.target).hasClass('nopicker')) datepicker.call($(e.target));
	$(e.target).find('input.datePicker').not('.nopicker').each(function(){
		datepicker.call(this);
	});
});

function Common($) {
	var _this = this;

	this.ie8 = false;
	this.ie9 = false;
	this.alertNumber = 0;

	this.Init = function(){
		var ieVer = getInternetExplorerVersion();
		this.ie8 = ieVer == 8 ? true : false;
		this.ie9 = ieVer == 9 ? true : false;

		if(ieVer < 9 && ieVer != -1){
			document.write('현재 사용하시고 계시는 브라우저의 버전은 지원하지 않습니다.');
			$(document).ready(function(){
				document.write('현재 사용하시고 계시는 브라우저의 버전은 지원하지 않습니다.');
			});
			return;
		}

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

	}; // Init

	this.preload = function (imgs) {
		$(imgs).each(function () {
			$('<img/>')[0].src = this;
		});
	};

	this.val = function(v){
		if(typeof v != 'undefined') return v;
		return '';
	}

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
	 *   AJAX
	 *
	 ------------------------------------------- */
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

	// ajax post
	this.post = function (ur, dt, success_func, fail_func) {
		dt.loadingEnable = false;
		this._ajax(ur, dt, {type : 'post'}, success_func, fail_func);
	};

	// ajax get
	this.get = function (ur, dt, success_func, fail_func) {
		dt.loadingEnable = false;
		this._ajax(ur, dt, {type : 'get'}, success_func, fail_func);
	};

	// ajax post
	this.postWithLoading = function (ur, dt, success_func, fail_func) {
		dt.loadingEnable = true;
		this._ajax(ur, dt, {type : 'post'}, success_func, fail_func);
	};

	// ajax get
	this.getWithLoading = function (ur, dt, success_func, fail_func) {
		dt.loadingEnable = true;
		this._ajax(ur, dt, {type : 'get'}, success_func, fail_func);
	};

	/* -------------------------------------------
	 *
	 *   MODAL
	 *
	 ------------------------------------------- */
	// modal 제거
	this.removeModal = function (obj) {
		var modal = (typeof obj == 'undefined') ? $('.modal_layer:visible').last() : $(obj);
		if(!modal.length) return;

		if(modal.attr('data-close-type') == 'hidden') modal.hide();
		else modal.remove();
		$('body').css('overflow-y', $('body')[0].hasAttribute('data-ovy') ? $('body').attr('data-ovy') : 'auto');
	};

	// modal 생성
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

	// ajax를 보낸 후 모달창을 띄움(createModal)
	this.getModal = function (ur, dt, title, modal_id, w, h) {
		this._ajaxModal('get', ur, dt, title, modal_id, w, h);
	};

	// ajax를 보낸 후 모달창을 띄움(createModal)
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

	/* -------------------------------------------
	 *
	 *   파일업로드
	 *   파일 업로드 영역 : .fileUploadArea
	 *   파일 업로드 file hidden type input : input.fileUploadInput
	 *   파일 업로드 미리보기 : .fileUploadImage
	 *   파일 업로드 버튼 : .fileUploadBtn
	 *
	 ------------------------------------------- */

	this.imageFileForm = function(){
		if($('#_uploadImgFrm').length) return;
		var frm = '<form id="_uploadImgFrm" method="post" action="/Upload/ImageUpload/" enctype="multipart/form-data" style="display:block; width:0; height:0; opacity:0; overflow:hidden;">' +
			'<input type="file" name="Filedata" value="" data-sname="" id="_uploadImgInp" style="display:block; width:0; height:0; opacity:0;" />' +
			'</form>';
		$('body').append(frm);

		$(document).on('click','.fileUploadArea button.fileUploadBtn',function(e){
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

	/* -------------------------------------------
	 *
	 *   Daum Get Postcode
	 *
	 ------------------------------------------- */

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

	this.Init();
};

/* -----------------------------------------------------
 *
 *   Message Modal
 *
 ----------------------------------------------------- */
function MessageModal($){
	var _this = this;
	this.activeElement = null;

	this.Init = function(){
		window.alert = function(msg) {
			_this.Create(msg);
		};

		$(document).on('mousedown touch', '.MessageModal footer a', function(e){
			_this.activeElement = $('*:focus');
		});

		$(document).on('click', '.MessageModal footer a', function(e){
			e.preventDefault();
			var obj = $(this).data();
			if(typeof(obj.onclick) == 'function') obj.onclick.call(this);
			_this.Remove.call(this);
			$(_this.activeElement).focus();
		});
	};

	this.Create = function(message, buttons, title){
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

		_this.Move('#MessageModal' + _this.alertNumber);
		for(var i = 1; i < 11; i++){
			setTimeout(function(){
				_this.Move('#MessageModal' + _this.alertNumber);
			}, i*100);
		}
	};

	this.Move = function(obj){
		var MessageModalWrap = $(obj).find('.MessageModalWrap');
		MessageModalWrap.css({
			'margin-top' : '-' + (MessageModalWrap.outerHeight() / 2) + 'px',
			'margin-left' : '-' + (MessageModalWrap.outerWidth() / 2) + 'px'
		});
	}

	this.Remove = function(){
		$(this).closest('.MessageModal').remove();
	};

	this.Init();
}

/* -----------------------------------------------------
 *
 *    .event 와 메소드 연결
 *    ex) <a href="#" class="event" e-click="common.test">Test</a>
 *    속성 : e-click, e-submit, e-mousedown, e-mouseup,
 *    e-transition-end(e-animate-end), e-touch, e-touch-start(e-mouse-down),
 *    e-touch-move(e-mouse-move), e-touch-end(e-mouse-up)
 *
 ----------------------------------------------------- */
function EventLink($){
	var _this = this;
	this.Init = function(){
		$(document).ready(function(e){
			$('.event').each(function(){
				_this.EventCheck.call(this);
			});
		});

		$(document).on('touchmove mousemove', 'body', function(e){
			var body = $(this);
			if(typeof(body.data) == 'undefined' || typeof(body.data('touchObject')) == 'undefined' || body.data('touchObject') == null) return;
			var touchObject = body.data('touchObject');
			if(typeof($(touchObject).data) == 'undefined' || typeof($(touchObject).data('touchStart')) == 'undefined' || $(touchObject).data('touchStart') == null) return;
			$.each(touchObject, function(idx, obj){
				$(obj).data('touchEnd', e.type == 'mousemove' ? {
					'pageX': e.pageX,
					'pageY': e.pageY
				} : e.originalEvent.touches[0]);
				if(obj.hasAttribute('e-touch-move')) $(obj).trigger('e_move', e);
				else if(obj.hasAttribute('e-mouse-move')) $(obj).trigger('e_move', e);
				if(obj.hasAttribute('e-drag')) $(obj).trigger('e_drag', e);
			});
		});

		$(document).on('touchend mouseup', 'body', function(e){
			var body = $(this);
			if(typeof(body.data) == 'undefined' || typeof(body.data('touchObject')) == 'undefined' || body.data('touchObject') == null) return;
			var touchObject = body.data('touchObject');
			if(!$(touchObject).length || typeof($(touchObject).data) == 'undefined' || typeof($(touchObject).data('touchStart')) == 'undefined' || $(touchObject).data('touchStart') == null) return;

			$.each(touchObject, function(idx, obj){
				if($(obj).data('touchEnd') == null) $(obj).data('touchEnd', $(obj).data('touchStart'));
				var elementFromPoint = $(document.elementFromPoint($(obj).data('touchEnd').pageX, $(obj).data('touchEnd').pageY))[0];

				if(obj.hasAttribute('e-touch') && (obj == elementFromPoint || $(elementFromPoint).closest(obj).length)){
					var x = $(obj).data('touchEnd').pageX - $(obj).data('touchStart').pageX;
					var y = $(obj).data('touchEnd').pageY - $(obj).data('touchStart').pageY;
					if(Math.abs(x) < 5 && Math.abs(y) < 5) $(obj).trigger('e_touch', e);
				}

				if(obj.hasAttribute('e-touch-visible') && typeof($(obj).data('visibleTouchIs')) != 'undefined' && $(obj).data('visibleTouchIs') && obj == document.elementFromPoint($(obj).data('touchEnd').pageX, $(obj).data('touchEnd').pageY)){
					var x = $(obj).data('touchEnd').pageX - $(obj).data('touchStart').pageX;
					var y = $(obj).data('touchEnd').pageY - $(obj).data('touchStart').pageY;
					if(Math.abs(x) < 5 && Math.abs(y) < 5) $(obj).trigger('e_touch_visible', e);
				}

				if(obj.hasAttribute('e-touch-end')) $(obj).trigger('e_touch_end', e);
				else if(obj.hasAttribute('e-mouse-up')) $(obj).trigger('e_mouse_up', e);

				if(obj.hasAttribute('e-drag-end')) $(obj).trigger('e_drag_end', e);

				$(obj).data('touchStart', null);
				$(obj).data('touchEnd', null);
			});
			body.data('touchObject', null);
		});
	};

	this.EventCheck = function(){

		function GetEventFunction(data){
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
			if(typeof(obj) == 'function') return obj;
			return null;
		};

		// .event 의 data-action 속성의 값과 이름이 일치하는 함수를 연결
		if(this.hasAttribute('e-touch') || this.hasAttribute('e-touch-visible') || this.hasAttribute('e-click')){
			if(this.tagName == 'A' || this.tagName == 'BUTTON' || this.tagName == 'INPUT'){
				$(this).on('click', function(e){
					e.preventDefault();
				});
			}
			if(this.hasAttribute('e-click')) $(this).on('click', GetEventFunction($(this).attr('e-click')));
		}

		if(this.hasAttribute('e-submit')){
			$(this).on('submit', function(e){
				e.preventDefault();
			});
			if(this.hasAttribute('e-submit')) $(this).on('submit', GetEventFunction($(this).attr('e-submit')));
		}

		if(this.hasAttribute('e-transition-end')) $(this).on(transitionEnd, GetEventFunction($(this).attr('e-transition-end')));
		else if(this.hasAttribute('e-animate-end')) $(this).on(transitionEnd, GetEventFunction($(this).attr('e-animate-end')));

		if(this.hasAttribute('e-change')) $(this).on('change', GetEventFunction($(this).attr('e-change')));

		// Touch Start Events
		if(this.hasAttribute('e-touch') || this.hasAttribute('e-touch-visible') || this.hasAttribute('e-mouse-down') || this.hasAttribute('e-touch-start') || this.hasAttribute('e-drag')){
			$(this).on('touchstart mousedown', function(e){
				if(typeof($('body').data) == 'undefined' || typeof($('body').data('touchObject')) == 'undefined' || $('body').data('touchObject') == null){
					$('body').data('touchObject', [this]);
				}
				else{
					var objs = $('body').data('touchObject');
					objs.push(this);
					$('body').data('touchObject', objs);
				}
				$(this).data('touchStart', e.type == 'mousedown' ? {
					'pageX': e.pageX,
					'pageY': e.pageY
				} : e.originalEvent.touches[0]);
				$(this).data('touchEnd', null);
				$(this).data('visibleTouchIs',
					(this.hasAttribute('e-touch-visible') && this == document.elementFromPoint($(this).data('touchStart').pageX, $(this).data('touchStart').pageY))
				);
			});
		}
		if(this.hasAttribute('e-touch-start')) $(this).on('touchstart mousedown', GetEventFunction($(this).attr('e-touch-start')));
		else if(this.hasAttribute('e-mouse-down')) $(this).on('touchstart mousedown', GetEventFunction($(this).attr('e-mouse-down')));

		// Touch Move Events
		if(this.hasAttribute('e-touch-move')) $(this).on('e_move', GetEventFunction($(this).attr('e-touch-move')));
		else if(this.hasAttribute('e-mouse-move')) $(this).on('e_move', GetEventFunction($(this).attr('e-mouse-move')));

		if(this.hasAttribute('e-drag')) $(this).on('e_drag', GetEventFunction($(this).attr('e-drag')));

		// Touch End Events
		if(this.hasAttribute('e-touch')) $(this).on('e_touch', GetEventFunction($(this).attr('e-touch')));

		if(this.hasAttribute('e-touch-visible')) $(this).on('e_touch_visible', GetEventFunction($(this).attr('e-touch-visible')));

		if(this.hasAttribute('e-touch-end')) $(this).on('e_touch_end', GetEventFunction($(this).attr('e-touch-end')));
		else if(this.hasAttribute('e-mouse-up')) $(this).on('e_mouse_up', GetEventFunction($(this).attr('e-mouse-up')));

		if(this.hasAttribute('e-drag-end')) $(this).on('e_drag_end', GetEventFunction($(this).attr('e-drag-end')));
	};

	this.Init();
};

var _EventLink = new EventLink(jQuery);


/* -----------------------------------------------------
 *
 *    이미지 정렬
 *    .imgAlign img
 *	   data-opt Attribute
 *    center, horizontal : 가로 중앙 정렬
 *    middle, vertical : 세로 중앙 정렬
 *    both: 가로, 세로 중앙 정렬
 *    contain : 이미지 사이즈 조절
 *
 ----------------------------------------------------- */

function ImageAlign($) {
	var _this = this;
	this.enabled = false;

	this.alignAll = function(){
		if(!this.enabled) return;
		$('.imgAlign').each(function () {
			_this.align.call(this);
		});
	};

	this.align = function(){
		if(!_this.enabled) return;

		function imagePosition(layer, img, opt){
			if(opt.indexOf('cover') >= 0 ){
				if(img.outerHeight() / img.outerWidth() < layer.outerHeight() / layer.outerWidth()){
					var h = layer.outerHeight();
					img.css({
						'width' : 'auto',
						'height' : h + 'px'
					});
				}else{
					var w = layer.outerWidth();
					img.css({
						'width' : w+'px',
						'height' : 'auto'
					});
				}
			}
			else{
				if(opt.indexOf('contain') >= 0 ){
					if(img.outerHeight() / img.outerWidth() > layer.outerHeight() / layer.outerWidth()){
						var h = layer.outerHeight();
						img.css({
							'width' : 'auto',
							'height' : h + 'px'
						});
					}else{
						var w = layer.outerWidth();
						img.css({
							'width' : w+'px',
							'height' : 'auto'
						});
					}
				}
			}

			if (opt.indexOf('center') >= 0 || opt.indexOf('horizontal') >= 0 || opt.indexOf('both') >= 0) {
				img.css({
					'margin-left': ((layer.width() - img.width()) / 2) + 'px'
				});
			}
			if (opt.indexOf('middle') >= 0 || opt.indexOf('vertical') >= 0 || opt.indexOf('both') >= 0) {
				img.css({
					'margin-top': ((layer.height() - img.height()) / 2) + 'px'
				});
			}
		}

		var layer = $(this);
		var opt = layer.attr('data-opt');
		var img = layer.find('img');

		if (layer.attr('data-load') == 'y') {
			imagePosition(layer, img, opt);
			return;
		}

		var tmpImg = new Image();
		layer.attr('data-load', 'y');
		tmpImg.onload = function () {
			imagePosition(layer, img, opt);
		};

		tmpImg.src = img.attr('src');
	}
};

var _ImageAlign = new ImageAlign(jQuery);

/* -------------------------------------------
 *
 *   For Custom Select Tag
 *
 ------------------------------------------- */
function SelectBox($){
	var _this = this;
	this.SetAll = function(){
		$('.selectBox select').each(function(){
			_this.Set.call(this);
		});

		$(document).off('change', '.selectBox select');
		$(document).on('change', '.selectBox select', function(e){
			var val = $(this).children('option:selected').text();
			$(this).closest('.selectBox').find('.selected').text(val);
		});
	};

	this.Set = function(){
		var selectTxtE = $(this).closest('.selectBox').find('.selected');
		var val = $(this).children('option:selected').text();
		if(!selectTxtE.length){
			$(this).before('<span class="selected"></span>');
			$(this).prev().text(val);
		}
		else selectTxtE.text(val);
	}
}

var _SelectBox = new SelectBox(jQuery);

/* -----------------------------------------------------
 *
 *    $(selector).FormReset
 *    $(selector).translate3d
 *    $(form selector).validCheck
 *
 ----------------------------------------------------- */
(function($) {
	$.ieIs = navigator.appName == 'Microsoft Internet Explorer';

	$.fn.FormReset = function(){
		this.reset();
		_SelectBox.SetAll();
		DateInputAll();
	};

	/* -----------------------------------------------------
	 *
	 *    translate3d
	 *    ie use top, left
	 *
	 ----------------------------------------------------- */
	$.fn.translate3d = function(before, after, duration, complete) {
		if(typeof before.z == 'undefined') before.z = 0;
		if(typeof before.css == 'undefined') before.css = {};
		if(typeof after.z == 'undefined') after.z = 0;
		if(typeof after.css == 'undefined') after.css = {};
		if($.ieIs){
			before.css.top = before.y;
			before.css.left = before.x;
			after.css.top = after.y;
			after.css.left = after.x;
			before.css['transition'] = 0;
			before.css.display = 'block';
			$(this).css(before.css);
			$(this).animate(after.css, duration, complete);
		}
		else{
			$(this).off('transitionend webkittransitionend mstransitionend');
			var beforeTranslate = 'translate3d(' + before.x + ', ' + before.y + ', ' + before.z + ')';
			var afterTranslate = 'translate3d(' + after.x + ', ' + after.y + ', ' + after.z + ')';
			before.css.transition = 0;
			before.css['-webkit-transform'] = beforeTranslate;
			before.css['-ms-transform'] = beforeTranslate;
			before.css.transform = beforeTranslate;
			before.css.display = 'block';
			$(this).css(before.css);

			$(this).css('width');
			if(typeof(complete) == 'function') $(this).on('transitionend webkittransitionend mstransitionend', complete);
			after.css.transition = duration + 'ms';
			after.css['-webkit-transform'] = afterTranslate;
			after.css['-ms-transform'] = afterTranslate;
			after.css.transform = afterTranslate;
			$(this).css(after.css);

		}
	};

	/* -----------------------------------------------------
	 *
	 *    form valid check
	 *
	 ----------------------------------------------------- */
	$.fn.validCheck = function(){
		var f = $(this);
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
						if(v != '' && !common.validateEmail(this.value)){
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
})(jQuery);

var common = new Common(jQuery);

/* -----------------------------------------------------
 *
 *   Input Date
 *   .dateInput .date
 *
 ----------------------------------------------------- */
function DateInputAll(){
	$('.dateInput .date').each(function(){
		DateInput.call(this);
	});
}

function DateInput(e){
	if(!$(this).siblings('.before').length) $(this).before('<div class="before"></div>');
	var val = $(this).val();
	var len = val.length;
	if(typeof(e) != 'undefined' && e.keyCode == 8){
		if(len == 4){
			e.preventDefault();
			val = val.substring(0, 3);
			len = 3;
			$(this).val(val);
		}
		else if(len == 7){
			e.preventDefault();
			val = val.substring(0, 6);
			len = 6;
			$(this).val(val);
		}
	}else{
		var n2 = $(this).val().replace(/[^0-9]/gi, '');
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
			$(this).val(n3);
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
	$(this).siblings('.before').html(newTxt);
}
$(document).on('keyup mousedown change focus focusout', '.dateInput input.date', function(e){
	DateInput.call(this, e);
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
		SwiperInit($(this), opt);
	});
});

var swiperJsIs = false;
function SwiperInit(obj, opt) {
	if(typeof(Swiper) == 'undefined'){
		if(!swiperJsIs){
			swiperJsIs = true;
			$('<link/>', {
				rel: 'stylesheet',
				type: 'text/css',
				href: '/Skin/css/idangerous.swiper.css'
			}).appendTo('head');

			$.getScript('/Skin/js/idangerous.swiper.js', function(){
				SwiperInit(obj, opt);
			});
			return;
		}
		setTimeout(function(){
			SwiperInit(obj, opt);
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

function datepicker() {
	$(this).datepicker({
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

/* -------------------------------------------
 *
 *   Input Value Check
 *
 ------------------------------------------- */
$(document).on('keyup', 'input.numberonly input.numberOnly', function() {
	if($(this).hasClass('commaNumber')) return;
	var val = this.value.replace(/[^0-9]/gi,'');
	if(this.value != val) this.value = val;
});

$(document).on('keyup click focusout focus', 'input.commaNumber input.commanumber', function() {
	var val = this.value.replace(/[^0-9]/gi,'');
	this.value = '';
	this.value = common.setComma(parseInt(val == '' ? '0' : val));
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

$(document).on('click', '.backbtn, .hback a, a.hback', function (e) {
	e.preventDefault();
	history.back();
});

