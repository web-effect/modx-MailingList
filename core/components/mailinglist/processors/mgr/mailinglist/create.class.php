<?php

require_once MODX_CORE_PATH . 'model/modx/modprocessor.class.php';
require_once MODX_CORE_PATH . 'model/modx/processors/resource/create.class.php';

class MailingListCreateProcessor extends modResourceCreateProcessor
{
	public $object;
	public $classKey = 'MailingList';
	
	public function beforeSave()
	{
        return parent::beforeSave();
    }
	
	public function afterSave()
	{
		$newSettings = $this->modx->newObject('MailingListSettings');
		$newSettings->set('mailinglist',$this->object->get('id'));
		$newSettings->set('emailfrom',$this->object->get('emailfrom'));
		$newSettings->set('emailfromname',$this->object->get('emailfromname'));
		$newSettings->set('emailreplyto',$this->object->get('emailreplyto'));
		$newSettings->set('emailreplytoname',$this->object->get('emailreplytoname'));
		$newSettings->set('emailsubject',$this->object->get('emailsubject'));
		$newSettings->set('attachments',$this->object->get('attachments'));
		$newSettings->save();
		
		$arSubcribers = json_decode($this->object->get('subscribers'),true);
		foreach($arSubcribers as $key=>$content)
		{
			$arKey = explode('_',$key);
			$type = $arKey[0];
			$object_id = $arKey[1];
			$hash = md5($type.$object_id.$this->object->get('id'));
			$newSubscriber = $this->modx->newObject('MailingListSubscribers');
			$newSubscriber->set('mailinglist',$this->object->get('id'));
			$newSubscriber->set('type',$type);
			$newSubscriber->set('object_id',$object_id);
			$newSubscriber->set('hash',$hash);
			switch($type)
			{
				case 'group':
				{
					if(!empty($content['exclude']))
					{
						$newSubscriber->set('exclude',json_encode($content['exclude']));
					}
					break;
				}
				case 'user':
				{
					break;
				}
				case 'anonym':
				{
					$newSubscriber->set('fields',json_encode($content));
					break;
				}
			}
			$newSubscriber->save();
		}
		//$this->setLog($arSubcribers);
		
		
		return parent::afterSave();
	}
	
	public function setLog($var)
	{
		ob_start();
		var_dump($var);
		$log = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListCreateProcessor.log',$log,FILE_APPEND);
	}
}