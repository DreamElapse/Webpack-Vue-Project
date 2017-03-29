
<script>
	import { updateAppHeader } from 'vuex_path/actions.js';
	var footer = document.querySelector('.fixed-footer');
	var display = footer.style.display;
	
	export default{
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		route: {
			deactivate(transition) {
				footer.style.display = display;
				transition.next();
			},
			data() {				
				this.updateAppHeader({
					type: 2,
					content: '维权清单'
				});
				this.rightList = [];
				this.page=1;
				footer.style.display = 'none';
				this.getTheList()
			}
		},
	    data(){
	    	return {
	    		loading: true,
        		loadTry: 0,
	    		page:1,
				loadingText: '加载中...',
	    		rightList:[]
	    	}	    	
	    },
	    ready() {
	    	$('.back').on('click',function(){
	    		this.$route.router.go({name: "protect_right"});
	    	})
	    },
	    methods:{
	    	getTheList(){
	    		this.loading = true;
		    	let data={
		    		page:this.page,
		    		pageSize:8
		    	}
		    	//请求维权清单
		    	this.$http.post('/Rights/getList.json', data).then((res) => {
					res = res.json();
					if(res.status == 1){
						//this.rightList=res.data.rows;
						this.$nextTick(() => {
                            if(res.data.rows.length == 0){
                                this.loadingText = '( ⊙ o ⊙ )啊哦，没有更多清单啦~';
                                this.loading = true;
                            }else{
                            	for(let i of res.data.rows){
		                            this.rightList.push(i);
		                        }
                                this.loading = false;
                            }
                        });
					}else{
						this.loadingText = '( ⊙ o ⊙ )啊哦，没有更多清单啦~';
                        this.loading = true;
                    }
				},()=>{
					this.loadTry++;
                    this.loading = false;
                    if(this.loadTry >= 3){
                        this.loading = true;
                    }
				});
	    	},
        	loadMore() {
                this.page += 1;
                this.getTheList();
            }
	    }
    }
</script>

<template>
	
    <div class="width-full">
        <ul class="right_list" v-infinite-scroll="loadMore()" infinite-scroll-disabled="loading">
	        <!--<div class="header">
	            <a class="back" v-link="{name: 'right_list'}"></a>
	            维权评价
	        </div>-->
        	<li v-for="right in rightList">
        		<p>投诉单号：{{right.complaint_no}}</p>
        		<p>受理工号：{{right.follow_kefu}}</p>
        		<p>提交时间：{{right.create_time}}</p>
        		<p>当前状态：{{right.status}}</p>
        		<a v-link="{name: 'right_evaluate',params:{ rid:right.rid }}" :class="{hasClick:right.handle==4}">维权评价</a>
        	</li>
        </ul>
        <div class="load-more">{{loadingText}}</div>
    </div>
</template>

<style scoped>
	html{font-size: 20px;}
	@media only screen and (max-width: 320px){html {font-size: 20px;}}
	@media only screen and (device-width: 360px){html {font-size: 22.5px;}}
	@media screen and (device-width: 375px){html {font-size: 23.4375px;}}
	@media only screen and (min-width: 401px){html {font-size: 25px;}}
	@media screen and (device-width: 414px){html {font-size: 25.875px;}}
	@media only screen and (min-width: 428px){html { font-size: 26.75px; }}
	@media only screen and (min-width: 481px){html {font-size: 30px; }}
	@media only screen and (min-width: 569px){html { font-size: 35px; }}
	@media only screen and (min-width: 640px){html {font-size: 40px; }}
	body{font-family:'Microsoft YaHei',Verdana,Arial,Helvetica,sans-serif; color:#000;background-color:#fff;min-width:320px;max-width:640px;margin:0 auto;}
    .width-full{background-color: #fff;}
    .headerwrap{background-color: #fff;}
    .header{position:relative;height:2rem;line-height:2rem;margin: 0 0.4rem;border-bottom: 3px solid #000000;color: #000;font-size: 0.75rem;font-weight: bold;text-align: center;}
    .header .back{position: absolute;width:0.8rem;height:1.2rem;left:0;top:0.5rem;background-image:url(/public/images/service_evaluation/commom-icon.png);background-repeat: no-repeat;background-position: -2rem -1rem;background-size: 2.5rem;}
    .right_list{padding: 0.6rem;}
    .right_list li{border-bottom: 1px solid #e5e5e5;font-size: 0.85rem;padding: 0.8rem 0;position: relative;left: 0;top: 0;}
    .right_list li a{position: absolute;top: 2.4rem;right: 1.6rem;text-decoration: underline;}
    .right_list li a.hasClick{display: none;}
</style>