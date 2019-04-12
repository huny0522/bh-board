var App = {
	Init : function(){
		// MessageModal.Init();
		// App.Tooltip.Init();
		App.UserMenuPopup();
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

	userMenu : {
		'UMP_SendMsgBtn' : {title : '쪽지보내기', action : function(uid){
				JCM.getModal('/Message/Write', {id : uid}, '쪽지보내기', 'messageModifyModal', 600, 550);
			}},
		'UMP_MsgChatBtn' : {title : '쪽지 채팅', action : function(uid){
				JCM.getModal('/Message/Chat/' + uid, {}, '쪽지 채팅', 'messageModal', 400, 450);
			}},
		'UMP_BlockBtn' : {title : '차단하기', action : function(uid){
				CMConfirm('정말 차단하시겠습니까?', function(){
					JCM.post('/MyPage/BlockUser', {id : uid}, function(data){
						location.reload();
					})
				})
			}},
		'UMP_SearchBtn' : {title : '쪽지 확인', action : function(uid){
			location.href = '/Message/?id=' + uid;
			}},
	},

	// 팝업메뉴 뜬 후 호출
	UserMenuPopupCustom : function(){

	},

	UserMenuPopup : function(){
		$(document).on('click', '.userPopupMenuBtn', function(e){
			var noChat = this.hasAttribute('data-no-chat');
			if(!this.hasAttribute('data-id') || $(this).attr('data-id') === '') return;
			var uid = $(this).attr('data-id');
			e.preventDefault();
			var top = $(this).offset().top + $(this).outerHeight();
			var left = $(this).offset().left;
			var html = '<article id="userMenuPopup" style="position:absolute; left:' + left + 'px; top:' + top + 'px; z-index:1001; border:1px solid #666; background:#fff;"><ul>';
			$.each(App.userMenu, function(id, val){
				if(noChat && id === 'UMP_MsgChatBtn') return;
				html += '<li><button type="button" id="' + id + '">' + val.title + '</button></li>';
			});
			html += '</ul></article>';
			html += '<button type="button" id="userMenuPopupRMBtn" style="position:fixed; z-index:1000; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0); cursor:default;"></button>';
			$('body').append(html);
			$.each(App.userMenu, function(id, val){
				$('#' + id).on('click', function(e){
					val.action(uid);
					$('#userMenuPopup').remove();
					$('#userMenuPopupRMBtn').remove();
				});
			});
			$('#userMenuPopupRMBtn').on('click', function(){
				$('#userMenuPopup').remove();
				$(this).remove();
			});
			App.UserMenuPopupCustom();
		});
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

	},

	messageChat : {
		config : {},
		messageRef : null,
		uid : '',
		roomId : '',
		layerObj : null,
		chatBoxObj : null,
		formObj : null,
		time : 0,
		dataGetUrl : '',
		targetId : '',
		reservedFunc : null,
		initIs : false,

		SetTargetId : function(id){
			this.targetId = id;
			return this;
		},
		SetId : function(id){
			this.uid = id;
			return this;
		},
		SetDataGetUrl : function(str){
			this.dataGetUrl = str;
			return this;
		},
		SetStartTime : function(str){
			this.time = str;
			return this;
		},
		SetRoomId : function(str){
			this.roomId = str;
			return this;
		},
		SetToken : function(str){
			this.token = str;
			return this;
		},
		SetConfig : function(str){
			this.config = str;
			return this;
		},
		Init : function(){
			if(this.dataGetUrl === ''){
				console.log('새글 불러오기 경로가 필요합니다.');
				return;
			}
			if(this.targetId === ''){
				console.log('대상 아이디가 필요합니다.');
				return;
			}
			$(function(){
				if(App.messageChat.layerObj === null) App.messageChat.layerObj = $('#messageBox');
				if(App.messageChat.chatBoxObj === null) App.messageChat.chatBoxObj = $('#messageChatWrap');
				if(App.messageChat.formObj === null) App.messageChat.formObj = App.messageChat.layerObj.find('form').eq(0);
				console.log(App.messageChat.layerObj.length);

				// Send Button 에 클릭이벤트 적용
				App.messageChat.formObj.on('submit',App.messageChat.Submit);

				App.messageChat.GetList();

				App.messageChat.layerObj.on('click', '#msgChatBeforeReadBtn', function(){
					App.messageChat.GetList(true);
				});

				if(App.messageChat.layerObj.closest('.modal_layer').length){
					App.messageChat.layerObj.closest('.modal_layer').data('close_method', function(){
						App.messageChat.MessageOff();
						App.messageChat.layerObj = null;
						App.messageChat.chatBoxObj = null;
						App.messageChat.formObj = null;
					});
				}


				App.messageChat.formObj.find('textarea')[0].disabled = true;
				if(!App.messageChat.initIs){
					firebase.initializeApp(App.messageChat.config);
				}

				var database = firebase.database();
				App.messageChat.messageRef = database.ref('/messages/' + App.messageChat.roomId + '/');


				App.messageChat.ChildAdded();
				App.messageChat.ChildChanged();

				firebase.auth().signInWithCustomToken(App.messageChat.token).then(function(){
					if(App.messageChat.formObj !== null) App.messageChat.formObj.find('textarea')[0].disabled = false;
				}).catch(function(reason){
					console.log(reason);
				});

				App.messageChat.initIs = true;
			});
		},

		MessageOff : function(){
			console.log('message ref off');
			if(App.messageChat.messageRef !== null) App.messageChat.messageRef.off();
		},

		Submit : function(e){
			e.preventDefault();
			var form = this;
			if(App.messageChat.messageRef === null) return;
			JCM.ajaxForm(this, function(data){
				// App.messageChat.messageRef.push().set({uid : App.messageChat.uid, message : $.trim(App.messageChat.formObj.find('textarea').val()), timestamp : firebase.database.ServerValue.TIMESTAMP});
				firebase.database().ref('/messages/' + App.messageChat.roomId + '/' + data.seq).set({timestamp : data.timestamp, readis : false});
				if($(form).find('.fileUploadArea2').length){
					$(form).find('.fileUploadArea2').each(function(){
						$(this).find('.fileName').remove();
						$(this).find('.fileUploadInput').val();
					});
				}
			});
			this.reset();
		},

		ChildChanged : function(){
			App.messageChat.messageRef.on('child_changed', function (snapshot) {
				var data = snapshot.val();
				if(data.readis) $('#msgChatAtc' + snapshot.key).find('div.notRead').remove();
				console.log('changed', data);
			});
		},

		ChildAdded : function(){
			App.messageChat.messageRef.orderByChild('timestamp').startAt(App.messageChat.time).on('child_added', function (snapshot) {
				if(App.messageChat.gettingListIs){
					App.messageChat.reservedFunc = App.messageChat.GetList;
				}
				else{
					App.messageChat.GetList();
				}
			});
		},

		gettingListIs : false,
		noBeforeDataIs : false,

		GetList : function(before){
			if(typeof(before) === 'undefined') before = false;
			App.messageChat.gettingListIs = true;
			var articles = App.messageChat.chatBoxObj.find('article');
			var last = before ? articles.eq(0) : articles.last();
			last = last.length ? last.attr('data-seq') : '';
			JCM.get(App.messageChat.dataGetUrl, {'lastSeq' : last, 'targetId' : App.messageChat.targetId, 'beforeIs' : before ? 1 : 0}, function(data){
				App.messageChat.gettingListIs = false;
				if(typeof(App.messageChat.reservedFunc) === 'function'){
					App.messageChat.reservedFunc();
					App.messageChat.reservedFunc = null;
				}
				if(data.data.length){
					var chat = '';
					for(var i = 0; i < data.data.length; i++){
						chat += App.messageChat.DataToHtml(data.data[i]);
					}
					if(!App.messageChat.chatBoxObj.children().length) App.messageChat.chatBoxObj.append('<div></div>');
					var wrap = App.messageChat.chatBoxObj.children();
					if(before){
						wrap.eq(0).prepend(chat);
					}
					else{
						wrap.eq(0).append(chat);
						setTimeout(function (){
							wrap.scrollTop(wrap[0].scrollHeight);
						}, 100);
					}

					if($('#msgChatBeforeReadBtn').length) $('#msgChatBeforeReadBtn').remove();
					if(!App.messageChat.noBeforeDataIs) wrap.prepend('<button class="beforeReadBtn" id="msgChatBeforeReadBtn">이전 메세지 읽기</button>');
				}

				if(data.noReadSeq.length){
					for(var i = 0; i < data.noReadSeq.length; i++){
						firebase.database().ref('/messages/' + App.messageChat.roomId + '/' + data.noReadSeq[i] + '/readis').set(true);
					}
				}
			}, function(data){
				App.messageChat.gettingListIs = false;
				if(typeof(App.messageChat.reservedFunc) === 'function'){
					App.messageChat.reservedFunc();
					App.messageChat.reservedFunc = null;
				}
				if(data.noBeforeDataIs === true){
					if($('#msgChatBeforeReadBtn').length) $('#msgChatBeforeReadBtn').remove();
					App.messageChat.noBeforeDataIs = true;
				}
			});
		},

		DataToHtml : function(article){
			var chat = '';
			var c = (article.sendIs) ? 'myMsg' : 'otherMsg';
			chat += '<article class="' + c+ '" data-seq="' + article.seq + '" id="msgChatAtc' + article.seq + '"><div class="msgArticleContent">';
			if(article.filePath !== ''){
				if(article.isImage){
					chat += '<div class="img"><a href="' + article.fileLink  + '"><img src="' + article.filePath + '"></a></div>';
				}
				else chat += '<div class="file"><a href="' + article.fileLink  + '">' + JCM.html2txt(article.fileName) + '</a></div>';
			}
			chat += '<div class="msg">' + JCM.html2txt(article.comment).replace(/\n/g, '<br>') + '</div>';
			chat += '<div class="date">' + article.date + '</div>';
			if(!article.readIs) chat += '<div class="notRead">읽지않음</div>';
			chat += "</div></article>";
			return chat;
		},
	},
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