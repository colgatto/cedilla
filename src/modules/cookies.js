
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