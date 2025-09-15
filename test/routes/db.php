<?php

$api->route('select')
->db(true)
->do(function($p){
	$res = $this->db->exec('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
	$this->response->done($res);
});

$api->route('stored')
->db(true)
->do(function($p){
	$res = $this->db->stored('test_stored')->fetchAll(PDO::FETCH_ASSOC);
	$this->response->done($res);
});

$api->route('storedParams')
->db(true)
->do(function($p){
	$res = $this->db->stored('test_stored_params', [
		//NB in mysql conta solo l'ordine dei paramentri, il nome può essere messo a caso
		'from' => 1,
		'to' => 2,
	])->fetchAll(PDO::FETCH_ASSOC);
	$this->response->done($res);
});

$api->route('storedList')
->db(true)
->do(function($p){
	$res = $this->db->stored('test_stored_params', [2, 2])->fetchAll(PDO::FETCH_ASSOC);
	$this->response->done($res);
});


?>