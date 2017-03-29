<script>
	import { updateAppHeader } from '../../vuex/actions.js';
	export default {
		vuex: {
			actions: {
				updateAppHeader
			}
		},
		ready() {
			this.updateAppHeader({
                type: 1
            });

		},
		route: {
            data() {
            	this.updateAppHeader({
                    type: 1
                });
                document.title = "管理账号";
            }
        }
	}
</script>

<template>
<div class="container">
	<div class="content account-form">
		<!-- <div class="block block-list">
			<a class="block-item link clearfix" href="https://h5.koudaitong.com/v2/buyer/auth/changephone?redirect_uri=https%3A%2F%2Fh5.koudaitong.com%2Fv2%2Fbuyer%2Fmanage">
			<p class="title-info c-black font-size-14 pull-left">
				修改手机号码
			</p>
			<p class="title-info c-black font-size-14 pull-right">
				137****5401
			</p>
			</a>
		</div> -->
		<div class="block block-list">
			<a class="block-item link" href="javascript:;" v-link="{ name: 'findPassword' }" >
			<p class="title-info c-black font-size-14">
				修改登录密码
			</p>
			</a>
		</div>
	</div>
</div>
</template>

<style scoped>
.account-form{padding-top:20px;}
.container{background-color:#f8f8f8;color:#adadad;font-weight:100}
.clearfix{zoom:1}
.c-black{color:#333!important}
.font-size-14{font-size:14px!important}
.pull-left{float:left}
.pull-right{float:right}
.block p{overflow:hidden}
.block{border-top:1px solid #e5e5e5;border-bottom:1px solid #e5e5e5;overflow:hidden;margin:10px 0;background-color:#fff;display:block;position:relative;font-size:14px}
.block.block-list{margin:0;padding:0;padding-left:10px;list-style:none;font-size:14px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
.block-item{position:relative;display:block;padding:10px;line-height:1.5;border:0;border-top:1px solid #e5e5e5;overflow:hidden}
.link:active,.link:hover,.link:link,.link:visited{color:#07d}
.block-list>.block-item{padding:10px 10px 10px 0}
.block.block-list>a.link{padding-left:0;padding-right:28px}
.block.block-list>a.link p::after{content:'';position:absolute;width:7px;height:7px;border-top:2px solid #cbc9cc;border-right:2px solid #cbc9cc;-webkit-transform:rotate(45deg);-moz-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate(45deg);top:16px;right:12px}
.block.block-list+.block.block-list{margin-top:12px}
.container .content:after{content:'';display:table;clear:both}
.block-list>.block-item:first-child{border-top:0 none}
.block.block-list>a.link{padding-left:0;padding-right:28px}

</style>