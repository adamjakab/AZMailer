<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerException;
use AZMailer\Core\AZMailerView;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerEditorHelper;

/**
 * Class AZMailerViewEditor
 */
class AZMailerViewEditor extends AZMailerView {
	/**
	 * @var object $params
	 */
	protected $params;

	/**
	 * @return mixed|void
	 * @throws AZMailerException
	 */
	public function edit() {
		/** @var AZMailerModelEditor $model */
		$model = $this->getModel();

		$JI = JFactory::getApplication()->input;
		$P = $JI->getString("params");

		/*(title, parent_type, parent_id, return_uri[base64encoded])*/
		$this->params = json_decode(base64_decode($P));

		if (gettype($this->params) != "object") {
			throw new AZMailerException("EDITOR PARAMETERS ERROR", 500);
		}

		$this->params->id = AZMailerEditorHelper::getBlobIdByParent($this->params->parent_type, $this->params->parent_id);
		$this->item = $model->getSpecificItem($this->params->id);
		$this->state = $this->get('State');

		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_EDITOR"), "editor");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "editor.save", 'save', 'JTOOLBAR_SAVE', false), /*save&close*/
			array("core.manage", "editor.cancel", 'cancel', 'JTOOLBAR_CANCEL', false), /*cancel*/
		));
		$JI->set("hidemainmenu", 1);
		return (parent::display("edit"));
	}


	/**
	 * Launch the quick-edit interface
	 */
	public function quickEdit() {
		parent::display("quickedit");
	}


	/**
	 * @param string $tpl
	 * @param bool $closeEdit
	 */
	public function save($tpl = null, $closeEdit = true) {
		$model = $this->getModel();
		$data = JRequest::get('post');
		$data["htmlblob"] = JRequest::getVar('htmlblob', '', 'post', 'string', JREQUEST_ALLOWHTML);
		$model->saveSpecificItem($data);
		if ($closeEdit || !$closeEdit) {
			$this->cancel($tpl);
		}
	}

	/**
	 * Cancel edit and go back to where we came from
	 */
	public function cancel() {
		global $AZMAILER;
		$redirectUrl = base64_decode(JRequest::getVar('return_uri', null));
		$AZMAILER->getController()->setRedirect($redirectUrl);
	}

	/**
	 * Show Elfinder
	 */
	public function elfinder() {
		$this->state = $this->get('State');
		parent::display("elfinder");
	}

	/**
	 * Elfinder Ajax connection
	 * @throws Exception
	 */
	public function elfinder_conn() {
		global $AZMAILER;
		$com_path = $AZMAILER->getOption("com_path_admin");
		$efcp = $com_path . DS . 'assets' . DS . 'js' . DS . 'elfinder' . DS . 'php';
		require_once($efcp . DS . 'elFinderConnector.class.php');
		require_once($efcp . DS . 'elFinder.class.php');
		require_once($efcp . DS . 'elFinderVolumeDriver.class.php');
		require_once($efcp . DS . 'elFinderVolumeLocalFileSystem.class.php');

		$rootPath = JPATH_SITE . DS . 'images/';

		$JURI = \JUri::getInstance();
		$uriHost = ($JURI->isSSL() ? "https://" : "http://") . $JURI->getHost();
		$uriSiteBase = str_replace("administrator/", "", str_replace($uriHost, '', $JURI->base()));
		$rootUriImg = 'http://' . $JURI->getHost() . $uriSiteBase . "images/";
		//$rootURI = 'http://' . $_SERVER['SERVER_NAME'] . str_replace(JPATH_ROOT,JPATH_SITE,"") . '/images/';

		$opts = array(
			'debug' => false,
			'roots' => array(
				array(
					'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
					'path' => $rootPath,
					'URL' => $rootUriImg
				)
			)
		);
		// run elFinder
		ob_clean();
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();

		$app = \JFactory::getApplication();
		$app->close();
	}


}
