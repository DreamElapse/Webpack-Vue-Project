import Vue from 'vue';
import Vuex from 'vuex';
import {
    isLogin
} from './actions.js';

Vue.use(Vuex);

// 需要维护的状态
const state = {
    app: {
        source_url: '',
        vm: null,
        header: {
            tabIndex: 0
        },
        footer: {
            vm: null
        }
    },
    appHeader: {
        type: 1,
        content: ''
    },
    Q_Chinaskin: false,
    isLogin: false,
    tel: '',
    showTel: '',
    SWT: false,
    QQ: '',
    actName: '20170323',
    user: {
        name: '',
        portrait: '',
        level: 0,
        curPoint: 0,
        maxPoint: 0,
        address: ''
    },
    defaultAddress: null,
    shoppingCart: {
        address: null,
        quantity: 0
    },
    buyOption: {}
}

const mutations = {
    // 初始化 state
    INIT_STORE(state, data) {
        localStorage.source_url = location.href;
        state.app.vm = data.vm;
    },

    //头部tab 小标
    SET_HADER_TAB_INDEX(state, index) {
        localStorage.source_url = location.href;
        state.app.header.tabIndex = index;
    },

    // 头部
    UPDATE_APP_HEADER(state, opt) {
        Object.assign(state.appHeader, opt);
    },

    // 用户
    UPDATE_USER(state, opt) {
        Object.assign(state.user, opt);
    },

    // 设置是否已登录
    SET_IS_LOGIN(state, bool) {
        state.isLogin = bool;
    },

    // 设置拨打的电话号码
    SET_TEL(state, val) {
        state.tel = val;
    },

     // 设置电话号码
    SET_SHOWTEL(state, val) {
        state.showTel = val;
    },

    // QQ
    SET_QQ(state, val) {
        state.QQ = val;
    },

    // 商务通
    SET_SWT(state, val) {
        state.SWT = val;
    },

    // Q站
    SET_Q_CHINASKIN(state, val) {
        state.Q_Chinaskin = val;
    },

    // 设置购物车商品数量
    SET_CART_GOODS_QTY(state, num, reset) {
        if (!num) {
            num = 0;
        }
        if (reset) {
            state.shoppingCart.quantity = num;
        } else {
            state.shoppingCart.quantity += num;
        }
    },

    // 设置默认地址
    SET_DEFAULT_ADDRESS(state, address) {
        state.defaultAddress = address;
    },

    // 地址
    UPDATE_ADDRESS(state, obj) {
        state.shoppingCart.address = obj;
    },

    // 设置专题列表
    SET_BUY_OPTION(state, opt) {
        state.buyOption = opt;
    }
}

export default new Vuex.Store({
    state,
    mutations
});