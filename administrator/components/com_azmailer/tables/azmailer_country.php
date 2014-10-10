<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
class TableAzmailer_country extends JTable {
	var $id = null;
	var $country_name = null;
	var $country_sigla = null;

	function __construct(&$_db) {
		parent::__construct('#__azmailer_country', 'id', $_db);
	}

	function check() {
		return true;
	}

}