import {get, post} from './api';

const mediawiki = {
	/* getArticleText: (title) => get('https://lv.wikipedia.org/w/api.php',{
		action: "parse",
		origin: '*',
		format: "json",
		page: title,
		redirects: 1,
		prop: "text"//|langlinks|links|revid|iwlinks
	}), */

	linksHere: (title) => get('https://lv.wikipedia.org/w/api.php',{
		action: "query",
		origin: '*',
		format: "json",
		prop: "linkshere",
		titles: title,
		lhprop: 'title',
		lhnamespace: '0',
		lhlimit: 'max',
		formatversion: '2'
	}),

	openSearch: (lang, title) => get(`https://${lang}.wikipedia.org/w/api.php`,{
		action: "opensearch",
		origin: '*',
		format: "json",
		formatversion: 2,
		search: title,
		namespace: 0,
		limit: 10,
		suggest: true
	}),

	/*

	deleteLog: (title) => get(`https://lv.wikipedia.org/w/api.php`,{
		action: "query",
		origin: '*',
		format: "json",
		list: 'logevents',
		letype: 'delete',
		letitle: title
	}),

	pageviews: (article, from, to) => get(`https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/lv.wikipedia/all-access/user/${article.replace(/ /g,'_')}/daily/${from}00/${to}00`),

	summary: (lang, article) => get(`https://${lang}.wikipedia.org/api/rest_v1/page/summary/${article.replace(/ /g,'_')}`), */
}

const tool = {
	nextArticle: () => get('',{
		action: 'next_suggestion'
	}),

	saveArticle: (title, message) => post('',{
		action: 'save_action',
		title, message
	}),

	redirect: (from, to) => post('',{
		action: 'redirect',
		from, to
	}),
}

const apiWrapper = {
	mediawiki,
	tool
}

export default apiWrapper;
