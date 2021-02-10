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
	
	public function updateInstance(string $instance_token, string $session_token = null, $data = null, $date, CommandExec $CommandExec) {
		
		try {
			if($this->instance_collection->findOne(['instance_token' => $instance_token]) === null) {
				$date = get_class($date) == \DateTime::class ? self::convertDate($date) : $date;
				$this->instance_collection->insertOne(['instance_token' => $instance_token, 'session_token' => $session_token, 'date' => $date]);
			} else {
				$updateResult = $this->instance_collection->updateOne(
					['instance_token' => $instance_token],
					['$set' => ['data' => $data]]
				);
			}
			
			
		} catch (\Exception $e) {
			$CommandExec->setStatus(false);
			$CommandExec->setStatusMessage($e->getMessage());
			
		}
	}
	
	public function setNewInstanceToken(string $old_instance_token, string $new_instance_token, CommandExec $CommandExec) {
		try {
			
			$instance_row = $this->instance_collection->findOne(['instance_token' => $new_instance_token]);
			
			if($instance_row !== null) {
				$old_instance_row = $this->instance_collection->findOne(['instance_token' => $old_instance_token]);
				$this->instance_collection->deleteOne(['instance_token' => $old_instance_token]);
				$this->updateInstance($new_instance_token, $instance_row->session_token, $old_instance_row->data, $instance_row->date, $CommandExec);
			} else {
				$updateResult = $this->instance_collection->updateOne(
					['instance_token' => $old_instance_token],
					['$set' => ['instance_token' => $new_instance_token]]
				);
			}
			
			$updateResult = $this->log_collection->updateOne(
				['instance_token' => $old_instance_token],
				['$set' => ['instance_token' => $new_instance_token]]
			);
			
			
			
		} catch (\Exception $e) {
			$CommandExec->setStatus(false);
			$CommandExec->setStatusMessage($e->getMessage());
			
		}
	}
	
	public function setNewSessionToken(string $instance_token, string $new_session_token, \DateTime $date, CommandExec $CommandExec) {
		try {
			
			if($this->session_collection->findOne(['session_token' => $new_session_token]) === null) 
				$this->session_collection->insertOne(['session_token' => $new_session_token, 'date' => self::convertDate($date)]);
			
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