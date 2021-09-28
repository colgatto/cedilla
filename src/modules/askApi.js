const config = require('../config');

const askApi = (route, data = {}) => new Promise( ( resolve, reject ) => {
	return fetch(config.apiEndpoint + '?_cedilla_route=' + encodeURI(route), {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		mode: 'same-origin',
		credentials: 'same-origin', 
		body: Object.keys( data ).map( k => k + '=' + data[k] ).join('&')
	}).then( res => res.json() ).then( res => {
		if(res.errors.length > 0){
			errorCB(res);
		}
		resolve(res.response);
	} );
});

askApi.errorCallback = {
	default: (err) => { console.error(err) },
	route_undefined: () => { console.log('missing route') }, 
	route_invalid: (v) => { console.log('invalid route: ' + v) }, 
	check: (v) => { console.log('check \'' + v + '\' not passed') }, 
	param_required: (v) => { console.log('required param ' + v) }, 
	param_not_required: (v) => { console.log('param ' + v + ' not required') }, 
	param_invalid: (v) => { console.log('invalid param ' + v) },
};

const errorCB = (res) => {
	const matcher = /^(A)|(?:(B|C|R|N|I):(.+))$/;
	for (let i = 0; i < res.errors.length; i++) {
		const err = res.errors[i];
		const match = matcher.exec(err);
		if(match){
			//console.log(match);
			if(match[1] == 'A') return askApi.errorCallback.route_undefined();
			switch(match[2]){
				case 'B':
					return askApi.errorCallback.route_invalid(match[3]);
				case 'C':
					return askApi.errorCallback.check(match[3]);
				case 'R':
					return askApi.errorCallback.param_required(match[3]);
				case 'N':
					return askApi.errorCallback.param_not_required(match[3]);
				case 'I':
					return askApi.errorCallback.param_invalid(match[3]);
			}
		}
		return askApi.errorCallback.default(err);
	}
};

module.exports = askApi;