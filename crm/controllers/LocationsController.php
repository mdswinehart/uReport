<?php
/**
 * @copyright 2012 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 * @author Cliff Ingham <inghamn@bloomington.in.gov>
 */
class LocationsController extends Controller
{
	/**
	 * Search Locations
	 */
	public function index()
	{
		$this->template->setFilename('locations');

		if ($this->template->outputFormat=='html') {
			$findForm = new Block('locations/findLocationForm.inc');
			if (isset($_GET['return_url'])) {
				$findForm->return_url = $_GET['return_url'];
			}
			$this->template->blocks['location-panel'][] = $findForm;
		}

		if (isset($_GET['location'])) {
			$results = new Block(
				'locations/findLocationResults.inc',
				array(
					'results'=>Location::search($_GET['location'])
				)
			);
			if (isset($_GET['return_url'])) {
				$results->return_url = $_GET['return_url'];
			}

			if ($this->template->outputFormat=='html') {
				$this->template->blocks['location-panel'][] = $results;
			}
			else {
				$this->template->blocks[] = $results;
			}
		}
	}

	/**
	 * View a single location
	 */
	public function view()
	{
		// Make sure we have the location in the system
		$location = trim($_GET['location']);
		if (!$location) {
			header('Location: '.BASE_URL.'/locations');
			exit();
		}
		$ticketList = new TicketList(array('location'=>$location));

		$filename = isset($_GET['partial']) ? 'partial' : 'locations';
		$this->template->setFilename($filename);

		$this->template->blocks['location-panel'][] = new Block(
			'locations/locationInfo.inc',
			array('location'=>$location,'disableButtons'=>isset($_GET['disableButtons']))
		);
		if (!isset($_GET['disableLinks']) && userIsAllowed('tickets','add')) {
			$this->template->blocks['location-panel'][] = new Block(
				'tickets/addNewForm.inc',
				array('title'=>'Report New Case')
			);
		}
		$this->template->blocks['location-panel'][] = new Block(
			'tickets/ticketList.inc',
			array(
				'ticketList'=>$ticketList,
				'title'=>'Cases Associated with this Location',
				'disableLinks'=>isset($_GET['disableLinks']),
				'disableButtons'=>isset($_GET['disableButtons'])
			)
		);
	}

	/**
	* Displays a single block
	*
	* This function is a mirror of ::index()
	* It responds to the same requests, but also lets you specify
	* a single block to to output.
	*/
	public function partial()
	{
		$block = new Block($_GET['partial']);

		if (isset($_GET['return_url'])) {
			$block->return_url = $_GET['return_url'];
		}
		if (isset($_GET['location'])) {
			$block->results = Location::search($_GET['location']);
		}

		$this->template->setFilename('partial');
		$this->template->blocks[] = $block;
	}
}