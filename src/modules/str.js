
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