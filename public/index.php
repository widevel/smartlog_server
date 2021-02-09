<?php
chdir(__DIR__ . '/..');

require_once 'includes/bootstrap.php';

use Widevel\SmartlogServer\ParseInbound;

new ParseInbound($_POST, ParseInbound::CLIENT_HTTP);
