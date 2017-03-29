import {
	getAdvisoryInfo
} from 'vuex_path/actions.js';

export default {
	vuex: {
		actions: {
			getAdvisoryInfo
		}
	},
	ready() {
		let channel = this.$route.query.campaign;
		if (channel) {
			channel = channel.match(/channel\d+/g)[0];
		}
		this.getAdvisoryInfo(channel);
	}
}