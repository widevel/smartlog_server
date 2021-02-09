<?php

namespace Widevel\SmartlogServer;

use MongoDB\Client as MongoClient;

class DbConnect {
	
	private $connection;
	
	public function __construct() {
		$this->connection = new MongoClient;
	}
	
	public function getConnection() :MongoClient { return $this->connection; }
}