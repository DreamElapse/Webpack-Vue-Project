<script>
	export default {
		ready() {

			// let host = location.host;
			// if(host == 'new2.3g.chinaskin.cn' || host == 'new.3g.chinaskin.cn'){
			// 	this.setQQ();
			// }else{
			// 	this.setSWT();
			// }

			
		},
		data() {
			return {
				qqShow: false,
				qq: 0,
				img: ''
			}
		},
		methods: {
			// setQQ() {
			// 	let campaign = this.$route.query.campaign;
			// 	let channel;
			// 	if(campaign){
			// 		this.$http.jsonp('http://wpa.b.qq.com/cgi/wpa.php', {credentials:true}).then((res) => {
			// 			console.log(res);
			// 		}, (err) => {
			// 			this.QQ_SUBMIT(this.qq);
			// 			// console.log(err);
			// 		});
			// 		channel = campaign.match(/\d+/g)[0];
			// 		switch (parseInt(channel)){
			// 			case 56:
			// 				this.qq = 800084236;
			// 				this.img = 'floatqq01.png';
			// 				break;
			// 			case 27:
			// 				this.qq = 800099123;
			// 				this.img = 'floatqq01.png';
			// 				break;
			// 			case 68:
			// 				this.qq = 800099120;
			// 				this.img = 'floatqq01.png';
			// 				break;
			// 			case 80:
			// 				this.qq = 800094277;
			// 				this.img = 'floatqq01.png';
			// 				break;
			// 			case 57:
			// 				this.qq = 800099120;
			// 				this.img = 'floatqq01.png';
			// 				break;
			// 			case 61:
			// 				this.qq = 800085050;
			// 				this.img = 'floatqq01.png';
			// 				break;
			// 			case 62:
			// 				this.qq = 800094277;
			// 				this.img = 'floatqq03.png';
			// 				break;
			// 			default:
			// 				break;
			// 		}
			// 		this.qqShow = true;
			// 		// setTimeout(() => {
			// 		// 	this.QQ_SUBMIT(this.qq);
			// 		// }, 5000);
			// 	}
			// },
			setSWT() {
				// this.$http.jsonp('http://kf.sansl.cn/JS/LsJS.aspx?siteid=LWA31671888&float=1&lng=cn', {credentials:true}).then((res) => {
				// 	console.log(res);
				// }, (err) => {
				// 	console.log(err.json());
				// });
			},

			QQ_SUBMIT(qq) {
				BizQQWPA.addCustom([{
					aty: '0',
					a: '0',
					nameAccount: qq,
					selector: 'floatbotQQ'
				}]);
			}
		}
	}
</script>

<template>
	<div v-if="qqShow" class="floatqq" id="floatQQ"><img :src="'/public/images/qq/' + img" alt="" /></div>
</template>

<style scoped>
	.floatqq{
		width: 24%; max-width: 100px; position:fixed; z-index: 9999; right: 0; top: 20%; cursor: pointer; animation: shake .15s 2s ease 6 both;
	}
	@keyframes shake{
		0%{transform: translateY(-4px);}
		25%{transform: translateX(-4px);}
		75%{transform: translateY(4px);}
		100%{transform: translateX(4px);}
	}
</style>