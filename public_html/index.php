<?php
//Composer autoloader
require 'vendor/autoload.php';

//My autoloader
require '../classes/autoloader.php';

//Zend autoloader
$path = '../classes/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once 'Zend/Loader.php';

$app = new \Slim\Slim();

$app->get('/test', function() use ($app){
	$cfg = new Shep_Config();
	$queue = new Shep_Queue($cfg->get('queue'));
	d($queue->removeItems());
});

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
		$file = array_shift($_FILES); //Get only the first file
		switch ($file['error'])
		{
			case UPLOAD_ERR_OK: //No error
				$cfg = new Shep_Config();
				$config = $cfg->get('add');
				$queue = new Shep_Queue($cfg->get('queue'));
				$list = new Shep_Service_List($cfg->get('service_list'), $cfg, $queue);
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$fileType = finfo_file($finfo, $file['tmp_name']);
				$serviceName = $list->isSupportedFileType($fileType);
				if ($serviceName !== FALSE)
				{
					if (move_uploaded_file($file['tmp_name'], $config['upload_path'] . basename($file['tmp_name'])))
					{
						$file = array(
							'path' => $config['upload_path'] . basename($file['tmp_name']),
							'name' => $file['name'],
							'size' => $file['size'],
							'uploaded' => FALSE,
							'service' => $serviceName,
							'type' => $fileType,
						);
						if ($queue->addItem($file))
						{
							generateOutput("Accepted", 202);
						}
						else
						{
							generateOutput("Error adding file to queue.", 400);
						}
					}
					else
					{
						generateOutput("Error moving file.", 400);
					}
				}
				else
				{
					generateOutput("Unsupported file type: " . $fileType, 415);
				}
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

$app->get('/queue', function () use ($app) {
	$cfg = new Shep_Config();
	$queue = new Shep_Queue($cfg->get('queue'));
	generateOutput($queue->getItems(), 200);
});

$app->delete('/queue', function() use ($app){
	$cfg = new Shep_Config();
	$queue = new Shep_Queue($cfg->get('queue'));
	generateOutput($queue->removeItems(), 200);
});

$app->get('/ytAuthStart', function() use ($app) {
	session_start();
	$next = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '/ytAuthReturn';
	$scope = 'http://gdata.youtube.com';
	$secure = false;
	$session = true;
	$url = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
	$app->response()->redirect($url, 303);
});

$app->get('/ytAuthReturn', function() use ($app) {
	$token = $app->request()->params('token');
	if ($token !== NULL)
	{
		$sessionToken = Zend_Gdata_AuthSub::getAuthSubSessionToken($token);
		$output = "Here is your YouTube session token.<br>\n";
		$output .= "Copy the following string:<br>\n";
		$output .= "<br>\n" . $sessionToken . "<br>\n";
		$output .= "<br>\n<br>\n";
		$output .= "Paste this string into the install script when prompted for it.<br>\n";
		generateOutput($output, 200, 'html');
		return;
	}
	generateOutput('Error - token not returned correctly.', 400);
});

function generateOutput($data, $status = 200, $type = 'json')
{
	global $app;
	$res = $app->response();
	$res->status($status);
	switch ($type)
	{
		case 'json':
			echo json_encode($data);
			$res['Content-Type'] = 'application/json';
		break;
		case 'html':
		default:
			echo $data;
		break;
	}
}

$app->run();
?>
