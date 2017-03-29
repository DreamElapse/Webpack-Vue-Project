<script>
	export default {
		ready() {
			let data = {
				cat_id: 0,
				page: 1
			}
			this.findPraiseList();
			// this.$http.post('/Praise/praise_list.json', data).then((res) => {
			// 	res = res.json();
			// 	if(res.status == 1){
			// 		this.$set('list', res.data);
			// 	}else{
			// 		this.$dispatch('popup', res.msg);
			// 	}
			// });

			$('.praise-item-hd span').click(function(){
			    $(this).addClass('on');
			});
		},
		data() {
			return {
				loading: false,
				loadTry: 0,
				loadingText: '加载中...',
				page: 1,
				list: []
			}
		},
		methods: {
			favor(a) {
				if(a.liked == 1){
					return;
				}
				this.$http.post('/Praise/praise_like.json', {id: a.article_id}).then((res) => {
					res = res.json();
					if(res.status == 1){
						a.liked = 1;
						a.fav_count = parseInt(a.fav_count) + 1;
					}
				});
			},
			findPraiseList() {
				this.loading = true;
				let data = {
					page: this.page
				}
				this.$http.post('/Praise/praise_list.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						for(let i of res.data){
							this.list.push(i);
						}
						this.$nextTick(() => {
							this.loading = false;
						});
					}
					if(res.status == 0){
						this.loadingText = '没有更多了';
						this.loading = true;
					}
				}, () => {
					this.loadTry++;
					this.loading = false;
					if(this.loadTry >= 3){
						this.loading = true;
					}
				});
			},
			loadMore() {
                this.page += 1;
                this.findPraiseList();
            }
		}
	}
</script>

<template>
	<div class="container">
		<!-- <div class="praise-filter">
			<a href="#javascript:;"><em>最新日记</em><i class="font icon-arrow-bottom"></i></a>
			<a href="#javascript:;"><em>热门推荐</em><i class="font icon-arrow-bottom"></i></a>
			<a href="#javascript:;"><em>功能筛选</em><i class="font icon-arrow-bottom"></i></a>
		</div> -->
		<ul class="praise-list"  v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
			<li class="clf" v-for="item in list">
				<div class="praise-item-hd">
					<h3>{{item.title}}</h3><span :class="{fav:item.liked=='1'}" @click="favor(item)"><i class="font icon-fav"></i><em>{{item.fav_count}}</em></span>
				</div>
				<a v-link="{ name: 'article', params: {id: item.article_id} }"><img v-lazy="item.file_url" alt="" /></a>
				<!-- {{{item.content}}} -->
				<!-- {{item.description}} -->
				<p class="p1">{{item.author}} {{item.author_age}} {{item.keywords}}</p>
				<p>{{item.description}}</p>
				<a class="btn" v-link="{ name: 'article', params: {id: item.article_id} }">点击阅读</a>
			</li>
			<!-- <li class="clf">
				<div class="praise-item-hd"><h3>去你的草莓鼻</h3><span><i class="font icon-fav"></i><em>2529</em></span></div>
				<img class="lazy" data-src="../public/images/praise/img.jpg" alt="" />
				<p class="p1">刘同学  网红  毛孔粗大    使用产品：去黑头套装</p>
				<p>如果你有像我一样的五官,你也可以像我一样的自信,曾经天真的以为,帅能够称霸世界........</p>
				<a class="btn" href="javascript:;">点击阅读</a>
			</li> -->
		</ul>
		<div class="load-more">{{loadingText}}</div>
	</div>
</template>

<style scoped>
	.praise-filter{margin: 1rem 0.8rem 0.4rem; border: 1px solid #e1e1e1; font-size: 0.9rem; display: flex;}
	.praise-filter a{line-height: 2.4rem; color: #676767; text-align: center; flex: 1;}
	.praise-filter a:after{content: ""; width: 1px; height: 1.4rem; margin-top: 0.5rem; background: #e1e1e1; float: right;}
	.praise-filter a:last-child:after{content: none;}
	.praise-filter a .font{margin-left: 0.4rem; font-size: 0.8rem;}

	.praise-list{background: #f5f5f5;}
	.praise-list li{padding: 0.8rem; margin-bottom: 0.8rem; background: #fff; font-size: 0.9rem;}
	.praise-item-hd{margin-bottom: 0.4rem; display: flex; align-items: center;}
	.praise-item-hd h3{font-size: 1rem; flex: 1;}
	.praise-item-hd span{color: #757575; transition: 1s;}
	.praise-item-hd span.fav{color: #d92123;}
	.praise-item-hd .font{margin-right: 0.2rem;}
	.praise-list li .p1{padding: 0.5rem 0 0.2rem; font-size: 0.8rem; color: #ec6468;}
	.praise-list li .btn{width: 6rem; line-height: 2.2rem; margin-top: 0.4rem; background: #000; color: #fff; float: right;}
	.praise-item-hd span.on{color:#f00;}
</style>