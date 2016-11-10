var common = new function ($) {

	this.data_trans_error = '데이타 전송오류';
	this.loading_cnt = 0;


	this.preload = function (imgs) {
		$(imgs).each(function () {
			$('<img/>')[0].src = this;
		});
	};

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

	this.ie8 = this.getInternetExplorerVersion() == 8 ? true : false;
	this.ie9 = this.getInternetExplorerVersion() == 9 ? true : false;

	this.loading = function () {
		if ($('#loading_layer').length) return;
		$('body').append('<div id="loading_layer"></div>');
	};

	this.loading_end = function () {
		if (!common.loading_cnt) $('#loading_layer').remove();
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


	/**
	 * 폼 ajax 전송
	 */
	this._formajax = function (formObj, datatype, success_func, fail_func) {
		common.loading_cnt++;
		common.loading();
		setTimeout(function () {
			$(formObj).ajaxSubmit({
				dataType: datatype,
				async : true,
				success: function (response, textStatus, xhr, form) {
					common.loading_cnt--;
					common.loading_end();
					if (datatype == 'html'){
						if(typeof success_func!= 'undefined') success_func(response);
					}
					else{
						if(typeof response.message != 'undefined' && response.message != null && response.message.length) alert(response.message);
						if(typeof response.result != 'undefined' && response.result != null){
							if(response.result === true){
								if(typeof success_func != 'undefined') success_func(response.data);
							}else{
								if(typeof fail_func != 'undefined') fail_func(response);
							}
						}else{
							if(typeof success_func != 'undefined') success_func(response);
						}
					}
				},
				error: function (xhr, textStatus, errorThrown) {
					common.loading_cnt--;
					common.loading_end();
					alert(textStatus);
				},
				uploadProgress: function (event, position, total, percentComplete) {
					// uploadProgress
				}

			});
		}, 50);
	};

	this.ajaxForm = function(formObj,success_func, fail_func){
		this._formajax(formObj, 'json', success_func, fail_func);
	};

	this.ajaxFormHtml = function(formObj,success_func, fail_func){
		this._formajax(formObj, 'html', success_func, fail_func);
	};

	/**
	 * ajax
	 * @param ur 전송할 URL
	 */
	this._ajax = function (ur, dt, opt, success_func, fail_func) {
		if (dt.loadingDisble !== true) {
			common.loading_cnt++;
			common.loading();
		}

		var datatype = typeof opt.dataType != 'undefined' ? opt.dataType : 'json';
		setTimeout(function () {
			$.ajax({
				type: (typeof opt.type != 'undefined' ? opt.type : 'post')
				, dataType: datatype
				, url: ur
				, data: dt
				, async: true
				, success: function (response, textStatus, jqXHR) {
					if (dt.loadingDisble !== true) {
						common.loading_cnt--;
						common.loading_end();
					}

					if (datatype == 'html'){
						if(typeof success_func!= 'undefined') success_func(response);
					}
					else {
						if (typeof response.message != 'undefined' && response.message != null && response.message.length) alert(response.message);
						if(typeof response.result != 'undefined' && response.result != null){
							if(response.result === true){
								if (typeof success_func!= 'undefined') success_func(response.data);

							}else{
								if (typeof fail_func != 'undefined') fail_func(response);
							}
						}else{
							if (typeof success_func!= 'undefined') success_func(response);
						}
					}
				}
				, error: function (jqXHR, textStatus, errorThrown) {
					if (dt.loadingDisble !== true) {
						common.loading_cnt--;
						common.loading_end();
					}
					alert(textStatus);
				}
			});
		}, 50);
	};

	/**
	 * ajax post
	 */
	this.post = function (ur, dt, success_func, fail_func) {
		this._ajax(ur, dt, {type : 'post', dataType : 'json'}, success_func, fail_func);
	};


	/**
	 * ajax get
	 */
	this.get = function (ur, dt, success_func, fail_func) {
		this._ajax(ur, dt, {type : 'get', dataType : 'json'}, success_func, fail_func);
	};

	/**
	 * ajax post, get HTML
	 */
	this.postHtml = function (ur, dt, success_func, fail_func) {
		this._ajax(ur, dt, {type : 'post', dataType : 'html'}, success_func, fail_func);
	};


	/**
	 * ajax get, get HTML
	 */
	this.getHtml = function (ur, dt, success_func, fail_func) {
		this._ajax(ur, dt, {type : 'get', dataType : 'html'}, success_func, fail_func);
	};


	/**
	 * ajax를 보낸 후 모달창을 띄움(createModal)
	 */
	this.getModal = function (ur, dt, title, modal_id, w, h) {
		this._ajaxModal('get', ur, dt, title, modal_id, w, h);
	}
	this.postModal = function (ur, dt, title, modal_id, w, h) {
		this._ajaxModal('post', ur, dt, title, modal_id, w, h);
	}

	this._ajaxModal = function (type, ur, dt, title, modal_id, w, h) {
		if (dt.loadingDisble !== true) {
			common.loading_cnt++;
			common.loading();
		}
		setTimeout(function () {
			$.ajax({
				type: type
				, url: ur
				, data: dt
				, async: true
				, success: function (data, textStatus, jqXHR) {
					if (dt.loadingDisble !== true) {
						common.loading_cnt--;
						common.loading_end();
					}
					common.createModal(title, modal_id, data, w, h);
				}
				, error: function (jqXHR, textStatus, errorThrown) {
					if (dt.loadingDisble !== true) {
						common.loading_cnt--;
						common.loading_end();
					}
					alert(common.data_trans_error);
				}
			});
		}, 50);
	};


	this.removeModal = function (modal_id) {
		if (!modal_id) $('.modal_layer').remove();
		else $('#' + modal_id).remove();
		$('body').css('overflow-y', 'scroll');
	};

	this.createModal = function (title, modal_id, data, w, h) {
		if (!modal_id) modal_id = 'modal_layer';
		if (!w) w = 400;
		if (!h) h = 300;
		var html = '<div id="' + modal_id + '" class="modal_layer"><div class="modal_wrap">';
		if (title && title != '') html += '<div class="modal_header"><h1 class="modal_title">' + title + '</h1><p class="close_modal_btn"><i class="fa fa-close" title="닫기" onclick="common.removeModal(\'' + modal_id + '\')"></i></p></div>';
		html += '<div class="modal_contents">' + data + '</div>';
		html += '</div></div>';
		$('body').append(html);
		$('#' + modal_id).children('.modal_wrap').css({
			'width': w + 'px'
			, 'height': h + 'px'
		});
		if (common.ie8) {
			$('#' + modal_id).append('<div style="position:absolute; top:0; left:0; z-index:1; width:100%; height:100%; filter:alpha(opacity:\'70\'); background:black;" class="background"></div>');
		}
		$('#' + modal_id).css("display", "block");
		var box = $('#' + modal_id).children('.modal_wrap');
		box.css({
			'margin': '-' + (box.outerHeight() / 2) + 'px' + ' 0 0 -' + (box.outerWidth() / 2) + 'px'
		});
		$('body').css('overflow-y', 'hidden');

	};

	$(document).on('click', '.modal_layer', function (e) {
		common.removeModal();
	});

	$(document).on('click', '.modal_wrap', function (e) {
		e.stopPropagation();
	});

	$(document).on('click', '.modal_layer .cancel, .modal_layer .close', function (e) {
		e.preventDefault();
		common.removeModal($(this).parents('.modal_layer').attr('id'));
	});


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
		common.img_align();
	};

	this.popPostCode = function (callback) {
		if (typeof daum == "undefined") {

			jQuery.getScript("http://dmaps.daum.net/map_js_init/postcode.v2.js").done(function (script, textStatus) {
				common.popDaumPostCode(callback);
			});
		} else {
			common.popDaumPostCode(callback);
		}
	}
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
	}


	$(window).resize(function () {
		common.img_align();
	});

	$(document).ready(function () {
		common.img_align();
	});

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
		return str.replace(/[&<>]/g, common.replaceTag);
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
						var val = this.value.replace(/[^a-zA-Z0-9~!@\#$%^&*\()\-=+_']/gi,'');
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
		common.setCookie('todayClosePopup' + seq, 'y', 1);
		jQuery('#BH_Popup' + seq).hide();
	}

	this.popup = function(target, seq, top, left, width, height, data) {
		var ck = common.getCookie('todayClosePopup' + seq);
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
	}

	/* Image Preview
	 * file입력창 바로 전에 클래스 filePreviewImg 가 있으면 이미지 미리보기
	 * ie10+
	 */

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

	$(document).on('keyup', 'input.engspecialonly', function() {
		var val = this.value.replace(/[^a-zA-Z0-9~!@\#$%^&*\()\-=+_']/gi,'');
		if(this.value != val) this.value = val;
	});

}(jQuery);


$(document).on('click', '.backbtn, .hback a, a.hback', function (e) {
	e.preventDefault();
	history.back();
});

$(window).load(function () {
	$('.swiper-container').each(function () {
		if ($(this).attr('data-auto-init') == '0') return;
		var opt = {};
		swiper_init($(this), opt);
	});
});

function swiper_init(obj, opt) {

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
	var paging = obj.find('.swiper_paging');

	if (!paging.length) paging = obj.parent().children('.swiper_paging');
	if (paging.length) {
		if (paging.find('.pagination').length) opt.pagination = paging.find('.pagination');
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

/* datepicker */
$.datepicker.regional.ko = { closeText: "닫기", prevText: "이전달", nextText: "다음 달", currentText: "오늘", monthNames: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"], monthNamesShort: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"], dayNames: ["일", "월", "화", "수", "목", "금", "토"], dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"], dayNamesMin: ["일", "월", "화", "수", "목", "금", " 토"], dateFormat: "yy-mm-dd", firstDay: 0, isRTL: false };
$.datepicker.setDefaults($.datepicker.regional.ko);


function datepicker(selector) {
	selector.datepicker({
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
	if ($('input.date').length) {
		datepicker($('input.date'));
	}

	$(document).on('click', '.tap_menu a', function (e) {
		e.preventDefault();
		var container = $(this).closest('.tap_container');
		var li = container.find('.tap_menu li');
		var idx = li.index($(this).parent());
		li.eq(idx).addClass('on').siblings().removeClass('on');
		container.find('section').eq(idx).addClass('on').siblings('section').removeClass('on');
	});

});


/* -------------------------------------------
 *
 *   smart editor 2 attach
 *
 ------------------------------------------- */
var oEditors = [];

function SE2_paste(id, defaultfolder, hiddenimage){
	$.getScript('/common/smart_editor/js/HuskyEZCreator.js').done(function( s, Status ) {

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
			sSkinURI: defaultfolder + "/common/smart_editor/SmartEditor2Skin.html",
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

	});

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