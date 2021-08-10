const path = require("path");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const HtmlWebpackPlugin = require("html-webpack-plugin");

const cssRules_withoutModules = {
	test: /\.css$/,
	use: [
		"style-loader", //3. Inject styles into DOM
		{
			loader: 'css-loader',
			options: {
				importLoaders: 1,
				modules: true,
				//localIdentName: '[name]__[local]___[hash:base64:5]'
			}
		},
		{ loader: 'postcss-loader' }
	],
	include: /\.module\.css$/i
};

const cssRules_withModules = {
	test: /\.css$/,
	use: [
		'style-loader',
		'css-loader',
		{ loader: 'postcss-loader' }
	],
	exclude: /\.module\.css$/i
};

const scssRules_withoutModules = {
	test: /\.s[ac]ss$/,
	use: [
		"style-loader", //3. Inject styles into DOM
		{
			loader: 'css-loader',
			options: {
				importLoaders: 1,
				modules: true,
				//localIdentName: '[name]__[local]___[hash:base64:5]'
			}
		},
		{ loader: 'postcss-loader' },
		'sass-loader'
	],
	include: /\.module\.s[ac]ss$/i
};

const scssRules_withModules = {
	test: /\.s[ac]ss$/,
	use: [
		'style-loader',
		'css-loader',
		{ loader: 'postcss-loader' },
		'sass-loader'
	],
	exclude: /\.module\.s[ac]ss$/i
};

module.exports = merge(common, {
	mode: "development",
	devtool: 'source-map',
	output: {
		filename: "[name].bundle.js",
		path: path.resolve(__dirname, "dist")
	},
	plugins: [
		new HtmlWebpackPlugin({
			template: "./src/template.php"
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
