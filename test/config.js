ç.DEBUG = true;
ç.api.default.webhook = 'test/api.php';
ç.render.default.templates_dir = 'test/templates';

//se definisco una errorCallback globale per un determinato tipo di errore allora verrà triggerata su ogni route
// !!! nel caso in cui la funzione torni true allora non verrà scatenato il catch della route
// è utile per filtrare errori generali su ogni route evitando millemila catch
ç.api.errorCallback.route_invalid = (err) => {
	console.log(err.message);
	return true;
};