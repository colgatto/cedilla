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