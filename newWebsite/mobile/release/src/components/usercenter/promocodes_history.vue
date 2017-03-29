
<script>
    export default {

        ready() {
            // this.loadList(); 
        },
        route:{
            data(){
                this.loadList(); 
            }
        },
        data() {
            return {
                loading: false,
                loadingText: '加载中...',
                loadTry: 0,
                page: 1,
                list: [],
                actived:false
            }
        },
        props: {
            limit: {
                type: Number
            },
            codeList: {}
        },
        watch: {
            codeList(val) {
                this.list = val;
            }
        },
        methods: {
            loadList() {
                this.loading = true;
                this.goodsList();
            },
            goodsList() {
                let data = {
                    pageSize: this.limit ? this.limit : 8,
                    page: this.page
                }
                this.$http.post('/Integral/logList.json', data).then((res) => {
                    res = res.json();
                    if(res.status == 1){
                        for(let i of res.data.list){
                            // if(res.data.list.points>0){
                            //     this.actived = true;
                            // }
                            this.list.push(i);
                        }
                        this.$nextTick(() => {
                            if(this.limit){
                                this.loading = true;
                                return;
                            }
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
                    this.loadTry++;
                    this.loading = false;
                    if(this.loadTry >= 3){
                        this.loading = true;
                    }
                });
            },
            loadMore() {
                this.page += 1;
                this.loadList();
            }
        }
    }
</script>

<template>
    <div v-if="list.length == 0" class="comments-none">暂无积分详情记录</div>
    <div v-else class="js-list b-list">
        <ul class="comment-list" v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
            <li class="pointsstore-item" v-for="item in list">
                    <div class="item-info pull-left">
                        <p class="item-desc">
                            {{item.remark}}
                        </p>
                        <p class="item-time">
                            {{item.add_date}}
                        </p>
                    </div>
                    <div class="item-amount pull-right" :class="item.points > 0 ? 'dec' : 'dac'">
                        {{item.points}}
                    </div>
                </li>
        </ul>
        <div v-if="limit == undefined" class="load-more">{{loadingText}}</div>
    </div>
</template>

<style scoped>

.comments-none{padding: 3rem 0; text-align: center;}
.points-title{padding:10px;padding-top:20px;color:#666;font-size:16px;}
.history-list{float:right;color:#00a0f8;position:relative;padding-right:10px}
.history-list::after{-webkit-transform:rotate(45deg);-moz-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate(45deg);content:'';width:6px;height:6px;right:0;top:7px;border:2px solid #00a0f8;border-left:0;border-bottom:0;position:absolute}
#list_container{background-color:#fff}
.pointsstore-item{margin-left:10px;padding:10px 10px 10px 0;overflow:hidden;border-bottom:1px solid #e5e5e5;position:relative}
.pointsstore-item .item-info {width: 60%;}
.pointsstore-item .item-amount{line-height: 36px;font-size: 18px;position: absolute;right: 10px;top: 50%;margin-top: -18px;color: #06bf04 !important;}
.pointsstore-item .item-amount.dec{color: #06bf04 !important;}
.pointsstore-item .item-amount.dac{color: #ed5050 !important;}
.pointsstore-item .item-desc {font-size: 16px;line-height: 1.5;color: #333;margin-bottom: 10px;}
.pointsstore-item .item-time {font-size: 12px;color: #666;}

</style>