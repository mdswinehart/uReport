<?php
/**
 * @copyright 2012-2016 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Application\Controllers;

use Application\Models\Category;
use Application\Models\CategoryTable;

use Blossom\Classes\Block;
use Blossom\Classes\Controller;
use Blossom\Classes\Template;
use Blossom\Classes\Url;

class CategoriesController extends Controller
{
	public function index()
	{
		$table = new CategoryTable;
		$list  = $table->find();

		$this->template->title = $this->template->_(['category', 'categories', count($list)]);
		$this->template->blocks[] = new Block('categories/categoryList.inc', ['categoryList'=>$list]);
	}

	public function view()
	{
		if (!empty($_REQUEST['category_id'])) {
			try {
				$category = new Category($_REQUEST['category_id']);
				$this->template->title = $category->getName();
				$this->template->blocks[] = new Block('categories/info.inc', ['category'=>$category]);
			}
			catch (\Exception $e) {
				$_SESSION['errorMessages'][] = $e;
			}
		}
	}

	public function update()
	{
		// Load the $category for editing
		if (isset($_REQUEST['category_id']) && $_REQUEST['category_id']) {
			try {
				$category = new Category($_REQUEST['category_id']);
			}
			catch (\Exception $e) {
				$_SESSION['errorMessages'][] = $e;
				header('Location: '.BASE_URL.'/categories');
				exit();
			}
		}
		else {
			$category = new Category();
		}


		if (isset($_POST['name'])) {
			try {
				$category->handleUpdate($_POST);
				$category->save();
				header('Location: '.BASE_URL.'/categories');
				exit();
			}
			catch (\Exception $e) {
				$_SESSION['errorMessages'][] = $e;
			}
		}

		$this->template->title = $category->getId()
            ? $this->template->_('category_edit')
            : $this->template->_('category_add');
		$this->template->blocks[] = new Block('categories/updateCategoryForm.inc', ['category'=>$category]);
	}

	/**
	 * A form for updating the SLA times for all categories at once
	 */
	public function sla()
	{
		if (isset($_POST['categories'])) {
			try {
				foreach ($_POST['categories'] as $id=>$post) {
					$category = new Category($id);
					$category->setSlaDays($post['slaDays']);
					$category->save();
				}
				header('Location: '.BASE_URL.'/categories');
				exit();
			}
			catch (\Exception $e) {
				$_SESSION['errorMessages'][] = $e;
			}
		}

		$t = new CategoryTable();
		$list = $t->find();

		$this->template->title = $this->template->_('service_level_agreement');
		$this->template->blocks[] = new Block('categories/slaForm.inc', ['categoryList'=>$list]);
	}
}
