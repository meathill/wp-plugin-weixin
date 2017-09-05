<template>
  <el-form ref="form" label-width="5rem">
    <el-form-item label="AppID">
      <el-input name="app_id" v-model="app_id"></el-input>
    </el-form-item>
    <el-form-item label="AppSecret">
      <el-input name="app_id" v-model="app_secret"></el-input>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" :loading="saving" @click="save">保存</el-button>
    </el-form-item>
  </el-form>
</template>

<script>
  import Vuex from 'vuex';

  /* global ajaxurl */

  export default {
    name: 'setting',
    computed: {
      ...Vuex.mapState([
        'app_id',
        'app_secret',
      ]),
    },
    data() {
      return {
        // UI
        saving: false,
      };
    },
    methods: {
      getFormData() {
        let data = new FormData();
        data.append('action', 'mm_weixin_save_config');
        data.append('app_id', this.app_id);
        data.append('app_secret', this.app_secret);
        return data;
      },
      save() {
        this.saving = true;
        this.$http.post(ajaxurl, this.getFormData())
          .then((response) => {
            return response.json();
          })
          .then((result) => {
            this.saving = false;
            this.$message({
              type: result.code === 0 ? 'success' : 'error',
              message: result.msg,
            });
          });
      },
    },
  };
</script>