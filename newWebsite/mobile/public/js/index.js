/*
* @Author: 9005751
* @Date:   2016-06-20 17:21:12
* @Last Modified by:   9005751
* @Last Modified time: 2016-06-20 18:10:04
*/

$(function(){

// 首页轮播,生成轮播小圆点
$(".banner").each(function(){
	for(var i = 0; i < $(this).find("ul li").length; i++){
		if(i == 0){
    		$(this).parent().siblings('.banner-num').find("ol").append('<li class="cur"></li>');
		}else{
    		$(this).parent().siblings('.banner-num').find("ol").append('<li></li>');
		}
	}
});
$(".show-banner").each(function(){
    for(var i = 0; i < $(this).find("ol li").length; i++){
        if(i == 0){
            $(this).children('.show-num').append('<span class="on"></span>');
        }else{
            $(this).children('.show-num').append('<span></span>');
        }
    }
});
// 实例化Swipe
new Swipe(document.getElementById("banner"), {
  	speed: 400,
  	auto: 2800,
  	callback: function(index, elem) {
  		$(".banner-num ol li").removeClass("cur").eq(index).addClass("cur");
  	}
});

new Swipe(document.getElementById("show-banner"), {
    speed: 400,
    auto: 2800,
    callback: function(index, elem) {
        $(".show-pic ol li").removeClass("cur").eq(index).addClass("cur");
        $(".show-num span").removeClass("on").eq(index).addClass("on");
    }
});


//品牌动态
    function pullUpActions () {
        var el, lis, i, len, pageNumber;
        //var query = genQuery("?");
        el = document.querySelector('#lis_wrap ul');
        //len = $('#lis_wrap ul').find("li").length;
        pageNumber = 3;
        var id = typeof info_id!='undefined' ? info_id : 0;
        var xhr = $.ajax({
            url:"/index/dynamics.json?pageNum="+pageNumber,
            type:"POST",
            dataType:"JSON",
            async:false
        });
        xhr.fail(function(x,s){
        });
        xhr.done(function(d){
            d = d.data;
            if(d.length > 0){
                $.each(d,function(k,v){
                    lis = document.createElement('li');
                    var short_title = v.title;
                    if(short_title.length > 23){
                        short_title = short_title.substr(0,23)+'...';
                    }
                    var no_img = false;
                    var v_time = v.create_time.substring(0,10);
                    if(v.thumb_one == '' && v.thumb_two == '' && v.thumb_three == ''){
                        no_img = true;
                    }
                    lis.innerHTML ='<a href="/BrandDynamics/detail/id/'+v.id+'"'+'><p>'+'<b>'+ (k+1) + '.' +'</b>'+'<i class="font icon-new-dt"></i>'+short_title+'</p><span>'+v_time+'</span></a>';
                    if(el != null){
                        el.appendChild(lis, el.childNodes[0]);
                    }
                });
            }else{
                return false;
            }
        });
    }
    pullUpActions();






})