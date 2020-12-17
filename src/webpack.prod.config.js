const { merge } = require('webpack-merge');
const baseConfig = require('./webpack.config');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = merge(baseConfig, {
    devtool: false,
    optimization: {    
        minimize: true,
        minimizer: [
            new TerserPlugin({
                parallel: true,
                terserOptions: {
                    keep_classnames: true,
                    keep_fnames: true,
                    mangle: {
                        reserved: ['Glaemscribe'],
                    }
                },
            }),
        ],
    },
});
