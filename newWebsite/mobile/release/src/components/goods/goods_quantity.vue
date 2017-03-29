<script>
	export default {
		props: {
			goods: {
				type: Object
			}
			// quantity: {
			// 	type: Number,
			// 	required: true,
			// 	twoWay: true
			// }
		},
		computed: {
			qty() {
				return this.goods.goods_number || this.goods.qty
			}
		},
		methods: {
			minus() {
				this.$dispatch('quantityMinus', this.goods);
			},
			add() {
				this.$dispatch('quantityAdd', this.goods);
			}
		}
	}
</script>

<template>
	<div class="goods-qty">
		<span @click="minus">-</span>
		<span><input type="text" readonly v-model="qty" /></span>
		<span @click="add">+</span>
	</div>
</template>

<style>
	.goods-qty{height: 1.8rem; text-align: center; display: inline-table;}
	.goods-qty span{width: 1.8rem; height: 100%; background: #f2f2f2; display: table-cell; vertical-align: middle;}
	.goods-qty input{width: 2.4rem; height: 100%; border: 1px solid #fff; border-width: 0 1px; background: #fbfbfb; text-align: center; display: inline-block; vertical-align: top;}
</style>