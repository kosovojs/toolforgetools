import {API_URL} from '../config';

function serialize( obj ) {
    let str = '?'+ Object.keys(obj).reduce(function(a, k){
        a.push(k + '=' + encodeURIComponent(obj[k]));
        return a;
    }, []).join('&');
    return str;
}

function formatURL(url, params) {
	let base = '';

	if (url.substr(0, 4) == 'http') {
		base = url;
	} else if (API_URL !== url.substr(0, API_URL.length)) {
		base = API_URL + url;
	}

	const queryString = typeof params === 'undefined' || Object.keys(params).length === 0 ? '' : serialize(params);

	return `${base}${queryString}`;
}

export default formatURL;
