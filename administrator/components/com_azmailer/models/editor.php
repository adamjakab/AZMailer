<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerModel;
use AZMailer\Helpers\AZMailerTemplateHelper;
use AZMailer\Helpers\AZMailerNewsletterHelper;
use \JFactory;
use \JTable;
use \JText;
use \JComponentHelper;

/**
 * Template Model
 */
class AZMailerModelEditor extends AZMailerModel {

    public function __construct($config = array()) {
	parent::__construct($config);
    }


    public function getSpecificItem($id=null) {
	$item = $this->_getSpecificItem($id);
	return($item);
    }

    public function saveSpecificItem($data) {
	JRequest::checkToken() or jexit( 'Invalid Token' );
	//do some checks
	$this->_saveSpecificItem($data);
	$errors = $this->getErrors();
	if (count($errors)) {
	    JError::raiseError(500, implode('<br />', $errors));
	    return false;
	}
	return(count($errors)==0);
    }

    /*
    public function removeSpecificItems($cidArray) {
	$delcnt = 0;
	$table = $this->getTable();
	while(count($cidArray)) {
	    $cid = array_pop($cidArray);
	    $table->load($cid);
	    if ($table->tpl_code != "default") {
		if (AZMailerNewsletterHelper::countNewslettersWithTemplateId($table->id) == 0) {
		    if ($table->delete( $cid )) {
			$delcnt++;
		    } else {
			$this->setError($table->getError());
		    }
		} else {
		    $this->setError(JText::sprintf('COM_AZMAILER_TEMPLATE_ERR_DELETE_USED', $table->tpl_code));
		}
	    } else {
		$this->setError(JText::_('COM_AZMAILER_TEMPLATE_ERR_DELETE_ISDEFAULT'));
	    }
	}
	$errors = $this->getErrors();
	if (count($errors)) {
	    JError::raiseError(500, implode('<br />', $errors));
	    return false;
	}
	return(count($errors)==0);
    }
*/





    public function getTable($type = null, $prefix = null, $config = array()) {
		return JTable::getInstance(($type?$type:'azmailer_blob'), ($prefix?$prefix:'Table'), $config);
    }
}
