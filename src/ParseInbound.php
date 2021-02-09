<?php

namespace Widevel\SmartlogServer;

class ParseInbound {
	
	const CLIENT_HTTP 	= 1;
	const CLIENT_CMD 	= 2;
	
	public function __construct(array $inbound_var, int $client_type) {
		if(array_key_exists('command', $inbound_var)) {
			$command = $inbound_var['command'];
			
			if(array_key_exists('command', $inbound_var)) {
				
				$serialized_data = (array_key_exists('serialized_data', $inbound_var) ? $inbound_var['serialized_data'] : null);
				
				$command_exec_instance = new CommandExec($inbound_var['command'], $serialized_data);
				
				if($command_exec_instance->getStatus()) $command_exec_instance->exec();
				
				if($client_type === self::CLIENT_HTTP ) {
					
					if(!headers_sent()) {
						header('Content-Type: application/json');
						http_response_code($command_exec_instance->getHttpRespondeCode());
					}
					
				}
				
				$json_response = new \stdclass;
				$json_response->response = $command_exec_instance->getHttpJsonRespondeCode();
				$json_response->message = $command_exec_instance->getStatusMessage();
				echo json_encode($json_response);
				
				
				
			} else if($client_type === self::CLIENT_HTTP ) {
				if(!headers_sent()) http_response_code(404);
				die('"command" property not found in inboud data');
			} else {
				echo '"command" property not found in inboud data';
			}
			
		}
	}
	
}