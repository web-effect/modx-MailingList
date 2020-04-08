<?php

/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'components/mailinglist/processors/mgr/mailinglist/create.class.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'components/mailinglist/processors/mgr/mailinglist/update.class.php';

class MailingList extends modResource
{
	public $showInContextMenu = true;
	public $allowChildrenResources = false;
	
	function __construct(xPDO & $xpdo)
	{
        parent :: __construct($xpdo);
        $this->set('class_key','MailingList');
    }
	
	public static function getControllerPath(xPDO &$modx)
	{
		return $modx->getOption('mailinglist.core_path',null,$modx->getOption('core_path').'components/mailinglist/').'controllers/mailinglist/';
	}
	
	public function getContextMenuText()
	{
		$this->xpdo->lexicon->load('mailinglist:default');
		return array
		(
			'text_create' => $this->xpdo->lexicon('mailinglist'),
			'text_create_here' => $this->xpdo->lexicon('mailinglist_create_here'),
		);
	}
	
	public function getResourceTypeName()
	{
		$this->xpdo->lexicon->load('mailinglist:default');
		return $this->xpdo->lexicon('mailinglist');
	}
	
	public function setLog($var)
	{
		ob_start();
		var_dump($var);
		$log = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingList.log',$log,FILE_APPEND);
	}
}