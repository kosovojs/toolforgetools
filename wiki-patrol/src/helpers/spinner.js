import React from 'react';
import clsx from 'clsx';
import CircularProgress from '@material-ui/core/CircularProgress';
import styles from './spinner_styles.module.css';

const Spinner = () => {
	return <div className={styles.loadingWrapper}>
	<div className={clsx("spinner-border", styles.loadingIndicator, styles.largeSpinner)} role="status">
	  {/* <span className="sr-only">Loading...</span> */}
	  <CircularProgress />
	</div>
  </div>;
}

export default Spinner;
