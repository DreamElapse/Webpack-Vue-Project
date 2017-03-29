/**
 * Created by 9005751 on 2016/7/11.
 */

//  产品详情页数量加减
//产品数量++
 function addCount(){
    var c = parseInt($(".quantity").val(), 10);
    if (c >= 999) {
        $(".quantity").val(1);
        //$("#amount").html("1\u4ef6")
    } else {
        c++;
        $(".quantity").val(c);
        //$("#amount").html(c + "\u4ef6")
    }

}
//产品数量--
function reduceCount() {
    var c = parseInt($(".quantity").val(), 10);
    if (c <= 1) {
        $(".quantity").val(1);
        //$("#amount").html("1\u4ef6")
    } else {
        c--;
        $(".quantity").val(c);
        //$("#amount").html(c + "\u4ef6")
    }
}

//产品轮播
var slide = (function() {
    var a = function(c) {
        return new b(c)
    };
    function b(c) {
        this.elem = c;
        this.oBox = document.querySelector(c);
        this.aLi = document.querySelectorAll(c + " [data-ul-child=child]");
        this.oUl = document.querySelector(c + " [data-slide-ul=firstUl]");
        this.now = 0;
        this.on0ff = false
    }
    b.prototype = {
        init: function(c) {
            var c = c || {},
                e = this.aLi;
            this.defaults = {
                startIndex: 0,
                loop: false,
                smallBtn: false,
                number: false,
                laseMoveFn: false,
                location: false,
                preDef: "lnr",
                autoPlay: false,
                autoHeight: false,
                preFn: null,
                lastImgSlider: false,
                playTime: 6000,
                mpingEvent: null,
                wareId: null
            };
            b.extend(this.defaults, c);
            this.now = this.defaults.startIndex;
            if (this.defaults.smallBtn) {
                this.oSmallBtn = document.querySelector(this.elem + ' [data-small-btn="smallbtn"]');
                this.oSmallBtn.innerHTML = this.addSmallBtn();
                this.btns = document.querySelectorAll(this.elem + ' [data-ol-btn="btn"]');
                for (var d = 0; d < this.btns.length; d++) {
                    this.btns[d].className = ""
                }
                this.btns[b.getNow(this.now, e.length)].className = "active"
            }
            if (this.defaults.number) {
                this.slideNub = document.getElementById("slide-nub");
                this.slideSum = document.getElementById("slide-sum");
                this.slideNub.innerHTML = this.now + 1;    //图片序号
                this.slideSum.innerHTML = this.aLi.length   //图片序号
            }
            if (this.aLi.length <= 2) {
                if (this.defaults.loop) {
                    this.oUl.innerHTML += this.oUl.innerHTML
                }
                this.aLi = document.querySelectorAll(this.elem + " [data-ul-child=child]");
                this.need = true
            }
            if (this.defaults.autoPlay) {
                this.pause();
                this.play()
            }
            this.liInit();
            this.bind()
        },
        bind: function() {
            var f = this.oBox,
                e = b._device();
            if (!e.hasTouch) {
                f.style.cursor = "pointer";
                f.ondragstart = function(g) {
                    if (g) {
                        return false
                    }
                    return true
                }
            }
            var d = "onorientationchange" in window;
            var c = d ? "orientationchange": "resize";
            f.addEventListener(e.startEvt, this);
            window.addEventListener(c, this);
            window.addEventListener("touchcancel", this);
            if (navigator.userAgent.indexOf("baidubrowser")) {
                window.addEventListener("focusin", this);
                window.addEventListener("focusout", this)
            } else {
                window.addEventListener("blur", this);
                window.addEventListener("focus", this)
            }
        },
        liInit: function() {
            var d = this.aLi,
                f = d.length,
                m = this.oUl,
                l = this.oBox.offsetWidth,
                e = this.now,
                k = this;
            if (this.defaults.preFn) {
                this.defaults.preFn()
            }
            m.style.width = l * f + "px";
            for (var h = 0; h < f; h++) {
                b.setStyle(d[h], {
                    WebkitTransition: "all 0ms ease",
                    transition: "all 0ms ease",
                    height: "auto"
                })
            }
            if (this.defaults.autoHeight) {
                var c = this.oBox.offsetWidth;
                if (c >= 640) {
                    c = 640
                } else {
                    if (c <= 320) {
                        c = 320
                    }
                }
                for (var h = 0; h < f; h++) {
                    d[h].style.width = c + "px"
                }
                if (d[0]) {
                    var j = d[0].getElementsByTagName("img")[0];
                    if (j) {
                        var g = new Image();
                        g.onload = function() {
                            k.oBox.style.height = d[0].offsetHeight + "px";
                            for (var n = 0; n < d.length; n++) {
                                d[n].style.height = d[0].offsetHeight + "px"
                            }
                        };
                        g.src = j.src
                    } else {
                        this.oBox.style.height = d[0].offsetHeight + "px"
                    }
                }
            }
            if (this.defaults.loop) {
                for (var h = 0; h < f; h++) {
                    b.setStyle(d[h], {
                        position: "absolute",
                        left: 0,
                        top: 0
                    });
                    if (h == b.getNow(e, f)) {
                        b.setStyle(d[h], {
                            WebkitTransform: "translate3d(" + 0 + "px, 0px, 0px)",
                            transform: "translate3d(" + 0 + "px, 0px, 0px)",
                            zIndex: 10
                        })
                    } else {
                        if (h == b.getPre(e, f)) {
                            b.setStyle(d[h], {
                                WebkitTransform: "translate3d(" + -l + "px, 0px, 0px)",
                                transform: "translate3d(" + -l + "px, 0px, 0px)",
                                zIndex: 10
                            })
                        } else {
                            if (h == b.getNext(e, f)) {
                                b.setStyle(d[h], {
                                    WebkitTransform: "translate3d(" + l + "px, 0px, 0px)",
                                    transform: "translate3d(" + l + "px, 0px, 0px)",
                                    zIndex: 10
                                })
                            } else {
                                b.setStyle(d[h], {
                                    WebkitTransform: "translate3d(" + 0 + "px, 0px, 0px)",
                                    transform: "translate3d(" + 0 + "px, 0px, 0px)",
                                    zIndex: 9
                                })
                            }
                        }
                    }
                }
            } else {
                for (var h = 0; h < f; h++) {
                    b.setStyle(d[h], {
                        WebkitTransform: "translate3d(" + e * -l + "px, 0px, 0px)",
                        transform: "translate3d(" + e * -l + "px, 0px, 0px)"
                    })
                }
            }
        },
        handleEvent: function(d) {
            var c = b._device(),
                e = this.oBox;
            switch (d.type) {
                case c.startEvt:
                    if (this.defaults.autoPlay) {
                        this.pause()
                    }
                    this.startHandler(d);
                    break;
                case c.moveEvt:
                    if (this.defaults.autoPlay) {
                        this.pause()
                    }
                    this.moveHandler(d);
                    break;
                case c.endEvt:
                    if (this.defaults.autoPlay) {
                        this.pause();
                        this.play()
                    }
                    this.endHandler(d);
                    break;
                case "touchcancel":
                    if (this.defaults.autoPlay) {
                        this.pause();
                        this.play()
                    }
                    this.endHandler(d);
                    break;
                case "orientationchange":
                    this.orientationchangeHandler();
                    break;
                case "resize":
                    this.orientationchangeHandler();
                    break;
                case "focus":
                    if (this.defaults.autoPlay) {
                        this.pause();
                        this.play()
                    }
                    break;
                case "blur":
                    if (this.defaults.autoPlay) {
                        this.pause()
                    }
                    break;
                case "focusin":
                    if (this.defaults.autoPlay) {
                        this.pause();
                        this.play()
                    }
                    break;
                case "focusout":
                    if (this.defaults.autoPlay) {
                        this.pause()
                    }
                    break
            }
        },
        startHandler: function(e) {
            this.on0ff = true;
            var d = b._device(),
                f = d.hasTouch,
                h = this.oBox,
                c = this.now,
                g = this.aLi;
            h.addEventListener(d.moveEvt, this);
            h.addEventListener(d.endEvt, this);
            this.downTime = Date.now();
            this.downX = f ? e.targetTouches[0].pageX: e.clientX - h.offsetLeft;
            this.downY = f ? e.targetTouches[0].pageY: e.clientY - h.offsetTop;
            this.startT = b.getTranX(g[b.getNow(c, g.length)]);
            this.startNowT = b.getTranX(g[b.getNow(c, g.length)]);
            this.startPreT = b.getTranX(g[b.getPre(c, g.length)]);
            this.startNextT = b.getTranX(g[b.getNext(c, g.length)]);
            b.stopPropagation(e)
        },
        moveHandler: function(o) {
            var l = this.oBox,
                e = b._device();
            if (this.on0ff) {
                var m = e.hasTouch;
                var h = m ? o.targetTouches[0].pageX: o.clientX - l.offsetLeft;
                var g = m ? o.targetTouches[0].pageY: o.clientY - l.offsetTop;
                var c = this.aLi,
                    f = c.length,
                    d = this.now,
                    q = c[0].offsetWidth;
                if (this.defaults.preDef == "all") {
                    b.stopDefault(o)
                }
                for (var k = 0; k < f; k++) {
                    b.setStyle(c[k], {
                        WebkitTransition: "all 0ms ease",
                        transition: "all 0ms ease"
                    })
                }
                if (Math.abs(h - this.downX) < Math.abs(g - this.downY)) {
                    if (this.defaults.preDef == "tnd" && this.defaults.preDef != "all") {
                        b.stopDefault(o)
                    }
                } else {
                    if (Math.abs(h - this.downX) > 10) {
                        if (this.defaults.preDef == "lnr" && this.defaults.preDef != "all") {
                            b.stopDefault(o)
                        }
                        if (this.defaults.loop) {
                            j = (this.startNowT + h - this.downX).toFixed(4);
                            preT = (this.startPreT + h - this.downX).toFixed(4);
                            nextT = (this.startNextT + h - this.downX).toFixed(4);
                            b.move(c[b.getNow(d, f)], j, 10);
                            b.move(c[b.getPre(d, f)], preT, 10);
                            b.move(c[b.getNext(d, f)], nextT, 10)
                        } else {
                            var j = b.getTranX(c[d]);
                            if (j > 0) {
                                var n = ((this.startT + h - this.downX) / 3).toFixed(4);
                                for (var k = 0; k < f; k++) {
                                    b.move(c[k], n)
                                }
                            } else {
                                if (Math.abs(j) >= Math.abs((f - 1) * q)) {
                                    var n = (this.startT + (h - this.downX) / 3).toFixed(4);
                                    for (var k = 0; k < f; k++) {
                                        b.move(c[k], n)
                                    }
                                    if (this.defaults.laseMoveFn && typeof this.defaults.laseMoveFn == "function") {
                                        var p = (n - this.startT).toFixed(4);
                                        this.defaults.laseMoveFn(p)
                                    }
                                } else {
                                    var n = (this.startT + h - this.downX).toFixed(4);
                                    for (var k = 0; k < f; k++) {
                                        b.move(c[k], n)
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                l.removeEventListener(e.moveEvt, this);
                l.removeEventListener(e.endEvt, this)
            }
            b.stopPropagation(o)
        },
        endHandler: function(i) {
            i.stopPropagation();
            this.on0ff = false;
            var f = Date.now(),
                e = b._device(),
                h = e.hasTouch,
                g = this.oBox,
                l = h ? i.changedTouches[0].pageX: i.clientX - g.offsetLeft,
                k = h ? i.changedTouches[0].pageY: i.clientY - g.offsetTop,
                c = this.aLi,
                j = c[0].offsetWidth,
                d = b.getTranX(c[b.getNow(this.now, c.length)]);
            if (l - this.downX < 30 && l - this.downX >= 0 && Math.abs(k - this.downY) < 30) {
                this.tab(d, "+=");
                return "click"
            } else {
                if (l - this.downX > -30 && l - this.downX <= 0 && Math.abs(k - this.downY) < 30) {
                    this.tab(d, "-=");
                    return "click"
                } else {
                    if (Math.abs(k - this.downY) - Math.abs(l - this.downX) > 30 && l - this.downX < 0) {
                        this.tab(d, "-=");
                        return
                    }
                    if (Math.abs(k - this.downY) - Math.abs(l - this.downX) > 30 && l - this.downX > 0) {
                        this.tab(d, "+=");
                        return
                    }
                    if (l < this.downX) {
                        if (this.downX - l > j / 3 || f - this.downTime < 200) {
                            this.now++;
                            this.tab(d, "++");
                            return "left"
                        } else {
                            this.tab(d, "+=");
                            return "stay"
                        }
                    } else {
                        if (l - this.downX > j / 3 || f - this.downTime < 200) {
                            this.now--;
                            this.tab(d, "--");
                            return "right"
                        } else {
                            this.tab(d, "-=");
                            return "stay"
                        }
                    }
                }
            }
            b.stopPropagation(i);
            g.removeEventListener(e.moveEvt, this);
            g.removeEventListener(e.endEvt, this)
        },
        tab: function(f, m, g) {
            var c = this.aLi,
                l = c.length,
                u = c[0].offsetWidth,
                s = this.oBox,
                h = b._device(),
                r = this,
                d = this.now;
            if (this.defaults.loop) {
                if (d < 0) {
                    d = l - 1;
                    this.now = l - 1
                }
                for (var q = 0; q < l; q++) {
                    if (q == b.getPre(d, l)) {
                        var k;
                        switch (m) {
                            case "++":
                                k = 300;
                                break;
                            case "--":
                                k = 0;
                                break;
                            case "+=":
                                k = 0;
                                break;
                            case "-=":
                                k = 300;
                                break;
                            default:
                                break
                        }
                        b.setStyle(c[b.getPre(d, l)], {
                            WebkitTransform: "translate3d(" + -u + "px, 0px, 0px)",
                            transform: "translate3d(" + -u + "px, 0px, 0px)",
                            zIndex: 10,
                            WebkitTransition: "all " + k + "ms ease",
                            transition: "all " + k + "ms ease"
                        })
                    } else {
                        if (q == b.getNow(d, l)) {
                            b.setStyle(c[b.getNow(d, l)], {
                                WebkitTransform: "translate3d(" + 0 + "px, 0px, 0px)",
                                transform: "translate3d(" + 0 + "px, 0px, 0px)",
                                zIndex: 10,
                                WebkitTransition: "all " + 300 + "ms ease",
                                transition: "all " + 300 + "ms ease"
                            })
                        } else {
                            if (q == b.getNext(d, l)) {
                                var k;
                                switch (m) {
                                    case "++":
                                        k = 0;
                                        break;
                                    case "--":
                                        k = 300;
                                        break;
                                    case "+=":
                                        k = 300;
                                        break;
                                    case "-=":
                                        k = 0;
                                        break;
                                    default:
                                        break
                                }
                                b.setStyle(c[b.getNext(d, l)], {
                                    WebkitTransform: "translate3d(" + u + "px, 0px, 0px)",
                                    transform: "translate3d(" + u + "px, 0px, 0px)",
                                    zIndex: 10,
                                    WebkitTransition: "all " + k + "ms ease",
                                    transition: "all " + k + "ms ease"
                                })
                            } else {
                                b.setStyle(c[q], {
                                    WebkitTransform: "translate3d(" + 0 + "px, 0px, 0px)",
                                    transform: "translate3d(" + 0 + "px, 0px, 0px)",
                                    zIndex: 9,
                                    WebkitTransition: "all " + 0 + "ms ease",
                                    transition: "all " + 0 + "ms ease"
                                })
                            }
                        }
                    }
                }
            } else {
                for (var q = 0; q < l; q++) {
                    b.setStyle(c[q], {
                        WebkitTransition: "all " + 300 + "ms ease",
                        transition: "all " + 300 + "ms ease"
                    })
                }
                if (d <= 0) {
                    d = 0;
                    this.now = 0
                }
                if (d > l - 1) {
                    if (g) {
                        d = 0;
                        this.now = 0
                    } else {
                        d = l - 1;
                        this.now = l - 1
                    }
                }
                for (var p = 0; p < l; p++) {
                    b.setStyle(c[p], {
                        WebkitTransform: "translate3d(" + ( - d * u) + "px, 0px, 0px)",
                        transform: "translate3d(" + ( - d * u) + "px, 0px, 0px)"
                    })
                }
            }
            if (this.defaults.smallBtn) {
                for (var q = 0; q < this.btns.length; q++) {
                    this.btns[q].className = ""
                }
                if (this.need) {
                    this.btns[b.getNow(d, l / 2)].className = "active"
                } else {
                    this.btns[b.getNow(d, l)].className = "active"
                }
            }
            if (this.defaults.number) {
                this.slideNub.innerHTML = b.getNow(d, l) + 1
            }
            c[b.getNow(d, l)].addEventListener("webkitTransitionEnd", n, false);
            c[b.getNow(d, l)].addEventListener("transitionend", n, false);
            function n() {
                if (r.defaults.location) {
                    if (f < -(l - 1) * u - u / 5) {
                        if (r.defaults.lastImgSlider && typeof r.defaults.lastImgSlider == "function") {
                            r.defaults.laseMoveFn(0);
                            r.defaults.lastImgSlider()
                        }
                    }
                }
                c[b.getNow(r.now, l)].removeEventListener("webkitTransitionEnd", n, false);
                c[b.getNow(r.now, l)].removeEventListener("transitionend", n, false)
            }
            try {
                if ($("#pingUse").val()) {
                    var v = new MPing.inputs.Click(this.defaults.mpingEvent);
                    v.page_param = this.defaults.wareId;
                    var o = new MPing();
                    o.send(v)
                }
            } catch(t) {}
        },
        play: function() {
            var c = this;
            c.timer = setInterval(function() {
                    c.now++;
                    c.tab(null, "++", true)
                },
                this.defaults.playTime)
        },
        pause: function() {
            var c = this;
            clearInterval(c.timer)
        },
        orientationchangeHandler: function() {
            var c = this;
            setTimeout(function() {
                    c.liInit()
                },
                300)
        },
        addSmallBtn: function() {
            var d = "",
                e = this.aLi;
            for (var c = 0; c < e.length; c++) {
                if (c == 0) {
                    d += '<span class="active" data-ol-btn="btn"></span>'
                } else {
                    d += '<span data-ol-btn="btn"></span>'
                }
            }
            return d
        },
        show: function() {
            this.oBox.style.display = "inline-block"
        },
        hide: function() {
            this.oBox.style.display = "none"
        }
    };
    b.extend = function(d, c) {
        for (name in c) {
            if (c[name] !== undefined) {
                d[name] = c[name]
            }
        }
    };
    b.extend(b, {
        setStyle: function(d, c) {
            for (name in c) {
                d.style[name] = c[name]
            }
        },
        getTranX: function(e) {
            var d = e.style.WebkitTransform || e.style.transform;
            var f = d.indexOf("translate3d(");
            var c = parseInt(d.substring(f + 12, d.length - 13));
            return c
        },
        _device: function() {
            var f = !!("ontouchstart" in window || window.DocumentTouch && document instanceof window.DocumentTouch);
            var d = "touchstart";
            var e = "touchmove";
            var c = "touchend";
            return {
                hasTouch: f,
                startEvt: d,
                moveEvt: e,
                endEvt: c
            }
        },
        getNow: function(d, c) {
            return d % c
        },
        getPre: function(e, c) {
            if (e % c - 1 < 0) {
                var d = c - 1
            } else {
                var d = e % c - 1
            }
            return d
        },
        getNext: function(e, d) {
            if (e % d + 1 > d - 1) {
                var c = 0
            } else {
                var c = e % d + 1
            }
            return c
        },
        move: function(e, c, d) {
            var f = d || null;
            if (f) {
                e.style.zIndex = f
            }
            b.setStyle(e, {
                WebkitTransform: "translate3d(" + c + "px, 0px, 0px)",
                transform: "translate3d(" + c + "px, 0px, 0px)"
            })
        },
        stopDefault: function(c) {
            if (c && c.preventDefault) {
                c.preventDefault()
            } else {
                window.event.returnValue = false
            }
            return false
        },
        stopPropagation: function(c) {
            if (c && c.stopPropagation) {
                c.stopPropagation()
            } else {
                c.cancelBubble = true
            }
        }
    });
    return a
})();
