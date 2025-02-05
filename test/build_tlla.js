const fs = require('fs');
const path = require('path');

const PATH_TEMPLATILLA = path.join(__dirname, '..', '..', 'templatilla');

function rm_cp($source, $dest){
	fs.rmSync($dest, { recursive: true, force: true });
	fs.cpSync($source, $dest, {recursive: true});
}

rm_cp(
	path.join(__dirname, '..', 'dist', 'php', 'src'),
	path.join(PATH_TEMPLATILLA, 'be', 'lib', 'cedilla', 'src')
);
rm_cp(
	path.join(__dirname, '..', 'dist', 'php', 'cedilla.php'),
	path.join(PATH_TEMPLATILLA, 'be', 'lib', 'cedilla', 'cedilla.php')
);
rm_cp(
	path.join(__dirname, '..', 'dist', 'js', 'cedilla.js'),
	path.join(PATH_TEMPLATILLA, 'lib', 'templatilla', 'js', 'cedilla.js')
);
rm_cp(
	path.join(__dirname, '..', 'dist', 'js', 'cedilla.min.js'),
	path.join(PATH_TEMPLATILLA, 'lib', 'templatilla', 'js', 'cedilla.min.js')
);