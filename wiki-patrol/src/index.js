import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from "react-redux";

require('./global.scss');

import 'bootstrap/dist/css/bootstrap.min.css';

import { configureStore } from '@reduxjs/toolkit'

import App from './components/App/component';

import rootReducer from './reducer';

const store = configureStore({
	reducer: rootReducer,
});

ReactDOM.render(<Provider store={store}>
	<App />
</Provider>, document.getElementById('app'));
