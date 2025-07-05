const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

const createConfig = (mode, isMinified) => ({
  mode,
  entry: {
    admin: path.resolve(__dirname, 'assets/admin/admin.js'),
    sidebar: path.resolve(__dirname, 'assets/admin/sidebar.js'),
  },
  output: {
    path: path.resolve(__dirname, 'build/admin'),
    filename: isMinified ? '[name].min.js' : '[name].js',
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
      filename: isMinified ? '[name].min.css' : '[name].css',
    }),
  ],
  optimization: {
    minimize: isMinified,
    minimizer: isMinified
      ? [
          `...`, // JS minifier (Terser)
          new CssMinimizerPlugin(),
        ]
      : [],
  },
});

module.exports = [
  createConfig('production', true),  // Minified
  createConfig('development', false) // Unminified
];
