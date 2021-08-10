import React, { Component } from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import styles from './styles.module.css';

class App extends Component {
	constructor(props) {
		super(props);
		this.state = {
			loadingData: true,
			loadingError: false,
			data: [],
		};
	}
	
	render() {
		return <div className="container">
			<span className={styles.noRatingText}>AAA</span>
		</div>
	}
}


export default App;
