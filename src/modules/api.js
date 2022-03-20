
const def = (options, key) => options[key] || api.default[key];

const api = (route, data = {}, opt = {}) => {

	const fetch_route = def(opt, 'webhook') + '?_cedilla_route=' + encodeURI(route);
	const fetch_opt = {
		method: def(opt, 'fetch_method'),
		headers: {
			'Content-Type': 'application/json'
		},
		mode: def(opt, 'fetch_mode'),
		credentials: def(opt, 'fetch_credentials'), 
		body: JSON.stringify(data)
	};

	return new Promise( ( resolve, reject ) => {
		return fetch(fetch_route, fetch_opt).then( res => res.json() ).then( res => {
			if(res.errors.length > 0){
				let triggErr = triggerGlobalError(res.errors);
				if(!triggErr) reject(res.errors);
			}else{
				resolve(res.response);
			}
		});
	});
};

api.default = {
	webhook: 'api.php',
	fetch_method: 'POST',
	fetch_mode: 'same-origin',
	fetch_credentials: 'same-origin',
};

api.errorCallback = {
	default: (err) => {
		console.error(err); //DEBUG
		return false;
	},
	route_undefined: () => {
		console.error('missing route'); //DEBUG
		return false;
	},
	route_invalid: (v) => {
		console.error('invalid route: ' + v); //DEBUG
		return false;
	},
	check: (v) => {
		console.error('check \'' + v + '\' not passed'); //DEBUG
		return false;
	},
	param_required: (v) => {
		console.error('required param ' + v); //DEBUG
		return false;
	},
	param_not_required: (v) => {
		console.error('param ' + v + ' not required'); //DEBUG
		return false;
	},
	param_invalid: (v) => {
		console.error('invalid param ' + v); //DEBUG
		return false;
	},
	internal_error: (v) => {
		console.error('internal server error: ' + v); //DEBUG
		return false;
	},
};

const triggerGlobalError = errors => {
	const matcher = /^(A)|(?:(B|C|R|N|I|E):(.+))$/;
	for (let i = 0; i < errors.length; i++) {
		const err = errors[i];
		const match = matcher.exec(err);
		if(match){
			if(match[1] == 'A') {
				return api.errorCallback.route_undefined();
			}
			switch(match[2]){
				case 'B':
					return api.errorCallback.route_invalid(match[3]);
				case 'C':
					return api.errorCallback.check(match[3]);
				case 'R':
					return api.errorCallback.param_required(match[3]);
				case 'N':
					return api.errorCallback.param_not_required(match[3]);
				case 'I':
					return api.errorCallback.param_invalid(match[3]);
				case 'E':
					return api.errorCallback.internal_error(match[3]);
			}
			return api.errorCallback.default(err);
		}
	}
};

module.exports = api;