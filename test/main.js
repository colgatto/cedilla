//CONFIG
ç.DEBUG = true;
ç.api.default.webhook = 'test/api.php';
ç.render.default.templates_dir = 'test/templates';

ç.dom.q('body').append( ç.dom('p', ç.str.titled('funGE_TUTTo', true) ) );

//se definisco una errorCallback globale per un determinato tipo di errore allora verrà triggerata su ogni route
// !!! nel caso in cui la funzione torni true allora non verrà scatenato il catch della route
// è utile per filtrare errori generali su ogni route evitando millemila catch

ç.api.errorCallback.route_invalid = (v) => { console.log(v); return true; }

/**/
ç.api('sfhdgjnfdgnmgh', {
	valA: 23,
	valB: '2',
	action: 'sum'
}).then( (response) => {
	ç.dom.q('#api_response').innerHTML = JSON.stringify(response, null, 4);
}).catch((e)=>{
	//di default ogni errore del backend triggera il catch di api
	console.log(e);
});
/**/

ç.api('queryTest', { danno: 15 }).then( res => {
	//console.log(res);
	ç.render('tabellaTest').with(res).on('body')
});

ç.api('testRegex26').then( res  => ç.dom.q('body').append(res) );
ç.api('testPriority').then( res => ç.dom.q('body').append(' ['+res+']') );
ç.api('main').then( res => console.log('test array: ' + res) );
ç.api('sub:test').then( res => console.log('test sub: ' + res) );

/*
ç.api('testInsert', { danno: 22 }).then( res => console.log(res) );
ç.api('customBD').then( res => ç.render('tabellaTest2').with(res).on('body') );
/**/