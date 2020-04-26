module.exports = {
	entry: "./src/index.js",
	module: {
		rules: [
			{
				test: /\.html$/,
				use: ["html-loader"]
			}, {
				test: /\.js/,
				exclude: /(node_modules|bower_components)/,
				use: [{
					loader: 'babel-loader'
				}]
			},
			//https://stackoverflow.com/questions/52704890/loading-fontawesome-via-webpack-fonts-do-not-load
			//https://stackoverflow.com/questions/52219401/webpack-style-loader-css-loader-url-path-resolution-not-working
			/* {
				test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
				use: [{
					loader: 'file-loader',
					options: {
						name: 'fonts/[name].[contentHash].[ext]',
						outputPath: (url, resourcePath, context) => {
							// `resourcePath` is original absolute path to asset
							// `context` is directory where stored asset (`rootContext`) or `context` option

							// To get relative path you can use
							// const relativePath = path.relative(context, resourcePath);

							return `./${url}`;
						  }
					}
				}]
			} */
		]
	}
};
