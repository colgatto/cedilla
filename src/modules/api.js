
const def = (options, key) => options[key] || api.default[key];

const api = (route, data = {}, opt = {}) => {

	const fetch_route = def(opt, 'webhook') + '?_cedilla_route=' + encodeURI(route);
	const fetch_opt = {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		},
		mode: def(opt, 'fetch_mode'),
		credentials: def(opt, 'fetch_credentials'), 
		body: JSON.stringify(data)
	};

	return new Promise( ( resolve, reject ) => {
		return fetch(fetch_route, fetch_opt).then( res => res.json() ).then( res => {
			if(res.time > api.default.response_max_warning_time) {
				console.warn('/' + route + '<br>Time: ' + res.time, 'Slow response');
			}
			if(res.error){
				if( !triggerGlobalError(res.error) ){
					reject(res.error.message, res.error.code);
				}
			}else{
				resolve(res.response);
			}
		});
	});
};

api.default = {
	webhook: 'api.php',
	fetch_mode: 'same-origin',
	fetch_credentials: 'same-origin',
	response_max_warning_time: 3
};

api.errorCallback = {
	default: (message) => {
		if(cedilla.DEBUG) console.error(message);
		return false;
	},
	route_undefined: (message) => {
		if(cedilla.DEBUG) console.error(message);
		return false;
	},
	route_invalid: (message) => {
		if(cedilla.DEBUG) console.error(message);
		return false;
	},
	check: (message) => {
		if(cedilla.DEBUG) console.error(message);
		return false;
	},
	param_required: (message) => {
		if(cedilla.DEBUG) console.error(message);
		return false;
	},
	param_not_required: (message) => {
		if(cedilla.DEBUG) console.error(message);
		return false;
	},
	param_invalid: (message) => {
		if(cedilla.DEBUG) console.error(message);
		return false;
	},
	internal_error: (message) => {
		if(cedilla.DEBUG) console.error(message);
		return false;
	},
};

const triggerGlobalError = err => {
	switch(err.code){
		case 'ROUTE_UNDEFINED':
			return api.errorCallback.route_undefined(err.message, err.code);
		case 'ROUTE_INVALID':
			return api.errorCallback.route_invalid(err.message, err.code);
		case 'CHECK_NOT_PASS':
			return api.errorCallback.check(err.message, err.code);
		case 'PARAM_REQUIRED':
			return api.errorCallback.param_required(err.message, err.code);
		case 'PARAM_NOT_REQUIRED':
			return api.errorCallback.param_not_required(err.message, err.code);
		case 'PARAM_INVALID':
			return api.errorCallback.param_invalid(err.message, err.code);
		case 'INTERNAL_ERROR':
			return api.errorCallback.internal_error(err.message, err.code);
	}
	return api.errorCallback.default(err.message, err.code);
};

module.exports = api;