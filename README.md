# Cedilla
Standalone backbone for webApp focused on single page application

```html
<script src="https://raw.githubusercontent.com/colgatto/cedilla/master/dist/cedilla.min.js"></script>
```

## Usage
fast way:
```js
let cells = ç.dom.q('.example td');
```
verbose way:
```js
let cells = cedilla.dom.q('.example td');
```
vanilla equivalent:
```js
let cells = document.querySelectorAll('.example td');
```

Like `$` and `jQuery` cedilla has 2 global variable `ç` and `cedilla`, both of this variables point on  `window.cedilla` so use what you want

```js

ç.dom('p', ç.str.titled('funGE_TUTTo', true) ):


ç.forAll('table.example td', (e) => {
	console.log(e);
});

```








## TODO

- parsare bene il body della api lato js
- migliorare la gestione errori
	- fare un set di funzioni che puoi passare dentro il catch `).catch( ç.api.error404 )`
	- OPPURE
	- fare una funzione generatrice `).catch( ç.api.catch( { CONFIG DELL' ERRORE } ) )`