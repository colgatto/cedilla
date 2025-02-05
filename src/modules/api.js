
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
				toastr.warning('/' + route + '<br>Time: ' + res.time, 'Slow response');
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

api.errorCallback = {
	default: (err) => {
		if(cedilla.DEBUG) console.error(err);
		return false;
	},
	route_undefined: (err) => {
		if(cedilla.DEBUG) console.error(err);
		return false;
	},
	route_invalid: (err) => {
		if(cedilla.DEBUG) console.error(err);
		return false;
	},
	check: (err) => {
		if(cedilla.DEBUG) console.error(err);
		return false;
	},
	param_required: (err) => {
		if(cedilla.DEBUG) console.error(err);
		return false;
	},
	param_not_required: (err) => {
		if(cedilla.DEBUG) console.error(err);
		return false;
	},
	param_invalid: (err) => {
		if(cedilla.DEBUG) console.error(err);
		return false;
	},
	internal_error: (err) => {
		if(cedilla.DEBUG) console.error(err);
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