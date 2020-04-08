<?php
/**
 * Tickets Connector
 *
 * @package tickets
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('mailinglist.core_path',null,$modx->getOption('core_path').'components/mailinglist/');
require_once $corePath.'model/mailinglist/mailinglistcontrols.class.php';
$modx->MailingListControls = new MailingListControls($modx);

$modx->lexicon->load('mailinglist:default');

/* handle request */
$path = $modx->getOption('processorsPath',$modx->MailingListControls->config,$corePath.'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));