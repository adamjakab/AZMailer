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
use AZMailer\Helpers\AZMailerSubscriberHelper;
use AZMailer\Helpers\AZMailerStatisticsHelper;
use AZMailer\Helpers\AZMailerDateHelper;

/**
 * Newsletter Model
 */
class AZMailerModelNewsletter extends AZMailerModel {
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'a.id', 'nl_title', 'nltitle_internal'
			);
		}
		parent::__construct($config);
	}

	protected function getListQuery() {
		$db = \JFactory::getDBO();
		$query = $db->getQuery(true);
		//
		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__azmailer_newsletter') . ' AS a');

		//Search
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(a.nl_title LIKE ' . $search . ' OR a.nl_title_internal LIKE ' . $search . ')');
		}

		//ORDERING
		//$orderCol = $this->state->get('list.ordering', 'a.id');
		//$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order("a.id DESC");
		//
		return $query;
	}

	protected function populateState($ordering = "id", $direction = "ASC") {
		//Filters
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', "STRING"));

		//Component parameters
		$params = \JComponentHelper::getParams('com_azmailer');
		$this->setState('params', $params);
		//
		parent::populateState($ordering, $direction);
	}

	/**
	 * TODO: this should return entity and NOT simple object - for now it is done in the view where needed
	 * @param null $id
	 * @return bool|object
	 */
	public function getSpecificItem($id = null) {
		$item = $this->_getSpecificItem($id);
		return ($item);
	}

	/**
	 * TODO: we need our component specific exception and a general catcher so we don't end up un J!'s error page ;)
	 *
	 * @param array $data The posted data in array format
	 * @return bool
	 * @throws \AZMailer\Core\AZMailerException
	 */
	public function saveSpecificItem(array $data) {
		if (empty($data["nl_title_internal"])) {
			$data["nl_title_internal"] = $data["nl_title"];
		}
		//save
		$this->_saveSpecificItem($data);
		return (count($this->getErrors()) == 0);
	}

	/*
		SENT NESLETTERS MAY STILL BE IN QUEUE AND IF WE REMOVE IMAGES/ATTACHMENTS IT CAN BE A PROBLEM
		SO THEY CAN BE REMOVED ONLY AFTER N DAYS OF BEING SENT
        N is the highest btwn mq_purge_sent_items_after_days AND mq_purge_unsent_items_after_days
	*/
	public function removeSpecificItems($cidArray) {
		global $AZMAILER;
		$PSIAD = (int)$AZMAILER->getOption('mq_purge_sent_items_after_days');
		$PUIAD = (int)$AZMAILER->getOption('mq_purge_unsent_items_after_days');
		$canDeleteAfterDays = ($PSIAD>$PUIAD?$PSIAD:$PUIAD);
		$canDeleteAfterSeconds = $canDeleteAfterDays * 24 * 60 *60;

		$table = $this->getTable();
		while (count($cidArray)) {
			$cid = array_pop($cidArray);
			$table->load($cid);
			if ($table->nl_send_date > (AZMailerDateHelper::now() - $canDeleteAfterSeconds)) {
				JError::raiseWarning( 500, \JText::sprintf('COM_AZMAILER_NEWSLETTER_MSG_DELETED_NOT', $table->nl_title, $canDeleteAfterDays));
			} else {
				//DELETE NEWSLETTER STATISTICS
				AZMailerStatisticsHelper::deleteStatisticsForNewsletter($cid);
				//DELETE NEWSLETTER IMAGES
				AZMailerNewsletterHelper::deleteImagesForNewsletter($cid);
				//DELETE NEWSLETTER ATTACHMENTS
				AZMailerNewsletterHelper::deleteAttachmentsForNewsletter($cid);
				if ($table->delete($cid)) {//OK newsletter deleted
					\JFactory::getApplication()->enqueueMessage("deleted newsletter: ".$table->nl_title);
				} else {
					$this->setError($table->getError());
					break;
				}
			}
		}
		$errors = $this->getErrors();
		if (count($errors)) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		return (count($errors) == 0);
	}

	public function duplicateItem($cidArray) {
		if (count($cidArray) == 1) {
			$id = $cidArray[0];
			$table = $this->getTable();
			$table->load($id);
			//DUPLICATE NEWSLETTER IMAGES
			$NEWSUBSTITUTIONS = AZMailerNewsletterHelper::duplicateImagesForNewsletter($id);
			//DUPLICATE ATTACHMENTS IMAGES
			$NEWATTACHMENTS = AZMailerNewsletterHelper::duplicateAttachmentsForNewsletter($id);
			//
			$table->id = null;//new record
			$table->nl_create_date = AZMailerDateHelper::now();
			$table->nl_send_date = 0;
			$table->nl_sendcount = 0;
			$table->nl_title_internal = $table->nl_title_internal . ' Copy';
			$table->nl_template_substitutions = $NEWSUBSTITUTIONS;
			$table->nl_attachments = $NEWATTACHMENTS;
			if (!$table->store()) {
				return JError::raiseWarning( 500, $table->getError() );
			}
			\JFactory::getApplication()->enqueueMessage(\JText::_("COM_AZMAILER_NEWSLETTER_MSG_DUPLICATED"));
		} else {
			JError::raiseWarning(500, \JText::_("COM_AZMAILER_NEWSLETTER_MSG_DUPLICATED_NOT"));
			return false;
		}
	}


	public function getTable($type = null, $prefix = null, $config = array()) {
		return JTable::getInstance(($type ? $type : 'azmailer_newsletter'), ($prefix ? $prefix : 'Table'), $config);
	}
}