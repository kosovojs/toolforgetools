import React, { useEffect } from 'react';
import MainComp from './main';
import PropTypes from 'prop-types';
import ErrorBoundary from './ErrorBoundary';
import { checkStatus } from './appSlice';
import styles from './styles.module.css';

import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const App = () => {

	return (
		<>
				<ErrorBoundary>
					<MainComp />
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

export default App;
