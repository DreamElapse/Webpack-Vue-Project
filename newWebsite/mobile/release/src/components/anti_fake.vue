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
				type: 2,
				content: '防伪打假'
			});
		}
	}
</script>

<template>
	<div class="container">
		<img src="/public/images/anti-fake/img_01.jpg" alt="" />
		<img src="/public/images/anti-fake/img_02.jpg" alt="" />
		<img src="/public/images/anti-fake/img_03.jpg" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_04.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_05.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_06.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_07.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_08.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_09.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_10.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_11.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_12.jpg'" alt="" />
		<img v-lazy="'/public/images/anti-fake/img_13.jpg'" alt="" />
		<ol class="explain">
			<li><em>1、</em><p>“韩国瓷肌”是我公司之注册商标，韩国瓷肌正品上均贴有此商标，无此商标均为假货。</p></li>
			<li><em>2、</em><p><span>www.chinaskin.cn</span>是销售韩国瓷肌产品的唯一官方网站。</p></li>
			<li><em>3、</em><p>韩国瓷肌推广网站：<br />
							①<span>www.chinaskin.net</span>  ②<span>www.chinaskin.org</span>
							③<span>www.chinaskin.net.cn</span></p>
			</li>
			<li><em>4、</em><p>除了以上官网、推广网站及相关授权渠道外，其他网站销售的韩国瓷肌产品均为假冒伪劣产品。</p></li>
			<li><em>5、</em><p>韩国瓷肌产品全国订购热线为：<span>400 1022 888</span></p></li>
		</ol>
	</div>
</template>

<style scoped>
	.explain{padding: 0 0.8rem 0 1.4rem; font-size: 0.8rem; color: #7d7e80;}
	.explain li{padding: 0.4rem 0; border-bottom: 1px dashed #ddd; display: flex;}
	.explain li em{align-self: stretch;}
	.explain li span{color: #f90925;}
</style>