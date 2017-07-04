//
//
//

//to the top
$(function() {
	//var showFlug = false;
	var topBtn = $('.tothetop');
	//最初はボタン位置をページ外にする
	topBtn.css('bottom', '-100px');//ボタンをページ外に配置
	var showFlug = false;
	$(window).scroll(function () {
		if ($(this).scrollTop() > 500) {//ボタンが表示されるスクロール量
			if (showFlug == false) {
				showFlug = true;
				topBtn.stop().animate({'bottom' : '20px'}, 200);
			}
		} else {
			if (showFlug) {
				showFlug = false;
				topBtn.stop().animate({'bottom' : '-100px'}, 200);
			}
		}
	});
	topBtn.click(function () {
		$('body,html').animate({
			scrollTop: 0
		}, 500);//戻りスクロール速度。大きいと遅い。
		return false;
	});
});

