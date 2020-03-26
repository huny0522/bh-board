/* -----------------------------------------------------
 *
 *    스크롤 영역 이미지 스크롤 바
 *
 *    element 필수 태그클래스
 *    .scrollContents
 *    .scrollBar > .scrollBtn
 *
 ----------------------------------------------------- */
function ScrollAreaInit(selector){
	var bar = $(selector).find('.scrollBar').eq(0);
	var btn = $(selector).find('.scrollBtn').eq(0);
	var scrollContents = $(selector).find('.scrollContents').eq(0);
	var maxHeight = bar.height() - btn.height();

	scrollContents.scroll(function(){
		var sh = scrollContents[0].scrollHeight - scrollContents.height();
		var sy = scrollContents.scrollTop() / sh;
		btn.css({top : (maxHeight * sy) + 'px'});
	});

	btn.drag(function(e, startPos, endPos){
		// scroll begin
		var y = endPos.pageY - (bar.offset().top - $(window).scrollTop());
		y -= btn.height() / 2;
		if(y < 0) y = 0;
		else if(y > maxHeight) y = maxHeight;
		btn.css({top : y + 'px'});
		scrollConetntsAct(y);
	}, function(e, startPos, endPos){
		// scroll end
	});

	function scrollConetntsAct(y){
		var sh = scrollContents[0].scrollHeight - scrollContents.height();
		var sy = sh * (y / maxHeight);
		if(!sh) btn.css({top : 0});
		scrollContents.scrollTop(sy);
	}
}
