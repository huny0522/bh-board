var StatePage = {
	_completeKeepAction : [],
	_completeAction : [],
	url : '',
	targetId : 'containerWrap',
	isInit : false,
	beforeHash : '',
	hashAction : {},
	emptyHashAction : {},
	isAnimate : false,
	animateSpeed : 300,
	animateDirection : 'left',
	historyForwardCount : 0,

	/**
	 * 최초 실행
	 */
	Init : function(tid){
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
			var href = location.href;
			console.log(document.referrer);
			StatePage.HashAction();
			StatePage.Load(href, null, true);
		});

		StatePage.url = location.pathname + location.search;

		$(function(){
			StatePage.HashAction(true);
			var splitHref = location.href.split('#');
			StatePage.beforeHash = splitHref.length > 1 ? splitHref[1] : '';
		});
	},

	HashAction : function(disableEmptyAction){
		var hash = location.hash.length > 1 ? location.hash.substr(1) : '';
		if(StatePage.beforeHash === hash) return;
		if(hash.length){
			if(typeof(StatePage.hashAction[hash]) === 'function') StatePage.hashAction[hash]();
		}
		else if(disableEmptyAction !== true){
			$.each(StatePage.emptyHashAction, function(i, obj){
				if(typeof(obj) === 'function') obj();
			});
		}
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

		JCM.getWithLoading(href, {hash : linkEl.hash}, function(html){

			var el = null;
			if(typeof(targetId) === 'string' && targetId !== '') el = $('#wrapInWrap').find('#' + targetId);
			if(!el) el = $('#wrapInWrap').find('#' + StatePage.targetId);

			$(el).html(html);
			StatePage.url = linkEl.href;

			if(StatePage.isAnimate === true){
				var temp = document.createElement('div');
				temp.id = 'wrapInWrap2';
				$(temp).html($('#wrapInWrap').html());
				$(temp).find('script').remove();
				$('#wrap').append(temp);

				$('#wrapInWrap').css({
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
				$('#wrapInWrap').translate3d({x : d.wrap1x[0]}, {x : d.wrap1x[1]}, StatePage.animateSpeed, function(){
					$('#wrapInWrap2').remove();
					$('#wrapInWrap').css({'position' : 'static', 'transition' : '0s', 'transform' : '', '-webkit-transform' : '', '-ms-transform' : ''});

					StatePage._CompleteAction();
				});

				$('#wrapInWrap2').translate3d({x : d.wrap2x[0]}, {x : d.wrap2x[1]}, StatePage.animateSpeed);
				StatePage.animateDirection = 'left';
			}
			else{
				StatePage._CompleteAction();
			}


			if(noPush) history.replaceState('', '', href);
			else history.pushState('', '', href);
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
	},

	/**
	 * a 태그 기본 액션
	 *
	 * @param e
	 * @private
	 */
	_LinkAction : function(e){
		if(!this.hasAttribute('href') || $(this).hasClass('button') || $(this).attr('href') === '#' || $(this).attr('href').substring(0, 11).toLowerCase() === 'javascript:') return;
		e.preventDefault();
		StatePage.Load(this.href, this.hasAttribute('data-target-id') ? document.getElementById($(this).attr('data-target-id')) : null);
	},

	/**
	 * 페이지 완료 후 함수들을 실행
	 *
	 * @private
	 */
	_CompleteAction :function(){
		for(var i = 0; i < StatePage._completeKeepAction.length; i++){
			if(typeof(StatePage._completeKeepAction[i]) === 'function') StatePage._completeKeepAction[i]();
		}
		for(var i = 0; i < StatePage._completeAction.length; i++){
			if(typeof(StatePage._completeAction[i]) === 'function') StatePage._completeAction[i]();
		}
		StatePage._completeAction = [];
	},
};

$(function(){
	StatePage._CompleteAction();
});