webpackJsonp([39],{419:function(e,i,a){var o,t,n={};a(420),o=a(422),t=a(423),e.exports=o||{},e.exports.__esModule&&(e.exports=e.exports.default);var r="function"==typeof e.exports?e.exports.options||(e.exports.options={}):e.exports;t&&(r.template=t),r.computed||(r.computed={}),Object.keys(n).forEach(function(e){var i=n[e];r.computed[e]=function(){return i}})},420:function(e,i,a){var o=a(421);"string"==typeof o&&(o=[[e.id,o,""]]);a(22)(o,{});o.locals&&(e.exports=o.locals)},421:function(e,i,a){i=e.exports=a(3)(),i.push([e.id,'.content[_v-6a3344e8]{margin:0 auto}.clearfix[_v-6a3344e8]:after{content:"";display:table;clear:both}.font-size-12[_v-6a3344e8]{font-size:12px!important}.block[_v-6a3344e8]{border-top:1px solid #e5e5e5;border-bottom:1px solid #e5e5e5;overflow:hidden;margin:10px 0;background-color:#fff;display:block;position:relative;font-size:14px}.block[_v-6a3344e8]:first-child{margin-top:0}.block.block-order[_v-6a3344e8]:last-of-type{margin-bottom:0}.block.block-order .header[_v-6a3344e8]{height:37px;box-sizing:border-box;padding:0 10px;padding-left:10px;line-height:37px}hr[_v-6a3344e8]{margin:10px 0;border:0;border-top:1px solid #e5e5e5}hr.margin-0[_v-6a3344e8]{margin:0}hr.left-10[_v-6a3344e8]{margin-left:10px}.block.border-top-0[_v-6a3344e8]{border-top:0}.block.border-bottom-0[_v-6a3344e8]{border-bottom:0}.block.block-list[_v-6a3344e8]{margin:0;padding:0;padding-left:10px;list-style:none;font-size:14px;box-sizing:border-box}.name-card[_v-6a3344e8]{margin-left:0;width:auto;padding:5px 0;overflow:hidden;position:relative}.name-card.name-card-3col[_v-6a3344e8]{padding:8px 0;padding-right:85px}.block-list>.block-item[_v-6a3344e8]:first-child{border-top:0 none}.name-card .thumb[_v-6a3344e8]{width:60px;height:60px;float:left;position:relative;margin-left:auto;margin-right:auto;overflow:hidden;background-size:cover}.name-card .thumb img[_v-6a3344e8]{position:absolute;margin:auto;top:0;left:0;right:0;bottom:0;width:auto;height:auto;max-width:100%;max-height:100%}.name-card .detail[_v-6a3344e8]{margin-left:68px;width:auto;position:relative}.name-card .detail a[_v-6a3344e8]{display:block}.name-card .detail h3[_v-6a3344e8]{margin-top:1px;color:#333;font-size:12px;line-height:16px;width:100%}.name-card.name-card-3col .right-col[_v-6a3344e8]{position:absolute;right:0;top:8px;width:125px;padding-right:10px;font-size:12px}.name-card.name-card-3col .right-col .price[_v-6a3344e8]{font-size:14px;color:#515151;text-align:right;line-height:16px}.name-card.name-card-3col .right-col .num[_v-6a3344e8]{font-weight:200;text-align:right;margin-top:3px;padding:0;color:#555}.name-card.name-card-3col .right-col .num .num-txt[_v-6a3344e8]{padding:0;line-height:22px;color:#515151}.block.block-order .bottom[_v-6a3344e8]{padding:10px;padding-left:10px;height:16px;font-size:14px;line-height:16px;box-sizing:initial}.opt-btn[_v-6a3344e8]{display:inline-block;margin-top:-6px;float:right}.btn[_v-6a3344e8]{display:inline-block;border-radius:3px;padding:5px 4px;text-align:center;margin:0;font-size:12px;cursor:pointer;line-height:1.5;-webkit-appearance:none;background-color:#fff;border:1px solid #e5e5e5;color:#999}.btn-green[_v-6a3344e8]{color:#fff;background-color:#06bf04;border-color:#03b401}.opt-btn .btn[_v-6a3344e8]{margin-left:5px;padding:4px;text-align:center;line-height:19px;width:60px;height:28px;box-sizing:border-box}',""])},422:function(e,i,a){"use strict";function o(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(i,"__esModule",{value:!0});var t=a(151),n=o(t);i.default={ready:function(){},route:{data:function(){document.title="兑换记录",this.findExchangeList()}},data:function(){return{exchange:[],loading:!0,loaded:!0,page:1,list:[],loadTry:0,loadingText:"加载中..."}},methods:{findExchangeList:function(){var e=this;this.loading=!0;var i={pageSize:this.limit?this.limit:8,page:this.page};this.$http.post("/Integral/exchangeList.json",i).then(function(i){i=i.json(),1==i.status?(e.loaded=!1,e.$nextTick(function(){var a=!0,o=!1,t=void 0;try{for(var r,d=(0,n.default)(i.data.list);!(a=(r=d.next()).done);a=!0){var l=r.value;e.exchange.push(l)}}catch(e){o=!0,t=e}finally{try{!a&&d.return&&d.return()}finally{if(o)throw t}}0==i.data.list.length?(e.loadingText="没有更多了",e.loading=!0):e.loading=!1})):e.loading=!1},function(){e.loadTry++,e.loading=!1,e.loadTry>=3&&(e.loading=!0)})},loadMore:function(){this.page+=1,this.findExchangeList()}}}},423:function(e,i){e.exports=' <div class=container _v-6a3344e8=""> <div v-show=loaded class=loading _v-6a3344e8=""></div> <div class=content _v-6a3344e8=""> <div id=order-list-container _v-6a3344e8=""> <div class="js-list b-list" _v-6a3344e8=""> <ul v-infinite-scroll=loadMore() infinite-scroll-disabled=loading _v-6a3344e8=""> <li v-for="item in exchange" class="block block-order animated" _v-6a3344e8=""> <div class=header _v-6a3344e8=""> <span class=font-size-12 _v-6a3344e8="">订单号：{{item.order_sn}}</span> </div> <hr class="margin-0 left-10" _v-6a3344e8=""> <div class="block block-list border-top-0 border-bottom-0" _v-6a3344e8=""> <div class="block-item name-card name-card-3col clearfix" _v-6a3344e8=""> <a href=javascript:; class=thumb _v-6a3344e8=""> <img :src=item.goods_info.image[0].img_url _v-6a3344e8=""> </a> <div class=detail _v-6a3344e8=""> <a href=javascript:; _v-6a3344e8=""> <h3 _v-6a3344e8="">{{item.goods_info.goods_name}}</h3> </a> </div> <div class=right-col _v-6a3344e8=""> <div class=price _v-6a3344e8=""><p class=goods-points _v-6a3344e8="">{{item.goods_info.point}}积分 + ￥{{item.goods_info.price}}</p></div> <div class=num _v-6a3344e8="">×<span class=num-txt _v-6a3344e8="">{{item.goods_number}}</span></div> </div> </div> </div> <hr class="margin-0 left-10" _v-6a3344e8=""> <div class=bottom _v-6a3344e8=""> <span class=font-size-12 _v-6a3344e8="">兑换时间： {{item.adddate}}</span> <div class=opt-btn _v-6a3344e8=""> </div> </div> </li> </ul> <div class=load-more _v-6a3344e8="">{{loadingText}}</div> </div> </div> </div> </div> '}});