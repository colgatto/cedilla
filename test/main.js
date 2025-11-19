/*
ç.api('testInsert', { danno: 22 }).then( res => console.log(res) );
ç.api('customBD').then( res => ç.render('tabellaTest2').with(res).on('body') );
/**/

testRoute('route:regex34', () => ç.api('route:regex34') );
testRoute('route:priority', () => ç.api('route:priority') );
testRoute('route:checkPassed', () => ç.api('route:checkPassed') );
testRoute('route:exception', () => ç.api('route:exception') );
testRoute('route:error', () => ç.api('route:error') );
testRoute('route:debug', () => ç.api('route:debug') );

test('db:select', (id) => {
	ç.api('db:select').then( res => {
		ç.render('tabellaTest').with(res).on(`#${id} .response`);
	}).catch((err) => {
		ç.dom.q(`#${id} .error`).innerHTML = JSON.stringify(err, null, 4);
	});
});

test('db:stored', (id) => {
	ç.api('db:stored').then( res => {
		ç.render('tabellaTest').with(res).on(`#${id} .response`);
	}).catch((err) => {
		ç.dom.q(`#${id} .error`).innerHTML = JSON.stringify(err, null, 4);
	});
});

test('db:storedParams', (id) => {
	ç.api('db:storedParams').then( res => {
		ç.render('tabellaTest').with(res).on(`#${id} .response`);
	}).catch((err) => {
		ç.dom.q(`#${id} .error`).innerHTML = JSON.stringify(err, null, 4);
	});
});

test('db:storedList', (id) => {
	ç.api('db:storedList').then( res => {
		ç.render('tabellaTest').with(res).on(`#${id} .response`);
	}).catch((err) => {
		ç.dom.q(`#${id} .error`).innerHTML = JSON.stringify(err, null, 4);
	});
});