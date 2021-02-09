<?php

namespace Widevel\SmartlogServer;

class Bootstrap {
	
	private $db_connect;
	private $db_handle;
	
	public function __construct() {
		$this->db_connect = new DbConnect;
		$this->db_handle = new DBHandle($this->db_connect->getConnection());
	}
	
	public function getDbHandle() :DBHandle { return $this->db_handle; }
}