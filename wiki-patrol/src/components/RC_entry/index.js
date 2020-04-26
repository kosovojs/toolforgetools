import PropTypes from 'prop-types';
import React, { Component, Fragment } from 'react';
import api from '../../api/methods';
import { toast } from 'react-toastify';

import clsx from 'clsx';

import { setRCFromAPI, rcEntrySelector } from '../RecentChanges/slice';

import styles from './style.module.scss';

import { connect } from 'react-redux';

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

class RC_entry extends Component {
	constructor(props) {
		super(props);

		this.state = {
			visible: true,//move logic to redux part
			editdiff: null,
			diffLoading: false,
			diffError: false,
			diffErrorMsg: null,
			lastGoodEdit: {},
		};
	}

	showEdit = (revID) => (e) => {
		console.log(revID);

		this.setState({ diffLoading: true, editdiff: null });
		api.mediawiki
			.diffRelative(revID, 'prev')
			.then((res) => {
				if (res.compare && res.compare.body) {
					this.setState({ editdiff: res.compare.body });
				}
			})
			.finally(() => {
				this.setState({ diffLoading: false });
			});
	};

	saveLastGoodEdit =async () => {
		if (Object.keys(this.state.lastGoodEdit).length === 0) {
			const lastTrustededit = await this.getLastGoodEdit();

			this.setState({lastGoodEdit: lastTrustededit})
		}
		api.tool
			.saveLastGoodVersion(this.props.article, this.state.lastGoodEdit, this.props.entryData)
			.then((res) => {
				if (res.status === 'ok') {
					toast.success('darbība saglabāta');
					this.setState({ editdiff: null, visible:false });
				}
			});
	};

	handlePatrolEdit = (revID) => (e) => {
		api.tool.patrolEdits([revID]).then((res) => {
			if (res.status === 'ok') {
				toast.success('darbība saglabāta');
				this.setState({ editdiff: null });
			}
		});
	};

	getLastGoodEdit  = async () => {
		const trusted = this.props.trusted_users;
		const resp = await api.mediawiki.pageRevisions(this.props.article);

		const lastEntry = [...this.props.entryData].pop();
		const lastRevid = lastEntry.revid;

		const pageRes = resp.query.pages[0].revisions;
		//console.log(pageRes)

		let lastTrustededit = {};

		for (let i = 0; i < pageRes.length; i++) {
			const revision = pageRes[i];

			const { user, revid, parentid } = revision;

			//console.log(user, revid, parentid, parentid === 0, trusted.includes(user))

			if (parentid === 0) {
				//this is page creation edit
				lastTrustededit = revision;
				break;
			}

			if (lastRevid < revid) {//edit after span of edits by untrusted users
				continue;
			}

			if (trusted.includes(user)) {
				lastTrustededit = revision;
				break;
			}
		}

		return lastTrustededit;
	}

	showFullEdits = async () => {
		const lastTrustededit = await this.getLastGoodEdit();

		this.setState({ diffLoading: true, editdiff: null, lastGoodEdit: lastTrustededit });
		api.mediawiki
			.diffRelative(lastTrustededit.revid, 'cur')
			.then((res) => {
				if (res.compare && res.compare.body) {
					this.setState({
						editdiff: res.compare.body === '' ? 'nav izmaiņu' : res.compare.body,
					});
				}
			})
			.finally(() => {
				this.setState({ diffLoading: false });
			});
	};

	patrolEdits = () => {
		const edits = this.props.entryData.map((entry) => entry.revid);
		api.tool.patrolEdits(edits).then((res) => {
			if (res.status === 'ok') {
				toast.success('darbība saglabāta');
				this.setState({ editdiff: null, visible:false });
			}
		});
	};

	render() {
		const { article, entryData } = this.props;
		const { editdiff, lastGoodEdit, visible } = this.state;

		if (!visible) {
			return '';
		}

		return (
			<div className={styles.container}>
				<div className={styles.header}>
					<div className={styles.articleTitle}>{articleLink(article)}</div>
					<div className='btn-group btn-group-sm' role='group' aria-label='Basic example'>
						<button
							type='button'
							className='btn btn-secondary'
							onClick={this.showFullEdits}>
							Kopējās izmaiņas rakstā
						</button>
						<button
							type='button'
							className='btn btn-success'
							onClick={this.patrolEdits}>
							Pārbaudīts!
						</button>
						<button
							type='button'
							className='btn btn-info'
							onClick={this.saveLastGoodEdit}>
							Saglabāt pēdējo uzticamo versiju
						</button>
					</div>
				</div>

				<br />
				<>
					{entryData.map((entry) => {
						//{"type":"edit","ns":0,"title":"Kalifornija","pageid":3340,"revid":3202752,"old_revid":3145480,"rcid":9235558,"user":"217.199.98.81","anon":true,"bot":false,"new":false,"minor":false,"oldlen":18695,"newlen":18702,"timestamp":"2020-04-12T14:01:51Z","comment":"/* ievads */","redirect":false,"patrolled":false,"unpatrolled":true,"autopatrolled":false,"tags":["visualeditor"],"oresscores":{"damaging":{"true":0.912,"false":0.08799999999999997},"goodfaith":{"true":0.016,"false":0.984}}}
						const {
							revid,
							user,
							oldlen,
							newlen,
							timestamp,
							comment,
							unpatrolled,
							oresscores,
						} = entry;

						const ores = oresscores.damaging.true;

						return (
							<div
								key={revid}
								className={clsx(styles.oneEdit, ores > 0.5 && styles.badFaith)}>
								<span className={styles.revTitle}>
									<a
										href={`https://lv.wikipedia.org/wiki/Special:Diff/${revid}`}
										target='_blank'
										rel='noopener noreferrer'>
										{timestamp}
									</a>
									<span
										style={{ cursor: 'pointer' }}
										onClick={(e) => this.showEdit(revid)(e)}>
										{' '}
										(izmaiņas)
									</span>
									<span
										style={{ cursor: 'pointer' }}
										onClick={(e) => this.handlePatrolEdit(revid)(e)}>
										{' '}
										(pārbaudīts)
									</span>
								</span>
								<span className={styles.summary}>{comment}</span>
								<span className={styles.revUser}>
									labojuma veicējs:{' '}
									<a
										href={`https://lv.wikipedia.org/wiki/User:${user}`}
										target='_blank'
										rel='noopener noreferrer'>
										{user}
									</a>
								</span>
								<span className={styles.revOres}>
									ORES ļaunprātīgs labojums: {ores * 100}
								</span>
							</div>
						);
					})}
				</>
				{Object.keys(lastGoodEdit).length > 0 && (
					<>
						Pēdējo uzticamo labojumu {lastGoodEdit.timestamp} veica {lastGoodEdit.user}{' '}
						(
						<a
							href={`https://lv.wikipedia.org/wiki/Special:Diff/${lastGoodEdit.revid}/cur`}
							target='_blank'
							rel='noopener noreferrer'>
							izmaiņas
						</a>
						)
					</>
				)}
				{editdiff && (
					<div className={styles.editContainer}>
						<table className='diff diff-contentalign-left' data-mw='interface'>
							<tbody
								className='diff'
								dangerouslySetInnerHTML={{ __html: editdiff }}></tbody>
						</table>
					</div>
				)}
			</div>
		);
	}
}

RC_entry.propTypes = {
	trusted_users: PropTypes.array,
	entryData: PropTypes.array,
	article: PropTypes.string.isRequired,
	error: PropTypes.bool,
	loading: PropTypes.bool,
	recent_changes: PropTypes.array,
	setRCFromAPI: PropTypes.func,
};

const mapDispatchToProps = { setRCFromAPI };

const mapStateToProps = ({ rc, app }, ownProps) => {
	const { article } = ownProps;

	const entryData = rcEntrySelector(rc, article);

	return {
		entryData,
		trusted_users: app.trustedUsers,
	};
};

export default connect(mapStateToProps, mapDispatchToProps)(RC_entry);
