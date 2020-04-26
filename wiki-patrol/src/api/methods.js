import { get, post } from './api';

import rcMock from './mocks/rc';
import trustedMock from './mocks/trusted';

const mediawiki = {
	//šim vēl jāpadod starting timestamp
	getRC: () => get('https://lv.wikipedia.org/w/api.php', {
			origin: '*',
			action: 'query',
			format: 'json',
			list: 'recentchanges',
			utf8: 1,
			formatversion: '2',
			rcnamespace: '0',
			rcprop:
				'title|timestamp|ids|comment|flags|loginfo|oresscores|redirect|sizes|tags|user',//patrolled|
			rcshow: 'unpatrolled',
			rclimit: '100',
			rctype: 'edit|new|log',
		}),

	pageRevisions: (title) =>
		get('https://lv.wikipedia.org/w/api.php', {
			origin: '*',
			action: 'query',
			format: 'json',
			prop: 'revisions',
			titles: title,
			utf8: 1,
			formatversion: '2',
			rvprop: 'ids|timestamp|flags|comment|user',
			rvlimit: '50',
		}),

	userinfo: (userList) =>
		get('https://lv.wikipedia.org/w/api.php', {
			origin: '*',
			action: 'query',
			format: 'json',
			list: 'users',
			usprop: 'editcount|rights|registration',
			ususers: userList.join('|'),
		}),
	trustedusers: () => get('https://lv.wikipedia.org/w/api.php', {
			origin: '*',
			action: 'query',
			format: 'json',
			list: 'allusers',
			augroup: 'bureaucrat|autopatrolled|sysop|patroller|bot',
			aulimit: 'max',
		}),

	diffRelative: (from, relative) =>
		get('https://lv.wikipedia.org/w/api.php', {
			origin: '*',
			action: 'compare',
			format: 'json',
			fromrev: from,
			torelative: relative,
			prop: 'diff|diffsize|comment',
			utf8: 1,
			formatversion: '2',
		}),
	diff: (from, to) =>
		get('https://lv.wikipedia.org/w/api.php', {
			origin: '*',
			action: 'compare',
			format: 'json',
			fromrev: from,
			torev: to,
			prop: 'diff|diffsize|comment',
			utf8: 1,
			formatversion: '2',
		}),
};

const tool = {
	//šim vēl jāpadod starting timestamp
	getRC: () => get('', {
		action: 'rc',
		}),

	patrolEdits: (edits) =>
		post('', {
			action: 'patrol_edits',
			edits,
		}),
		saveLastGoodVersion: (article, revID, editsForPatrol) =>
			post('', {
				action: 'save_last_good',
				article,
				rev: revID,
				editsForPatrol
			}),
};

const apiWrapper = {
	mediawiki,
	tool,
};

export default apiWrapper;
