import React, {useState, useEffect} from 'react';
import PropTypes from 'prop-types';
import { makeStyles, useTheme } from '@material-ui/core/styles';
import Table from '@material-ui/core/Table';
import TableBody from '@material-ui/core/TableBody';
import TableCell from '@material-ui/core/TableCell';
import TableFooter from '@material-ui/core/TableFooter';
import TablePagination from '@material-ui/core/TablePagination';
import TableRow from '@material-ui/core/TableRow';
import Paper from '@material-ui/core/Paper';
import IconButton from '@material-ui/core/IconButton';
import FirstPageIcon from '@material-ui/icons/FirstPage';
import KeyboardArrowLeft from '@material-ui/icons/KeyboardArrowLeft';
import KeyboardArrowRight from '@material-ui/icons/KeyboardArrowRight';
import LastPageIcon from '@material-ui/icons/LastPage';
import api from '../../api/methods';
import NavigateNextIcon from '@material-ui/icons/NavigateNext';

import { connect } from 'react-redux';
import { fetchNextArticle, saveArticle, settingFromArticleList, resetID } from '../Article/articleSlice';

import { Redirect, withRouter } from 'react-router-dom';

import TableHead from '@material-ui/core/TableHead';
import TableSortLabel from '@material-ui/core/TableSortLabel';
import Tooltip from '@material-ui/core/Tooltip';
import CheckCircleOutlineIcon from '@material-ui/icons/CheckCircleOutline';

import articleTitle from '../../helpers/articleTitle';
import userLink from '../../helpers/userLink';
import Spinner from '../../helpers/spinner';

const useStyles1 = makeStyles(theme => ({
	root: {
		flexShrink: 0,
		marginLeft: theme.spacing(2.5)
	}
}));

function TablePaginationActions(props) {
	const classes = useStyles1();
	const theme = useTheme();
	const { count, page, rowsPerPage, onChangePage } = props;

	const handleFirstPageButtonClick = event => {
		onChangePage(event, 0);
	};

	const handleBackButtonClick = event => {
		onChangePage(event, page - 1);
	};

	const handleNextButtonClick = event => {
		onChangePage(event, page + 1);
	};

	const handleLastPageButtonClick = event => {
		onChangePage(event, Math.max(0, Math.ceil(count / rowsPerPage) - 1));
	};

	return (
		<div className={classes.root}>
			<IconButton
				onClick={handleFirstPageButtonClick}
				disabled={page === 0}
				aria-label='first page'>
				{theme.direction === 'rtl' ? <LastPageIcon /> : <FirstPageIcon />}
			</IconButton>
			<IconButton
				onClick={handleBackButtonClick}
				disabled={page === 0}
				aria-label='previous page'>
				{theme.direction === 'rtl' ? <KeyboardArrowRight /> : <KeyboardArrowLeft />}
			</IconButton>
			<IconButton
				onClick={handleNextButtonClick}
				disabled={page >= Math.ceil(count / rowsPerPage) - 1}
				aria-label='next page'>
				{theme.direction === 'rtl' ? <KeyboardArrowLeft /> : <KeyboardArrowRight />}
			</IconButton>
			<IconButton
				onClick={handleLastPageButtonClick}
				disabled={page >= Math.ceil(count / rowsPerPage) - 1}
				aria-label='last page'>
				{theme.direction === 'rtl' ? <FirstPageIcon /> : <LastPageIcon />}
			</IconButton>
		</div>
	);
}

TablePaginationActions.propTypes = {
	count: PropTypes.number.isRequired,
	onChangePage: PropTypes.func.isRequired,
	page: PropTypes.number.isRequired,
	rowsPerPage: PropTypes.number.isRequired
};

function createData(name, calories, fat) {
	return { name, calories, fat };
}

function desc(a, b, orderBy) {
	if (b[orderBy] < a[orderBy]) {
		return -1;
	}
	if (b[orderBy] > a[orderBy]) {
		return 1;
	}
	return 0;
}

//const rows = fakeData.sort((a, b) => (a.id < b.id ? -1 : 1));

function EnhancedTableHead(props) {
	const {
		classes,
		hasComments,
		onSelectAllClick,
		order,
		orderBy,
		numSelected,
		rowCount,
		onRequestSort
	} = props;
	const createSortHandler = property => event => {
		onRequestSort(event, property);
	};

	const headCells = [
		{ id: 'title', numeric: false, disablePadding: false, label: 'Raksta nosaukums', classes: 'articleName', sorting: true },
		{ id: 'date', numeric: false, disablePadding: true, label: 'Raksts izveidots', classes: 'articleDate', sorting: true },
		{ id: 'user', numeric: false, disablePadding: true, label: 'Raksta izveidotājs', classes: 'articleAuthor', sorting: true },
		hasComments ? { id: 'comment', numeric: false, disablePadding: true, label: 'Komentārs', classes: 'articleActions', sorting: true } : null,
		{ id: 'act', numeric: false, disablePadding: true, label: 'Darbības', classes: 'articleActions', sorting: false }
	]
	.filter(row => row !== null);

	return (
		<TableHead>
			<TableRow>
				{/* <TableCell padding="checkbox">
			<Checkbox
			  indeterminate={numSelected > 0 && numSelected < rowCount}
			  checked={numSelected === rowCount}
			  onChange={onSelectAllClick}
			  inputProps={{ 'aria-label': 'select all desserts' }}
			/>
		  </TableCell> */}
				{headCells.map(headCell => (
					<TableCell
						className={classes[headCell.classes]}
						key={headCell.id}
						align={headCell.numeric ? 'right' : 'left'}
						padding={headCell.disablePadding ? 'none' : 'default'}
						sortDirection={orderBy === headCell.id ? order : false}>
							{headCell.sorting ?
						<TableSortLabel
							active={orderBy === headCell.id}
							direction={order}
							onClick={createSortHandler(headCell.id)}>
							{headCell.label}
						</TableSortLabel> : headCell.label}
					</TableCell>
				))}
			</TableRow>
		</TableHead>
	);
}

EnhancedTableHead.propTypes = {
  classes: PropTypes.object,
  numSelected: PropTypes.number,
  onRequestSort: PropTypes.func,
  onSelectAllClick: PropTypes.func,
  order: PropTypes.string,
  orderBy: PropTypes.string,
  rowCount: PropTypes.number,
  hasComments: PropTypes.bool.isRequired
}

function stableSort(array, cmp) {
	const stabilizedThis = array.map((el, index) => [el, index]);
	stabilizedThis.sort((a, b) => {
		const order = cmp(a[0], b[0]);
		if (order !== 0) return order;
		return a[1] - b[1];
	});
	return stabilizedThis.map(el => el[0]);
}

function getSorting(order, orderBy) {
	//console.log(order, orderBy)
	return order === 'desc' ? (a, b) => desc(a, b, orderBy) : (a, b) => -desc(a, b, orderBy);
}

const useStyles2 = makeStyles(theme => ({
	root: {
		margin: '1vw'
	},
	buttons: {
		margin: theme.spacing(1)
	},
	articleName: {
		width: 300
	},
	articleDate: {
		width: 150
	},
	articleAuthor: {
		width: 150
	},
	articleActions: {
		width: 150
	}
}));

function CustomPaginationActionsTable({ fetchNextArticle, saveArticle, settingFromArticleList, resetID, location }) {
	const classes = useStyles2();
	const [rows, setTableRows] = useState([]);
	const [order, setOrder] = useState('asc');
	const [orderBy, setOrderBy] = useState('id');
	const [selected, setSelected] = useState([]);
	const [page, setPage] = useState(0);
	const [dense, setDense] = useState(false);
	const [rowsPerPage, setRowsPerPage] = useState(50);
	const [goToArticle, setGoToArticle] = useState(null);
	const [hasComments, setComments] = useState(false);
	const [loading, setLoading] = useState(false);
	const [hasBeenLoaded, setHasBeenLoaded] = useState(false);

	useEffect(() => {
		setHasBeenLoaded(false);
		let localHasComments = false;
		if (location.pathname === '/comments') {
			setComments(true);
			localHasComments = true;
		} else {
			setComments(false);
		}
		setTableRows([]);
		resetID();

		const apiEndp = localHasComments === true ? api.tool.articlesWithComments : api.tool.articleList;

		setLoading(true);
		apiEndp().then(res=> {
			setHasBeenLoaded(true);
			setTableRows(res.sort((a, b) => (a.id < b.id ? -1 : 1)));
		}).finally(() => setLoading(false))
	}, [location.pathname]);

	const emptyRows = rowsPerPage - Math.min(rowsPerPage, rows.length - page * rowsPerPage);

	const handleChangePage = (event, newPage) => {
		setPage(newPage);
	};


	const handleChangeRowsPerPage = event => {
		setRowsPerPage(parseInt(event.target.value, 10));
		setPage(0);
	};

	const handleRequestSort = (event, property) => {
		const isDesc = orderBy === property && order === 'desc';
		setOrder(isDesc ? 'asc' : 'desc');
		setOrderBy(property);
	};

	const handleSelectAllClick = event => {
		if (event.target.checked) {
			const newSelecteds = rows.map(n => n.name);
			setSelected(newSelecteds);
			return;
		}
		setSelected([]);
	};

	const handleArticleSaving = (articleID, articleTitle) => {
		saveArticle(articleID, articleTitle, false);

		const newRows = rows.filter(n => n.id !== articleID);
		setTableRows(newRows);
	};

	const goingToArticle = articleID => {
		settingFromArticleList(true);
		fetchNextArticle('this', articleID);
		setGoToArticle(true);
	};

	if (goToArticle) {
		return <Redirect push to='/' />;
	}

	if (!hasBeenLoaded && loading) {
		return <Spinner />;
	}

	return (
		<>
			{rows.length> 0 ?
				<Paper className={classes.root}><div className={classes.tableWrapper}>
				<Table className={classes.table} size="small" aria-label='custom pagination table'>
					<EnhancedTableHead
						hasComments={hasComments}
						classes={classes}
						numSelected={0}
						order={order}
						orderBy={orderBy}
						onSelectAllClick={handleSelectAllClick}
						onRequestSort={handleRequestSort}
						rowCount={rows.length}
					/>
					<TableBody>
						{stableSort(rows, getSorting(order, orderBy))
							.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
							.map(row => (
								<TableRow key={row.id}>
									<TableCell component='th' scope='row'>
										{articleTitle(row.title,'title')}
									</TableCell>
									<TableCell>{row.date}</TableCell>
									<TableCell>
										{userLink(row.user)}</TableCell>
									{hasComments && <TableCell>
										{row.comment}</TableCell>}
									<TableCell className={classes.buttons}>
										<Tooltip title='Pārbaudīt šo rakstu'>
											<IconButton
												aria-label='go-to-article'
												onClick={() => goingToArticle(row.id)}>
												<NavigateNextIcon />
											</IconButton>
										</Tooltip>
										<Tooltip title='Atzīmēt kā pārbaudītu'>
											<IconButton
												aria-label='mark-as-resolved'
												onClick={() =>
													handleArticleSaving(row.id, row.title)
												}>
												<CheckCircleOutlineIcon />
											</IconButton>
										</Tooltip>
									</TableCell>
								</TableRow>
							))}
					</TableBody>
					<TableFooter>
						<TableRow>
							<TablePagination
								rowsPerPageOptions={[5, 10, 25, 50, { label: 'All', value: -1 }]}
								colSpan={3}
								count={rows.length}
								rowsPerPage={rowsPerPage}
								page={page}
								SelectProps={{
									inputProps: { 'aria-label': 'rows per page' },
									native: true
								}}
								onChangePage={handleChangePage}
								onChangeRowsPerPage={handleChangeRowsPerPage}
								ActionsComponent={TablePaginationActions}
							/>
						</TableRow>
					</TableFooter>
				</Table>
			</div></Paper> : "Nav datu"}</>
	);
}

CustomPaginationActionsTable.propTypes = {
  fetchNextArticle: PropTypes.func,
  saveArticle: PropTypes.func,
  settingFromArticleList: PropTypes.func,
  resetID: PropTypes.func,
  location: PropTypes.object
}

const mapDispatchToProps = { fetchNextArticle, saveArticle, settingFromArticleList, resetID };

export default withRouter(connect(null, mapDispatchToProps)(CustomPaginationActionsTable));
