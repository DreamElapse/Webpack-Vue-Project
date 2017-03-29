import Vue from 'vue';
import VueRouter from 'vue-router';
import VueResource from 'vue-resource';
// import VueAsyncData from 'vue-async-data';
import VueTouch from 'vue-touch';
import VueLazyload from 'vue-lazyload';


import App from './App.vue';
import Index from './components/index.vue';


import View from './components/common/view.vue';
import Act from './components/common/act.vue';


Vue.use(VueRouter);
Vue.use(VueResource);
Vue.http.options.emulateHTTP = true;
Vue.http.options.emulateJSON = true;
// Vue.use(VueAsyncData);
Vue.use(VueTouch);
Vue.use(VueLazyload, {
	// loading: '/public/images/common/transparent.png',
	// error: '/public/images/common/transparent.png'
});


import {
	InfiniteScroll
} from 'mint-ui';
Vue.use(InfiniteScroll);


//开启debug模式
Vue.config.debug = true;


var router = new VueRouter({
	// hashbang: false, history: true
	hashbang: false
});

router.beforeEach(() => {
	// scrollTo(0, 0);
});

router.afterEach(() => {
	scrollTo(0, 0);
});

// 每条路由规则应该映射到一个组件。这里的“组件”可以是一个使用 Vue.extend创建的组件构造函数，也可以是一个组件选项对象。
router.map({
	'/index': {
		name: 'index',
		component: Index
	},
	'/view/:type/:name': {
		name: 'view',
		component: View
	},
	'/topic/:name': {
		name: 'topic',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/goods/goods_detail.vue'));
			}, 'goodsDetail');
		}
	},
	'/goods-act/:name': {
		name: 'act',
		component: Act
	},
	'/brand': {
		name: 'brand',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/brand/brand.vue'));
			}, 'brand');
		}
	},
	'/brandArticles': {
		name: 'brandArticles',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/brand/brand_articles.vue'));
			}, 'brandArticles');
		}
	},
	'/brand/:id': {
		name: 'brandDetail',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/brand/brand_detail.vue'));
			}, 'brandDetail');
		}
	},
	'/regist': {
		name: 'regist',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/login/regist.vue'));
			}, 'regist');
		},
		hideRightBtn: true
	},
	'/login': {
		name: 'login',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/login/login.vue'));
			}, 'login');
		},
		hideRightBtn: true
	},
	'/findPassword': {
		name: 'findPassword',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/login/password.vue'));
			}, 'findPassword');
		},
		hideRightBtn: true
	},
	'/member': {
		name: 'member',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/member/member.vue'));
			}, 'member');
		}
	},
	'/orderDetail/:id': {
		name: 'orderDetail',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/order_detail.vue'));
			}, 'orderDetail');
		}
	},
	'/praise': {
		name: 'praise',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/praise.vue'));
			}, 'praise');
		}
	},
	'/praise-article/:id': {
		name: 'article',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/article.vue'));
			}, 'article');
		}
	},
	'/goods-list/:cid/:package': {
		name: 'goodsList',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/goods/goods_list.vue'));
			}, 'goodsList');
		}
	},
	'/goods-topic/:name': {
		name: 'topic',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/goods/goods_detail.vue'));
			}, 'goodsDetail');
		}
	},
	'/goods-detail/:id/:package': {
		name: 'goodsDetail',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/goods/goods_detail.vue'));
			}, 'goodsDetail');
		}
	},
	'/goodsInfo/:id/:view': {
		name: 'goodsInfo',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/goods/goods_info.vue'));
			}, 'goodsInfo');
		}
	},
	'/goodsComment/:id': {
		name: 'goodsComment',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/goods/goods_comment.vue'));
			}, 'goodsComment');
		}
	},
	'/freetryDetail/:id': {
		name: 'freetryDetail',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/freetry/detail.vue'));
			}, 'freetryDetail');
		}
	},
	'/freetryRule': {
		name: 'freetryRule',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/freetry/rule.vue'));
			}, 'freetryRule');
		}
	},
	'/freetryForm/:id': {
		name: 'freetryForm',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/freetry/form.vue'));
			}, 'freetryForm');
		}
	},
	'/shoppingCart': {
		name: 'shoppingCart',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/goods/shopping_cart.vue'));
			}, 'shoppingCart');
		}
	},
	'/order': {
		name: 'order',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/order.vue'));
			}, 'order');
		}
	},
	'/exchangeOorder/:id': {
		name: 'exchangeOorder',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/exchange_order.vue'));
			}, 'exchangeOorder');
		}
	},
	'/rotateOrder/:id/:prize': {
		name: 'rotateOrder',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/rotate_order.vue'));
			}, 'rotateOrder');
		}
	},
	'/paySuccess': {
		name: 'paySuccess',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/payment/pay_success.vue'));
			}, 'paySuccess');
		}
	},
	'/quickPay': {
		name: 'quickPay',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/payment/quick_pay.vue'));
			}, 'quickPay');
		}
	},
	'/payNotice': {
		name: 'payNotice',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/payment/pay_notice.vue'));
			}, 'payNotice');
		}
	},
	'/payGuide': {
		name: 'payGuide',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/payment/pay_guide.vue'));
			}, 'payGuide');
		}
	},


	'/address': {
		name: 'address',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/member/address.vue'));
			}, 'address');
		}
	},
	'/address/edit/:id': {
		name: 'addressEdit',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/member/address_edit.vue'));
			}, 'addressEdit');
		}
	},
	'/searTest': {
		name: 'searTest',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/sear_test.vue'));
			}, 'searTest');
		}
	},

	'/store': {
		name: 'store',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/store.vue'));
			}, 'store');
		}
	},
	'/anti-fake': {
		name: 'anti-fake',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/anti_fake.vue'));
			}, 'antiFake');
		}
	},
	'/check-code': {
		name: 'check-code',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/checkcode/checkcode.vue'));
			}, 'checkCode');
		}
	},
	'/checkin/:id': {
		name: 'checkin',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/checkin.vue'));
			}, 'checkin');
		}
	},
	'/checkin-rule': {
		name: 'checkin-rule',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/checkin_rule.vue'));
			}, 'checkinRule');
		}
	},
	'/user': {
		name: 'user',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/user.vue'));
			}, 'user');
		}
	},
	'/orderAll/:id': {
		name: 'orderAll',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/orderAll.vue'));
			}, 'orderAll');
		}
	},
	'/trades': {
		name: 'trades',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/trades.vue'));
			}, 'trades');
		}
	},
	'/promocodes': {
		name: 'promocodes',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/promocodes.vue'));
			}, 'promocodes');
		}
	},
	'/promocodes-history': {
		name: 'codesHistory',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/promocodes_history.vue'));
			}, 'codesHistory');
		}
	},
	'/codeExchange': {
		name: 'codeExchange',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/exchange.vue'));
			}, 'codeExchange');
		}
	},
	'/coupons': {
		name: 'coupons',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/coupons.vue'));
			}, 'coupons');
		}
	},
	'/buyer': {
		name: 'buyer',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/buyer.vue'));
			}, 'buyer');
		}
	},
	'/buyer_auth': {
		name: 'buyer_auth',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/buyer_auth.vue'));
			}, 'buyer_auth');
		}
	},
	'/myfocu': {
		name: 'myfocu',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/myfocus.vue'));
			}, 'myfocu');
		}
	},
	'/pointsstore': {
		name: 'pointsstore',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/pointsstore.vue'));
			}, 'pointsstore');
		}
	},
	'/pointsgood/:id/:package': {
		name: 'pointsGood',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/pointsgood.vue'));
			}, 'pointsGood');
		}
	},
	'/changepassword': {
		name: 'changepassword',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/changepassword.vue'));
			}, 'changepassword');
		}
	},
	'/upload_photo': {
		name: 'upload_photo',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/upload_photo.vue'));
			}, 'uploadPhoto');
		}
	},
	'/rotaryDraw': {
		name: 'rotaryDraw',
		component: function(resolve) {
			require.ensure([], function() {
				resolve(require('./components/usercenter/rotarydraw.vue'));
			}, 'rotaryDraw');
		}
	},
	'/goods_eval/:id':{
		name:'goods_eval',
		component:function(resolve){
			require.ensure([],function(){
				resolve(require('./components/goods-eval.vue'));
			},'goods_eval');
		},
		hideRightBtn:true
	},
	'/protect_right':{
		name:'protect_right',
		component:function(resolve){
			require.ensure([],function(){
				resolve(require('./components/protect-right.vue'));
			},'protect_right');
		},
		hideRightBtn:true
	},
	'/right_list':{
		name:'right_list',
		component:function(resolve){
			require.ensure([],function(){
				resolve(require('./components/right-list.vue'));
			},'right_list');
		},
		hideRightBtn:true
	},
	'/right_evaluate/:rid':{
		name:'right_evaluate',
		component:function(resolve){
			require.ensure([],function(){
				resolve(require('./components/right-evaluate.vue'));
			},'right_evaluate');
		},
		hideRightBtn:true
	},
	'/brand_subvay':{
		name:'brand_subvay',
		component:function(resolve){
			require.ensure([],function(){
				resolve(require('./components/brand_subvay.vue'));
			},'brand_subvay');
		},
		hideRightBtn:true
	}
});

router.redirect({ //定义全局的重定向规则。全局的重定向会在匹配当前路径之前执行。
	'*': "/index" //重定向任意未匹配路径到/index
});

// new Vue(app);//这是上一篇用到的，新建一个vue实例，现在使用vue-router就不需要了。
// 路由器需要一个根组件。

// var App = Vue.extend({});
// 现在我们可以启动应用了！
// 路由器会创建一个 App 实例，并且挂载到选择符 #app 匹配的元素上。
router.start(App, '#app');