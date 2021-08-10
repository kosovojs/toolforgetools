

//https://stackoverflow.com/questions/31353213/does-javascript-support-array-list-comprehensions-like-python
Array.prototype.compreh = function (xs) {
	return this.reduce((acc, f) => acc.concat(xs.map(f)), [])
}

const onlyUnique = (value, index, self) => self.indexOf(value) === index;

const stats = (apiResults) => {
	if (!apiResults.hasOwnProperty('query')) {
		return {};
	}
	let data = apiResults.query.pages[Object.keys(apiResults.query.pages)[0]];

	const revs = data['revisions'];
	let outstats = {};

	if (Object.keys(data).includes('redirects')) {
		var redirects = [x => x['title']].compreh(data['redirects']);
	} else {
		var redirects = [];
	}

	let deletable = false;

	if (data.categories && data.categories.length>0) {

		data.categories.forEach(element => {
			if (element.title === 'Kategorija:Dzēšanai izvirzītās lapas') {
				deletable = true;
				return;
			}
		});

	}

	outstats['to_delete'] = deletable;


	outstats['no_iw'] = !Object.keys(data).includes('langlinks');

	outstats['first_edit'] = { 'time': revs[0]['timestamp'], 'user': revs[0]['user'] }

	outstats['last_edit'] = { 'time': revs[revs.length - 1]['timestamp'], 'user': revs[revs.length - 1]['user'] }

	var users = [x => x['user']].compreh(revs);

	const sorted_users = users.filter(onlyUnique);

	outstats['users'] = sorted_users;
	outstats['reds'] = redirects;

	return outstats;
}

export default stats;
