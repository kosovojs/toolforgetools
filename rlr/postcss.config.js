module.exports = {
	plugins: [
		require('precss'),
		require('autoprefixer'),
		require('postcss-preset-env'),
		require('postcss-normalize'),
		require('css-declaration-sorter'),
	]
}
