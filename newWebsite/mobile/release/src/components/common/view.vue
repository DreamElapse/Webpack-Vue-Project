<script>
	export default {
		created() {
			let type = this.$route.params.type;
			let name = this.$route.params.name;

			this.$http.post('/release/src/view/' + type + '/' + name + '.html').then((res) => {
				let topic = Vue.extend({
					// components: {
					// 	Favourable
					// },
					template: res.data
				});
				new topic().$mount().$appendTo('#view');
            });
		}
	}
</script>

<template>
	<div id="view"></div>
</template>