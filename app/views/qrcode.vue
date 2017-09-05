<template>
  <div>
    <el-row type="flex" class="mb-1" justify="space-between">
      <el-button type="primary" @click="add">增加二维码</el-button>
      <el-pagination layout="prev, pager, next" :total="total" :page-size="20" @current-change="turnToPage">
      </el-pagination>
    </el-row>
    <el-table :data="items" border>
      <el-table-column label="用途">
        <template scope="scope">
          <a :href="scope.row.url" v-text="scope.row.title" target="_blank"></a>
        </template>
      </el-table-column>
      <el-table-column prop="author" label="参数"></el-table-column>
      <el-table-column prop="digest" label="二维码"></el-table-column>
      <el-table-column label="创建时间">
        <template scope="scope">
          <time :datetime="scope.row.update_time" v-text="scope.row.update_time"></time>
        </template>
      </el-table-column>
      <el-table-column label="操作">
        <el-button type="danger" icon="delete2" @click="remove(item.id)">删除</el-button>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        items: [],
        total: 0,
      };
    },
    methods: {
      add() {
        console.log('add');
      },
      remove(id) {
        console.log('remove', id);
      },
      turnToPage(page) {
        console.log(page);
      },
    },
  };
</script>