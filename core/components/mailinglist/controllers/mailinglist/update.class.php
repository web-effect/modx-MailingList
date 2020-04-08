<?php

class MailingListUpdateManagerController extends ResourceUpdateManagerController
{
	public $resource;
	
    public function getLanguageTopics()
	{
        return array('resource','mailinglist:default');
    }
	
	public function loadCustomCssJs()
	{
		$html = $this->head['html'];
		parent::loadCustomCssJs();
		$this->head['html'] = $html;
		
		$Controls = $this->modx->getService('MailingListControls');
		$Controls->loadManagerFiles($this, array(
			'config' => true,
			'utils' => true,
			'css' => true,
			'resource' => true,
			'resource_id' => $this->resource->id
		));
		$Controls->addJsControllers($this, array("resource/update.js"));
		
		$Settings = $this->resource->getOne('Settings');
		$Subscribers = $this->resource->getMany('Subscribers');
		
		$arSubscribers = array();
		foreach($Subscribers as $Subscriber)
		{
			$type = $Subscriber->get('type');
			$object_id =  $Subscriber->get('object_id');
			$key = $type.'_'.$object_id;
			$arSubscriber = array();
			switch($type)
			{
				case 'group':
				{
					if(!empty($Subscriber->get('exclude')))
					{
						$arSubscriber['exclude'] = json_decode($Subscriber->get('exclude'),true);
					}
					$arSubscriber['checked'] = true;
					break;
				}
				case 'user':
				{
					$arSubscriber['checked'] = true;
					break;
				}
				case 'anonym':
				{
					if(!empty($Subscriber->get('fields')))
					{
						$arSubscriber = json_decode($Subscriber->get('fields'),true);
					}
					break;
				}
			}
			$arSubscribers[$key] = $arSubscriber;
		}
		
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
		
		$arInstances = array();
		$criteria = $this->modx->newQuery('MailingListInstance');
		$criteria->where(array(
			  'status:IN'=>array('process','created','prepared','pause'),
		));
		$criteria->sortby('id','ASC');
		$Instances = $this->resource->getMany('Instances',$criteria);
		foreach($Instances as $Instance)
		{
			$arInstances[] = $Instance->toArray();
		}
		
		$ready = array(
			'xtype' => 'mailinglist-page-update',
			'record' => array_merge($this->resourceArray,$Settings->toArray()),
			'publish_document' => (int)$this->canPublish,
			'canSave' => (int)$this->canSave,
			'show_tvs' => (int)!empty($this->tvCounts),
			'mode' => 'update',
		);
		$strSubscribers = '{}';
		if(!empty($arSubscribers))$strSubscribers = json_encode($arSubscribers);
		$ready['record']['subscribers'] = $strSubscribers;
		$ready['record']['taskerAvail'] = $TaskerAvail;
		$ready['record']['instances'] = $arInstances;
		
		
		$this->addHtml('
		<script type="text/javascript">
		// <![CDATA[
		MODx.config.publish_document = ' . (int)$this->canPublish . ';
		MODx.onDocFormRender = "' . $this->onDocFormRender . '";
		MODx.ctx = "' . $this->ctx . '";
		Ext.onReady(function() {
			MODx.load(' . $this->modx->toJSON($ready) . ');
		});
		// ]]>
		</script>');
	}
	
	public function setLog($var)
	{
		ob_start();
		var_dump($var);
		$log = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListUpdateManagerController.log',$log,FILE_APPEND);
	}
}