<?php

class MailingListGetAnonymousProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		//$this->setToLog($scriptProperties);
		
		$Users=array();
		if(!empty($scriptProperties['mailinglist']))
		{
			$MailingList = $this->modx->getObject('MailingList',(int)$scriptProperties['mailinglist']);
			$criteria = $this->modx->newQuery('MailingListSubscribers');
			$criteria->where(array(
			   'type'=>'anonym',
			));
			$criteria->sortby('object_id','ASC');
			$Anonymous = $MailingList->getMany('Subscribers',$criteria);
			foreach($Anonymous as $Anonym)
			{
				$Users[] = json_decode($Anonym->get('fields'),true);
			}
		}
		$count = count($Users);
		
		return $this->outputArray($Users, $count);
    }
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListGetAnonymousProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListGetAnonymousProcessor';