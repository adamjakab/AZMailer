<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerController;


/**
 * Controller for Category - AJAX REQUESTS
 * contoller is called with "format=raw"
 * No View is involved - works with model and outputs clean data in JSON FORMAT
 *
 */
class AZMailerControllerCategory extends AZMailerController {
	private $model;

	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		global $AZMAILER;
		parent::__construct($config);
		\JFactory::getDocument()->setMimeEncoding('application/json');
		$this->model = $this->getModel($AZMAILER->getOption("controller"));
	}

	/**
	 * @param bool $cachable
	 * @param bool $urlparams
	 * @return JController|void
	 */
	public function display($cachable = false, $urlparams = false) {
		global $AZMAILER;
		$answer = new stdClass();
		$answer->result = false;
		$answer->errors[] = "The task you have requested does not exist!\nTask: " . $AZMAILER->getOption("ctrl.task");
		echo json_encode($answer);
	}


	public function addNew() {
		$cat_index = \JRequest::getVar('cat_index', '', 'post', 'int');
		$name = \JRequest::getVar('name', '', 'post', 'string');
		$answer = new stdClass();
		$answer->result = $this->model->addNew($cat_index, $name);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function changeName() {
		$cat_index = \JRequest::getVar('cat_index', '', 'post', 'int');
		$name = \JRequest::getVar('name', '', 'post', 'string');
		$id = \JRequest::getVar('id', '', 'post', 'int');
		$answer = new stdClass();
		$answer->result = $this->model->changeName($cat_index, $name, $id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function delete() {
		$cat_index = \JRequest::getVar('cat_index', '', 'post', 'int');
		$id = \JRequest::getVar('id', '', 'post', 'int');
		$answer = new stdClass();
		$answer->result = $this->model->delete($cat_index, $id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function setDefaultOption() {
		$cat_index = \JRequest::getVar('cat_index', '', 'post', 'int');
		$id = \JRequest::getVar('id', '', 'post', 'int');
		$is_default = \JRequest::getVar('is_default', '0', 'post', 'int');
		$answer = new stdClass();
		$answer->result = $this->model->setDefaultOption($cat_index, $id, $is_default);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function saveOrderedItems() {
		$cat_index = \JRequest::getVar('cat_index', '', 'post', 'int');
		$serialized = \JRequest::getVar('serialized', '', 'post', 'string');
		$answer = new stdClass();
		$answer->result = $this->model->saveOrderedItems($cat_index, $serialized);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

}
