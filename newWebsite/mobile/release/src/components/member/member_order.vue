<script>
	export default {
		ready() {
			this.loadOrder();
		},
		data() {
			return {
				loading: false,
                loadingText: '加载中...',
				page: 1,
				list: [],
				filterCondition: ''
			}
		},
		methods: {
			filter(e, expr = '') {
				$(e.currentTarget).addClass('on').siblings().removeClass('on');
				this.filterCondition = expr;
			},
			loadOrder() {
				let data = {
					page: this.page,
					pageSize: 5
				}
				this.$http.post('/Order/Lists.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						for(let i of res.data.list){
							this.list.push(i);
						}
						this.$nextTick(() => {
						    if(res.data.list.length == 0){
						        this.loadingText = '没有更多了';
						        this.loading = true;
						    }else{
						        this.loading = false;
						    }
						});
					}else{
						this.loading = false;
					}
				}, () => {
					this.loading = false;
				});
			},
			loadMore() {
			    this.page += 1;
			    this.loadOrder();
			}
		}
	}
</script>

<template>
	<div class="member-order">
		<div class="member-order-hd">
			<a class="on" href="javascript:;" @click="filter($event)">全部</a>
			<a href="javascript:;" @click="filter($event, 0)">货到付款</a>
			<a href="javascript:;" @click="filter($event, 1)">在线付款</a>
			<!-- <a href="javascript:;" @click="filter($event, 2)">未付款</a> -->
		</div>
		<ul class="member-order-list" v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
			<li :class="{'not-payment': item.pay_status == 0}" v-for="item in list | filterBy filterCondition in 'pay_status'" track-by="order_id">
				<a v-link="{ name: 'orderDetail', params: {id: item.order_sn} }">
					<div><strong>订单号: {{item.order_sn}}</strong><span class="date">{{item.add_date}}</span></div>
					<em>{{item.pay_status_name}}</em><i class="font icon-arrow-right"></i>
				</a>
			</li>
		</ul>
		<div class="load-more">{{loadingText}}</div>
	</div>
</template>

<style scoped>
	.member-order{padding: 1rem 0.8rem 0; border-top: 1px solid #e1e1e1; background: #fff;}
	.member-order-hd{font-size: 0.9rem; border-bottom: 2px solid #000;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center; justify-content: space-between; -webkit-justify-content: space-between;
	}
	.member-order-hd a{width: 30%; line-height: 2rem; border: 1px solid #c4c4c4; border-bottom: 0; color: #232323; text-align: center; display: block;}
	.member-order-hd a.on{border-color: #000; background: #000; color: #fff;}

	.member-order-list li{font-size: 0.9rem;}
	.member-order-list li a{height: 4rem; border-bottom: 1px solid #e1e1e1;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;
	}
	.member-order-list li div{
		flex: 1; -webkit-flex: 1;
	}
	.member-order-list li strong{font-weight: normal; display: block;}
	.member-order-list li .date{font-size: 0.8rem; color: #939393;}
	.member-order-list li em{line-height: 2;}
	.member-order-list li .font{margin-left: 0.8rem;}
	.member-order-list li.not-payment em{color: #c6000c;}
	.list-none{line-height: 8rem; font-size: 0.9rem; text-align: center;}
</style>