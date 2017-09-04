/**
 * Created by Meathill on 2017/3/22.
 */
const path = require('path');

module.exports = {
  entry: {
    admin: './app/admin.js',
  },
  output: {
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, 'dist'),
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
      },
      {
        test: /\.hbs$/,
        loader: 'handlebars-loader',
      },
      {
        test: /\.vue$/,
        exclude: /node_modules/,
        loader: 'vue-loader',
      },
    ],
  },
  devtool: 'source-map',
  watch: true,
  watchOptions: {
    ignored: 'node_modules,dist,build,docs,css,styl',
    poll: 1000,
  },
  externals: {
    'vue': 'Vue',
    'vue-resource': 'VueResource',
    'moment': 'moment',
    'element-ui': 'ElementUI',
  },
};
