let testRouteIndex = 0;

const testRoute = (name, cb) => {
	const id = `testBox${testRouteIndex++}`;
	ç.dom.q('main').innerHTML += `<div id="${id}">
		<h3>${name}</h3>
		<code class="response"></code>
		<code class="error"></code>
		<hr>
	</div>`;
	cb().then(res => {
		ç.dom.q(`#${id} .response`).innerHTML = JSON.stringify(res, null, 4);
	}).catch((err)=>{
		ç.dom.q(`#${id} .error`).innerHTML = JSON.stringify(err, null, 4);
	});
};

const test = (name, cb) => {
	const id = `testBox${testRouteIndex++}`;
	ç.dom.q('main').innerHTML += `<div id="${id}">
		<h3>${name}</h3>
		<div class="response"></div>
		<div class="error"></div>
		<hr>
	</div>`;
	cb(id);
	/*.then(res => {
		ç.dom.q(`#${id} .response`).innerHTML = JSON.stringify(res, null, 4);
	}).catch((err)=>{
		ç.dom.q(`#${id} .error`).innerHTML = JSON.stringify(err, null, 4);
	});
	*/
};