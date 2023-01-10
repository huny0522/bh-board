(function(){
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
	if(typeof(window._CM_LANG.del) === 'undefined') window._CM_LANG.del = '삭제';
}());

(function(bhJQuery){
	window.getInternetExplorerVersion = function() {
		var rv = -1;
		if (navigator.appName === 'Microsoft Internet Explorer') {
			var ua = navigator.userAgent;
			var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
			if (re.exec(ua) !== null)
				rv = parseFloat(RegExp.$1);
		}
		else if (!navigator.userAgent.match(/Trident\/7\./) && navigator.userAgent.indexOf("rv:11.") >= 0) rv = 11;
		return rv;
	}

	window.JCM = new function(){

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
				bhJQuery(document).ready(function(){
					document.write('현재 사용하시고 계시는 브라우저의 버전은 지원하지 않습니다.');
				});
				return;
			}*/

			/* -------------------------------------------
			 *
			 *   Modal
			 *
			 ------------------------------------------- */
			bhJQuery(document).on('click', '.modal_wrap', function (e) {
				e.stopPropagation();
			});

			bhJQuery(document).on('click', '.modal_layer .cancel, .modal_layer .close', function (e) {
				e.preventDefault();
				_this.removeModal(bhJQuery(this).closest('.modal_layer'));
			});

		}; // Init

		this.preload = function (imgs) {
			bhJQuery(imgs).each(function () {
				bhJQuery('<img/>')[0].src = this;
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
			bhJQuery('body').append('<div class="loading_layer"><div class="loading_layer_wrap"><div class="animation"></div><p>Loading...</p></div></div>');
			if(typeof this.loadingAnimation === 'function') this.loadingAnimation(bhJQuery('.loading_layer .animation').last());
		};

		this.loading_end = function () {
			this.loadingIs = false;
			bhJQuery('.loading_layer').eq(0).remove();
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
			str = str.replace(/\&/ig, '&amp;');
			str = str.replace(/</ig, '&lt;');
			str = str.replace(/>/ig, '&gt;');
			str = str.replace(/'/ig, '&#39;');
			str = str.replace(/"/ig, '&quot;');
			return str;
		};


		/* scrollTop */
		this.goTop = function () {
			bhJQuery(window).scrollTop(0);
		};

		this.goBottom = function () {
			bhJQuery(window).scrollTop(bhJQuery('body').prop('scrollHeight') + bhJQuery('body').height());
		};

		this.getCookie = function(cname, callback){
			var name = cname + "=";
			var ca = document.cookie.split(';');
			for(var i = 0; i <ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) === ' ') {
					c = c.substring(1);
				}
				if (c.indexOf(name) === 0) {
					if(typeof(callback) === 'function') callback(decodeURIComponent(c.substring(name.length,c.length)));
					return decodeURIComponent(c.substring(name.length,c.length));
				}
			}
			if(typeof(callback) === 'function') callback('');
			return "";
		};

		this.setCookie = function(cname, cvalue, exdays){
			this.setCookieMinute(cname, cvalue, exdays === null || typeof(exdays) === 'undefined' ? null : exdays * 60 * 24);
		};

		this.setCookieMinute = function(cname, cvalue, exdays){
			var expires = '';
			if(exdays !== null){
				var d = new Date();
				d.setTime(d.getTime() + (exdays*60*1000));
				expires = "expires="+ d.toUTCString();
			}
			document.cookie = cname + "=" + encodeURIComponent(cvalue) + "; path=/;" + expires;
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
			bhJQuery('#BH_Popup' + seq).hide();
		};

		this.popup = function(target, seq, top, left, width, height, data) {
			var ck = _this.getCookie('todayClosePopup' + seq);
			if (ck === 'y') return;
			if(document.getElementById('BH_Popup' + seq)){
				bhJQuery('#BH_Popup' + seq).show();
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
			bhJQuery(target).append(html);
		};

		this.validateEmail = function(email) {
			email = bhJQuery.trim(email);
			var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
			return re.test(email);
		};

		/* -------------------------------------------
		 *
		 *   AJAX
		 *
		 ------------------------------------------- */
		this.forceAjaxForm = function(formObj, success_func, fail_func, data){
			if(_this.loadingIs){
				setTimeout(function(){
					_this.forceAjaxForm(formObj, success_func, fail_func, data);
				}, 100);
			}
			else _this.ajaxForm(formObj, success_func, fail_func, data);
		};

		this.ajaxForm = function (formObj, success_func, fail_func, data){
			if(typeof bhJQuery.fn.ajaxSubmit === 'undefined'){
				console.log('jquery form not installed.');
				return;
			}
			if(!bhJQuery(formObj).length) return;
			if(this.loadingIs) return;
			_this.loading();

			bhJQuery(formObj).ajaxSubmit({
				dataType: 'json',
				async : true,
				data : typeof(data) === 'undefined' ? {} : data,
				success: function (response, textStatus, xhr, form) {
					_this.loading_end();

					if(typeof response.message !== 'undefined' && response.message !== null && response.message.length) CMAlert(response.message);
					if(typeof response.result !== 'undefined' && response.result !== null){
						if(response.result === true){
							if(typeof(_this.ajaxSuccess) === 'function')_this.ajaxSuccess(response.data, typeof(response.common) === 'undefined' ? null : response.common, success_func);
							else if(typeof success_func === 'function') success_func(response.data, typeof(response.common) === 'undefined' ? null : response.common);
						}else{
							if(typeof(_this.ajaxFailed) === 'function')_this.ajaxFailed(response.data, fail_func);
							else if(typeof fail_func === 'function') fail_func(response.data);
						}
					}else{
						if(typeof success_func === 'function') success_func(response);
					}
				},
				error: function (xhr, textStatus, errorThrown) {
					_this.loading_end();
					CMAlert('네트워크 오류가 발생했습니다.(' + textStatus + ')');
				},
				uploadProgress: function (event, position, total, percentComplete) {
					// uploadProgress
				}

			});

		};

		this._ajax = function (ur, dt, opt, success_func, fail_func) {
			var le = false;
			if (typeof(opt.loadingEnable) !== 'undefined'){
				le = opt.loadingEnable;
				if(opt.loadingEnable === true){
					if(this.loadingIs) return;
					_this.loading();
				}
				delete opt.loadingEnable;
			}


			bhJQuery.ajax({
				type: (typeof opt.type !== 'undefined' ? opt.type : 'post')
				, dataType: (typeof opt.dataType !== 'undefined' ? opt.dataType : 'json')
				, url: ur
				, data: dt
				, async: true
				, success: function (response, textStatus, jqXHR) {
					if (le === true) _this.loading_end();

					if (typeof response.message !== 'undefined' && response.message !== null && response.message.length) CMAlert(response.message);
					if(typeof response.result !== 'undefined' && response.result !== null){
						if(response.result === true){
							if(typeof(_this.ajaxSuccess) === 'function')_this.ajaxSuccess(response.data, typeof(response.common) === 'undefined' ? null : response.common, success_func);
							else if(typeof success_func !== 'undefined') success_func(response.data, typeof(response.common) === 'undefined' ? null : response.common);
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
			this._ajax(ur, dt, {type : 'post', loadingEnable : false}, success_func, fail_func);
		};

		// ajax get
		this.get = function (ur, dt, success_func, fail_func) {
			this._ajax(ur, dt, {type : 'get', loadingEnable : false}, success_func, fail_func);
		};

		// ajax post
		this.forcePostWithLoading = function (ur, dt, success_func, fail_func) {
			if(_this.loadingIs){
				setTimeout(function(){
					_this.forcePostWithLoading(ur, dt, success_func, fail_func);
				}, 100);
			}
			else _this.postWithLoading(ur, dt, success_func, fail_func);
		};

		// ajax post
		this.postWithLoading = function (ur, dt, success_func, fail_func) {
			_this._ajax(ur, dt, {type : 'post', loadingEnable : true}, success_func, fail_func);
		};

		// ajax post
		this.forceGetWithLoading = function (ur, dt, success_func, fail_func) {
			if(_this.loadingIs){
				setTimeout(function(){
					_this.forceGetWithLoading(ur, dt, success_func, fail_func);
				}, 100);
			}
			else _this.getWithLoading(ur, dt, success_func, fail_func);
		};

		// ajax get
		this.getWithLoading = function (ur, dt, success_func, fail_func) {
			this._ajax(ur, dt, {type : 'get', loadingEnable : true}, success_func, fail_func);
		};

		/**
		 * @param {{url : string, data : object, type : 'get'|'post'|'delete'|'put'=, dataType : 'json'|'html'=, isForce : boolean=, successCallback : function=, failCallback : function=, withLoading : boolean=}} opt
		 */
		this.ajax = function(opt){
			if(opt.isForce && _this.loadingIs){
				setTimeout(function(){
					_this.ajax(opt);
				}, 100);
			}
			else{
				var type = typeof(opt.type) === 'undefined' ? 'get' : opt.type;
				var dataType = typeof(opt.dataType) === 'undefined' ? 'json' : opt.dataType;
				var loadingEnable = typeof(opt.withLoading) === 'undefined' ? false : opt.withLoading;

				this._ajax(opt.url, opt.data, {type: type, dataType: dataType, loadingEnable: loadingEnable}, opt.successCallback, opt.failCallback);
			}
		}

		/* -------------------------------------------
		 *
		 *   MODAL
		 *
		 ------------------------------------------- */
		// modal 제거
		this.removeModal = function (obj) {
			var modal = (typeof obj === 'undefined') ? bhJQuery('.modal_layer:visible').last() : bhJQuery(obj);
			if(!modal.length) return;

			if(modal.attr('data-close-type') === 'hidden') modal.hide();
			else{
				if(typeof modal.data('close_method') === 'function') modal.data('close_method')();
				modal.remove();
			}
			bhJQuery('body').css({'overflow-y' : bhJQuery('body')[0].hasAttribute('data-ovy') ? bhJQuery('body').attr('data-ovy') : 'auto', 'margin-right' : '0'});
		};

		// modal 생성
		this.createModal = function (title, modal_id, data, w, h) {
			if (!modal_id) modal_id = 'modal_layer';
			if (!w) w = 400;
			if (!h) h = 300;
			var html = '<div id="' + modal_id + '" class="modal_layer"><button type="button" class="close"></button><div class="modal_wrap">';
			if (title && title !== '') html += '<div class="modal_header"><h1 class="modal_title">' + title + '</h1><button class="close"><i class="cross" title="' + window._CM_LANG.close + '"></i></button></div>';
			html += '<div class="modal_contents">' + data + '</div>';
			html += '</div></div>';
			bhJQuery('body').append(html);
			JCM.showModal(modal_id, w, h);
		};

		this.showModal = function(modal_id, w, h){
			var wrap = bhJQuery('#' + modal_id).children('.modal_wrap');
			if(!this.modalAutoSize){
				if(typeof(w) === 'number') wrap.css({'width': w + 'px'});
				else if(typeof(w) !== 'undefined'){
					var w2 = w.replace(/[^0-9]/ig,'');
					if(w2 == w) wrap.css({'width': w + 'px'});
					else wrap.css({'width': w});
				}
				if(typeof (h) === 'number') wrap.css({'height': h + 'px'});
				else if(typeof(h) !== 'undefined'){
					var h2 = h.replace(/[^0-9]/ig,'');
					if(h2 == h) wrap.css({'height': h + 'px'});
					else wrap.css({'height': h});
				}
			}

			if (_this.ie8) {
				bhJQuery('#' + modal_id).append('<div style="position:absolute; top:0; left:0; z-index:1; width:100%; height:100%; filter:alpha(opacity:70); background:black;" class="background"></div>');
			}
			bhJQuery('#' + modal_id).css("display", "block");
			var beforeW = bhJQuery('body').width();
			if(!bhJQuery('body')[0].hasAttribute('data-ovy')) bhJQuery('body').attr('data-ovy', bhJQuery('body').css('overflow-y'));
			bhJQuery('body').css('overflow-y', 'hidden');
			bhJQuery('body').css({'position' : 'relative', 'width' : 'auto', 'margin-right' : (bhJQuery('body').width() - beforeW)+'px'});

			if(this.modalAutoSize){
				if(typeof(this.modalAutoSizeWindowEvent) === 'undefined'){
					this.modalAutoSizeWindowEvent = true;
					bhJQuery(window).resize(function(){
						if(bhJQuery('.modal_wrap').length){
							bhJQuery('.modal_wrap').each(function(){
								_this.modalResize(this);
							});
						}
					});
				}

				_this.modalResize(wrap[0]);
			}
		};

		this.modalResize = function(wrapEl){
			bhJQuery(wrapEl).css({
				height : 'auto'
			});
			bhJQuery(wrapEl).css({
				height : bhJQuery(wrapEl).outerHeight() + 'px'
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
					_this.createModal(title, modal_id, typeof(data.html) !== 'undefined' ? data.html : data, w, h);
				});
			}else{
				this.post(ur, dt, function(data){
					_this.createModal(title, modal_id, typeof(data.html) !== 'undefined' ? data.html : data, w, h);
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
			var area = bhJQuery(this).parent().prev();
			var maxFileN = area[0].hasAttribute('data-max-file-number') ? parseInt(area.attr('data-max-file-number')) : 3;
			if(area.children().length >= maxFileN) return;
			area.append(bhJQuery(this).attr('data-html'));
		};

		this.imageFileFormRunIs = false;
		this.imageFileFormBtnAction = null;
		this.imageFileForm = function(btnCallback){
			if(typeof(btnCallback) === 'function') this.imageFileFormBtnAction = btnCallback;
			var _this = this;
			if(this.imageFileFormRunIs) return;
			this.imageFileFormRunIs = true;

			if(bhJQuery('#_uploadImgFrm').length) return;
			var frm = '<form id="_uploadImgFrm" method="post" action="/Upload/ImageUpload/" enctype="multipart/form-data" style="display:block; width:0; height:0; opacity:0; overflow:hidden;">' +
				'<input type="file" name="Filedata" value="" data-sname="" id="_uploadImgInp" accept="image/*" style="display:block; width:0; height:0; opacity:0;" />' +
				'</form>';
			bhJQuery('body').append(frm);

			bhJQuery(document).on('click','.fileUploadArea button.fileUploadBtn',function(e){
				e.preventDefault();
				bhJQuery('#_uploadImgFrm').data({
					obj : bhJQuery(this).closest('.fileUploadArea').find('input.fileUploadInput')[0]
				});
				if(typeof(_this.imageFileFormBtnAction) === 'function') _this.imageFileFormBtnAction(document.getElementById('_uploadImgInp'));
				else bhJQuery('#_uploadImgInp').click();
			});
			bhJQuery(document).off('click','.multiFileUploadArea + div.multiFileUploadAdd button.fileUploadAreaAddBtn');
			bhJQuery(document).on('click','.multiFileUploadArea + div.multiFileUploadAdd button.fileUploadAreaAddBtn',function(e){
				e.preventDefault();
				_this.addFileInp.call(this);
			});

			bhJQuery(document).on('click','.fileUploadArea button.fileUploadAreaRmBtn',function(e){
				e.preventDefault();
				var area = bhJQuery(this).closest('.fileUploadArea');
				if(!area.siblings('.fileUploadArea').length) _this.addFileInp.call(this);
				area.remove();
			});
			bhJQuery(document).on('change', '#_uploadImgInp', function(e){
				e.preventDefault();
				bhJQuery('#_uploadImgFrm').submit();
			});

			bhJQuery(document).on('submit','#_uploadImgFrm',function(e){
				e.preventDefault();
				_this.ajaxForm(this, _this.imageFileSubmitRes);
			});
		};

		this.imageFileSubmitRes = function(result){
			bhJQuery('#_uploadImgFrm')[0].reset();
			var obj = bhJQuery('#_uploadImgFrm').data().obj;
			var area = bhJQuery(obj).closest('.fileUploadArea');
			bhJQuery(obj).val(result.path);
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

			if(bhJQuery('#_uploadFileFrm').length) return;
			var frm = '<form id="_uploadFileFrm" method="post" action="/Upload/FileUpload/" enctype="multipart/form-data" style="display:block; width:0; height:0; opacity:0; overflow:hidden;">' +
				'<input type="file" name="Filedata" value="" data-sname="" id="_uploadFileInp" style="display:block; width:0; height:0; opacity:0;" />' +
				'</form>';
			bhJQuery('body').append(frm);

			bhJQuery(document).on('click','.fileUploadArea2 button.fileUploadBtn',function(e){
				e.preventDefault();
				bhJQuery('#_uploadFileFrm').data({
					obj : bhJQuery(this).closest('.fileUploadArea2').find('input.fileUploadInput')[0]
				});
				if(typeof(_this.fileFormBtnAction) === 'function') _this.fileFormBtnAction(document.getElementById('_uploadFileInp'));
				else bhJQuery('#_uploadFileInp').click();
			});
			bhJQuery(document).off('click','.multiFileUploadArea + div.multiFileUploadAdd button.fileUploadAreaAddBtn');
			bhJQuery(document).on('click','.multiFileUploadArea + div.multiFileUploadAdd button.fileUploadAreaAddBtn',function(e){
				e.preventDefault();
				_this.addFileInp.call(this);
			});

			bhJQuery(document).on('click','.fileUploadArea2 button.fileUploadAreaRmBtn',function(e){
				e.preventDefault();
				var area = bhJQuery(this).closest('.fileUploadArea2');
				if(!area.siblings('.fileUploadArea2').length) _this.addFileInp.call(this);
				area.remove();
			});
			bhJQuery(document).on('change', '#_uploadFileInp', function(e){
				e.preventDefault();
				bhJQuery('#_uploadFileFrm').submit();
			});

			bhJQuery(document).on('submit','#_uploadFileFrm',function(e){
				e.preventDefault();
				_this.ajaxForm(this, _this.fileSubmitRes);
			});
		};

		this.fileSubmitRes = function(result){
			bhJQuery('#_uploadFileFrm')[0].reset();
			var obj = bhJQuery('#_uploadFileFrm').data().obj;
			var area = bhJQuery(obj).closest('.fileUploadArea2');
			bhJQuery(obj).val(result.path + '*' + result.fname);
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
			var area = bhJQuery(obj).closest('div.jqFileUploadArea');
			bhJQuery(obj).fileupload({
				url :  '/JQUpload?maxfilesize=' + (area[0].hasAttribute('data-max-size') ? bhJQuery(area).attr('data-max-size') : '') + '&ext=' + (area[0].hasAttribute('data-ext') ? bhJQuery(area).attr('data-ext') : ''),
				maxChunkSize: 2000000,
				dataType: 'json',
				done: function (e, data) {
					bhJQuery.each(data.result, function (index, file) {
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

		bhJQuery(document).on('click', 'div.jqFileUploadArea button.fileUploadBtn', function(){
			var area = bhJQuery(this).closest('div.jqFileUploadArea');
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
				JCM.addScript('//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js', function() {
					_this.popDaumPostCode(callback);
				});
			} else {
				_this.popDaumPostCode(callback);
			}
		};

		this.popDaumPostCode = function (callback) {
			if (bhJQuery('#DaumPostCode').length) return;
			bhJQuery('body').append('<div id="DaumPostCode"><div id="DaumPostCodeWrap"></div></div>');
			bhJQuery('#DaumPostCode').css({
				'position': 'fixed',
				'z-index': '9998',
				'top': '0',
				'left': '0',
				'width': '100%',
				'height': '100%',
				'background': 'none',
				'background': 'rgba(0,0,0,0.4)'
			});

			bhJQuery('#DaumPostCode').on('click touchstart', function (e) {
				bhJQuery(this).remove();
			});

			var w = 500;
			var h = 500;
			if(w > bhJQuery('body').width()){
				w = 320;
				h = 410;
			}

			bhJQuery('#DaumPostCodeWrap').css({
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
						bhJQuery('#DaumPostCode').remove();
						if(callback) callback(data);
					},
					width: (w - 10) + 'px',
					height: (h - 10) + 'px'
				}).embed(bhJQuery('#DaumPostCodeWrap')[0]);
			});
		};

		this.FindDaumAddress = function(e){
			e.preventDefault();
			var area = bhJQuery(this).closest('.daumAddress');
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

		this._addedScript = [];
		this.addScript = function(url, callback){
			if(bhJQuery.inArray(url, this._addedScript) >= 0){
				if(typeof(callback) === 'function') callback();
				return;
			}
			this._addedScript.push(url);
			var el = document.createElement('script');
			el.onload = function(e){
				if(typeof(callback) === 'function') callback();
			};
			el.type = 'text/javascript';
			el.src = url;
			document.head.appendChild(el);
		};

		this.lang = function(text){
			if(!Array.isArray(text)){
				if(arguments.length === 1) return text;
				else text = Array.from(arguments);
			}
			return text[typeof(window._lang) === 'undefined' ? 0 : window._lang];
		};

		/**
		 *
		 * @param string areaId
		 * @param {{fname : string, path : string, uploadDir : string}} uploadInfo
		 */
		this.fileUploadSuccess = function(areaId, uploadInfo){
			bhJQuery('#' + areaId).find('input[type=hidden]').val(uploadInfo.path + '*' + uploadInfo.fname);
			bhJQuery('#' + areaId).find('p.flowFile').html('<span class="fileName"></span> <label class="delFile delNewFile checkbox"><input type="checkbox"><i></i><span>' + window._CM_LANG.del + '</span></label>');
			bhJQuery('#' + areaId).find('p.flowFile span.fileName').text(uploadInfo.fname);
		}

		/**
		 *
		 * @param string areaId
		 * @param {{fname : string, path : string, uploadDir : string}} uploadInfo
		 */
		this.imageUploadSuccess = function(areaId, uploadInfo){
			bhJQuery('#' + areaId).find('input[type=hidden]').val(uploadInfo.path + '*' + uploadInfo.fname);
			bhJQuery('#' + areaId).find('p.flowImage').html('<i class="image" style="background-image:url(' + uploadInfo.uploadDir + uploadInfo.path + ')"></i><label class="delFile delNewFile checkbox"><input type="checkbox"><i></i><span>' + window._CM_LANG.del + '</span></label>');
		}

		/**
		 *
		 * @param string areaId
		 * @param {{fname : string, path : string, uploadDir : string}} uploadInfo
		 */
		this.multiFileUploadSuccess = function(areaId, uploadInfo){
			const inpName = bhJQuery('#' + areaId).attr('data-name');
			bhJQuery('#' + areaId).find('ul.flowFiles').append('<li><span class="fileName"></span><input type="hidden" name="' + inpName + '[]" value="' + uploadInfo.path + '*' + uploadInfo.fname + '"><label class="delFile delNewFile checkbox"><input type="checkbox"><i></i><span>' + window._CM_LANG.del + '</span></label></li>');
			bhJQuery('#' + areaId).find('ul.flowFiles span.fileName').last().text(uploadInfo.fname);
		}

		/**
		 *
		 * @param string areaId
		 * @param {{fname : string, path : string, uploadDir : string}} uploadInfo
		 */
		this.multiImageUploadSuccess = function(areaId, uploadInfo){
			const inpName = bhJQuery('#' + areaId).attr('data-name');
			bhJQuery('#' + areaId).find('ul.flowImages').append('<li><input type="hidden" name="' + inpName + '[]" value="' + uploadInfo.path + '*' + uploadInfo.fname + '"><i class="image" style="background-image:url(' + uploadInfo.uploadDir + uploadInfo.path + ')"></i><label class="delFile delNewFile checkbox"><input type="checkbox"><i></i><span>' + window._CM_LANG.del + '</span></label></li>');
		}

		bhJQuery(document).on('click', 'ul.flowImages label.delFile input[type=checkbox]', function(){
			if(!this.name || this.name === ''){
				bhJQuery(this).closest('li').remove();
			}
		});

		bhJQuery(document).on('click', 'ul.flowFiles label.delFile input[type=checkbox]', function(){
			if(!this.name || this.name === ''){
				bhJQuery(this).closest('li').remove();
			}
		});

		bhJQuery(document).on('click', 'p.flowFile label.delFile input[type=checkbox]', function(){
			if(!this.name || this.name === ''){
				const wrap = bhJQuery(this).closest('.flowUploadWrap');
				wrap.find('input[type=hidden]').val('');
				wrap.find('p.flowFile').text('');
				const oldVal = wrap.find('input[type=hidden]').attr('data-old-value');
				if(oldVal !== ''){
					const inpName = wrap.find('input[type=hidden]').attr('name');
					const val = oldVal.split('*');
					const fName = val.length > 1 ? val[1] : val[0].split('/').pop();
					wrap.find('p.flowFile').html('<span class="fileName"></span> <label class="delFile checkbox"><input type="checkbox" name="del_file_' + inpName + '" value="y"><i></i><span>' + window._CM_LANG.del + '</span></label>');
					wrap.find('p.flowFile span.fileName').text(fName);
				}
			}
		});

		bhJQuery(document).on('click', 'p.flowImage label.delFile input[type=checkbox]', function(){
			if(!this.name || this.name === ''){
				const wrap = bhJQuery(this).closest('.flowUploadWrap');
				wrap.find('input[type=hidden]').val('');
				wrap.find('p.flowImage').text('');
				const oldVal = wrap.find('input[type=hidden]').attr('data-old-value');
				if(oldVal !== ''){
					const inpName = wrap.find('input[type=hidden]').attr('name');
					const val = oldVal.split('*');
					wrap.find('p.flowImage').html('<i class="image" style="background-image:url(' + val[0] + ')"></i><label class="delFile checkbox"><input type="checkbox" name="del_file_' + inpName + '" value="y"><i></i><span>' + window._CM_LANG.del + '</span></label>');
				}
			}
		});


		this.Init();

	}

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

	window.MessageModal = {
		activeElement : null,
		alertNumber : 0,

		Init : function(){

			bhJQuery(document).on('mousedown touch', '.MessageModal footer a', function(e){
				MessageModal.activeElement = bhJQuery('*:focus');
			});

			bhJQuery(document).on('click', '.MessageModal footer a', function(e){
				e.preventDefault();
				var obj = bhJQuery(this).data();
				if(typeof(obj.onclick) === 'function') obj.onclick.call(this);
				MessageModal.Remove.call(this);
				bhJQuery(MessageModal.activeElement).focus();
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

			bhJQuery('body').append(html);
			if(buttons.length === 1){
				bhJQuery('.MessageModal footer a').last().focus();
			}

			for(var i = 0; i < buttons.length; i++){
				var func = buttons[i].onclick;
				bhJQuery('#MessageModal' + this.alertNumber + ' footer a').eq(i).data({'onclick' : func});
			}
		},

		Remove : function(){
			bhJQuery(this).closest('.MessageModal').remove();
		}
	}

	window.EventLink = {

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

			var node = document.elementFromPoint(EventLink.startPos.pageX - bhJQuery(window).scrollLeft(), EventLink.startPos.pageY - bhJQuery(window).scrollTop());

			while(node){
				if(bhJQuery(node).hasClass('bh-event-touch')){
					if (this.tagName === 'SELECT'){
						e.preventDefault();
					}
				}
				if(bhJQuery(node).hasClass('bh-event-drag')){
					EventLink.dragObj = node;
				}
				node = node.parentNode;
			}
		},

		Init : function(){
			bhJQuery.fn.touch = function(arg1, arg2){
				if(typeof arg1 === 'function'){
					arg2 = arg1;
					arg1 = this;
				}
				if(this === arg1){
					bhJQuery(this).on('e_touch', arg2);

					bhJQuery(this).on('click', function(e){
						e.preventDefault();
					});

					bhJQuery(this).on('touchstart mousedown', function(e){
						bhJQuery(this).addClass('bh-event-touch');
						EventLink.touchStart.call(this, e);
					});
				}
				else{
					bhJQuery(this).on('e_touch', arg1, arg2);

					bhJQuery(this).on('click', arg1, function(e){
						e.preventDefault();
					});
					bhJQuery(this).on('touchstart mousedown', arg1, function(e){
						bhJQuery(this).addClass('bh-event-touch');
						EventLink.touchStart.call(this, e);
					});
				}
			};

			bhJQuery.fn.touchVisible = function(arg1, arg2){
				if(typeof arg1 === 'function'){
					arg2 = arg1;
					arg1 = this;
				}

				if(this === arg1){
					bhJQuery(this).on('e_touch_visible', arg2);
					bhJQuery(this).on('touchstart mousedown', function(e){
						bhJQuery(this).addClass('bh-event-touch-visible');
						EventLink.touchStart.call(this, e);
					});
				}
				else{
					bhJQuery(this).on('e_touch_visible', arg1, arg2);
					bhJQuery(this).on('touchstart mousedown', arg1, function(e){
						bhJQuery(this).addClass('bh-event-touch-visible');
						EventLink.touchStart.call(this, e);
					});
				}
			};

			bhJQuery.fn.drag = function(arg1, arg2, arg3){
				if(typeof arg1 === 'function'){
					arg3 = arg2;
					arg2 = arg1;
					arg1 = this;
				}
				bhJQuery(arg1).on('dragstart', function(e){
					return false;
				});

				if(this === arg1){

					bhJQuery(this).on('e_drag', arg2);
					bhJQuery(this).on('e_drag_end', arg3);
					bhJQuery(this).on('touchstart mousedown', function(e){
						bhJQuery(this).addClass('bh-event-drag');
						EventLink.touchStart.call(this, e);
					});
				}
				else{

					bhJQuery(this).on('e_drag', arg1, arg2);
					bhJQuery(this).on('e_drag_end', arg1, arg3);
					bhJQuery(this).on('touchstart mousedown', arg1, function(e){
						bhJQuery(this).addClass('bh-event-drag');
						EventLink.touchStart.call(this, e);
					});
				}
			};

			bhJQuery(document).on('touchmove mousemove', 'body', function(e){
				if(EventLink.startPos === null) return;
				EventLink.endPos = (typeof(e.originalEvent) === 'undefined' || typeof(e.originalEvent.touches) === 'undefined') ? {
					'pageX': e.pageX,
					'pageY': e.pageY,
					'clientX' : e.clientX,
					'clientY' : e.clientY
				} : e.originalEvent.touches[0];

				if(EventLink.dragObj !== null) bhJQuery(EventLink.dragObj).trigger('e_drag', [EventLink.startPos, EventLink.endPos]);
			});

			bhJQuery(document).on('touchend mouseup', 'body', function(e){
				if(EventLink.startPos === null) return true;
				if(EventLink.endPos === null) EventLink.endPos = EventLink.startPos;

				var node = document.elementFromPoint(EventLink.endPos.pageX - bhJQuery(window).scrollLeft(), EventLink.endPos.pageY - bhJQuery(window).scrollTop());

				var x = EventLink.endPos.clientX - EventLink.startPos.clientX;
				var y = EventLink.endPos.clientY - EventLink.startPos.clientY;

				var clickIs = (Math.abs(x) < 5 && Math.abs(y) < 5);

				if(clickIs && bhJQuery(node).hasClass('bh-event-touch-visible')){
					e.cancelable=false;
					e.preventDefault();
					e.stopImmediatePropagation();
					if(node.tagName === 'A') node.off('click').on('click', function(e){e.preventDefault()});
					bhJQuery(node).trigger('e_touch_visible');
				}

				if(!clickIs && EventLink.dragObj !== null){
					if(typeof(e.originalEvent) !== 'undefined' && e.originalEvent.type == 'touchend'){
						e.cancelable=true;
					}
					e.preventDefault();
					e.stopImmediatePropagation();
					if(bhJQuery(EventLink.dragObj)[0].tagName === 'A') bhJQuery(EventLink.dragObj).off('click').on('click', function(e){e.preventDefault()});
					bhJQuery(EventLink.dragObj).trigger('e_drag_end', [EventLink.startPos, EventLink.endPos]);
				}

				while(node !== this && node){
					if(clickIs && bhJQuery(node).hasClass('bh-event-touch')){
						if(typeof(e.originalEvent) !== 'undefined' && e.originalEvent.type == 'touchend'){
							e.cancelable=true;
						}
						e.preventDefault();
						e.stopImmediatePropagation();
						if(node.tagName === 'A') bhJQuery(node).off('click').on('click', function(e){e.preventDefault()});
						bhJQuery(node).trigger('e_touch');
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
	 *    translate3d
	 *    ie use top, left
	 *
	 ----------------------------------------------------- */
	bhJQuery.fn.translate3d = function(before, after, duration, complete) {
		if(typeof before.z === 'undefined') before.z = 0;
		if(typeof before.x === 'undefined') before.x = 0;
		if(typeof before.y === 'undefined') before.y = 0;
		if(typeof before.css === 'undefined') before.css = {};
		if(typeof after.z === 'undefined') after.z = 0;
		if(typeof after.x === 'undefined') after.x = 0;
		if(typeof after.y === 'undefined') after.y = 0;
		if(typeof after.css === 'undefined') after.css = {};
		bhJQuery.each(before, function(idx, val){
			if(idx !== 'z' && idx !== 'x' && idx !== 'y' && idx !== 'css') before.css[idx] = val;
		});
		bhJQuery.each(after, function(idx, val){
			if(idx !== 'z' && idx !== 'x' && idx !== 'y' && idx !== 'css') after.css[idx] = val;
		});
		if(bhJQuery.ieIs){
			before.css.top = before.y;
			before.css.left = before.x;
			after.css.top = after.y;
			after.css.left = after.x;
			before.css['transition'] = '0s';
			before.css.display = 'block';
			bhJQuery(this).css(before.css);
			bhJQuery(this).animate(after.css, duration, complete);
		}
		else{
			bhJQuery(this).off('transitionend webkittransitionend mstransitionend');
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
			bhJQuery(this).css(before.css);

			bhJQuery(this).css('width');
			if(typeof(complete) === 'function'){
				var t = this;
				bhJQuery(this).on('transitionend webkittransitionend mstransitionend', function(e){
					bhJQuery(t).off('transitionend webkittransitionend mstransitionend');
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
			bhJQuery(this).css(after.css);

		}
	};


	/* -----------------------------------------------------
	 *
	 *    form valid check
	 *
	 ----------------------------------------------------- */
	bhJQuery.fn.validCheck = function(){
		var f = bhJQuery(this);
		var inp = f.find('input, select, textarea');
		var ret = true;
		inp.each(function(){
			if(ret) {

				if ((this.hasAttribute('required') || bhJQuery(this).hasClass('checkboxRequired')) && !this.hasAttribute('disabled')) {
					if (bhJQuery(this).attr('type') === 'checkbox' || bhJQuery(this).attr('type') === 'radio') {
						if (!f.find('input[name^="' + bhJQuery(this).attr('name').replace(/[\[\]]/g,'') + '"]:checked').length) {
							var obj = this;
							CMAlert(window._CM_LANG.selectItem.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}
					else if (bhJQuery.trim(bhJQuery(this).val()) === '') {
						var obj = this;
						CMAlert(window._CM_LANG.inputItem.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
							bhJQuery(obj).focus();
						});
						ret = false;
						return false;
					}
				}

				if(this.tagName === 'INPUT' && bhJQuery(this).attr('type') !== 'radio' && bhJQuery(this).attr('type') !== 'checkbox' && bhJQuery.trim(this.value) !== ''){
					if(bhJQuery(this).hasClass('engonly')){
						var val = this.value.replace(/[^a-zA-Z]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyEng.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if(bhJQuery(this).hasClass('email')){
						var v = bhJQuery.trim(this.value);
						if(v !== '' && !JCM.validateEmail(this.value)){
							var obj = this;
							CMAlert(window._CM_LANG.wrongType.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if(bhJQuery(this).hasClass('tel')){
						var val = this.value.replace(/[^0-9\-\+\(\)\*\#]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.wrongType.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if(bhJQuery(this).hasClass('engnumonly')){
						var val = this.value.replace(/[^a-zA-Z0-9]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyEngNum.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if(bhJQuery(this).hasClass('numberonly')){
						var val = this.value.replace(/[^0-9\-\.]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyNum.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if(bhJQuery(this).hasClass('numberformat')){
						var val = this.value.replace(/[^0-9\,]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyNum.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if(bhJQuery(this).hasClass('engspecialonly')) {
						var val = this.value.replace(/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>'\"\?\-=\+_\:\;\[\]\{\}\/]/gi,'');
						if(val !== this.value){
							var obj = this;
							CMAlert(window._CM_LANG.onlyEngNumSpecial.replace('{item}', bhJQuery(this).attr('data-displayname')), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}

					if(this.hasAttribute('data-minlength')){
						var len = parseInt(bhJQuery(this).attr('data-minlength'));
						if(bhJQuery(this).val().length < len){
							var obj = this;
							CMAlert(window._CM_LANG.orMore.replace('{item}', bhJQuery(this).attr('data-displayname')).replace('{n}', len), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}
					if(this.hasAttribute('data-maxlength')){
						var len = parseInt(bhJQuery(this).attr('data-maxlength'));
						if(bhJQuery(this).val().length > len){
							var obj = this;
							CMAlert(window._CM_LANG.orLess.replace('{item}', bhJQuery(this).attr('data-displayname')).replace('{n}', len), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}

					}
					if((bhJQuery(this).hasClass('numberonly') || bhJQuery(this).hasClass('numberformat')) && this.hasAttribute('data-minvalue')){
						var min = parseInt(bhJQuery(this).attr('data-minvalue'));
						var val = parseInt(JCM.removeComma(bhJQuery(this).val()));
						if(val < min){
							var obj = this;
							CMAlert(window._CM_LANG.orMoreValue.replace('{item}', bhJQuery(this).attr('data-displayname')).replace('{n}', min), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}

					}
					if((bhJQuery(this).hasClass('numberonly') || bhJQuery(this).hasClass('numberformat')) && this.hasAttribute('data-maxvalue')){
						var max = parseInt(bhJQuery(this).attr('data-maxvalue'));
						var val = parseInt(JCM.removeComma(bhJQuery(this).val()));
						if(val > max){
							var obj = this;
							CMAlert(window._CM_LANG.orLessValue.replace('{item}', bhJQuery(this).attr('data-displayname')).replace('{n}', max), function(){
								bhJQuery(obj).focus();
							});
							ret = false;
							return false;
						}
					}
				}

				if(this.hasAttribute('data-same') && this.tagName === 'INPUT'){
					var target = bhJQuery(this).closest('form').find('input[name=' + bhJQuery(this).attr('data-same') + ']');
					if(target.length){
						if(bhJQuery(this).val() !== target.val()){
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

	bhJQuery.fn.imgLoad = function(callback){
		var nowThis = this;
		var tmpImg = new Image();
		tmpImg.onload = function () {
			if(typeof callback === 'function') callback.call(nowThis);
		};

		tmpImg.src = this.attr('src');
	};


	/* -------------------------------------------
	 *
	 *   Datepicker
	 *
	 ------------------------------------------- */
	if(typeof(jQuery) !== 'undefined' && typeof(jQuery.datepicker) !== 'undefined'){
		jQuery.datepicker.regional.ko = { closeText: "닫기", prevText: "이전달", nextText: "다음 달", currentText: "오늘", monthNames: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"], monthNamesShort: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"], dayNames: ["일", "월", "화", "수", "목", "금", "토"], dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"], dayNamesMin: ["일", "월", "화", "수", "목", "금", " 토"], dateFormat: "yy-mm-dd", firstDay: 0, isRTL: false };
		jQuery.datepicker.setDefaults(jQuery.datepicker.regional.ko);
	}

	window.datepicker = function() {
		if(typeof(jQuery) === 'undefined' || typeof(jQuery.datepicker) === 'undefined'){
			console.log('datepicker plugin not find.');
			return;
		}
		var opt = {
			changeYear: true,
			changeMonth: true,
			showMonthAfterYear: true,
			dateFormat: 'yy-mm-dd',
			endDate: 'today',
			todayHighlight: true
		};
		if(this.hasAttribute('data-yearRange')) opt.yearRange = $(this).attr('data-yearRange');
		else if(this.hasAttribute('data-year-range')) opt.yearRange = $(this).attr('data-year-range');
		bhJQuery(this).datepicker(opt).click(function () {
			bhJQuery(this).datepicker('show');
		});
	};


	/* -------------------------------------------
	 *
	 *   Check All
	 *
	 ------------------------------------------- */
	bhJQuery(document).on('click', '.checkAllArea input.checkAll', function(){
		var checked = this.checked;
		bhJQuery(this).closest('.checkAllArea').find('input.checkItem').each(function(){
			this.checked = checked;
		});
	});

	bhJQuery(document).on('click', '.checkAllArea input.checkItem', function(){
		var area = bhJQuery(this).closest('.checkAllArea');
		area.find('input.checkAll')[0].checked = !area.find('input.checkItem:not(:checked)').length;
	});


	/* -------------------------------------------
	 *
	 *   tinymce
	 *
	 ------------------------------------------- */
	window.tinyMCEHelper = {
		useTinyMce : false,
		tinyMCELoadIs : false,
		tinyMCEPath : '',
		plugin : 'advlist autolink link image lists charmap preview media emoticons table',
		toolbar : 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bulllist numlist outdent indent | link image media emoticons | table',
		mobileToolbar : ['undo', 'bold', 'italic', 'link', 'image', 'bullist', 'styleselect'],
		defaultFolder : '',
		opt : {
			selector: '',  // change this value according to your HTML
			language : 'ko_KR',
			relative_urls : false,
			plugins: '',
			toolbar : '',
			mobile: {
				theme: 'mobile',
				plugins: [ 'autosave', 'lists', 'autolink' ],
				toolbar: null
			},
			images_upload_credentials: true,
			image_dimensions : false,
			images_upload_handler: function(blobInfo, progress){
				return new Promise(function (resolve, reject) {
					var xhr, formData;

					xhr = new XMLHttpRequest();
					xhr.withCredentials = false;
					xhr.open('POST', tinyMCEHelper.defaultFolder + '/Upload/ImageUpload');

					xhr.onload = function() {
						var json;

						if (xhr.status < 200 || xhr.status >= 300) {
							reject('HTTP Error: ' + xhr.status);
							return;
						}

						json = JSON.parse(xhr.responseText);

						if (!json || !json.result || typeof json.data.path != 'string') {
							if(typeof(json.message) !== 'undefined') reject(json.message);
							else reject('Invalid JSON: ' + xhr.responseText);
							return;
						}

						var hinp = '<input type="hidden" name="addimg[]" value="'+json.data.path+'|'+json.data.fname+'">';
						bhJQuery(tinyMCEHelper.opt.selector).after(hinp);
						resolve(json.data.uploadDir + json.data.path);
					};

					xhr.onerror = function () {
						reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
					};

					formData = new FormData();
					var fileName = ( typeof(blobInfo.blob().name) !== undefined ) ? blobInfo.blob().name : blobInfo.filename();
					formData.append('Filedata', blobInfo.blob(), fileName);

					xhr.send(formData);
				});}
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
					bhJQuery('body').append('<script src="' + _this.tinyMCEPath + '"></script>');
				}
				setTimeout(function(){
					_this.Paste(id, defaultfolder, hiddenimage);
				}, 200);
			}

			function _tmPaste(id, defaultfolder, hiddenimage){
				var p = _this.plugin;
				var t = _this.toolbar;
				var mt = [];
				if(hiddenimage === true){
					p = p.replace(/image/, '');
					t = t.replace(/image/, '');

					for(var i = 0; i < _this.mobileToolbar.length; i++){
						mt.push(_this.mobileToolbar[i]);
					}
				}
				if(typeof callback === 'function'){
					callback(tinymce);
				}
				_this.opt.plugins = p;
				_this.opt.toolbar = t;
				_this.opt.mobile.toolbar = mt;
				if(tinymce.get(id)) tinymce.get(id).remove();
				tinymce.init(_this.opt);
			}
		}
	};


	/* -------------------------------------------
	 *
	 *   Image Preview
	 *   file입력창 바로 전에 클래스 UploadImagePreview 가 있으면 이미지 미리보기
	 *   ie10+
	 *
	 ------------------------------------------- */
	bhJQuery(document).on('change', '.UploadImagePreview input[type=file]', function () {
		if (JCM.ie8 || JCM.ie9) return;

		var img = bhJQuery(this).closest('.UploadImagePreview').find('img.preview');
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
	bhJQuery(document).on('keyup click change focusin focusout', 'input.numberonly, input.numberOnly', function(e) {
		var val = this.value;
		if(val.length){
			var re = '^([\\-]*)?(\\d+)?([\\.][0-9]*|)';
			if(this.hasAttribute('data-decimal-places')){
				var dp = bhJQuery(this).attr('data-decimal-places');
				dp = dp.replace(/[^0-9]/ig, '');
				if(dp.length){
					dp = parseInt(dp);
					if(dp === 0) re = '^([\\-]*)?(\\d+)';
					else re = '^([\\-]*)?(\\d+)?([\\.][0-9]{0,' + dp + '}|)';
				}
			}
			var r = RegExp(re, 'ig');
			var m = r.exec(val);
			val = '';
			if(m && m.length > 1){
				if(typeof(m[1]) !== 'undefined') val = '-';
				if(m.length > 2 && typeof(m[2]) !== 'undefined') val += m[2];
				if(m.length > 3 && typeof(m[3]) !== 'undefined') val += m[3];
			}
		}
		else val = '';
		if(this.value !== val){
			var cp = getCaretPosition(this);
			var end = this.value.length - cp.end;
			if(!this.hasAttribute('data-before-value') || bhJQuery(this).attr('data-before-value') !== this.value){
				this.value = '';
				this.value = val;
				cp.end = this.value.length - end;
				setCaretPosition(this, cp.end, cp.end);
				bhJQuery(this).attr('data-before-value', val);
			}
		}
		if(e.type === 'focusout' && this.value === '' && !bhJQuery(this).hasClass('leaveBlank') && !bhJQuery(this).hasClass('nozero')) this.value = '0';
	});

	bhJQuery(document).on('keyup', 'input.engonly', function() {
		var val = this.value.replace(/[^a-zA-Z]/gi,'');
		if(this.value !== val) this.value = val;
	});

	bhJQuery(document).on('keyup', 'input.engnumonly', function() {
		var val = this.value.replace(/[^a-zA-Z0-9]/gi,'');
		if(this.value !== val) this.value = val;
	});

	bhJQuery(document).on('keyup', 'input.tel', function() {
		var val = this.value.replace(/[^0-9\-\+\(\)\*\#]/gi,'');
		if(this.value !== val) this.value = val;
	});

	bhJQuery(document).on('keyup', 'input.engspecialonly', function() {
		var val = this.value.replace(/[^a-zA-Z0-9~!@\#$%^&*\(\)\.\,\<\>'\"\?\-=\+_\:\;\[\]\{\}\/]/gi,'');
		if(this.value !== val) this.value = val;
	});

	bhJQuery(document).on('keyup click change focusin focusout', 'input.numberformat', function(e){
		var cp = getCaretPosition(this);
		var end = bhJQuery(this).val().length - cp.end;
		var val = JCM.setComma(this.value.replace(/[^0-9]/gi,''));
		if(e.type === 'focusout' && this.value === '' && !bhJQuery(this).hasClass('leaveBlank') && !bhJQuery(this).hasClass('nozero')) val = this.value = '0';
		if(!this.hasAttribute('data-before-value') || bhJQuery(this).attr('data-before-value') !== this.value){
			this.value = '';
			this.value = val;
			cp.end = bhJQuery(this).val().length - end;
			setCaretPosition(this, cp.end, cp.end);
			bhJQuery(this).attr('data-before-value', val);
		}
	});

	window.getCaretPosition = function(ctrl){
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

	window.setCaretPosition = function(ctrl, start, end){
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



	bhJQuery('.UploadImagePreview img.preview').each(function () {
		var obj = bhJQuery(this);
		this.onerror = function () {
			obj.parent().hide();
		};
		this.oncomplete = function () {
			obj.parent().show();
		};
		this.src = obj.attr('src') === '' ? '#' : obj.attr('src');
	});

	if (bhJQuery('.selectMail select[name=selectMail]').length) {
		bhJQuery(document).on('change', '.selectMail select[name=selectMail]', function (e) {
			var inp = bhJQuery(this).closest('.selectMail').find('input.emailAddr');
			if (bhJQuery(this).val() === 'x') {
				inp.val('');
			} else if (bhJQuery(this).val() !== '') {
				inp.val(bhJQuery(this).val());
			}
		});
	}

	bhJQuery(document).on('click', '.tabMenu a', function (e) {
		e.preventDefault();
		var container = bhJQuery(this).closest('.tabContainer');
		var li = container.find('.tabMenu li');
		var idx = li.index(bhJQuery(this).parent());
		li.eq(idx).addClass('on').siblings().removeClass('on');
		container.find('section').eq(idx).addClass('on').siblings('section').removeClass('on');
	});

	bhJQuery(document).on('click', '.daumAddress .find_address', JCM.FindDaumAddress);

	bhJQuery(document).on('click', '.backbtn, .hback a, a.hback', function (e) {
		e.preventDefault();
		history.back();
	});


	function DomInserted(e){
		if(typeof(jQuery) === 'undefined' || typeof(jQuery.datepicker) === 'undefined') return;
		if(e.target.tagName === 'INPUT' && bhJQuery(e.target).hasClass('datePicker') && !bhJQuery(e.target).hasClass('nopicker')) datepicker.call(e.target);
		bhJQuery(e.target).find('input.datePicker').each(function(){
			if(bhJQuery(this).hasClass('datePicker') && !bhJQuery(this).hasClass('nopicker')) datepicker.call(this);
		});
	}

	document.addEventListener('DOMNodeInserted', DomInserted);
	document.addEventListener('DomNodeInsertedIntoDocument', DomInserted);

	bhJQuery(function(){
		if(typeof(jQuery) === 'undefined' || typeof(jQuery.datepicker) === 'undefined') return;
		bhJQuery('input.datePicker').not('.nopicker').each(function(){
			datepicker.call(this);
		});
	});

	window.SE2_paste = function(id, defaultfolder, hiddenBtns){
		if(tinyMCEHelper.useTinyMce){
			tinyMCEHelper.Paste(id, defaultfolder, hiddenBtns);
			return;
		}
	}

	window.SE2_update = function(id){
		if(tinyMCEHelper.useTinyMce) return;
	}

	// 함수가 생성됐는지 체크 후 콜백 실행
	window.ExecuteWhenExistsFunction = function(func, callback){
		if(typeof(func) === 'function') callback();
		else{
			setTimeout(function(){
				window.ExecuteWhenExistsFunction(func, callback);
			}, 100);
		}
	}

	var customEvent = new CustomEvent('jcm_ready');
	window.dispatchEvent(customEvent);
	window.isJcmReady = true;
}($));
