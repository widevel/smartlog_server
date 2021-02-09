<?php

namespace Widevel\SmartlogServer;

use Widevel\SmartlogServer\Command\Log;
use Widevel\SmartlogServer\Command\UpdateInstance;
use Widevel\SmartlogServer\Command\UpdateSession;
use MongoDB\Client;

class DBHandle {
	
	private $mongo_link;
	
	public function __construct(Client $mongo_link) {
		$this->mongo_link = $mongo_link;
	}
	
	public function insertLog(Log $log, CommandExec $instance) {
		
		if($this->mongo_link->smartlog->log->findOne(['uniq_id' => $log->getUniqId()]) !== null) 
			return;
		
		try {
			
			$log->setMilliseconds((int) $log->getDate()->format('u'));
			
			$log->setDate(self::convertDate($log->getDate()));
			
			$this->mongo_link->smartlog->log->insertOne($log->getDbObject());
			
			if($log->getSessionToken() !== null && $this->mongo_link->smartlog->session->findOne(['hash' => $log->getSessionToken()]) === null) 
				$this->mongo_link->smartlog->session->insertOne(['hash' => $log->getSessionToken(), 'date' => $log->getDate()]);
			if($log->getInstanceToken() !== null && $this->mongo_link->smartlog->instance->findOne(['hash' => $log->getInstanceToken()]) === null) 
				$this->mongo_link->smartlog->instance->insertOne(['hash' => $log->getInstanceToken(), 'session' => $log->getSessionToken(), 'date' => $log->getDate()]);
			
		} catch (\Exception $e) {
			$instance->setStatus(false);
			$instance->setStatusMessage($e->getMessage());
			
		}
	}
	
	private static function convertDate(\DateTime $date) {
		return new \MongoDB\BSON\UTCDateTime((int) ($date->format('U') . substr($date->format('u'), 0, 3)));
	}
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
/*
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
}*/