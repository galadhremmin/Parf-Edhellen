const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const cleanWebpack = require('clean-webpack-plugin');
const WebpackNotifierPlugin = require('webpack-notifier');
// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

// Reads `.env` configuration values to `process.env`
require('dotenv').config();

const bundleCssWithJavaScript = false;
const version = process.env.ED_VERSION;

const nodeModulesPath = path.resolve(__dirname, 'node_modules');
const sourcePath = path.resolve(__dirname, 'resources/assets/ts');
const publicPath = `/v${version}`;
const outputPath = path.resolve(__dirname, `public/${publicPath}`);

module.exports = {
  entry: {
    index: `${sourcePath}/index.tsx`,
    'style-auth': `${sourcePath}/apps/auth/index.scss`,
    'style-dashboard': `${sourcePath}/apps/dashboard/index.scss`,
    'style-timeline': `${sourcePath}/apps/timeline/index.scss`,
    'style-sentence': `${sourcePath}/apps/sentence/index.scss`,
  },
  output: {
    path: outputPath,
    publicPath: `${publicPath}/`,
  },
  optimization: {
    chunkIds: 'deterministic',
    splitChunks: {
      cacheGroups: {
        default: false,
        vendors: {
          test: /\/node_modules\/(axios|classnames|html\-to\-react|luxon|react|redux|spinkit)/,
          priority: 0,
          reuseExistingChunk: false,
        },
        glaemscribe: {
          name(module, chunks, cacheGroupKey) {
            const moduleFileName = module.identifier().split('/').reduceRight(item => item).replace(/\.js$/, '');
            const type = moduleFileName.includes('.cst.') ? 'charset' : 'mode';
            return [cacheGroupKey, type, moduleFileName].join('.');
          },
          chunks: 'async',
          test: /node_modules\/glaemscribe\//,
          priority: 10,
          reuseExistingChunk: true,
        },
        grid: {
          test: /\/node_modules\/ag\-grid\-(community|react)/,
          priority: 20,
          reuseExistingChunk: true,
        },
        recharts: {
          test: /\/node_modules\/(recharts|d3|lodash|core\-js)/,
          priority: 30,
          reuseExistingChunk: true,
        },
        common: {
          name: 'common',
          minChunks: 2,
          priority: -10,
          reuseExistingChunk: true,
        },
      },
    },
  },
  // devtool: 'source-map',
  resolve: {
    alias: {
      '@root': sourcePath,
    },
    extensions: [ 
      '.ts', 
      '.tsx', 
      '.js', 
      '.json' 
    ],
  },
  module: {
    rules: [
      {
        test: require.resolve('glaemscribe/js/glaemscribe.min.js'),
        use: [{
          loader: 'exports-loader',
          options: {
            exports: 'Glaemscribe',
          },
        }],
      },
      {
        test: /\.(cst|glaem)\.js$/,
        use: [{
          loader: 'imports-loader',
          options: {
            additionalCode: 'var Glaemscribe = window.Glaemscribe;',
          },
        }],
      },
      { 
        test: /\.tsx?$/, 
        use: 'ts-loader',
      },
      // All output '.js' files will have any sourcemaps re-processed by 'source-map-loader'.
      /*
      { 
        enforce: 'pre', 
        test: /\.js$/, 
        use: 'source-map-loader'
      },
      */
      {
        test: /\.scss$/,
        use: [
          bundleCssWithJavaScript ? 'style-loader' : MiniCssExtractPlugin.loader, // creates style nodes from JS strings
          {
            loader: 'css-loader',
            options: {
              modules: false, // translates CSS into CommonJS
              sourceMap: false,
            },
          },
          {
            loader: 'sass-loader', // compiles Sass to CSS, using Node Sass by defaultoptions: {
            options: {
              sourceMap: false,
            },
          },
        ]
      },
      {
        test: /\.css$/,
        use: [
          bundleCssWithJavaScript ? 'style-loader' : MiniCssExtractPlugin.loader, // creates style nodes from JS strings
          {
            loader: 'css-loader',
            options: {
              modules: false, // translates CSS into CommonJS
              sourceMap: false,
            },
          },
        ]
      },
    ],
  },
  plugins: [
    new cleanWebpack.CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      // Options similar to the same options in webpackOptions.output
      // both options are optional
      filename: "[name].css",
      chunkFilename: "[id].css",
      ignoreOrder: true,
    }),
    // new AsyncChunkNames(),
    new WebpackNotifierPlugin(),
    // new BundleAnalyzerPlugin(),
  ],
};
