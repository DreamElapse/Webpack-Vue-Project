<script>
	import { updateAppHeader } from 'vuex_path/actions.js';

	export default {
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		ready() {
			this.updateAppHeader({
				type: 1
			});
		}
	}
</script>

<template>
	<div class="prla">
		<a v-link="{ name: 'payNotice' }"></a><img src="/public/images/guide/img_01.jpg" alt="" />
	</div>
	<img src="/public/images/guide/img_02.jpg" alt="" />
	<img src="/public/images/guide/img_03.jpg" alt="" />
	<img src="/public/images/guide/img_04.jpg" alt="" />
	<img src="/public/images/guide/img_05.jpg" alt="" />
	<img src="/public/images/guide/img_06.jpg" alt="" />
	<img src="/public/images/guide/img_07.jpg" alt="" />
	<img src="/public/images/guide/img_08.jpg" alt="" />
	<img src="/public/images/guide/img_09.jpg" alt="" />
	<img src="/public/images/guide/img_10.jpg" alt="" />
	<img src="/public/images/guide/img_11.jpg" alt="" />
	<img src="/public/images/guide/img_12.jpg" alt="" />
	<img src="/public/images/guide/img_13.jpg" alt="" />
	<img src="/public/images/guide/img_14.jpg" alt="" />
	<img src="/public/images/guide/img_15.jpg" alt="" />
	<img src="/public/images/guide/img_16.jpg" alt="" />
	<img src="/public/images/guide/img_17.jpg" alt="" />
	<img src="/public/images/guide/img_18.jpg" alt="" />
	<img src="/public/images/guide/img_19.jpg" alt="" />
	<img src="/public/images/guide/img_20.jpg" alt="" />
	<img src="/public/images/guide/img_21.jpg" alt="" />
	<img src="/public/images/guide/img_22.jpg" alt="" />
	<img src="/public/images/guide/img_23.jpg" alt="" />
	<img src="/public/images/guide/img_24.jpg" alt="" />
	<img src="/public/images/guide/img_25.jpg" alt="" />
	<img src="/public/images/guide/img_26.jpg" alt="" />
	<img src="/public/images/guide/img_27.jpg" alt="" />
	<img src="/public/images/guide/img_28.jpg" alt="" />
	<img src="/public/images/guide/img_29.jpg" alt="" />
	<img src="/public/images/guide/img_30.jpg" alt="" />
	<img src="/public/images/guide/img_31.jpg" alt="" />
	<img src="/public/images/guide/img_32.jpg" alt="" />
	<img src="/public/images/guide/img_33.jpg" alt="" />
	<img src="/public/images/guide/img_34.jpg" alt="" />
</template>

<style>
	.prla{position: relative;}
	.prla a{position: absolute;top:10%;right:4%;width:27%;height:15%;display:inline-block;}
</style>