/**
 * Created by realm on 2017/3/21.
 */

const each = Array.prototype.forEach;

moment.locale('zh-CN');

let admin = new Vue({
  el: '#mm-weixin-admin',
  data: {
    activeName: 'weixin-article-list',
    items: [],
    pageSize: 20,
    settings: {
      app_id: '',
      app_secret: '',
      action: 'mm_weixin_save_config'
    }
  },
  methods: {
    fetch(page = 0) {
      this.$refs.fetchButton.loading = true;
      this.$http.get(ajaxurl, {
        params: {
          action: 'mm_weixin_fetch_news_list',
          page: page
        }
      })
        .then( response => {
          return response.json();
        })
        .then( response => {
          this.items = response.item.reduce((memo, item) => {
            let news = item.content.news_item.map(one => {
              one.media_id = item.media_id;
              one.post_id = one.post_id || '';
              one.update_time = moment(item.update_time * 1000).format('YYYY-MM-DD HH:mm:ss');
              return one;
            });
            return memo.concat(news);
          }, []);
          this.$refs.pagination.total = response.total_count;
          this.$refs.fetchButton.loading = false;
        });
    },
    importArticle(row, index) {
      this.items[index].fetching = true;
      this.$http.post(ajaxurl, row, {
        params: {
          action: 'mm_weixin_import_article'
        }
      })
        .then( response => {
          return response.json();
        })
        .then( response => {
          this.items[index].fetching = false;
          let isSuccess = response.code === 0;
          if (isSuccess) {
            row.post_id = response.post_id;
            row.fetch_time = response.fetch_time;
          }
          this.$message({
            type: isSuccess ? 'success' : 'error',
            message: response.msg
          });
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
          this.$message({
            type: result.code === 0 ? 'success' : 'error',
            message: result.msg
          });
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
    },
    turnToPage() {
      this.fetch(this.$refs.pagination.currentPage - 1);
    }
  },
  mounted() {
    each.call(this.$el.querySelectorAll('.el-input'), item => {
      let key = item.getAttribute('data-name');
      this.settings[key] = item.getAttribute('value');
    });
  }
});