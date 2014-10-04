<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * AZMailer Controller
 */
class AZMailerControllerAZMailer extends \JControllerLegacy {
	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * @param bool $cachable
	 * @return JControllerLegacy|void
	 */
	public function display($cachable = false) {
		global $AZMAILER;
		$view = $this->getView($AZMAILER->getOption("controller"), 'html', '');
		$tmpl = JRequest::getVar('tmpl', 'default');
		if (($model = $this->getModel($AZMAILER->getOption("controller")))) {
			$view->setModel($model, true);
		}
		$view->setLayout($tmpl);
		if (method_exists($view, $AZMAILER->getOption('task'))) {
			call_user_func(array($view, $AZMAILER->getOption('task')));
		} else {
			$view->display();
		}
	}

}
