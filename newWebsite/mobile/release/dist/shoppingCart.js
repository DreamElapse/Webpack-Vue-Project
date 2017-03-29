webpackJsonp([18],{163:function(t,e,i){t.exports={default:i(164),__esModule:!0}},164:function(t,e,i){i(144),i(115),i(128),i(165),t.exports=i(63).Promise},165:function(t,e,i){"use strict";var o,n,s,a=i(118),r=i(62),c=i(64),d=i(155),l=i(61),f=i(69),g=i(65),p=i(166),u=i(167),v=i(170),h=i(171).set,m=i(173)(),_="Promise",b=r.TypeError,x=r.process,y=r[_],x=r.process,k="process"==d(x),w=function(){},j=!!function(){try{var t=y.resolve(1),e=(t.constructor={})[i(126)("species")]=function(t){t(w,w)};return(k||"function"==typeof PromiseRejectionEvent)&&t.then(w)instanceof e}catch(t){}}(),L=function(t,e){return t===e||t===y&&e===s},C=function(t){var e;return!(!f(t)||"function"!=typeof(e=t.then))&&e},A=function(t){return L(y,t)?new G(t):new n(t)},G=n=function(t){var e,i;this.promise=new t(function(t,o){if(void 0!==e||void 0!==i)throw b("Bad Promise constructor");e=t,i=o}),this.resolve=g(e),this.reject=g(i)},$=function(t){try{t()}catch(t){return{error:t}}},M=function(t,e){if(!t._n){t._n=!0;var i=t._c;m(function(){for(var o=t._v,n=1==t._s,s=0,a=function(e){var i,s,a=n?e.ok:e.fail,r=e.resolve,c=e.reject,d=e.domain;try{a?(n||(2==t._h&&E(t),t._h=1),a===!0?i=o:(d&&d.enter(),i=a(o),d&&d.exit()),i===e.promise?c(b("Promise-chain cycle")):(s=C(i))?s.call(i,r,c):r(i)):c(o)}catch(t){c(t)}};i.length>s;)a(i[s++]);t._c=[],t._n=!1,e&&!t._h&&z(t)})}},z=function(t){h.call(r,function(){var e,i,o,n=t._v;if(q(t)&&(e=$(function(){k?x.emit("unhandledRejection",n,t):(i=r.onunhandledrejection)?i({promise:t,reason:n}):(o=r.console)&&o.error&&o.error("Unhandled promise rejection",n)}),t._h=k||q(t)?2:1),t._a=void 0,e)throw e.error})},q=function(t){if(1==t._h)return!1;for(var e,i=t._a||t._c,o=0;i.length>o;)if(e=i[o++],e.fail||!q(e.promise))return!1;return!0},E=function(t){h.call(r,function(){var e;k?x.emit("rejectionHandled",t):(e=r.onrejectionhandled)&&e({promise:t,reason:t._v})})},P=function(t){var e=this;e._d||(e._d=!0,e=e._w||e,e._v=t,e._s=2,e._a||(e._a=e._c.slice()),M(e,!0))},T=function(t){var e,i=this;if(!i._d){i._d=!0,i=i._w||i;try{if(i===t)throw b("Promise can't be resolved itself");(e=C(t))?m(function(){var o={_w:i,_d:!1};try{e.call(t,c(T,o,1),c(P,o,1))}catch(t){P.call(o,t)}}):(i._v=t,i._s=1,M(i,!1))}catch(t){P.call({_w:i,_d:!1},t)}}};j||(y=function(t){p(this,y,_,"_h"),g(t),o.call(this);try{t(c(T,this,1),c(P,this,1))}catch(t){P.call(this,t)}},o=function(t){this._c=[],this._a=void 0,this._s=0,this._d=!1,this._v=void 0,this._h=0,this._n=!1},o.prototype=i(174)(y.prototype,{then:function(t,e){var i=A(v(this,y));return i.ok="function"!=typeof t||t,i.fail="function"==typeof e&&e,i.domain=k?x.domain:void 0,this._c.push(i),this._a&&this._a.push(i),this._s&&M(this,!1),i.promise},catch:function(t){return this.then(void 0,t)}}),G=function(){var t=new o;this.promise=t,this.resolve=c(T,t,1),this.reject=c(P,t,1)}),l(l.G+l.W+l.F*!j,{Promise:y}),i(125)(y,_),i(175)(_),s=i(63)[_],l(l.S+l.F*!j,_,{reject:function(t){var e=A(this),i=e.reject;return i(t),e.promise}}),l(l.S+l.F*(a||!j),_,{resolve:function(t){if(t instanceof y&&L(t.constructor,this))return t;var e=A(this),i=e.resolve;return i(t),e.promise}}),l(l.S+l.F*!(j&&i(176)(function(t){y.all(t).catch(w)})),_,{all:function(t){var e=this,i=A(e),o=i.resolve,n=i.reject,s=$(function(){var i=[],s=0,a=1;u(t,!1,function(t){var r=s++,c=!1;i.push(void 0),a++,e.resolve(t).then(function(t){c||(c=!0,i[r]=t,--a||o(i))},n)}),--a||o(i)});return s&&n(s.error),i.promise},race:function(t){var e=this,i=A(e),o=i.reject,n=$(function(){u(t,!1,function(t){e.resolve(t).then(i.resolve,o)})});return n&&o(n.error),i.promise}})},166:function(t,e){t.exports=function(t,e,i,o){if(!(t instanceof e)||void 0!==o&&o in t)throw TypeError(i+": incorrect invocation!");return t}},167:function(t,e,i){var o=i(64),n=i(168),s=i(169),a=i(68),r=i(85),c=i(154),d={},l={},e=t.exports=function(t,e,i,f,g){var p,u,v,h,m=g?function(){return t}:c(t),_=o(i,f,e?2:1),b=0;if("function"!=typeof m)throw TypeError(t+" is not iterable!");if(s(m)){for(p=r(t.length);p>b;b++)if(h=e?_(a(u=t[b])[0],u[1]):_(t[b]),h===d||h===l)return h}else for(v=m.call(t);!(u=v.next()).done;)if(h=n(v,_,u.value,e),h===d||h===l)return h};e.BREAK=d,e.RETURN=l},168:function(t,e,i){var o=i(68);t.exports=function(t,e,i,n){try{return n?e(o(i)[0],i[1]):e(i)}catch(e){var s=t.return;throw void 0!==s&&o(s.call(t)),e}}},169:function(t,e,i){var o=i(120),n=i(126)("iterator"),s=Array.prototype;t.exports=function(t){return void 0!==t&&(o.Array===t||s[n]===t)}},170:function(t,e,i){var o=i(68),n=i(65),s=i(126)("species");t.exports=function(t,e){var i,a=o(t).constructor;return void 0===a||void 0==(i=o(a)[s])?e:n(i)}},171:function(t,e,i){var o,n,s,a=i(64),r=i(172),c=i(124),d=i(73),l=i(62),f=l.process,g=l.setImmediate,p=l.clearImmediate,u=l.MessageChannel,v=0,h={},m="onreadystatechange",_=function(){var t=+this;if(h.hasOwnProperty(t)){var e=h[t];delete h[t],e()}},b=function(t){_.call(t.data)};g&&p||(g=function(t){for(var e=[],i=1;arguments.length>i;)e.push(arguments[i++]);return h[++v]=function(){r("function"==typeof t?t:Function(t),e)},o(v),v},p=function(t){delete h[t]},"process"==i(82)(f)?o=function(t){f.nextTick(a(_,t,1))}:u?(n=new u,s=n.port2,n.port1.onmessage=b,o=a(s.postMessage,s,1)):l.addEventListener&&"function"==typeof postMessage&&!l.importScripts?(o=function(t){l.postMessage(t+"","*")},l.addEventListener("message",b,!1)):o=m in d("script")?function(t){c.appendChild(d("script"))[m]=function(){c.removeChild(this),_.call(t)}}:function(t){setTimeout(a(_,t,1),0)}),t.exports={set:g,clear:p}},172:function(t,e){t.exports=function(t,e,i){var o=void 0===i;switch(e.length){case 0:return o?t():t.call(i);case 1:return o?t(e[0]):t.call(i,e[0]);case 2:return o?t(e[0],e[1]):t.call(i,e[0],e[1]);case 3:return o?t(e[0],e[1],e[2]):t.call(i,e[0],e[1],e[2]);case 4:return o?t(e[0],e[1],e[2],e[3]):t.call(i,e[0],e[1],e[2],e[3])}return t.apply(i,e)}},173:function(t,e,i){var o=i(62),n=i(171).set,s=o.MutationObserver||o.WebKitMutationObserver,a=o.process,r=o.Promise,c="process"==i(82)(a);t.exports=function(){var t,e,i,d=function(){var o,n;for(c&&(o=a.domain)&&o.exit();t;){n=t.fn,t=t.next;try{n()}catch(o){throw t?i():e=void 0,o}}e=void 0,o&&o.enter()};if(c)i=function(){a.nextTick(d)};else if(s){var l=!0,f=document.createTextNode("");new s(d).observe(f,{characterData:!0}),i=function(){f.data=l=!l}}else if(r&&r.resolve){var g=r.resolve();i=function(){g.then(d)}}else i=function(){n.call(o,d)};return function(o){var n={fn:o,next:void 0};e&&(e.next=n),t||(t=n,i()),e=n}}},174:function(t,e,i){var o=i(66);t.exports=function(t,e,i){for(var n in e)i&&t[n]?t[n]=e[n]:o(t,n,e[n]);return t}},175:function(t,e,i){"use strict";var o=i(62),n=i(63),s=i(67),a=i(71),r=i(126)("species");t.exports=function(t){var e="function"==typeof n[t]?n[t]:o[t];a&&e&&!e[r]&&s.f(e,r,{configurable:!0,get:function(){return this}})}},176:function(t,e,i){var o=i(126)("iterator"),n=!1;try{var s=[7][o]();s.return=function(){n=!0},Array.from(s,function(){throw 2})}catch(t){}t.exports=function(t,e){if(!e&&!n)return!1;var i=!1;try{var s=[7],a=s[o]();a.next=function(){return{done:i=!0}},s[o]=function(){return a},t(s)}catch(t){}return i}},300:function(t,e,i){var o,n,s={};i(301),i(303),o=i(305),n=i(311),t.exports=o||{},t.exports.__esModule&&(t.exports=t.exports.default);var a="function"==typeof t.exports?t.exports.options||(t.exports.options={}):t.exports;n&&(a.template=n),a.computed||(a.computed={}),Object.keys(s).forEach(function(t){var e=s[t];a.computed[t]=function(){return e}})},301:function(t,e,i){var o=i(302);"string"==typeof o&&(o=[[t.id,o,""]]);i(22)(o,{});o.locals&&(t.exports=o.locals)},302:function(t,e,i){e=t.exports=i(3)(),e.push([t.id,".goods-tab-cont ol{min-height:1.4rem;line-height:0;padding:.4rem;text-align:center}.goods-tab-cont ol li{width:.6rem;height:.6rem;margin:0 .15rem;border-radius:100%;background:#b1b1b1;display:inline-block}.goods-tab-cont ol li.on{background:#000}",""])},303:function(t,e,i){var o=i(304);"string"==typeof o&&(o=[[t.id,o,""]]);i(22)(o,{});o.locals&&(t.exports=o.locals)},304:function(t,e,i){e=t.exports=i(3)(),e.push([t.id,'.tips[_v-36e8c904]{padding:0 .8rem;height:2.4rem;background:#fff;border:1px solid #e1e1e1;border-width:1px 0;font-size:.9rem;color:#222;display:-webkit-box;display:-ms-flexbox;display:flex;display:-webkit-flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-align-items:center;position:fixed;z-index:10}.tips div[_v-36e8c904]{white-space:nowrap;text-overflow:ellipsis;overflow:hidden;-webkit-box-flex:1;-ms-flex:1;flex:1;-webkit-flex:1;display:block}.tips span[_v-36e8c904]{margin-left:.8rem}.goods-list[_v-36e8c904]{padding:2.4rem .6rem 0}.goods-item[_v-36e8c904]{padding:.8rem 0;border-bottom:1px solid #e1e1e1;display:-webkit-box;display:-ms-flexbox;display:flex;display:-webkit-flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-align-items:center;position:relative}.goods-item .sale[_v-36e8c904]{width:2rem;padding:.2rem 0 0;border-radius:0 0 4px 4px;background:#da3737;font-weight:400;font-size:.6rem;color:#fff;text-align:center;display:inline-block;position:absolute;left:0;top:0}.goods-item .sale[_v-36e8c904]:after{content:"";border:1rem solid transparent;border-top-width:.3rem;border-bottom:.3rem solid #fff;display:block}.goods-item .checkbox[_v-36e8c904]{display:block;margin-right:.8rem}.goods-item-img[_v-36e8c904]{width:5.6rem;height:5.6rem;text-align:center;display:inline-block}.goods-item-img img[_v-36e8c904]{max-height:100%}.goods-item-info[_v-36e8c904]{padding-left:.4rem;-webkit-box-flex:1;-ms-flex:1;flex:1;-webkit-flex:1}.goods-item-info strong[_v-36e8c904]{font-weight:400;font-size:1.2rem;margin-bottom:.6rem;cursor:pointer;display:block}.goods-item .price[_v-36e8c904]{color:#c50007}.goods-action[_v-36e8c904]{display:-webkit-box;display:-ms-flexbox;display:flex;display:-webkit-flex;-webkit-box-align:end;-ms-flex-align:end;align-items:flex-end;-webkit-align-items:flex-end}.goods-action .price[_v-36e8c904]{-webkit-box-flex:1;-ms-flex:1;flex:1;-webkit-flex:1;display:block}.goods-action del[_v-36e8c904]{font-size:.8rem;color:#adadad;margin-left:.2rem}.goods-item .btn-delete[_v-36e8c904]{width:3rem;background:#c50007;font-size:.9rem;color:#fff;display:none}.goods-list.edit[_v-36e8c904]{padding-top:2.4rem}.goods-list.edit .goods-item-info[_v-36e8c904]{overflow:hidden}.goods-list.edit .goods-item-info strong[_v-36e8c904]{margin:0;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.goods-list.edit .goods-action[_v-36e8c904]{display:block}.goods-list.edit .goods-action .price[_v-36e8c904]{margin:.2rem 0}.goods-list.edit .btn-delete[_v-36e8c904]{-ms-flex-item-align:stretch;align-self:stretch;-webkit-align-self:stretch;display:-webkit-box;display:-ms-flexbox;display:flex;display:-webkit-flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-align-items:center;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-justify-content:center}.goods-tab[_v-36e8c904]{margin-top:.8rem}.goods-tab-hd[_v-36e8c904]{margin:0 .8rem;border-bottom:2px solid #000;-webkit-box-pack:end;-ms-flex-pack:end;justify-content:flex-end;-webkit-justify-content:flex-end}.goods-tab-hd[_v-36e8c904],.goods-tab-hd a[_v-36e8c904]{display:-webkit-box;display:-ms-flexbox;display:flex;display:-webkit-flex}.goods-tab-hd a[_v-36e8c904]{width:5.4rem;height:1.8rem;margin-left:.4rem;font-size:.9rem;border:1px solid #c4c4c4;border-bottom:0;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-justify-content:center;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-align-items:center}.goods-tab-hd a.on[_v-36e8c904]{background:#000;color:#fff}.goods-tab-hd a .font[_v-36e8c904]{margin-right:.2rem;font-size:1.1rem;display:block}.goods-tab-bd[_v-36e8c904]{position:relative;overflow:hidden}.tab-cont-wrapper[_v-36e8c904]{width:200%;position:relative}.goods-tab-cont[_v-36e8c904]{width:50%;float:left;position:relative}.goods-tab-cont .goods-other-li[_v-36e8c904]{position:relative;float:left}.goods-tab-bd .goods-item[_v-36e8c904]{padding:.8rem;margin:0 0 2px;background:#f4f4f4}.goods-tab-bd .goods-item-img[_v-36e8c904]{width:3rem;height:3rem;margin-left:.4rem;background:#fff}.goods-tab-bd .goods-item strong[_v-36e8c904]{padding:0 .6rem;font-weight:400;font-size:.9rem;-webkit-box-flex:1;-ms-flex:1;flex:1;-webkit-flex:1;display:block}.goods-tab-bd .goods-item .price[_v-36e8c904]{width:4rem}.goods-tab-bd .goods-qty span[_v-36e8c904]{background:#fcfcfc}.p2[_v-36e8c904]{line-height:2.4rem;padding:0 .8rem;border:1px solid #e1e1e1;border-width:1px 0;font-size:.9rem;color:#222}.goods-ft[_v-36e8c904]{width:100%;height:3rem;background:#fff;border-top:1px solid #e1e1e1;display:table;position:fixed;bottom:0}.goods-ft>[_v-36e8c904]{display:table-cell;vertical-align:middle}.goods-ft .check-all[_v-36e8c904]{width:25%;padding-left:.8rem}.goods-ft .check-all input[_v-36e8c904]{vertical-align:middle;margin-right:.2rem}.goods-ft .check-all em[_v-36e8c904]{line-height:1;vertical-align:middle;font-size:.9rem}.goods-ft .total[_v-36e8c904]{padding-right:.8rem;text-align:right}.goods-ft .total span[_v-36e8c904]{color:#c50007}.goods-ft .btn[_v-36e8c904]{width:25%;background:#c50007}',""])},305:function(t,e,i){(function(t){"use strict";function o(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var n=i(147),s=o(n),a=i(163),r=o(a),c=i(151),d=o(c),l=i(31),f=i(306),g=o(f),p=i(44),u=o(p),v=i(103);e.default={components:{GoodsQuantity:g.default,Quantity:u.default},vuex:{actions:{updateAppHeader:l.updateAppHeader,getCartGoodsQty:l.getCartGoodsQty,caclCartGoodsQty:l.caclCartGoodsQty,addGoodsToCart:l.addGoodsToCart}},route:{data:function(){this.updateAppHeader({type:2,content:"购物车"}),this.isEdit=!1,this.page=1,this.goodsList=[],this.getPromotionMsg(),this.showCart(),this.getActList()}},ready:function(){t(".goods-tab-hd a").on("click",function(){var e=t(this);e.addClass("on").siblings().removeClass("on"),e.closest(".goods-tab").find(".goods-tab-cont").removeClass("on").hide().eq(e.index()).addClass("on").show()})},data:function(){return{promotionMsg:"",isEdit:!1,computing:!1,page:1,loadCartBtn:"点击加载更多...",goodsList:[],page_size:5,total_page:0,giftList:[],exchangeList:[],total_amount:0}},watch:{goodsList:function(t){0!=t.length&&(this.page=Math.ceil(t.length/this.page_size))}},computed:{isSelectAll:function(){if(0==this.goodsList.length)return!1;var t=!0,e=!1,i=void 0;try{for(var o,n=(0,d.default)(this.goodsList);!(t=(o=n.next()).done);t=!0){var s=o.value;if(0==s.select)return!1}}catch(t){e=!0,i=t}finally{try{!t&&n.return&&n.return()}finally{if(e)throw i}}return!0}},methods:{getPromotionMsg:function(){var t=this,e={is_promotion:1};this.$http.post("/User/getInformations.json",e).then(function(e){e=e.json(),1==e.status&&e.data[0]&&(t.promotionMsg=e.data[0].title)})},edit:function(t){this.isEdit=!this.isEdit;var e=t.target;this.isEdit?e.innerHTML="完成":e.innerHTML="编辑"},showCart:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},i={page_size:this.page_size,page:this.page};return this.loadCartBtn="加载中...",this.$http.post("/cart/showcart.json",i).then(function(i){i=i.json();var o=i.cart_goods_page_data;if(o){if(e.page){var n=1==t.page?0:t.page_size*e.page;n=n%t.page_size==0?n-t.page_size:n,t.goodsList.splice(n,t.page_size)}var s=!0,a=!1,r=void 0;try{for(var c,l=(0,d.default)(o);!(s=(c=l.next()).done);s=!0){var f=c.value;t.goodsList.push(f)}}catch(t){a=!0,r=t}finally{try{!s&&l.return&&l.return()}finally{if(a)throw r}}}t.$set("total_page",i.total_page),t.$set("total_amount",i.total_amount),t.page<t.total_page?t.loadCartBtn="点击加载更多...":t.loadCartBtn="没有更多了"})},showCartNextPage:function(){var t=this;this.page<this.total_page&&(this.page+=1,this.computing=!0,this.showCart().then(function(){t.computing=!1}))},updateGoods:function(){var t=this,e={page_size:this.page_size*this.page,page:1};return this.$http.post("/cart/showcart.json",e).then(function(e){e=e.json();var i=e.cart_goods_page_data;i?t.goodsList=i:t.goodsList=[],t.$set("total_amount",e.total_amount),t.$set("total_page",e.total_page),t.page<t.total_page?t.loadCartBtn="点击加载更多...":t.loadCartBtn="没有更多了"})},txtLimit:function(t){return t.length>10?t.substr(0,10)+"...":t},slider:function(e){var i=t(e);new v(e,{speed:800,auto:0,continuous:!1,callback:function(t,e){i.find("ol").children().removeClass("on").eq(t).addClass("on")}});var o=i.find(".goods-other-li").length,n="";if(o>=2){for(var s=0;s<o;s++)n+=0!=s?"<li></li>":'<li class="on"></li>';i.find("ol").append(n)}},select:function(t){t.select?this.unselectGoods(t):this.selectGoods(t)},selectGoods:function(t){var e=this;if(!this.computing){this.computing=!0;var i={num:t.goods_number,act_id:t.is_gift,option:"select",msg:!1};parseInt(t.pg_id)?(i.id=t.pg_id,"package_buy"==t.extension_code&&(i.package=0)):(i.id=t.goods_id,"package_buy"==t.extension_code&&(i.package=1)),t.act_id&&(i.id=t.id,i.act_id=t.act_id,i.package=t.is_package),this.addGoodsToCart(i,function(){t.select=!0;var i=[e.showCart({page:e.page}),e.getActList(),e.getCartGoodsQty(!0)];r.default.all(i).then(function(){e.computing=!1},function(){e.computing=!1}).catch(function(){e.computing=!1})},function(){e.computing=!1})}},unselectGoods:function(t){var e=this,i=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0;if(!this.computing){this.computing=!0;var o={rec_id:t.rec_id,real_del:i};this.$http.post("/cart/delGoods.json",o).then(function(o){if(o=o.json(),1==o.status){i?e.goodsList.$remove(t):t.select=!1;var n=[e.updateGoods(),e.getActList(),e.getCartGoodsQty(!0)];r.default.all(n).then(function(){e.computing=!1},function(){e.computing=!1}).catch(function(){e.computing=!1})}else e.$dispatch("popup",o.msg),e.computing=!1},function(){e.computing=!1})}},delGoods:function(t){var e=window.confirm("确认删除该商品吗");e?this.unselectGoods(t,1):this.computing=!1},selectAll:function(t){var e=this;t.preventDefault(),this.computing||(this.computing=!0,this.isSelectAll?this.$http.post("/cart/delAllGoods.json").then(function(t){if(t=t.json(),1==t.status){var i=!0,o=!1,n=void 0;try{for(var s,a=(0,d.default)(e.goodsList);!(i=(s=a.next()).done);i=!0){var c=s.value;c.select=!1}}catch(t){o=!0,n=t}finally{try{!i&&a.return&&a.return()}finally{if(o)throw n}}r.default.all([e.updateGoods(),e.getActList()]).then(function(){e.computing=!1}).catch(function(){e.computing=!1})}else e.computing=!1}):this.$http.post("/cart/selectAllGoods.json").then(function(t){if(t=t.json(),1==t.status){var i=!0,o=!1,n=void 0;try{for(var s,a=(0,d.default)(e.goodsList);!(i=(s=a.next()).done);i=!0){var c=s.value;c.select=!0}}catch(t){o=!0,n=t}finally{try{!i&&a.return&&a.return()}finally{if(o)throw n}}r.default.all([e.getActList(),e.getAmount()]).then(function(){e.computing=!1}).catch(function(){e.computing=!1})}else e.computing=!1}))},getAmount:function(){var t=this;this.$http.post("/cart/showcart.json").then(function(e){e=e.json(),t.total_amount=e.total_amount})},getActList:function(){var e=this;this.$http.get("/cart/activityList.json?gift=1").then(function(i){i=i.json();var o=[],n=[],a=!0,r=!1,c=void 0;try{for(var l,f=(0,d.default)((0,s.default)(i));!(a=(l=f.next()).done);a=!0){var g=l.value,p=i[g];if(1==p.is_free_gift){var u=!0,v=!1,h=void 0;try{for(var m,_=(0,d.default)((0,s.default)(p.gift));!(u=(m=_.next()).done);u=!0){var b=m.value;p.gift[b].act_id=p.act_id,p.gift[b].is_package=0,p.gift[b].goods_number=1,o.push(p.gift[b])}}catch(t){v=!0,h=t}finally{try{!u&&_.return&&_.return()}finally{if(v)throw h}}var x=!0,y=!1,k=void 0;try{for(var w,j=(0,d.default)((0,s.default)(p.gift_package));!(x=(w=j.next()).done);x=!0){var L=w.value;p.gift_package[L].act_id=p.act_id,p.gift_package[L].is_package=1,p.gift_package[L].goods_number=1,o.push(p.gift_package[L])}}catch(t){y=!0,k=t}finally{try{!x&&j.return&&j.return()}finally{if(y)throw k}}}if(1==p.is_exchange_buy){var C=!0,A=!1,G=void 0;try{for(var $,M=(0,d.default)((0,s.default)(p.gift));!(C=($=M.next()).done);C=!0){var z=$.value;p.gift[z].act_id=p.act_id,p.gift[z].is_package=0,p.gift[z].goods_number=1,p.gift[z].select=!1,n.push(p.gift[z])}}catch(t){A=!0,G=t}finally{try{!C&&M.return&&M.return()}finally{if(A)throw G}}var q=!0,E=!1,P=void 0;try{for(var T,O=(0,d.default)((0,s.default)(p.gift_package));!(q=(T=O.next()).done);q=!0){var S=T.value;p.gift_package[S].act_id=p.act_id,p.gift_package[S].is_package=1,p.gift_package[S].goods_number=1,p.gift_package[S].select=!1,n.push(p.gift_package[S])}}catch(t){E=!0,P=t}finally{try{!q&&O.return&&O.return()}finally{if(E)throw P}}}}}catch(t){r=!0,c=t}finally{try{!a&&f.return&&f.return()}finally{if(r)throw c}}e.$set("giftList",o),e.$set("exchangeList",n),e.$nextTick(function(){t(".goods-tab-cont ol").html("");var i=document.querySelectorAll(".goods-tab-cont");t(".goods-tab-hd a").removeClass("on").eq(0).addClass("on");var o=!0,n=!1,s=void 0;try{for(var a,r=(0,d.default)(i);!(o=(a=r.next()).done);o=!0){var c=a.value;c.style.display="block",e.slider(c)}}catch(t){n=!0,s=t}finally{try{!o&&r.return&&r.return()}finally{if(n)throw s}}})})},toOrder:function(){if(0==this.goodsList.length)return void this.$dispatch("popup","购物车还没有加入商品");var t=!1,e=!0,i=!1,o=void 0;try{for(var n,s=(0,d.default)(this.goodsList);!(e=(n=s.next()).done);e=!0){var a=n.value;if(a.select){t=!0;break}}}catch(t){i=!0,o=t}finally{try{!e&&s.return&&s.return()}finally{if(i)throw o}}t?this.$route.router.go({name:"order"}):this.$dispatch("popup","你还没有勾选商品")}},events:{quantityMinus:function(t){var e=this;if(!this.computing){this.computing=!0;var i={act_id:t.is_gift};parseInt(t.pg_id)?(i.goods_id=t.pg_id,"package_buy"==t.extension_code&&(i.is_package=0)):(i.goods_id=t.goods_id,"package_buy"==t.extension_code&&(i.is_package=1)),this.$http.post("/cart/mineOneGoods.json",i).then(function(i){i=i.json(),1==i.status?(t.goods_number-=1,e.caclCartGoodsQty(-1),r.default.all([e.getActList(),e.showCart({page:e.page})]).then(function(){e.computing=!1})):(e.$dispatch("popup",i.msg),e.computing=!1)})}},quantityAdd:function(t){var e=this;if(!this.computing){this.computing=!0;var i={num:1,act_id:t.is_gift,msg:!1};parseInt(t.pg_id)?(i.id=t.pg_id,"package_buy"==t.extension_code&&(i.package=0)):(i.id=t.goods_id,"package_buy"==t.extension_code&&(i.package=1)),this.addGoodsToCart(i,function(){t.select=!0,t.goods_number+=1,r.default.all([e.getActList(),e.showCart({page:e.page})]).then(function(){e.computing=!1})},function(){e.computing=!1})}}}}}).call(e,i(24))},306:function(t,e,i){var o,n,s={};i(307),o=i(309),n=i(310),t.exports=o||{},t.exports.__esModule&&(t.exports=t.exports.default);var a="function"==typeof t.exports?t.exports.options||(t.exports.options={}):t.exports;n&&(a.template=n),a.computed||(a.computed={}),Object.keys(s).forEach(function(t){var e=s[t];a.computed[t]=function(){return e}})},307:function(t,e,i){var o=i(308);"string"==typeof o&&(o=[[t.id,o,""]]);i(22)(o,{});o.locals&&(t.exports=o.locals)},308:function(t,e,i){e=t.exports=i(3)(),e.push([t.id,".goods-qty{height:1.8rem;text-align:center;display:inline-table}.goods-qty span{width:1.8rem;height:100%;background:#f2f2f2;display:table-cell;vertical-align:middle}.goods-qty input{width:2.4rem;height:100%;border:1px solid #fff;border-width:0 1px;background:#fbfbfb;text-align:center;display:inline-block;vertical-align:top}",""])},309:function(t,e){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={props:{goods:{type:Object}},computed:{qty:function(){return this.goods.goods_number||this.goods.qty}},methods:{minus:function(){this.$dispatch("quantityMinus",this.goods)},add:function(){this.$dispatch("quantityAdd",this.goods)}}}},310:function(t,e){t.exports=" <div class=goods-qty> <span @click=minus>-</span> <span><input type=text readonly=readonly v-model=qty /></span> <span @click=add>+</span> </div> "},311:function(t,e){t.exports=' <div class=container _v-36e8c904=""> <div v-show=computing class=loading _v-36e8c904=""></div> <div class="width-full tips" _v-36e8c904=""> <div _v-36e8c904=""><p v-if=promotionMsg _v-36e8c904="">本期优惠大放送：{{promotionMsg}}</p></div><span v-if="goodsList.length > 0" @click=edit($event) _v-36e8c904="">编辑</span> </div> <ul class=goods-list :class="{edit: isEdit}" _v-36e8c904=""> <li v-for="item in goodsList" _v-36e8c904=""> <div class=goods-item _v-36e8c904=""> <b v-if=parseInt(item.is_gift) class=sale _v-36e8c904="">活动SALE</b> <b class=checkbox :class="{selected: item.select}" @click.prevent=select(item) _v-36e8c904=""></b> <a v-if=parseInt(item.is_gift) class=goods-item-img _v-36e8c904=""><img :src=item.thumb alt="" _v-36e8c904=""></a> <template v-else=""> <a v-if="item.extension_code==\'package_buy\'" class=goods-item-img v-link="{ name: \'goodsDetail\', params: {id: item.pg_id, package: 0} }" _v-36e8c904=""><img :src=item.thumb alt="" _v-36e8c904=""></a> <a v-else="" class=goods-item-img v-link="{ name: \'goodsDetail\', params: {id: item.goods_id, package: 0} }" _v-36e8c904=""><img :src=item.thumb alt="" _v-36e8c904=""></a> </template> <div class=goods-item-info _v-36e8c904=""> <strong v-if=parseInt(item.is_gift) _v-36e8c904="">{{item.goods_name}}</strong> <template v-else=""> <strong v-if="item.extension_code==\'package_buy\'" v-link="{ name: \'goodsDetail\', params: {id: item.pg_id, package: 0} }" _v-36e8c904="">{{item.goods_name}}</strong> <strong v-else="" v-link="{ name: \'goodsDetail\', params: {id: item.goods_id, package: 0} }" _v-36e8c904="">{{item.goods_name}}</strong> </template> <div class=goods-action _v-36e8c904=""> <div class=price _v-36e8c904="">￥{{item.goods_price}}<del _v-36e8c904="">￥{{item.market_price}}</del></div> <goods-quantity :goods=item v-on:quantityminus="" v-on:quantityadd="" _v-36e8c904=""></goods-quantity> </div> </div> <div class=btn-delete @click=delGoods(item) _v-36e8c904="">删除</div> </div> </li> </ul> <a class=load-more href=javascript:; @click=showCartNextPage _v-36e8c904="">{{loadCartBtn}}</a> <div class=goods-tab _v-36e8c904=""> <div class=goods-tab-hd _v-36e8c904=""> <a class=on href=javascript:; _v-36e8c904=""><i class="font icon-gift" _v-36e8c904=""></i><em _v-36e8c904="">赠品</em></a> <a href=javascript:; _v-36e8c904=""><i class="font icon-add-to-ShoppingCart" _v-36e8c904=""></i><em _v-36e8c904="">换购</em></a> </div> <div class=goods-tab-bd _v-36e8c904=""> <div class=tab-cont-wrapper _v-36e8c904=""> <div class=goods-tab-cont _v-36e8c904=""> <div class="goods-other-list clf" _v-36e8c904=""> <div class=goods-other-li v-for="item in giftList" _v-36e8c904=""> <div class=goods-item _v-36e8c904=""> <input class=checkbox type=checkbox @click.prevent=select(item) _v-36e8c904=""> <a class=goods-item-img href=javascript:; _v-36e8c904=""><img :src=item.thumb alt="" _v-36e8c904=""></a> <strong _v-36e8c904="">{{txtLimit(item.name)}}</strong> <div class=price _v-36e8c904="">￥{{item.price}}</div> <div class=goods-action _v-36e8c904=""> x{{item.goods_number}} </div> </div> </div> </div> <ol _v-36e8c904=""></ol> </div> <div class=goods-tab-cont _v-36e8c904=""> <div class="goods-other-list clf" _v-36e8c904=""> <div class=goods-other-li v-for="item in exchangeList" _v-36e8c904=""> <div class=goods-item _v-36e8c904=""> <input class=checkbox type=checkbox @click.prevent=select(item) _v-36e8c904=""> <a class=goods-item-img href=javascript:; _v-36e8c904=""><img :src=item.thumb alt="" _v-36e8c904=""></a> <strong _v-36e8c904="">{{txtLimit(item.name)}}</strong> <div class=price _v-36e8c904="">￥{{item.price}}</div> <div class=goods-action _v-36e8c904=""> x{{item.goods_number}} </div> </div> </div> </div> <ol _v-36e8c904=""></ol> </div> </div> </div> </div> <p class=p2 _v-36e8c904="">包邮提示：购买满200元包邮！(港澳台除外)</p> <div class="width-full goods-ft" _v-36e8c904=""> <div class=check-all _v-36e8c904=""><label @click.prevent=selectAll($event) _v-36e8c904=""><input class=checkbox type=checkbox v-model=isSelectAll _v-36e8c904=""><em _v-36e8c904="">全选</em></label></div> <div class=total _v-36e8c904="">合计：<span _v-36e8c904="">￥{{total_amount}}</span></div> <a class=btn @click=toOrder _v-36e8c904="">结算</a> </div> </div> '}});