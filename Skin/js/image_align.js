/* -----------------------------------------------------
 *
 *    이미지 정렬
 *    .imgAlign img
 *	   data-opt Attribute
 *    center, horizontal : 가로 중앙 정렬
 *    middle, vertical : 세로 중앙 정렬
 *    both: 가로, 세로 중앙 정렬
 *    contain : 이미지 사이즈 조절
 *
 ----------------------------------------------------- */
(function($){
	window.ImageAlign = new function() {
		var _this = this;
		this.enabled = false;

		this.alignAll = function(){
			if(!this.enabled) return;
			$('.imgAlign').each(function () {
				_this.align.call(this);
			});
		};

		this.align = function(){
			if(!_this.enabled) return;

			function imagePosition(layer, img, opt){
				if(opt.indexOf('cover') >= 0 ){
					if(img.outerHeight() / img.outerWidth() < layer.outerHeight() / layer.outerWidth()){
						var h = layer.outerHeight();
						img.css({
							'width' : 'auto',
							'height' : h + 'px'
						});
					}else{
						var w = layer.outerWidth();
						img.css({
							'width' : w+'px',
							'height' : 'auto'
						});
					}
				}
				else{
					if(opt.indexOf('contain') >= 0 ){
						if(img.outerHeight() / img.outerWidth() > layer.outerHeight() / layer.outerWidth()){
							var h = layer.outerHeight();
							img.css({
								'width' : 'auto',
								'height' : h + 'px'
							});
						}else{
							var w = layer.outerWidth();
							img.css({
								'width' : w+'px',
								'height' : 'auto'
							});
						}
					}
				}

				if (opt.indexOf('center') >= 0 || opt.indexOf('horizontal') >= 0 || opt.indexOf('both') >= 0) {
					img.css({
						'margin-left': ((layer.width() - img.width()) / 2) + 'px'
					});
				}
				if (opt.indexOf('middle') >= 0 || opt.indexOf('vertical') >= 0 || opt.indexOf('both') >= 0) {
					img.css({
						'margin-top': ((layer.height() - img.height()) / 2) + 'px'
					});
				}
			}

			var layer = $(this);
			var opt = layer.attr('data-opt');
			var img = layer.find('img');

			if (layer.attr('data-load') === 'y') {
				imagePosition(layer, img, opt);
				return;
			}

			var tmpImg = new Image();
			layer.attr('data-load', 'y');
			tmpImg.onload = function () {
				imagePosition(layer, img, opt);
			};

			tmpImg.src = img.attr('src');
		};

		this.run = function(){
			function DomImgInserted(e){
				if($(e.target).hasClass('imgAlign')) window.ImageAlign.align.call(e.target);
				if(e.target.tagName === 'IMG' && $(e.target).parent().hasClass('imgAlign')) window.ImageAlign.align.call($(e.target).parent());

				$(e.target).find('.imgAlign').each(function(){
					if($(this).hasClass('imgAlign')) window.ImageAlign.align.call(this);
				});
			}

			document.body.addEventListener('DOMNodeInserted', DomImgInserted);
			document.body.addEventListener('DomNodeInsertedIntoDocument', DomImgInserted);

			window.ImageAlign.alignAll();
		};
	};
}($));