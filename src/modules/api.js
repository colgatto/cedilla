
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
				let triggErr = errorCB(res);
				if(!triggErr) reject();
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
	default: (err) => { console.error(err) },
	route_undefined: () => { console.error('missing route') }, 
	route_invalid: (v) => { console.error('invalid route: ' + v) }, 
	check: (v) => { console.error('check \'' + v + '\' not passed') }, 
	param_required: (v) => { console.error('required param ' + v) }, 
	param_not_required: (v) => { console.error('param ' + v + ' not required') }, 
	param_invalid: (v) => { console.error('invalid param ' + v) },
	internal_error: (v) => { console.error('internal server error: ' + v) },
};


const errorCB = (res) => {
	const matcher = /^(A)|(?:(B|C|R|N|I|E):(.+))$/;
	for (let i = 0; i < res.errors.length; i++) {
		const err = res.errors[i];
		const match = matcher.exec(err);
		if(match){
			//console.log(match);
			if(match[1] == 'A') {
				api.errorCallback.route_undefined();
				return true;
			}
			switch(match[2]){
				case 'B':
					api.errorCallback.route_invalid(match[3]);
					return true;
				case 'C':
					api.errorCallback.check(match[3]);
					return true;
				case 'R':
					api.errorCallback.param_required(match[3]);
					return true;
				case 'N':
					api.errorCallback.param_not_required(match[3]);
					return true;
				case 'I':
					api.errorCallback.param_invalid(match[3]);
					return true;
				case 'E':
					api.errorCallback.internal_error(match[3]);
					return true;
			}
			return false;//api.errorCallback.default(err);
		}
	}
};

module.exports = api;