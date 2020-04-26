import React from 'react';
import Button from '@material-ui/core/Button';
import TextField from '@material-ui/core/TextField';
import Dialog from '@material-ui/core/Dialog';
import DialogActions from '@material-ui/core/DialogActions';
import DialogContent from '@material-ui/core/DialogContent';
import DialogContentText from '@material-ui/core/DialogContentText';
import DialogTitle from '@material-ui/core/DialogTitle';

import PropTypes from 'prop-types';

import api from '../../api/methods';
import clsx from 'clsx';
import './Copyvio.css';

import exampleData from './copyvio_resp.js';

function copyvioFormat(data) {
	if (Object.keys(data).length == 0) {
		return;
	}
	const { status, best, error } = data;
	//meta: {cache_time, cached},

	if (status === 'error') {
		return `Kļūda! Atbilde no autortiesību pārkāpumu servisa: ${error.info}`;
	}

	const { url, confidence, violation } = best;

	return (
		<span
			className={clsx({
				status_copyvioViolation: violation === 'suspected',
				status_copyvioPossible: violation === 'possible'
			})}>
			Labākais avots:{' '}
			<a href={url} target='_blank' rel='noopener noreferrer'>
				{url}
			</a>
			<br />
			Pārliecība par autortiesību pārkāpumu: {(confidence * 100).toFixed(2)}%
		</span>
	);
}

export default function FormDialog({ isOpen, modalOpenHandle, title }) {
	//const [open, setOpen] = React.useState(false);
	const [loading, setLoading] = React.useState(false);
	const [loaded, setLoaded] = React.useState(false);
	const [copyvioData, setCopyvioData] = React.useState({});

	React.useEffect(() => {
		setCopyvioData({});
		//return setOpen(false);
	}, [title]);

	/* React.useEffect(() => {
		setOpen(isOpen);
		//return setOpen(false);
	}, [isOpen]); */

	const handleClose = () => {
		//setOpen(false);
		modalOpenHandle('');
	};

	const handleCheck = () => {
		setLoading(true);

		/* setTimeout(() => {
			setCopyvioData({"status": "ok", "meta": {"time": 10.828045845031738, "queries": 8, "cached": false, "redirected": false}, "page": {"title": "Priekules rajons", "url": "https://lv.wikipedia.org/wiki/Priekules_rajons"}, "best": {"url": "https://lv.wikipedia.org/wiki/Priekules_rajons", "confidence": 0.0, "violation": "none"}, "sources": [{"url": "https://lv.wikipedia.org/wiki/Priekules_rajons", "confidence": 0.0, "violation": "none", "skipped": true, "excluded": true}, {"url": "https://www.wikiwand.com/lv/Liep%C4%81jas_apri%C5%86%C4%B7is", "confidence": 0.0, "violation": "none", "skipped": true, "excluded": true}]});
			setLoading(false);
			setLoaded(true);

		}, 200); */
		api.tool.checkCopyvio(title).then(res => {
			setCopyvioData(res);
			setLoading(false);
			setLoaded(true);
		});
	};

	return (
		<div>
			<Dialog disableEnforceFocus={false} open={isOpen} aria-labelledby='form-dialog-title'>
				<DialogTitle id='form-dialog-title'>
					Pārbaudīt, vai šis raksts nav autortiesību pārkāpums
				</DialogTitle>
				<DialogContent>
					{loading ? 'Pārbauda...' : (loaded ? copyvioFormat(copyvioData) : null)}
				</DialogContent>
				<DialogActions>
					<Button onClick={handleCheck} color='secondary'>
						Pārbaudīt
					</Button>
					<Button onClick={handleClose} color='primary'>
						Aizvērt
					</Button>
				</DialogActions>
			</Dialog>
		</div>
	);
}

FormDialog.propTypes = {
	isOpen: PropTypes.bool,
	modalOpenHandle: PropTypes.func,
	title: PropTypes.string
};
