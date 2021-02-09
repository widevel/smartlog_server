<?php

namespace Widevel\SmartlogServer;

require_once 'debug_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

function getBootstrap() :Bootstrap {
	global $bootstrap;
	return $bootstrap;
}

$bootstrap = new Bootstrap;