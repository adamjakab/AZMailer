<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerController;
use \JFactory;
use \JRequest;


/**
 * Controller for Location - AJAX REQUESTS
 * contoller is called with "format=raw"
 * No View is involved - works with model and outputs clean data in JSON FORMAT
 *
 */
class AZMailerControllerLocation extends AZMailerController {
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
		$add_what = JRequest::getVar('add_what', '', 'post', 'string');
		$name = JRequest::getVar('name', '', 'post', 'string');
		$sigla = strtoupper(JRequest::getVar('sigla', '', 'post', 'string'));
		$country_id = JRequest::getVar('country_id', '', 'post', 'int');
		$region_id = JRequest::getVar('region_id', '', 'post', 'int');
		$answer = new stdClass();
		$answer->result = $this->model->addNew($add_what, $name, $sigla, $country_id, $region_id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function changeName() {
		$change_what = JRequest::getVar('change_what', '', 'post', 'string');
		$name = JRequest::getVar('name', '', 'post', 'string');
		$sigla = strtoupper(JRequest::getVar('sigla', '', 'post', 'string'));
		$id = JRequest::getVar('id', '', 'post', 'int');
		$answer = new stdClass();
		$answer->result = $this->model->changeName($change_what, $name, $sigla, $id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function delete() {
		$delete_what = JRequest::getVar('delete_what', '', 'post', 'string');
		$id = JRequest::getVar('id', '', 'post', 'int');
		$answer = new stdClass();
		$answer->result = $this->model->delete($delete_what, $id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	/*
	public function setDefaultOption() {
	$cat_index = JRequest::getVar('cat_index','','post','int');
	$id = JRequest::getVar('id','','post','int');
	$is_default = JRequest::getVar('is_default','0','post','int');
	$answer = new stdClass();
	$answer->result = $this->model->setDefaultOption($cat_index, $id, $is_default);
	$answer->errors = $this->model->getErrors();
	echo json_encode($answer);
	}*/
}
