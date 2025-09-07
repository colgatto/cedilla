<?php

$api->route('select')
->db(true)
->do(function($p){
	$res = $this->db->exec('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
	$this->response->done($res);
});


?>