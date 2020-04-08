<?php

class MailingListGetInstanceStatusProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		$this->setToLog($scriptProperties);
		
		$Result=array();
		if(!empty($scriptProperties['instance']))
		{
			$oInstance = $this->modx->getObject('MailingListInstance',(int)$scriptProperties['instance']);
			$Result['status'] = $oInstance->get('status');
		}
		
		return $this->outputArray($Result);
    }
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListGetInstanceStatusProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListGetInstanceStatusProcessor';