
const Handlebars = require("handlebars");
const sleep = async n => new Promise( r => setTimeout(r, n));

class Render {
	
	constructor(templateName) {
		this.templateName = templateName;
		this.templatePath = render.default.templates_dir + '/' + templateName + '.hbs';
		this.template = null;
		this.f = null;
		fetch(this.templatePath, {
			method: 'POST',
			mode: 'same-origin',
			credentials: 'same-origin'
		}).then( res => res.text() ).then((res) => this.f = Handlebars.compile(res));
	}

	with(data){
		this.data = data;
		return this;
	}

	on(selector, overwrite = false){
		return new Promise( async (resolve) => {
			while(this.f === null) await sleep(100);
			const rendered = this.f(this.data);
			document.querySelectorAll(selector).forEach( el => {
				if(overwrite) el.innerHTML = rendered;
				else el.innerHTML += rendered;
			});
			resolve();
		});
	}
}

const _render_pool = {};

const render = (templateName) => {
	if(typeof _render_pool[templateName] == 'undefined') _render_pool[templateName] = new Render(templateName);
	return _render_pool[templateName];
}

render.default = {
	templates_dir: 'templates',
}

module.exports = render;