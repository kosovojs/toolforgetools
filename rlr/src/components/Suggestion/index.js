import React, { Component, Fragment } from 'react';
import api from '../../api/methods';
import { toast } from 'react-toastify';
var Diff = require('text-diff');

require('./style.css');

const titleForURL = (title) => title.replace(/ /g, '_');
const formattedTitle = (title) => title.replace(/_/g, ' ');

const maxSaves = 5;

const getTextDiff = (first, second) => {
	var diff = new Diff({ timeout: 1 }); // options may be passed to constructor; see below
	var textDiff = diff.main(first, second); // produces diff array
	return diff.prettyHtml(textDiff); // produces a formatted HTML string
};

const articleLink = (title) => (
	<a
		href={`https://lv.wikipedia.org/wiki/${titleForURL(title)}`}
		target='_blank'
		rel='noopener noreferrer'>
		{title}
	</a>
);

class Suggestion extends Component {
	constructor(props) {
		super(props);

		this.state = {
			suggestion: null,
			targets: [],
			search: [],
			loading: false,
			error: false,
			saving: false,
			previews: {},
			whatLinksHere: [],
			saves: 0,
		};
	}

	init = () => {
		this.setState({ loading: true });

		api.tool
			.nextArticle()
			.then((res) => {
				let { suggestion, targets } = res;

				suggestion = formattedTitle(suggestion);
				targets = targets.map((entry) => formattedTitle(entry));

				this.setState({ suggestion, targets });

				this.setLinks(suggestion);
				this.setSearch(suggestion);

			})
			.catch((err) => {
				this.setState({ error: true });
			})
			.finally(() => {
				this.setState({ loading: false });
			});
	};

	setLinks = (title) => {
		api.mediawiki.linksHere(title).then((res) => {
			const { query } = res;

			const page = query.pages[0];

			if (!('missing' in page)) {
				this.saveData('not redlink', false);
				return;
			}

			if (!('linkshere' in page)) {
				this.saveData('no links', false);
				return;
			}

			const links = page['linkshere'].map((article) => article.title);

			this.setState({ whatLinksHere: links });
		});
	};

	setSearch = (title) => {
		api.mediawiki.openSearch('lv', title).then((res) => {
			const [___, results, _, __] = res;

			this.setState({ search: results });
		});
	};

	saveData = (message = '', toast = true) => {
		this.setState({ saving: true });

		api.tool
			.saveArticle(this.state.suggestion, message)
			.then((res) => {
				if (res.status === 'ok' && toast) {
					toast.success(`Darbība rakstam "${this.state.suggestion}" saglabāta`, {
						autoClose: 3000,
					});
				}

				if (res.status !== 'ok') {
					console.log(res);
					toast.warn(`Neveiksmīga saglabāšana: ${res.message}`, { autoClose: 7500 });
				}
			})
			.catch((err) => {
				//toast.warn(`Neveiksmīga saglabāšana: ${err}`, { autoClose: 7500 });
			})
			.finally(() => {
				this.setState({ saving: false }, () => {
					/* if (this.state.saves < maxSaves) {
					this.setState(prevState => ({
						saves: prevState.saves+1
					})) */
					this.init();
					//}
					//
				});
			});
	};

	componentDidMount() {
		this.init();
	}

	createRedirect = (title) => {
		console.log(title);

		this.setState({ saving: true });

		api.tool
			.redirect(this.state.suggestion, title)
			.then((res) => {
				if (res.status === 'ok' && toast) {
					toast.success(`Darbība rakstam "${this.state.suggestion}" saglabāta`, {
						autoClose: 3000,
					});
				}

				if (res.status !== 'ok') {
					toast.warn(`Neveiksmīga saglabāšana: ${res.message}`, { autoClose: 7500 });
				}
			})
			.catch((err) => {
				//toast.warn(`Neveiksmīga saglabāšana: ${err}`, { autoClose: 7500 });
			})
			.finally(() => {
				this.setState({ saving: false }, () => {
					/* if (this.state.saves < maxSaves) {
					this.setState(prevState => ({
						saves: prevState.saves+1
					})) */
					this.init();
					//}
					//
				});
			});
	};

	render() {
		const { suggestion, targets, loading, error, saving, whatLinksHere, search } = this.state;

		if (suggestion === null || loading) {
			return '';
		}

		return (
			<div style={{ display: 'flex', flexDirection: 'row' }}>
				<div style={{ display: 'flex', flexDirection: 'column', minWidth: '250px' }}>
					<button type='button' className='btn btn-outline-primary' onClick={this.init}>
						Cits kandidāts
					</button>
					<button
						type='button'
						className='btn btn-outline-success'
						onClick={() => this.saveData('done')}>
						Salabots
					</button>
					<button
						type='button'
						className='btn btn-outline-danger'
						onClick={() => this.saveData('delete')}>
						Nav jālabo
					</button>
				</div>
				<div style={{ marginLeft: '3vw' }}>
					<h3>
						&quot;
						<a
							href={`https://lv.wikipedia.org/wiki/Special:WhatLinksHere/${titleForURL(
								suggestion
							)}`}
							target='_blank'
							rel='noopener noreferrer'>
							{formattedTitle(suggestion)}
						</a>
						&quot;{' '}
						<small>
							(
							<a
								href={`https://lv.wikipedia.org/w/index.php?title=${titleForURL(
									suggestion
								)}&action=edit`}
								target='_blank'
								rel='noopener noreferrer'>
								labot
							</a>
							)
						</small>
					</h3>
					<h4>Kandidātlapas</h4>
					<div style={{ display: 'flex', flexDirection: 'row', flexWrap: 'wrap' }}>
						{targets.map((target) => {
							return (
								<div className='card' key={target}>
									<div className='card-body'>
										<h5 className='card-title'>{articleLink(target)}</h5>
										<div dangerouslySetInnerHTML={{__html: getTextDiff(target, suggestion)}} />
										<a
											href='#'
											className='card-link'
											onClick={() => this.createRedirect(target)}>
											Izveidot pāradresāciju
										</a>
									</div>
								</div>
							);
						})}
					</div>
					{search.length > 0 && (<>
					<h4>Meklēšanas rezultāti</h4>
					<div style={{ display: 'flex', flexDirection: 'row', flexWrap: 'wrap' }}>
						{search.map((target) => {
							return (
								<div className='card' key={target}>
									<div className='card-body'>
										<h5 className='card-title'>{articleLink(target)}</h5>
										<div dangerouslySetInnerHTML={{__html: getTextDiff(target, suggestion)}} />
										<a
											href='#'
											className='card-link'
											onClick={() => this.createRedirect(target)}>
											Izveidot pāradresāciju
										</a>
									</div>
								</div>
							);
						})}
					</div></>)}
					{whatLinksHere.length > 0 && (
						<>
							<h4>Saites uz šo lapu</h4>
							<ul>
								{whatLinksHere.map((link) => (
									<li key={link}>
										<a
											href={`https://lv.wikipedia.org/wiki/${titleForURL(
												link
											)}`}
											target='_blank'
											rel='noopener noreferrer'>
											{link}
										</a>{' '}
										(
										<a
											href={`https://lv.wikipedia.org/w/index.php?title=${titleForURL(
												link
											)}&action=edit`}
											target='_blank'
											rel='noopener noreferrer'>
											labot
										</a>
										)
									</li>
								))}
							</ul>
						</>
					)}
				</div>
			</div>
		);
	}
}

export default Suggestion;
