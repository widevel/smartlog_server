<?php

namespace Widevel\SmartlogServer\Command;

use Widevel\DynamicGS\DynamicGS;

class UpdateInstance extends DynamicGS {
	
	/* string (required) */
	private $hash;
	
	/* string (optional) */
	private $session_token;
	
	/* object (optional) */
	private $data;

	/* \DateTime (required) */
	private $date;
	
	
}