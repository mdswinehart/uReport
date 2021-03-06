<?php
/**
 * @copyright 2011-2018 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Application\Models;

use Application\ActiveRecord;
use Application\TableGateway;
use Zend\Db\Sql\Select;

class TicketTable extends TableGateway
{
	/**
	 * The set of fields we want to display in search results by default
	 */
	public static $defaultFieldsToDisplay = [
		'enteredDate'=>'on', 'location'=>'on', 'description'=>'on', 'category_id'=>'on', 'status'=>'on'
	];

	public function __construct() { parent::__construct('tickets', __namespace__.'\Ticket'); }

	/**
	 * @param array $fields
	 * @param string|array $order Multi-column sort should be given as an array
	 * @param bool $paginated Whether to return a paginator or a raw resultSet
	 * @param int $limit
	 */
	public function find($fields=null, $order='tickets.enteredDate desc', $paginated=false, $limit=null)
	{
		$select = new Select('tickets');
		$select->join(['c'=>'categories'], 'tickets.category_id=c.id', [], $select::JOIN_LEFT);

		if ($fields) {
			foreach ($fields as $key=>$value) {
				if ($value) {
					switch ($key) {
                        case 'start_date':
                        case 'end_date':
                        case 'lastModified_before':
                        case 'lastModified_after':
                            if (get_class($value) !== 'DateTime') { throw new \Exception('invalidDate'); }
                            $datetime = $value->format(ActiveRecord::MYSQL_DATETIME_FORMAT);

                            switch ($key) {
                                case          'start_date': $select->where("tickets.enteredDate  >= '$datetime'"); break;
                                case            'end_date': $select->where("tickets.enteredDate  <= '$datetime'"); break;
                                case 'lastModified_before': $select->where("tickets.lastModified <= '$datetime'"); break;
                                case  'lastModified_after': $select->where("tickets.lastModified >= '$datetime'"); break;
                            }
                        break;

						case 'bbox':
							$bbox = explode(',', $value);
							if (count($bbox) == 4) {
								$minLat  = (float)$bbox[0];
								$minLong = (float)$bbox[1];
								$maxLat  = (float)$bbox[2];
								$maxLong = (float)$bbox[3];
								$select->where('tickets.latitude is not null and tickets.longitude is not null');
								$select->where("tickets.latitude  > $minLat" );
								$select->where("tickets.longitude > $minLong");
								$select->where("tickets.latitude  < $maxLat" );
								$select->where("tickets.longitude < $maxLong");
							}

							break;
						default:
							$select->where(["tickets.$key"=>$value]);
					}
				}
			}
		}
		// Only get tickets for categories this user is allowed to see
		if (!isset($_SESSION['USER'])) {
			$select->where("c.displayPermissionLevel='anonymous'");
		}
		elseif (   $_SESSION['USER']->getRole()!='Staff'
				&& $_SESSION['USER']->getRole()!='Administrator') {
			$select->where("c.displayPermissionLevel in ('public','anonymous')");
		}


		return parent::performSelect($select, $order, $paginated, $limit);
	}

	/**
	 * Returns fields that can be displayed in ticketList and searchResults
	 *
	 * The key should be the fieldname in the database
	 * The value should be a nice, readable label for the field
	 *
	 * @return array
	 */
	public static function getDisplayableFields()
	{
		$fields = [
			'id'                => 'Case #',
			'enteredDate'       => 'Case Date',
			'enteredByPerson_id'=> 'Entered By',
			'assignedPerson_id' => 'Assigned To',
			'category_id'       => 'Category',
			'department_id'     => 'Department',
			'status'            => 'Status',
			'substatus_id'      => 'Substatus',
			'location'          => 'Location',
			'latitude'          => 'Latitude',
			'longitude'         => 'Longitude',
			'city'              => 'City',
			'state'             => 'State',
			'zip'               => 'Zip',
			'description'       => 'Description',
			'slaPercentage'     => 'SLA'
		];
		foreach (AddressService::$customFieldDescriptions as $key=>$value) {
			$fields[$key] = $value['description'];
		}
		return $fields;
	}

	/**
	 * Returns fields this class knows how to search for
	 *
	 * These should match the fieldnames in the database
	 *
	 * @return array
	 */
	public static function getSearchableFields()
	{
		$fields = [
			'enteredDate', 'description',
			'enteredByPerson_id', 'assignedPerson_id', 'reportedByPerson_id',
			'category_id', 'department_id', 'client_id',
			'status', 'substatus_id',
			'location', 'latitude', 'longitude',
			'city', 'state', 'zip',
			'start_date',
			'end_date',
			'label_id', 'issueType_id'
		];
		foreach (AddressService::$customFieldDescriptions as $key=>$value) {
			$fields[$key] = $key;
		}
		return $fields;
	}

	/**
	 * Tells whether the given request has fields that are searchable
	 *
	 * @param array $request Usually the raw $_REQUEST
	 * @return bool
	 */
	public static function isValidSearch($request)
	{
		return count(array_intersect(
			array_keys(self::getSearchableFields()),
			array_keys($request)
		)) ? true : false;
	}

}
