/**
 * Created by 9005751 on 2016/7/8.
 */
$(function(){
    var pageNum = 1;
    var myScroll, pullUpEl, pullDownOffset, pullUpOffset;
    $('.hd-item span').on('click',function(){
        var $self = $('.combo-box').children().eq($(this).index());
        $(this).addClass('cur');
        $self.addClass('cur').siblings().removeClass('cur');
//            if($(this).hasClass('cur')){
//                $self.addClass('cur').siblings().removeClass('cur');
//                $(this).siblings().removeClass('cur');
//            }else{
//                $self.removeClass('cur')
//            }
        return false;
    })

    $('.combo-box a').click(function(e){
        e.stopPropagation();
        pageNum = 1;
        var v = $(this).text();
        var d = $(this).closest("div").index();
        //$(this).addClass('on').siblings().removeClass('on');
        $(this).closest('div').find('a').removeClass('on');
        $(this).addClass('on');
        $(this).parent().parent().removeClass('cur')
        $('.hd-item em').eq(d).text(v);
        pullUpAction();
        $('#wrapper ul').empty();
        myScroll.scrollTo(0, 0, 100);
    })
    function pullUpAction(){
        var cjhot = $('.cj-hot .on').attr('data');
        var cjprice = $('.cj-price .on').attr('data');
        var cjall = $('.cj-all .on').attr('data');
        url = 'http://3g.chinaskin.cn/Goods/lists' + '/package/' + cjhot + '/price/' + cjprice  + '/keyword/'+ cjall ;
        $.post(url+'/page/'+(parseInt(pageNum)), '', function (data) {
            var data = data.data;
            var el, len;
            el = document.querySelector('#wrapper ul');
            len = $('#wrapper ul').find("li").length;
            //$('#wrapper ul').empty();
            if (data.length > 0) {
                $.each(data, function (k, v) {
                    li = document.createElement('li');
                    li.innerHTML = '<a href="/detail.html?gd='+ v.goods_id +'" class="product-img" ><img src="' + v.goods_thumb + '" alt="" ></a><div class="product-infor"><h3>' + v.goods_name + '</h3><p>' + v.click_count + '</p> <div class="cj-cart"> <span class="price">¥' + v.shop_price + '</span> <a href="/detail.html?gd='+ v.goods_id +'"> <i class="font icon-cart"></i>加入购物车</a> </div> </div>';
                    el.appendChild(li, el.childNodes[0]);
                });
            } else {
                $("#pullUp").html('已经是最后一页');
                return false;
            }
            pageNum++;

        }, 'json');

        myScroll.refresh();

    }
    window.addEventListener("load", function(){
        pullUpEl = document.getElementById('pullUp');
        pullUpOffset = pullUpEl.offsetHeight;

        myScroll = new iScroll("wrapper", {
            useTransition: true,
            topOffset: pullDownOffset,
            checkDOMChanges:true,
            onRefresh: function () {
                if(document.querySelector("#wrapper").clientHeight > document.querySelector("#wrapper ul").clientHeight){
                    document.getElementById("pullUp").innerHTML = "";
                }
                if (pullUpEl.className.match('loading')) {
                    pullUpEl.className = '';
                    pullUpEl.innerText = '向上滑动加载更多...';
                }
            },
            onScrollMove: function () {
                if (this.y < (this.maxScrollY - 5) && !pullUpEl.className.match('flip')) {
                    pullUpEl.className = 'flip';
                    pullUpEl.innerHTML = '松开刷新...';
                    this.maxScrollY = this.maxScrollY;
                } else if (this.y > (this.maxScrollY + 5) && pullUpEl.className.match('flip')) {
                    pullUpEl.className = '';
                    pullUpEl.innerHTML = '向上滑动加载更多...';
                    this.maxScrollY = pullUpOffset;
                }
            },
            onScrollEnd: function () {
                if (pullUpEl.className.match('flip')) {
                    pullUpEl.className = 'loading';
                    pullUpEl.innerHTML = '正在加载...';
                    pullUpAction();
                }
            }
        });
        pullUpAction();
    }, false);

})