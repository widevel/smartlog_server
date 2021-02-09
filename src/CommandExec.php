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
			
			getBootstrap()->getDbHandle()->insertLog($logObject, $this);
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

/*
PREDEFINED FIELDS:
	(array) tags
	(int) level
	(string) tracking
	(string) session
	(string) instance
	(string) message
	(int) timestamp
	(int) milliseconds
	(object) data
	attachments: array(
		object: {
			(string) name : "attachment_name",
			(string) data : "attachment_binary_data",
			(bool) base64 : "decode data base64",
			(bool) gz : "decode data gz",
		}
	)
*/

function pushToMongo(stdclass $data) {
	
	global $log_collection, $session_collection, $instance_collection;
	
	if(property_exists($data, 'attachments')) {
		$attachments = $data->attachments;
		
		if(is_array($attachments)) {
			foreach($attachments as $index => $attachment) {
				$attachments[$index] = saveAttachment($attachment);
			}
		}
	}
	
	$data->date = new \MongoDB\BSON\UTCDateTime($data->timestamp);
	
	unset($data->timestamp);
	
	$data->unique = hash('sha256', serialize($data));
	
	if($log_collection->findOne(['unique' => $data->unique]) !== null) return;
	
	$insertOneResult = $log_collection->insertOne($data);
	
	if($data->session !== null && $session_collection->findOne(['hash' => $data->session]) === null) $session_collection->insertOne(['hash' => $data->session, 'date' => $data->date]);
	if($data->instance !== null && $instance_collection->findOne(['hash' => $data->instance]) === null) $instance_collection->insertOne(['hash' => $data->instance, 'session' => $data->session, 'date' => $data->date]);
	
}

function saveAttachment(stdclass $attachment) {
	if(property_exists($attachment, 'base64') && $attachment->base64 === true) {
		$attachment->data = base64_decode($attachment->data);
	}
	
	if(property_exists($attachment, 'gz') && $attachment->gz === true) {
		$attachment->data = gzinflate($attachment->data);
	}
	
	file_put_contents('attachments/' . $attachment->name, $attachment->data);
	
	unset($attachment->data);
	
	return $attachment;
}