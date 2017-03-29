<script>
	// import { MessageBox } from 'mint-ui';

	export default {
		data() {
			return {
				loading: false,
				regionIndex: 1,
				regionList: '',
				regionData: [
					{id: 0, name: ''},
					{id: 0, name: ''},
					{id: 0, name: ''},
					{id: 0, name: ''}
				],
				selectTown: false
			}
		},
		methods: {
			show(id) {
				$(this.$el).addClass('on');
				if(this.regionData[2].id == 0){
					this.regionList = '';
				}
				if(typeof id != 'undefined'){
					if(id > 0){
						this.selectTown = true;
						this.regionList = '';
						this.findRegion(id);
					}else{
						window.alert('你还没填写所在区域信息');
						this.close();
					}
				}else{
					this.selectTown = false;
					this.regionIndex = 1;
					this.findRegion(1);
				}
			},
			back() {
				this.regionIndex -= 1;
				if(this.selectTown){
					this.close();
					return;
				}
				if(this.regionIndex <= 0){
					this.close();
					return;
				}
				this.findRegion(this.regionIndex);
			},
			close() {
				$(this.$el).removeClass('on');
				this.$dispatch('setRegion', this.regionData);
			},
			findRegion(id) {
				if(this.loading){
					return;
				}
				this.loading = true;
				let data = {
					parent_id: id
				}
				this.$http.post('/Region/Lists.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.regionList = res.data;
						// 如果街道没有数据
						if(this.selectTown && res.data.length == 0){
							// MessageBox({
							// 	message: '暂无街道信息'
							// });
							window.alert('暂无街道信息');
							this.close();
						}
						this.$nextTick(() => {
							this.loading = false;
						});
					}else{
						this.loading = false;
					}
				}, () => {
					this.loading = false;
				});
			},
			select(region) {
				let id = region.region_id;
				let index = region.region_type;
				this.regionIndex = index;
				this.regionData[index - 1].id = id;
				this.regionData[index - 1].name = region.region_name;
				if(index >= 3){
					// 重置街道数据
					if(this.selectTown == false){
						this.regionData[3].id = 0;
						this.regionData[3].name = '';
					}
					this.close();
					return;
				}
				this.findRegion(id);
			}
		}
	}
</script>

<template>
	<div class="region-box">
		<div v-show="loading" class="loading"></div>
		<div class="region-hd">
			<span @click="back"><i class="font icon-arrow-left"></i><em>选择地区</em></span>
		</div>
		<ul>
			<li v-for="item in regionList" @click="select(item)">
				<span>{{item.region_name}}</span><i class="font icon-arrow-right"></i>
			</li>
		</ul>
	</div>
</template>

<style scoped>
	.region-box{width: 100%; height: 100%; background: #eee; position: fixed; top: 0; z-index: 9999; opacity: 0; visibility: hidden;}
	.region-box.on{opacity: 1; transition: 0.4s ease-out; visibility: visible;}

	.region-hd{height: 2.6rem; padding: 0 0.8rem; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.region-hd span{height: 100%; display: inline-flex; display: -webkit-inline-flex; align-items: center; -webkit-align-items: center;}
	.region-hd .font{margin-right: 0.4rem;}
	.region-hd span em{line-height: 1;}

	.region-box ul{height: 100%; background: #fff; overflow-y: scroll; -webkit-overflow-scrolling: touch;}
	.region-box li{height: 2.6rem; padding: 0 0.8rem; border-top: 1px solid #e1e1e1; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.region-box li:first-child{border-top: 0;}
	.region-box li span{flex: 1; -webkit-flex: 1; display: block;}
</style>