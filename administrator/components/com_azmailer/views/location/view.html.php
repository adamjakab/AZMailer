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

class AZMailerViewLocation extends AZMailerView {

    function display($tpl = null) {
	global $AZMAILER;
	$this->items = $this->get('Items');
	$this->filters = $this->get('Filters');
	$this->pagination = $this->get('Pagination');
	$this->state = $this->get('State');
	if (count($errors = $this->get('Errors'))) {
	    JError::raiseError(500, implode('<br />', $errors));
	    return false;
	}
	parent::display($tpl);
	//
	AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_LOCATION"),"location");
	AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
	    array("core.create", "location.new", 'new', 'JTOOLBAR_NEW', false),
	));
    }

}
