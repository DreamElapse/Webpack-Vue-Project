function DetailSlider(a) {  //DetailSlider构造函数
    this.p = a;
    this.oDiv = document.querySelector(a);  //容器
    this.oUl = document.querySelector(".container-slider");  //外层容器
    this.aLi = document.querySelectorAll(".slider-item"); //外层li
    this.oMCommonHeader = document.getElementById("m_common_header");  //新版商详头部
    this.aTabLi = document.querySelectorAll(".header-tab-item");     //产品详情评价导航li
    this.aTabTitle = document.querySelectorAll(".header-tab-title");  //产品详情评价导航li中的P
    this.oBox = {     //运动参数配置
        startX: 0,
        startY: 0,
        finalX: 0,
        finalY: 0,
        endX: 0,
        endY: 0,
        sliderX: 0,
        moveX: 0,
        tabIndex: 0,
        numTime: 0,
        isMove: false,
        startTime: 0,
        moveTime: 0
    }
}
DetailSlider.prototype = {  //DetailSlider原型链函数
    init: function(c) {
        var c = c || {},
            g = this.aLi,
            b = this.oDiv,
            f = this.oBox,
            d =this;
        this.setHeightTimer = null;
        this.clearHeightTimer = null;
        this.aLiLen = g.length;  //li的个数
        this.oDivWidth = b.offsetWidth;  //li的父级div宽度
        this.oUserAgent = window.navigator.userAgent;  //判断浏览器类型
        this.defaults = { //默认
            loadInfoFn: false
            //loadAssessFn: false
            //nowInfo: false
        };
        DetailSlider.extend(this.defaults,c);
        this.liInit();
        this.bind();
        clearInterval(this.setHeightTimer);
        this.setHeightTimer = setInterval(function(){
            d.setHeight()
        },800);
        clearTimeout(this.clearHeightTimer);
        this.clearHeightTimer =setTimeout(function(){
            clearInterval(d.setHeightTimer)
        },10000);
        this.setHoldTopHeight()
    },
    liInit: function(){
        var f = this.aLi,
            c = this.oDiv,
            b = this.oUl,
            d = this.aLiLen;
        clearTimeout(a);
        var a =setTimeout(function(){
            var j = document.documentElement.clientWidth; //获取窗口宽度
            for (var h = 0;h < d; h++){
                f[h].style.width = j + "px"; //外层li的宽度等于网页可见域宽
            }
            this.aLiWidth = f[0].offsetWidth; //网页可见域宽，包括边线的宽
            var g = this.aLiWidth * d;
            b.style.width = g + "px";
            c.style.width = j + "px";
        },300)
    },
    bind:function(){
        var c = this.oDiv,//容器
            f = this.oUserAgent, //判断浏览器类型
            b = "onorientationchange" in window,
            a = b ? "orientationchange": "resize";  //判断出手机是处在横屏还是竖屏状态
        if (f.indexOf("iPhone") != -1) {
            c.addEventListener("touchstart", this, false) //添加touchstart时间
        }
        window.addEventListener(a, this, false);  //参数为false,在事件冒泡时执行
        this.clickJump();  //按钮的跳转事件
        this.tabClick();
    },
    setHeight:function(){ //初始化高度
        var a = this.aLi,
            h = this.oUl,
            d = this.oBox;
        var g = document.documentElement.clientHeight;
        var e = g;
//        if(e > g){
//            a[d.tabIndex].style.height = e + "px";
//            h.style.height = e + "px";
//        }else{
//            a[d.tabIndex].style.height = g + "px";
//            h.style.height = g + "px";
//        }
        if (a[d.tabIndex].offsetHeight > e) {  //外层大容器的高度
            h.style.height = a[d.tabIndex].offsetHeight + "px"
        } else {
            h.style.height = e + "px"
        }

    },
    setHoldTopHeight: function(){ //导航事件

    },
    handleEvent:function(a){ //H5触摸事件
        switch (a.type) {
            case "touchstart":
                this.startHandle(a);
                break;
            case "touchmove":
                this.moveHandle(a);
                break;
            case "touchend":
                this.endHandle(a);
                break;
            case "orientationchange":
                this.orientationchangeHandler(a);
                break;
            case "resize":
                this.orientationchangeHandler(a);
                break
        }
    },
    startHandle:function(d){ //li就绪时
        d.stopPropagation();
        var c = d.targetTouches[0],
            a = this.oUl, //外层大容器
            b = this.oDiv,//容器
            f = this.oBox;
        f.numTime = 0;
        f.startX = c.pageX;
        f.startY = c.pageY;
        f.isMove = false;
        f.startTime = new Date().getTime();
        DetailSlider.setStyle(a, {
            WebkitTransition: "",
            transition: ""
        });
        b.addEventListener("touchmove", this, false);
        b.addEventListener("touchend", this, false)
    },
    moveHandle: function (g) {
        g.stopPropagation();
        var f = g.targetTouches[0],
            i = this.oBox,
            b = this.oUl;
        i.moveTime = new Date().getTime();
        if ((i.moveTime - i.startTime) >= 50) {
            if (i.numTime == 0) {
                i.finalX = f.pageX;
                i.finalY = f.pageY;
                var c = DetailSlider.getAngle(i.startX, i.finalX, i.startY, i.finalY);
                var a = DetailSlider.getSpeed(i.startX, i.finalX, i.startY, i.finalY, 50)
            }
            i.numTime++;
            if (a >= 5) {
                if ((c >= 0 && c <= 10) || (c >= 170 && c <= 190) || (c >= 350 && c <= 360)) {
                    i.isMove = true
                }
            } else {
                if ((c >= 0 && c <= 20) || (c >= 160 && c <= 200) || (c >= 340 && c <= 360)) {
                    i.isMove = true
                }
            }
        }
        var d = document.documentElement.clientWidth,  //窗口可见域的宽度
            h = this.aLiLen;  //外层li轮播个数
        if (i.isMove) {
            g.preventDefault();
            i.moveX = i.sliderX + (f.pageX - i.startX);
            if (i.moveX > 0) {
                i.moveX = 0
            }
            if (i.moveX < -(d * (h - 1))) {
                i.moveX = -(d * (h - 1))
            }
            DetailSlider.moving(b, i.moveX)
        }

    },
    endHandle: function(l){
        l.stopPropagation();
        var n = l.changedTouches[0],
            k = this.oBox, //运动参数配置
            b = this.oDiv,  //容器
            r = this.aTabTitle, //产品详情评价导航li中的p
            o = this.oDivWidth, //容器宽度
            c = this.oSiftTab, //普通商品详情分支头部
            g = this.oTabs,     //商品评价分支头部
            p = this.oUl,
            d = this.aLiLen, //外层li个数
            q = this.oTryme,  //免费领188元大红包
            h = this.oUserAgent, //判断浏览器类型
            m = this.oMCommonHeader, //新版商详头部
            a = document.documentElement.clientWidth,
            j = this;
        k.numTime = 0;
        k.endX = n.pageX;
        k.endY = n.pageY;
        if ((k.isMove) && (k.endX - k.startX < 0)) {
            if (Math.abs(k.endX - k.startX) > o / 3) {
                k.tabIndex++;
                if (j.defaults.loadInfoFn && typeof j.defaults.loadInfoFn == "function") {
                    j.defaults.loadInfoFn(k.tabIndex, 1)
                }
                if (j.defaults.loadAssessFn && typeof j.defaults.loadAssessFn == "function") {
                    j.defaults.loadAssessFn(k.tabIndex, 2)
                }
                k.sliderX = k.sliderX - a;
                if (k.sliderX < -(a * (d - 1))) {
                    k.tabIndex = d - 1;
                    k.sliderX = -(a * (d - 1))
                }
                DetailSlider.sliding(p, k.sliderX);
                DetailSlider.headSwitch(c, k.tabIndex, 1);
                DetailSlider.headSwitch(g, k.tabIndex, 2);
                DetailSlider.visible(c, k.tabIndex, 1);
                if (k.tabIndex >= d) {
                    k.tabIndex = d - 1
                } else {
                    document.documentElement.scrollTop = 0;
                    document.body.scrollTop = 0;
                    if (q && h.indexOf("Html5Plus") < 0) {
                        if (!isPanelClose()) {
                            q.style.display = "block"
                        }
                    }
                }
                j.HeightTimer();
                j.setHoldTopHeight();
                j.slideMping(k.tabIndex, "商品")
            } else {
                DetailSlider.sliding(p, k.sliderX)
            }
        } else {
            if ((k.isMove) && (k.endX - k.startX > 0)) {
                if (Math.abs(k.endX - k.startX) > o / 3) {
                    k.tabIndex--;
                    if (j.defaults.loadInfoFn && typeof j.defaults.loadInfoFn == "function") {
                        j.defaults.loadInfoFn(k.tabIndex, 1)
                    }
                    if (j.defaults.loadAssessFn && typeof j.defaults.loadAssessFn == "function") {
                        j.defaults.loadAssessFn(k.tabIndex, 2)
                    }
                    k.sliderX = k.sliderX + a;
                    if (k.sliderX > 0) {
                        k.tabIndex = 0;
                        k.sliderX = 0
                    }
                    DetailSlider.sliding(p, k.sliderX);
                    DetailSlider.headSwitch(c, k.tabIndex, 1);
                    DetailSlider.headSwitch(g, k.tabIndex, 2);
                    DetailSlider.visible(c, k.tabIndex, 1);
                    if (k.tabIndex <= 0) {
                        k.tabIndex = 0
                    } else {
                        document.documentElement.scrollTop = 0;
                        document.body.scrollTop = 0;
                        if (q && h.indexOf("Html5Plus") < 0) {
                            if (!isPanelClose()) {
                                q.style.display = "block"
                            }
                        }
                    }
                    j.HeightTimer();
                    j.setHoldTopHeight();
                    j.slideMping(k.tabIndex, "评价")
                } else {
                    DetailSlider.sliding(p, k.sliderX)
                }
            }
        }
        if (m != null) {
            for (var f = 0; f < r.length; f++) {
                DetailSlider.removeClass(r[f], "tab-selected")
            }
            DetailSlider.addClass(r[k.tabIndex], "tab-selected")
        }
        b.removeEventListener("touchmove", this, false);
        b.removeEventListener("touchend", this, false);
        if (k.tabIndex == 2) {
            window.removeEventListener("scroll", this, false)
        } else {
            window.addEventListener("scroll", this, false)
        }

    },
    tabClick: function(){
        var a = this.aTabLi, //四个导航li
            d = this.oBox, //参数配置
            c = this;
        for (var b = 0; b < a.length; b++) { (function(e) {
            a[e].onclick = function() {
                d.tabIndex = e;
//                if (e == 1) {
//                    $("#indexToTop").attr("report-eventid", "MProductdetail_DetailBackToTop")
//                } else {
//                    if (e == 2) {
//                        $("#indexToTop").attr("report-eventid", "MProductdetail_CommentBackToTop")
//                    } else {
//                        $("#indexToTop").attr("report-eventid", "MProductdetail_BackToTop")
//                    }
//                }
                if (c.defaults.loadInfoFn && typeof c.defaults.loadInfoFn == "function") {
                    c.defaults.loadInfoFn(d.tabIndex, 1)
                }
                if (c.defaults.loadAssessFn && typeof c.defaults.loadAssessFn == "function") {
                    c.defaults.loadAssessFn(d.tabIndex, 2, 0)
                }
                c.tabSwitch(d.tabIndex)
            }
        })(b)
        }
    },
    tabSwitch: function(i) {  //导航按钮切换，商品，详情，评价
        var e = this.aTabLi, //产品详情评价导航li
            h = this.oBox, //运动参数配置
            b = this.oSiftTab, //普通商品详情分支头部
            m = this.aTabTitle, //产品详情评价导航li中的P
            d = this.oTabs,     //商品评价分支头部
            l = this.oUl,
            k = this.oMCommonHeader, //新版商详头部
            a = document.documentElement.clientWidth,
            f = this.oUserAgent,  //判断浏览器类型
            g = this;
        h.tabIndex = i;
        if (k != null) {
            for (var c = 0; c < e.length; c++) {
                DetailSlider.removeClass(m[c], "tab-selected")
            }
            DetailSlider.addClass(m[h.tabIndex], "tab-selected")
        }
        h.sliderX = -(h.tabIndex * a);
        DetailSlider.sliding(l, h.sliderX); //滑动距离
        DetailSlider.headSwitch(b, h.tabIndex, 1); //详情时，商品介绍，参数等出现
        DetailSlider.visible(b, h.tabIndex, 1);
        DetailSlider.headSwitch(d, h.tabIndex, 2);  //评论时，评论小导航出现
        this.HeightTimer();
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;
//        if (n && f.indexOf("Html5Plus") < 0) {
//            if (!isPanelClose()) {
//                n.style.display = "block"
//            }
//        }
        this.setHoldTopHeight();
        if (h.tabIndex == 2) {
            window.removeEventListener("scroll", this, false)
        } else {
            window.addEventListener("scroll", this, false)
        }
    },
    sliderJump: function() { //
        var b = this.oBox,  //运动参数配置
            a = this;
        b.tabIndex++;
        if (a.defaults.loadInfoFn && typeof a.defaults.loadInfoFn == "function") {
            a.defaults.loadInfoFn(b.tabIndex, 1)
        }
        this.tabSwitch(b.tabIndex)
    },
    clickJump: function(){
        var b = this.oBox, //参数配置
            a = this;
        $(".pro-data").on("click",  //查看详情按钮
            function() {
                //var c = $(this).find(".comment-img-container");
                b.tabIndex = 1;
                a.tabSwitch(b.tabIndex);
                //$("#indexToTop").attr("report-eventid", "MProductdetail_DetailBackToTop");  //返回顶部按钮隐藏
                // 产品详情信息加载
//                productInfoLoad({
//                    containerID: "goodDetail",
//                    wareId: $("#currentWareId").val(),
//                    url: "/ware/detail.json?wareId=" + $("#currentWareId").val(),
//                    cbfn: function() {
//                        scale.init()
//                    }
//                })
            })

        $('.pro-favo').on('click',   //本期优惠信息查看
            function(){
                b.tabIndex = 3;
                a.tabSwitch(b.tabIndex);
                // 优惠页面载入
        });

        $('#go-comment').on('click',
            function(){
                b.tabIndex = 2;
                a.tabSwitch(b.tabIndex);
                //评论信息载入
            });
        $('.pro-param dl dt').on('click',function(){
            $(this).closest('.pro-param').toggleClass('on');
        })
    },
    orientationchangeHandler: function() { //窗口大小改变时，300秒自动适应当前窗口
        var a = this.oUl,
            c = this.oBox;  //运动参数配置
        this.liInit();
        clearTimeout(b);
        var b = setTimeout(function() {
                var d = document.documentElement.clientWidth;
                c.sliderX = -(d * c.tabIndex);
                DetailSlider.sliding(a, c.sliderX)
            },
            300);
        this.HeightTimer()
    },
    HeightTimer: function() {  //高度初始化
        var b = this.oBox,
            a = this;
        if (b.tabIndex == 1) {
            clearInterval(this.setHeightTimer);  //关闭定时器
            this.setHeightTimer = setInterval(function() { //设置定时器
                    a.setHeight()
                },
                1000);
            clearTimeout(this.clearHeightTimer);//关闭定时器
            this.clearHeightTimer = setTimeout(function() {
                    clearInterval(a.setHeightTimer)   //设置定时器，只执行一次
                },
                60000)
        } else {
            clearInterval(this.setHeightTimer);
            this.setHeightTimer = setInterval(function() {
                    a.setHeight()
                },
                800);
            clearTimeout(this.clearHeightTimer);
            this.clearHeightTimer = setTimeout(function() {
                    clearInterval(a.setHeightTimer)
                },
                2500)
        }
    },
    slideMping: function(b, a) {
        if (b == 0) {  //当导航为商品时
            $("#indexToTop").attr("report-eventid", "MProductdetail_BackToTop");
            //pingClickWithLevel("MProductdetail_ProductTabSlide", "", "", $("#currentWareId").val(), "5")
        } else {
            if (b == 1) {//当导航为详情时
                $("#indexToTop").attr("report-eventid", "MProductdetail_DetailBackToTop");
                //pingClickWithLevel("MProductdetail_DetailTabSlide", a, "", $("#currentWareId").val(), "5")
            } else {
                if (b == 2) { //当导航切换到评论时
                    $("#indexToTop").attr("report-eventid", "MProductdetail_CommentBackToTop");
                    //pingClickWithLevel("MProductdetail_CommentTabSlide", "", "", $("#currentWareId").val(), "5")
                }
            }
        }
    }



}




DetailSlider.extend = function(b,a){
    for (name in a){
        if(a[name] !== undefined){
            b[name] = a[name]
        }
    }
};
DetailSlider.extend(DetailSlider,{
    setStyle: function(b, a) {
        for (name in a) {
            b.style[name] = a[name]
        }
    },
    getStyle: function(b, a) {
        return (b.currentStyle || getComputedStyle(b, false))[a]
    },
    hasClass: function(b, a) {
        return b.className.match(new RegExp("(\\s|^)" + a + "(\\s|$)"))
    },
    addClass: function(b, a) {
        if (!DetailSlider.hasClass(b, a)) {
            b.className += " " + a
        }
    },
    removeClass: function(c, a) {
        if (DetailSlider.hasClass(c, a)) {
            var b = new RegExp("(\\s|^)" + a + "(\\s|$)");
            c.className = c.className.replace(b, " ")
        }
    },
    sliding: function(b, a) {
        DetailSlider.setStyle(b, {
            WebkitTransition: "500ms ease",
            transition: "500ms ease"
        });
        DetailSlider.setStyle(b, {
            WebkitTransform: "translate3d(" + a + "px,0,0)",
            transform: "translate3d(" + a + "px,0,0)"
        })
    },
    moving: function(b, a) {
        DetailSlider.setStyle(b, {
            transform: "translate3d(" + a + "px,0,0)",
            WebkitTransform: "translate3d(" + a + "px,0,0)"
        })
    },
    headSwitch: function(c, a, b) {  //三大导航中的小导航，结合visible使用
        if (c != null) {
            if (a == b) {
                c.style.display = "block"
            } else {
                c.style.display = "none"
            }
        }
    },
    visible: function(c, a, b) { //三大导航中的小导航，结合headSwitch使用
        if (c != null) {
            if (a == b) {
                c.style.visibility = "visible"
            } else {
                c.style.visibility = "hidden"
            }
        }
    },
    getAngle: function(c, b, e, d) {
        var a = Math.abs(c - b);
        var h = Math.abs(e - d);
        var g = Math.sqrt(a * a + h * h);
        var f = Math.round((Math.asin(h / g) / Math.PI * 180));
        if (b >= c && d <= e) {
            f = f
        } else {
            if (b <= c && d <= e) {
                f = 180 - f
            } else {
                if (b <= c && d >= e) {
                    f = 180 + f
                } else {
                    if (b >= c && d >= e) {
                        f = 360 - f
                    }
                }
            }
        }
        return f
    },
    getSpeed: function(b, a, h, g, d) {
        var i = Math.abs(b - a);
        var f = Math.abs(h - g);
        var e = Math.sqrt(i * i + f * f);
        var c = e / d;
        return c
    }


});




