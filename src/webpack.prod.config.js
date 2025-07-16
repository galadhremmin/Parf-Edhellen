const { merge } = require('webpack-merge');
const baseConfig = require('./webpack.config');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = [
    baseConfig.filter(c => c.target === 'node')[0],
    merge(
        baseConfig.filter(c => target === 'web')[0],
        {
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
        }
    ),
];


