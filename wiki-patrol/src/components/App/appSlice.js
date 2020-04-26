import { createSlice } from '@reduxjs/toolkit';
import api from '../../api/methods';
import { toast } from 'react-toastify';

const appSlice = createSlice({
	name: 'app',
	initialState: { isAuth: false, user: null, trustedUsers: [] },
	reducers: {
		setAuthUser: {
			reducer(state, action) {
				const { user } = action.payload;
				return { ...state, user, isAuth: true };
			},
			prepare(user) {
				return { payload: { user } };
			}
		},
		setTrustedUsers: {
			reducer(state, action) {
				const { users } = action.payload;
				return { ...state, trustedUsers: users };
			},
			prepare(users) {
				return { payload: { users } };
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
	setTrustedUsers,
	setAuthUser,
	logout
} = appSlice.actions;

const checkStatus = () => (dispatch, getState) => {
	api.tool.check().then(res => {
		if ('error' in res) {
			return;
		}

		const userName = res.query.userinfo.name;

		dispatch(setAuthUser(userName));
	});
};

const trustedUsers = () => (dispatch) => {
	api.mediawiki.trustedusers().then(res => {
		const users = res.query.allusers.map(usr => usr.name)

		dispatch(setTrustedUsers(users));
	});
};

export { checkStatus, trustedUsers };

export default appSlice.reducer;
