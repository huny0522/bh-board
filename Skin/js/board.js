var AppBoard = {
	EventInit : false,
	boardWrap : '#bhBoardList',
	listWrap : 'table.list tbody',
	moreBtn : '.moreViewBtn',
	article : 'tr',

	boardWrapElement : null,
	listWrapElement : null,
	moreBtnElement : null,

	ListInit : function(){
		this.boardWrapElement = $(this.boardWrap);
		this.listWrapElement = this.boardWrapElement.find(this.listWrap);

		if(!this.EventInit){
			this.EventInit = true;

			$(document).on('click', '.passwordView', AppBoard.ClickPwdView);

			$(document).on('click', '#secretViewForm button[type=reset]', AppBoard.ResetPwdView);

			$('#moveSelItemBtn').on('click', AppBoard.CheckedMoveArticles);

			$('#copySelItemBtn').on('click', AppBoard.CheckedCopyArticles);

			$('#delSelItemBtn').on('click', AppBoard.CheckedDelArticles);

			$('#unDelSelItemBtn').on('click', AppBoard.CheckedUnDelArticles);
		}
	},

	MoreListEventInit : false,

	MoreListInit : function(){
		this.boardWrapElement = $(this.boardWrap);
		this.listWrapElement = this.boardWrapElement.find(this.listWrap);
		this.moreBtnElement = this.boardWrapElement.find(this.moreBtn);

		if(!this.MoreListEventInit){
			this.MoreListEventInit = true;

			$(document).on('submit', '#bbsSchForm', AppBoard.SubmitSearchForm);

			$(document).on('click', '#moreViewBtn', AppBoard.ClickMoreViewBtn);

			$(document).on('click', '.passwordView', AppBoard.ClickPwdView);

			$(document).on('click', '#secretViewForm button[type=reset]', AppBoard.ResetPwdView);

			$('#moveSelItemBtn').on('click', AppBoard.CheckedMoveArticles);

			$('#copySelItemBtn').on('click', AppBoard.CheckedCopyArticles);

			$('#delSelItemBtn').on('click', AppBoard.CheckedDelArticles);

			$('#unDelSelItemBtn').on('click', AppBoard.CheckedUnDelArticles);
		}

		AppBoard.GetMoreList();

	},

	ClickPwdView : function(e){
		e.preventDefault();
		$('#viewForm').attr('action', this.href);
		$('#secretViewForm').show();
	},

	ResetPwdView : function(e){
		document.querySelector('#viewForm').reset();
		$('#secretViewForm').hide();
	},

	GetMoreList : function(){
		JCM.ajaxForm('#bbsSchForm', function(data){
			if(data.lastIs) AppBoard.moreBtnElement.hide();
			else AppBoard.moreBtnElement.show();
			if($.trim(data.list) === ''){
				if($.trim(AppBoard.listWrapElement.text()) === '') AppBoard.listWrapElement.html('<p class="nothing">검색된 글이 없습니다.</p>');
				return;
			}
			AppBoard.listWrapElement.append(data.list);
		});
	},

	SubmitSearchForm : function(e){
		e.preventDefault();
		AppBoard.listWrapElement.html('');
		$(this).find('input[name=keyword]').val($(this).find('input[name=searchInput]').val());
		$(this).find('input[name=lastSeq]').val('');
		AppBoard.GetMoreList();
	},

	ClickMoreViewBtn : function(e){
		e.preventDefault();
		$('#bhBoardList .lastSeq').val(AppBoard.listWrapElement.find(AppBoard.article).last().attr('data-seq'));
		AppBoard.GetMoreList();
	},

	GetCheckBox : function(){
		var chk = '';
		AppBoard.listWrapElement.find('input.boardCheckBox:checked').each(function(){
			chk += (chk == '' ? '' : ',') + $(this).val();
		});
		return chk;
	},

	CheckedDelArticles : function(e){
		e.preventDefault();
		var obj = this;
		var chk = AppBoard.GetCheckBox();
		if(chk == ''){
			CMAlert('삭제할 게시물을 선택하여 주세요.');
			return;
		}
		CMConfirm('정말 삭제하시겠습니까?', function(){
			JCM.post(obj.href, {seq : chk}, function(){
				location.reload();
			});
		});
	},

	CheckedUnDelArticles : function(e){
		e.preventDefault();
		var obj = this;
		var chk = AppBoard.GetCheckBox();
		if(chk == ''){
			CMAlert('복구할 게시물을 선택하여 주세요.');
			return;
		}
		CMConfirm('복구하시겠습니까?', function(){
			JCM.post(obj.href, {seq : chk}, function(){
				location.reload();
			});
		});
	},

	CheckedCopyArticles : function(e){
		var chk = AppBoard.GetCheckBox();
		if(chk == ''){
			CMAlert('복사할 게시물을 선택하여 주세요.');
			return;
		}
		if(!$('#checkActionModal').length) return;
		var modal = $('#checkActionModal');
		var form = modal.find('form');
		modal.find('h1').text('복사하기');
		modal.find('input[name=seq]').val(chk);
		form[0].action = form.attr('data-copy-url');
		JCM.showModal('checkActionModal', 400, 480);
	},

	CheckedMoveArticles : function(e){
		var chk = AppBoard.GetCheckBox();
		if(chk == ''){
			CMAlert('이동할 게시물을 선택하여 주세요.');
			return;
		}
		if(!$('#checkActionModal').length) return;
		var modal = $('#checkActionModal');
		var form = modal.find('form');
		modal.find('h1').text('이동하기');
		modal.find('input[name=seq]').val(chk);
		form[0].action = form.attr('data-move-url');
		JCM.showModal('checkActionModal', 400, 480);
	},

	CheckActionInit : function(){
		$('#checkActionModal').on('click', 'button.boardActionArticleBtn', function(){
			$('#boardActionSelected > b').text($(this).text());
			$(this).closest('li').addClass('active').siblings().removeClass('active');
			$('#checkActionModal input[name=bid]').val($(this).attr('data-bid'));
			$('#checkActionModal input[name=subid]').val($(this).attr('data-subid'));

			var t = $.trim($(this).attr('data-category'));
			if(t.length){
				var html = '<select name="category" id="c_a_category" required data-btn-id="' + $(this).attr('id') + '">';
				var category = t.split(',');
				for(var i = 0; i < category.length; i++){
					html += '<option>' + JCM.html2txt(category[i]) + '</option>';
				}
				html += '</select>';
				$('#boardActionCategory').html(html);
				$('#c_a_category').on('change', function(){
					AppBoard.CheckActionCategorySelect();
				});
				AppBoard.CheckActionCategorySelect();
			}
			else{
				$('#boardActionCategory').html('');
			}
		});

		$('#checkActionModal').on('click', 'button.boardActionGroupBtn', function(){
			var ul = $(this).next();
			if(ul.length && ul[0].tagName.toLowerCase() == 'ul'){
				ul.toggle();
			}
		});

		$('#cActForm').on('submit', function(e){
			e.preventDefault();
			JCM.ajaxForm(this, function(data){
				location.reload();
			});
		});
	},

	CheckActionCategorySelect : function(){
		if($('#c_a_sub_category').length) $('#c_a_sub_category').remove();
		var t = $('#' + $('#c_a_category').attr('data-btn-id')).attr('data-sub-category');
		var sub_category = (typeof(t) !== 'undefined' && t.length) ? JSON.parse(t) : null;
		console.log(sub_category);
		if(sub_category !== null){
			var html = '<select name="sub_category" id="c_a_sub_category" required data-btn-id="' + $(this).attr('id') + '">';
			for(var x = 0; x < sub_category.length; x++){
				if(sub_category[x].category === $('#c_a_category').val()){
					for(var i = 0; i < sub_category[x].sub_category.length; i++){
						html += '<option>' + JCM.html2txt(sub_category[x].sub_category[i]) + '</option>';
					}
				}
			};
			html += '</select>';
			$('#c_a_category').after(html);
		}
	},

	View : {
		EventInit : false,

		Init : function(){
			if(!this.EventInit){
				this.EventInit = true;

				$(document).on('click', '#deleteArticle', AppBoard.View.ClickDeleteBtn);
				$(document).on('click', '#deleteForm button[type=reset]', AppBoard.View.ResetDeleteForm);

				$(document).on('click', '#modifyBtn', AppBoard.View.ClickModifyBtn);
				$(document).on('click', '#modifyForm button[type=reset]', AppBoard.View.ResetModifyForm);

				$(document).on('click', 'a.boardActionBtn, a.replyActionBtn', AppBoard.View.BoardAction);
			}
		},

		BoardAction : function(e){
			e.preventDefault();
			var obj = this;
			var message = '';
			var type = $(obj).attr('data-type');
			if(type == 'recommend'){
				message = !$(obj).hasClass('already') ? '추천하시겠습니까?' : '추천을 취소하시겠습니까?';
			}
			else if(type == 'oppose'){
				message = !$(obj).hasClass('already') ? '반대하시겠습니까?' : '반대를 취소하시겠습니까?';
			}
			else if(type == 'report'){
				message = !$(obj).hasClass('already') ? '신고하시겠습니까?' : '신고를 취소하시겠습니까?';
			}
			else if(type == 'scrap'){
				message = !$(obj).hasClass('already') ? '스크랩하시겠습니까?' : '스크랩을 취소하시겠습니까?';
			}
			else{
				CMAlert('잘못된 접근입니다.');
				return;
			}
			CMConfirm(message, function(){
				if($(obj).hasClass('already')) AppBoard.View.BoardCancelAction(obj, type);
				else AppBoard.View.BoardDoAction(obj, type);
			});
		},

		BoardDoAction : function(obj, type){
			JCM.postWithLoading(obj.href, {type : type}, function(data){
				$(obj).addClass('already');
				if(type == 'recommend'){
					CMAlert('추천했습니다.');
				}
				else if(type == 'oppose'){
					CMAlert('반대했습니다.');
				}
				else if(type == 'report'){
					CMAlert('신고했습니다.');
				}
				else if(type == 'scrap'){
					CMAlert('스크랩했습니다.');
				}
				var n = parseInt($(obj).find('span.num').text());
				$(obj).find('span.num').text(n + 1);
			}, function(data){
				var msg = '';
				var msg2 = '';
				if(type == 'recommend') msg2 = '추천';
				else if(type == 'oppose') msg2 = '반대';
				else if(type == 'report') msg2 = '신고';

				if(data == 'recommend' || data == 'rp_recommend') msg = '추천';
				else if(data == 'oppose' || data == 'rp_oppose') msg = '반대';
				else if(data == 'report' || data == 'rp_report') msg = '신고';
				else if(data == 'scrap') CMAlert('이미 스크랩했습니다.');

				if(msg !== '') CMAlert('이 글을 ' + msg + '했습니다.\n' + msg2 + '하시려면 ' + msg + ' 취소하시기 바랍니다.');
			});
		},

		BoardCancelAction : function(obj, type){
			JCM.postWithLoading($(obj).attr('data-cancel-href'), {type : type}, function(data){
				$(obj).removeClass('already');
				CMAlert('취소되었습니다.');

				var n = parseInt($(obj).find('span.num').text());
				$(obj).find('span.num').text(n - 1);
			}, function(){
			});
		},

		ClickDeleteBtn : function(e){
			e.preventDefault();
			$('#deleteForm').show();
		},

		ResetDeleteForm : function(e){
			document.querySelector('#delForm').reset();
			$('#deleteForm').hide();
		},

		ClickModifyBtn : function(e){
			if($('#modForm').length){
				e.preventDefault();
				$('#modifyForm').show();
			}
		},

		ResetModifyForm : function(e){
			document.querySelector('#modForm').reset();
			$('#modifyForm').hide();
		}
	},

	Write : {
		useSE2Is : false,
		EventInit : false,
		subCategory : '',
		bid : '',
		subid : '',
		getSubCategoryUrl : '',

		Init : function(useSE2){
			this.useSE2Is = useSE2;
			if(useSE2) SE2_paste('MD_content','');
			if(!this.EventInit){
				this.EventInit = true;

				$(document).on('submit', '#BoardWriteForm', AppBoard.Write.SubmitWrite);
			}

			$(document).ready(function(){
				AppBoard.Write.GetSubCategory();
			});

			$('#MD_category').on('change', function(){
				AppBoard.Write.GetSubCategory();
			});

		},

		SubmitWrite : function(e){
			if(AppBoard.Write.useSE2Is) SE2_update('MD_content');

			var res = $(this).validCheck();
			if(!res){
				e.preventDefault();
				return false;
			}
		},

		GetSubCategory : function(){
			var cate = $.trim($('#MD_category').val());
			var html = '<option value="">선택</option>';
			var subCate = this.subCategory;
			if(cate === ''){
				$('#MD_sub_category').html(html);
			}
			else{
				JCM.postWithLoading(this.getSubCategoryUrl, {bid : this.bid, subid : this.subid, cate : cate}, function(data){
					for(var i = 0; i < data.length; i++){
						html += '<option' + (subCate == data[i] ? ' selected' : '') + '>' + JCM.html2txt(data[i]) + '</option>';
					}
					$('#MD_sub_category').html(html);
				});
			}
		}


	}
};
