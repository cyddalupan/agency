<?php //-->

include __DIR__.'/Uploader.php';

//use \Application\Uploader as Uploader;


if ( isset( $_FILES['file'] )) {
	$file = $_FILES['file'];

	Uploader::init();
	Uploader::setOptions([
		'dir-destination'    => __DIR__,
		'filename'           => time(),
		'allowed-file-types' => 'docx',
		//'extension'          => 'docx',
		'extension-case'     => Uploader::FILE_EXTENSION_UPPERCASE,
	]);

	$errors = [];
	if ( ! Uploader::upload( $file ) ) {
		$errors = Uploader::getError();
	}

	$filePath = Uploader::getPath();
	$file     = Uploader::getFileInfo();

	var_dump($errors);
	var_dump($filePath);
	var_dump($file);
}

?>

<form method="post" enctype="multipart/form-data">
<input type="file" name="file">
<br>
<button type="submit">Upload</button>
</form>

 