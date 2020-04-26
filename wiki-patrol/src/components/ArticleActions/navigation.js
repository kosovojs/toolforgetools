import React from 'react';
import Button from '@material-ui/core/Button';
import ButtonGroup from '@material-ui/core/ButtonGroup';
import Tooltip from '@material-ui/core/Tooltip';
import NavigateNextIcon from '@material-ui/icons/NavigateNext';
import ShuffleIcon from '@material-ui/icons/Shuffle';
import CheckCircleOutlineIcon from '@material-ui/icons/CheckCircleOutline';
import TimerIcon from '@material-ui/icons/Timer';
import ChatBubbleOutlineIcon from '@material-ui/icons/ChatBubbleOutline';
import PropTypes from 'prop-types'

import SetForLaterWithComment from '../Dialogs/SetForLaterWithComment';


import { makeStyles } from '@material-ui/core/styles';

import styles from './styles.module.scss'

import CircularProgress from '@material-ui/core/CircularProgress';
import { green } from '@material-ui/core/colors';

import { connect } from 'react-redux'
import { fetchNextArticle, saveArticle, putArticleInQueqe } from '../Article/articleSlice';

const mapDispatch = { fetchNextArticle, saveArticle, putArticleInQueqe };




//ja 'Atzīmēt kā pārbaudītu', tad arī atzīmēt kā patrolētu @wiki
const ArticleActions = ({fetchNextArticle, saveArticle, putArticleInQueqe}) => {
	const [open, setOpen] = React.useState(false);

	const handleOpening = val => {
		setOpen(val);
	};

	return <>
		<ButtonGroup fullWidth variant="contained">
			<Tooltip title="Pārbaudīt nākamo rakstu"><Button className={styles.toNext} onClick={() => fetchNextArticle('next')}><NavigateNextIcon /></Button></Tooltip>
			<Tooltip title="Pārbaudīt nejauši izvēlētu rakstu"><Button className={styles.toNext} onClick={() => fetchNextArticle('rnd')}><ShuffleIcon /></Button></Tooltip>
			<Tooltip title="Atzīmēt kā pārbaudītu"><Button className={styles.markAsChecked} onClick={() => saveArticle()}><CheckCircleOutlineIcon /></Button></Tooltip>
			<Tooltip title="Atstāt"><Button  className={styles.forLater} onClick={() => putArticleInQueqe()}><TimerIcon /></Button></Tooltip>
			<Tooltip title="Atstāt ar komentāru"><Button  className={styles.forLater} onClick={() => handleOpening(true)}><ChatBubbleOutlineIcon /></Button></Tooltip>
		</ButtonGroup>
		<SetForLaterWithComment isOpen={open} modalOpenHandle={handleOpening} />
	</>
}

ArticleActions.propTypes = {
	fetchNextArticle: PropTypes.func.isRequired,
	saveArticle: PropTypes.func.isRequired,
	putArticleInQueqe: PropTypes.func.isRequired,
}

export default connect(
	null,
	mapDispatch
)(ArticleActions)
