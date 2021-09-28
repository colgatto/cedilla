(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
module.exports = {
	apiEndpoint: 'api.php',
	defaultDom: {
		type: []
	}
};
},{}],2:[function(require,module,exports){
window.cedilla = {
	askApi: require('./modules/askApi'),
	dom: require('./modules/dom'),
	str: require('./modules/str'),
	arr: require('./modules/arr'),
	cookies: require('./modules/cookies')
};
window.ç = window.cedilla;
},{"./modules/arr":3,"./modules/askApi":4,"./modules/cookies":5,"./modules/dom":6,"./modules/str":7}],3:[function(require,module,exports){
const arr = {};
arr.pickRandom = (array) => array[Math.floor(Math.random() * array.length)];

module.exports = arr;
},{}],4:[function(require,module,exports){
const config = require('../config');

const errorCB = (res) => {
	const matcher = /^(A)|((B|C|((R|N|I)(R|P|G))):(.+))$/;
	for (let i = 0; i < res.errors.length; i++) {
		const err = res.errors[i];
		const match = matcher.exec(err);
		if(match){
			console.log(match);
			if(match[1] == 'A'){
				console.log('missing route');
			}else if(match[3] == 'B'){
				console.log('route not valid: ' + match[7]);
			}else if(match[3] == 'C'){
				console.log('check error: ' + match[7]);
			}else if(match[5] == 'R'){
				//console.log('param required');
				switch(match[6]){
					case 'R':
						console.log(match[7] + ' required');
						break;
					case 'P':
						console.log(match[7] + ' required on post');
						break;
					case 'G':
						console.log(match[7] + ' required on get');
						break;
				}
			}else if(match[5] == 'N'){
				//console.log('param not required');
				switch(match[6]){
					case 'R':
						console.log(match[7] + ' not required');
						break;
					case 'P':
						console.log(match[7] + ' on post is not required');
						break;
					case 'G':
						console.log(match[7] + ' on get is not required');
						break;
				}
			}else if(match[5] == 'I'){
				//console.log('param invalid');
				switch(match[6]){
					case 'R':
						console.log(match[7] + ' invalid');
						break;
					case 'P':
						console.log(match[7] + ' on post is invalid');
						break;
					case 'G':
						console.log(match[7] + ' on get is invalid');
						break;
				}
			}else{
				console.error(err);
			}
		}else{
			console.error(err);
		}
	}
};

const askApi = (route, data = {}) => new Promise( ( resolve, reject ) => {
	let args =  Object.assign( {}, data, { '_cedilla_route': route } );
	return fetch(config.apiEndpoint, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		mode: 'same-origin',
		credentials: 'same-origin', 
		body: Object.keys( args ).map( k => k + '=' + args[k] ).join('&')
	}).then( res => res.json() ).then( res => {
		if(res.errors.length > 0){
			errorCB(res);
		}
		resolve(res.response);
	} );
});

module.exports = askApi;
},{"../config":1}],5:[function(require,module,exports){

const cookies = {};

cookies.setCookie = (c_name, c_val, ex_time = 1, timeInSecond = false) => {
	let d = new Date();
	d.setTime( d.getTime() + ( timeInSecond ? ex_time * 1000 : ex_time * 24 * 60 * 60 * 1000 ) );
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
},{}],6:[function(require,module,exports){
const config = require('../config');

const dom = (tagName, opt = {}) => {
	let el = document.createElement(tagName);
	/**
	 * se passato content al posto di options
	 * se opotion è String | Element | Array
	*/
	if(typeof opt == 'string'){
		el.innerHTML = opt;
		return el;
	}else if(opt instanceof Element){
		el.append(opt);
		return el;
	}else if( opt instanceof Array){
		for (let i = 0; i < opt.length; i++) {
			el.append(opt[i]);
		}
		return el;
	}

	let options =  Object.assign( {}, opt, config.defaultDom );
	
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
			console.log(rows[i][j]);
			row.append(dom('td', rows[i][j]));
		}
		trows.push(row);
	}
	
	return dom('table',{
		content: trows
	});
}

module.exports = dom;
},{"../config":1}],7:[function(require,module,exports){

const str = {};

str.zerofill = (v) => {
	v = parseInt(v);
	return ( v >= 0 && v <= 9 ? '0' : '' ) + v;
};

str.firstUp = (v, forceLower = false) => v.slice(0,1).toUpperCase() + ( forceLower ? v.slice(1).toLowerCase() : v.slice(1) );

str.titled = (v, forceLower = false) => {
	let lastC = '';
	let newV = '';
	for (let i = 0; i < v.length; i++) {
		let c = v[i];
		if(c == '_'){
			c = ' ';
		}
		if( ( i == 0 || lastC == ' ' ) && c >= 'a' && c <= 'z' ) {
			c = c.toUpperCase();
		}else if( forceLower && ( i != 0 && lastC != ' ' ) && c >= 'A' && c <= 'Z'){
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
},{}]},{},[2]);
