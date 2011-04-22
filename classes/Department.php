<?php
/**
 * @copyright 2011 City of Bloomington, Indiana
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * @author Cliff Ingham <inghamn@bloomington.in.gov>
 */
class Department
{
	private $data = array();

	/**
	 * Populates the object with data
	 *
	 * Passing in an associative array of data will populate this object without
	 * hitting the database.
	 *
	 * Passing in a scalar will load the data from the database.
	 * This will load all fields in the table as properties of this class.
	 * You may want to replace this with, or add your own extra, custom loading
	 *
	 * @param int|array $id
	 */
	public function __construct($id=null)
	{
		if ($id) {
			if (is_array($id)) {
				$result = $id;
			}
			else {
				$mongo = Database::getConnection();
				if (preg_match('/[0-9a-f]{24}/',$id)) {
					$search = array('_id'=>new MongoId($id));
				}
				else {
					$search = array('name'=>$id);
				}
				$result = $mongo->departments->findOne($search);
			}

			if ($result) {
				$this->data = $result;
			}
			else {
				throw new Exception('departments/unknownDepartment');
			}
		}
		else {
			// This is where the code goes to generate a new, empty instance.
			// Set any default values for properties that need it here
		}
	}

	/**
	 * Throws an exception if anything's wrong
	 * @throws Exception $e
	 */
	public function validate()
	{
		// Check for required fields here.  Throw an exception if anything is missing.
		if (!$this->data['name']) {
			throw new Exception('missingRequiredFields');
		}
	}

	/**
	 * Saves this record back to the database
	 */
	public function save()
	{
		$this->validate();

		$mongo = Database::getConnection();
		$mongo->departments->save($this->data,array('safe'=>true));
	}

	//----------------------------------------------------------------
	// Generic Getters
	//----------------------------------------------------------------

	/**
	 * @return string Mongo's unique identifier
	 */
	public function getId()
	{
		if (isset($this->data['_id'])) {
			return $this->data['_id'];
		}
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		if (isset($this->data['name'])) {
			return $this->data['name'];
		}
	}

	/**
	 * @return array
	 */
	public function getDefaultPerson()
	{
		if (isset($this->data['defaultPerson'])) {
			return $this->data['defaultPerson'];
		}
	}

	/**
	 * @return array
	 */
	public function getCategories()
	{
		if (isset($this->data['categories'])) {
			return $this->data['categories'];
		}
		return array();
	}

	/**
	 * @return array
	 */
	public function getCustomStatuses()
	{
		if (isset($this->data['customStatuses'])) {
			return $this->data['customStatuses'];
		}
		return array();
	}
	/**
	 * @return array
	 */
	public function getActions()
	{
		if (isset($this->data['actions'])) {
			return $this->data['actions'];
		}
		return array();
	}
	//----------------------------------------------------------------
	// Generic Setters
	//----------------------------------------------------------------

	/**
	 * @param string $string
	 */
	public function setName($string)
	{
		$this->data['name'] = trim($string);
	}

	/**
	 * @param string|Person $person
	 */
	public function setDefaultPerson($person)
	{
		if (!$person instanceof Person) {
			$person = trim($person);
			if ($person) {
				$person = new Person($person);
			}
		}
		if ($person->getId()) {
			$this->data['defaultPerson'] = array(
				'_id'=>$person->getId(),
				'fullname'=>$person->getFullname(),
				'email'=>$person->getEmail()
			);
		}
		else {
			unset($this->data['defaultPerson']);
		}
	}

	/*
	 *@param array $array
	 */
	public function setCategories($categories)
	{
		if ($categories && is_array($categories)) {
			$this->data['categories'] = array();

			foreach ($categories as $id) {
				try {
					$category = new Category($id);
					$this->data['categories'][] = array(
						'_id'=>$category->getId(),
						'name'=>$category->getName()
					);
				}
				catch (Eexception $ex) {
					// Just ignore the bad ones
				}
			}
		}
	}

	/*
	 *@param string $string
	 */
	public function setCustomStatuses($string)
	{
		$this->data['customStatuses'] = array();
		foreach (explode(',',$string) as $status) {
			$status = trim($status);
			if ($status) {
				$this->data['customStatuses'][] = $status;
			}
		}
	}

	//----------------------------------------------------------------
	// Custom Functions
	// We recommend adding all your custom code down here at the bottom
	//----------------------------------------------------------------
	public function __toString()
	{
		return $this->getName();
	}
	
	/**
	 * @param string $string
	 * @return bool
	 */
	public function hasCategory($string)
	{
		foreach ($this->getCategories() as $category) {
			if ($string == $category['name']) {
				return true;
			}
		}
	}

	/**
	 * @param array $action
	 * @param int $index
	 */
	public function updateActions($action,$index=null)
	{
		if (!isset($this->data['action'])) {
			$this->data['actions'] = array();
		}
		foreach ($action as $key=>$vale) {
			$action[$key] = (string)$value;
		}
		if (isset($index) && isset($this->data['actions'][$index])) {
			$this->data['actions'][$index] = $action;
		}
		else {
			$this->data['actions'][] = $action;
		}
	}

	/**
	 * @param int $index
	 */
	public function removeAction($index)
	{
		if (isset($this->data['actions'][$index])) {
			unset($this->data['actions'][$index]);
		}
	}

	/**
	 * @param string $string
	 * @return bool
	 */
	public function hasAction($string)
	{
		foreach ($this->getActions() as $action) {
			if ($string == $action['name']) {
				return true;
			}
		}
	}

	/**
	 * @return UserList
	 */
	public function getPeople()
	{
		return new PersonList(array('department._id'=>$this->data['_id']));
	}
}
