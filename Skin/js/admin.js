var AppAdmin = {
	BoardViewInit : function(){
		AppBoard.View.AdminInit = function(){
			AppBoard.View.Init();

			$(document).on('click', '#removeBtn', AppBoard.View.ClickRemoveBtn);
			$(document).on('click', '#removeForm button[type=reset]', AppBoard.View.ResetRemoveForm);
		};

		AppBoard.View.ClickRemoveBtn = function(e){
			e.preventDefault();
			$('#removeForm').show();
		};

		AppBoard.View.ResetRemoveForm = function(e){
			document.querySelector('#removeForm').reset();
			$('#removeForm').hide();
		};

		AppBoard.View.AdminInit();
	},
	BoardListInit : function(){
		AppBoard.AdminList = {
			Init : function(){
				$('a.deleteArticle').on('click', AppBoard.AdminList.ClickDeleteBtn);

				$('#deleteForm button[type=reset]').on('click', AppBoard.AdminList.ResetDeleteForm);

				$('a.removeArticle').on('click', AppBoard.AdminList.ClickRemoveBtn);

				$('#removeForm button[type=reset]').on('click', AppBoard.AdminList.ResetRemoveForm);

				$('.passwordView').on('click', AppBoard.AdminList.ClickPwdBtn);

				$('#secretViewForm button[type=reset]').on('click', AppBoard.AdminList.ResetPwdForm);
			},

			ClickDeleteBtn : function(e){
				e.preventDefault();
				$('#delForm').attr('action', this.href);
				$('#deleteForm').show();
			},

			ResetDeleteForm : function(e){
				document.querySelector('#deleteForm').reset();
				$('#deleteForm').hide();
			},

			ClickRemoveBtn : function(e){
				e.preventDefault();
				$('#remForm').attr('action', this.href);
				$('#removeForm').show();
			},

			ResetRemoveForm : function(e){
				document.querySelector('#removeForm').reset();
				$('#removeForm').hide();
			},

			ClickPwdBtn : function(e){
				e.preventDefault();
				$('#viewForm').attr('action', this.href);
				$('#secretViewForm').show();
			},

			ResetPwdForm : function(e){
				document.querySelector('#viewForm').reset();
				$('#secretViewForm').hide();
			}
		};

		AppBoard.AdminList.Init();
	}
};
