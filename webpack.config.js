'use strict';

const NODE_ENV = process.env.NODE_ENV || 'development';
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const AssetsPlugin = require('assets-webpack-plugin');
const ConcatPlugin = require('webpack-concat-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const webpack = require('webpack');
const glob = require('glob');
const rimraf = require('rimraf');
const fs = require('fs');

// Plugin
const extractTextPluginCss = new ExtractTextPlugin({
  filename: '[name].css?[contenthash]',
  allChunks: true
});

class Project {
  constructor() {
    this._aliases = null;
    this.requireInitDist = __dirname + '/src/Core/Resources/assets/js/lib/require-init.dist.js';
    this.requireInit = __dirname + '/src/Core/Resources/assets/js/lib/require-init.js';
  }
  
  generateRequireInit() {
    let self = this,
      requireAliasTemplate = null,
      cases = [];
    
    fs.readFile(self.requireInitDist, 'utf8', function (err, content) {
      if (err) {
        return console.log(err);
      }
      
      requireAliasTemplate = content.match(/\/\* case template:([\s\S]+)\*\//)[1];
      
      Object.keys(self.getAliases()).forEach(function (aliasName) {
        cases.push(requireAliasTemplate.replace(/{{name}}/g, aliasName));
      });
      
      content = content.replace('// {{cases}}', cases.join("\n"));
      
      fs.writeFile(self.requireInit, content, function (err) {
        if (err) {
          console.log(err);
        }
        
        console.log('Generate require-init.js');
      });
    });
  }
  
  getAliases() {
    let self = this;
    
    if (!self._aliases) {
      self._aliases = {};
      
      let vendor = {};
      Object.keys(vendor).forEach(function (alias) {
        self._aliases[alias] = vendor[alias];
      });
      
      let libBasePath = __dirname + '/src/Core/Resources/assets/js/lib/';
      glob.sync(libBasePath + '**/*.js').forEach(function (path) {
        let alias = 'lib-' + Project.generateAliasName(path, libBasePath);
        self._aliases[alias] = path;
      });
      
      let moduleBasePath = __dirname + '/src/Core/Resources/assets/js/module/';
      glob.sync(moduleBasePath + '**/*.js').forEach(function (path) {
        let alias = Project.generateAliasName(path, moduleBasePath);
        self._aliases[alias] = path;
      });
      
      let widgetBasePath = __dirname + '/src/Core/Resources/assets/js/widget/';
      glob.sync(widgetBasePath + '**/*.js').forEach(function (path) {
        let alias = Project.generateAliasName(path, widgetBasePath);
        self._aliases[alias] = path;
      });
      
      let visualComponentBasePath = __dirname + '/src/Core/Resources/assets/visual-component/';
      glob.sync(visualComponentBasePath + '**/*.js').forEach(function (path) {
        let alias = path.match(/([^\/]+)\.js$/)[1];
        self._aliases[alias] = path;
      });
      
      let phpModulePath = __dirname + '/src/';
      glob.sync(phpModulePath + '*/Resources/assets/js/**/*.js').forEach(function (path) {
        if (path.match(/\/[A-Z][^\/]*\.js$/) || path.match(/\/src\/Core\//)) {
          return;
        }
        
        let alias = path.match(/([^\/]+)\.js$/)[1];
        self._aliases[alias] = path;
      });
      
      delete self._aliases['lib-require-init.dist'];
      delete self._aliases['main'];
      delete self._aliases['admin-main'];
      
      console.log(self._aliases);
    }
    
    return self._aliases;
  }
  
  static generateAliasName(filePath, basePath) {
    return filePath
      .replace(basePath, '')
      .replace(/\//g, '-')
      .replace(/\.[^\.]+$/, '');
  }
  
  getPlugins() {
    let plugins = [];
    
    plugins.push({
      apply: (compiler) => {
        rimraf.sync(compiler.options.output.path);
      }
    });
    
    plugins.push(extractTextPluginCss);
    
    plugins.push(new AssetsPlugin({
      prettyPrint: true,
      filename: 'assets.json',
      path: __dirname + '/public/assets/'
    }));
    
    plugins.push(new ConcatPlugin({
      uglify: NODE_ENV === 'production',
      useHash: true, // md5 file
      sourceMap: false, // generate sourceMap
      name: 'jquery', // used in html-webpack-plugin
      fileName: '[name].js?[hash]',
      filesToConcat: [
        __dirname + '/node_modules/jquery/dist/jquery.min.js',
        __dirname + '/node_modules/jquery-migrate/dist/jquery-migrate.min.js',
        __dirname + '/node_modules/what-input/dist/what-input.min.js'
      ]
    }));
    
    plugins.push(new ConcatPlugin({
      uglify: NODE_ENV === 'production',
      useHash: true, // md5 file
      sourceMap: false, // generate sourceMap
      name: 'bootstrap', // used in html-webpack-plugin
      fileName: '[name].js?[hash]',
      filesToConcat: [
        __dirname + '/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'
      ]
    }));
    
    plugins.push(new CopyWebpackPlugin([
      {
        from: __dirname + '/src/Core/Resources/assets/img',
        to: __dirname + '/public/assets/core/img'
      }
    ]));
    
    if (NODE_ENV === 'production') {
      plugins.push(new UglifyJsPlugin({
        test: /\.js($|\?)/i,
        cache: true,
        parallel: 4,
        sourceMap: true
      }));
    }
    
    plugins.push(new webpack.LoaderOptionsPlugin({
      debug: NODE_ENV !== 'production'
    }));
    
    return plugins;
  }
}

let project = new Project();

project.generateRequireInit();

module.exports = {
  context: __dirname,
  entry: {
    style: [
      './src/Core/Resources/assets/sass/style.sass'
    ],
    bootstrap: './src/Core/Resources/assets/sass/bootstrap.scss',
    main: [
      './src/Core/Resources/assets/js/main.js',
    ],
    // admin_style: './src/Core/Resources/assets/scss/admin/style.scss',
    // admin_main: './src/Core/Resources/assets/js/admin/main.js',
    font_awesome: [__dirname + '/node_modules/font-awesome/scss/font-awesome.scss']
  },
  
  output: {
    path: __dirname + '/public/assets',
    publicPath: '/assets/',
    filename: '[name].js?[chunkhash]',
    chunkFilename: 'js/chunk/[name].[id].js?[chunkhash]',
    library: '[name]'
  },
  
  resolve: {
    extensions: ['.js', '.css', '.scss', '.sass'],
    alias: project.getAliases()
  },
  
  externals: {
    "jquery": "jQuery"
  },
  
  devtool: NODE_ENV === 'development' ? "source-map" : false,
  
  watchOptions: {
    aggregateTimeout: 300
  },
  
  module: {
    rules: [
      {
        test: /\.js$/, // include .js files
        enforce: "pre", // preload the jshint loader
        exclude: /node_modules|bower_components|thirdparty|/, // exclude any and all files in the node_modules folder
        use: [
          {
            loader: "jshint-loader",
            options: {
              camelcase: true,
              emitErrors: false,
              failOnHint: false
            }
          }
        ]
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ["babel-preset-es2015"].map(require.resolve)
          }
        }
      },
      {
        test: /\.jade$/,
        use: {
          loader: "jade-loader"
        }
      },
      {
        test: /\.css$/,
        use: extractTextPluginCss.extract(['css-loader', 'resolve-url-loader'])
      },
      {
        test: /\.scss$/,
        use: extractTextPluginCss.extract(['css-loader?sourceMap', 'resolve-url-loader?sourceMap', 'sass-loader?sourceMap'])
      },
      {
        test: /\.sass$/,
        use: extractTextPluginCss.extract(['css-loader?sourceMap', 'resolve-url-loader?sourceMap', 'sass-loader?sourceMap'])
      },
      {
        test: /\.(gif|png|jpg|svg|ttf|eot|woff|woff2)(\?\S*)?/,
        use: {
          loader: 'file-loader',
          options: {
            filename: '[path][name].[ext]?[hash:6]'
          }
        }
      }
    ]
  },
  
  plugins: project.getPlugins()
};
