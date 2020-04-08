<?php

require_once MODX_CORE_PATH . 'model/modx/modprocessor.class.php';
require_once MODX_CORE_PATH . 'model/modx/processors/resource/update.class.php';

class MailingListUpdateProcessor extends modResourceUpdateProcessor
{
	public $object;
	public $classKey = 'MailingList';
	
	public function afterSave()
	{
		$Settings = $this->object->getOne('Settings');
		$Settings->set('mailinglist',$this->object->get('id'));
		$Settings->set('emailfrom',$this->object->get('emailfrom'));
		$Settings->set('emailfromname',$this->object->get('emailfromname'));
		$Settings->set('emailreplyto',$this->object->get('emailreplyto'));
		$Settings->set('emailreplytoname',$this->object->get('emailreplytoname'));
		$Settings->set('emailsubject',$this->object->get('emailsubject'));
		$Settings->set('attachments',$this->object->get('attachments'));
		$Settings->save();
		
		$arSubcribers = json_decode($this->object->get('subscribers'),true);
		$Subscribers = $this->object->getMany('Subscribers');
		//Проходим по текущим и удаляем тех кого нет
		//Проходим по текущим и изменем тех кто есть
		foreach($Subscribers as $Subscriber)
		{
			$type = $Subscriber->get('type');
			$object_id =  $Subscriber->get('object_id');
			$key = $type.'_'.$object_id;
			if(!isset($arSubcribers[$key]))
			{
				$Subscriber->remove();
				continue;
			}
			switch($type)
			{
				case 'group':
				{
					$Subscriber->set('exclude',json_encode($arSubcribers[$key]['exclude']));
					break;
				}
				case 'user':
				{
					break;
				}
				case 'anonym':
				{
					$Subscriber->set('fields',json_encode($arSubcribers[$key]));
					break;
				}
			}
			$Subscriber->save();
			unset($arSubcribers[$key]);
		}
		
		//Проходим по оставшимся и добавляем их
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
		
		return parent::afterSave();
	}
	
	public function setLog($var)
	{
		ob_start();
		var_dump($var);
		$log = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListUpdateProcessor.log',$log,FILE_APPEND);
	}
}