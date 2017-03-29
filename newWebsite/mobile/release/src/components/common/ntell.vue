<script>
	import { tel } from 'vuex_path/getters.js';

	export default {
		vuex: {
			getters: {
				tel
			}
		}
	}
</script>

<template>
	<div class="new_tel">
		<p style="font-weight:normal;">美容老师1对1教你解决肌肤问题</p>
		<p>
			<a :href="'tel:' + tel" ><img src="/public/images/detail/on_tel.png" alt="" /></a>
			<a v-link="{ name: 'shoppingCart' }"><img src="/public/images/detail/on_zx.png" alt="" /></a>
		</p>
	</div>
</template>