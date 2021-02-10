<?php

namespace Widevel\SmartlogServer;

class CommandExec {
	
	private $command_name, $data;
	
	private $status = true, $status_message;
	
	public function __construct(string $command_name, string $serialized_data = null) {
		
		$this->command_name = $command_name;
		
		$serialized_data = $serialized_data === null ? false : gzinflate(base64_decode($serialized_data));
		
		if($serialized_data === false) $this->setResponse(false, 'Unable to decode gzipped input data');
		
		$unserialized_data = unserialize($serialized_data);
		
		if(!is_object($unserialized_data)) $this->setResponse(false, 'Unable to decode serialized input data');
		
		$this->data = $unserialized_data;
	}
	
	public function exec() {
		
		
		
		if($this->command_name == 'log') {
			if(!property_exists($this->data, 'uniq_id')) return $this->setResponse(false, 'Field "uniq_id" is required');
			
			if(!property_exists($this->data, 'instance_token')) return $this->setResponse(false, 'Field "instance_token" is required');
			
			if(!property_exists($this->data, 'message')) return $this->setResponse(false, 'Field "message" is required');
			
			if(!property_exists($this->data, 'level')) return $this->setResponse(false, 'Field "level" is required');
			
			if(!property_exists($this->data, 'date')) return $this->setResponse(false, 'Field "date" is required');
			
			$logObject = new \Widevel\SmartlogServer\Command\Log;
			
			$logObject->setUniqId($this->data->uniq_id);
			$logObject->setInstanceToken($this->data->instance_token);
			$logObject->setMessage($this->data->message);
			$logObject->setLevel($this->data->level);
			$logObject->setDate($this->data->date);
			if(property_exists($this->data, 'data')) $logObject->setData($this->data->data);
			if(property_exists($this->data, 'session_token')) $logObject->setSessionToken($this->data->session_token);
			if(property_exists($this->data, 'name')) $logObject->setName($this->data->name);
			if(property_exists($this->data, 'tags')) $logObject->setTags($this->data->tags);
			
			getBootstrap()->getDbHandle()->insertLog($logObject, $this);
		}
		
		if($this->command_name == 'update_instance_data') {
			if(!property_exists($this->data, 'instance_token')) return $this->setResponse(false, 'Field "instance_token" is required');
			if(!property_exists($this->data, 'session_token')) return $this->setResponse(false, 'Field "session_token" is required');
			if(!property_exists($this->data, 'data')) return $this->setResponse(false, 'Field "data" is required');
			if(!property_exists($this->data, 'date')) return $this->setResponse(false, 'Field "date" is required');
			
			getBootstrap()->getDbHandle()->updateInstance($this->data->instance_token, $this->data->session_token, $this->data->data, $this->data->date, $this);
			
		}
		
		if($this->command_name == 'set_new_instance_token') {
			if(!property_exists($this->data, 'old_instance_token')) return $this->setResponse(false, 'Field "old_instance_token" is required');
			if(!property_exists($this->data, 'new_instance_token')) return $this->setResponse(false, 'Field "new_instance_token" is required');
			
			getBootstrap()->getDbHandle()->setNewInstanceToken($this->data->old_instance_token, $this->data->new_instance_token, $this);
		}
		
		if($this->command_name == 'set_new_session_token') {
			if(!property_exists($this->data, 'instance_token')) return $this->setResponse(false, 'Field "instance_token" is required');
			if(!property_exists($this->data, 'new_session_token')) return $this->setResponse(false, 'Field "new_session_token" is required');
			if(!property_exists($this->data, 'date')) return $this->setResponse(false, 'Field "date" is required');
			
			getBootstrap()->getDbHandle()->setNewSessionToken($this->data->instance_token, $this->data->new_session_token, $this->data->date, $this);
		}
	}
	
	public function setResponse(bool $status, string $message) {
		$this->status = $status;
		$this->status_message = $message;
	}
	
	public function getStatus() :bool { return $this->status; }
	
	public function setStatus(bool $status) :CommandExec { $this->status = $status; return $this; }
	
	public function getStatusMessage() :?string { return $this->status_message; }
	
	public function setStatusMessage(string $status_message) :CommandExec { $this->status_message = $status_message; return $this; }
	
	public function getHttpRespondeCode() :int { return $this->status ? 200 : 500; }
	
	public function getHttpJsonRespondeCode() :string { return $this->status ? 'OK' : 'KO'; }
}
