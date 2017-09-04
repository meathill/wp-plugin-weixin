/**
 * Created by realm on 2017/3/21.
 */

import Vue from 'vue';
import moment from 'moment';
import App from './App.vue';

moment.locale('zh-CN');

let dom = document.getElementById('mm-weixin-admin');
let app = new Vue(App).$mount(dom);
app.settings.app_id = dom.dataset.appId;
app.settings.app_secret = dom.dataset.appSecret;
