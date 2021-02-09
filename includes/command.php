<?php

require_once __DIR__ . './bootstrap.php';

use Widevel\SmartlogServer\ParseInbound;

if(count($argv) < 3) die('insufficient arguments');

$inbound_array = [
	'command' => $argv[1],
	'serialized_data' => $argv[2]
];

new ParseInbound($inbound_array, ParseInbound::CLIENT_CMD);
