(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
window.cedilla = {
	api: require('./modules/api'),
	dom: require('./modules/dom'),
	obj: require('./modules/obj'),
	render: require('./modules/render'),
	DEBUG: false
};
window.ç = window.cedilla;
},{"./modules/api":2,"./modules/dom":3,"./modules/obj":4,"./modules/render":5}],2:[function(require,module,exports){

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
			}else if(res.log){
				console.log(res.log);
			}else if(res.debug){
				const mod = document.createElement('pre');
				Object.assign(mod.style, {
					width: '100%',
					height: '100%',
					'z-index': '10000',
					position: 'fixed',
					top: 0,
					background: '#1a1a1aed',
					color: '#fff',
					margin: '0 auto',
					display: 'flex',
					'justify-content': 'center',
					'padding-top': '1rem',
				});
				mod.innerHTML = JSON.stringify(res.debug, null, 4);
				const close = document.createElement('button');
				close.classList.add('btn', 'btn-outline-secondary');
				Object.assign(close.style, {
					position: 'fixed',
					'z-index': '10001',
					top: '1rem',
					right: '1rem'
				});
				close.innerHTML = 'X';
				close.onclick = () => {
					mod.remove();
					close.remove();
				};
				document.body.append(mod);
				document.body.append(close);
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
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	route_undefined: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	route_invalid: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	check: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	param_required: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	param_not_required: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	param_invalid: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	pdo_error: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	internal_error: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	exception_error: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	generic_error: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
		return false;
	},
	fatal_error: (err) => {
		if(cedilla.DEBUG) console.error(err.details || err.message);
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
		case 'PDO_ERROR':
			return api.errorCallback.pdo_error(err);
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
},{}],3:[function(require,module,exports){
const dom = (tagName, options = {}) => {
	let el = document.createElement(tagName);
	/**
	 * se passato content al posto di options
	 * se opotion è String | Element | Array
	*/
	if(typeof options == 'string'){
		el.innerHTML = options;
		return el;
	}else if(options instanceof Element){
		el.append(options);
		return el;
	}else if( options instanceof Array){
		for (let i = 0; i < options.length; i++) {
			el.append(options[i]);
		}
		return el;
	}

	/**
	 * click: function(){}
	 */
	if(typeof options.click == 'function'){
		el.onclick = options.click;
	}

	/**
	 * class: string | Array
	 */
	if(typeof options.class == 'string'){
		el.className = options.class;
	}else if(typeof options.class == 'object'){
		for (let i = 0; i < options.class.length; i++) {
			el.classList.add(options.class[i]);
		}
	}

	/**
	 * data: Object
	 */
	if(typeof options.data == 'object'){
		let d = Object.keys(options.data);
		for (let i = 0; i < d.length; i++) {
			const k = d[i];
			el.dataset[k] = options.data[k];
		}
	}

	/**
	 * content: String | Element | Array
	*/
	if(typeof options.content != 'undefined'){
		dom_append_content(options.content, el);
	}

	return el;
}

const dom_append_content = (content, el) => {
	if(content instanceof Array){
		for (let i = 0; i < content.length; i++) {
			dom_append_content(content[i], el);
		}
	}else if(content instanceof Element){
		el.append(content);
	}else {
		el.innerHTML = '' + content;
	}
}

dom.q = (query) => document.querySelector(query);
dom.qAll = (query) => document.querySelectorAll(query);
dom.forAll = (query, cb) => document.querySelectorAll(query).forEach( cb );
/**
 * @public
 * @method addEvent
 * @param {String} eventName Event name
 * @param {String|Element|HTMLCollection} selector DOMElement, HTMLCollection or queryselector
 * @param {Function} cb callback to run on event
 * @param {Boolean} useCapture A boolean value indicating whether events of this type will be dispatched to the registered listener before being dispatched to any EventTarget beneath it in the DOM tree
 */
dom.addEvent = (eventName, selector, cb, useCapture = false) => {
	if(selector instanceof Element){
		selector.addEventListener(eventName, (e) => {
			return cb(e, selector);
		}, useCapture);
	}else if(selector instanceof HTMLCollection){
		for (let i = 0; i < selector.length; i++) {
			const el = selector[i];
			el.addEventListener(eventName, (e) => {
				return cb(e, el);
			}, useCapture);
		}
	}else{
		document.querySelectorAll(selector).forEach( el => el.addEventListener(eventName, (e) => {
			return cb(e, el);
		}, useCapture));
	}
};
dom.stringify = (el) => {
	const tmpEl = document.createElement("div");
	tmpEl.appendChild(el.cloneNode(true));
	return tmpEl.innerHTML;
};

module.exports = dom;
},{}],4:[function(require,module,exports){
const obj = {};

/**
 * Recursively copy the values of all of the enumerable own properties from one or more source objects to a target object. 
 * @param {object} object The target object to copy to.
 * @param {object} toassign The object to copy.
 * @return {object} The target object.
 */
obj.recAssign = ( object, ...toassign ) => {
	const isPlainObject = o => o !== null && typeof o !== 'undefined' && typeof o.constructor !== 'undefined' && o.constructor.prototype === Object.prototype;
	const assign = ( ref, key, value ) => {
		if( isPlainObject(value) ){
			if( !isPlainObject(ref[key]) ){
				ref[key] = {};
			}
			mergeInObject( ref[key], value );
		}else{
			ref[key] = value;
		}
	};
	const mergeInObject = ( dest, data ) => {
		Object.keys( data ).forEach( key => {
			assign( dest, key, data[key] );
		});
	};
	if( typeof object === 'object' ){
		toassign.forEach( data => {
			if( isPlainObject(data) ){
				mergeInObject( object, data );
			}
		});
	}
	return object;
};

/**
 * Recursively trasform object into array of {key, value} object
 * @param {object} data The starting object.
 * @return {array} The final array.
 */
obj.flatObj = (data) => {
	let keys = Object.keys(data);
	let plain = [];
	for (let i = 0; i < keys.length; i++) {
		const k = keys[i];
		const v = data[k];
		if(v === null || typeof v != 'object'){
			plain.push({key: k, value: v});
		}else{
			if(Object.keys(v).length == 0){
				plain.push({key: k, value: null});
			}else{
				plain.push(...obj.flatObj(v));
			}
		}
	}
	return plain;
};

/**
 * Return the object with object.key equals to value searched
 * @param {object[]} objList Array of object to search within.
 * @param {string} key The key to use for match.
 * @param {any} value The value searched.
 * @return {any} The return value.
 */
obj.getBy = (objList, key, value) => {
	for (let i = 0; i < objList.length; i++) {
		if(objList[i][key] === value)
			return objList[i];
	}
};

/**
 * Return the index of object with object.key equals to value searched, -1 if not found
 * @param {object[]} objList Array of object to search within.
 * @param {string} key The key to use for match.
 * @param {any} value The value searched.
 * @return {int} The index found.
 */
obj.getIndexBy = (objList, key, value) => {
	for (let i = 0; i < objList.length; i++) {
		if(objList[i][key] == value)
			return i;
	}
	return -1;
};

/**
 * Return true if object has at least one of the key
 * @param {object} obj Object to search within
 * @param {...string} keys List of key to match
 */
obj.hasAny = (obj, ...keys) => {
	const kList = Object.keys(obj);
	for (let i = 0; i < keys.length; i++) {
		if(kList.includes(keys[i])) return true;
	}
	return false;
};

module.exports = obj;
},{}],5:[function(require,module,exports){
class Render {

	constructor(templateName, template = null) {

		if(typeof window.Handlebars == 'undefined') throw new Error('Cedilla Render require Handlebars to work');

		this.templateName = templateName;
		this.template = template;
		this.f = null;
		this.templatePath = null;
		this.loading = new Promise( r => r());

		if(this.template) {
			this._compile();
			return;
		}

		let tDom = document.getElementById(templateName);

		if(tDom){
			this.templatePath = '#';
			this.template = tDom.innerHTML;
			this._compile();
			return;
		}
		this.templatePath = render.default.templates_dir + '/' + templateName + '.hbs';
		this.loading = fetch(this.templatePath, {
			mode: 'same-origin',
			credentials: 'same-origin'
		}).then( res => res.text() ).then((res) => {
			this.template = res;
			this._compile();
		});
	}

	_compile(){
		this.f = window.Handlebars.compile(this.template);
	}

	with(data){
		this.data = data;
		return this;
	}

	on(selector, overwrite = false){
		return new Promise( async (resolve) => {
			await this.loading;
			const rendered = this.f(this.data);
			document.querySelectorAll(selector).forEach( el => {
				if(overwrite) el.innerHTML = rendered;
				else el.innerHTML += rendered;
			});
			resolve();
		});
	}
}

const _render_pool = {};

const render = (templateName) => {
	if(typeof _render_pool[templateName] == 'undefined') _render_pool[templateName] = new Render(templateName);
	return _render_pool[templateName];
};

render.load = (templateName, template) => {
	if(typeof _render_pool[templateName] == 'undefined') _render_pool[templateName] = new Render(templateName, template);
	return _render_pool[templateName];
};

render.default = {
	templates_dir: 'templates',
}

module.exports = render;

},{}]},{},[1]);
