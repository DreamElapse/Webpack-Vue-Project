function makeAction(type) {
	return ({
		dispatch
	}, ...args) => dispatch(type, ...args);
};

export const initStore = makeAction('INIT_STORE');

export const updateAppHeader = makeAction('UPDATE_APP_HEADER');

// 设置头部切换下标
export const setHeaderTabIndex = ({
	dispatch
}, index) => {
	dispatch('SET_HADER_TAB_INDEX', index);
}

export const updateUser = makeAction('UPDATE_USER');

//判断是否微信访问并获取微信id接口
export const getWxOpenId = ({
	dispatch
}, source) => {
	Vue.http.post('/Global/getOpenId.json', source).then((res) => {
		res = res.json();
		if (res.status == 1) {
			let wxopenid = res.data;
			if (wxopenid.result == 1) {
				window.location.href = wxopenid.url;

			}else{
				Vue.http.post('/Global/getUserId.json').then((res) => {
					res = res.json();
					if (res.status == 1) {
						dispatch('SET_IS_LOGIN', true);
					} else {
						dispatch('SET_IS_LOGIN', false);
					}
				});
			}
		}
	});
}

// 检查是否已登录
export const checkLogin = ({
	dispatch
}) => {

	// Vue.http.post('/Global/getUserId.json').then((res) => {
	// 	res = res.json();
	// 	alert(11)
	// 	if (res.status == 1) {
	// 		dispatch('SET_IS_LOGIN', true);
	// 	} else {
	// 		dispatch('SET_IS_LOGIN', false);
	// 	}
	// });
}

// 设置登录状态
export const setIsLogin = ({
	dispatch
}, bool) => {
	dispatch('SET_IS_LOGIN', bool);
}

// 注销
export const logout = ({
	dispatch
}) => {
	let result = window.confirm('确定要退出该账号吗？');
	if (result) {
		Vue.http.post('/User/logout.json').then((res) => {
			res = res.json();
			if (res.status == 1) {
				dispatch('SET_IS_LOGIN', false);
			}
		});
	}
}

// 获取频道电话和QQ
export const getAdvisoryInfo = ({
	dispatch
}, number) => {
	let data = {
		campaign: number
	}
	return Vue.http.post('/Global/getAdvisoryInfo.json', data).then((res) => {
		res = res.json();
		if (res.status == 1) {
			dispatch('SET_QQ', res.data.qq_show);
			dispatch('SET_TEL', res.data.tel);
			dispatch('SET_SHOWTEL', res.data.showTel);
			dispatch('SET_SWT', res.data.kf);
			// 是否Q站
			dispatch('SET_Q_CHINASKIN', res.data.isq);
		}
	});
}

// 获取活动
export const getAct = ({
	dispatch
}) => {
	Vue.http.post('/index/dynamics.json').then((res) => {
		res = res.json();
	});
}

// 加入购物车
export const addGoodsToCart = ({
		dispatch,
		state
}, obj, success, err) => {
		let data = {
			goods_id: obj.id,
			goods_number: 1
		}
		if (obj.num) data.goods_number = obj.num;
		if (obj.package && obj.package != 'undefined') data.is_package = obj.package;
		if (obj.act_id) data.act_id = obj.act_id;
		if (obj.option) data.option = obj.option;		
		Vue.http.post('/cart/addgoodstocart.json', data).then((res) => {
			res = res.json();
			if (res.status == 1) {
				if (obj.delay) {
					setTimeout(() => {
						dispatch('SET_CART_GOODS_QTY', data.goods_number);
					}, obj.delay);
				} else {
					dispatch('SET_CART_GOODS_QTY', data.goods_number);
				}
				if (obj.msg != false) {
					state.app.vm.$dispatch('popup', '已加入购物车 , 请到购物车结算哦');
				}
				success && success();
			} else {
				state.app.vm.$dispatch('popup', res.msg);
				err && err();
			}
		}, () => {
			err && err();
		});
	}
	// 购物车商品数量
export const getCartGoodsQty = ({
	dispatch
}, reset) => {
	Vue.http.post('/Global/cartGoodsNum.json').then((res) => {
		res = res.json();
		if (res.status == 1) {
			dispatch('SET_CART_GOODS_QTY', parseInt(res.data.cartGoodsNum), reset);
		}
	});
}

// 计算购物车商品数量
export const caclCartGoodsQty = ({
	dispatch
}, num) => {
	dispatch('SET_CART_GOODS_QTY', num);
}

// 获取默认地址
export const getDefaultAddress = ({
	dispatch
}) => {
	Vue.http.post('/UserAddress/Defaults.json').then((res) => {
		res = res.json();
		if (res.status == 1) {
			let address = res.data;
			if (address) {
				dispatch('SET_DEFAULT_ADDRESS', address);
			}
		}
	});
}

export const updateAddress = makeAction('UPDATE_ADDRESS');

// 设置专题列表
export const setBuyOption = ({
	dispatch
}, opt) => {
	dispatch('SET_BUY_OPTION', opt);
}