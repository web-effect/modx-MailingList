<?php

class MailingListInstancePlayProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		$this->setToLog($scriptProperties);
		
		$Instances=array();
		if(!empty($scriptProperties['instance']))
		{
			$TaskerAvail = false;
			$TaskerService = $this->modx->getOption('mailinglist.tasker.service',null,null);
			if($TaskerService)
			{
				$TaskerModelPath = $this->modx->getOption(strtolower($TaskerService).'_core_path',null,$this->modx->getOption('core_path').'components/'.strtolower($TaskerService).'/').'model/'.strtolower($TaskerService).'/';
				$Tasker = $this->modx->getService(strtolower($TaskerService),$TaskerService,$TaskerModelPath);
				if($Tasker->initialized)
				{
					$TaskerAvail = $Tasker->test();
				}
			}
			if($TaskerAvail)
			{
				$oInstance = $this->modx->getObject('MailingListInstance',(int)$scriptProperties['instance']);
				$curTask = $oInstance->get('task');
				if(!empty($curTask))
				{
					$Tasker->remove($curTask);
					$oInstance->set('task',null);
				}
				$TaskCommandParams = array
				(
					'mailinglist'=> $scriptProperties['mailinglist'],
					'instance'=> $scriptProperties['instance'],
					'action'=> 'mgr/mailinglist/InstanceProcess',
					'PHPSESSID' => $_COOKIE['PHPSESSID'],
					'HTTP_MODAUTH'=> $_SESSION["modx.{$this->modx->context->get('key')}.user.token"]
				);
				$TaskCommand = $Tasker->construct_command('assets/components/mailinglist/cron_connector.php',$TaskCommandParams);
				$TaskParams = array
				(
					'command'=>$TaskCommand,
					'time'=>time()+240
				);
				if($TaskID = $Tasker->add($TaskParams))
				{
					$oInstance->set('status','process');
					$oInstance->set('task',$TaskID);
					if(empty($oInstance->get('start_date')))
					{
						$oInstance->set('start_date',time());
					}
				}
				$oInstance->save();
			}
		}
		
		return $this->outputArray($Instances);
    }
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListInstancePlayProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListInstancePlayProcessor';