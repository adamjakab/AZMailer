<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerView;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerEditorHelper;

/**
 * Class AZMailerViewEditor
 */
class AZMailerViewEditor extends AZMailerView {
	/**
	 * @param null $tpl
	 * @return mixed|void
	 */
	function display($tpl = null) {
		/*nothing here*/
	}

	/**
	 * @param null $tpl
	 * @return object
	 */
	function edit($tpl = null) {
		global $AZMAILER;
		$model = $this->getModel();
		$P = JRequest::getVar('params', null);
		try {
			$this->params = json_decode(base64_decode($P)); /*(title, parent_type, parent_id, return_uri[base64encoded])*/
		} catch (Exception $e) {
			$AZMAILER->getController()->setRedirect('index.php?option=' . $AZMAILER->getOption("com_name"));
			return JError::raiseWarning(500, "EDITOR PARAMETERS ERROR");
		}
		if (gettype($this->params) != "object") {
			$AZMAILER->getController()->setRedirect('index.php?option=' . $AZMAILER->getOption("com_name"));
			return JError::raiseWarning(500, "EDITOR PARAMETERS ERROR");
		}
		//
		$this->params->id = AZMailerEditorHelper::getBlobIdByParent($this->params->parent_type, $this->params->parent_id);
		$this->item = $model->getSpecificItem($this->params->id);
		$this->state = $this->get('State');
		parent::display("edit");
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_EDITOR"), "editor");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "editor.save", 'save', 'JTOOLBAR_SAVE', false), /*save&close*/
			array("core.manage", "editor.cancel", 'cancel', 'JTOOLBAR_CANCEL', false), /*cancel*/
		));
		JRequest::setVar('hidemainmenu', 1); //blocks main-menu
	}

	function quickEdit($tpl = null) {
		parent::display("quickedit");
	}



	function save($tpl = null, $closeEdit = true) {
		global $AZMAILER;
		$model = $this->getModel();
		$data = JRequest::get('post');
		$data["htmlblob"] = JRequest::getVar('htmlblob', '', 'post', 'string', JREQUEST_ALLOWHTML);
		//print_r($data);
		//die();
		$model->saveSpecificItem($data);
		if ($closeEdit || !$closeEdit) {
			$this->cancel($tpl);
		}
	}

	function cancel($tpl = null) {
		global $AZMAILER;
		$redirectUrl = base64_decode(JRequest::getVar('return_uri', null));
		$AZMAILER->getController()->setRedirect($redirectUrl);
	}


	function elfinder($tpl = null) {
		$this->state = $this->get('State');
		parent::display("elfinder");
	}


	function elfinder_conn() {
		global $AZMAILER;
		$com_path = $AZMAILER->getOption("com_path_admin");
		$efcp = $com_path . DS . 'assets' . DS . 'js' . DS . 'elfinder' . DS . 'php';
		require_once($efcp . DS . 'elFinderConnector.class.php');
		require_once($efcp . DS . 'elFinder.class.php');
		require_once($efcp . DS . 'elFinderVolumeDriver.class.php');
		require_once($efcp . DS . 'elFinderVolumeLocalFileSystem.class.php');

		$rootPath = JPATH_SITE . DS . 'images/';

		$JURI = \JUri::getInstance();
		$uriHost = ($JURI->isSSL()?"https://":"http://") . $JURI->getHost();
		$uriSiteBase = str_replace("administrator/","", str_replace($uriHost, '', $JURI->base()));
		$rootUriImg = 'http://' .  $JURI->getHost() . $uriSiteBase . "images/";
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
