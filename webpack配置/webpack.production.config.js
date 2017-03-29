var webpack = require('webpack');
var path = require('path');

var precss = require('precss');
var autoprefixer = require('autoprefixer');

module.exports = {
    // 入口文件地址，不需要写完，会自动查找
    entry: './src/main',
    // 输出
    output: {
        path: path.join(__dirname, './dist'),
        // 文件地址，使用绝对路径形式
        filename: '[name].js',
        //[name]这里是webpack提供的根据路口文件自动生成的名字
        chunkFilename: '[name].js',
        publicPath: '/release/dist/'
            // 公共文件生成的地址
    },
    // 加载器
    module: {
        // 加载器
        loaders: [
            // 解析.vue文件
            {
                test: /\.vue$/,
                loader: 'vue'
            },
            // 转化ES6的语法
            {
                test: /\.js$/,
                loader: 'babel',
                exclude: /node_modules/
            },
            // 编译css并自动添加css前缀
            {
                test: /\.css$/,
                // loader: 'style!css!autoprefixer'
                loader: 'style!css!postcss'
            },
            //.scss 文件想要编译，scss就需要这些东西！来编译处理
            //install css-loader style-loader sass-loader node-sass --save-dev
            {
                test: /\.scss$/,
                loader: 'style!css!sass?sourceMap'
            },
            // 图片转化，小于8K自动转化为base64的编码
            {
                test: /\.(png|jpg|gif)$/,
                loader: 'url-loader?limit=8192'
            },
            // html模板编译？
            {
                test: /\.(html|tpl)$/,
                loader: 'html-loader'
            }
        ]
    },
    postcss: function() {
        return [precss, autoprefixer];
    },
    plugins: [
        // new webpack.optimize.CommonsChunkPlugin('common.js'),
        new webpack.ProvidePlugin({
            Vue: 'vue',
            $: 'webpack-zepto',
            Zepto: 'webpack-zepto'
        }),
        new webpack.optimize.UglifyJsPlugin({
            minimize: true
        })
    ],
    // .vue的配置。需要单独出来配置，其实没什么必要--因为我删了也没保错，不过这里就留这把，因为官网文档里是可以有单独的配置的。
    // vue: {
    //     loaders: {
    //         css: 'style!css!autoprefixer',
    //     }
    // },
    // 转化成es5的语法
    babel: {
        presets: ['es2015'],
        plugins: ['transform-runtime']
    },
    resolve: {
        // require时省略的扩展名，如：require('module') 不需要module.js
        extensions: ['', '.js', '.vue'],
        // 别名，可以直接使用别名来代表设定的路径以及其他
        alias: {
            filter: path.join(__dirname, './src/filters'),
            components: path.join(__dirname, './src/components'),
            vuex_path: path.join(__dirname, './src/vuex')
        }
    }
}