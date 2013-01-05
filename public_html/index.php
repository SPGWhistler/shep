<?php
function __autoload($class_name) {
	include "../classes/" . $class_name . '.php';
}
$dao = new shep_queue_dao();
echo "success";
exit;
require 'vendor/autoload.php';

$app = new \Slim\Slim();

/**
 * Add a file.
 * This requires the following php.ini settings:
 * file_uploads = true
 * upload_max_filesize = 1G
 * post_max_size = 1G
 * memory_limit = 1G
 * max_input_time = 300
 * max_execution_time = 300
 */
$app->post('/add', function () use ($app) {
	if (is_array($_FILES))
	{
		$file = array_shift($_FILES); //Get onlyt first file
		switch ($file['error'])
		{
			case UPLOAD_ERR_OK: //No error
				generateOutput("Accepted", 202);
				break;
			case UPLOAD_ERR_INI_SIZE:
				generateOutput("The uploaded file exceeds the upload_max_filesize directive in php.ini.", 413);
				break;
			case UPLOAD_ERR_FORM_SIZE:
				generateOutput("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.", 413);
				break;
			case UPLOAD_ERR_PARTIAL:
				generateOutput("The uploaded file was only partially uploaded.", 400);
				break;
			default:
				generateOutput("There was an unspecified error uploading the file.", 400);
				break;
		}
	}
	else
	{
		generateOutput("No file uploaded.", 400);
	}
});

function generateOutput($object, $status = 200)
{
	global $app;
	echo json_encode($object);
	$res = $app->response();
	$res['Content-Type'] = 'application/json';
	$res->status($status);
}

$app->run();
?>
