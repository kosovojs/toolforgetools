import React from 'react';
import useMediaQuery from '@material-ui/core/useMediaQuery';
import { fade, useTheme, makeStyles } from '@material-ui/core/styles';
import Input from '@material-ui/core/Input';
import SearchIcon from '@material-ui/icons/Search';
import useDebounce from '../../helpers/useDebounce';
import Menu from '@material-ui/core/Menu';
import MenuItem from '@material-ui/core/MenuItem';
import { connect } from 'react-redux';
import {fetchNextArticle} from '../Article/articleSlice';
import CircularProgress from '@material-ui/core/CircularProgress';
import PropTypes from 'prop-types'

import api from '../../api/methods';

const useStyles = makeStyles(
	theme => ({
		root: {
			fontFamily: theme.typography.fontFamily,
			position: 'relative',
			marginRight: theme.spacing(2),
			marginLeft: theme.spacing(1),
			borderRadius: theme.shape.borderRadius,
			backgroundColor: fade(theme.palette.common.white, 0.15),
			'&:hover': {
				backgroundColor: fade(theme.palette.common.white, 0.25)
			},
			'& $inputInput': {
				transition: theme.transitions.create('width'),
				width: 120,
				'&:focus': {
					width: 170
				}
			}
		},
		search: {
			width: theme.spacing(9),
			height: '100%',
			position: 'absolute',
			pointerEvents: 'none',
			display: 'flex',
			alignItems: 'center',
			justifyContent: 'center'
		},
		inputRoot: {
			color: 'inherit'
		},
		inputInput: {
			padding: theme.spacing(1, 1, 1, 9)
		}
	}),
	{ name: 'AppSearch' }
);

function AppSearch({fetchNextArticle}) {
	const classes = useStyles();
	const theme = useTheme();
	const desktop = useMediaQuery(theme.breakpoints.up('sm'));
	const [searchValue, setSearchValue] = React.useState('');
	const [options, setOptions] = React.useState([]);
	const [anchorEl, setAnchorEl] = React.useState(null);
	const [loading, setLoading] = React.useState(false);

	const divRef = React.useRef(null);

	const closeMenu = (options) => {
		setAnchorEl(null);
		setOptions([]);
		setSearchValue('');
		fetchNextArticle('this',options.id);
	};

	const handleClose = () => {
		setAnchorEl(null);
	};

	const debouncedSearchTerm = useDebounce(searchValue, 600);

	React.useEffect(() => {
		let active = true;

		if (debouncedSearchTerm === '') {
			setOptions([]);
			return undefined;
		}

		setAnchorEl(divRef.current);

		setLoading(true);
		api.tool.search(debouncedSearchTerm).then(res => {
			if (active) {
				//console.log(res)
				setLoading(false);
				setOptions(res);
			}
		});

		return () => {
			active = false;
		};
	}, [debouncedSearchTerm]);

	const handleSearch = event => {
		setSearchValue(event.target.value);
	};

	return (
		<div
			style={{
				display: desktop ? 'flex' : 'none',
				flexDirection: 'column',
				flexWrap: 'wrap'
			}}>
			<div className={classes.root} style={{ display: 'flex' }}>
				<div className={classes.search}>
					<SearchIcon />
				</div>
				<Input
				 ref={divRef}
					disableUnderline
					placeholder='Meklēt…'
					value={searchValue}
					type='search'
					onChange={handleSearch}
					id='docsearch-input'
					classes={{
						root: classes.inputRoot,
						input: classes.inputInput
					}}

					endAdornment={<React.Fragment>
						{loading ? (
							<CircularProgress color='inherit' size={20} />
						) : null}
					</React.Fragment>}
				/>
			</div>
			<Menu
				elevation={0}
				getContentAnchorEl={null}
				anchorOrigin={{
					vertical: 'bottom',
					horizontal: 'center'
				}}
				transformOrigin={{
					vertical: 'top',
					horizontal: 'center'
				}}
				id='customized-menu'
				anchorEl={anchorEl}
				keepMounted
				open={Boolean(anchorEl) && options.length > 0}
				//open={options.length > 0}
				onClose={handleClose}>
				{options.length > 0 &&
					options.map(opt => (
						<MenuItem key={opt.id} onClick={() => closeMenu(opt)}>
							{opt.title}
						</MenuItem>
					))}
			</Menu>
		</div>
	);
}

AppSearch.propTypes = {
  fetchNextArticle: PropTypes.func
}

const mapDispatchToProps = { fetchNextArticle }

export default connect(
	null,
	mapDispatchToProps
)(AppSearch)
