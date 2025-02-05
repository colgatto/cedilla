<?php
	require_once __DIR__ . '/../dist/php/cedilla.php';
	use cedilla\Security;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<script src="/test/lib/handlebars.4.7.8.min.js"></script>
	<script src="/dist/js/cedilla.js"></script>
	<title>Test</title>
</head>
<body>
	<p>WE!</p>
	<script id="tabellaTest" type="text/x-handlebars-template">
		<table border="1">
			<tr><th>name</th><th>surname</th><th>username</th><th>email</th><th>last_login</th></tr>
			{{#each this}} 
				<tr><td>{{name}}</td><td>{{surname}}</td><td>{{username}}</td><td>{{email}}</td><td>{{last_login}}</td></tr>
			{{/each}}
		</table>
	</script>
	<code id="api_response"></code>
	<?php Security::CSRFTag(); ?>
	<script src="test/main.js"></script>
</body>
</html>