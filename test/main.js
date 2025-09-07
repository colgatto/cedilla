/*
ç.api('testInsert', { danno: 22 }).then( res => console.log(res) );
ç.api('customBD').then( res => ç.render('tabellaTest2').with(res).on('body') );
/**/

testRoute('route:regex34', () => ç.api('route:regex34') );
testRoute('route:priority', () => ç.api('route:priority') );
testRoute('route:checkPassed', () => ç.api('route:checkPassed') );

test('db:select', (id) => {
	ç.api('db:select').then( res => {
		ç.render('tabellaTest').with(res).on(`#${id} .response`);
	}).catch((err)=>{
		ç.dom.q(`#${id} .error`).innerHTML = JSON.stringify(err, null, 4);
	});
});