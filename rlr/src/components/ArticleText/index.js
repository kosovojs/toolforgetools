import React, {useEffect, useState} from 'react';
import PropTypes from 'prop-types'
import './mediawikistyle.scss';
import { connect } from 'react-redux';
import {updateArticleTitle} from '../Article/articleSlice';
import api from '../../api/methods';
import Typography from '@material-ui/core/Typography';

//https://lv.wikipedia.org/w/load.php?lang=lv&modules=ext.cite.styles%7Cext.dismissableSiteNotice.styles%7Cext.echo.styles.badge%7Cext.uls.interlanguage%7Cext.visualEditor.desktopArticleTarget.noscript%7Cext.wikimediaBadges%7Cmediawiki.legacy.commonPrint%2Cshared%7Cmediawiki.skinning.interface%7Coojs-ui.styles.icons-alerts%7Cskins.vector.styles%7Cwikibase.client.init&only=styles&skin=vector
const removeLinks = text => {
    text = text.replace(/<!--[\s\S]*?-->/g, '');
    text = text.replace(/&#160;/g, ' ');
    text = text.replace(/<h2>/g, '<h4>');
    text = text.replace(/<h3>/g, '<h5>');
	//href=\"//lv.wikipedia.org/w

    text = text.replace(/"\/wiki/g, '"https://lv.wikipedia.org/wiki');
    text = text.replace(/"\/w\/index/g, '"https://lv.wikipedia.org/w/index');

    text = text.replace(/"\/\/upload\.wikimedia.org/g, '"https://upload.wikimedia.org');
	//text = text.replace(/<\/?a[^>]*>/g, '');

    return text;
}

const ArticleText = ({title, updateArticleTitle}) => {
	const [articleText, setArticleText] = useState('');
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState(false);
	const [errMsg, setErrMsg] = useState('');
	const [deleteLog, setDeleteLog] = useState([]);

	useEffect(() => {
		//'Aleksandrs Pētersons (politiķis)'
		setLoading(true);
		setError(false);
		api.mediawiki.getArticleText(title).then(res => {
			if ('error' in res) {
				setError(true);
				if (res.error.code === 'missingtitle') {

					api.mediawiki.deleteLog(title).then(res => {

						const logEventList = res.query.logevents;
						if (logEventList.length === 0) {
							setErrMsg('Raksts ar šādu nosaukumu nepastāv');
							return;
						}

						const deleteActions = [];

						logEventList.forEach(element => {
							deleteActions.push(`${element.timestamp} rakstu izdzēsa dalībnieks ${element.user} ar šādu pamatojumu: «${element.comment}»`);
						});

						setErrMsg('Raksts ar šādu nosaukumu nepastāv');
						setDeleteLog(deleteActions);
					})
					.catch(err => {
						setLoading(false);
						setErrMsg(`Problēmas ar Vikipēdijas raksta izgūšanu: ${JSON.stringify(err)}`);
					});


				} else {
					setErrMsg(`Šāds raksts nepastāv`);
				}
			} else {
				const pageText = res.parse.text['*'];
				setArticleText(pageText);
				/* if (title !== res.parse.title) {
					updateArticleTitle(res.parse.title);
				} */
			}
		})
		.catch(err => {
			setErrMsg(`Problēmas ar Vikipēdijas raksta izgūšanu: ${JSON.stringify(err)}`);
		})
		.finally(() => {
			setLoading(false);
		})
		//setArticleText('AA--AA-'+title);
	}, [title]);

	if (loading) {
		return 'Ielādē rakstu...';
	}

	return <div>
		{error ? <Typography component='div' variant="body1">{errMsg}{deleteLog.length> 0 && <><br />Ieraksti no dzēšanas reģistra:<br /><ul>{deleteLog.map((elem, key) => <li key={key}>{elem}</li>)}</ul></>}</Typography> : <div className="articlePreview" dangerouslySetInnerHTML={{__html: removeLinks(articleText)}} />}
	</div>;
}

ArticleText.propTypes = {
	title: PropTypes.string,
	updateArticleTitle: PropTypes.func
}

const mapDispatchToProps = { updateArticleTitle }

const mapStateToProps = state => ({
	title: state.article.title
})

export default connect(
	mapStateToProps,
	mapDispatchToProps
)(ArticleText)
