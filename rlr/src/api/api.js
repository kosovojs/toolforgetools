import ApiError from './error';
import formatURL from './formatURL';

const fetchResource = (path, userOptions = {}) => {
	// Define default options
	const defaultOptions = {};
	// Define default headers
	const defaultHeaders = {};
	const bodyMethods = ['post', 'put', 'patch'];

	const options = {
		// Merge options
		...defaultOptions,
		...userOptions,
		// Merge headers
		headers: {
			...defaultHeaders,
			...userOptions.headers
		}
	};

	options.method = options.method.toLowerCase();

	// Detect is we are uploading a file
	const isFile = options.body instanceof File;

	// Stringify JSON data
	// If body is not a file
	if (
		options.body &&
		typeof options.body === 'object' &&
		!isFile &&
		bodyMethods.indexOf(options.method) > -1
	) {
		options.body = JSON.stringify(options.body);
	}

	// Build Url
	const url = formatURL(path, options.params);

	// Variable which will be used for storing response
	let response = null;

	return (
		fetch(url, options)
			.then(responseObject => {
				// Saving response for later use in lower scopes
				response = responseObject;

				// HTTP unauthorized
				/* if (response.status === 401) {
          // Handle unauthorized requests
          // Maybe redirect to login page?
        } */

				// Check for error HTTP error codes
				if (response.status < 200 || response.status >= 300) {
					// Get response as text
					return response.text();
				}

				// Get response as json
				return response.json();
			})
			// "parsedResponse" will be either text or javascript object depending if
			// "response.text()" or "response.json()" got called in the upper scope
			.then(parsedResponse => {
				// Check for HTTP error codes
				if (response.status < 200 || response.status >= 300) {
					// Throw error
					throw parsedResponse;
				}

				// Request succeeded
				return parsedResponse;
			})
			.catch(error => {
				// Throw custom API error
				// If response exists it means HTTP error occured
				if (response) {
					throw new ApiError(
						`Request failed with status ${response.status}.`,
						error,
						response.status
					);
				} else {
					throw new ApiError(error.toString(), null, 'REQUEST_FAILED');
				}
			})
	);
};

const get = (path, params = {}) => {
	return fetchResource(path, { params, method: 'get' });
};

const post = (path, body = {}) => {
	return fetchResource(path, { body, method: 'post' });
};

const put = (path, body = {}) => {
	return fetchResource(path, { body, method: 'put' });
};

export default fetchResource;

export { get, post, put };
