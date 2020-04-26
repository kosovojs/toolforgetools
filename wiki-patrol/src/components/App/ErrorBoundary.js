import React from 'react';
import PropTypes from 'prop-types'

export default class ErrorBoundary extends React.Component {
	constructor(props) {
		super(props);
		this.state = { error: null, errorInfo: null };
	}

	componentDidCatch(error, errorInfo) {
		this.setState({
			error,
			errorInfo
		});
	}

	render() {
		if (this.state.errorInfo) {
			return (
				<div className='bodyWrapper'>
					<h2>Notika kļūda, ielādējot lapu!</h2>
					<details style={{ whiteSpace: 'pre-wrap' }}>
						{this.state.error && this.state.error.toString()}
						<br />
						{this.state.errorInfo.componentStack}
					</details>
				</div>
			);
		}
		return this.props.children;
	}
}

ErrorBoundary.propTypes = {
	children: PropTypes.node
}
