const path = require("path");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");

const WebpackAssetsManifest = require("webpack-assets-manifest");

const cssRules_withoutModules = {
	test: /\.css$/,
	use: [
		MiniCssExtractPlugin.loader, //3. Inject styles into DOM
		{
			loader: 'css-loader',
			options: {
			  importLoaders: 1,
			  modules: true,
			  //localIdentName: '[name]__[local]___[hash:base64:5]'
			}
		  },
		  { loader: 'postcss-loader'},
		  {
			loader: 'resolve-url-loader',
			options: {
			  attempts: 1,
			  sourceMap: true
			}
		  },
	],
	include: /\.module\.css$/i
};

const cssRules_withModules = {
	test: /\.css$/,
	use: [
		MiniCssExtractPlugin.loader,
	  'css-loader',
	 { loader: 'postcss-loader'},
	 {
		loader: 'resolve-url-loader',
		options: {
		  attempts: 1,
		  sourceMap: true
		}
	  },
	],
	exclude: /\.module\.css$/i
};

const scssRules_withoutModules = {
	test: /\.s[ac]ss$/,
	use: [
		MiniCssExtractPlugin.loader, //3. Inject styles into DOM
		{
			loader: 'css-loader',
			options: {
			  importLoaders: 1,
			  modules: true,
			  //localIdentName: '[name]__[local]___[hash:base64:5]'
			}
		  },
		 { loader: 'postcss-loader'},
		 {
			loader: 'resolve-url-loader',
			options: {
			  attempts: 1,
			  sourceMap: true
			}
		  },
			'sass-loader'
	],
	include: /\.module\.s[ac]ss$/i
};

const scssRules_withModules = {
	test: /\.s[ac]ss$/,
	use: [
		MiniCssExtractPlugin.loader,
	  'css-loader',
	  { loader: 'postcss-loader'},
	  {
		loader: 'resolve-url-loader',
		options: {
		  attempts: 1,
		  sourceMap: true
		}
	  },
		'sass-loader'
	],
	exclude: /\.module\.s[ac]ss$/i
};

module.exports = merge(common, {
	mode: "production",
	devtool: 'source-map',
	output: {
		//publicPath: "/",
		filename: "js/[name].[contentHash].js",
		path: path.resolve(__dirname, "dist")
	},
	optimization: {
		splitChunks: {
			chunks: 'all'
		},
		minimizer: [
			new OptimizeCssAssetsPlugin(),
			new TerserPlugin(),
			new HtmlWebpackPlugin({
				filename: '../index.php',
				template: "./src/template.php",
				minify: {
					collapseWhitespace: true,
					removeComments: true,
					removeRedundantAttributes: true,
					removeScriptTypeAttributes: true,
					removeStyleLinkTypeAttributes: true
				}
			})
		]
	},
	plugins: [
		new CleanWebpackPlugin(),

		new MiniCssExtractPlugin({
			filename: 'css/[name].[contentHash].css'
		}),
		new WebpackAssetsManifest({
			output: 'manifest.json',
			writeToDisk: true,
			sortManifest: true,
			merge: true,
		  })
	],
	module: {
		rules: [
			cssRules_withoutModules,
			cssRules_withModules,
			scssRules_withoutModules,
			scssRules_withModules
		]
	}
});
