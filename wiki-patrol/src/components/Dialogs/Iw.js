import React, { useRef } from 'react';
import Button from '@material-ui/core/Button';
import TextField from '@material-ui/core/TextField';
import Dialog from '@material-ui/core/Dialog';
import DialogActions from '@material-ui/core/DialogActions';
import DialogContent from '@material-ui/core/DialogContent';
import DialogContentText from '@material-ui/core/DialogContentText';
import DialogTitle from '@material-ui/core/DialogTitle';

import PropTypes from 'prop-types';
import { toast } from 'react-toastify';
//import throttle from 'lodash/throttle';

import IconButton from '@material-ui/core/IconButton';
import fakeResponse from './search_resp';

import useDebounce from '../../helpers/useDebounce';

import Typography from '@material-ui/core/Typography';
import LocationOnIcon from '@material-ui/icons/LocationOn';
import CloseIcon from '@material-ui/icons/Close';
import Grid from '@material-ui/core/Grid';

import api from '../../api/methods';

import Autocomplete from '@material-ui/lab/Autocomplete';
import CircularProgress from '@material-ui/core/CircularProgress';
import { StepLabel } from '@material-ui/core';

const parseSearchResults = apiResp => {
	let res = [];

	const [__, articleTitles, introText, _] = apiResp;

	for (let i = 0; i < articleTitles.length; i++) {
		if (articleTitles[i] === '') {
			continue; //no such article
		}
		res.push({ value: articleTitles[i], intro: introText[i] });
	}

	return res;
};

export default function FormDialog({ isOpen, title, modalOpenHandle }) {
	//const classes = useStyles();
	const [open, setOpen] = React.useState(false);
	const [loading, setLoading] = React.useState(false);
	const [saving, setSaving] = React.useState(false);
	const [choosedArticle, setChoosedArticle] = React.useState('');
	const [articleIntro, setArticleIntro] = React.useState('');
	const [wbItem, setWBItem] = React.useState('');
	//const [openSearch, setOpenSearch] = React.useState(false);

	const [inputValue, setInputValue] = React.useState('');
	const [wiki, setWiki] = React.useState('en');

	const [options, setOptions] = React.useState([]);
	//const loading = open && options.length === 0;

	const debouncedSearchTerm = useDebounce(inputValue, 400);

	React.useEffect(() => {
		if (choosedArticle === '' || wiki === '') {
			return;
		}
		api.mediawiki.summary(wiki, choosedArticle).then(res => {
			if ('extract' in res) {
				setArticleIntro(res.extract);
			}
			if ('wikibase_item' in res) {
				setWBItem(res.wikibase_item);
			} else {
				setWBItem(null);
			}
		});
	}, [choosedArticle]);

	React.useEffect(() => {
		setChoosedArticle('');
		setArticleIntro('');
		setInputValue('');
		setWiki('en');
		setWBItem('');
		setOptions([]);
	}, [title]);

	/* React.useEffect(() => {
    let active = true;

    if (!loading) {
      return undefined;
	}


    return () => {
      active = false;
    };
  }, [loading]); */

	React.useEffect(() => {
		if (!open) {
			setOptions([]);
		}
	}, [open]);

	React.useEffect(() => {
		let active = true;

		if (debouncedSearchTerm === '') {
			setOptions([]);
			return undefined;
		}

		setLoading(true);
		api.mediawiki.openSearch(wiki, debouncedSearchTerm).then(res => {
			if (active) {
				setLoading(false);
				setOptions(parseSearchResults(res));
			}
		});

		return () => {
			active = false;
		};
	}, [debouncedSearchTerm]);

	React.useEffect(() => {
		setOpen(isOpen);
	}, [isOpen]);

	const handleClose = () => {
		setOpen(false);
	};

	const handleChange = event => {
		setInputValue(event.target.value);
	};

	const handleWikiChange = ev => {
		setWiki(event.target.value);
	};

	const setLabel = option => {
		if (choosedArticle === '') {
			return '';
		}
		const val = typeof option === 'string' ? option : option.value;
		//setChoosedArticle(val);
		return val;
	};

	const handleSave = () => {
		setSaving(true);

		api.tool.setIWwb(wbItem, title).then(res => {
			if (res.status === 'error') {
				toast.warn(`Neveiksmīga saglabāšana`, { autoClose: 7500 });
			} else {
				toast.success(`Rakstam pievienota starpviki saite`, { autoClose: 3000 });
				modalOpenHandle('');
			}
		})
		.finally(() => {
			setSaving(false);
		});
	};

	const removeOption = () => {
		setChoosedArticle('');
		setInputValue('');
	};

	const handleInputChange = (ev, val) => {
		setChoosedArticle(val.value);
	};

	const isButtonDisabled = choosedArticle.length < 1 || saving || wbItem === null;
	return (
		<div>
			<Dialog
				fullWidth={false}
				maxWidth='md'
				disableEnforceFocus={false}
				open={open}
				aria-labelledby='form-dialog-title'>
				<DialogTitle id='form-dialog-title'>Pievienot starpviki saites rakstam</DialogTitle>
				<DialogContent>
					<div style={{ display: 'flex', flexDirection: 'row' }}>
						<TextField
							value={wiki}
							label='Vikipēdija, kurā meklēt'
							variant='outlined'
							id='wiki-search'
							//fullWidth
							onChange={handleWikiChange}
						/>
						<Autocomplete
							id='iw-search'
							disableClearable={true}
							//fullWidth
							style={{ minWidth: 500 }}
							onChange={handleInputChange}
							getOptionLabel={option => setLabel(option)}
							//getOptionSelected={(option, value) => setLabel(option)}
							filterOptions={x => x}
							options={options}
							autoComplete
							//includeInputInList
							//freeSolo
							disableOpenOnFocus
							renderInput={params => (
								<>
									<TextField
										{...params}
										fullWidth
										id='article-search'
										label='Nosaukums'
										variant='outlined'
										onChange={handleChange}
										InputProps={{
											...params.InputProps,
											endAdornment: (
												<React.Fragment>
													{choosedArticle.length > 0 ? (
														<IconButton
															title='Clear'
															onClick={removeOption}
															style={{
																marginRight: -2,
																padding: 4
															}}>
															<CloseIcon fontSize='small' />
														</IconButton>
													) : null}
													{loading ? (
														<CircularProgress
															color='inherit'
															size={20}
														/>
													) : null}
													{params.InputProps.endAdornment}
												</React.Fragment>
											)
										}}
									/>
								</>
							)}
							renderOption={option => {
								return (
									<Grid container alignItems='center'>
										<Grid item xs>
											<Typography variant='body1' color='textSecondary'>
												{option.value}
											</Typography>

											<Typography variant='caption' color='textSecondary'>
												{option.intro}
											</Typography>
										</Grid>
									</Grid>
								);
							}}
						/>
				</div>
				{articleIntro.length > 0 && <Typography>
					{choosedArticle}
					<br />
					{articleIntro}
					<br />
					{wbItem === null && 'Ar rīku nevar pievienot starpviki saiti šim rakstam!'}
				</Typography>}
				</DialogContent>
				<DialogActions>
					<Button disabled={isButtonDisabled} onClick={handleSave} color='secondary'>
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

FormDialog.propTypes = {
	isOpen: PropTypes.bool,
	title: PropTypes.string,
	modalOpenHandle: PropTypes.string
};
