const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const cleanWebpack = require('clean-webpack-plugin');
const WebpackNotifierPlugin = require('webpack-notifier');
const CircularDependencyPlugin = require('circular-dependency-plugin');
const { readdirSync, statSync } = require('fs');
// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

// Reads `.env` configuration values to `process.env`
require('dotenv').config({ path: path.resolve(__dirname, '.env') });

const bundleCssWithJavaScript = false;
const version = process.env.ED_VERSION;
const ssrEnabled = process.env.SSR_ENABLED === 'true';

const sourcePath = path.resolve(__dirname, 'resources/assets/ts');
const publicPath = `/v${version}`;
const outputPath = path.resolve(__dirname, `public/${publicPath}`);

const entry = readdirSync(path.resolve(sourcePath, 'apps')) //
  .filter(file => statSync(path.resolve(sourcePath, 'apps', file)).isDirectory())
  .reduce((entries, file) => ({
    ...entries,
    [file]: path.resolve(sourcePath, 'apps', file, 'index.tsx'),
  }), {});

const isProduction = process.env.NODE_ENV === 'production';

const clientConfig = {
  entry: {
    index: `${sourcePath}/index.tsx`,
    'style-auth': `${sourcePath}/styles/auth.scss`,
    'style-timeline': `${sourcePath}/styles/timeline.scss`,
    'style-sentence': `${sourcePath}/styles/sentence.scss`,
  },
  target: 'web',
  output: {
    path: outputPath,
    publicPath: `${publicPath}/`,
    filename: '[name].js',
    chunkFilename: '[name].[contenthash].js',
  },
  optimization: {
    chunkIds: 'deterministic',
    splitChunks: {
      chunks: 'async',
      cacheGroups: {
        vendors: {
          test: /[\\/]node_modules[\\/](html-to-react|redux|spinkit)[\\/]/,
          name: 'vendors',
          priority: 30,
          reuseExistingChunk: true,
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
          test: /[\\/]node_modules[\\/]@ag-grid-community[\\/]/,
          name: 'ag-grid',
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
        defaultVendors: {
          test: /[\\/]node_modules[\\/]/,
          priority: -20,
          reuseExistingChunk: true,
        },
        default: false,
      },
    },
  },
  devtool: isProduction ? false : 'eval-source-map',
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
        use: [{
          loader: 'ts-loader',
          options: {
            configFile: path.resolve(__dirname, 'tsconfig.json'),
          },
        }],
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
      filename: '[name].css',
      chunkFilename: '[name].[contenthash].css',
      ignoreOrder: true,
    }),
    // new AsyncChunkNames(),
    new WebpackNotifierPlugin(),
    // new BundleAnalyzerPlugin(),
    new CircularDependencyPlugin({
      // exclude detection of files based on a RegExp
      exclude: /node_modules/,
      // add errors to webpack instead of warnings
      failOnError: true,
      // allow import cycles that include an asyncronous import,
      // e.g. via import(/* webpackMode: "weak" */ './file.js')
      allowAsyncCycles: false,
      // set the current working directory for displaying module paths
      cwd: process.cwd(),
    }),
  ],
};

const serverConfig = {
  entry,
  target: 'node',
  output: {
    path: `${outputPath}-server`,
    publicPath: `${publicPath}-server/`,
  },
  devtool: isProduction ? false : 'eval-source-map',
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
        use: [{
          loader: 'ts-loader',
          options: {
            configFile: path.resolve(__dirname, 'tsconfig.server.json'),
          },
        }],
      },
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
      filename: '[name].css',
      chunkFilename: '[name].[contenthash].css',
      ignoreOrder: true,
    }),
    // new BundleAnalyzerPlugin(),
    new CircularDependencyPlugin({
      // exclude detection of files based on a RegExp
      exclude: /node_modules/,
      // add errors to webpack instead of warnings
      failOnError: true,
      // allow import cycles that include an asyncronous import,
      // e.g. via import(/* webpackMode: "weak" */ './file.js')
      allowAsyncCycles: false,
      // set the current working directory for displaying module paths
      cwd: process.cwd(),
    }),
  ],
};

module.exports = ssrEnabled ? [
  clientConfig,
  serverConfig,
] : [
  clientConfig,
];
