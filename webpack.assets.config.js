const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = {
  mode: 'production', // or 'production'
  entry: {
    admin: path.resolve(__dirname, 'assets/admin/admin.js'),
    sidebar: path.resolve(__dirname, 'assets/admin/sidebar.js'),
  },
  output: {
    path: path.resolve(__dirname, 'build/admin'),
    filename: '[name].min.js',
  },
  devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.css$/i,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].min.css',
    }),
  ],
   optimization: {
    minimizer: [
      `...`, // extend existing minimizers (i.e., Terser for JS)
      new CssMinimizerPlugin(),
    ],
  },
};
