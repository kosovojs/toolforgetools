const path = require("path");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const webpack = require("webpack");

const onePage = ({ title, template, filename, chunk }) =>
	new HtmlWebpackPlugin({
		template: `./src/pages/${template}.ejs`,
		filename: `${filename}.html`,
		title,
		chunks: [chunk, "vendors"]
	});

module.exports = merge(common, {
	mode: "development",
	devtool: "source-map",
	output: {
		filename: "js/[name].bundle.js",
		path: path.resolve(__dirname, "dist")
	},
	plugins: [
		new webpack.ProvidePlugin({
			$: "jquery",
			jQuery: "jquery"
		}),
		onePage({
			template: "first/index",
			filename: "index",
			title: "Pirmā kārta",
			chunk: "first"
		}),
		onePage({
			template: "second/index",
			filename: "second",
			title: "Otrā kārta",
			chunk: "second"
		}),
		onePage({
			template: "newbie/index",
			filename: "newbie",
			title: "Labākais jauniņais",
			chunk: "newbie"
		})
	],
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ["style-loader", "css-loader"]
			}
		]
	}
});
