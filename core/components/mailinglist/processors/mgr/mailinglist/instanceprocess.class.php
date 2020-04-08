<?php

class MailingListInstanceProcessProcessor extends modProcessor
{
	public $Tasker;
	public $oInstance;
	public $oMailingList;
	public $oMailTemplate;
	public $mailSettings;
	public $mail;
	public $Parser;
	
    public function process()
	{
        $scriptProperties = $this->getProperties();
		//$this->setToLog($scriptProperties);
		$start_time = explode(" ",microtime());
		$start_time = $start_time[1];
		$max_exec = ini_get('max_execution_time');
		$TaskerService = $this->modx->getOption('mailinglist.tasker.service',null,null);
		$TaskerModelPath = $this->modx->getOption(strtolower($TaskerService).'_core_path',null,$this->modx->getOption('core_path').'components/'.strtolower($TaskerService).'/').'model/'.strtolower($TaskerService).'/';
		$this->Tasker = $this->modx->getService(strtolower($TaskerService),$TaskerService,$TaskerModelPath);
		$this->mail = $this->modx->getService('mail', 'mail.modPHPMailer');
		$this->Parser = $this->modx->getService('parser', $this->modx->getOption('parser_class', null, 'modParser'), $this->modx->getOption('parser_class_path', null, ''));
		$this->oMailingList = $this->modx->getObject('MailingList',(int)$scriptProperties['mailinglist']);
		$this->oMailTemplate = $this->oMailingList->getOne('Template');
		$this->modx->resource = $this->oMailingList;
		$this->mailSettings = $this->oMailingList->getOne('Settings');
		$this->mailSettings = $this->mailSettings->toArray();
		if(empty($this->mailSettings['emailsubject']))
		{
			$this->mailSettings['emailsubject'] = $this->modx->getOption('mailinglist_subject',null,$this->modx->getOption('site_name'));
		}
		if(empty($this->mailSettings['emailfrom']))
		{
			$this->mailSettings['emailfrom'] = $this->modx->getOption('mailinglist_emailfrom',null,$this->modx->getOption('emailsender'));
		}
		if(empty($this->mailSettings['emailfromname']))
		{
			$this->mailSettings['emailfromname'] = $this->modx->getOption('mailinglist_emailfromname',null,$this->modx->getOption('site_name'));
		}
		if(empty($this->mailSettings['emailreplyto']))
		{
			$this->mailSettings['emailreplyto'] = $this->modx->getOption('mailinglist_emailreplyto',null,$this->modx->getOption('emailsender'));
		}
		if(empty($this->mailSettings['emailreplytoname']))
		{
			$this->mailSettings['emailreplytoname'] = $this->modx->getOption('mailinglist_emailreplytoname',null,$this->modx->getOption('site_name'));
		}
		
		$Result=array();
		if(!empty($scriptProperties['instance']))
		{
			$this->oInstance = $this->modx->getObject('MailingListInstance',(int)$scriptProperties['instance'],false);
			if($this->oInstance->get('status')!='process')
			{
				return $this->endProcess();
			}
			$now_time = explode(" ",microtime());
			$exec_time = $now_time[1]-$start_time;
			while($exec_time < ($max_exec - 5))
			{
				$QueueCriteria = $this->modx->newQuery('MailingListQueue');
				$QueueCriteria->where(array(
					'instance'=>(int)$scriptProperties['instance'],
					'mailinglist'=>(int)$scriptProperties['mailinglist'],
					'status'=>'expected',
				));
				$QueueCriteria->limit(50);
				$InstanceQueues = $this->modx->getCollection('MailingListQueue',$QueueCriteria);
				if(empty($InstanceQueues))
				{
					return $this->completeProcess();
				}
				else
				{
					foreach($InstanceQueues as $Queue)
					{
						$this->oInstance = $this->modx->getObject('MailingListInstance',(int)$scriptProperties['instance'],false);
						if($this->oInstance->get('status')!='process')
						{
							return $this->endProcess();
						}
						$now_time = explode(" ",microtime());
						$exec_time = $now_time[1]-$start_time;
						if(($exec_time+5) > $max_exec)
						{
							return $this->continueProcess();
						}
						$status = $this->sendMail($Queue);
						$Queue->set('status',$status);
						$Queue->save();
					}
				}
				$now_time = explode(" ",microtime());
				$exec_time = $now_time[1]-$start_time;
			}
			$now_time = explode(" ",microtime());
			$exec_time = $now_time[1]-$start_time;
			if(($exec_time+5) > $max_exec)
			{
				return $this->continueProcess();
			}
		}
		
		return $this->outputArray($Result);
    }
	
	public function continueProcess()
	{
		$TaskCommandParams = $_REQUEST;
		$TaskCommand = $this->Tasker->construct_command('assets/components/mailinglist/cron_connector.php',$TaskCommandParams);
		$TaskParams = array
		(
			'command'=>$TaskCommand,
			'time'=>time()+240
		);
		if($TaskID = $this->Tasker->add($TaskParams))
		{
			$this->oInstance->set('task',$TaskID);
			$this->oInstance->save();
		}
		return $this->outputArray(array('proccess' => true));
	}
	
	public function completeProcess()
	{
		$this->oInstance->set('status','completed');
		$this->oInstance->set('end_date',time());
		$this->oInstance->save();
		return $this->endProcess();
	}
	
	public function endProcess()
	{
		return $this->outputArray(array('proccess' => false));
	}
	
	public function sendMail($Queue)
	{
		$this->setMailSettings();
		
		$placeholders = $this->makePlaceholders($Queue);
		
		$this->oMailTemplate->_cacheable = false;
		$this->oMailTemplate->_processed = false;
		$this->oMailTemplate->_output = '';
		$body = $this->oMailTemplate->process($placeholders);

		if ($this->Parser && $this->Parser instanceof modParser)
		{
			$maxIterations = (integer) $this->modx->getOption('parser_max_iterations', null, 10);
			$this->Parser->processElementTags('', $body, true, true, '[[', ']]', array(), $maxIterations);
		}
		/*$this->oMailingList->_cacheable = false;
		$this->oMailingList->_processed = false;
		$this->modx->setPlaceholders($placeholders);
		$content = $this->oMailingList->process();
		if ($this->Parser && $this->Parser instanceof modParser)
		{
			$maxIterations = (integer) $this->modx->getOption('parser_max_iterations', null, 10);
			$this->Parser->processElementTags('', $content, true, true, '[[', ']]', array(), $maxIterations);
		}*/
		$this->mail->set(modMail::MAIL_BODY, $body);

		$this->mail->address('to', $Queue->get('email'));
		if (!$this->mail->send())
		{
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . $this->mail->mailer->ErrorInfo);
			$mail->reset();
			return 'error';
		}
		$this->mail->reset();
		return 'sended';
	}
	
	public function setMailSettings()
	{
		$this->mail->setHTML(true);
		//$this->setToLog($this->mailSettings);
		$this->mail->set('mail_subject', $this->mailSettings['emailsubject']);
		$this->mail->set('mail_sender', $this->modx->getOption('emailsender'));
		$this->mail->set('mail_from', $this->mailSettings['emailfrom']);
		$this->mail->set('mail_from_name', $this->mailSettings['emailfromname']);
		$this->mail->address('reply-to',$this->mailSettings['emailreplyto'],$this->mailSettings['emailreplytoname']);
		if(!empty($this->mailSettings['attachments']))
		{
			$attachments = json_decode($this->mailSettings['attachments'],true);
			foreach($attachments as $attach)
			{
				$this->mail->attach($_SERVER['DOCUMENT_ROOT'].'/'.$attach['filename'],$attach['attachname'].'.'.pathinfo($attach['filename'],PATHINFO_EXTENSION));
			}
		}
	}
	
	public function makePlaceholders($Queue)
	{
		$placeholders = array();
		$placeholders['queue'] = $Queue->toArray();
		$Subscriber = $Queue->getOne('Subscriber');
		$placeholders['subscriber'] = $Subscriber->toArray();
		if($Subscriber)
		{
			switch($Subscriber->get('type'))
			{
				case 'group':
				{
					$Group = $Subscriber->getOne('Group');
					$placeholders['group']=$Group->toArray();
					$hashes = explode('_',$Queue->get('hash'));
					$User = $this->modx->getObject('modUser',array('salt'=>$hashes[1]));
					$placeholders['user']=$User->toArray();
					if($User)
					{
						$Profile = $User->getOne('Profile');
						$placeholders['user']=array_merge($placeholders['user'],$Profile->toArray());
					}
					break;
				}
				case 'user':
				{
					$User = $Subscriber->getOne('User');
					$placeholders['user']=$User->toArray();
					if($User)
					{
						$Profile = $User->getOne('Profile');
						$placeholders['user']=array_merge($placeholders['user'],$Profile->toArray());
					}
					break;
				}
				case 'anonym':
				{
					$fields = json_decode($Subscriber->get('fields'),true);
					$placeholders['user'] = $fields;
					break;
				}
			}
		}
		
		$placeholders['instance'] = $this->oInstance->toArray();
		$placeholders['settings'] = $this->mailSettings;
		
		return $placeholders;
	}
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListInstanceProcessProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListInstanceProcessProcessor';