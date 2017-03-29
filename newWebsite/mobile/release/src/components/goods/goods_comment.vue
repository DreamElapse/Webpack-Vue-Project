<script>
    import { setBuyOption } from 'vuex_path/actions.js';

	export default {
        vuex: {
            actions: {
                setBuyOption
            }
        },
		ready() {
            // let id = this.$route.params.id;
            this.loadComments();
		},
		data() {
			return {
                loading: false,
                loadingText: '加载中...',
                loadTry: 0,
                // gid: '',
                gid: this.$route.params.id,
                page: 1,
				comments: []
			}
		},
        props: {
            limit: {
                type: Number
            },
            commentList: {}
        },
        watch: {
            commentList(val) {
                this.comments = val;
            }
        },
        methods: {
            loadComments() {
                this.loading = true;
                if(parseInt(this.gid)){
                    // 商品评论
                    this.goodsComments();
                }else{
                    // 专题评论
                    if(this.commentList == undefined){
                        this.topicComments();
                    }
                }
            },
            goodsComments() {
                let data = {
                    gid: this.gid,
                    limit: this.limit ? this.limit : 5,
                    page: this.page
                }
                this.$http.post('/Goods/getGoodComment.json', data).then((res) => {
                    res = res.json();
                    if(res.status == 1){
                        for(let i of res.data){
                            this.comments.push(i);
                        }
                        this.$nextTick(() => {
                            if(this.limit){
                                this.loading = true;
                                return;
                            }
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
                    this.loadTry++;
                    this.loading = false;
                    if(this.loadTry >= 3){
                        this.loading = true;
                    }
                });
            },
            topicComments() {
                let data = {
                    page_name: this.gid,
                    limit: this.limit ? this.limit : 5,
                    page: this.page
                }
                this.$http.post('/Goods/specialPage.json', data).then((res) => {
                    res = res.json();
                    if(res.status == 1){
                        for(let i of res.data.comment_list){
                            this.comments.push(i);
                        }

                        this.setBuyOption({
                            selected: res.data.goods_list[0],
                            list: res.data.goods_list
                        });

                        this.$nextTick(() => {
                            if(this.limit){
                                this.loading = true;
                                return;
                            }
                            if(res.data.comment_list.length == 0){
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
                    this.loadTry++;
                    this.loading = false;
                    if(this.loadTry >= 3){
                        this.loading = true;
                    }
                });
            },
            loadMore() {
                this.page += 1;
                this.loadComments();
            }
        }
	}
</script>

<template>
    <div v-if="comments.length == 0" class="comments-none">暂无评论</div>
    <div v-else>
    	<ul class="comment-list" v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
        	<li v-for="item in comments">
        		<div class="comment-item">
        	       	<div class="item-left">
        	       		<span class="user">
        	            	<img src="/public/images/detail/person.jpg" alt=""/>
        	            </span>
        	       	</div>
        	        <div class="item-right">
        	        	<div class="uaer-name">{{item.user_name}}<!-- <div class="comment-date">{{item.show_time}}</div> --></div>
        	            <div class="comment-container">
        	            	<p>{{item.content}}</p>
        	           	</div>
        	            <div class="comment-img">
                            <span class="item-img"><img :src="item.pic" alt="" /></span>
        	               	<span class="item-img"><img :src="item.pic1" alt="" /></span>
        	            </div>
                    </div>
    	        </div>
        	</li>
        </ul>
        <div v-if="limit == undefined" class="load-more">{{loadingText}}</div>
    </div>
</template>

<style>
    .comments-none{padding: 3rem 0; text-align: center;}

	.good-comment{background:#fff; overflow: hidden; margin-top: 0.4rem;}
	.good-comment .info{border-bottom: 1px solid #f4f4f4; padding: 0 0.65rem; height: 2.4rem;}
	.good-comment .info a{color: #b2b2b2; line-height: 2.3rem; height: 2.4rem; display: block;}
	.good-comment .info a span{font-size: 0.75rem; display: inline-block;}
	.good-comment-text{float: right;}

	.comment-list{padding: 0 0.65rem; overflow: hidden;}
	.comment-list li{padding: 0.65rem 0; overflow: hidden; border-bottom: 1px solid #e8e8e8;}
	.comment-list .comment-item{overflow: hidden;}
	.comment-list .comment-item .item-left{float: left; width: 18%; text-align: center;}
	.comment-list .comment-item .item-right{float: left; width: 82%;}
    .comment-img{padding-top: 0.5rem;}
	.comment-img span{display: inline-block;}
	.comment-item .item-left span{display: inline-block; text-align: center; width: 82%;}
	.comment-item .item-left span img{max-width: 88px; width: 100%;}
	.uaer-name{font-size: 0.75rem; padding-right: 0.5rem;}
	.comment-date{float: right; color: #b5b5b5; font-size: 0.65rem;}
	.comment-container p{font-size: 0.72rem;}

    .comment-img{display: flex; display: -webkit-flex;}
    .comment-img .item-img{width: 25%; margin-right: 0.4rem; display: block;}
</style>