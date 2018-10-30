const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const AsyncChunkNames = require('webpack-async-chunk-names-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');
var WebpackNotifierPlugin = require('webpack-notifier');

// Reads `.env` configuration values to `process.env`
require('dotenv').config();

const devMode = process.env.NODE_ENV !== 'production';
const version = process.env.ED_VERSION;

const outputPath = path.resolve(__dirname, `public/v${version}`);

module.exports = {
  entry: {
    index: './resources/assets/ts/index.tsx'
  },
  output: {
    filename: 'main.js',
    path: outputPath,
    chunkFilename: '[name].js'
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
          test: /node_modules/
        },

        // common chunks, like components that are used by at least
        // in two separate chunks.
        common: {
          name: 'common',
          minChunks: 2,
          chunks: 'async',
          priority: 10,
          reuseExistingChunk: true,
          enforce: true
        }
      }
    }
  },
  devtool: 'source-map',
  resolve: {
    extensions: [ 
      '.ts', 
      '.tsx', 
      '.js', 
      '.json' 
    ]
  },
  module: {
    rules: [
      { 
        test: /\.tsx?$/, 
        loader: 'awesome-typescript-loader' 
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
          devMode ? 'style-loader' : MiniCssExtractPlugin.loader, // creates style nodes from JS strings
          "css-loader", // translates CSS into CommonJS
          "sass-loader" // compiles Sass to CSS, using Node Sass by default
        ]
      },
      {
        test: /\.(eot|ttf|woff|woff2|svg)$/,
        use: [
          'file-loader?name=fonts/[name].[ext]'
        ]
      }
    ]
  },
  plugins: [
    new CleanWebpackPlugin(outputPath),
    new MiniCssExtractPlugin({
      // Options similar to the same options in webpackOptions.output
      // both options are optional
      filename: "[name].css",
      chunkFilename: "[id].css"
    }),
    new AsyncChunkNames(),
    new WebpackNotifierPlugin(),
  ],
};
