<?php //-->

//namespace Application;

class Uploader {

	const FILE_NAME_ORIGINAL           = 1;
	const FILE_EXTENSION_ORIGINAL      = 2;
	const FILE_EXTENSION_UPPERCASE     = 3;
	const FILE_EXTENSION_LOWERCASE     = 4;
	const FILE_EXTENSION_CASE_ORIGINAL = 5;
	const FILE_EXISTS_OVERWRITE        = 6;
	const FILE_EXISTS_RENAME           = 7;

	private static $FILE_MAX_SIZE = 1024;
	private static $MESSAGE_FILESIZE_EXCEEDED = 'File size exceeded.'; 

	protected static $error = '';

	private static $fileInfo = [
		'name'            => '',
		'type'            => '',
		'tmp_name'        => '',
		'path'            => '',
		'error'           => 0,
		'size'            => 0,		
		'extension'       => '',
		'mime'            => '',
		'photo-dimension' => '',
	];

	protected static $settings = [
		'dir-destination'    => DIRECTORY_SEPARATOR,
		//This part is optional
		'dir-permission'     => '0777', //str_pad
		'filename'           => self::FILE_NAME_ORIGINAL,
		'allowed-file-types' => [],//'jpg|jpeg|gif|png|bmp',//Separated by bar
		'extension'          => Uploader::FILE_EXTENSION_ORIGINAL,
		'extension-case'     => Uploader::FILE_EXTENSION_CASE_ORIGINAL,//,Uploader::FILE_EXTENSION_UPPERCASE, //Uploader::FILE_EXTENSION_LOWERCASE
		'max-file-size'      => false,
		'file-exists'        => Uploader::FILE_EXISTS_OVERWRITE,//Uploader::FILE_EXISTS_RENAME	
	];

	public static function init()
	{

	}

	public static function setOptions( array $options )
	{
		//$extension = pathinfo( $file, PATHINFO_EXTENSION);

		foreach ( $options as $key => $value) {
			if ( ! isset( self::$settings[$key] ) ) {
				throw new Exception( 'Uploader errr: Key \''.$key.'\ is unrecognizable.', 1);
			}
		}

		if ( ! isset( $options['dir-destination'] ) ) {
			throw new Exception( 'Uploader error: Destination directory is required.', 1);
		}

		//Put trailing slash if not present
		if ( isset( $options['dir-destination'] ) ) {
			$options['dir-destination'] = rtrim( $options['dir-destination'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		}

		//Make allowed file types expression into an array
		if ( isset( $options['allowed-file-types'] ) 
			&& ! is_array( $options['allowed-file-types'] ) 
			) {

			$options['allowed-file-types'] = explode( '|', $options['allowed-file-types'] );
			$options['allowed-file-types'] = array_filter( $options['allowed-file-types'] );
			
		}

		//Truncate default and new settings
		self::$settings = self::array_merge_recursive_distinct( self::$settings, $options);

	}

	public static function upload( $file, $options = null )
	{
		if ( ! is_null( $options ) ) {
			self::setOptions( $options );
		}

		self::$fileInfo = self::array_merge_recursive_distinct( self::$fileInfo, $file );

		$settings = self::$settings;

		$filename  = pathinfo( $file['name'], PATHINFO_FILENAME);
		$extension = pathinfo( $file['name'], PATHINFO_EXTENSION);

		if ( count( $settings['allowed-file-types'] ) > 0 
			&& ! in_array( $extension, $settings['allowed-file-types'] ) 
			) {
			self::$error = 'File extension \''.$extension.'\' is now allowed.';
			return false;
		}

		if ( self::checkFileSize( $file ) ) {
			self::$error = self::$MESSAGE_FILESIZE_EXCEEDED;
			return false;
		}

		if ( $settings['filename'] !== self::FILE_NAME_ORIGINAL ) {
			$filename = $settings['filename'];
		}

		if ( $settings['extension'] !== self::FILE_EXTENSION_ORIGINAL ) {
			$extension = $settings['extension'];
		}

        if ( $settings['extension-case'] === self::FILE_EXTENSION_UPPERCASE ) {
        	$extension = strtoupper( $extension );
        } elseif ( $settings['extension-case'] === self::FILE_EXTENSION_LOWERCASE ) {
        	$extension = strtolower( $extension );
        }
        
        if ( ! is_dir( $settings['dir-destination'] ) ) {
        	mkdir( $settings['dir-destination'], $settings['dir-permission'], true );
        }

        if ( ! is_writable( $settings['dir-destination'] ) ) {
        	self::$error = 'The destination directory is not rewritable. Please check the access permission.';
        	return false;
        	//chmod( $settings['dir-destination'], 0777 );
        }
        
        $uploaded = move_uploaded_file( $file['tmp_name'], $settings['dir-destination'].$filename.'.'.$extension );

        if ( $uploaded ) {
        	self::$fileInfo['extension'] = $extension;
        	self::$fileInfo['path']      = $settings['dir-destination'].$filename.'.'.$extension;
        }

        return $uploaded;
	}

	public static function getError()
	{
		return self::$error;
	}

	public static function getPath()
	{
		return self::$fileInfo['path'];
	}

	public static function getFileInfo()
	{
		return self::$fileInfo;
	}	

	protected static function checkFileSize( $file )
	{
		return $file['size'] <= self::$FILE_MAX_SIZE;
	}

	protected static function &array_merge_recursive_distinct( array &$array1, &$array2 = null )
	{
	  	if ( ! is_array( $array2 ) || empty( $array2 ) ) {
	  		return $array1;
	  	}

	  	foreach ( $array2 as $key => $item ) {
	  		if ( is_array( $item ) ) {
	  			$array1[$key] = isset( $array1[$key] ) && is_array( $array1[$key] ) 
				  				? self::array_merge_recursive_distinct( $array1[$key], $item ) 
				  				: $item;
	  			continue;
	  		}

	  		$array1[$key] = $item;
	  	}


	  	return $array1;
	}
}
/*
$file = $_FILES['file'];

Uploader::init();
Uploader::setOptions([
	'dir-destination'    => __DIR__.'/uploads/',
	//This part is optional
	'dir-permission'     => '0777', //str_pad
	'filename'       => '00001-resume',
	'allowed-file-types' => 'jpg|jpeg|gif|png|bmp',//Separated by bar
	'extension'      => Uploader::FILE_EXTENSION_ORIGINAL,
	'extension-case' => Uploader::FILE_EXTENSION_UPPERCASE, //Uploader::FILE_EXTENSION_LOWERCASE
	'max-file-size'      => false,
	'file-exists'        => Uploader::FILE_EXISTS_OVERWRITE,//Uploader::FILE_EXISTS_RENAME	
]);


if ( ! Uploader::upload( $file ) )
	$errors = Uploader::getErrors();
}

$filePath = Uploader::getPath();
$file     = Uploader::getFileInfo();
*/