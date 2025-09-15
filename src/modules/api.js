
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
	
	if(api.default.CSRFToken){
		fetch_opt.headers.CSRFtoken = api.default.CSRFToken;
	}

	return new Promise( ( resolve, reject ) => {
		return fetch(fetch_route, fetch_opt).then( res => res.json() ).then( res => {
			if(res.time > api.default.response_max_warning_time) {
				console.warn('/' + route + '<br>Time: ' + res.time, 'Slow response');
			}
			if(res.error){
				res.error.toString = function(){ return this.message; }
				if( !triggerGlobalError(res.error) ){
					reject(res.error);
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
	response_max_warning_time: 3,
	fetch_content_type: 'application/octet-stream',
	CSRFToken: null
};

api.raw = (route, data, opt = {}) => {

	const fetch_route = def(opt, 'webhook') + '?_raw=1&_cedilla_route=' + encodeURI(route);
	const fetch_opt = {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': def(opt, 'fetch_content_type'),
		},
		mode: def(opt, 'fetch_mode'),
		credentials: def(opt, 'fetch_credentials'), 
		body: data
	};
	
	if(api.default.CSRFToken){
		fetch_opt.headers.CSRFtoken = api.default.CSRFToken;
	}

	return new Promise( ( resolve, reject ) => {
		return fetch(fetch_route, fetch_opt).then( res => res.json() ).then( res => {
			if(res.time > api.default.response_max_warning_time) {
				console.warn('/' + route + '<br>Time: ' + res.time, 'Slow response');
			}
			if(res.error){
				res.error.toString = function(){ return this.message; }
				if( !triggerGlobalError(res.error) ){
					reject(res.error);
				}
			}else{
				resolve(res.response);
			}
		});
	});
};

api.errorCallback = {
	default: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	route_undefined: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	route_invalid: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	check: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	param_required: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	param_not_required: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	param_invalid: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	internal_error: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	exception_error: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	generic_error: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
	fatal_error: (err) => {
		if(cedilla.DEBUG) console.error(err.message);
		return false;
	},
};

const triggerGlobalError = err => {
	switch(err.type){
		case 'ROUTE_UNDEFINED':
			return api.errorCallback.route_undefined(err);
		case 'ROUTE_INVALID':
			return api.errorCallback.route_invalid(err);
		case 'CHECK_NOT_PASS':
			return api.errorCallback.check(err);
		case 'PARAM_REQUIRED':
			return api.errorCallback.param_required(err);
		case 'PARAM_NOT_REQUIRED':
			return api.errorCallback.param_not_required(err);
		case 'PARAM_INVALID':
			return api.errorCallback.param_invalid(err);
		case 'INTERNAL_ERROR':
			return api.errorCallback.internal_error(err);
		case 'EXCEPTION_ERROR':
			return api.errorCallback.exception_error(err);
		case 'FATAL_ERROR':
			return api.errorCallback.fatal_error(err);
		case 'GENERIC_ERROR':
			return api.errorCallback.generic_error(err);
	}
	return api.errorCallback.default(err);
};

document.addEventListener('click', function (event) {
	const t = event.target.closest('[ced-action]');
	if (!t) return;
	const args = {};
	for (let i = 0; i < t.attributes.length; i++) {
		const att = t.attributes[i];
		if(att.name.slice(0,8) == 'ced-args'){
			args[att.name.slice(9)] = att.value;
		}
	}
	const action = t.getAttribute('ced-action');
	let cb = t.getAttribute('ced-callback');
	if(cb){
		cb = cb.split('.');
		let context = window;
		let lastContext = window;
		for (let i = 0; i < cb.length; i++) {
			lastContext = context;
			context = context[cb[i]];
		}
		//rebind context to main context, prevent Uncaught TypeError: Illegal invocation
		context = context.bind(lastContext);
		api(action, args).then((data) => context(data));
	}else{
		api(action, args);
	}
});

module.exports = api;