import { createSlice } from '@reduxjs/toolkit';
import api from '../../api/methods';
import { toast } from 'react-toastify';
import rcTransformer from './transform_rc';

const rcSlice = createSlice({
	name: 'rc',
	initialState: { entries: {}, loading: false, error: false, error_msg: null },
	reducers: {
		setRC: {
			reducer(state, action) {
				const { entries } = action.payload;
				return { ...state, entries };
			},
			prepare(entries) {
				return { payload: { entries } };
			},
		},
		setLoading: {
			reducer(state) {
				return { ...state, loading: true };
			},
		},
		setSucess: {
			reducer(state) {
				return { ...state, loading: false };
			},
		},
	},
});

const recentChangesArticlesSelector = state => Object.keys(state.entries);

const rcEntrySelector = (state, article) => state.entries[article];

const { setRC, setLoading, setSucess } = rcSlice.actions;

const setRCFromAPI = () => (dispatch) => {
	dispatch(setLoading());

	const res = api.tool.getRC().then(res=> {

		const transformed = rcTransformer(res);

		dispatch(setRC(transformed));
		dispatch(setSucess());
	})
};

export { setRCFromAPI, recentChangesArticlesSelector, rcEntrySelector };

export default rcSlice.reducer;
