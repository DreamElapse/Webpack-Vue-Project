/**
 * Created by 9005751 on 2016/7/18.
 */
$(function(){
//页面初始化的时候页面最先加载
function newDetailInit() {

    $.ajax({
        type: "POST",
        url: '/Goods/detail/gid/'+currentId,
        async:false,
        dataType: "json",
        success: function(d){
            d = d.data;
            $('.pro-tit').text(d.goods_name);
            $('#shop-price').text(d.shop_price);
            $('#market-price').text(d.market_price);
            $('#core-efficacy').text(d.attr_value)
            document.title = d.goods_name;
            var imgs = d.good_imgs;
            for(i = 0; i < imgs.length;i++){
                if(i == 5){
                    break;
                }
                el = document.querySelector('#slide ul');
                li = document.createElement('li');
                li.setAttribute('data-ul-child',"child");
                //li.innerHTML = '<a href="" class="product-img" ><img src="' + imgs[i].thumb_url + '" alt="" ></a>';
                li.innerHTML = '<a href="" class="product-img" ><img src="public/images/detail/slide_01.jpg" alt="" ></a>';
                el.appendChild(li, el.childNodes[0]);
            }


        }
    })

    slide("#slide").init({  //产品轮播
        startIndex: 0,
        number: true,
        laseMoveFn: jump,
        lastImgSlider: sliderJump,
        preDef: "lnr",
        location: true,
        autoPlay: false,
        autoHeight: true,
        mpingEvent: "MProductdetail_SlideFocusPic",
        wareId: currentId
    });

    if ($("#isShowDetail").val() != "false" && currentId != "") {  //商品首页的评论
        commentListInit({
            containerID: "comment-list",
            url: '/Goods/detail/gid/'+currentId,
            maxCount: 3,
            fontSize: 14,
            filters: {}
        })
    } else {
        $("#comment-rank").parent().text("");
    }



}
newDetailInit(); //详情页初始化，价格，评论首先加载
//产品轮播图切换轮播
function jump(a) {
    var c = document.getElementById("tittup");
    var d = document.getElementById("slide");
    var b = d.offsetWidth;
    if (a < -b / 5) {
        c.children[0].classList.add("rotate");
        c.children[1].innerHTML = "\u91ca\u653e\u67e5\u770b\u8be6\u60c5"
    }
    if (a > -b / 5) {
        c.children[0].classList.remove("rotate");
        c.children[1].innerHTML = "\u6ed1\u52a8\u67e5\u770b\u8be6\u60c5"
    }
    c.style.WebkitTransform = "translateX(" + a + "px)";
    c.style.transform = "translateX(" + a + "px)"
}

function sliderJump() { //当当前页面是最后一页是触发
    detaildlider.sliderJump()
}


//评论公共函数
function commentListInit(p) {
    var f = {
        containerID: "comment-list",
        maxCount: 5,
        fontSize: 14,
        spaceWidth: 60,
        url: "",
        filters: {}
    };
    var o = $.extend(f, p);
    var e = document.getElementById(o.containerID);
    var itemDiv = "";
    var supportsOrientationChange = "onorientationchange" in window;
    var resize = supportsOrientationChange ? "orientationchange": "resize";
    var oUserAgent = window.navigator.userAgent;
    var utils = {
        measureWidth: function() {
            return document.documentElement.clientWidth
        },
        adjustWidth: function() {
            var screenW = utils.measureWidth();
            var ww = screenW - 20 - 65 * 4 - 13 * 3;
            var adjustW = 65 + ww / 4;
            if (adjustW > 120) {
                adjustW = 120
            }
            $(".item-img").width(adjustW).height(adjustW);
            $(".item-img").find("img").each(function(i) {
                var img = $(this);
                var realWidth;
                var realHeight;
                var imageNew = new Image();
                imageNew.src = $(img).attr("src");
                imageNew.onload = function() {
                    realWidth = this.width;
                    realHeight = this.height;
                    if (realHeight > realWidth) {
                        img.width(adjustW);
                        img.css("marginTop", (adjustW - img.height()) / 2 + "px")
                    } else {
                        img.height(adjustW);
                        img.css("marginLeft", (adjustW - img.width()) / 2 + "px")  //设置评论图片margin-left
                    }
                }
            })
        },
        addHtml: function(obj) {  //评论拼接
            itemDiv += '<li><div class="comment-item">'
            itemDiv += '<div class="item-left"><span class="user">';
            itemDiv += '<img src="../public/images/detail/person.png" alt=""/>'   //图片路径
            itemDiv += '</span></div>'
            itemDiv += '<div class="item-right"><div class="uaer-name">  '
            itemDiv += obj.user_name;
            itemDiv += '<div class="comment-date">';
            itemDiv += obj.add_time.split(" ")[0];
            itemDiv += '</div></div>';
            itemDiv += '<div class="comment-container"><p>'
            itemDiv += obj.content;
            itemDiv += '</p></div></div>'
            if(obj.imgs != undefined && obj.imgs.length >0 ){

                itemDiv += '<div class="comment-img">'
                for(var i = 0; i < obj.imgs.length;i++ ){
                    if(i == 3){
                        break
                    }
                    itemDiv += '<span class="item-img"><img src=" ';
                    itemDiv += obj.imgs[i]
                    itemDiv += '"alt=""/></span>'

                }
            }
            itemDiv += '</div></li>'
        }

    }
    var initList = function() {
        loadList()
    };
    var drawList = function(obj) {
        var limit = o.maxCount;
        var size = obj.length;
        if (size == 0) {
            $("#comment-rank").text("");  //好评率
            document.getElementById("showDetail").style.display = "none";
            return
        }

        var len = size <= limit ? size: limit;
        for (var i = 0; i < len; i++) {
            utils.addHtml(obj[i])
        }

        e.innerHTML = itemDiv;
        utils.adjustWidth()
    };

    var loadList = function() {
        jQuery.ajax({
            url: '/Goods/detail/gid/'+currentId,
            data: {
                wareId: currentId
            },
            success: function(d) {
                try {
                    window.addEventListener(resize,
                        function() {
                            if (oUserAgent.indexOf("MQQBrowser") != -1 || oUserAgent.indexOf("baidubrowser") != -1 || (oUserAgent.indexOf("Chrome") != -1 && oUserAgent.indexOf("Android") != -1)) {
                                clearTimeout(imgAdjustTimer);
                                var imgAdjustTimer = setTimeout(function() {
                                        utils.adjustWidth()
                                    },
                                    200)
                            } else {
                                utils.adjustWidth()
                            }
                        },
                        false);

                     var obj = d.data;

                    var goodCnt = parseInt(obj.good_comments.length);
                    if (goodCnt > 0) {
                        if (goodCnt > 0) {
                            $("#comment-rank").text(Math.round(goodCnt * 100 / goodCnt) + "%");   //好评率控制
                        }
                    } else {
                        $("#comment-rank").text("");
                    }

                    drawList(obj.good_comments)

                } catch(e) {
                    $("#showDetail").attr("style", "display:none;")
                }
            },
            error: function(d) {

                $("#showDetail").attr("style", "display:none;")
            }
        });
        utils.adjustWidth()
    }
    return initList();
}


})