import VueRouter from 'vue-router';
import Fetch from '../views/fetch.vue';
import Setting from '../views/setting.vue';
import QRCode from '../views/qrcode.vue';
import Help from '../views/help.vue';

const routes = [
  {
    path: '/',
    redirect: {
      name: 'fetch',
    },
  },
  {
    path: '/fetch',
    name: 'fetch',
    component: Fetch,
  },
  {
    path: '/setting',
    name: 'setting',
    component: Setting,
  },
  {
    path: '/qrcode',
    name: 'qrcode',
    component: QRCode,
  },
  {
    path: '/help',
    name: 'help',
    component: Help,
  },
];

export default new VueRouter({
  base: location.pathname.substr(0, location.pathname.lastIndexOf('/') + 1),
  linkActiveClass: 'is-active',
  routes,
});
