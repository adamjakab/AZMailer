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
 * Category Model
 */
class AZMailerModelCategory extends AZMailerModel {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'category_id', 'a.category_id',
			);
		}
		parent::__construct($config);
	}

	public function addNew($catIndex = null, $catName = null) {
		$answer = false;
		if (!empty($catIndex) && in_array($catIndex, array(1, 2, 3, 4, 5))) {
			if (!empty($catName)) {
				$data = array();
				$data["category_id"] = $catIndex;
				$data["item_order"] = 1;
				$data["is_default"] = 0;
				$data["name"] = $catName;
				$table = $this->getTable();
				if ($table->save($data)) {
					$answer = true;
				} else {
					$this->setError("Error - Save failed: " . print_r($table->getErrors(), true));
				}
			} else {
				$this->setError(JText::_('COM_AZMAILER_CATEGORY_MOD_ERR_EMPTY'));
			}
		} else {
			$this->setError("Error - Unknown Category ( $catIndex )!");
		}
		return ($answer);
	}

	public function getTable($type = 'azmailer_category_item', $prefix = 'Table', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	public function changeName($catIndex = null, $catName = null, $id = null) {
		$answer = false;
		if (!empty($catIndex) && in_array($catIndex, array(1, 2, 3, 4, 5))) {
			if (!empty($catName) && !empty($id)) {
				$data = array();
				$data["id"] = $id;
				$data["name"] = $catName;
				$table = $this->getTable();
				if ($table->save($data)) {
					$answer = true;
				} else {
					$this->setError("Error - Save failed: " . print_r($table->getErrors(), true));
				}
			} else {
				$this->setError("Error - Nome or ID is empty!");
			}
		} else {
			$this->setError("Error - Unknown Category ( $catIndex )!");
		}
		return ($answer);
	}

	public function delete($catIndex = null, $id = null) {
		$answer = false;
		if (!empty($catIndex) && in_array($catIndex, array(1, 2, 3, 4, 5))) {
			if (!empty($id)) {
				$table = $this->getTable();
				if ($table->delete($id)) {
					$answer = true;
				} else {
					$this->setError("Error - Delete failed: " . print_r($table->getErrors(), true));
				}
			} else {
				$this->setError("Error - ID is empty!");
			}
		} else {
			$this->setError("Error - Unknown Category ( $catIndex )!");
		}
		return ($answer);
	}

	public function setDefaultOption($catIndex = null, $id = null, $is_default = 0) {
		$answer = false;
		if (!empty($catIndex) && in_array($catIndex, array(1, 2, 3, 4, 5))) {
			if (!empty($id)) {
				$data = array();
				$data["id"] = $id;
				$data["is_default"] = $is_default;
				$table = $this->getTable();
				if ($table->save($data)) {
					$answer = true;
				} else {
					$this->setError("Error - Save failed: " . print_r($table->getErrors(), true));
				}
			} else {
				$this->setError("Error - ID is empty!");
			}
		} else {
			$this->setError("Error - Unknown Category ( $catIndex )!");
		}
		return ($answer);
	}

	public function saveOrderedItems($catIndex = null, $serialized = null) {
		$answer = false;
		if (!empty($catIndex) && in_array($catIndex, array(1, 2, 3, 4, 5))) {
			if (!empty($serialized)) {
				$table = $this->getTable();
				$item_order = 1;
				$idArr = explode("&", $serialized);
				foreach ($idArr as $idStr) {
					$id = (int)str_replace("element[]=", "", $idStr);
					if ($id) {
						$table->reset();
						$table->load($id);
						$table->item_order = $item_order;
						$table->store();
						$item_order++;
					}
				}
				$answer = true;
			} else {
				$this->setError("Error - Order is undefined!");
			}
		} else {
			$this->setError("Error - Unknown Category ( $catIndex )!");
		}
		return ($answer);
	}

	protected function getListQuery() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		//
		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__azmailer_category_item') . ' AS a');
		//
		// Filter by CATEGORY
		$category_id = (int)$this->getState('filter.category_id', 1);
		$query->where('a.category_id = ' . $category_id);

		//ORDERING
		$query->order("a.item_order ASC");
		//
		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @param string $ordering
	 * @param string $direction
	 */
	protected function populateState($ordering = "id", $direction = "ASC") {
		// Load the filter state.
		$category_id = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', 1, "INTEGER");
		$this->setState('filter.category_id', $category_id);

		// Load the parameters.
		/** @var \JRegistry|\Joomla\Registry\Registry - AZMailer Settings params */
		$params = JComponentHelper::getParams('com_azmailer');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);

		//kill limiting here
		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);
	}
}

