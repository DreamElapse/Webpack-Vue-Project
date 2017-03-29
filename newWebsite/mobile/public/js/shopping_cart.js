$(function(){
	
	$('.goods-tab-cont').each(function(){
		var $self = $(this);
		var count = $self.find('.goods-other-li').length;
		new Swipe($self[0], {
		  	speed: 800,
		  	auto: 4000,
		  	continuous: false,
		  	callback: function(index, elem) {
		  		$(elem).closest('.goods-other-list').siblings('ol').children().removeClass("on").eq(index).addClass("on");
		  	}
		});
		var ol = '<ol>'
		if(count >= 2){
			for(var i = 0; i < count; i++){
				if(i == 0){
					ol += '<li class="on"></li>';
					continue;
				}
				ol += '<li></li>';
			}
			$self.append(ol + '</ol>');
		}
	});
	
	$('.goods-tab-hd a').on('click', function(){
		var $self = $(this);
		$self.addClass('on').siblings().removeClass('on');
		$self.closest('.goods-tab').find('.goods-tab-cont').removeClass('on').hide().eq($self.index()).addClass('on').show();
	});
	
	$('.goods-tab-cont').not('.on').hide();
	
});