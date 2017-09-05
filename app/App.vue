<template>
  <div id="mm-weixin-admin" class="el-tabs el-tabs--border-card">
    <nav class="el-tabs__header">
      <div class="el-tabs__nav-wrap">
        <router-link class="el-tabs__item" to="/fetch">抓取文章</router-link>
        <router-link class="el-tabs__item" to="/setting">设置</router-link>
        <router-link class="el-tabs__item" to="/qrcode">二维码管理</router-link>
        <router-link class="el-tabs__item" to="/help">帮助</router-link>
      </div>
    </nav>
    <router-view class="el-tabs__content"></router-view>
  </div>
</template>

<script>
  export default {
    data() {
      return {
      };
    },
  };
</script>