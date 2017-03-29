<script>
	export default {
		ready() {

		},
		data() {
			
		}
	}
</script>

<template>
	<div class="order-list">
		<label class="order-row input-item"><span>收货人</span><input type="text" /></label>
		<label class="order-row input-item"><span>联系电话</span><input type="text" /></label>
		<label class="order-row input-item"><span>所在区域</span><input id="inputRegion" type="text" /></label>
		<label class="order-row input-item input-textarea"><span>详细地址</span><textarea></textarea></label>
	</div>
	<dl class="order-item select-box">
		<dt class="order-hd">
			<span>属性</span>
			<i class="font icon-arrow-bottom"></i>
		</dt>
		<dd>
			<label><span>公司</span><input class="checkbox" type="checkbox" /></label>
			<label><span>家庭</span><input class="checkbox" type="checkbox" /></label>
			<label><span>代收</span><input class="checkbox" type="checkbox" /></label>
			<label><span>物业</span><input class="checkbox" type="checkbox" /></label>
		</dd>
	</dl>
</template>