/**
 * Created by realm on 2017/3/21.
 */

const each = Array.prototype.forEach;

let admin = new Vue({
  el: '#mm-weixin-admin',
  data: {
    activeName: 'weixin-article-list',
    settings: {
      app_id: '',
      app_secret: '',
      action: 'mm_weixin_save_config'
    }
  },
  mounted() {
    each.call(this.$el.querySelectorAll('.el-input'), item => {
      let key = item.getAttribute('data-name');
      this.settings[key] = item.getAttribute('value');
    });
  },
  methods: {
    fetch() {
      this.$refs.fetchButton.loading = true;
      this.$http.get(ajaxurl, {
        params: {
          action: 'mm_weixin_fetch_news_list'
        }
      })
        .then( response => {
          return response.json();
        })
        .then( list => {
          this.list = list;
          this.$refs.fetchButton.loading = false;
        });
    },
    saveSettings() {
      this.$refs.submitButton.loading = true;
      this.$http.post(ajaxurl, this.toFormData(this.settings))
        .then( response => {
          return response.json()
        })
        .then( result => {
          this.$refs.submitButton.loading = false;
          if (result.code !== 0) {
            this.$message({
              type: 'success',
              message: result.msg
            });
          }
        });
    },
    toFormData(obj) {
      let data = new FormData();
      for (let prop in obj) {
        if (!obj.hasOwnProperty(prop)) {
          continue;
        }
        data.append(prop, obj[prop]);
      }
      return data;
    }
  }
});