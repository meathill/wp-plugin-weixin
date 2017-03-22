/**
 * Created by Meathill on 2017/3/22.
 */
const path = require('path');

module.exports = {
  entry: {
    admin: './app/admin.js'
  },
  output: {
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, 'dist')
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader'
      },
      {
        test: /\.hbs$/,
        loader: 'handlebars-loader'
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader'
      }
    ]
  },
  watch: true,
  watchOptions: {
    ignored: /node_modules|dist|build|docs|css/,
    poll: 1000
  }
};