<?php

namespace Widevel\SmartlogServer;

use Widevel\SmartlogServer\Command\Log;
use Widevel\SmartlogServer\Command\UpdateInstance;
use MongoDB\Client;

class DBHandle {
	
	private $mongo_link;
	
	private $log_collection;
	private $instance_collection;
	private $session_collection;
	
	public function __construct(Client $mongo_link) {
		$this->mongo_link = $mongo_link;
		
		$this->log_collection = $this->mongo_link->smartlog->log;
		$this->instance_collection = $this->mongo_link->smartlog->instance;
		$this->session_collection = $this->mongo_link->smartlog->session;
	}
	
	public function insertLog(Log $log, CommandExec $CommandExec) {
		
		if($this->log_collection->findOne(['uniq_id' => $log->getUniqId()]) !== null) 
			return;
		
		try {
			
			$log->setMilliseconds((int) $log->getDate()->format('u'));
			
			$log->setDate(self::convertDate($log->getDate()));
			
			$this->log_collection->insertOne($log->getDbObject());
			
			if($log->getSessionToken() !== null && $this->session_collection->findOne(['session_token' => $log->getSessionToken()]) === null) 
				$this->session_collection->insertOne(['session_token' => $log->getSessionToken(), 'date' => $log->getDate()]);
			if($log->getInstanceToken() !== null && $this->instance_collection->findOne(['instance_token' => $log->getInstanceToken()]) === null) 
				$this->instance_collection->insertOne(['instance_token' => $log->getInstanceToken(), 'session_token' => $log->getSessionToken(), 'date' => $log->getDate()]);
			
		} catch (\Exception $e) {
			$CommandExec->setStatus(false);
			$CommandExec->setStatusMessage($e->getMessage());
			
		}
	}
	
	public function updateInstance(string $instance_token, $data, CommandExec $CommandExec) {
		
		try {
			$updateResult = $this->instance_collection->updateOne(
				['instance_token' => $instance_token],
				['$set' => ['data' => $data]]
			);
		} catch (\Exception $e) {
			$CommandExec->setStatus(false);
			$CommandExec->setStatusMessage($e->getMessage());
			
		}
	}
	
	public function setNewSessionToken(string $instance_token, string $new_session_token, CommandExec $CommandExec) {
		try {
			$updateResult = $this->log_collection->updateOne(
				['instance_token' => $instance_token],
				['$set' => ['session_token' => $new_session_token]]
			);
			
			$updateResult = $this->instance_collection->updateOne(
				['instance_token' => $instance_token],
				['$set' => ['session_token' => $new_session_token]]
			);
			
		} catch (\Exception $e) {
			$CommandExec->setStatus(false);
			$CommandExec->setStatusMessage($e->getMessage());
			
		}
	}
	
	private static function convertDate(\DateTime $date) {
		return new \MongoDB\BSON\UTCDateTime((int) ($date->format('U') . substr($date->format('u'), 0, 3)));
	}
}