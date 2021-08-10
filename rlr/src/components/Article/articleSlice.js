import { createSlice } from '@reduxjs/toolkit';
import api from '../../api/methods';
import {setArticleCount } from '../App/appSlice';
import { toast } from 'react-toastify';

//https://github.com/iamhosseindhv/notistack

const articleSlice = createSlice({
	name: 'article',
	initialState: { id: 0, title: null, fetching: false, saving: false, fromList: false },
	reducers: {
		getNextArticle: {
			reducer(state, action) {
				const { id, title } = action.payload;
				//console.log(id, title);
				return { ...state, id, title, fetching: false };
			},
			prepare(id, title) {
				return { payload: { id, title } };
			}
		},
		setFetchStart: {
			reducer(state) {
				return { ...state, fetching: true };
			}
		},
		setComingFromArticleList: {
			reducer(state, action) {
				const { newState } = action.payload;
				return { ...state, fromList: newState };
			},
			prepare(newState) {
				return { payload: { newState } };
			}
		},
		setSaveProcess: {
			reducer(state) {
				return { ...state, saving: !state.saving };
			}
		},
		resetCounter: {
			reducer(state) {
				return { id: 0, title: null, fetching: false, saving: false, fromList: false };
			}
		},
		setTitle: {
			reducer(state, action) {
				const { title } = action.payload;
				//console.log(id, title);
				return { ...state, title };
			},
			prepare(title) {
				return { payload: { title } };
			}
		}
	}
});

const {
	getNextArticle,
	setFetchStart,
	setSaveProcess,
	resetCounter,
	setTitle,
	setComingFromArticleList
} = articleSlice.actions;

const fetchNextArticle = (mode, optID = null) => (dispatch, getState) => {
	const { id, fetching } = getState().article;

	if (fetching) {
		console.error('Already fetching');
		return;
	}

	dispatch(setFetchStart(true));
	api.tool.nextArticle(optID || id, mode).then(res => {
		//{"article":{"id":"1947","title":"Simona Krupeckaite"},"results":"5433","last_article":"2019-04-02 20:44:00"}

		const {article: {id, title}, results} = res;

		dispatch(getNextArticle(id, title));
		dispatch(setArticleCount(parseInt(results)));
	});
};

const saveArticle = (articleID = null, articleTitle = null, fetchNext = true) => (
	dispatch,
	getState
) => {
	//let id, title;

	if (articleID === null) {
		var { id, title } = getState().article;
	} else {
		var [id, title] = [articleID, articleTitle];
	}

	dispatch(setSaveProcess(true));
	api.tool
		.saveArticle(id)
		.then(res => {
			dispatch(setSaveProcess(false));
			if (res.status === 'error') {
				toast.warn(`Neveiksmīga saglabāšana: ${res.message}`, { autoClose: 7500 });
			} else {
				toast.success(`Darbība rakstam "${title}" saglabāta`, { autoClose: 3000 });
				if (fetchNext) {
					//if called from article list, there is no need to fetch next article
					dispatch(fetchNextArticle('next'));
				} else {
					var {articles } = getState().app;
					dispatch(setArticleCount(articles-1));
				}
			}
		})
		.catch(err => {
			dispatch(setSaveProcess(false));
			toast.warn(`Neveiksmīga saglabāšana`, { autoClose: 7500 });
		});
};

const putArticleInQueqe = (comment = null) => (dispatch, getState) => {
	const { id, title } = getState().article;

	dispatch(setSaveProcess(true));
	api.tool
		.putArticleInQueqe(id, comment)
		.then(res => {
			dispatch(setSaveProcess(false));
			if (res.status === 'error') {
				toast.warn(`Neveiksmīga saglabāšana: ${res.message}`, { autoClose: 7500 });
			} else {
				toast.success(`Darbība rakstam "${title}" saglabāta`, { autoClose: 3000 });
				dispatch(fetchNextArticle('next'));
			}
		})
		.catch(err => {
			dispatch(setSaveProcess(false));
			toast.warn(`Neveiksmīga saglabāšana: ${err}`, { autoClose: 7500 });
		});
};

const resetID = () => dispatch => {
	dispatch(resetCounter());
};

const updateArticleTitle = newTitle => dispatch => {
	dispatch(setTitle(newTitle));
};

const settingFromArticleList = (newValue) => dispatch => {
	dispatch(setComingFromArticleList(newValue));
}

export { fetchNextArticle, saveArticle, putArticleInQueqe, resetID, updateArticleTitle, settingFromArticleList };

export default articleSlice.reducer;
