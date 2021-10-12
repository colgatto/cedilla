# cedilla

## TODO

- parsare bene il body della askApi lato js
- `cedilla.php` riscrivere tutti i metodi statici `con_questo_pattern` e i metodi non statici `conQuestAltro` X tutte le Classi
- migliorare la gestione errori
	- fare un set di funzioni che puoi passare dentro il catch `).catch( ç.askApi.error404 )`
	- OPPURE
	- fare una funzione generatrice `).catch( ç.askApi.catch( { CONFIG DELL' ERRORE } ) )`