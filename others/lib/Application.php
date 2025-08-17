<?php //-->

namespace Application;


class Message {
		
	public static function add($type, $message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> $type,
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addSuccess($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> 'success',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}	
	
	public static function addInfo($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> 'info',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addWarning($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> 'warning',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addDanger($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> 'danger',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addDefault($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'] = [
			'type'		=> 'default',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}

	public static function addModal($type, $message, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'] = [
			'type'		=> $type,
			'message'	=> $message,
			'timeout'	=> false,
			'title'		=> $title,
		];
	}

	public static function addModalSuccess($message, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'success',
			'message'	=> $message,
			'timeout'	=> false,
			'title'		=> $title,
		];
	}

	public static function addModalInfo($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'info',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addModalWarning($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'warning',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addModalDanger($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'danger',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addModalDefault($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'default',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function all()
	{
		self::startSession();

		$messages = [];
		
		if ( isset( $_SESSION['alerts'] ) ) {
			$messages['alerts'] = $_SESSION['alerts'];
			unset( $_SESSION['alerts'] );
		}
		
		if ( isset( $_SESSION['modals'] ) ) {
			$messages['modals'] = $_SESSION['modals'];
			unset( $_SESSION['modals'] );
		}
		
		return $messages;
	}
	
	protected static function startSession()
	{
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
	}
}

class Pagination {
	/* Constants
	-------------------------------*/
	/* Public Properties
	-------------------------------*/
	
	/* Protected Properties
	-------------------------------*/
	protected static $pageCursor = 0;

	/**
	 * The pre-defined settings of our pagination.
	 * You can override any of these elements
	 *
	 * @var array
	 */
	protected static $settings = array(
		'total-records'     => 0,
		'current-records'   => 0,
		'per-page'          => 0,
		'query-string'      => '',
		'html'              => array(),
	);
	
	/* Private Properties
	-------------------------------*/
	private static $totalPages = 0;

	/* Get
	-------------------------------*/
	/* Magic
	-------------------------------*/ 
	/* Public Methods
	-------------------------------*/
	

	/**
	 * Method to initialize our class
	 *
	 * @return void
	 */	

	public static function init( $perPage )
	{
		self::$totalPages = 0;
		self::$pageCursor = 0;
		self::$settings = array(
			'total-records'     => 0,
			'current-records'   => 0,
			'per-page'          => $perPage,
			'query-string'      => '',
			'html'              => array(
				'pagination_open_tag'   => '<ul class="pagination">',
				'pagination_close_tag'  => '</ul>',
				'first_open_tag'        => '<li><a href="{link}">',
				'first_text'            => '&laquo;',
				'first_close_tag'       => '</a></li>',
				'previous_open_tag'     => '<li><a href="{link}">',
				'previous_text'         => 'Prev',
				'previous_close_tag'    => '</a></li>',
				'digit_open_tag'        => '<li><a href="{link}">',
				'digit_close_tag'       => '</a></li>',
				'active_open_tag'       => '<li class="disabled active"><a href="javascript:void();">',
				'active_close_tag'      => '</a></li>',
				'next_open_tag'         => '<li><a href="{link}">',
				'next_text'             => 'Next',
				'next_close_tag'        => '</a></li>',
				'last_open_tag'         => '<li><a href="{link}">',
				'last_text'             => '&raquo;',
				'last_close_tag'        => '</a></li>',
			),
		);

		//Get page cursor
		self::$pageCursor = isset( $_GET['page'] ) && is_numeric( $_GET['page'] ) ? $_GET['page'] : 1;
	}

	/**
	 * Method to override our pagination settings
	 *
	 * @param array $options
	 * @return void
	 */
	public static function setOptions( array $options )
	{	
		if ( ! self::$settings['per-page'] ) {
			throw new Exception( 'You haven\'t initalized the pagination with a per-page value. 
                                  Please put '.__CLASS__.'::init( <per-page> ) on the top of your method.' );
		}

		//Truncate options
		//$settings = array_merge_recursive( self::$settings, $options );
		$settings = self::array_merge_recursive_distinct( self::$settings, $options );
		
		//Get query string
		if ( empty( self::$settings['query-string'] ) ) {
			$settings['query-string'] = $_SERVER['QUERY_STRING'];
		}
		
		// Remove 'page' variable from query string
		if ( ! empty( $settings['query-string'] ) ) {
            
			parse_str( $settings['query-string'], $vars );
			
			if ( isset( $vars['page'] ) ) {
				unset( $vars['page'] );
			}
            
            $queryString = http_build_query( $vars );
			
            //No leading ampersand
			$queryString = ! empty( $queryString ) ? '&'.ltrim( $queryString, '&' ) : '';
            
			self::$settings['query-string'] = $settings['query-string'] = $queryString;
		}

		//Compute total pages
		self::$totalPages = ceil( $settings['total-records'] / $settings['per-page'] );

		//Initially, make the current records count equals to 'per-page' value
		if ( $settings['total-records'] > 0) {
			$settings['current-records'] = $settings['per-page'];
		}

		//If the cursor is on the last position, make the remaining records count as our current records count
		if ( self::$pageCursor == self::$totalPages && $settings['total-records'] % $settings['per-page'] > 0 ) {
			$settings['current-records'] = $settings['total-records'] % $settings['per-page'];
		}

		//If the page cursor is greather that the total number of pages, move cursor to last position
		if ( self::$pageCursor > self::$totalPages ) {
			self::$pageCursor = self::$totalPages;
		}

		self::$settings = $settings;
	}

	/**
	 * Method to get the current record position
	 *
	 * @return void
	 */
	public static function getRecordCursor()
	{
		return ( self::$pageCursor - 1 ) * self::$settings['per-page'];
	}

	/**
	 * Method to get the records count per page
	 *
	 * @return void
	 */
	public static function getPerPage()
	{
		return self::$settings['per-page'];
	}

	/**
	 * Method to generate our pagination HTML tags
	 *
	 * @return string
	 */
	public static function generateHTML()
	{
		$pages      = self::$totalPages;
		$pageCursor = self::$pageCursor;
		$settings   = self::$settings;
		$tags       = $settings['html'];

		//New line
		$nL         = sprintf( "\n" );

		//Our variable to handle HTMLs
		$string     = '';

		if ( $pages <= 1 && $settings['current-records'] <= $settings['total-records'] ) {
			return $string;
		}

		//Append Wrapper Opening
		$string .= $tags['pagination_open_tag'] . $nL;

		if ( $pageCursor > 1 ) {
			//Append 'First' button
			$link    = '?page=1' . $settings['query-string'];
			$string .= str_replace( '{link}', $link, $tags['first_open_tag'] );
			$string .= $tags['first_text'];
			$string .= $tags['first_close_tag'] . $nL;

			//Append 'Previous' button
			$link    = '?page=' . ( $pageCursor - 1 ) . $settings['query-string'];
			$string .= str_replace( '{link}', $link, $tags['previous_open_tag'] );
			$string .= $tags['previous_text'];
			$string .= $tags['previous_close_tag'] . $nL;
		}

		for ( $page = 1; $page <= $pages; $page++ ) {			
			$link = '?page=' . $page . $settings['query-string'];

			if ( $page == $pageCursor ) {
				$string .= $tags['active_open_tag'];
				$string .= $page;
				$string .= $tags['active_close_tag'] . $nL;
				continue;
			}

			$string .= str_replace( '{link}', $link, $tags['digit_open_tag'] );
			$string .= $page;
			$string .= $tags['digit_close_tag'] . $nL;
		}
		
		if ( $pageCursor < $pages ) {
			//Append 'Next' button
			$link    = '?page='. ( $pageCursor + 1 ) . $settings['query-string'];
			$string .= str_replace( '{link}', $link, $tags['next_open_tag'] );
			$string .= $tags['next_text'];
			$string .= $tags['next_close_tag'] . $nL;

			//Append 'Last' button
			$link    = '?page=' . $pages . $settings['query-string'];
			$string .= str_replace( '{link}', $link, $tags['last_open_tag'] );
			$string .= $tags['last_text'];
			$string .= $tags['last_close_tag'] . $nL;
		}

		//Append Wrapper Closing
		$string .= $tags['pagination_close_tag'] . $nL;

		return $string;
	}

	/**
	 * Method to generate our pagination counters
	 *
	 * @return array
	 */
	public static function getCounters()
	{
		$settings   = self::$settings;
		$pageCursor = self::$pageCursor;

		$from = $to = 0;

		if ( $settings['current-records'] > 0) {
			if ( $pageCursor == 1 ) {
				$from   = 1;
				$to     = $settings['current-records'];
			} else {
				$from = ( ($pageCursor - 1) * $settings['per-page'] ) + 1;
				$to   = ( ($pageCursor - 1) * $settings['per-page'] ) + $settings['current-records'];
			}
		}

		return array(
			'from'          => $from,
			'to'            => $to,
			'page'          => $pageCursor,
			'records'       => $settings['current-records'],
			'total-records' => $settings['total-records'],
		);
	}

	/* Protected Methods
	-------------------------------*/

	/* 
	 * @param array $array1
	 * @param mixed $array2
	 * @return array
	 */
	protected static function &array_merge_recursive_distinct( array &$array1, &$array2 = null )
	{
        if ( ! is_array( $array2 ) ) {
            return $array1;
        }
        
        foreach ( $array2 as $key => $item ) {
            if ( is_array( $item ) ) {
                $array1[$key] = is_array( $array1[$key] ) ? self::array_merge_recursive_distinct( $array1[$key], $item ) : $item;
                continue;
            }
            
            $array1[$key] = $item;
        }
        
        return $array1;
	}
}



class Uploader {
	
	protected static $response = [
		'error'		=> 'Failed',
		'status'	=> 0,
	];
	 
	public static function upload( $file, $options = [] )
	{
		$settings = [
			'saveAs'				=> '',
			'allowed_characters'	=> [],
			'max_file_size'			=> 4000,			
		];
		
		$fileInfo = [
			'extension'	=> pathinfo( $file['name'], PATHINFO_EXTENSION ),
			'size'		=> $filesize['size'],
		];
		
		$settings = array_merge( $settings, $options );
		
		if ( ! in_array( $fileInfo['extension'], $settings['allowed_characters'] ) ) {
			self::$response = [
				'error'	=> ' Unknown Image extension ',
				'status'	=> 0,
			];
			
			return self::$response;
		}
		
		if ($fileInfo['size'] > $settings['max_file_size'] * 1024) {			
			self::$response = [
				'error'	=> ' You have exceeded the size limit ',
				'status'	=> 0,
			];
			
			return self::$response;
		} 
		
		$uploaded = move_uploaded_file ( $file['tmp_name'], $settings['saveAs'] )  ;
		
		if ( $uploaded ) {
			self::$response = [
				'message'		=> 'Success',
				'status'		=> 1,
			];
		}
		
		return self::$response;
	}
	
	public static function createCustom( $width = 80, $height = 100 )
	{
		return;
	}
	
	public static function createThumbnail()
	{
		return;
	}
	
}
 