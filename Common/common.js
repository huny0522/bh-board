if(typeof(window._CM_LANG) === 'undefined') window._CM_LANG = {};
if(typeof(window._CM_LANG.OK) === 'undefined') window._CM_LANG.OK = '확인';
if(typeof(window._CM_LANG.todayClose) === 'undefined') window._CM_LANG.todayClose = '오늘하루 이창 열지 않기';
if(typeof(window._CM_LANG.close) === 'undefined') window._CM_LANG.close = '닫기';
if(typeof(window._CM_LANG.notification) === 'undefined') window._CM_LANG.notification = '알림';
if(typeof(window._CM_LANG.cancel) === 'undefined') window._CM_LANG.cancel = '취소';
if(typeof(window._CM_LANG.selectItem) === 'undefined') window._CM_LANG.selectItem = '{item} 항목을 선택하여 주세요.';
if(typeof(window._CM_LANG.inputItem) === 'undefined') window._CM_LANG.inputItem = '{item} 항목을 입력하여 주세요.';
if(typeof(window._CM_LANG.onlyEng) === 'undefined') window._CM_LANG.onlyEng = '{item} 항목은 영문만 입력하여 주세요.';
if(typeof(window._CM_LANG.wrongType) === 'undefined') window._CM_LANG.wrongType = '{item} 항목 형식이 올바르지 않습니다.';
if(typeof(window._CM_LANG.onlyEngNum) === 'undefined') window._CM_LANG.onlyEngNum = '{item} 항목은 영문 또는 숫자만 입력하여 주세요.';
if(typeof(window._CM_LANG.onlyNum) === 'undefined') window._CM_LANG.onlyNum = '{item} 항목은 숫자만 입력하여 주세요.';
if(typeof(window._CM_LANG.onlyEngNumSpecial) === 'undefined') window._CM_LANG.onlyEngNumSpecial = '{item} 항목은 영문 및 숫자, 특수문자만 입력하여 주세요.';
if(typeof(window._CM_LANG.orMore) === 'undefined') window._CM_LANG.orMore = '{item} 항목은 {n}자 이상으로 입력하여 주세요.';
if(typeof(window._CM_LANG.orLess) === 'undefined') window._CM_LANG.orLess = '{item} 항목은 {n}자 이하로 입력하여 주세요.';
if(typeof(window._CM_LANG.orMoreValue) === 'undefined') window._CM_LANG.orMoreValue = '{item} 항목의 최소값은 {n}입니다.';
if(typeof(window._CM_LANG.orLessValue) === 'undefined') window._CM_LANG.orLessValue = '{item} 항목의 최대값은 {n}입니다.';
if(typeof(window._CM_LANG.notMatchValue) === 'undefined') window._CM_LANG.notMatchValue = '{item} 값이 일치하지 않습니다.';
if(typeof(window._CM_LANG.image) === 'undefined') window._CM_LANG.image = '이미지';
if(typeof(window._CM_LANG.youtube) === 'undefined') window._CM_LANG.youtube = '유튜브';
if(typeof(window._CM_LANG.link) === 'undefined') window._CM_LANG.link = '링크';
if(typeof(window._CM_LANG.youtubeLink) === 'undefined') window._CM_LANG.youtubeLink = '유튜브 링크';
if(typeof(window._CM_LANG.linkUrl) === 'undefined') window._CM_LANG.linkUrl = '링크주소';
if(typeof(window._CM_LANG.size) === 'undefined') window._CM_LANG.size = '크기';
if(typeof(window._CM_LANG.width) === 'undefined') window._CM_LANG.width = '넓이';
if(typeof(window._CM_LANG.height) === 'undefined') window._CM_LANG.height = '높이';
if(typeof(window._CM_LANG.append) === 'undefined') window._CM_LANG.append = '삽입';
if(typeof(window._CM_LANG.appendLink) === 'undefined') window._CM_LANG.appendLink = '링크 삽입';


function getInternetExplorerVersion() {
	var rv = -1;
	if (navigator.appName === 'Microsoft Internet Explorer') {
		var ua = navigator.userAgent;
		var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) !== null)
			rv = parseFloat(RegExp.$1);
	}
	return rv;
}

var transitionEnd = 'transitionend webkittransitionend otransitionend mstransitionend';

$(window).resize(function () {
	_ImageAlign.alignAll();
});

function Common($){
	var _this = this;

	this.ie8 = false;
	this.ie9 = false;

	this.loadingIs = false;

	this.modalAutoSize = false;

	// 예약
	this.ajaxSuccess = null;
	this.ajaxFailed = null;

	this.Init = function(){
		var ieVer = getInternetExplorerVersion();
		this.ie8 = ieVer === 8;
		this.ie9 = ieVer === 9;

		/*if(ieVer < 9 && ieVer !== -1){
			document.write('현재 사용하시고 계시는 브라우저의 버전은 지원하지 않습니다.');
			$(document).ready(function(){
				document.write('현재 사용하시고 계시는 브라우저의 버전은 지원하지 않습니다.');
			});
			return;
		}*/

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
		if(typeof v !== 'undefined') return v;
		return '';
	};

	this.loadingAnimation = function(obj){

	};

	this.loading = function () {
		this.loadingIs = true;
		$('body').append('<div class="loading_layer"><div class="loading_layer_wrap"><div class="animation"></div><p>Loading...</p></div></div>');
		if(typeof this.loadingAnimation === 'function') this.loadingAnimation($('.loading_layer .animation').last());
	};

	this.loading_end = function () {
		this.loadingIs = false;
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

	this.html2txt = function(str){
		//language=JSRegexp
		str = str.replace(/</ig, '&lt;');
		str = str.replace(/>/ig, '&gt;');
		str = str.replace(/'/ig, '&#39;');
		str = str.replace(/"/ig, '&quot;');
		return str;
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
			while (c.charAt(0) === ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) === 0) {
				return c.substring(name.length,c.length);
			}
		}
		return "";
	};

	this.setCookie = function(cname, cvalue, exdays){
		this.setCookieMinute(cname, cvalue, exdays === null ? null : exdays * 60 * 24);
	};

	this.setCookieMinute = function(cname, cvalue, exdays){
		var expires = '';
		if(exdays !== null){
			var d = new Date();
			d.setTime(d.getTime() + (exdays*60*1000));
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
		if(!str || str === '') return '';
		return str.replace(/[&<>]/g, _this.replaceTag);
	};

	this.todayPopupClose = function(seq) {
		_this.setCookie('todayClosePopup' + seq, 'y', 1);
		jQuery('#BH_Popup' + seq).hide();
	};

	this.popup = function(target, seq, top, left, width, height, data) {
		var ck = _this.getCookie('todayClosePopup' + seq);
		if (ck === 'y') return;
		if(document.getElementById('BH_Popup' + seq)){
			$('#BH_Popup' + seq).show();
			return;
		}
		//return;
		var html = '';
		html += '<div class="BH_Popup" id="BH_Popup' + seq + '" style="top:' + top + 'px; left:' + left + 'px;"><div class="BH_PopupWrap">'
			+ '<div class="BH_PopupContent" style="width:'+ width + 'px; height:'+ height + 'px; background:#fff;">' + data + '</div>'
			+ '<div class="BH_PopupBtns">'
			+ '<span class="BH_PopupTodayClose"><a onclick="JCM.todayPopupClose(' + seq + ');"><i></i> <span>' + window._CM_LANG.todayClose + '</span></a></span>'
			+ '<span class="BH_PopupClose"><a onclick="jQuery(this).closest(\'.BH_Popup\').hide();"><i></i> <span>' + window._CM_LANG.close + '</span></a></span>'
			+ '</div>'
			+ '</div></div>';
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
		if(this.loadingIs) return;
		_this.loading();

		$(formObj).ajaxSubmit({
			dataType: 'json',
			async : true,
			success: function (response, textStatus, xhr, form) {
				_this.loading_end();

				if(typeof response.message !== 'undefined' && response.message !== null && response.message.length) CMAlert(response.message);
				if(typeof response.result !== 'undefined' && response.result !== null){
					if(response.result === true){
						if(typeof(_this.ajaxSuccess) === 'function')_this.ajaxSuccess(response.data, success_func);
						else if(typeof success_func !== 'undefined') success_func(response.data);
					}else{
						if(typeof(_this.ajaxFailed) === 'function')_this.ajaxFailed(response.data, fail_func);
						else if(typeof fail_func !== 'undefined') fail_func(response.data);
					}
				}else{
					if(typeof success_func !== 'undefined') success_func(response);
				}
			},
			error: function (xhr, textStatus, errorThrown) {
				_this.loading_end();
				CMAlert(textStatus);
			},
			uploadProgress: function (event, position, total, percentComplete) {
				// uploadProgress
			}

		});

	};

	this._ajax = function (ur, dt, opt, success_func, fail_func) {
		var le = false;
		if (typeof(dt.loadingEnable) !== 'undefined'){
			le = dt.loadingEnable;
			if(dt.loadingEnable === true){
				if(this.loadingIs) return;
				_this.loading();
			}
			delete dt.loadingEnable;
		}


		$.ajax({
			type: (typeof opt.type !== 'undefined' ? opt.type : 'post')
			, dataType: 'json'
			, url: ur
			, data: dt
			, async: true
			, success: function (response, textStatus, jqXHR) {
				if (le === true) _this.loading_end();

				if (typeof response.message !== 'undefined' && response.message !== null && response.message.length) CMAlert(response.message);
				if(typeof response.result !== 'undefined' && response.result !== null){
					if(response.result === true){
						if(typeof(_this.ajaxSuccess) === 'function')_this.ajaxSuccess(response.data, success_func);
						else if(typeof success_func !== 'undefined') success_func(response.data);
					}else{
						if(typeof(_this.ajaxFailed) === 'function')_this.ajaxFailed(response.data, fail_func);
						else if(typeof fail_func !== 'undefined') fail_func(response.data);
					}
				}else{
					if (typeof success_func !== 'undefined') success_func(response);
				}
			}
			, error: function (jqXHR, textStatus, errorThrown) {
				if (le === true) _this.loading_end();
				CMAlert(textStatus);
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
		var modal = (typeof obj === 'undefined') ? $('.modal_layer:visible').last() : $(obj);
		if(!modal.length) return;

		if(modal.attr('data-close-type') === 'hidden') modal.hide();
		else{
			if(typeof modal.data('close_method') === 'function') modal.data('close_method')();
			modal.remove();
		}
		$('body').css({'overflow-y' : $('body')[0].hasAttribute('data-ovy') ? $('body').attr('data-ovy') : 'auto', 'margin-right' : '0'});
	};

	// modal 생성
	this.createModal = function (title, modal_id, data, w, h) {
		if (!modal_id) modal_id = 'modal_layer';
		if (!w) w = 400;
		if (!h) h = 300;
		var html = '<div id="' + modal_id + '" class="modal_layer"><div class="modal_wrap">';
		if (title && title !== '') html += '<div class="modal_header"><h1 class="modal_title">' + title + '</h1><button class="close"><i class="cross" title="' + window._CM_LANG.close + '"></i></button></div>';
		html += '<div class="modal_contents">' + data + '</div>';
		html += '</div></div>';
		$('body').append(html);
		JCM.showModal(modal_id, w, h);
	};

	this.showModal = function(modal_id, w, h){
		var wrap = $('#' + modal_id).children('.modal_wrap');
		if(!this.modalAutoSize){
			if(typeof (w) !== 'undefined') wrap.css({'width': w + 'px'});
			if(typeof (h) !== 'undefined') wrap.css({'height': h + 'px'});
		}

		if (_this.ie8) {
			$('#' + modal_id).append('<div style="position:absolute; top:0; left:0; z-index:1; width:100%; height:100%; filter:alpha(opacity:70); background:black;" class="background"></div>');
		}data-same
		$('#' + modal_id).css("display", "block");
		var beforeW = $('body').width();
		if(!$('body')[0].hasAttribute('data-ovy')) $('body').attr('data-ovy', $('body').css('overflow-y'));
		$('body').css('overflow-y', 'hidden');
		$('body').css({'position' : 'relative', 'width' : 'auto', 'margin-right' : ($('body').width() - beforeW)+'px'});

		if(this.modalAutoSize){
			if(typeof(this.modalAutoSizeWindowEvent) === 'undefined'){
				this.modalAutoSizeWindowEvent = true;
				$(window).resize(function(){
					if($('.modal_wrap').length){
						$('.modal_wrap').each(function(){
							_this.modalResize(this);
						});
					}
				});
			}

			_this.modalResize(wrap[0]);
		}
	};

	this.modalResize = function(wrapEl){
		$(wrapEl).css({
			height : 'auto'
		});
		$(wrapEl).css({
			height : $(wrapEl).outerHeight() + 'px'
		});
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
		if(type === 'get'){
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
	 *   이미지 파일업로드
	 *   파일 업로드 영역 : .fileUploadArea
	 *   파일 업로드 file hidden type input : input.fileUploadInput
	 *   파일 업로드 미리보기 : .fileUploadImage
	 *   파일 업로드 버튼 : .fileUploadBtn
	 *
	 ------------------------------------------- */

	this.addFileInp = function(){
		var area = $(this).parent().prev();
		var maxFileN = area[0].hasAttribute('data-max-file-number') ? parseInt(area.attr('data-max-file-number')) : 3;
		if(area.children().length >= maxFileN) return;
		area.append($(this).attr('data-html'));
	};

	this.imageFileFormRunIs = false;
	this.imageFileFormBtnAction = null;
	this.imageFileForm = function(btnCallback){
		if(typeof(btnCallback) === 'function') this.imageFileFormBtnAction = btnCallback;
		var _this = this;
		if(this.imageFileFormRunIs) return;
		this.imageFileFormRunIs = true;

		if($('#_uploadImgFrm').length) return;
		var frm = '<form id="_uploadImgFrm" method="post" action="/Upload/ImageUpload/" enctype="multipart/form-data" style="display:block; width:0; height:0; opacity:0; overflow:hidden;">' +
			'<input type="file" name="Filedata" value="" data-sname="" id="_uploadImgInp" accept="image/*" style="display:block; width:0; height:0; opacity:0;" />' +
			'</form>';
		$('body').append(frm);

		$(document).on('click','.fileUploadArea button.fileUploadBtn',function(e){
			e.preventDefault();
			$('#_uploadImgFrm').data({
				obj : $(this).closest('.fileUploadArea').find('input.fileUploadInput')[0]
			});
			if(typeof(_this.imageFileFormBtnAction) === 'function') _this.imageFileFormBtnAction(document.getElementById('_uploadImgInp'));
			else $('#_uploadImgInp').click();
		});
		$(document).off('click','.multiFileUploadArea + div.multiFileUploadAdd button.fileUploadAreaAddBtn');
		$(document).on('click','.multiFileUploadArea + div.multiFileUploadAdd button.fileUploadAreaAddBtn',function(e){
			e.preventDefault();
			_this.addFileInp.call(this);
		});

		$(document).on('click','.fileUploadArea button.fileUploadAreaRmBtn',function(e){
			e.preventDefault();
			var area = $(this).closest('.fileUploadArea');
			if(!area.siblings('.fileUploadArea').length) AddFileInp.call(this);
			area.remove();
		});
		$(document).on('change', '#_uploadImgInp', function(e){
			e.preventDefault();
			$('#_uploadImgFrm').submit();
		});

		$(document).on('submit','#_uploadImgFrm',function(e){
			e.preventDefault();
			_this.ajaxForm(this, _this.imageFileSubmitRes);
		});
	};

	this.imageFileSubmitRes = function(result){
		$('#_uploadImgFrm')[0].reset();
		var obj = $('#_uploadImgFrm').data().obj;
		var area = $(obj).closest('.fileUploadArea');
		$(obj).val(result.path);
		var img = area.find('.fileUploadImage');
		if(img.length){
			img.html('<i style="background-image:url(' + result.uploadDir + result.path  + ')"></i>');
		}
	};

	/* -------------------------------------------
	 *
	 *   일반 파일업로드
	 *   파일 업로드 영역 : .fileUploadArea2
	 *   파일 업로드 file hidden type input : input.fileUploadInput
	 *   파일 업로드 미리보기 : .fileName
	 *   파일 업로드 버튼 : .fileUploadBtn
	 *
	 ------------------------------------------- */

	this.fileFormRunIs = false;
	this.fileFormBtnAction = null;
	this.fileForm = function(btnCallback){
		if(typeof(btnCallback) === 'function') this.fileFormBtnAction = btnCallback;
		if(this.fileFormRunIs) return;
		this.fileFormRunIs = true;

		if($('#_uploadFileFrm').length) return;
		var frm = '<form id="_uploadFileFrm" method="post" action="/Upload/FileUpload/" enctype="multipart/form-data" style="display:block; width:0; height:0; opacity:0; overflow:hidden;">' +
			'<input type="file" name="Filedata" value="" data-sname="" id="_uploadFileInp" style="display:block; width:0; height:0; opacity:0;" />' +
			'</form>';
		$('body').append(frm);

		$(document).on('click','.fileUploadArea2 button.fileUploadBtn',function(e){
			e.preventDefault();
			$('#_uploadFileFrm').data({
				obj : $(this).closest('.fileUploadArea2').find('input.fileUploadInput')[0]
			});
			if(typeof(_this.fileFormBtnAction) === 'function') _this.fileFormBtnAction(document.getElementById('_uploadFileInp'));
			else $('#_uploadFileInp').click();
		});
		$(document).off('click','.multiFileUploadArea + div.multiFileUploadAdd button.fileUploadAreaAddBtn');
		$(document).on('click','.multiFileUploadArea + div.multiFileUploadAdd button.fileUploadAreaAddBtn',function(e){
			e.preventDefault();
			_this.addFileInp.call(this);
		});

		$(document).on('click','.fileUploadArea2 button.fileUploadAreaRmBtn',function(e){
			e.preventDefault();
			var area = $(this).closest('.fileUploadArea2');
			if(!area.siblings('.fileUploadArea2').length) AddFileInp2.call(this);
			area.remove();
		});
		$(document).on('change', '#_uploadFileInp', function(e){
			e.preventDefault();
			$('#_uploadFileFrm').submit();
		});

		$(document).on('submit','#_uploadFileFrm',function(e){
			e.preventDefault();
			_this.ajaxForm(this, _this.fileSubmitRes);
		});
	};

	this.fileSubmitRes = function(result){
		$('#_uploadFileFrm')[0].reset();
		var obj = $('#_uploadFileFrm').data().obj;
		var area = $(obj).closest('.fileUploadArea2');
		$(obj).val(result.path + '*' + result.fname);
		var file = area.find('p');
		if(file.length){
			file.html('<span class="fileName">' + result.fname + '</span>');
		}
	};

	/* -------------------------------------------
	 *
	 *   JQuery 파일업로드
	 *
	 ------------------------------------------- */
	this.NewJQFile = function(obj){
		var area = $(obj).closest('div.jqFileUploadArea');
		$(obj).fileupload({
			url :  '/JQUpload?maxfilesize=' + (area[0].hasAttribute('data-max-size') ? $(area).attr('data-max-size') : '') + '&ext=' + (area[0].hasAttribute('data-ext') ? $(area).attr('data-ext') : ''),
			maxChunkSize: 2000000,
			dataType: 'json',
			done: function (e, data) {
				$.each(data.result, function (index, file) {
					if(typeof file[0].error !== 'undefined') CMAlert(file[0].error);
					else{
						var fn = decodeURIComponent(file[0].name.replace(/\+/g, '%20'));
						area.find('input.fileUploadPath').val('/temp/' + file[0].name + '*' + fn);
						area.find('b.upload_file_name').text(fn);
					}
				});
				area.find('div.progress div.bar').fadeOut(1000);
			},
			progressall: function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
				area.find('div.progress div.bar').fadeIn(100);
				area.find('div.progress div.bar').css({ 'width' : progress + '%'});
			}
		});
	};

	$(document).on('click', 'div.jqFileUploadArea button.fileUploadBtn', function(){
		var area = $(this).closest('div.jqFileUploadArea');
		if(!area[0].hasAttribute('data-jquery-file-loading')){
			_this.NewJQFile(area);
			area.attr('data-jquery-file-loading', 'yes');
		}
		area.find('input.fileUploadInp').trigger('click');
	});

	/* -------------------------------------------
	 *
	 *   Daum Get Postcode
	 *
	 ------------------------------------------- */

	this.popPostCode = function (callback) {
		if (typeof daum === "undefined" || typeof daum.postcode === "undefined") {
			jQuery.getScript(document.location.protocol == 'https:' ? 'https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js?autoload=false' : 'http://dmaps.daum.net/map_js_init/postcode.v2.js?autoload=false').done(function (script, textStatus) {
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

		var w = 500;
		var h = 500;
		if(w > $('body').width()){
			w = 320;
			h = 410;
		}

		$('#DaumPostCodeWrap').css({
			'position': 'fixed',
			'z-index': '9999',
			'top': '50%',
			'left': '50%',
			'width': w + 'px',
			'height': h + 'px',
			'max-height' : '100%',
			'box-sizing': 'border-box',
			'background': 'white',
			'border': '5px solid black',
			'transform' : 'translate(-50%, -50%)',
			'-webkit-transform' : 'translate(-50%, -50%)',
			'-moz-transform' : 'translate(-50%, -50%)',
			'-ms-transform' : 'translate(-50%, -50%)',
		});

		daum.postcode.load(function () {
			new daum.Postcode({
				oncomplete: function (data) {
					$('#DaumPostCode').remove();
					if(callback) callback(data);
				},
				width: (w - 10) + 'px',
				height: (h - 10) + 'px'
			}).embed($('#DaumPostCodeWrap')[0]);
		});
	};

	this.FindDaumAddress = function(e){
		e.preventDefault();
		var area = $(this).closest('.daumAddress');
		_this.popPostCode(function(data) {
			//console.log(data);
			area.find('input.zipcode').val(data.zonecode);
			area.find('input.address1').val(data.address);
			var sido = area.find('input.address_sido');
			var sigungu = area.find('input.address_sigungu');
			var bname = area.find('input.address_bname');
			var code = area.find('input.address_bcode');
			if(sido.length) sido.val(data.sido);
			if(sigungu.length) sigungu.val(data.sigungu);
			if(bname.length) bname.val(data.bname);
			if(code.length) code.val(data.bcode.substr(0, 8));
		});
	};

	this.getAttribute = function(el, name){
		if(typeof(el.hasAttribute) === 'undefined') return '';
		if(el.hasAttribute(name)) return el.getAttribute(name);
		return '';
	};
	this.hasClass = function(el, name){
		if(el.tagName === 'undefined') el = el[0];
		return new RegExp('(\\s|^)'+name+'(\\s|$)').test(JCM.getAttribute(el, 'class'));
	};

	this.addClass = function(el, name){
		if(el.tagName === 'undefined') el = el[0];
		if (!this.hasClass(el, name)){
			if(typeof(el.setAttribute) === 'undefined') return;
			el.setAttribute('class', (JCM.getAttribute(el, 'class') ? JCM.getAttribute(el, 'class') + ' ' : '') +name);
		}
	};

	this.removeClass = function(el, name){
		if(el.tagName === 'undefined') el = el[0];
		if (this.hasClass(el, name)) {
			el.setAttribute('class', JCM.getAttribute(el, 'class').replace(new RegExp('(\\s|^)'+name+'(\\s|$)'),' ').replace(/^\s+|\s+$/g, ''));
		}
	};

	this.lang = function(text){
		if(!Array.isArray(text)){
			if(arguments.length === 1) return text;
			else text = Array.from(arguments);
		}
		return text[typeof(window._lang) === 'undefined' ? 0 : window._lang];
	};

	this.Init();
};

/* -----------------------------------------------------
 *
 *   Message Modal
 *
 ----------------------------------------------------- */
window.CMAlert = function(message, callback){
	alert(message);
	if(typeof(callback) === 'function') callback();
};

window.CMConfirm = function(message, yesCallback, noCallback){
	if(confirm(message)){
		if(typeof(yesCallback) === 'function') yesCallback();
	}
	else{
		if(typeof(noCallback) === 'function') noCallback();
	}
};

var MessageModal = {
	activeElement : null,
	alertNumber : 0,

	Init : function(){

		$(document).on('mousedown touch', '.MessageModal footer a', function(e){
			MessageModal.activeElement = $('*:focus');
		});

		$(document).on('click', '.MessageModal footer a', function(e){
			e.preventDefault();
			var obj = $(this).data();
			if(typeof(obj.onclick) === 'function') obj.onclick.call(this);
			MessageModal.Remove.call(this);
			$(MessageModal.activeElement).focus();
		});

		window.CMAlert = function(msg, callback){
			if(typeof callback === 'function')
				MessageModal.Create(msg, [{text : window._CM_LANG.OK, onclick : function(obj){
						callback();
					}}]);
			else MessageModal.Create(msg);
		};

		window.CMConfirm = function(message, yesCallback, noCallback, title){
			if(typeof title === 'undefined') title = window._CM_LANG.notification;
			MessageModal.Create(message, [
				{text : window._CM_LANG.OK, onclick : function(obj){
						if(typeof yesCallback === 'function') yesCallback();
					}},
				{text : window._CM_LANG.cancel, onclick : function(obj){
						if(typeof noCallback === 'function') noCallback();
					}}
			], title);
		};
	},

	Create : function(message, buttons, title){
		this.alertNumber++;
		if(typeof(title) === 'undefined') title = window._CM_LANG.notification;
		if(typeof(buttons) === 'undefined'){
			buttons = [{'text' : window._CM_LANG.OK}];
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
		if(buttons.length === 1){
			$('.MessageModal footer a').last().focus();
		}

		for(var i = 0; i < buttons.length; i++){
			var func = buttons[i].onclick;
			$('#MessageModal' + this.alertNumber + ' footer a').eq(i).data({'onclick' : func});
		}
	},

	Remove : function(){
		$(this).closest('.MessageModal').remove();
	}
}

var EventLink = {

	startPos : null,
	endPos : null,
	dragObj : null,

	touchStart : function(e){
		EventLink.startPos = (typeof(e.originalEvent) === 'undefined' || typeof(e.originalEvent.touches) === 'undefined') ? {
			'pageX': e.pageX,
			'pageY': e.pageY,
			'clientX' : e.clientX,
			'clientY' : e.clientY
		} : e.originalEvent.touches[0];

		var node = document.elementFromPoint(EventLink.startPos.pageX - $(window).scrollLeft(), EventLink.startPos.pageY - $(window).scrollTop());

		while(node){
			if($(node).hasClass('bh-event-touch')){
				if (this.tagName === 'SELECT'){
					e.preventDefault();
				}
			}
			if($(node).hasClass('bh-event-drag')){
				EventLink.dragObj = node;
			}
			node = node.parentNode;
		}
	},

	Init : function(){
		$.fn.touch = function(arg1, arg2){
			if(typeof arg1 === 'function'){
				arg2 = arg1;
				arg1 = this;
			}
			if(this === arg1){
				$(this).on('e_touch', arg2);

				$(this).on('click', function(e){
					e.preventDefault();
				});

				$(this).on('touchstart mousedown', function(e){
					JCM.addClass(this, 'bh-event-touch');
					EventLink.touchStart.call(this, e);
				});
			}
			else{
				$(this).on('e_touch', arg1, arg2);

				$(this).on('click', arg1, function(e){
					e.preventDefault();
				});
				$(this).on('touchstart mousedown', arg1, function(e){
					JCM.addClass(this, 'bh-event-touch');
					EventLink.touchStart.call(this, e);
				});
			}
		};

		$.fn.touchVisible = function(arg1, arg2){
			if(typeof arg1 === 'function'){
				arg2 = arg1;
				arg1 = this;
			}

			if(this === arg1){
				$(this).on('e_touch_visible', arg2);
				$(this).on('touchstart mousedown', function(e){
					JCM.addClass(this, 'bh-event-touch-visible');
					EventLink.touchStart.call(this, e);
				});
			}
			else{
				$(this).on('e_touch_visible', arg1, arg2);
				$(this).on('touchstart mousedown', arg1, function(e){
					JCM.addClass(this, 'bh-event-touch-visible');
					EventLink.touchStart.call(this, e);
				});
			}
		};

		$.fn.drag = function(arg1, arg2, arg3){
			if(typeof arg1 === 'function'){
				arg3 = arg2;
				arg2 = arg1;
				arg1 = this;
			}
			$(arg1).on('dragstart', function(e){
				return false;
			});

			if(this === arg1){

				$(this).on('e_drag', arg2);
				$(this).on('e_drag_end', arg3);
				$(this).on('touchstart mousedown', function(e){
					JCM.addClass(this, 'bh-event-drag');
					EventLink.touchStart.call(this, e);
				});
			}
			else{

				$(this).on('e_drag', arg1, arg2);
				$(this).on('e_drag_end', arg1, arg3);
				$(this).on('touchstart mousedown', arg1, function(e){
					JCM.addClass(this, 'bh-event-drag');
					EventLink.touchStart.call(this, e);
				});
			}
		};

		$(document).on('touchmove mousemove', 'body', function(e){
			if(EventLink.startPos === null) return;
			EventLink.endPos = (typeof(e.originalEvent) === 'undefined' || typeof(e.originalEvent.touches) === 'undefined') ? {
				'pageX': e.pageX,
				'pageY': e.pageY,
				'clientX' : e.clientX,
				'clientY' : e.clientY
			} : e.originalEvent.touches[0];

			if(EventLink.dragObj !== null) $(EventLink.dragObj).trigger('e_drag', [EventLink.startPos, EventLink.endPos]);
		});

		$(document).on('touchend mouseup', 'body', function(e){
			if(EventLink.startPos === null) return true;
			if(EventLink.endPos === null) EventLink.endPos = EventLink.startPos;

			var node = document.elementFromPoint(EventLink.endPos.pageX - $(window).scrollLeft(), EventLink.endPos.pageY - $(window).scrollTop());

			var x = EventLink.endPos.clientX - EventLink.startPos.clientX;
			var y = EventLink.endPos.clientY - EventLink.startPos.clientY;

			var clickIs = (Math.abs(x) < 5 && Math.abs(y) < 5);

			if(clickIs && $(node).hasClass('bh-event-touch-visible')){
				e.cancelable=false;
				e.preventDefault();
				e.stopImmediatePropagation();
				if(node.tagName === 'A') node.off('click').on('click', function(e){e.preventDefault()});
				$(node).trigger('e_touch_visible');
			}

			if(!clickIs && EventLink.dragObj !== null){
				if(typeof(e.originalEvent) !== 'undefined' && e.originalEvent.type == 'touchend'){
					e.cancelable=true;
				}
				e.preventDefault();
				e.stopImmediatePropagation();
				if($(EventLink.dragObj)[0].tagName === 'A') $(EventLink.dragObj).off('click').on('click', function(e){e.preventDefault()});
				$(EventLink.dragObj).trigger('e_drag_end', [EventLink.startPos, EventLink.endPos]);
			}

			while(node !== this && node){
				if(clickIs && $(node).hasClass('bh-event-touch')){
					if(typeof(e.originalEvent) !== 'undefined' && e.originalEvent.type == 'touchend'){
						e.cancelable=true;
					}
					e.preventDefault();
					e.stopImmediatePropagation();
					if(node.tagName === 'A') $(node).off('click').on('click', function(e){e.preventDefault()});
					$(node).trigger('e_touch');
				}
				node = node.parentNode;
			}

			// end
			EventLink.startPos = null;
			EventLink.endPos = null;
			EventLink.dragObj = null;
			return true;
		});
	}
}

/* -----------------------------------------------------
 *
 *    스크롤 영역 이미지 스크롤 바
 *
 *    element 필수 태그클래스
 *    .scrollContents
 *    .scrollBar > .scrollBtn
 *
 ----------------------------------------------------- */
function ScrollAreaInit(selector){
	var bar = $(selector).find('.scrollBar').eq(0);
	var btn = $(selector).find('.scrollBtn').eq(0);
	var scrollContents = $(selector).find('.scrollContents').eq(0);
	var maxHeight = bar.height() - btn.height();

	scrollContents.scroll(function(){
		var sh = scrollContents[0].scrollHeight - scrollContents.height();
		var sy = scrollContents.scrollTop() / sh;
		btn.css({top : (maxHeight * sy) + 'px'});
	});

	btn.drag(function(e, startPos, endPos){
		// scroll begin
		var y = endPos.pageY - (bar.offset().top - $(window).scrollTop());
		y -= btn.height() / 2;
		if(y < 0) y = 0;
		else if(y > maxHeight) y = maxHeight;
		btn.css({top : y + 'px'});
		scrollConetntsAct(y);
	}, function(e, startPos, endPos){
		// scroll end
	});

	function scrollConetntsAct(y){
		var sh = scrollContents[0].scrollHeight - scrollContents.height();
		var sy = sh * (y / maxHeight);
		if(!sh) btn.css({top : 0});
		scrollContents.scrollTop(sy);
	}
}

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

		if (layer.attr('data-load') === 'y') {
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
}

var _ImageAlign = new ImageAlign(jQuery);

/* -------------------------------------------
 *
 *   For Custom Select Tag
 *
 ------------------------------------------- */
function SelectBox($){
	var _this = this;
	this.parentSelectElement = null;

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

		if($(this)[0].hasAttribute('data-option-type') && !$(this)[0].hasAttribute('data-has-touch-e')){
			if(typeof $(this).touch === 'undefined') EventLink.Init();
			$(this).touch(_this.OptionToUl);
			$(this).attr('data-has-touch-e', 'y');
		}
	};

	this.OptionToUl = function(){
		if($('#selectOptionWrap').length){
			_this.parentSelectElement = null;
			$('#selectOptionWrap').remove();
			return;
		}
		if(_this.parentSelectElement !== null) return;

		_this.parentSelectElement = this;

		var type = $(this).attr('data-option-type');
		var html = '<div id="selectOptionWrap" class="selectOptionWrap' + type + '"><div class="selectOptionContents"><ul>';
		var selVal = $(this).val();
		$(this).children('option').each(function(){
			var value = this.hasAttribute('value') ? $(this).attr('value') : $(this).text();
			var view = this.hasAttribute('data-view') ? $(this).attr('data-view') : $(this).text();
			var selected = selVal == value ? ' class="selected"' : '';
			html += '<li><button type="button" data-value="' + value + '"' + selected + '>' + view + '</button></li>';
		});
		html += '</ul></div>';

		$('body').append(html);

		var optWrap = $('#selectOptionWrap');
		optWrap.css({
			position : 'absolute',
			top : 0,
			left : 0,
			width : '100%',
			'z-index' : 9999,
			height : $(document).height() + 'px'
		});

		optWrap.find('.selectOptionContents').css({
			position : 'absolute',
			left : 0,
			top : 0,
			'min-width' : $(_this.parentSelectElement).closest('.selectBox').outerWidth() + 'px'
		});

		optWrap.touchVisible(function(){
			$(this).remove();
			_this.parentSelectElement = null;
		});

		optWrap.find('button').touch(_this.OptionClick);

		$(_this.parentSelectElement).trigger('init_layer_option', document.getElementById('selectOptionWrap'));

		if(type === 'drop') _this.DropOptionSetPosition();
	};

	this.DropOptionSetPosition = function(){
		var optWrap = $('#selectOptionWrap');
		if(!optWrap.length) return;
		var sBox = $(_this.parentSelectElement).closest('.selectBox');
		var x = sBox.offset().left;
		optWrap.css({
			height : $(document).height() + 'px'
		});
		if($(window).height() / 2 > sBox.offset().top - $(window).scrollTop()){
			var y = sBox.offset().top + sBox.outerHeight();
			optWrap.find('.selectOptionContents').css({
				top : y + 'px',
				left : x + 'px',
			});
		}
		else{
			var y = sBox.offset().top;
			optWrap.find('ul').css({
				top : (y - optWrap.find('ul').outerHeight()) + 'px',
				left : x + 'px',
			});
		}
		setTimeout(function(){
			_this.DropOptionSetPosition();
		}, 200);
	};

	this.OptionClick = function(e){
		$(_this.parentSelectElement).selectVal($(this).attr('data-value'));
		_this.parentSelectElement = null;
		$('#selectOptionWrap').remove();
	};
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
	$.ieIs = navigator.appName === 'Microsoft Internet Explorer';

	$.fn.FormReset = function(){
		$(this)[0].reset();
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
		if(typeof before.z === 'undefined') before.z = 0;
		if(typeof before.x === 'undefined') before.x = 0;
		if(typeof before.y === 'undefined') before.y = 0;
		if(typeof before.css === 'undefined') before.css = {};
		if(typeof after.z === 'undefined') after.z = 0;
		if(typeof after.x === 'undefined') after.x = 0;
		if(typeof after.y === 'undefined') after.y = 0;
		if(typeof after.css === 'undefined') after.css = {};
		$.each(before, function(idx, val){
			if(idx !== 'z' && idx !== 'x' && idx !== 'y' && idx !== 'css') before.css[idx] = val;
		});
		$.each(after, function(idx, val){
			if(idx !== 'z' && idx !== 'x' && idx !== 'y' && idx !== 'css') after.css[idx] = val;
		});
		if($.ieIs){
			before.css.top = before.y;
			before.css.left = before.x;
			after.css.top = after.y;
			after.css.left = after.x;
			before.css['transition'] = '0s';
			before.css.display = 'block';
			$(this).css(before.css);
			$(this).animate(after.css, duration, complete);
		}
		else{
			$(this).off('transitionend webkittransitionend mstransitionend');
			var beforeTranslate = 'translate3d(' + before.x + ', ' + before.y + ', ' + before.z + ')';
			var afterTranslate = 'translate3d(' + after.x + ', ' + after.y + ', ' + after.z + ')';
			before.css.transition = '0s';
			if(before.css.transform){
				beforeTranslate += ' ' + before.css.transform;
				delete before.css.transform;
			}
			before.css['-webkit-transform'] = beforeTranslate;
			before.css['-ms-transform'] = beforeTranslate;
			before.css.transform = beforeTranslate;
			before.css.display = 'block';
			$(this).css(before.css);

			$(this).css('width');
			if(typeof(complete) === 'function'){
				var t = this;
				$(this).on('transitionend webkittransitionend mstransitionend', function(e){
					$(t).off('transitionend webkittransitionend mstransitionend');
					complete.call(this, e);
				});
			}

			after.css.transition = duration + 'ms';
			if(after.css.transform){
				afterTranslate += ' ' + after.css.transform;
				delete after.css.transform;
			}
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

				if ((this.hasAttribute('required') || $(this).hasClass('checkboxRequired')) && !this.hasAttribute('disabled')) {
					if ($(this).attr('type') === 'checkbox' || $(this).attr('type') === 'radio') {
						if (!f.find('input[name="' + $(this).attr('name') + '"]:checked').length) {
							var obj = this;
							CMAlert(window._CM_LANG.selectItem.replace('{item}', $(this).attr('data-displayname')), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}
					else if ($.trim($(this).val()) === '') {
						var obj = this;
						CMAlert(window._CM_LANG.inputItem.replace('{item}', $(this).attr('data-displayname')), function(){
							$(obj).focus();
						});
						ret = false;
						return false;
					}
				}

				if(this.tagName === 'INPUT' && $(this).attr('type') !== 'radio' && $(this).attr('type') !== 'checkbox' && $.trim(this.value) !== ''){
					if($(this).hasClass('engonly')){
						var val = this.value.replace(/[^a-zA-Z]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyEng.replace('{item}', $(this).attr('data-displayname')), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('email')){
						var v = $.trim(this.value);
						if(v !== '' && !JCM.validateEmail(this.value)){
							var obj = this;
							CMAlert(window._CM_LANG.wrongType.replace('{item}', $(this).attr('data-displayname')), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('tel')){
						var val = this.value.replace(/[^0-9\-\+\(\)\*\#]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.wrongType.replace('{item}', $(this).attr('data-displayname')), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('engnumonly')){
						var val = this.value.replace(/[^a-zA-Z0-9]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyEngNum.replace('{item}', $(this).attr('data-displayname')), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('numberonly')){
						var val = this.value.replace(/[^0-9\-\.]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyNum.replace('{item}', $(this).attr('data-displayname')), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('numberformat')){
						var val = this.value.replace(/[^0-9\,]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyNum.replace('{item}', $(this).attr('data-displayname')), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if($(this).hasClass('engspecialonly')) {
						var val = this.value.replace(/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>'\"\?\-=\+_\:\;\[\]\{\}\/]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyEngNumSpecial.replace('{item}', $(this).attr('data-displayname')), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if(this.hasAttribute('data-minlength')){
						var len = parseInt($(this).attr('data-minlength'));
						if($(this).val().length < len){
							var obj = this;
							CMAlert(window._CM_LANG.orMore.replace('{item}', $(this).attr('data-displayname')).replace('{n}', len), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}
					if(this.hasAttribute('data-maxlength')){
						var len = parseInt($(this).attr('data-maxlength'));
						if($(this).val().length > len){
							var obj = this;
							CMAlert(window._CM_LANG.orLess.replace('{item}', $(this).attr('data-displayname')).replace('{n}', len), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}

					}
					if(($(this).hasClass('numberonly') || $(this).hasClass('numberformat')) && this.hasAttribute('data-minvalue')){
						var min = parseInt($(this).attr('data-minvalue'));
						var val = parseInt(JCM.removeComma($(this).val()));
						if(val < min){
							var obj = this;
							CMAlert(window._CM_LANG.orMoreValue.replace('{item}', $(this).attr('data-displayname')).replace('{n}', min), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}

					}
					if(($(this).hasClass('numberonly') || $(this).hasClass('numberformat')) && this.hasAttribute('data-maxvalue')){
						var max = parseInt($(this).attr('data-maxvalue'));
						var val = parseInt(JCM.removeComma($(this).val()));
						if(val > max){
							var obj = this;
							CMAlert(window._CM_LANG.orLessValue.replace('{item}', $(this).attr('data-displayname')).replace('{n}', max), function(){
								$(obj).focus();
							});
							ret = false;
							return false;
						}
					}
				}

				if(this.hasAttribute('data-same') && this.tagName === 'INPUT'){
					var target = $(this).closest('form').find('input[name=' + $(this).attr('data-same') + ']');
					if(target.length){
						if($(this).val() !== target.val()){
							CMAlert(window._CM_LANG.notMatchValue.replace('{item}', target.attr('data-displayname')), function(){
								target.focus();
							});
							ret = false;
							return false;
						}
					}
				}

			}
		});
		return ret;
	};

	$.fn.selectVal = function(str){
		if($(this)[0].tagName === 'SELECT'){
			var opt = $(this).find('option[value="' + str + '"]');
			if(opt.length) opt[0].selected = true;
			else{
				var options = $(this).find('option');
				options[0].selected = true;
				options.each(function(){
					if(!this.hasAttribute('value') && $(this).text() === str) this.selected = true;
				});

			}
			if($(this).closest('.selectBox').length){
				_SelectBox.Set.call(this);
			}
		}
		else $(this).val(str);
	};

	$.fn.imgLoad = function(callback){
		var nowThis = this;
		var tmpImg = new Image();
		tmpImg.onload = function () {
			if(typeof callback === 'function') callback.call(nowThis);
		};

		tmpImg.src = this.attr('src');
	};
})(jQuery);

var JCM = new Common(jQuery);

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
	if(typeof(e) !== 'undefined' && e.keyCode === 8){
		if(len === 4){
			e.preventDefault();
			val = val.substring(0, 3);
			len = 3;
			$(this).val(val);
		}
		else if(len === 7){
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

		if(n3 !== val){
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
function SE2_paste(id, defaultfolder, hiddenBtns){
	if(tinyMCEHelper.useTinyMce){
		tinyMCEHelper.Paste(id, defaultfolder, hiddenBtns);
		return;
	}
	var scriptLoadIs = typeof(nhn) !== 'undefined' && typeof(nhn.husky) !== 'undefined' && typeof(nhn.husky.EZCreator) !== 'undefined';
	if(scriptLoadIs){
		SE2LoadIs = true;
		spaste(id, defaultfolder, hiddenBtns);
	}
	else{
		if(!SE2LoadIs){
			SE2LoadIs = true;
			$.getScript('/Skin/js/smarteditor2/dist/js/service/HuskyEZCreator.js').done(function( s, Status ) {
				spaste(id, defaultfolder, hiddenBtns);
			});
		}
		else{
			setTimeout(function(){
				SE2_paste(id, defaultfolder, hiddenBtns);
			}, 200);
		}
	}


	function spaste(id, defaultfolder, hiddenBtns){
		if(!hiddenBtns){
			var additionalBtns = '<div class="se2_add_img" data-sname="'+id+'">' +
				'<span><button type="button" class="upbtn"><i></i><span>' + window._CM_LANG.image + '</span></button></span>' +
				'<div></div>' +
				'</div>';

			additionalBtns += '<div class="se2_add_youtube">' +
				'<span><button type="button" data-sname="'+id+'"><i></i><span>' + window._CM_LANG.youtube + '</span></button></span>' +
				'</div>';

			additionalBtns += '<div class="se2_add_link">' +
				'<span><button type="button" data-sname="'+id+'"><i></i><span>' + window._CM_LANG.link + '</span></button></span>' +
				'</div>';

			$('#'+id).before('<div class="se2_addi_btns">' + additionalBtns + '</div>');
		}
		if(!$('#fileupfrm').length){
			var imgfrm = '<form id="fileupfrm" method="post" action="/Upload/ImageUpload/" enctype="multipart/form-data" style="display:block; width:0; height:0; overflow: hidden;">' +
				'<input type="file" name="Filedata" value="" data-sname="" accept="image/*" id="fileupinp" />' +
				'</form>';
			$('body').append(imgfrm);
		}

		nhn.husky.EZCreator.createInIFrame({
			oAppRef: oEditors,
			elPlaceHolder: id,
			sSkinURI: defaultfolder + "/Skin/js/smarteditor2/dist/SmartEditor2Skin.html",
			htParams : {
				bUseToolbar : true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
				bUseVerticalResizer : true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
				bUseModeChanger : true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
				//aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
				// bSkipXssFilter : true,		// client-side xss filter 무시 여부 (true:사용하지 않음 / 그외:사용)
				bSkipXssFilter : true,
				fOnBeforeUnload : function(){
					//CMAlert("완료!");
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

$(document).on('click','.se2_add_youtube button',function(e){
	e.preventDefault();
	var sname = $(this).attr('data-sname');
	if($('#youtubeLinkModal').length) return;
	var html = '<article id="youtubeLinkModal" class="modal_layer" data-sname="' + sname + '"><div class="modal_wrap">' +
		'<header class="modal_header"><h1>' + window._CM_LANG.youtubeLink + '</h1><button type="button" class="close"><i class="cross"></i></button></header><div class="modal_contents">' +
		'<dl><dt>' + window._CM_LANG.linkUrl + '</dt><dd><textarea id="youtubeText"></textarea></dd></dl>' +
		'<dl><dt>' + window._CM_LANG.size + '</dt><dd>' + window._CM_LANG.width + ' : <input type="text" class="num" id="youtubeWidthInp" value="720"> * ' + window._CM_LANG.height + ' : <input type="text" id="youtubeHeightInp" class="num" value="405"></dd></dl>' +
		'<footer><button type="button" id="youtubeSubmitBtn" class="mBtn btn2">' + window._CM_LANG.append + '</button></footer>' +
		'</div></div></article>';
	$('body').append(html);
	JCM.showModal('youtubeLinkModal');

	$('#youtubeSubmitBtn').on('click', function(){

		var w = $('#youtubeWidthInp').val();
		var h = $('#youtubeHeightInp').val();
		if(!w.match(/[^0-9]/)) w = w + 'px';
		if(!h.match(/[^0-9]/)) h = h + 'px';
		var html = Youtube($('#youtubeText').val(), w, h);
		oEditors.getById[sname].exec('PASTE_HTML', [html]);
		JCM.removeModal('#youtubeLinkModal');
	});



	function Youtube(urlOrId, width, height){
		urlOrId = GetYoutubeId(urlOrId);
		if(typeof(height) !== 'undefined') height = 'height : ' + height + ';';
		if(typeof(width) !== 'undefined') width = 'width : ' + width + ';';
		if(urlOrId !== false) return '<iframe src="https://www.youtube.com/embed/' + urlOrId + '?rel=0&amp;showinfo=0&amp;autohide=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen style="' + width + height + '" autohide="1"></iframe>';
		return '';
	}

	function GetYoutubeId(urlOrId){
		urlOrId = $.trim(urlOrId);
		var find = urlOrId.match(/youtu\.be\/([a-zA-Z0-9\-\_]+)/);
		if(find !== null && find.length > 1){
			return find[1];
		}

		var find = urlOrId.match(/youtube\.com\/embed\/([a-zA-Z0-9\-\_]+)/);
		if(find !== null && find.length > 1){
			return find[1];
		}

		var find = urlOrId.match(/youtube\.com\/watch.*?v=([a-zA-Z0-9\-\_]+)/);
		if(find !== null && find.length > 1){
			return find[1];
		}
		return false;
	}
});

$(document).on('click','.se2_add_link button',function(e){
	e.preventDefault();
	var sname = $(this).attr('data-sname');
	if($('#urlLinkModal').length) return;
	var html = '<article id="urlLinkModal" class="modal_layer" data-sname="' + sname + '"><div class="modal_wrap">' +
		'<header class="modal_header"><h1>' + window._CM_LANG.appendLink + '</h1><button type="button" class="close"><i class="cross"></i></button></header><div class="modal_contents">' +
		'<dl><dt>' + window._CM_LANG.linkUrl + '</dt><dd><input type="text" id="urlLinkInp" class="w100p"></dd></dl>' +
		'<footer><button type="button" id="urlLinkSubmitBtn" class="mBtn btn2">' + window._CM_LANG.append + '</button></footer>' +
		'</div></div></article>';
	$('body').append(html);
	JCM.showModal('urlLinkModal');

	$('#urlLinkSubmitBtn').on('click', function(){
		var url = $.trim($('#urlLinkInp').val());
		if(url !== ''){
			var html = '<a href="' + url + '" target="_blank">' + url + '</a>';
			oEditors.getById[sname].exec('PASTE_HTML', [html]);
		}
		JCM.removeModal('#urlLinkModal');
	});
});

$(document).on('change', '#fileupinp', function(e){
	e.preventDefault();
	$('#fileupfrm').submit();
});

$(document).on('submit','#fileupfrm',function(e){
	e.preventDefault();
	JCM.ajaxForm(this, function(result){
		var hinp = '<input type="hidden" name="addimg[]" value="'+result.path+'|'+result.fname+'">';
		$('.se2_add_img div').append(hinp);
		$('#fileupfrm')[0].reset();
		var html = '<img src="' + result.uploadDir + result.path + '">';
		oEditors.getById[$('#fileupinp').attr('data-sname')].exec('PASTE_HTML', [html]);
	});
});

function SE2_update(id){
	if(tinyMCEHelper.useTinyMce) return;
	oEditors.getById[id].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
}

/* -------------------------------------------
 *
 *   tinymce
 *
 ------------------------------------------- */
var tinyMCEHelper = {
	useTinyMce : false,
	tinyMCELoadIs : false,
	tinyMCEPath : '',
	plugin : 'advlist autolink link image lists charmap print preview media emoticons',
	toolbar : 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bulllist numlist outdent indent | link image media emoticons',
	mobileToolbar : 'undo bold italic link image bullist styleselect forecolor',
	defaultFolder : '',
	opt : {
		selector: '',  // change this value according to your HTML
		language : 'ko_KR',
		relative_urls : false,
		plugins: '',
		toolbar : '',
		mobile: {
			theme: 'mobile',
			toolbar: null
		},
		images_upload_credentials: true,
		images_upload_handler: function (blobInfo, success, failure) {
			var xhr, formData;

			xhr = new XMLHttpRequest();
			xhr.withCredentials = false;
			xhr.open('POST', tinyMCEHelper.defaultFolder + '/Upload/ImageUpload');

			xhr.onload = function() {
				var json;

				if (xhr.status < 200 || xhr.status >= 300) {
					failure('HTTP Error: ' + xhr.status);
					return;
				}

				json = JSON.parse(xhr.responseText);

				if (!json || !json.result || typeof json.data.path != 'string') {
					failure('Invalid JSON: ' + xhr.responseText);
					return;
				}

				var hinp = '<input type="hidden" name="addimg[]" value="'+json.data.path+'|'+json.data.fname+'">';
				$(tinyMCEHelper.opt.selector).after(hinp);
				success(json.data.uploadDir + json.data.path);
			};

			formData = new FormData();
			var fileName = ( typeof(blobInfo.blob().name) !== undefined ) ? blobInfo.blob().name : blobInfo.filename();
			formData.append('Filedata', blobInfo.blob(), fileName);

			xhr.send(formData);
		}
	},
	Paste : function(id, defaultfolder, hiddenimage, callback){
		var _this = this;
		_this.opt.selector = '#' + id,
			_this.defaultFolder = defaultfolder;
		var scriptLoadIs = typeof(tinymce) !== 'undefined';
		if(scriptLoadIs){
			_this.tinyMCELoadIs = true;
			_tmPaste(id, defaultfolder, hiddenimage);
		}
		else{
			if(!_this.tinyMCELoadIs){
				_this.tinyMCELoadIs = true;
				$('body').append('<script src="' + _this.tinyMCEPath + '"></script>');
			}
			setTimeout(function(){
				_this.Paste(id, defaultfolder, hiddenimage);
			}, 200);
		}

		function _tmPaste(id, defaultfolder, hiddenimage){
			var p = _this.plugin;
			var t = _this.toolbar;
			var mt = _this.mobileToolbar;
			if(hiddenimage === true){
				p = p.replace(/image/, '');
				t = t.replace(/image/, '');
				mt = mt.replace(/image/, '');
				mt = mt.replace(/\s\s/, ' ').split(' ');
			}
			if(typeof callback === 'function'){
				callback(tinymce);
			}
			_this.opt.plugins = p;
			_this.opt.toolbar = t;
			_this.opt.mobile.toolbar = mt;
			tinymce.init(_this.opt);
		}
	}
};


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
	area.find('input.checkAll')[0].checked = !area.find('input.checkItem:not(:checked)').length;
});

/* -------------------------------------------
 *
 *   Image Preview
 *   file입력창 바로 전에 클래스 UploadImagePreview 가 있으면 이미지 미리보기
 *   ie10+
 *
 ------------------------------------------- */
$(document).on('change', '.UploadImagePreview input[type=file]', function () {
	if (JCM.ie8 || JCM.ie9) return;

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
$(document).on('keyup click change focusin focusout', 'input.numberonly, input.numberOnly', function() {
	var val = this.value;
	var minusIs = false;
	if(val.length){
		if(val[0] === '-') minusIs = true;
		val = val.replace(/[^0-9\.]/ig, '');
		var vsp = val.split('.');
		vsp[0] = vsp[0].replace(/^[0]+/ig, '');
		if(vsp.length > 1) val = (vsp[0].length ? vsp[0] : '0') + '.' + vsp[1];
		else val = vsp[0];
		val = (minusIs ? '-' : '') + val;
	}
	else val = '';
	if(this.value !== val){
		var cp = getCaretPosition(this);
		var end = this.value.length - cp.end;
		if(!this.hasAttribute('data-before-value') || $(this).attr('data-before-value') !== this.value){
			this.value = '';
			this.value = val;
			cp.end = this.value.length - end;
			setCaretPosition(this, cp.end, cp.end);
			$(this).attr('data-before-value', val);
		}
	}
});

$(document).on('keyup', 'input.engonly', function() {
	var val = this.value.replace(/[^a-zA-Z]/gi,'');
	if(this.value !== val) this.value = val;
});

$(document).on('keyup', 'input.engnumonly', function() {
	var val = this.value.replace(/[^a-zA-Z0-9]/gi,'');
	if(this.value !== val) this.value = val;
});

$(document).on('keyup', 'input.tel', function() {
	var val = this.value.replace(/[^0-9\-\+\(\)\*\#]/gi,'');
	if(this.value !== val) this.value = val;
});

$(document).on('keyup', 'input.engspecialonly', function() {
	var val = this.value.replace(/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>'\"\?\-=\+_\:\;\[\]\{\}\/]/gi,'');
	if(this.value !== val) this.value = val;
});

$(document).on('keyup click change focusin focusout', 'input.numberformat', function(e){
	var cp = getCaretPosition(this);
	var end = $(this).val().length - cp.end;
	if(this.value === '') this.value = '0';
	var val = JCM.setComma(this.value.replace(/[^0-9]/gi,''));
	if(e.type !== 'focusout' && val === '0') val = '';
	if(!this.hasAttribute('data-before-value') || $(this).attr('data-before-value') !== this.value){
		this.value = '';
		this.value = val;
		cp.end = $(this).val().length - end;
		setCaretPosition(this, cp.end, cp.end);
		$(this).attr('data-before-value', val);
	}
});

function getCaretPosition(ctrl){
	// IE < 9 Support
	if (document.selection) {
		ctrl.focus();
		var range = document.selection.createRange();
		var rangelen = range.text.length;
		range.moveStart ('character', -ctrl.value.length);
		var start = range.text.length - rangelen;
		return {'start': start, 'end': start + rangelen };
	}
	// IE >=9 and other browsers
	else if (ctrl.selectionStart || ctrl.selectionStart == '0') {
		return {'start': ctrl.selectionStart, 'end': ctrl.selectionEnd };
	} else {
		return {'start': 0, 'end': 0};
	}
}


function setCaretPosition(ctrl, start, end){
	// IE >= 9 and other browsers
	if(ctrl.setSelectionRange)
	{
		ctrl.focus();
		ctrl.setSelectionRange(start, end);
	}
	// IE < 9
	else if (ctrl.createTextRange) {
		var range = ctrl.createTextRange();
		range.collapse(true);
		range.moveEnd('character', end);
		range.moveStart('character', start);
		range.select();
	}
}

$(document).on('click', '.backbtn, .hback a, a.hback', function (e) {
	e.preventDefault();
	history.back();
});

$(document).ready(function(){

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
		this.src = obj.attr('src') === '' ? '#' : obj.attr('src');
	});

	if ($('.selectMail select[name=selectMail]').length) {
		$(document).on('change', '.selectMail select[name=selectMail]', function (e) {
			var inp = $(this).closest('.selectMail').find('input.emailAddr');
			if ($(this).val() === 'x') {
				inp.val('');
			} else if ($(this).val() !== '') {
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

	function DomModified(e){
		if(e.target.tagName === 'SELECT' && $(e.target).closest('.selectBox').length){
			_SelectBox.Set.call($(e.target));
		}
	}

	function DomInserted(e){
		if($(e.target).hasClass('imgAlign')) _ImageAlign.align.call(e.target);

		if(e.target.tagName === 'IMG' && $(e.target).parent().hasClass('imgAlign')) _ImageAlign.align.call($(e.target).parent());

		if($(e.target).hasClass('selectBox')) _SelectBox.Set.call($(e.target).find('select'));

		if(e.target.tagName === 'INPUT' && $(e.target).hasClass('datePicker') && !$(e.target).hasClass('nopicker')) datepicker.call(e.target);
		$(e.target).find('.imgAlign, .selectBox, input.datePicker').each(function(){
			if($(this).hasClass('imgAlign'))_ImageAlign.align.call(this);
			if($(this).hasClass('selectBox')) _SelectBox.Set.call($(this).find('select'));
			if($(this).hasClass('datePicker') && !$(this).hasClass('nopicker')) datepicker.call(this);
		});
	}

	if(JCM.getInternetExplorerVersion >= 0){
		document.body.attachEvent('DOMNodeInserted', DomInserted);
		document.body.attachEvent('DomNodeInsertedIntoDocument', DomInserted);
		document.body.attachEvent('DOMSubtreeModified', DomModified);
	}
	else{
		document.body.addEventListener('DOMNodeInserted', DomInserted);
		document.body.addEventListener('DomNodeInsertedIntoDocument', DomInserted);
		document.body.addEventListener('DOMSubtreeModified', DomModified);
	}

	$(document).on('click', '.daumAddress .find_address', JCM.FindDaumAddress);
});

