<?php

class MailingListCreateInstanceProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		$this->setToLog($scriptProperties);
		
		$Instances=array();
		if(!empty($scriptProperties['mailinglist']))
		{
			$NewInstance = $this->modx->newObject('MailingListInstance');
			$NewInstance->set('mailinglist',(int)$scriptProperties['mailinglist']);
			$NewInstance->set('status','created');
			$NewInstance->save();
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
		
		return $this->outputArray($Instances, $count);
    }
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListCreateInstanceProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListCreateInstanceProcessor';