<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerModel;
use AZMailer\Helpers\AZMailerComponentParamHelper;

/**
 * Settings Model
 */
class AZMailerModelSettings extends AZMailerModel {

	/**
	 * @param array $config
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * @param string $paramName
	 * @return string
	 */
	public function getParamEditForm($paramName) {
		$answer = '';
		if (!empty($paramName)) {
			if (AZMailerComponentParamHelper::keyExists($paramName)) {
				$answer = AZMailerComponentParamHelper::getParamEditForm($paramName);
			} else {
				$this->setError("Error - Unknown parameter name ( $paramName )!");
			}
		} else {
			$this->setError("Error - No parameter name supplied!");
		}
		return ($answer);
	}

	/**
	 * @param string $paramName
	 * @param mixed $paramValue
	 * @return bool|string
	 */
	public function submitParamEditForm($paramName, $paramValue) {
		$answer = '';
		if (!empty($paramName)) {
			if (AZMailerComponentParamHelper::keyExists($paramName)) {
				$answer = AZMailerComponentParamHelper::submitParamEditForm($paramName, $paramValue);
				if ($answer !== true) {
					$this->setError("Error - $answer");
					$answer = '';
				}
			} else {
				$this->setError("Error - Unknown parameter name ( $paramName )!");
			}
		} else {
			$this->setError("Error - No parameter name supplied!");
		}
		return ($answer);
	}


}

