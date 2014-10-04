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
use \JError;
use \JText;

/**
 * ControlPanel View
 */
class AZMailerViewCp extends AZMailerView {
	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		parent::__construct($config);
    }

	/**
	 * @param null $tpl
	 * @return bool|mixed|void
	 */
	function display($tpl = null) {
		// Check for model errors
		if (count($errors = $this->get('Errors'))) {
		    JError::raiseError(500, implode('<br />', $errors));
		    return false;
		}

		$this->cpbuttons = $this->get('CpButtons');
		$this->cpinfo = $this->get('CpInfo');
		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_CP"),"azmailer");
		return(parent::display($tpl));
    }

}
