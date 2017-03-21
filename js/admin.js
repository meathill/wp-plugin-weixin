/**
 * Created by realm on 2017/3/21.
 */

let ArticleList = new Vue({
  el: '#article-list',
  data: {
    list: null
  },
  methods: {
    fetch(button) {
      $(button).spin();
      $.ajax(ajaxurl, {
        action: 'fetch_news_list'
      })
        .then( list => {
          this.list = list;
        });
    }
  }
});