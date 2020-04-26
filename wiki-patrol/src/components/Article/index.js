import React from 'react';
import ArticleText from '../ArticleText';
import ArticleInfo from '../ArticleInfo';
import styles from './styles.module.scss';
import ArticleNavigationActions from '../ArticleActions/navigation';
import ArticleActions from '../ArticleActions';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import { fetchNextArticle, resetID, settingFromArticleList } from './articleSlice';
import title from '../../helpers/articleTitle';

class Article extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			title: '',
			id: null
		};
	}

	componentDidMount() {
		if (this.props.fromList === false) {
			this.props.resetID();
			this.props.fetchNextArticle('next');
		} else {
			this.props.settingFromArticleList(false);
		}
		//this.props.fetchNextArticle('this',2347);
	}

	render() {
		const { isFetching, title: currTitle } = this.props;

		return (
			<>
				{currTitle && (
					<div className={styles.mainFlexContainer}>
						<div className={styles.infoFlexContainer}>
							<div className={styles.navigActions}>
								<ArticleNavigationActions />
								<div className={styles.articleActions}><ArticleActions /></div>
							</div>
							<div className={styles.info}>
								<ArticleInfo />
							</div>
						</div>
						<div className={styles.articleTextContainer}>
							<div className={styles.title}>{title(currTitle, 'all', styles)}</div>
							<div className={styles.text}>
								<ArticleText />
							</div>
						</div>
					</div>
				)}
			</>
		);
	}
}

Article.propTypes = {
	isFetching: PropTypes.bool,
	title: PropTypes.string,
	fetchNextArticle: PropTypes.func,
	resetID: PropTypes.func,
	fromList: PropTypes.bool,
	settingFromArticleList: PropTypes.func
};

const mapStateToProps = state => ({
	isFetching: state.article.fetching,
	title: state.article.title,
	fromList: state.article.fromList
});

const mapDispatchToProps = { fetchNextArticle, resetID, settingFromArticleList };

export default connect(mapStateToProps, mapDispatchToProps)(Article);
