# Cedilla
Standalone backbone for webApp focused on single page application

# Frontend JS

```html
<script src="https://raw.githubusercontent.com/colgatto/cedilla/master/dist/cedilla.min.js"></script>
```

### Basic Usage

```js
let cells = ç.dom.q('.example td'); //FAST
//Equivalent to
let cells = cedilla.dom.q('.example td'); //VERBOSE
//Equivalent to
let cells = document.querySelectorAll('.example td'); //VANILLA JS
```

> Like `$` and `jQuery` cedilla has 2 global variable `ç` and `cedilla`,
> both of this variables point on  `window.cedilla` so use what you want

## Modules

- api
- dom
- cookie
- str
- arr

```js

ç.dom('p', ç.str.titled('funGE_TUTTo', true) ):


ç.forAll('table.example td', (e) => {
	console.log(e);
});

```

# Backend PHP


## init

### `Api([options])` 

```php
require_once __DIR__ . '/cedilla.php';

use cedilla\Api;

$api = new Api([
	'db' => [
		'database' => 'dadomaster'
	]
]);

```
The following table describes the properties of the options object.
| Property | Description | Type | Default |
| - | - | - | - |
| db | Database infos | Object |  |

The following table describes the properties of the `db` object.

| Property | Description | Type | Default |
| - | - | - | - |
| database | Database name | String | `<empty>` |
| user | Database username | String | `"root"` |
| pass | Database password | String | `<empty>` |
| host | Database host | String | `"127.0.0.1"` |
| port | Database port | Int | Based on type |
| type | Database type | Enum | DB::DB_MYSQL |
| dsn | dsn params, used if `type == DB::DB_MYSQL` | String |  |

valid `type` and default `port`
| Type | Port |
| - | - |
| `DB::DB_MYSQL` | 3306 |
| `DB::DB_OCI` | 1521 |
| `DB::DB_POSTGRESS` | 5432 |
| !`DB::DB_MSSQL` | TODO |










## TODO

- migliorare la gestione errori
	- fare un set di funzioni che puoi passare dentro il catch `).catch( ç.api.error404 )`
	- OPPURE
	- fare una funzione generatrice `).catch( ç.api.catch( { CONFIG DELL' ERRORE } ) )`