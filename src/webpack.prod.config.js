const { merge } = require('webpack-merge');
const baseConfig = require('./webpack.config');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

const serverConfig = baseConfig.find(c => c.target === 'node');
const clientConfig = merge(
    baseConfig.find(c => c.target === 'web'),
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
                new CssMinimizerPlugin({
            minimizerOptions: {
                preset: ['default', { calc: false }],
            },
        }),
            ],
        },
    },
);

module.exports = serverConfig ? [
    serverConfig,
    clientConfig,
] : [
    clientConfig,
];
