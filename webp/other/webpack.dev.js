const path = require("path");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const HtmlWebpackPlugin = require("html-webpack-plugin");

module.exports = merge(common, {
	mode: "development",
	devtool: 'source-map',
	output: {
		filename: "[name].bundle.js",
		path: path.resolve(__dirname, "dist")
	},
	plugins: [
		new HtmlWebpackPlugin({
			template: "./template.php"
		})
	],
	module: {
		rules: [
			{
			  test: /\.css$/,
			  use: [
				'style-loader',
				{
				  loader: 'css-loader',
				  options: {
					importLoaders: 1,
					modules: true,
					localIdentName: '[name]__[local]___[hash:base64:5]'
				  }
				}
			  ],
			  include: /\.module\.css$/
			},
			{
			  test: /\.css$/,
			  use: [
				'style-loader',
				'css-loader'
			  ],
			  exclude: /\.module\.css$/
			}
		  ]
	}
});