// -------------------------------------
// Write

$(document).on('submit', '.replyWrite form', function(e){
	e.preventDefault();
	common.valCHeck(this);

    common.ajaxForm(this, function(data){
		$('#replyGetForm input[name=page]').val(1);
		getReplyList();
	});
});

// -------------------------------------
// Get List

function getReplyList(){
	common.ajaxFormHtml('#replyGetForm', function(data){
		$('#Reply').html(data);
	});
}

// Paging
$(document).on('click', '#replyPaging a', function(e){
	e.preventDefault();
	$('#replyGetForm input[name=page]').val($(this).text());
	getReplyList();
});


$(document).on('click', '.repLayer button[type=reset]', function(e){
	$(this).closest('form')[0].reset();
	$(this).closest('.repLayer').hide();
});


// -------------------------------------
// Secret Reply View Pwd

$(document).on('click', '#replyListContents a.pwdView', function(e){
	e.preventDefault();
	var seq = $(this).closest('article').attr('data-seq');
	$('#replyPwdLayer form')[0].reset();
	$('#replyPwdLayer input[name=seq]').val(seq);
	$('#replyPwdLayer').show();
});

$(document).on('submit', '#repPwdForm', function(e){
	e.preventDefault();
	var seq = $('#repPwdForm input[name=seq]').val();
	common.ajaxForm(this, function(data){
		var btns = '<a href="#" class="answerBtn">답변</a><a href="#" class="modifyBtn">수정</a><a href="#" class="deleteBtn">삭제</a>';
		$('#repArticle'+seq+' .commentText').html(data);
		$('#repArticle'+seq+' .btns').html(btns);
		$('#replyPwdLayer').hide();
	});
});


// -------------------------------------
// Delete Reply

$(document).on('click', '#replyListContents a.deleteBtn', function(e){
	e.preventDefault();
	var seq = $(this).closest('article').attr('data-seq');
	$('#replyDeleteLayer form')[0].reset();
	$('#replyDeleteLayer input[name=seq]').val(seq);
	if($(this).hasClass('myDoc')){
		$('#replyDeleteLayer .pwdinp').hide();
		$('#replyDeleteLayer .pwdinp input').attr('disabled', 'disabled');
	}else{
		$('#replyDeleteLayer .pwdinp').show();
		$('#replyDeleteLayer .pwdinp input').removeAttr('disabled');
	}

	$('#replyDeleteLayer').show();
});

$(document).on('submit', '#repDeleteForm', function(e){
	e.preventDefault();
	common.ajaxForm(this, function(data){
		getReplyList();
	});
});

// -------------------------------------
// Modify Reply

$(document).on('click', '#replyListContents a.modifyBtn', function(e){
	e.preventDefault();
	var seq = $(this).closest('article').attr('data-seq');
	var txt = $(this).closest('article').find('.commentText').text();
	$('#replyModifyLayer form')[0].reset();
	$('#replyModifyLayer input[name=seq]').val(seq);
	$('#replyModifyLayer textarea[name=comment]').val(txt);
	if($(this).hasClass('myDoc')){
		$('#replyModifyLayer .pwdinp').hide();
		$('#replyModifyLayer .pwdinp input').attr('disabled', 'disabled');
	}else{
		$('#replyModifyLayer .pwdinp').show();
		$('#replyModifyLayer .pwdinp input').removeAttr('disabled');
	}
	$('#replyModifyLayer').show();
});

$(document).on('submit', '#repModifyForm', function(e){
	e.preventDefault();
	common.ajaxForm(this, function(data){
		getReplyList();
	});
});

// -------------------------------------
// Answer Reply

$(document).on('click', '#replyListContents a.answerBtn', function(e){
	e.preventDefault();
	var article = $(this).closest('article');
	var seq = article.attr('data-seq');
	$('#replyAnswerLayer form')[0].reset();
	$('#replyAnswerLayer input[name=target_seq]').val(seq);

	var comment = article.find('.commentText').html();
	var mname = article.find('header b').text();
	var dt = article.find('header span').text();
	$('#replyAnswerLayer .targetContent p').html(comment);
	$('#replyAnswerLayer header b').html(mname);
	$('#replyAnswerLayer header span').html(dt);

	$('#replyAnswerLayer').show();
});

$(document).on('submit', '#repAnswerForm', function(e){
	e.preventDefault();
	common.ajaxForm(this, function(data){
		getReplyList();
	});
});
