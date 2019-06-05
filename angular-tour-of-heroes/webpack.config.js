const path = require('path');
const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin'); //плагин минимизации

module.exports = {
    entry: {
        'polyfills': './polyfills.ts',
        'app': './main.ts'
    },
    output: {
        path: path.resolve(__dirname, './public'), //папка для компилляции проекта
        publicPath: '/public/',
        filename: "[name].js",
    },
    resolve: {
        extensions: ['.ts', '.js']
    },
    devServer: {
        host: '0.0.0.0',
        port: 4300
        // stats: 'errors-only',
        // compress: true
    },
    context: path.resolve(__dirname, 'src'),
    module: {
        rules: [
        {
          test: /\.html$/,
          loader: 'html-loader'
        },
        {
          test: /\.css$/,
          use: [
            {
              loader: MiniCssExtractPlugin.loader,
              options: {
                hmr: process.env.NODE_ENV === 'development',
              },
            },
            'css-loader',
          ],
        },
        {  // определяем загрузчик для TypeScript файлов
          test: /\.ts$/,
          use: [
          {
            loader: 'awesome-typescript-loader',
            options: { configFileName: path.resolve(__dirname, 'tsconfig.json') }
          },
          'angular2-template-loader'
          ]
        }
        ]
    },
    plugins: [
      new webpack.ContextReplacementPlugin( //управление путями к файлам вне зависимости от ОС
        /angular(\\|\/)core/,
        path.resolve(__dirname, 'src'),
        {}
      ),
      new UglifyJSPlugin(),
      new HtmlWebpackPlugin({
        template: 'index.html'
      }),
      new MiniCssExtractPlugin({
        filename: '[name].css',
        chunkFilename: '[id].css',
      })
    ]
};