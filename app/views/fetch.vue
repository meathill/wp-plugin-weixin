<template>
  <div>
    <el-row type="flex" class="mb-1">
      <el-button type="primary" @click="fetch" :loading="loading">获取文章列表</el-button>
      <el-alert v-if="errorMsg" :title="errorMsg" type="error" show-icon class="mx-1"></el-alert>
      <el-pagination layout="prev, pager, next" :total="total" :page-size="20" @current-change="turnToPage" class="ml-auto">
      </el-pagination>
    </el-row>
    <el-table :data="items" border>
      <el-table-column label="标题">
        <template slot-scope="scope">
          <a :href="scope.row.url" v-text="scope.row.title" target="_blank"></a>
        </template>
      </el-table-column>
      <el-table-column prop="author" label="作者"></el-table-column>
      <el-table-column prop="digest" label="摘要"></el-table-column>
      <el-table-column label="更新时间">
        <template slot-scope="scope">
          <time :datetime="scope.row.update_time" v-text="scope.row.update_time"></time>
        </template>
      </el-table-column>
      <el-table-column label="操作">
        <template slot-scope="scope">
          <template v-if="scope.row.post_id">
            <el-popover placement="top-start" title="导入信息" trigger="hover">
              <el-tag slot="reference" type="success">已导入</el-tag>
              导入时间：<time :datetime="scope.row.fetch_time" v-text="scope.row.fetch_time"></time><br>
              <a :href="'/?p=' + scope.row.post_id" target="_blank">前往预览</a>
            </el-popover>
          </template>
          <el-button @click="importArticle(scope.row, scope.$index)" :ref="'importButton' + scope.$index">导入</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
  import moment from 'moment';
  import {mapState} from 'vuex';

  /* global ajaxurl */

  export default {
    computed: {
      ...mapState([
        'app_id',
        'app_secret',
      ]),
    },
    data() {
      return {
        // logical variables
        activeName: 'weixin-article-list',
        items: [],
        page: 1,
        pageSize: 20,
        total: 0,
        // UI variables
        loading: false,
        errorMsg: '',
      };
    },
    methods: {
      fetch(page) {
        this.errorMsg = '';
        this.loading = true;
        page = isNaN(page) ? 0 : page;
        this.$http.get(ajaxurl, {
          params: {
            action: 'mm_weixin_fetch_news_list',
            page: page,
          },
        })
          .then((response) => {
            return response.json();
          })
          .then(({code, data, total_count, msg}) => {
            if (code !== 0) {
              throw new Error(msg);
            }
            this.items = data.item.reduce((memo, item) => {
              let news = item.content.news_item.map((one) => {
                one.media_id = item.media_id;
                one.post_id = one.post_id || '';
                one.update_time = moment(item.update_time * 1000)
                  .format('YYYY-MM-DD HH:mm:ss');
                return one;
              });
              return memo.concat(news);
            }, []);
            this.total = total_count; // eslint-disable-line camelcase
          })
          .catch((err) => {
            console.log(err);
            this.errorMsg = '抓取失败。原因：' + (err.message || err.msg);
          })
          .then(() => {
            this.loading = false;
          });
      },
      importArticle(row, index) {
        this.$refs['importButton' + index].loading = true;
        this.$http.post(ajaxurl, row, {
          params: {
            action: 'mm_weixin_import_article',
          },
        })
          .then((response) => {
            return response.json();
          })
          .then((response) => {
            this.$refs['importButton' + index].loading = false;
            let isSuccess = response.code === 0;
            if (isSuccess) {
              this.items[index].post_id = response.post_id;
              this.items[index].fetch_time = response.fetch_time;
            }
            this.$message({
              type: isSuccess ? 'success' : 'error',
              message: response.msg,
            });
          });
      },
      turnToPage(page) {
        if (this.page !== page) {
          this.page = page;
          this.fetch(page - 1);
        }
      },
    },
  };
</script>