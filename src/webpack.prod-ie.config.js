const path = require('path');
const merge = require('webpack-merge');
const baseConfig = require('./webpack.prod.config');

const version = process.env.ED_VERSION;
const publicPath = `/v${version}-ie`;
const outputPath = path.resolve(__dirname, `public/${publicPath}`);

module.exports = merge(baseConfig, {
  output: {
    path: outputPath,
    publicPath: publicPath,
  },
});
