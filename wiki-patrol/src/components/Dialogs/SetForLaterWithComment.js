import React from 'react';
import Button from '@material-ui/core/Button';
import TextField from '@material-ui/core/TextField';
import Dialog from '@material-ui/core/Dialog';
import DialogActions from '@material-ui/core/DialogActions';
import DialogContent from '@material-ui/core/DialogContent';
import DialogTitle from '@material-ui/core/DialogTitle';

import { connect } from 'react-redux'
import { fetchNextArticle, saveArticle, putArticleInQueqe } from '../Article/articleSlice';

import PropTypes from 'prop-types';

import { makeStyles } from '@material-ui/core/styles';

const useStyles = makeStyles(theme => ({
	root: {
		'& .MuiTextField-root': {
			margin: theme.spacing(1),
			width: 200
		}
	}
}));

const mapDispatch = { putArticleInQueqe };

function SetForLaterWithComment({ isOpen, putArticleInQueqe, modalOpenHandle }) {
	const classes = useStyles();
	const [pamatojums, setPamatojums] = React.useState('');

	const handleClose = () => {
		modalOpenHandle(false);
	};

	const handleSave = () => {
		putArticleInQueqe(pamatojums)
		setPamatojums('');
		modalOpenHandle(false);
	};

	const handlePamatojumsChange = event => {
		setPamatojums(event.target.value);
	};

	return (
		<div className={classes.root}>
			<Dialog
				fullWidth={true}
				disableEnforceFocus={false}
				open={isOpen}
				aria-labelledby='form-dialog-title'
				maxWidth='md'>
				<DialogTitle id='form-dialog-title'>Pievienot komentāru</DialogTitle>
				<DialogContent>
						<TextField
							//autoFocus
							margin='dense'
							id='name'
							label='Komentārs'
							type='text'
							onChange={handlePamatojumsChange}
							value={pamatojums}
							fullWidth
						/>
				</DialogContent>
				<DialogActions>
					<Button onClick={handleSave} color='secondary'>
						Saglabāt
					</Button>
					<Button onClick={handleClose} color='primary'>
						Atcelt
					</Button>
				</DialogActions>
			</Dialog>
		</div>
	);
}

SetForLaterWithComment.propTypes = {
	putArticleInQueqe: PropTypes.func.isRequired,
	isOpen: PropTypes.bool,
	modalOpenHandle: PropTypes.func
};

export default connect(
	null,
	mapDispatch
)(SetForLaterWithComment)
