var App = {
	Init : function(){
		// MessageModal.Init();
		// App.Tooltip.Init();
	},

	FindDaumAddress : function(e){
		e.preventDefault();
		var area = $(this).closest('.daumAddress');
		JCM.popPostCode(function(data) {
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
	},

	AddressToLocByNaver : function(address1, address2, callback){
		if(typeof(naver) === 'undefined' || typeof(naver.maps) === 'undefined') return;
		naver.maps.Service.geocode({
			address: address1 + ' ' + address2
		}, function(status, response) {
			if (status === naver.maps.Service.Status.ERROR) {
				callback(false, false);
			}
			else{
				var item = response.result.items[0];
				callback(item.point.y, item.point.x);
			}
		});
	},

	AddressToLocByGoogle : function(address1, address2, callback){
		JCM.getWithLoading('http://maps.googleapis.com/maps/api/geocode/json?address=' + encodeURIComponent(address1 + ' ' + address2) + '&language=ko&sensor=false', {}, function(res){
			if (res.status == 'OK') {
				if (res.results[0]) callback(res.results[0].geometry.location.lat, res.results[0].geometry.location.lng);
			}
			else callback(false, false);
		});
	},

	Tooltip : {
		idx : 0,

		Init : function(){
			/* -------------------------------------------
			 *
			 *   툴팁
			 *
			 ------------------------------------------- */
			$(document).on('mouseenter', '.tooltip', function(e){
				if(!this.hasAttribute('title')) return;
				if(!this.hasAttribute('data-title')){
					$(this).attr('data-title', $(this).attr('title'));
					$(this).attr('title', '');
				}
				var txt = $(this).attr('data-title');
				if(!this.hasAttribute('data-tooltip-idx')){
					$(this).attr('data-tooltip-idx', App.Tooltip.idx);
					App.Tooltip.idx++;
				}
				var id = 'tooltip' + $(this).attr('data-tooltip-idx');
				if(!$('#' + id).length){
					$('body').append('<div id="' + id + '" class="popTooltip">' + txt + '</div>');
					$('div#' + id).last().css({
						position : 'absolute',
						top : 0,
						left : 0,
						'z-index' : 6000,
						padding : '3px',
						border : '1px solid #000',
						background : '#fea; color:#000',
						'white-space': 'nowrap',
						'background-color' : 'rgba(255, 250, 230, 1)',
						'border-color' : 'rgba(0,0,0,0.1)',
						'font-size' : '12px',
						'margin-top':'-3px'
					});
				}
				tooltipPostion(e);
			});

			$(document).on('mouseleave', '.tooltip', function(){
				$('#tooltip' + $(this).attr('data-tooltip-idx')).remove();
			});

			$(document).on('mousemove', '.tooltip', function(e){
				tooltipPostion(e);
			});

			function getMouseXY(e){
				return {x : e.pageX||(e.clientX+document.body.scrollLeft), y : e.pageY||(e.clientY+document.body.scrollTop)};
			}

			function tooltipPostion(e){
				var obj = $('.popTooltip');
				var xy = getMouseXY(e);
				var x = xy.x + 5;
				var y = xy.y - $(obj).outerHeight() - 5;
				if(x + $(obj).outerWidth() > $(window).width() + $(window).scrollLeft()) x = xy.x - $(obj).outerWidth() - 5;
				if(y < $(window).scrollTop()){
					y = xy.y + 5;
					x += 10;
				}
				$(obj).css({
					'left' : (x) + 'px',
					'top' : (y) + 'px'
				});
			}
		}
	},


	swiperJsIs : false,

	AutoSwiperInit : function(){
		/* -------------------------------------------
 *
 *   Swiper
 *
 ------------------------------------------- */
		$(function () {
			$('.swiper-container').each(function () {
				if ($(this).attr('data-auto-init') === '0') return;
				var opt = {};
				SwiperInit($(this), opt);
			});
		});

		App.swiperJsIs = false;
		function SwiperInit(obj, opt) {
			if(typeof(Swiper) === 'undefined'){
				if(!App.swiperJsIs){
					App.swiperJsIs = true;
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
				DOMAnimation: !(JCM.ie8 || JCM.ie9)
			};

			if(obj.attr('data-complete') === 'y'){
				return;
			}
			obj.attr('data-complete', 'y');

			if (obj.attr('data-loop') === '1') {
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
			if (obj.attr('data-center') === '1') {
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

	}
};

App.NaverMap = function(id){
	if(typeof(naver) === 'undefined' || typeof(naver.maps) === 'undefined') return;

	var div = $('#' + id);
	var lat = div.attr('data-lat');
	var lng = div.attr('data-lng');

	if(lat !== '', lng !== ''){
		lat = parseFloat(lat);
		lng = parseFloat(lng);
		var mapOptions = {
			center: new naver.maps.LatLng(lat, lng),
			zoom: 10
		};
		var map = new naver.maps.Map(id, mapOptions);

		var marker = new naver.maps.Marker({
			position: new naver.maps.LatLng(lat, lng),
			map: map,
			title : div.attr('data-title'),
		});

		if(div[0].hasAttribute('data-title') && div.attr('data-title') != ''){
			var infowindow = new naver.maps.InfoWindow({
				content: '<h1 style="font-size:16px; padding:10px 20px; margin:0;">' + div.attr('data-title') + '</h1>'
			});

			infowindow.open(map, marker);
		}
	}
}

App.AjaxQueryPage = {
	submitResetSelector : '',
	formObj : '',
	targetObj : '',
	Init : function(formId, targetId){
		this.formObj = $('#' + formId);
		this.targetObj = $('#' + targetId);

		$(window).on('popstate', function(e) {
			var q = window.location.search.substring(1);
			App.AjaxQueryPage.formObj.deserialize(q);
			App.AjaxQueryPage.AjaxForm();
		});

		this.formObj.on('submit', this.Submit);

		this.AjaxForm();
	},

	Submit : function(e){
		var l = location.href.split('?');
		var iever = getInternetExplorerVersion();
		if(iever !== -1 && iever < 10){
			$(this).attr('action', l[0]);
			return true;
		}
		e.preventDefault();
		$(this).find(App.AjaxQueryPage.submitResetSelector).val('');
		App.AjaxQueryPage.AjaxForm();

	},

	PushState : function(){
		var l = location.href.split('?');
		var q = App.AjaxQueryPage.formObj.serialize();
		history.pushState(q, '', l[0] + '?' + q);
	},

	AjaxForm : function(){
		JCM.ajaxForm(this.formObj, function(data){
			App.AjaxQueryPage.targetObj.html(data);
		});
	}
};

$.fn.deserialize = function(serializedString){
	var $form = $(this);
	$form[0].reset();
	$form.find('input[type=checkbox], input[type=radio]').each(function(){
		this.checked = false;
	});
	$form.find('select,input,textarea').not('input[type=checkbox], input[type=radio], input[type=hidden]').val('');

	serializedString = serializedString.replace(/\+/g, '%20');
	var formFieldArray = serializedString.split("&");

	// Loop over all name-value pairs
	$.each(formFieldArray, function(i, pair){
		var nameValue = pair.split("=");
		var name = decodeURIComponent(nameValue[0]);
		var value = decodeURIComponent(nameValue[1]);
		if(name === '') return;
		// Find one or more fields
		var $field = $form.find('[name="' + name + '"]');

		// Checkboxes and Radio types need to be handled differently
		if ($field[0].type == "radio" || $field[0].type == "checkbox")
		{
			var $fieldWithValue = $field.filter('[value="' + value + '"]');
			var isFound = ($fieldWithValue.length > 0);
			// Special case if the value is not defined; value will be "on"
			if (!isFound && value == "on") {
				$field.first().prop("checked", true);
			} else {
				$fieldWithValue.prop("checked", isFound);
			}
		} else { // input, textarea
			$field.val(value);
		}
	});
	return this;
}

App.Init();