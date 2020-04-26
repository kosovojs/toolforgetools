import PropTypes from 'prop-types';
import React, { Component, Fragment } from 'react';
import api from '../../api/methods';
import { toast } from 'react-toastify';

import { setRCFromAPI, recentChangesArticlesSelector } from './slice';

import { connect } from 'react-redux';
import RC_entry from '../RC_entry';

const titleForURL = (title) => title.replace(/ /g, '_');
const formattedTitle = (title) => title.replace(/_/g, ' ');

const articleLink = (title) => (
	<a
		href={`https://lv.wikipedia.org/wiki/${titleForURL(title)}`}
		target='_blank'
		rel='noopener noreferrer'>
		{title}
	</a>
);

class RecentChanges extends Component {
	/* constructor(props) {
		super(props);

		this.state = {
			recent_changes: [],
			loading: false,
			error: false,
			errorMsg: null,
		};
	} */

	componentDidMount() {
		this.props.setRCFromAPI();
	}

	render() {
		const { recent_changes, loading, error } = this.props;

		console.log(recent_changes)

		if (recent_changes.length === 0 || loading) {
			return '';
		}

		return (
			<div style={{ display: 'flex', flexDirection: 'column', margin: '1vw' }}>
				{recent_changes.map((entry) => <RC_entry key={entry} article={entry}/>)}
			</div>
		);
	}
}

RecentChanges.propTypes = {
	error: PropTypes.bool,
	loading: PropTypes.bool,
	recent_changes: PropTypes.array,
	setRCFromAPI: PropTypes.func,
};

const mapDispatchToProps = { setRCFromAPI };

const mapStateToProps = ({ rc }) => ({
	recent_changes: recentChangesArticlesSelector(rc),
	loading: rc.loading,
	error: rc.error,
});

export default connect(mapStateToProps, mapDispatchToProps)(RecentChanges);
