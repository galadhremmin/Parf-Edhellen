module.exports = {
  entry: {
    index: './resources/assets/ts/index.tsx'
  },
  output: {
    filename: '[name].bundle.js',
    path: __dirname + '/public/dist'
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendors: {
          test: /[\\/]node_modules[\\/]/,
          chunks: 'all', 
          priority: 1
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
      }
    ]
  }
};
