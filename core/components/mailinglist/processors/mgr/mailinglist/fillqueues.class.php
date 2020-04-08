<?php

class MailingListFillQueuesProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		
		$start_time = explode(" ",microtime());
		$start_time = $start_time[1];
		$max_exec = 30;
		$limit = 50;
        $offset = $_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset'];
		$current_type = $_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_type'];
		if(empty($offset))$offset=0;
		if(empty($current_type))$current_type='clear';
		//$this->setToLog($scriptProperties);
		//$this->setToLog($current_type);
		//$this->setToLog($offset);
		
		$Result=array();
		if(!empty($scriptProperties['mailinglist'])&&!empty($scriptProperties['instance']))
		{
			if($current_type=='clear')
			{
				$this->modx->cacheManager->clearCache(array("db"=>array()));
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
				while($exec_time < ($max_exec - 5))
				{
					$QueueCriteria = $this->modx->newQuery('MailingListQueue',null,false);
					$QueueCriteria->where(array(
						'instance'=>(int)$scriptProperties['instance'],
						'status'=>'expected',
					));
					$QueueCriteria->limit($limit);
					$InstanceQueues = $this->modx->getCollection('MailingListQueue',$QueueCriteria,false);
					if(empty($InstanceQueues))
					{
						$current_type = 'group';
						$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_type'] = 'group';
						$offset=0;
						$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset'] = 0;
						$this->modx->cacheManager->clearCache(array("db"=>array()));
						break;
					}
					else
					{
						foreach($InstanceQueues as $Queue)
						{
							$now_time = explode(" ",microtime());
							$exec_time = $now_time[1]-$start_time;
							if(($exec_time+5) > $max_exec)
							{
								$Result['proccess'] = true;
								return $this->outputArray($Result);
							}
							$Queue->remove();
							$this->modx->cacheManager->clearCache(array("db"=> array("MailingListQueue"=>array())));
							$offset ++;
							$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset']++;
						}
					}
					$now_time = explode(" ",microtime());
					$exec_time = $now_time[1]-$start_time;
				}
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
				if(($exec_time+5) > $max_exec)
				{
					$Result['proccess'] = true;
					return $this->outputArray($Result);
				}
			}
			if($current_type=='group')
			{
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
				while($exec_time < ($max_exec - 5))
				{
					$Subscribers = $this->getSubscribers($scriptProperties,$limit,$offset,$current_type);
					if(empty($Subscribers))
					{
						$current_type = 'user';
						$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_type'] = 'user';
						$offset=0;
						$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset'] = 0;
						break;
					}
					else
					{
						foreach($Subscribers as $Subscriber)
						{
							$now_time = explode(" ",microtime());
							$exec_time = $now_time[1]-$start_time;
							if(($exec_time+5) > $max_exec)
							{
								$Result['proccess'] = true;
								return $this->outputArray($Result);
							}
							$Group = $Subscriber->getOne('Group');
							if($Group)
							{
								$group_exclude = json_decode($Subscriber->get('exclude'),true);
								$group_offset = $_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_group_offset'];
								if(empty($group_offset))$group_offset=0;
								$now_time = explode(" ",microtime());
								$exec_time = $now_time[1]-$start_time;
								while($exec_time < ($max_exec - 5))
								{
									$group_criteria = $this->modx->newQuery('modUserGroupMember');
									$group_criteria->where(array(
										'user_group'=>$Group->id,
									));
									$group_criteria->limit($limit,$group_offset);
									$Members = $this->modx->getCollection('modUserGroupMember',$group_criteria);
									if(empty($Members))
									{
										$group_offset=0;
										$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_group_offset']=null;
										break;
									}
									else
									{
										foreach($Members as $Member)
										{
											$now_time = explode(" ",microtime());
											$exec_time = $now_time[1]-$start_time;
											if(($exec_time+5) > $max_exec)
											{
												$Result['proccess'] = true;
												return $this->outputArray($Result);
											}
											if(!in_array('user_'.$Member->get('member'),$group_exclude))
											{
												$User = $Member->getOne('User');
												if($User)
												{
													$UserGroups = $User->getMany('UserGroupMembers');
													$ExcludeByGroup = false;
													foreach($UserGroups as $UserGroup)
													{
														if(in_array('group_'.$UserGroup->get('user_group'),$group_exclude))
														{
															$ExcludeByGroup = true;
															break;
														}
													}
													if(!$ExcludeByGroup)
													{
														$Profile = $User->getOne('Profile');
														if(!empty($Profile->get('email')))
														{
															$isExists = $this->modx->getObject('MailingListQueue',array('email'=>$Profile->get('email'),'instance'=>$scriptProperties['instance'],'mailinglist'=>$scriptProperties['mailinglist']));
															if(!$isExists)
															{
																$newQueue = $this->modx->newObject('MailingListQueue');
																$newQueue->set('mailinglist',(int)$scriptProperties['mailinglist']);
																$newQueue->set('instance',(int)$scriptProperties['instance']);
																$newQueue->set('status','expected');
																$newQueue->set('email',$Profile->get('email'));
																$newQueue->set('type',$current_type);
																$newQueue->set('subscriber',$Subscriber->id);
																$newQueue->set('hash',$Subscriber->get('hash').'_'.$User->get('salt'));
																$newQueue->save();
															}
														}
													}
												}
											}
											$group_offset++;
											$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_group_offset']++;
										}
									}
									$now_time = explode(" ",microtime());
									$exec_time = $now_time[1]-$start_time;
								}
								$now_time = explode(" ",microtime());
								$exec_time = $now_time[1]-$start_time;
								if(($exec_time+5) > $max_exec)
								{
									$Result['proccess'] = true;
									return $this->outputArray($Result);
								}
							}
							$offset++;
							$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset']++;
						}
					}
					$now_time = explode(" ",microtime());
					$exec_time = $now_time[1]-$start_time;
				}
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
				if(($exec_time+5) > $max_exec)
				{
					$Result['proccess'] = true;
					return $this->outputArray($Result);
				}
			}
			if($current_type=='user')
			{
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
				while($exec_time < ($max_exec - 5))
				{
					$Subscribers = $this->getSubscribers($scriptProperties,$limit,$offset,$current_type);
					if(empty($Subscribers))
					{
						
						$current_type = 'anonym';
						$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_type'] = 'anonym';
						$offset=0;
						$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset'] = 0;
						break;
					}
					else
					{
						foreach($Subscribers as $Subscriber)
						{
							$now_time = explode(" ",microtime());
							$exec_time = $now_time[1]-$start_time;
							if(($exec_time+5) > $max_exec)
							{
								$Result['proccess'] = true;
								return $this->outputArray($Result);
							}
							$User = $Subscriber->getOne('User');
							if($User)
							{
								$Profile = $User->getOne('Profile');
								if(!empty($Profile->get('email')))
								{
									$isExists = $this->modx->getObject('MailingListQueue',array('email'=>$Profile->get('email'),'instance'=>$scriptProperties['instance'],'mailinglist'=>$scriptProperties['mailinglist']));
									if(!$isExists)
									{
										$newQueue = $this->modx->newObject('MailingListQueue');
										$newQueue->set('mailinglist',(int)$scriptProperties['mailinglist']);
										$newQueue->set('instance',(int)$scriptProperties['instance']);
										$newQueue->set('status','expected');
										$newQueue->set('email',$Profile->get('email'));
										$newQueue->set('type',$current_type);
										$newQueue->set('subscriber',$Subscriber->id);
										$newQueue->set('hash',$Subscriber->get('hash'));
										$newQueue->save();
									}
								}
							}
							$offset ++;
							$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset']++;
						}
					}
					$now_time = explode(" ",microtime());
					$exec_time = $now_time[1]-$start_time;
				}
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
				if(($exec_time+5) > $max_exec)
				{
					$Result['proccess'] = true;
					return $this->outputArray($Result);
				}
			}
			if($current_type=='anonym')
			{
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
				while($exec_time < ($max_exec - 5))
				{
					$Subscribers = $this->getSubscribers($scriptProperties,$limit,$offset,$current_type);
					if(empty($Subscribers))
					{
						$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_type'] = null;
						$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset'] = null;
						$this->modx->cacheManager->clearCache(array("db"=>array()));
						$Result['proccess'] = false;
						return $this->outputArray($Result);
					}
					else
					{
						foreach($Subscribers as $Subscriber)
						{
							$now_time = explode(" ",microtime());
							$exec_time = $now_time[1]-$start_time;
							if(($exec_time+5) > $max_exec)
							{
								$Result['proccess'] = true;
								return $this->outputArray($Result);
							}
							$fields = json_decode($Subscriber->get('fields'),true);
							if(!empty($fields['email']))
							{
								$isExists = $this->modx->getObject('MailingListQueue',array('email'=>$fields['email'],'instance'=>$scriptProperties['instance'],'mailinglist'=>$scriptProperties['mailinglist']));
								if(!$isExists)
								{
									$newQueue = $this->modx->newObject('MailingListQueue');
									$newQueue->set('mailinglist',(int)$scriptProperties['mailinglist']);
									$newQueue->set('instance',(int)$scriptProperties['instance']);
									$newQueue->set('status','expected');
									$newQueue->set('email',$fields['email']);
									$newQueue->set('type',$current_type);
									$newQueue->set('subscriber',$Subscriber->id);
									$newQueue->set('hash',$Subscriber->get('hash'));
									$newQueue->save();
								}
							}
							$offset ++;
							$_SESSION["fillqueue_".$scriptProperties['mailinglist'].$scriptProperties['instance'].'_offset']++;
						}
					}
					$now_time = explode(" ",microtime());
					$exec_time = $now_time[1]-$start_time;
				}
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
				if(($exec_time+5) > $max_exec)
				{
					$Result['proccess'] = true;
					return $this->outputArray($Result);
				}
			}
		}
		
		return $this->outputArray($Result);
    }
	
	public function getSubscribers($scriptProperties,$limit,$offset,$type)
	{
		$criteria = $this->modx->newQuery('MailingListSubscribers');
		$criteria->where(array(
			'type'=>$type,
			'mailinglist'=>(int)$scriptProperties['mailinglist']
		));
		$criteria->limit($limit,$offset);
		return $this->modx->getCollection('MailingListSubscribers',$criteria);
	}
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListFillQueuesProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListFillQueuesProcessor';