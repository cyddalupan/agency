<?php //-->

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class applicant extends Schema_Model {
 	/* Constants
	-------------------------------*/    
    protected static $TABLE    = 'applicant';
    protected static $TABLE_PK = 'applicant_id';
    
	/* Public Properties
	-------------------------------*/    
    
	/* Protected Properties
	-------------------------------*/
	/* Private Properties
	-------------------------------*/
	/* Get
	-------------------------------*/
	/* Magic
	-------------------------------*/ 
	public function __construct() 
	{
		parent::__construct( self::$TABLE, self::$TABLE_PK ); 
	}
	
	/* Public Methods
	-------------------------------*/    
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
