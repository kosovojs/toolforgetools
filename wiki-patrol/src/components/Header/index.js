import React from 'react';
import { fade, makeStyles } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';
import MenuItem from '@material-ui/core/MenuItem';
import { Link, NavLink, withRouter } from 'react-router-dom';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import GitHubIcon from '@material-ui/icons/GitHub';

import { API_URL } from '../../config';

import ArticleSearch from '../ArticleSearch';

const useStyles = makeStyles(theme => ({
	grow: {
		flexGrow: 1
	},
	removedShadow: {
		boxShadow: '0px 1px 1px 0px rgba(0,0,0,0.2),0px 1px 1px 0px rgba(0,0,0,0.14),0px 1px 1px 0px rgba(0,0,0,0.12)'
	},
	menuButton: {
		marginRight: theme.spacing(2)
	},
	title: {
		display: 'none',
		[theme.breakpoints.up('sm')]: {
			display: 'block'
		}
	},
	search: {
		position: 'relative',
		borderRadius: theme.shape.borderRadius,
		backgroundColor: fade(theme.palette.common.white, 0.15),
		'&:hover': {
			backgroundColor: fade(theme.palette.common.white, 0.25)
		},
		marginRight: theme.spacing(2),
		marginLeft: 0,
		width: '100%',
		[theme.breakpoints.up('sm')]: {
			marginLeft: theme.spacing(3),
			width: 'auto'
		}
	},
	searchIcon: {
		width: theme.spacing(7),
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
		padding: theme.spacing(1, 1, 1, 7),
		transition: theme.transitions.create('width'),
		width: '100%',
		[theme.breakpoints.up('md')]: {
			width: 200
		}
	},
	sectionDesktop: {
		display: 'none',
		[theme.breakpoints.up('md')]: {
			display: 'flex'
		}
	},
	sectionMobile: {
		display: 'flex',
		[theme.breakpoints.up('md')]: {
			display: 'none'
		}
	}
}));

function Header({ isAuth, user, articles, location }) {
	const classes = useStyles();

	return (
		<div className={classes.grow}>
			<AppBar position='sticky' className={classes.removedShadow}>
				<Toolbar variant="dense">
					<NavLink to='/' style={{ textDecoration: 'none', color: 'unset' }}>
						<Typography className={classes.title} variant='h6' noWrap>
							NPP
						</Typography>
					</NavLink>
					<MenuItem component={Link} to='/list'>
						Saraksts
					</MenuItem>
					<MenuItem component={Link} to='/comments'>
						Ar komentāriem
					</MenuItem>
					<MenuItem component={Link} to='/dashboard'>
						Dashboard
					</MenuItem>
					{location.pathname === '/' && articles && <MenuItem>Vēl {articles} raksti!</MenuItem>}
					<div className={classes.grow} />
					{location.pathname === '/' && <ArticleSearch />}
					<MenuItem onClick={() => window.open(`https://github.com/kosovojs/npp-lv`)}>
						<GitHubIcon />
					</MenuItem>
					{isAuth ? (
						<>
							<MenuItem>Sveiks, {user}!</MenuItem>
							<MenuItem onClick={() => window.open(`${API_URL}?action=logout`)}>
								Iziet
							</MenuItem>
						</>
					) : (
						<MenuItem onClick={() => window.open(`${API_URL}?action=authorize`)}>
							Ienākt
						</MenuItem>
					)}
				</Toolbar>
			</AppBar>
		</div>
	);
}

Header.propTypes = {
	isAuth: PropTypes.bool,
	location: PropTypes.object,
	user: PropTypes.string,
	articles: PropTypes.oneOfType([PropTypes.number, PropTypes.bool])
};

const mapStateToProps = ({ app }) => ({
	isAuth: app.isAuth,
	user: app.user,
	articles: app.articles
});

export default withRouter(connect(mapStateToProps)(Header));
