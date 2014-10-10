<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerModel;
use JComponentHelper;

/**
 * Template Model
 */
class AZMailerModelEditor extends AZMailerModel {

	/**
	 * @param array $config
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * @param integer $id
	 * @return object
	 */
	public function getSpecificItem($id = null) {
		$item = $this->_getSpecificItem($id);
		return ($item);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function saveSpecificItem($data) {
		JRequest::checkToken() or jexit('Invalid Token');
		//do some checks
		$this->_saveSpecificItem($data);
		$errors = $this->getErrors();
		if (count($errors)) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		return (count($errors) == 0);
	}

	/**
	 * @param string  $type
	 * @param string  $prefix
	 * @param array $config
	 * @return JTable|mixed
	 */
	public function getTable($type = null, $prefix = null, $config = array()) {
		return JTable::getInstance(($type ? $type : 'azmailer_blob'), ($prefix ? $prefix : 'Table'), $config);
	}
}
