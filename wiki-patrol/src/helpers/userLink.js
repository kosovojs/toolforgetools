import React from 'react';

const userInfoLink = name => {
	if (!name) {
		return '';
	}
	const urlSafeName = encodeURI(name);
	return <>
		<a target="_blank" rel="noopener noreferrer" href={`https://lv.wikipedia.org/wiki/Dalībnieks:${urlSafeName}`}>{name.replace(/_/g, ' ')}</a>
	{" "}
	<small>(
		<a target="_blank" rel="noopener noreferrer" href={`https://lv.wikipedia.org/wiki/Dalībnieka diskusija:${urlSafeName}`}>diskusija</a>
		{" | "}
		<a target="_blank" rel="noopener noreferrer" href={`https://lv.wikipedia.org/wiki/Special:Contributions/${urlSafeName}`}>devums</a>
	)</small>
	</>;
}

export default userInfoLink;
