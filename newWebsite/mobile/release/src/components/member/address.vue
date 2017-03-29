<script>
	export default {
		ready() {
			
		},
		route: {
            data() {
            	this.$http.post('/UserAddress/Lists.json').then((res) => {
					res = res.json();
					if(res.status == 1){
						this.$set('addressList', res.data.list);
						for(let i of this.addressList){
							if(i.is_defaults == 1){
								this.defaultAddress = i;
								break;
							}
						}
					}
				});
            }
        },
		data() {
			return {
				loading: false,
				addressList: '',
				defaultAddress: ''
			}
		},
		methods: {
			setDefault(e, address) {
				e.preventDefault();
				if(address.is_defaults){
					return;
				}
				this.loading = true;
				let data = {
					address_id: address.address_id
				}
				this.$http.post('/UserAddress/SetDefaults.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.defaultAddress.is_defaults = 0;
						this.defaultAddress = address;
						this.defaultAddress.is_defaults = 1;
						this.$dispatch('popup', '设置默认地址成功');
					}else{
						this.$dispatch('popup', res.msg);
					}
					this.loading = false;
				}, () => {
					this.loading = false;
				});
			},
			deleteAddrss(address) {
				let result = window.confirm('确定要删除该地址吗？');
				if(result == false){
					return;
				}
				let data = {
					address_id: address.address_id
				}
				this.$http.post('/UserAddress/Delete.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.addressList.$remove(address);
						this.$dispatch('popup', '地址删除成功');
					}else{
						this.$dispatch('popup', res.msg);
					}
				});
			}
		}
	}
</script>

<template>
	<div class="container">
		<div v-show="loading" class="loading"></div>

		<a class="add-address" v-link="{ name: 'addressEdit', params: {id: -1} }">+ 添加新地址</a>
		<ul class="address-list">
			<li v-for="item in addressList">
				<p>
					<em>收货人：</em>
					<span>{{item.consignee}}</span><span class="fr">{{item.mobile}}</span>
				</p>
				<p>
					<em>收货地址：</em>
					<span>[{{item.attribute}}]{{item.province_name}}{{item.city_name}}{{item.district_name}}{{item.town_name}}{{item.address}}</span>
				</p>
				<div class="address-action">
					<label v-if="item.is_defaults" :class="{on:item.is_defaults}" @click="setDefault($event, item)"><input class="checkbox" type="checkbox" checked /><em>默认地址</em></label>
					<label v-else @click="setDefault($event, item)"><input class="checkbox" type="checkbox" /><em>设为默认</em></label>
					<div>
						<a v-link="{ name: 'addressEdit', params: {id: item.address_id} }"><i class="font icon-edit"></i><em>编辑</em></a>
						<a href="javascript:;" @click="deleteAddrss(item)"><i class="font icon-delete"></i><em>删除</em></a>
					</div>
				</div>
			</li>
		</ul>
	</div>
</template>

<style scoped>
	.container{background: #f8f8f8;}

	.add-address{line-height: 3rem; border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff; color: #c6000c; text-align: center; display: block;}
	.address-list li{padding: 0.6rem 0.8rem 0; margin: 0.4rem 0; border: 1px solid #e1e1e1; border-width: 1px 0; background: #fff; font-size: 0.9rem;}
	.address-list li p{line-height: 1.6;}
	.address-action{height: 2.4rem; margin-top: 0.4rem; border-top: 1px solid #e1e1e1;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;
	}
	.address-action label.on{color: #c50007;}
	.address-action label input{margin-right: 0.2rem; vertical-align: middle;}
	.address-action label em{vertical-align: middle;}
	.address-action > div{height: 100%; text-align: right; flex: 1; -webkit-flex: 1;}
	.address-action > div a{height: 100%; color: #696969; margin-left: 1rem; display: inline-table;}
	.address-action a .font{padding-right: 0.2rem; font-size: 1rem; display: table-cell; vertical-align: middle;}
	.address-action a em{display: table-cell; vertical-align: middle;}
</style>