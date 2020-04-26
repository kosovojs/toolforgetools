import React, { useEffect } from 'react';
import RecentChanges from '../RecentChanges';
import PropTypes from 'prop-types';
import ErrorBoundary from './ErrorBoundary';
import { checkStatus, trustedUsers } from './appSlice';
import { connect } from 'react-redux';

import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const App = ({ trustedUsers }) => {
	useEffect(() => {
		trustedUsers();
		//checkStatus();
	}, []);

	return (
		<>
			<ErrorBoundary>
				<RecentChanges />
			</ErrorBoundary>
			<ToastContainer
				position='bottom-right'
				autoClose={2500}
				hideProgressBar={false}
				newestOnTop={false}
				closeOnClick
				rtl={false}
				pauseOnVisibilityChange
				draggable={false}
				pauseOnHover
			/>
		</>
	);
};

App.propTypes = {
	trustedUsers: PropTypes.func
};

export default connect(null, { trustedUsers })(App);
