<script>
	export default {
		ready() {
			this.loadMsg();
		},
		data() {
			return {
				loading: false,
                loadingText: '加载中...',
				list: [],
				page: 1
			}
		},
		methods: {
			loadMsg() {
				let data = {
					page: this.page,
					page_size: 3
				}
				this.$http.post('/User/getInformations.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						for(let i of res.data){
							this.list.push(i);
						}
						this.$nextTick(() => {
						    if(res.data.length == 0){
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
				this.loadMsg();
			}
		}
	}
</script>

<template>
	<div class="member-message">
		<ul v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
			<li v-for="item in list">
				<a :href="item.url">
					<i class="font icon-msg-favorable"></i>
					<div><strong>{{item.title}}</strong><p>{{item.content}}</p></div>
					<span class="hl">1</span>
				</a>
			</li>
			<!-- <li>
				<i class="font icon-msg-notice"></i>
				<div><strong>系统公告</strong><p>今日优惠大促，补水套装买一送一赠品，免邮....</p></div>
			</li>
			<li>
				<i class="font icon-msg-logistics"></i>
				<div><strong>物流通知</strong><p>今日优惠大促，补水套装买一送一赠品，免邮....</p></div>
				<span class="hl">2</span>
			</li> -->
		</ul>
		<div class="load-more">{{loadingText}}</div>
	</div>
</template>

<style scoped>
	.member-message{background: #fff;}
	.member-message li a{height: 4.4rem; padding: 0 0.8rem; border-bottom: 1px solid #e1e1e1; font-size: 0.9rem;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;
	}
	.member-message li .font{width: 2.4rem; height: 2.4rem; line-height: 2.4rem; margin-right: 0.4rem; border-radius: 3rem; background: #d4d4d4; font-size: 1.8rem; color: #fff; text-align: center; display: inline-block;}
	.member-message li div{
		flex: 1; -webkit-flex: 1; display: block;
	}
	.member-message li strong{font-weight: normal;}
	.member-message li p{font-size: 0.8rem; color: #959595; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;}
	.member-message li .hl{width: 1.3rem; height: 1.3rem; line-height: 1.3rem; border-radius: 2rem; background: #c6000c; color: #fff; text-align: center; display: inline-block;}

	.load-more{line-height: 2.6rem; font-size: 0.9rem; text-align: center; display: block;}
</style>