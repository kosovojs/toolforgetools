import React from 'react';
import Typography from '@material-ui/core/Typography';

const articleTitle = (name, mode = 'title', styles = {}) => {
	if (!name) {
		return '';
	}
	const urlSafeTitle = encodeURI(name);
	if (mode === 'all') {
		return (
			<>
				<Typography component='span'>
					<span className={styles.articleName}>
						<a
							href={`https://lv.wikipedia.org/w/index.php?title=${urlSafeTitle}`}
							target='_blank'
							rel='noopener noreferrer'>
							{name}
						</a>
					</span>{' '}
					<small>
						(
						<a
							href={`https://lv.wikipedia.org/w/index.php?title=${urlSafeTitle}&action=edit&section=0`}
							target='_blank'
							rel='noopener noreferrer'>
							labot sākumu
						</a>
						{' | '}
						<a
							href={`https://lv.wikipedia.org/w/index.php?title=${urlSafeTitle}&action=edit`}
							target='_blank'
							rel='noopener noreferrer'>
							labot rakstu
						</a>
						{' | '}
						<a
							href={`https://lv.wikipedia.org/w/index.php?title=Diskusija:${urlSafeTitle}`}
							target='_blank'
							rel='noopener noreferrer'>
							diskusija
						</a>
						{' | '}
						<a
							href={`https://lv.wikipedia.org/w/index.php?title=${urlSafeTitle}&action=history`}
							target='_blank'
							rel='noopener noreferrer'>
							vēsture
						</a>
						)
					</small>
				</Typography>
			</>
		);
	}
	return (
		<Typography
			component='a'
			href={`https://lv.wikipedia.org/w/index.php?title=${urlSafeTitle}`}
			target='_blank'
			rel='noopener noreferrer'>
			{name}
		</Typography>
	);
};

export default articleTitle;
