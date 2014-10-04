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
 * Controller for Editor
 * Extends AZMailerController which has the default display function
 * which will handle all actions in absence of exlicit handler function.
 */
class AZMailerControllerEditor extends AZMailerController {
	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		parent::__construct($config);
    }

}
