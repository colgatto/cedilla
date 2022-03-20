(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
window.cedilla = {
	api: require('./modules/api'),
	dom: require('./modules/dom'),
	str: require('./modules/str'),
	arr: require('./modules/arr'),
	cookies: require('./modules/cookies')
};
window.ç = window.cedilla;
},{"./modules/api":2,"./modules/arr":3,"./modules/cookies":4,"./modules/dom":5,"./modules/str":6}],2:[function(require,module,exports){

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
},{"./str":6}],6:[function(require,module,exports){

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
