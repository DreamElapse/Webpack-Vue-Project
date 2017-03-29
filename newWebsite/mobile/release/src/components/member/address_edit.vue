<script>
	import { isLogin, defaultAddress } from 'vuex_path/getters.js';
	import { updateAppHeader, updateAddress, getDefaultAddress } from 'vuex_path/actions.js';

	import SelectRegion from './select_region.vue';

	export default {
		components: {
			SelectRegion
		},
		vuex: {
			getters: {
				isLogin,
				defaultAddress
			},
			actions: {
				updateAppHeader,
				updateAddress,
				getDefaultAddress
			}
		},
		route: {
			data() {
				this.address_id = parseInt(this.$route.params.id);

				this.updateAppHeader({
					type: 2,
					content: this.isLogin == false ? '填写地址' : this.address_id < 0 ? '添加地址' : '编辑地址'
				});
				if(this.address_id > 0){
					let data = {
						address_id: this.address_id
					}
					this.$http.post('/UserAddress/Info.json', data).then((res) => {
						res = res.json();
						if(res.status == 1){
							this.consignee = res.data.consignee;
							this.mobile = res.data.mobile;
							this.isChangeTel=res.data.mobile;
							this.regionData[0].id = res.data.province;
							this.regionData[0].name = res.data.province_name;
							this.regionData[1].id = res.data.city;
							this.regionData[1].name = res.data.city_name;
							this.regionData[2].id = res.data.district;
							this.regionData[2].name = res.data.district_name;
							this.regionData[3].id = res.data.town;
							this.regionData[3].name = res.data.town_name;
							this.address = res.data.address;
							this.attribute = res.data.attribute;
						}
					});
				}else if(this.address_id == 0){
					this.getDefaultAddress();
				}
			}
		},
		ready() {
			$('.order-hd').on('click', function(){
				$(this).toggleClass('on');
			});
		},
		data() {
			return {
				address_id: '',
				consignee: '',
				mobile: '',
				isChangeTel:'',
				regionData: [
					{id: 0, name: ''},
					{id: 0, name: ''},
					{id: 0, name: ''},
					{id: 0, name: ''}
				],
				districtID: 0,
				// province: '',
				// province_name: '',
				// city: '',
				// city_name: '',
				// district: '',
				// district_name: '',
				// town: '',
				// town_name: '',
				// region: '',
				address: '',
				attribute: '家庭'
			}
		},
		watch: {
			defaultAddress(val) {
				this.consignee = val.consignee;
				this.mobile = val.mobile;
				this.regionData[0].id = val.province;
				this.regionData[0].name = val.province_name;
				this.regionData[1].id = val.city;
				this.regionData[1].name = val.city_name;
				this.regionData[2].id = val.district;
				this.regionData[2].name = val.district_name;
				this.regionData[3].id = val.town;
				this.regionData[3].name = val.town_name;
				this.address = val.address;
				this.attribute = val.attribute;
			}
		},
		computed: {
			region() {
				let result = '';
				for(let i = 0; i < this.regionData.length; i++){
					if(i >= 3){
						break;
					}
					result += this.regionData[i].name;
				}
				return result;
			}
		},
		methods: {
			test_consignee() {
				if (this.consignee == '') {
					this.$dispatch('popup', '收货人不能为空');
					return false;
				}
				return true;
			},
			test_mobile() {
				if (this.mobile == '') {
					this.$dispatch('popup', '联系电话不能为空');
					return false;
				}
				if ((!/^1[34578]\d{9}$/.test(this.mobile))&&(this.mobile!=this.isChangeTel)) {
					this.$dispatch('popup', '联系电话格式不正确');
					return false;
				}
				return true;
			},
			test_region() {
				if (this.region == '') {
					this.$dispatch('popup', '所在区域不能为空');
					return false;
				}
				return true;
			},
			test_address() {
				if (this.address == '') {
					this.$dispatch('popup', '详细地址不能为空');
					return false;
				}
				return true;
			},
			validate() {
				for (let i of ['consignee', 'mobile', 'region', 'address']) {
					if (!this['test_' + i]()) {
						return false;
					}
				}
				return true;
			},
			selectRegion(e, id) {
				e.target.blur();
				if(typeof id != 'undefined'){
					this.$refs.select.show(id);
				}else{
					this.$refs.select.show();
				}
			},
			saveAddress() {
				if(this.validate() == false){
					return;
				}
				let data = {
					address_id: this.address_id,
					consignee: this.consignee,
					mobile: this.mobile,
					province: this.regionData[0].id,
					city: this.regionData[1].id,
					district: this.regionData[2].id,
					town: this.regionData[3].id,
					address: this.address,
					attribute: this.attribute,
				}
				this.$http.post('/UserAddress/Save.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						data.region = this.region;

						this.updateAddress(data);
						if(this.isLogin == true){
							this.$dispatch('popup', '地址保存成功');
						}
						window.history.back();
					}else{
						this.$dispatch('popup', res.msg);
					}
				});
			}
		},
		events: {
			setRegion(arr) {
				this.regionData = arr;
				this.districtID = arr[2].id;
			}
		}
	}
</script>

<template>
	<div class="container">
		<div class="order-list">
			<label class="input-item"><span>收货人</span><input type="text" v-model="consignee" /></label>
			<label class="input-item"><span>联系电话</span><input type="text" v-model="mobile" /></label>
			<label class="input-item"><span>所在区域</span><input type="text" v-model="region" @focus.prevent="selectRegion($event)" /></label>
			<label class="input-item"><span>街道</span><input type="text" v-model="regionData[3].name" @focus.prevent="selectRegion($event, districtID)" /></label>
			<label class="input-item input-textarea"><span>详细地址</span><textarea v-model="address"></textarea></label>
		</div>
		<dl class="order-item select-box">
			<dt class="order-hd">
				<span>属性</span>
				<i class="font icon-arrow-bottom"></i>
			</dt>
			<dd class="select-box">
				<label class="item"><span>公司</span><input class="checkbox" type="radio" value="公司" v-model="attribute" /></label>
				<label class="item"><span>家庭</span><input class="checkbox" type="radio" value="家庭" v-model="attribute" /></label>
				<label class="item"><span>代收</span><input class="checkbox" type="radio" value="代收" v-model="attribute" /></label>
				<label class="item"><span>物业</span><input class="checkbox" type="radio" value="物业" v-model="attribute" /></label>
			</dd>
		</dl>
		<a v-else class="btn btn-block" href="javascript:;" @click="saveAddress">提交</a>
		<select-region v-ref:select v-on:setRegion></select-region>
	</div>
</template>

<style scoped>
	.input-select{flex: 1; display: flex;}
	.input-select select{width: 25%;}

	.order-list{padding: 0 0.8rem; border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff; color: #696969;}
	.order-list .input-item:first-child{border-top: 0;}
	.input-item{height: 2.4rem; border-top: 1px solid #e1e1e1; display: flex; align-items: center;}
	.input-item span{width: 5rem; font-size: 0.9rem; display: block;}
	.input-item input, .input-item textarea{border: 0; font-size: 1em; flex: 1; -webkit-flex: 1; display: block;}
	.input-textarea{height: 5rem;}
	.input-textarea span{line-height: 2.4rem; align-self: flex-start;}
	.input-textarea textarea{padding: 0.6rem 0; font-size: 1em; align-self: stretch;}

	.order-item{margin: 0.4rem 0; border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff;}
	.order-hd.on .font{-webkit-transform: rotate(-180deg); transform: rotate(-180deg);}
	.order-hd.on + dd{display: block;}
	.order-hd{height: 2.4rem; padding: 0 0.8rem; font-size: 0.9rem; display: flex; align-items: center;}
	.order-hd span{flex: 1; -webkit-flex: 1; display: block;}
	.order-hd .font{margin-left: 0.8rem; transition: 0.6s ease-out;}
	.order-item dd{border-top: 1px solid #e1e1e1; display: none;}
	.order-item dd > :last-child{border-bottom: 0;}

	.select-box .item{height: 2.4rem; padding: 0 0.8rem; margin-bottom: 0.2rem; background: #f8f8f8; display: flex; align-items: center;}
	.select-box label span{font-size: 0.9rem; flex: 1; -webkit-flex: 1; display: block;}

	.btn-block{line-height: 2.8rem; background: #c50007; color: #fff; display: block;}
</style>