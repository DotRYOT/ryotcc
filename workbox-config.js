module.exports = {
	globDirectory: 'app',
	globPatterns: [
		'**/*.{js,scss,html,php,json,svg,md,css,ico,png,jpeg,jpg,psd,eps,pdf}'
	],
	swDest: 'app/sw.js',
	ignoreURLParametersMatching: [
		/^utm_/,
		/^fbclid$/
	]
};