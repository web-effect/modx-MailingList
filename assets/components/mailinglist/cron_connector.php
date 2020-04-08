<?php
$_COOKIE['PHPSESSID']=$_REQUEST['PHPSESSID'];
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('mailinglist.core_path',null,$modx->getOption('core_path').'components/mailinglist/');

require_once $corePath.'model/mailinglist/mailinglistcontrols.class.php';
$modx->MailingListControls = new MailingListControls($modx);

$modx->lexicon->load('mailinglist:default');

$TaskerAvail = false;
$TaskerService = $modx->getOption('mailinglist.tasker.service',null,null);
if($TaskerService)
{
	$TaskerModelPath = $modx->getOption(strtolower($TaskerService).'_core_path',null,$modx->getOption('core_path').'components/'.strtolower($TaskerService).'/').'model/'.strtolower($TaskerService).'/';
	$Tasker = $modx->getService(strtolower($TaskerService),$TaskerService,$TaskerModelPath);
	if($Tasker->initialized)
	{
		$TaskerAvail = $Tasker->test();
	}
}
if($TaskerAvail)
{
	$oInstance = $modx->getObject('MailingListInstance',(int)$_REQUEST['instance']);
	$Tasker->remove($oInstance->get('task'));
	$oInstance->set('task',null);
	$oInstance->save();
	
	$path = $modx->getOption('processorsPath',$modx->MailingListControls->config,$corePath.'processors/');
	$modx->request->handleRequest(array(
		'processors_path' => $path,
		'location' => '',
	));
	/*$response = $modx->runProcessor
	(
		strtolower($_REQUEST['action']).'.class',
		$_REQUEST
		, array
		(
			'processors_path' => $path,
		)
	);*/
}