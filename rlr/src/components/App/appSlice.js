import { createSlice } from '@reduxjs/toolkit';
import api from '../../api/methods';
import { toast } from 'react-toastify';

const appSlice = createSlice({
	name: 'app',
	initialState: { isAuth: false, user: null, articles: null, lastArticleDate: null },
	reducers: {
		setOverview: {
			reducer(state, action) {
				const { count, date } = action.payload;
				return { ...state, articles: count, lastArticleDate: date };
			},
			prepare(count, date) {
				return { payload: { count, date } };
			}
		},
		setAuthUser: {
			reducer(state, action) {
				const { user } = action.payload;
				return { ...state, user, isAuth: true };
			},
			prepare(user) {
				return { payload: { user } };
			}
		},
		logout: {
			reducer(state) {
				return { ...state, user: null, isAuth: false };
			}
		},
	}
});

const {
	setOverview,
	setAuthUser,
	logout
} = appSlice.actions;

const checkStatus = () => (dispatch, getState) => {
	//const { id, fetching } = getState().article;

	api.tool.check().then(res => {
		if ('error' in res) {
			return;
		}
		//const fakeRes = {"batchcomplete":"","query":{"userinfo":{"id":9590,"name":"Edgars2007","groups":["bureaucrat","interface-admin","sysop","*","user","autoconfirmed"],"rights":["autopatrol","autoconfirmed","editsemiprotected","ipblock-exempt","skipcaptcha","abusefilter-log-detail","flow-edit-post","read","edit","writeapi","abusefilter-view","abusefilter-log","flow-hide","minoredit","purge","applychangetags","changetags"]}}};

		const userName = res.query.userinfo.name;

		dispatch(setAuthUser(userName));


		//console.log(res)
		//{"article":{"id":"1947","title":"Simona Krupeckaite"},"results":"5433","last_article":"2019-04-02 20:44:00"}

		//dispatch(getNextArticle(id, title));
	});
};

const setArticleCount = (count) => (dispatch) => {
	dispatch(setOverview(count, null));
};

export { checkStatus, setArticleCount };

export default appSlice.reducer;
