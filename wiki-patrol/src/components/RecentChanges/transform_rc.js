export default function rcTransformer(data) {
	const recentchanges = data.query.recentchanges;

	const finalRC = {};

	recentchanges.forEach(rc => {
		const {title, ...revisionInfo} = rc;

		if (title in finalRC) {
			finalRC[title].push(revisionInfo);
		} else {
			finalRC[title] = [revisionInfo];
		}


	})

	return finalRC
}
