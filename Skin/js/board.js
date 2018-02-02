var AppBoard = {
	EventInit : false,

	ListInit : function(){
		if(!this.EventInit){
			this.EventInit = true;

			$(document).on('click', '.passwordView', AppBoard.ClickPwdView);

			$(document).on('click', '#secretViewForm button[type=reset]', AppBoard.ResetPwdView);
		}
	},

	MoreListEventInit : false,

	MoreListInit : function(){
		if(!this.MoreListEventInit){
			this.MoreListEventInit = true;

			$(document).on('submit', '#bbsSchForm', AppBoard.SubmitSearchForm);

			$(document).on('click', '#moreViewBtn', AppBoard.ClickMoreViewBtn);

			$(document).on('click', '.passwordView', AppBoard.ClickPwdView);

			$(document).on('click', '#secretViewForm button[type=reset]', AppBoard.ResetPwdView);
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
			var boardList = $('#bhBoardList table.list tbody');
			if(data.lastIs) $('#bhBoardList .moreViewBtn').hide();
			else $('#bhBoardList .moreViewBtn').show();
			if($.trim(data.list) === ''){
				if($.trim(boardList.text()) === '') boardList.html('<p class="nothing">검색된 글이 없습니다.</p>');
				return;
			}
			boardList.append(data.list);
		});
	},

	SubmitSearchForm : function(e){
		e.preventDefault();
		$('#bhBoardList table.list tbody').html('');
		$(this).find('input[name=searchKeyword]').val($(this).find('input[name=searchInput]').val());
		$(this).find('input[name=lastSeq]').val('');
		AppBoard.GetMoreList();
	},

	ClickMoreViewBtn : function(e){
		e.preventDefault();
		$('#bhBoardList .lastSeq').val($('#bhBoardList table.list tbody tr').last().attr('data-seq'));
		AppBoard.GetMoreList();
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
			}
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

		Init : function(useSE2){
			this.useSE2Is = useSE2;
			if(useSE2) SE2_paste('MD_content','');
			if(!this.EventInit){
				this.EventInit = true;

				$(document).on('submit', '#BoardWriteForm', AppBoard.Write.SubmitWrite);
			}
		},

		SubmitWrite : function(e){
			if(AppBoard.Write.useSE2Is) SE2_update('MD_content');

			var res = $(this).validCheck();
			if(!res){
				e.preventDefault();
				return false;
			}

		}
	}
};
