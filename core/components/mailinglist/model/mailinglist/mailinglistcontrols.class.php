<?php

class MailingListControls
{
	public $modx;
	public $pdoTools;
	public $initialized = array();
	public $authenticated = false;

	function __construct(modX &$modx, array $config = array())
	{
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('mailinglist.core_path', $config, $this->modx->getOption('core_path') . 'components/mailinglist/');
		$assetsPath = $this->modx->getOption('mailinglist.assets_path', $config, $this->modx->getOption('assets_path') . 'components/mailinglist/');
		$assetsUrl = $this->modx->getOption('mailinglist.assets_url', $config, $this->modx->getOption('assets_url') . 'components/mailinglist/');
		$actionUrl = $this->modx->getOption('mailinglist.action_url', $config, $assetsUrl . 'action.php');
		$connectorUrl = $assetsUrl . 'connector.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . $this->modx->context->get('key') . '/css/',
			'jsUrl' => $assetsUrl . $this->modx->context->get('key') . '/js/',
			'jsPath' => $assetsPath . $this->modx->context->get('key') . '/js/',
			'imagesUrl' => $assetsUrl . $this->modx->context->get('key') . '/img/',

			'connectorUrl' => $connectorUrl,
			'actionUrl' => $actionUrl,

			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'chunksPath' => $corePath . 'elements/chunks/',
			'templatesPath' => $corePath . 'elements/templates/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath . 'elements/snippets/',
			'processorsPath' => $corePath . 'processors/'
		), $config);

		$this->modx->addPackage('mailinglist', $this->config['modelPath']);
		$this->modx->lexicon->load('mailinglist:default');

		$this->authenticated = $this->modx->user->isAuthenticated($this->modx->context->get('key'));
	}

	public function initialize($ctx = 'web', $scriptProperties = array())
	{
		$this->config = array_merge($this->config, $scriptProperties);

		$this->config['ctx'] = $ctx;

		return true;
	}

	public function runProcessor($action = '', $data = array())
	{
		if (empty($action)){return false;}
		$this->modx->error->reset();
		$processorsPath = !empty($this->config['processorsPath'])
			? $this->config['processorsPath']
			: MODX_CORE_PATH . 'components/mailinglist/processors/';

		return $this->modx->runProcessor($action, $data, array('processors_path' => $processorsPath));
	}

	public function sanitizeString($string = '') {
		if (is_array($string)) {
			foreach ($string as $key => $value) {
				$string[$key] = $this->sanitizeString($value);
			}
			return $string;
		}

		$string = htmlentities(trim($string), ENT_QUOTES, "UTF-8");
		$string = preg_replace('/^@.*\b/', '', $string);
		$arr1 = array('[', ']', '`');
		$arr2 = array('&#091;', '&#093;', '&#096;');

		return str_replace($arr1, $arr2, $string);
	}

	public function error($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => false,
			'message' => $this->modx->lexicon($message, $placeholders),
			'data' => $data,
		);

		return $this->config['json_response']
			? $this->modx->toJSON($response)
			: $response;
	}

	public function success($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => true,
			'message' => $this->modx->lexicon($message, $placeholders),
			'data' => $data,
		);

		return $this->config['json_response']
			? $this->modx->toJSON($response)
			: $response;
	}

	public function systemVersion($version = '2.3.0', $dir = '>=')
	{
		$this->modx->getVersionData();

		return !empty($this->modx->version) && version_compare($this->modx->version['full_version'], $version, $dir);
	}

	public function loadManagerFiles(modManagerController $controller, array $properties = array())
	{
		$modxVersion = (int)$this->systemVersion();
		$assetsUrl = $this->config['assetsUrl'];
		$connectorUrl = $this->config['connectorUrl'];
		$cssUrl = $this->config['cssUrl'];
		$jsUrl = $this->config['jsUrl'];

		if (!empty($properties['config']))
		{
			$tmp = array(
				'assets_js' => $assetsUrl,
				'connector_url' => $connectorUrl,
				'auth' => $_SESSION["modx.{$this->modx->context->get('key')}.user.token"],
			);
			if(!empty($properties['resource_id']))$tmp['id']=$properties['resource_id'];
			$controller->addHtml('<script type="text/javascript">MODx.modxVersion = ' . $modxVersion . ';MailingList.config = ' . $this->modx->toJSON($tmp) . ';</script>', true);
		}
		if (!empty($properties['utils'])) {
			$controller->addJavascript($jsUrl . 'mailinglist.js');
			$controller->addLastJavascript($jsUrl . 'assets/utils.js');
		}
		if (!empty($properties['css'])) {
			$controller->addCss($cssUrl . 'mailinglist.css');
			if (!$modxVersion)
			{
				$controller->addCss($cssUrl . 'font-awesome.min.css');
			}
		}
	}
	
	public function addJsControllers(modManagerController $controller, array $properties = array())
	{
		foreach($properties as $cotrollerFile)
		{
			$controller->addLastJavascript($this->config['jsUrl'] . 'controllers/' . $cotrollerFile);
		}
	}
}