<template>
  <el-form ref="form" label-width="5rem">
    <el-form-item label="AppID">
      <el-input name="app_id" v-model="localAppId"></el-input>
    </el-form-item>
    <el-form-item label="AppSecret">
      <el-input name="app_id" v-model="localAppSecret"></el-input>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="saving" @click="save">保存</el-button>
    </el-form-item>
  </el-form>
</template>

<script>
  import {mapState} from 'vuex';

  /* global ajaxurl */

  export default {
    name: 'setting',
    computed: {
      ...mapState([
        'app_id',
        'app_secret',
      ]),
    },
    data() {
      return {
        // UI
        saving: false,
        localAppId: '',
        localAppSecret: '',
      };
    },
    methods: {
      getFormData() {
        let data = new FormData();
        data.append('action', 'mm_weixin_save_config');
        data.append('app_id', this.localAppId);
        data.append('app_secret', this.localAppSecret);
        return data;
      },
      processData() {
        this.localAppId = this.app_id;
        this.localAppSecret = this.app_secret;
      },
      save() {
        this.saving = true;
        this.$http.post(ajaxurl, this.getFormData())
          .then((response) => {
            return response.json();
          })
          .then((result) => {
            this.saving = false;
            this.$store.commit('setAppInfo', this.localAppId, this.localAppSecret);
            this.$message({
              type: result.code === 0 ? 'success' : 'error',
              message: result.msg,
            });
          });
      },
    },
    beforeMount() {
      this.processData();
    },
  };
</script>