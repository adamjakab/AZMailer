<?php
/**
 * @package AZ Newsletter component for Joomla! 1.5
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 **/
defined('_JEXEC') or die('Restricted access');


class TableAzmailer_template extends JTable {
	var $id = null;
	var $tpl_code = null;
	var $tpl_type = null;
	var $tpl_name = null;
	var $tpl_title = null;


	function __construct(&$_db) {
		parent::__construct('#__azmailer_template', 'id', $_db);
	}

	function check() {
		return true;
	}

}