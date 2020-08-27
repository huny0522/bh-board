var StatePage = {
	_completeKeepAction : [],
	_completeAction : [],
	url : '',
	targetId : 'containerWrap',
	isInit : false,
	beforeHash : '',
	hashAction : {}, // 해쉬가 있을 때 실행
	emptyHashAction : {}, // 해쉬가 없을 때 실행
	isAnimate : false,
	animateSpeed : 300,
	animateDirection : 'left',
	historyForwardCount : 0,
	isPopState : false,
	isHashAction : false,
	isHistoryBack : false,
	beforeTime : 0,
	wrapObj : null,
	dataCheckFunc : null,

	/**
	 * 최초 실행
	 */
	Init : function(tid){
		this.wrapObj = document.body;
		StatePage.isInit = true;
		if(typeof(tid) === 'string' && tid.length) this.targetId = tid;
		var targetElement = document.getElementById(this.targetId);
		if(!targetElement){
			console.log('StatePage Warning : 페이지 컨테이너 객체가 존재하지 않습니다.');
			return;
		}

		// a 태그 기본 액션 설정
		$(document).on('click', 'a', StatePage._LinkAction);

		$(document).on('click', '.hashClose', function(e){
			e.preventDefault();
			if(location.hash.length > 1) window.history.back();
			else if($(this).closest('.hashModal').length) $(this).closest('.hashModal').remove();
		});

		$(document).on('click', '.hashChangeBtn', function(e){
			if(this.hasAttribute('data-hash')){
				e.preventDefault();
				location.href = '#' + $(this).attr('data-hash');
			}
		});

		$(window).on('popstate', function(e) {
			if(e.originalEvent.state !== null && typeof(e.originalEvent.state.time) !== 'undefined'){
				if(StatePage.beforeTime > e.originalEvent.state.time) StatePage.isHistoryBack = true;
				StatePage.beforeTime = e.originalEvent.state.time;
			}

			var href = location.href;
			StatePage.HashAction();
			StatePage.isPopState = true;
			StatePage.Load(href, null, true);
		});

		StatePage.url = location.pathname + location.search;

		$(function(){
			StatePage.HashAction(true);
			var splitHref = location.href.split('#');
			StatePage.beforeHash = splitHref.length > 1 ? splitHref[1] : '';
		});
	},

	/**
	 * 애니메이션 활성화
	 *
	 * @param wrapObj 애니메이션을 할 객체
	 * @returns {StatePage}
	 * @constructor
	 */
	EnableAnimation : function(wrapObj){
		this.wrapObj = wrapObj;
		this.isAnimate = true;
		return this;
	},

	HashAction : function(disableEmptyAction){
		var hash = location.hash.length > 1 ? location.hash.substr(1) : '';
		if(StatePage.beforeHash === hash) return;
		if(hash.length){
			var hash2 = hash.split('-');
			var hash1 = hash2.shift();
			if(typeof(StatePage.hashAction[hash1]) === 'function'){
				StatePage.hashAction[hash1](hash2);
				StatePage.isHashAction = true;
			}
		}
		else if(disableEmptyAction !== true){
			$.each(StatePage.emptyHashAction, function(i, obj){
				if(typeof(obj) === 'function') obj();
				StatePage.isHashAction = true;
			});
		}
	},

	AddHashAction : function(name, func){
		StatePage.hashAction[name] = func;
		return this;
	},

	AddEmptyHashAction : function(name, func){
		StatePage.emptyHashAction[name] = func;
		return this;
	},

	SetDataCheck : function(func){
		this.dataCheckFunc = func;
		return this;
	},

	/**
	 * 해당 주소로 선택된 객체의 페이지를 변경
	 *
	 * @param href
	 * @param obj
	 * @param noPush boolean(true : replaceState, false : pushState) default : false
	 * @param force boolean
	 * @public
	 */
	Load : function(href, targetId, noPush, force){
		var splitHref = href.split('#');
		StatePage.beforeHash = splitHref.length > 1 ? splitHref[1] : '';
		if(!StatePage.isInit){
			location.href = href;
			return;
		}

		var linkEl = document.createElement("a");
		linkEl.href = href;

		if(StatePage.url !== '' && (StatePage.url == linkEl.pathname + linkEl.search && force !== true)) return;

		if(!StatePage.isPopState) StatePage.beforeTime = (new Date()).getTime();

		JCM.getWithLoading(href, {hash : linkEl.hash}, function(html, commonData){

			var el = null;
			if(typeof(targetId) === 'string' && targetId !== '') el = $(StatePage.wrapObj).find('#' + targetId);
			if(!el) el = $(StatePage.wrapObj).find('#' + StatePage.targetId);

			if(typeof StatePage.dataCheckFunc === 'function' && !StatePage.dataCheckFunc(html, commonData)) return;
			$(el).html(html);

			if(typeof(commonData) !== 'undefined' && commonData !== null && typeof(commonData.appendHtml) !== 'undefined'){
				$(el).append(commonData.appendHtml);
			}
			StatePage.url = linkEl.href;

			if(StatePage.isAnimate === true){
				var temp = document.createElement('div');
				temp.id = 'statePageAnimationObj';
				$(temp).html($(StatePage.wrapObj).html());
				$(temp).find('script').remove();
				$('#wrap').append(temp);

				$(StatePage.wrapObj).css({
					position : 'fixed',
					top : 0,
					left : 0
				});
				var d = {
					'wrap1x' : ['100%', '0%'],
					'wrap2x' : ['0%', '-100%'],
				};
				if(StatePage.animateDirection === 'right'){
					d = {
						'wrap1x' : ['-100%', '0%'],
						'wrap2x' : ['0%', '100%'],
					};
				}
				$(StatePage.wrapObj).translate3d({x : d.wrap1x[0]}, {x : d.wrap1x[1]}, StatePage.animateSpeed, function(){
					$(temp).remove();
					$(StatePage.wrapObj).css({'position' : 'static', 'transition' : '0s', 'transform' : '', '-webkit-transform' : '', '-ms-transform' : ''});

					StatePage._CompleteAction(commonData);
				});

				$(temp).translate3d({x : d.wrap2x[0]}, {x : d.wrap2x[1]}, StatePage.animateSpeed);
				StatePage.animateDirection = 'left';
			}

			if(noPush) history.replaceState({time : StatePage.beforeTime}, '', href);
			else history.pushState({time : StatePage.beforeTime}, '', href);

			if(StatePage.isAnimate !== true) StatePage._CompleteAction(commonData);
		});
	},

	LoadForce : function(href, obj, noPush){
		StatePage.Load(href, obj, noPush, true);
	},

	/**
	 * 페이지 완료 후 1회 실행할 함수를 추가
	 *
	 * @param func
	 * @public
	 */
	AddCompleteAction : function(func){
		StatePage._completeAction.push(func);
	},

	/**
	 * 페이지 완료 후 지속적으로 실행할 함수를 추가
	 *
	 * @param func
	 * @public
	 */
	AddCompleteKeepAction : function(func){
		StatePage._completeKeepAction.push(func);
		return this;
	},

	/**
	 * a 태그 기본 액션
	 *
	 * @param e
	 * @private
	 */
	_LinkAction : function(e){
		if(!this.hasAttribute('href') || $(this).hasClass('button') || $(this).attr('href') === '#' || $(this).attr('href').substring(0, 11).toLowerCase() === 'javascript:' || $(this).closest('.buttons').length) return;
		e.preventDefault();
		StatePage.Load(this.href, this.hasAttribute('data-target-id') ? document.getElementById($(this).attr('data-target-id')) : null);
	},

	/**
	 * 페이지 완료 후 함수들을 실행
	 *
	 * @private
	 */
	_CompleteAction :function(commonData){
		for(var i = 0; i < StatePage._completeKeepAction.length; i++){
			if(typeof(StatePage._completeKeepAction[i]) === 'function') StatePage._completeKeepAction[i](commonData);
		}
		for(var i = 0; i < StatePage._completeAction.length; i++){
			if(typeof(StatePage._completeAction[i]) === 'function') StatePage._completeAction[i](commonData);
		}
		StatePage._completeAction = [];
		StatePage.isPopState = false;
		StatePage.isHashAction = false;
		StatePage.isHistoryBack = false;
	},
};

$(function(){
	StatePage._CompleteAction();
});