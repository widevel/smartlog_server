<?php
chdir(__DIR__ . '/..');

require_once 'includes/bootstrap.php';

use Widevel\SmartlogServer\ParseInbound;

/*$serialized_data = new \stdclass;
$serialized_data->uniq_id = hash('sha256', mt_rand());
$serialized_data->instance_token = hash('sha256', mt_rand());
$serialized_data->message = "Hola mundo";
$serialized_data->level = 2;
$serialized_data->date = new \DateTime('now');
$serialized_data->data = (object) [
	'test' => 1234
];
$inbound_var = [
	'command' => 'log',
	'serialized_data' => base64_encode(gzdeflate(serialize($serialized_data), 9))
];

new ParseInbound($inbound_var, ParseInbound::CLIENT_HTTP);
*/


new ParseInbound($_POST, ParseInbound::CLIENT_HTTP);
