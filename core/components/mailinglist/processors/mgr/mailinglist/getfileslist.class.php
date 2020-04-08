<?php

class MailingListGetFilesProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		//$this->setToLog($scriptProperties);
		
		$Attachments=array();
		if(!empty($scriptProperties['mailinglist']))
		{
			$MailingList = $this->modx->getObject('MailingList',(int)$scriptProperties['mailinglist']);
			$Settings = $MailingList->getOne('Settings');
			$Attachments = json_decode($Settings->get('attachments'),true);
			if(!$Attachments)$Attachments=array();
		}
		$count = count($Attachments);
		
		return $this->outputArray($Attachments, $count);
    }
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListGetFilesProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListGetFilesProcessor';

