const path = require('path');
const { merge } = require('webpack-merge');
const baseConfig = require('./webpack.prod.config');

const version = process.env.ED_VERSION;
const publicPath = `/v${version}-ie`;
const outputPath = path.resolve(__dirname, `public/${publicPath}`);

const config = merge(baseConfig, {
  output: {
    path: outputPath,
    publicPath: publicPath,
  },
});

config.module.rules = baseConfig.module.rules.map(
  r => {
    if (r.loader !== 'ts-loader') {
      return r;
    }

    r.options = {
      configFile: 'tsconfig.ie.json',
    }
    return r;
  }
);

module.exports = config;
