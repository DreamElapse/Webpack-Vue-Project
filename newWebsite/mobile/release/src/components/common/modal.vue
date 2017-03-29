<script>
	export default {
		ready() {
			this.$watch('show', (val) => {
				if(val){
					setTimeout(() => {
						this.show = false;
					}, 3000);
				}
			});
		},
		props: {
			show: {
				type: Boolean,
				required: true,
				twoWay: true
			},
			msg: {}
		},
		methods: {
			close() {
				this.show = false;
			}
		}
	}
</script>

<template>
	<div class="modal-wrapper" v-show="show" transition="modal">
		<span class="msg">{{msg}}</span>
	</div>
</template>

<style scoped>
	.modal-wrapper{width: 100%; height: 100%; position: fixed; top: 0; left: 0; pointer-events: none;
		display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center; justify-content: center; -webkit-justify-content: center;    z-index: 999;
	}
	.modal-wrapper.mask{background: rgba(0,0,0,0.5);}
	.modal-wrapper.close{opacity: 0; transition: 1s ease-out;}
	.modal-wrapper .msg{max-width: 80%; padding: 0.6rem 1rem; margin-top: 4rem; border-radius: 4px; background: rgba(32,32,32,0.85); color: #fff; display: block;}
</style>