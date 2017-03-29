<script>
	export default {
		props: {
			className: '',
			goods: {},
			quantity: {
				type: Number,
				twoWay: true,
				default: 1
			}
		},
		methods: {
			minus() {
				if(this.quantity <= 1){
					return;
				}
				this.quantity -= 1;
				this.$dispatch('quantityChange', this.quantity, this.goods);
			},
			add() {
				this.quantity += 1;
				this.$dispatch('quantityChange', this.quantity, this.goods);
			}
		}
	}
</script>

<template>
	<div :class="['goods-qty', className]">
		<span @click="minus">-</span>
		<span><input type="text" readonly v-model="quantity" /></span>
		<span @click="add">+</span>
	</div>
</template>

<style>
	.goods-qty{height: 1.8rem; text-align: center; display: inline-table;}
	.goods-qty span{width: 1.8rem; height: 100%; background: #f2f2f2; display: table-cell; vertical-align: middle;}
	.goods-qty input{width: 2.4rem; height: 100%; border: 1px solid #fff; border-width: 0 1px; background: #fbfbfb; text-align: center; display: inline-block; vertical-align: top;}
</style>