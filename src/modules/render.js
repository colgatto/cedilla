class Render {

	constructor(templateName, template = null) {

		if(typeof window.Handlebars == 'undefined') throw new Error('Cedilla Render require Handlebars to work');

		this.templateName = templateName;
		this.template = template;
		this.f = null;
		this.templatePath = null;
		this.loading = new Promise( r => r());

		if(this.template) {
			this._compile();
			return;
		}

		let tDom = document.getElementById(templateName);

		if(tDom){
			this.templatePath = '#';
			this.template = tDom.innerHTML;
			this._compile();
			return;
		}
		this.templatePath = render.default.templates_dir + '/' + templateName + '.hbs';
		this.loading = fetch(this.templatePath, {
			mode: 'same-origin',
			credentials: 'same-origin'
		}).then( res => res.text() ).then((res) => {
			this.template = res;
			this._compile();
		});
	}

	_compile(){
		this.f = window.Handlebars.compile(this.template);
	}

	with(data){
		this.data = data;
		return this;
	}

	on(selector, overwrite = false){
		return new Promise( async (resolve) => {
			await this.loading;
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
};

render.load = (templateName, template) => {
	if(typeof _render_pool[templateName] == 'undefined') _render_pool[templateName] = new Render(templateName, template);
	return _render_pool[templateName];
};

render.default = {
	templates_dir: 'templates',
}

module.exports = render;
