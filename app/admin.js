/**
 * Created by realm on 2017/3/21.
 */

import Vue from 'vue';
import Vuex from 'vuex';
import moment from 'moment';
import App from './App.vue';
import router from './router';

moment.locale('zh-CN');

let dom = document.getElementById('mm-weixin-admin');
let init = {
  app_id: dom.dataset.appId,
  app_secret: dom.dataset.appSecret,
};
const store = new Vuex.Store({
  state: init,
  mutations: {
    setAppInfo(state, appId, appSecret) {
      state.app_id = appId;
      state.app_secret = appSecret;
    },
  },
});

new Vue({
  router,
  store,
  ...App,
}).$mount(dom);
