<?php
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 **/
defined('_JEXEC') or die('Restricted access');


class TableAzmailer_mail_queue_item extends JTable {
	var $id = null;
	var $mq_date = null;
	var $mq_type = null;
	var $mq_typeid = null;
	var $mq_priority = null;
	var $mq_state = null;
	var $mq_from = null;
	var $mq_from_name = null;
	var $mq_to = null;
	var $mq_subject = null;
	var $mq_body = null;
	var $mq_body_txt = null;
	var $mq_attachments = null;
	var $mq_substitutions = null;

	var $mq_send_attempt_count = null;
	var $mq_last_send_attempt_date = null;
	var $mq_last_send_attempt_result_code = null;
	var $mq_last_send_attempt_log = null;
	var $mq_has_been_read = null;

	function __construct(&$_db) {
		parent::__construct('#__azmailer_mail_queue_item', 'id', $_db);
	}

	function check() {
		return true;
	}

}
