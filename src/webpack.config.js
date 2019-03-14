const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const WebpackNotifierPlugin = require('webpack-notifier');

// const AsyncChunkNames = require('webpack-async-chunk-names-plugin');

// Reads `.env` configuration values to `process.env`
require('dotenv').config();

const devMode = process.env.NODE_ENV !== 'production';
const bundleCssWithJavaScript = false;
const version = process.env.ED_VERSION;

const sourcePath = path.resolve(__dirname, 'resources/assets/ts');
const publicPath = `/v${version}`;
const outputPath = path.resolve(__dirname, `public/${publicPath}`);

module.exports = {
  entry: {
    index: `${sourcePath}/index.tsx`,
    'style-auth': `${sourcePath}/apps/auth/index.scss`,
    'style-discuss': `${sourcePath}/apps/discuss/index.scss`
  },
  output: {
    filename: '[name].js',
    path: outputPath,
    chunkFilename: '[name].js',
    publicPath: `${publicPath}/`,
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        // disable Webpack 4's default cache groups.
        default: false,
        vendors: false,

        // vendor bundle
        vendor: {
          name: 'vendor',
          chunks: 'all', // async and sync chunks
          test: /node_modules/,
        },

        // common chunks, like components that are used by at least
        // in two separate chunks.
        common: {
          name: 'common',
          minChunks: 2,
          chunks: 'async',
          priority: 10,
          reuseExistingChunk: true,
          enforce: true,
        }
      }
    }
  },
  devtool: 'source-map',
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
        /* this is a much more elegant approach, but it will need additional effort to work with Glaemscribe's
        resource manager, as *.glaem.js files expects Glaemscribe to be a global variable.
        loaders: [
          'imports-loader?this=>window',
          'exports-loader?Glaemscribe',
        ],
        */
        use: 'script-loader',
      },
      {
        test: /\.(glaem|cst)\.js$/,
        use: 'script-loader',
      },
      { 
        test: /\.tsx?$/, 
        loader: 'ts-loader',
      },
      // All output '.js' files will have any sourcemaps re-processed by 'source-map-loader'.
      { 
        enforce: 'pre', 
        test: /\.js$/, 
        loader: 'source-map-loader' 
      },
      {
        test: /\.scss$/,
        use: [
          bundleCssWithJavaScript ? 'style-loader' : MiniCssExtractPlugin.loader, // creates style nodes from JS strings
          "css-loader?modules=false", // translates CSS into CommonJS
          "sass-loader" // compiles Sass to CSS, using Node Sass by default
        ]
      },
      {
        test: /\.(eot|ttf|svg)$/,
        use: [
          'file-loader?name=fonts/[name].[ext]'
        ]
      },
      {
        test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: 'url-loader?name=fonts/[name].[ext]&mimetype=application/font-woff'
      },
    ]
  },
  plugins: [
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      // Options similar to the same options in webpackOptions.output
      // both options are optional
      filename: "[name].css",
      chunkFilename: "[id].css"
    }),
    // new AsyncChunkNames(),
    new WebpackNotifierPlugin(),
  ],
};
