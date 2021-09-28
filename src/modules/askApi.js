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