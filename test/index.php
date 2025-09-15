<?php
	require_once __DIR__ . '/../dist/php/cedilla.php';
	use cedilla\Security;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Test</title>
	<script src="/test/lib/handlebars.4.7.8.min.js"></script>
	<script src="/dist/js/cedilla.js"></script>
	<style>

		main{
			max-width: 1440px;
			margin: 0 auto;
		}
		
		.response,
		.error{
			width: 100%;
			display: block;
			padding: 1rem;
			border-radius: .5rem;
			border: 1px solid;
		}
		.response {
			border-color: #0b5900;
			margin-bottom: .5rem;
		}
		.error{
			border-color: #510000;
		}
		code.response {
			background: #1bd70078;
		}
		code.error {
			background: #d7280078;
		}
	</style>
	<?php Security::CSRFTag(); ?>
	<script src="test/config.js"></script>
	<script src="test/testLib.js"></script>
</head>
<body>

	<main></main>

	<script id="tabellaTest" type="text/x-handlebars-template">
		<table border="1">
			<tr><th>name</th><th>surname</th><th>username</th><th>email</th><th>last_login</th></tr>
			{{#each this}} 
				<tr><td>{{name}}</td><td>{{surname}}</td><td>{{username}}</td><td>{{email}}</td><td>{{last_login}}</td></tr>
			{{/each}}
		</table>
	</script>

	<script src="test/main.js"></script>

</body>
</html>