<script>
	var imagesLoaded = require('imagesloaded');

	export default {
		route: {
			data() {
				Promise.all([this.loadBrandDetail(), this.loadBrandList()]);
			}
		},
		ready() {
			
		},
		data() {
			return {
				article: {},
				loading: false,
				loadTry: 0,
				loadingText: '加载中...',
				page: 1,
				brandList: []
			}
		},
		methods: {
			parseImg() {
				$('#brand_detail_content img').each(function(){
					var width = $(this).attr('width');
					var height = $(this).attr('height');
					var style = $(this).attr('style');
					if(width != undefined){
						$(this).removeAttr('width');
					}
					if(height != undefined){
						$(this).removeAttr('height');
					}
					if(style != undefined){
						style = style.replace(/(width[\s]*:[\s]*[\s]*[0-9]+[\s]*[px|em]+[;]*)/i,'');
						style = style.replace(/(height[\s]*:[\s]*[\s]*[0-9]+[\s]*[px|em]+[;]*)/i,'');
						$(this).attr('style',style);
					}
				});
			},
			loadBrandDetail() {
				let data = {
					id: this.$route.params.id
				}
				return this.$http.post('/Brand/brand_detail.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						this.article = res.data;
					}
					this.$nextTick(() => {
						imagesLoaded('#brand_detail_content', () => {
							this.parseImg();
						});
					});
				});
			},
			loadBrandList() {
				this.loading = true;
				let data = {
					page: this.page
				}
				return this.$http.post('Brand/brand_list.json', data).then((res) => {
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
                this.loadBrandList();
            }
		}
	}
</script>

<template>
	<div class="brand_com">
		<div class="lis_tit">
			<h3 id="info_title">{{article.title}}</h3>
		</div>
		<div class="lis_head">
			<!-- <span class="back" v-link="{ name: 'brand' }"><i class="font icon-arrow-left"></i><em>返回</em></span> -->
			<span class="tim">{{article.create_date}}</span>
		</div>
		<div class="bann" id="brand_detail_content">
			{{{article.content}}}
        </div>

		<a v-link="{ name: 'brand' }" class="back_line"><i class="font icon-arrow-left"></i>返回活动列表</a>

		<div class="rela_read">
			<h4>相关阅读</h4>
			<ul class="brand-list" v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
				<li v-for="item in brandList">
					<a v-link="{ name: 'brandDetail', params: {id: item.id} }">
						<span>{{item.short_title}}</span><i class="font icon-arrow-right"></i>
					</a>
				</li>
			</ul>
			<div class="load-more">{{loadingText}}</div>
		</div>

	</div>
</template>

<style scoped>
	.brand_com{padding: 0 0.8rem 1rem;}
	.brand_com .lis_tit h3{padding: 1rem 0; color: #3a3a3a; border-bottom: 1px solid #d6d6d6;}
	.brand_com .lis_head{padding: 0.8rem 0; text-align: right;}
	/*.brand_com .lis_head{height: 2.6rem; font-size: 0.9rem; display: flex; -webkit-display: flex; justify-content: space-between; -webkit-justify-content: space-between; align-items: center; -webkit-align-items: center;}*/
	/*.brand_com .lis_head span{line-height: 1; display: flex; -webkit-display: flex; align-items: center; -webkit-align-items: center;}*/

	.brand_com .bann{padding: 0.4rem 0 1.2rem;}
	.brand_com .bann img{width:100%;max-width: 575px;height:auto;}
	.brand_com .brand_wrap p{font-size: 1em;text-indent: 2em;line-height: 2em;color:#717171;}
	.brand_com .brand_wrap p.hea_t{text-align: center;text-indent: 0em;color:#9c9c9c;}

	.brand_com .back_line{display: flex; display: -webkit-flex; justify-content: center; -webkit-justify-content: center; align-items: center; -webkit-align-items: center; position: relative; margin: 1.4rem 0 1.4rem;}
	.brand_com .back_line:after{position: absolute;content:"";top:50%;left:0;width:30%;height:1px;margin-top:-0.5px;background: #d4d4d4;}
	.brand_com .back_line:before{position: absolute;content:"";top:50%;right:0;width:30%;height:1px;margin-top:-0.5px;background: #d4d4d4;}
	.brand_com .back_line .font{font-weight: bold; color: #d0393e; margin-right: 0.4rem;}

	.rela_read h4{font-size: 1rem; color:#3a3a3a; padding:0.4rem 0 0.4rem 0.4rem; border-bottom: 0.1em solid #3a3a3a;}
	.brand-list li a{height: 2.6rem; border-bottom: 1px solid #e1e1e1; font-size: 0.9rem; color: #878787; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
	.brand-list li span{padding-right: 2rem; flex: 1; -webkit-flex: 1; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
</style>