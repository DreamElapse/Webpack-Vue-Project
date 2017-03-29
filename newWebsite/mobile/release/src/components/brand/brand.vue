<script>
	export default {
		ready() {
			this.$http.post('/Brand/brandVideoList.json').then((res) => {
				res = res.json();
				if(res.status == 1){
					this.videoList = res.data;
					this.video = res.data[0];
				}
			});

			this.findBrandList();
		},
		data() {
			return {
				video: {},
				videoList: '',
				loading: false,
				loadTry: 0,
				loadingText: '加载中...',
				page: 1,
				brandList: []
			}
		},
		methods: {
			switchVideo(index) {
				this.video = this.videoList[index];
			},
			findBrandList() {
				this.loading = true;
				let data = {
					page: this.page
				}
				this.$http.post('/Brand/brand_list.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						for(let i of res.data){
							this.brandList.push(i);
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
                this.findBrandList();
            }
		}
	}
</script>

<template>
	<div>
		<div class="brand-video">
	        <video controls poster="{{video.file_url}}" :src="video.video_url"></video>
		</div>
		<dl class="brand-video-list">
			<dt>媒体快讯 LATEST NEWS</dt>
			<dd>
				<ul>
					<li v-for="item in videoList" @click="switchVideo($index)"><img :src="item.file_url" alt="" /></li>
				</ul>
			</dd>
		</dl>
		<ul class="brand_wrap" v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
			<li v-for="item in brandList">
				<h2><a v-link="{ name: 'brandDetail', params: {id: item.id} }">{{item.short_title}}</a></h2>
				<div class="brand-wrap-img">
					<a v-link="{ name: 'brandDetail', params: {id: item.id} }"><img :src="item.thumb_one" alt="" /></a>
					<a v-link="{ name: 'brandDetail', params: {id: item.id} }"><img :src="item.thumb_two" alt="" /></a>
					<a v-link="{ name: 'brandDetail', params: {id: item.id} }"><img :src="item.thumb_three" alt="" /></a>
				</div>
				<p>{{item.create_date}}</p>
			</li>
		</ul>
		<div class="load-more">{{loadingText}}</div>
	</div>
</template>

<style scoped>
	.brand_wrap{width: 100%; overflow: hidden; padding: 0 0.8rem 1rem; background: #fff;}
	.brand_wrap li{width: 100%; overflow: hidden; border-top: 1px solid #e9edf1;}
	.brand_wrap li:nth-child(0){border-top :0;}
	.brand_wrap li h2{font-size: 0.9rem; color: #000; margin: 0.8rem 0 1.2rem;}
	.brand-wrap-img{display: flex; display: -webkit-flex;}
	.brand-wrap-img > a{flex: 1; -webkit-flex: 1; display: block;}
	.brand-wrap-img img{width: 95.8%; max-width: 192px;}
	.brand_wrap li p{padding: 1rem 0; font-size: 0.8rem; color:#a6a6a6; text-align: right;}
	
	.brand-video video{width: 100%; display: block;}
	.brand-video p{line-height: 2; padding: 0 0.8em; background: #2d2d2d; color: #fff; text-align: right;}
	.brand-video-list dt{padding: 0.8rem; color: #222;}
	.brand-video-list dd{padding-bottom: 1em; overflow-x: auto; -webkit-overflow-scrolling: touch;}
	.brand-video-list dd ul{padding: 0 0.8em; display: table; white-space: nowrap;}
	.brand-video-list dd li{width: 10em; display: inline-block; margin: 0 2px;}
	.brand_wrap ul li:first-child{border-color: #000;}
</style>