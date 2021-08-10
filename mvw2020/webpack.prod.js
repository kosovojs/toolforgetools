const path = require("path");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");

const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");

const onePage = ({ title, template, filename, chunk }) =>
	new HtmlWebpackPlugin({
		template: `./src/pages/${template}.ejs`,
		filename: `../${filename}.html`,
		title,
		chunks: ["vendor", chunk, "common"],
		meta: {
			viewport: "width=device-width, initial-scale=1, shrink-to-fit=no",
			"theme-color": "#4285f4"
		},
		minify: {
			collapseWhitespace: true,
			removeComments: true,
			removeRedundantAttributes: true,
			removeScriptTypeAttributes: true,
			removeStyleLinkTypeAttributes: true
		}
	});

module.exports = merge(common, {
	mode: "production",
	output: {
		filename: "js/[name].[contentHash].js",
		path: path.resolve(__dirname, "dist")
	},
	optimization: {
		minimizer: [
			new OptimizeCssAssetsPlugin(),
			new TerserPlugin()
		],
		splitChunks: {
			cacheGroups: {
				default: false,
				vendors: false,
				vendor: {
					name: "vendor",
					chunks: "all",
					test: /node_modules/,
					priority: 20
				},
				common: {
					name: "common",
					minChunks: 2,
					chunks: "all",
					priority: 10,
					reuseExistingChunk: true,
					enforce: true
				}
			}
		}
	},
	plugins: [
		new CleanWebpackPlugin(),

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
		}),
		new MiniCssExtractPlugin({
			filename: "css/[name].[contentHash].css",
			//chunkFilename: 'css/[id]-[name].[contentHash].css',
			ignoreOrder: false,
		})
	],
	module: {
		rules: [
			{
				test: /\.css$/,
				use: [MiniCssExtractPlugin.loader, "css-loader"]
			}
		]
	}
});
