(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
window.cedilla = {
	api: require('./modules/api'),
	dom: require('./modules/dom'),
	obj: require('./modules/obj'),
	str: require('./modules/str'),
	arr: require('./modules/arr'),
	cookies: require('./modules/cookies'),
	render: require('./modules/render'),
	DEBUG: false
};
window.ç = window.cedilla;
},{"./modules/api":2,"./modules/arr":3,"./modules/cookies":4,"./modules/dom":5,"./modules/obj":6,"./modules/render":7,"./modules/str":8}],2:[function(require,module,exports){

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
const arr = {};
arr.pickRandom = (array) => array[Math.floor(Math.random() * array.length)];

module.exports = arr;
},{}],4:[function(require,module,exports){

const cookies = {
	default: {
		expiration_time: 24 * 60 * 60 * 1000 // 1 DAY
	}
};

cookies.setCookie = (c_name, c_val, ex_time = null, timeInSecond = false) => {
	let d = new Date();
	d.setTime( d.getTime() + (
		ex_time ? ( timeInSecond ? ex_time * 1000 : ex_time * 24 * 60 * 60 * 1000 )
			: cookies.default.expiration_time
	));
	let expires = "expires="+d.toUTCString();
	document.cookie = c_name + "=" + c_val + ";" + expires + ";path=/";
};

cookies.getCookie = (c_name) => {
	let name = c_name + "=";
	let ca = document.cookie.split(';');
	for(let i = 0; i < ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
};

cookies.deleteCookie = (c_name) => {
	document.cookie = c_name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/';
};

module.exports = cookies;
},{}],5:[function(require,module,exports){
const str = require('./str');

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
const possibleEvents = ['abort','afterprint','animationend','animationiteration','animationstart','beforeprint','beforeunload','blur',
	'canplay','canplaythrough','change','click','contextmenu','copy','cut','dblclick','drag','dragend','dragenter','dragleave','dragover',
	'dragstart','drop','durationchange','ended','error','focus','focusin','focusout','fullscreenchange','fullscreenerror','hashchange',
	'input','invalid','keydown','keypress','keyup','load','loadeddata','loadedmetadata','loadstart','message','mousedown','mouseenter',
	'mouseleave','mousemove','mouseout','mouseover','mouseup','offline','online','open','pagehide','paste','pause','play','playing','progress',
	'ratechange','reset','resize','scroll','search','seeked','seeking','select','show','stalled','submit','suspend','timeupdate','toggle',
	'touchcancel','touchend','touchmove','touchstart','transitionend','unload','volumechange','waiting','wheel'
];
for (let i = 0; i < possibleEvents.length; i++) {
	const ev = possibleEvents[i];
	dom.forAll[ev] = (query, cb) => document.querySelectorAll(query).forEach( item => { item.addEventListener(ev, cb) } );
}

dom.makeTable = (data) => {
	let header = [];
	let rows = [];
	if(data instanceof Array){
		header = Object.keys(data[0]);
		for (let i = 0; i < data.length; i++) {
			const v = data[i];
			rows[i] = [];
			for (let j = 0; j < header.length; j++) {
				rows[i][j] = v[header[j]];
			}
		}
	}else{
		header = Object.keys(data);
		for (let i = 0, l = data[header[0]].length; i < l; i++) {
			rows[i] = [];
			for (let j = 0; j < header.length; j++) {
				const h = header[j];
				rows[i][j] = data[h][i];
			}
		}
	}

	let thead = cedilla.dom('tr');
	for (let i = 0; i < header.length; i++) {
		thead.append( dom('th', str.titled(header[i]) ) );
	}

	let trows = [ thead ];
	for (let i = 0; i < rows.length; i++) {
		let row = dom('tr');
		for (let j = 0; j < rows[i].length; j++) {
			//console.log(rows[i][j]);
			row.append(dom('td', rows[i][j]));
		}
		trows.push(row);
	}
	
	return dom('table',{
		content: trows
	});
}

module.exports = dom;
},{"./str":8}],6:[function(require,module,exports){
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
 * @param {object[]} objList array of object to search into.
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
 * @param {object[]} objList array of object to search into.
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

module.exports = obj;
},{}],7:[function(require,module,exports){
const sleep = async n => new Promise( r => setTimeout(r, n));

class Render {
	
	constructor(templateName) {
		
		if(typeof window.Handlebars == 'undefined') throw new Error('Cedilla Render require Handlebars to work');

		this.templateName = templateName;
		this.template = null;
		this.f = null;
		this.loading = new Promise( r => r());
		
		let tDom = document.getElementById(templateName);
		
		if(tDom){
			this.templatePath = '#';
			this.template = tDom.innerHTML;
			this._compile();
		}else{
			this.templatePath = render.default.templates_dir + '/' + templateName + '.hbs';
			this.loading = fetch(this.templatePath, {
				mode: 'same-origin',
				credentials: 'same-origin'
			}).then( res => res.text() ).then((res) => {
				this.template = res;
				this._compile();
			});
		}
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
}

render.default = {
	templates_dir: 'templates',
}

module.exports = render;

},{}],8:[function(require,module,exports){

const str = {};

str.zerofill = (v) => {
	v = parseInt(v);
	return ( v >= 0 && v <= 9 ? '0' : '' ) + v;
};

str.firstUp = (v, forceLower = false) => v.slice(0,1).toUpperCase() + ( forceLower ? v.slice(1).toLowerCase() : v.slice(1) );

str.titled = (v, forceLower = false, separators = ['_', ':']) => {
	let lastC = '';
	let newV = '';
	for (let i = 0; i < v.length; i++) {
		let c = v[i];
		if(separators.includes(c)) c = ' ';
		if( ( i == 0 || lastC.match(/^\s$/) ) && c >= 'a' && c <= 'z' ) {
			c = c.toUpperCase();
		}else if( forceLower && ( i != 0 && lastC.match(/^\S$/) ) && c >= 'A' && c <= 'Z'){
			c = c.toLowerCase();
		}
		newV += c;
		lastC = c;
	}
	return newV;
}

str.htmlStrip = v => (new DOMParser()).parseFromString(v,'text/html').body.textContent;
str.htmlEscape = v => v.replace(/&/g, "&amp;").replace(/'/g, '&#39;').replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;");

module.exports = str;
},{}]},{},[1]);
