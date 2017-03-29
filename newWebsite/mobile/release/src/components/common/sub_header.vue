<script>
    import { appHeader, isLogin, user } from 'vuex_path/getters.js';
    import { setHeaderTabIndex } from 'vuex_path/actions.js';

    export default {
        vuex: {
            getters: {
                appHeader,
                isLogin,
                user,
                index: state => state.app.header.tabIndex
            },
            actions: {
                setHeaderTabIndex
            }
        },
        computed: {
            hide() {
                return this.$route.hideRightBtn;
            }
        },
        methods: {
            back() {
                history.back();
            },
            tabChange(index) {
                this.setHeaderTabIndex(index);
            }
        }
    }
</script>

<template>
    <div class="width-full nav-header-wrapper">
        <div class="nav-header">
            <a href="javascript:;" @click="back"><i class="font icon-arrow-left"></i></a>
            <div class="nav-header-cont">
                <h2 v-if="appHeader.content">{{appHeader.content}}</h2>
                <div v-else class="nav-header-tab">
                    <a :class="{on: index == 0}" href="javascript:;" @click="tabChange(0)">图文详情</a><b></b><a :class="{on: index == 1}" href="javascript:;" @click="tabChange(1)">用户评价</a>
                </div>
            </div>
            <a v-if="isLogin" :class="{'hide': hide}" v-link="{ name: 'user' }"><i class="font icon-user"></i></a>
            <a v-else :class="{'hide': hide}" v-link="{ name: 'login' }"><em>登录</em></a>
        </div>
    </div>
</template>

<style>
    .nav-header-wrapper{padding: 0 0.8rem; background: #fff; position: fixed; top: 0; z-index: 9999;}
    .nav-header{height: 3.2rem; border-bottom: 0.2rem solid #000; display: flex; display: -webkit-flex; align-items: center; -webkit-align-items: center;}
    .nav-header > a{width: 2.4rem; line-height: 1;}
    .nav-header > a.hide{visibility: hidden;}
    .nav-header > a:last-child{display: flex; display: -webkit-flex; justify-content: flex-end; -webkit-justify-content: flex-end; align-items: center; -webkit-align-items: center;}
    .nav-header .font{font-size: 1.4rem;}
    .nav-header-cont{text-align: center; flex: 1; -webkit-flex: 1;}
    .nav-header-cont h2{font-weight: bold; font-size: 1.1rem;}
    .nav-header-tab{display: flex; display: -webkit-flex; justify-content: center; -webkit-justify-content: center; align-items: center; -webkit-align-items: center;}
    .nav-header-tab a{font-weight: bold; color: #a2a2a2;}
    .nav-header-tab a.on{color: #000;}
    .nav-header-tab b{width: 2px; height: 1.2rem; margin: 0 0.6rem; background: #000; display: block;}
</style>