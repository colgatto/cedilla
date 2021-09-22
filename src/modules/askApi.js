const config = require('../config');

const askApi = (action, data = {}) => new Promise( ( resolve, reject ) => {
    let args =  Object.assign( {}, data, { '_action': action } );
    return fetch(config.apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        mode: 'same-origin',
        credentials: 'same-origin', 
        body: Object.keys( args ).map( k => k + '=' + args[k] ).join('&')
    }).then( res => res.json() ).then( res => {
        if(res.error){
            alert(res.message);
            reject(res.message);
        }
        resolve(res.response);
    } );
});

module.exports = askApi;