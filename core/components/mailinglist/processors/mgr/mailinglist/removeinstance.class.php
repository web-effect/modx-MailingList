<?php

class MailingListRemoveInstanceProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		//$this->setToLog($scriptProperties);
		
		$Instances=array();
		if(!empty($scriptProperties['instance'])&&!empty($scriptProperties['mailinglist']))
		{
			$iSQL = 'DELETE FROM `modx_mailinglist_instances` WHERE `id` = '.$scriptProperties['instance'];
			$qSQL = 'DELETE FROM `modx_mailinglist_queues` WHERE `instance` = '.$scriptProperties['instance'];
			
			$iQuery = new xPDOCriteria($this->modx,$iSQL);
			$qQuery = new xPDOCriteria($this->modx,$qSQL);
			
			$iQuery->prepare();
			$iQuery->stmt->execute();
			$qQuery->prepare();
			$qQuery->stmt->execute();
			
			$this->modx->cacheManager->refresh(array("db"=> array("MailingListInstance"=>array())));
			
			$MailingList = $this->modx->getObject('MailingList',(int)$scriptProperties['mailinglist']);
			$criteria = $this->modx->newQuery('MailingListInstance');
			$criteria->where(array(
				  'status:IN'=>array('process','created','prepared','pause'),
			));
			$criteria->sortby('id','ASC');
			$oInstances = $MailingList->getMany('Instances',$criteria);
			foreach($oInstances as $oInstance)
			{
				$Instances[] = $oInstance->toArray();
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
		file_put_contents(dirname(__FILE__).'/MailingListRemoveInstanceProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListRemoveInstanceProcessor';