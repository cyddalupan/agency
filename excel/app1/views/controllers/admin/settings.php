<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends Admin_Controller {
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
	}
	
	public function index()
	{ 
		show_404();
	}
	
	public function categories()
	{ 
		$this->load->model( 'm_category');

		//Form Submitted
		if ( isset( $_POST['category'], $_POST['flag'] ) && $_POST['flag'] == 'add' ) {
			
			$_SESSION['post']['admin']['settings/categories/add'] = $_POST;
		
			self::checkCategoryDataAdd();
			
			$category = $this->m_category->addCategory();
			
			if ( !empty( $category ) ) {
				Message::addSuccess('New category has been added successfully!', false, 'Success');
				redirect( site_url( 'admin/settings/categories' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/settings/categories' ) );
			exit;
		}
		
		if ( isset( $_POST['category'], $_POST['flag'] ) && $_POST['flag'] == 'edit' ) {
			
			$_SESSION['post']['admin']['settings/categories/edit'] = $_POST;
		
			self::checkCategoryDataEdit();
			
			$category = $this->m_category->updateCategory( $_POST['category']['category_id'] );
			
			if ( !empty( $category ) ) {
				Message::addSuccess('<strong>'.$category['category_name'].'</strong> has been updated!', false, 'Success');
				redirect( site_url( 'admin/settings/categories' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/settings/categories' ) );
			exit;
		}
		//endOf: Form Submitted
		
		if ( isset( $_POST['position'], $_POST['flag'] ) && $_POST['flag'] == 'add-position' ) {
			
			$_SESSION['post']['admin']['settings/categories/add-position'] = $_POST;
		
			self::checkPositionDataAdd();
			
			$this->load->model( 'm_position' );
			$position = $this->m_position->addPosition();
			
			if ( ! empty( $position ) ) {
				Message::addSuccess('New position has been added!', false, 'Success');
				redirect( site_url( 'admin/settings/categories' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/settings/categories' ) );
			exit;
		}
		//endOf: Form Submitted

		$categories = $this->m_category->getCategories();
		
		$this->scripts = [
			$this->getPath()['scripts'] . 'pages/settings/category.js',
		];
		$this->modalsTpl = 'settings/categories.modal.php';
		
		$this->setVariables([
			'categories'	 => $categories,
		])
		->setTitle('Categories')
		->renderPage('settings/categories');
	}

	public function delete_cateogry($category_id){
		$this->load->model( 'm_category');
		$this->m_category->deleteCategory($category_id);
		redirect('admin/settings/categories');
	}
	
	public function countries()
	{ 
		Pagination::init( 50 );

		$this->load->model( 'm_country');
		
		//Form Submitted
		if ( isset( $_POST['country'], $_POST['flag'] ) && $_POST['flag'] == 'add' ) {
			$_SESSION['post']['admin']['settings/countries/add'] = $_POST;
		
			self::checkCountryDataAdd();
			
			$country = $this->m_country->addCountry();
			
			if ( !empty( $country ) ) {
				Message::addSuccess('New country has been added successfully!', false, 'Success');
				redirect( site_url( 'admin/settings/countries' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/settings/countries' ) );
			exit;
		}
		
		if ( isset( $_POST['country'], $_POST['flag'] ) && $_POST['flag'] == 'edit' ) {
			
			$_SESSION['post']['admin']['settings/countries/edit'] = $_POST;
		
			self::checkCountryDataEdit();
			
			$country = $this->m_country->updateCountry( $_POST['country']['country_id'] );
			
			if ( !empty( $country ) ) {
				Message::addSuccess('<strong>'.$country['country_name'].'</strong> has been updated!', false, 'Success');
				redirect( site_url( 'admin/settings/countries' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/settings/countries' ) );
			exit;
		}
		//endOf: Form Submitted
		
		$options = [];
        
        $limit  = Pagination::getPerPage();
        $offset = Pagination::getRecordCursor();
        
		$countries      = $this->m_country->getCountries( $options, $limit, $offset );
		$countriesCount = $this->m_country->getCountriesCount( $options );

		Pagination::setOptions([
			'total-records'   => $countriesCount,
			'html'            => [
				'pagination_open_tag'  => '<ul class="pull-right pagination">',
			    'previous_open_tag'    => '<li class="prev"><a href="{link}">',
			],
		]);
		
		$this->scripts = [
			$this->getPath()['scripts'] . 'pages/settings/country.js',
		];
		
		$this->modalsTpl = 'settings/countries.modal.php';
		
		$this->setVariables([
			'countries'         => $countries,
			'pagination'        => Pagination::generateHTML(),
			'paginationCounter' => Pagination::getCounters(),
		])
		->setTitle('Countries')
		->renderPage('settings/countries');
	}

	protected static function checkCategoryDataAdd()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/settings/categories' );
		$category 	= $_POST['category'];
 
		if ( empty( $category['name']  ) ) {
			$errors[] = '* <strong>Category name</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
	
	protected static function checkCategoryDataEdit()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/settings/categories' );
		$category 	= $_POST['category'];
 
		if ( empty( $category['name']  ) ) {
			$errors[] = '* <strong>Category name</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
	
	protected static function checkCountryDataAdd()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/settings/countries' );
		$country 	= $_POST['country'];
 
		if ( empty( $country['name']  ) ) {
			$errors[] = '* <strong>Country name</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
	
	protected static function checkPositionDataAdd()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/settings/categories' );
		$position 	= $_POST['position'];
 
 		if ( empty( $position['category']  ) ) {
			$errors[] = '* <strong>Category</strong> not define.';
		}
		
		if ( empty( $position['name']  ) ) {
			$errors[] = '* <strong>Position name</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
	
	protected static function checkCountryDataEdit()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/settings/countries' );
		$country 	= $_POST['country'];
 
		if ( empty( $country['name']  ) ) {
			$errors[] = '* <strong>Country name</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
}

/* End of file settings.php */
/* Location: ./app/controllers/admin/settings.php */