<?php

class MailingListCreateManagerController extends ResourceCreateManagerController
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
			'resource' => true
		));
		$Controls->addJsControllers($this, array("resource/create.js"));
		
		$ready = array(
			'xtype' => 'mailinglist-page-create',
			'record' => $this->resourceArray,
			'publish_document' => (int)$this->canPublish,
			'canSave' => (int)$this->canSave,
			'show_tvs' => (int)!empty($this->tvCounts),
			'mode' => 'create',
		);
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
}