/**
 * Created by realm on 2017/3/21.
 */

let admin = new Vue({
  el: '#mm-weixin-admin',
  data: {
    activeName: 'weixin-article-list'
  },
  methods: {
    fetch() {
      this.$http.get(ajaxurl, {
        params: 'mm_weixin_fetch_news_list'
      })
        .then( response => {
          return response.json();
        })
        .then( list => {
          this.list = list;
        });
    },
    saveSettings() {
      this.$refs.submitButton.loading = true;
      this.$http.post(ajaxurl, {
        body: this.$refs.form.elements
      })
        .then( response => {
          return response.json()
        })
        .then( result => {
          this.$refs.submitButton.loading = false;
          if (result.code !== 0) {
            alert(result.msg);
          }
        });
    }
  }
});