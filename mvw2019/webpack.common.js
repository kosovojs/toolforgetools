const path = require("path");

module.exports = {
	entry: {
		first: "./src/pages/first/index.js",
		second: "./src/pages/second/index.js",
		newbie: "./src/pages/newbie/index.js",
		//style: "./src/style.css"
	},
	module: {
		rules: [
			{
				test: /\.html$/,
				use: ["html-loader"]
			},
			{
				test: /\.js/,
				exclude: /(node_modules|bower_components)/,
				use: [
					{
						loader: "babel-loader"
					}
				]
			},
			{ test: /\.ejs$/, use: "ejs-compiled-loader" },

			{
				test: /\.(png|jpg|jpeg|gif|ico)$/,
				use: [
					{
						loader: "file-loader",
						options: {
							name: "./img/[name].[hash].[ext]"
						}
					}
				]
			},
			{
				test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
				use: [
					{
						loader: "file-loader",
						options: {
							name: "fonts/[name].[ext]"
						}
					}
				]
			}
		]
	}
};
