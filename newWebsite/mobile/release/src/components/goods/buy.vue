<script>
	import { addGoodsToCart } from 'vuex_path/actions.js';
	import quantity from './quantity.vue';

	export default {
		components: {
			quantity
		},
		vuex: {
			actions: {
				addGoodsToCart
			}
		},
		props: {
			id: {},
			ispackage: '',
			isstatus: {
				type: Number
			},
			quantity: {
				type: Number
			}
		},
		ready(){

		},
		methods: {
			buy(data) {
				this.addGoodsToCart(data, () => {
					this.$route.router.go({name: 'shoppingCart'});
				});
			},
			addCollect(a) {
				// console.log(typeof this.ispackage)
				if(this.isstatus == 1){
					let data ={
						goods_id: this.id,
						url: this.$route.path,
						type: this.ispackage == 1 ? 2 : 1
					}
					this.$http.post('/Goods/collectGoods.json',data).then((res) => {
						res = res.json();
						this.$dispatch('popup', res.data);
						this.isstatus = 2 ;
						this.$dispatch('isStatus', 2)
					});
				}else if(this.isstatus == 2){
					let data ={
						goods_id: this.id,
						url: this.$route.path,
						type: this.ispackage == 1 ? 2 : 1
					}
					this.$http.post('/Goods/collectGoods.json',data).then((res) => {
						res = res.json();
						this.$dispatch('popup', res.data);
						this.isstatus = 1 ;
						this.$dispatch('isStatus', 1)
					});
				}else{
					let data ={
						goods_id:this.id
					}
					// this.isstatus = 0 ;
					this.$http.post('/Goods/isCollectGoods.json',data).then((res) => {
						res = res.json();
						this.$dispatch('popup', res.msg);
					});	
					
				}
			}
		},
		data(){
			return{
				collectGood: false
			}
		}
	}
</script>

<template>
	<div class="buy-qty">
		<span>购买数量</span>
		<quantity :class-name="'qty-ctrl'" :quantity.sync="quantity"></quantity>
	</div>
	<div class="buy-action">
		<a class="font icon-add-to-ShoppingCart" href="javascript:;" @click="addGoodsToCart({id: id, num: quantity})"></a>
		<span :class="{favou:isstatus == 1 }" href="javascript:;" @click="addCollect()"><i class="font icon-level"></i>收藏</span>
		<a class="btn" href="javascript:;" @click="buy({id: id, num: quantity})">立即购买</a>
	</div>
</template>

<style scoped>
.buy-qty{padding: 0.8rem; background: #fff; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
.buy-qty > span{font-size: 0.9rem; color: #b2b2b2; flex: 1; -webkit-flex: 1; display: block;}

.buy-qty .qty-ctrl{border: 1px solid #e1e1e1;}
.buy-qty .qty-ctrl span{width: 2rem; background: #fff; font-size: 1.2rem; color: #c3c3c3;}
.buy-qty .qty-ctrl input{width: 3rem; border-color: #e1e1e1; background: #fff; font-size: 1rem;}

.buy-action{width: 100%; height: 3rem; background: #ff7474; text-align: center; display: table;}
.buy-action > a{display: table-cell; vertical-align: middle;}
.buy-action > span{display: table-cell; vertical-align: middle;}
.buy-action .font{width: 30%; font-size: 2rem; color: #fff;}
.buy-action span{width: 30%; font-size: 1rem; color: #fff;background:#ffb03f;}
.buy-action span .font{font-size:1.2rem;margin-right:0.2rem;-webkit-animation: focusedScale .2s 1 ease 0s;-moz-animation: focusedScale .2s 1 ease 0s;-ms-animation: focusedScale .2s 1 ease 0s;-o-animation: focusedScale .2s 1 ease 0s;animation: focusedScale .2s 1 ease 0s;}
.icon-level:before{}
.buy-action span.favou{color:#f00;-webkit-animation: focusedScale .2s 1 ease 0s;-moz-animation: focusedScale .2s 1 ease 0s;-ms-animation: focusedScale .2s 1 ease 0s;-o-animation: focusedScale .2s 1 ease 0s;animation: focusedScale .2s 1 ease 0s;}
.buy-action span.favou .font{color:#f00;/* -webkit-animation: focused .2s 1 ease 0s;-moz-animation: focused .2s 1 ease 0s;-ms-animation: focused .2s 1 ease 0s;-o-animation: focused .2s 1 ease 0s;animation: focused .2s 1 ease 0s;  */}
.buy-action .btn{background: #da3737;}

@-webkit-keyframes focused {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}

	20% {
		transform: scale(0.8,0.8);
		-webkit-transform: scale(0.8,0.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	40% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	60% {
		transform: scale(1.1,1.1);
		-webkit-transform: scale(1.1,1.1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	80% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .8;
	}

	100% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}
}

@-moz-keyframes focused {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}

	20% {
		transform: scale(0.8,0.8);
		-webkit-transform: scale(0.8,0.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	40% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	60% {
		transform: scale(1.1,1.1);
		-webkit-transform: scale(1.1,1.1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	80% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .8;
	}

	100% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}
}

@-ms-keyframes focused {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}

	20% {
		transform: scale(0.8,0.8);
		-webkit-transform: scale(0.8,0.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	40% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	60% {
		transform: scale(1.1,1.1);
		-webkit-transform: scale(1.1,1.1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	80% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .8;
	}

	100% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}
}

@-o-keyframes focused {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}

	20% {
		transform: scale(0.8,0.8);
		-webkit-transform: scale(0.8,0.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	40% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	60% {
		transform: scale(1.1,1.1);
		-webkit-transform: scale(1.1,1.1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	80% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .8;
	}

	100% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}
}

@keyframes focused {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}

	20% {
		transform: scale(0.8,0.8);
		-webkit-transform: scale(0.8,0.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	40% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	60% {
		transform: scale(1.1,1.1);
		-webkit-transform: scale(1.1,1.1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .6;
	}

	80% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .8;
	}

	100% {
		transform: scale(1,1);
		-webkit-transform: scale(1,1);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: 1;
	}
}
@-webkit-keyframes focusedScale {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	20% {
		transform: scale(0.6,0.6);
		-webkit-transform: scale(0.6,0.6);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	40% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	60% {
		transform: scale(1.5,1.5);
		-webkit-transform: scale(1.5,1.5);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	80% {
		transform: scale(1.8,1.8);
		-webkit-transform: scale(1.8,1.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	100% {
		transform: scale(2,2);
		-webkit-transform: scale(2,2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}
}

@-moz-keyframes focusedScale {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	20% {
		transform: scale(0.6,0.6);
		-webkit-transform: scale(0.6,0.6);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	40% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	60% {
		transform: scale(1.5,1.5);
		-webkit-transform: scale(1.5,1.5);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	80% {
		transform: scale(1.8,1.8);
		-webkit-transform: scale(1.8,1.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	100% {
		transform: scale(2,2);
		-webkit-transform: scale(2,2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}
}

@-ms-keyframes focusedScale {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	20% {
		transform: scale(0.6,0.6);
		-webkit-transform: scale(0.6,0.6);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	40% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	60% {
		transform: scale(1.5,1.5);
		-webkit-transform: scale(1.5,1.5);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	80% {
		transform: scale(1.8,1.8);
		-webkit-transform: scale(1.8,1.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	100% {
		transform: scale(2,2);
		-webkit-transform: scale(2,2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}
}

@-o-keyframes focusedScale {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	20% {
		transform: scale(0.6,0.6);
		-webkit-transform: scale(0.6,0.6);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	40% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	60% {
		transform: scale(1.5,1.5);
		-webkit-transform: scale(1.5,1.5);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	80% {
		transform: scale(1.8,1.8);
		-webkit-transform: scale(1.8,1.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	100% {
		transform: scale(2,2);
		-webkit-transform: scale(2,2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}
}

@keyframes focusedScale {
	0% {
		transform: scale(0,0);
		-webkit-transform: scale(0,0);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	20% {
		transform: scale(0.6,0.6);
		-webkit-transform: scale(0.6,0.6);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .2;
	}

	40% {
		transform: scale(1.2,1.2);
		-webkit-transform: scale(1.2,1.2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	60% {
		transform: scale(1.5,1.5);
		-webkit-transform: scale(1.5,1.5);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	80% {
		transform: scale(1.8,1.8);
		-webkit-transform: scale(1.8,1.8);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}

	100% {
		transform: scale(2,2);
		-webkit-transform: scale(2,2);
		transform-origin: center center;
		-webkit-transform-origin: center center;
		opacity: .1;
	}
}


</style>