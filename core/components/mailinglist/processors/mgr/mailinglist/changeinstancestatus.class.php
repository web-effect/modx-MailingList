<?php

class MailingListChangeInstanceStatusProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		$this->setToLog($scriptProperties);
		
		$Instances=array();
		if(!empty($scriptProperties['instance'])&&!empty($scriptProperties['status']))
		{
			$oInstance = $this->modx->getObject('MailingListInstance',(int)$scriptProperties['instance']);
			$oInstance->set('status',$scriptProperties['status']);
			$oInstance->save();
		}
		
		return $this->outputArray($Instances);
    }
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListChangeInstanceStatusProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListChangeInstanceStatusProcessor';