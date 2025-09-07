<?php

$api->route('checkPassed')
->check('3UNDER30', function(){ return 3 < 30; })
->do(function($p){
	return 'done';
});

$api->route('checkNotPassed')
->check('3OVER30', function(){ return 3 > 30; })
->do(function($p){
	return 'done';
});

$api->route('/regex([0-9]+)/')
->do(function($p, $matches){
	$this->response->done('passato con ' . $matches[1]);
});

$api->route('priority')
->priority(4)
->do(function($p, $matches){
	return 'vince 1';
});

$api->route('priority')
->priority(6)
->do(function($p, $matches){
	return 'vince 2';
});

?>