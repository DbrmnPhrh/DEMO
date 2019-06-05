const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const extractPlugin = new ExtractTextPlugin({
            filename: './style.css'
        });

module.exports = {
    entry: './index.js',
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'public')
    },
    devServer: {
        host: '0.0.0.0',
        port: 8000,
        stats: 'errors-only',
        compress: true
    },
    context: path.resolve(__dirname, 'src'),
    plugins: [
        // new CleanWebpackPlugin(),
        new HtmlWebpackPlugin({
            template: 'index.html'
        }),
        extractPlugin
    ],
    module: {
        rules: [{
            test: /\.(jpg|png|gif|svg)$/,
            use: [{
                    loader: 'file-loader',
                    options: {
                        name: '[name].[ext]',
                        outputPath: './assets/',
                    }
            }]
        },
        {
            test: /\.css$/,
            use: extractPlugin.extract({
                use: ["css-loader", "sass-loader", "postcss-loader"],
                fallback: 'style-loader'
            })
        },
        {
            test: /\.js$/,
            use: {
                loader: 'babel-loader'
            }
        }]
    }
};