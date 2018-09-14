var AppAdmin = {
	BoardView : {
		Init : function(){
			AppBoard.View.Init();

			$(document).on('click', '#removeBtn', AppAdmin.BoardView.ClickRemoveBtn);
			$(document).on('click', '#removeForm button[type=reset]', AppAdmin.BoardView.ResetRemoveForm);
		},

		ClickRemoveBtn : function(e){
			e.preventDefault();
			$('#removeForm').show();
		},

		ResetRemoveForm : function(e){
			document.querySelector('#removeForm').reset();
			$('#removeForm').hide();
		}
	},

	BoardList : {
		Init : function(){
			AppBoard.boardWrapElement = $(AppBoard.boardWrap);
			AppBoard.listWrapElement = AppBoard.boardWrapElement.find(AppBoard.listWrap);
			$('a.deleteArticle').on('click', AppAdmin.BoardList.ClickDeleteBtn);

			$('#listDeleteForm button[type=reset]').on('click', AppAdmin.BoardList.ResetDeleteForm);

			$('a.removeArticle').on('click', AppAdmin.BoardList.ClickRemoveBtn);

			$('#listRemoveForm button[type=reset]').on('click', AppAdmin.BoardList.ResetRemoveForm);

			$('.passwordView').on('click', AppAdmin.BoardList.ClickPwdBtn);

			$('#secretViewForm button[type=reset]').on('click', AppAdmin.BoardList.ResetPwdForm);

			$('#schDelViewChk').on('click', function(){
				$(this).closest('form')[0].submit();
			});

			$('#moveSelItemBtn').on('click', AppBoard.CheckedMoveArticles);

			$('#copySelItemBtn').on('click', AppBoard.CheckedCopyArticles);

			$('#delSelItemBtn').on('click', AppBoard.CheckedDelArticles);

			$('#unDelSelItemBtn').on('click', AppBoard.CheckedUnDelArticles);
		},

		ClickDeleteBtn : function(e){
			e.preventDefault();
			$('#listDelForm').attr('action', this.href);
			$('#listDeleteForm').show();
		},

		ResetDeleteForm : function(e){
			$('#listDelForm')[0].reset();
			$('#listDeleteForm').hide();
		},

		ClickRemoveBtn : function(e){
			e.preventDefault();
			$('#listRemForm').attr('action', this.href);
			$('#listRemoveForm').show();
		},

		ResetRemoveForm : function(e){
			$('#listRemForm')[0].reset();
			$('#listRemoveForm').hide();
		},

		ClickPwdBtn : function(e){
			e.preventDefault();
			$('#viewForm').attr('action', this.href);
			$('#secretViewForm').show();
		},

		ResetPwdForm : function(e){
			document.querySelector('#viewForm').reset();
			$('#secretViewForm').hide();
		},
	}
};
