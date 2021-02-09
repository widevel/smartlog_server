<?php

namespace Widevel\SmartlogServer\Command;

use Widevel\DynamicGS\DynamicGS;

class Log extends DynamicGS {
	
	/* string (required) */
	protected $uniq_id;
	
	/* string (required) */
	protected $instance_token;
	
	/* string (optional) */
	protected $session_token;
	
	/* string (required) */
	protected $message;
	
	/* string (optional) */
	protected $name;
	
	/* object (optional) */
	protected $data;
	
	/* int (required) */
	protected $level;
	
	/* array (optional) */
	protected $tags = [];
	
	/* (required) */
	public $date;
	
	/* int (required) */
	protected $milliseconds;
	
	public function getDbObject() { return get_object_vars($this); }
}