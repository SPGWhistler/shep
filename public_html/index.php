<?php
require 'vendor/autoload.php';
require '../classes/autoloader.php';

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
		$file = array_shift($_FILES); //Get only the first file
		switch ($file['error'])
		{
			case UPLOAD_ERR_OK: //No error
				$cfg = new Shep_Config();
				$config = $cfg->get('add');
				$db = new Shep_Db_Mongo($cfg->get('db_mongo'));
				$dao = new Shep_Dao_Queue($cfg->get('dao_queue'), $db);
				if ($dao->addToQueue($file))
				{
					if (move_uploaded_file($file['tmp_name'], $config['upload_path'] . $file['name']))
					{
						generateOutput("Accepted", 202);
					}
					else
					{
						generateOutput("Error moving file.", 400);
					}
				}
				else
				{
					generateOutput("Error adding file to queue.", 400);
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
	$db = new Shep_Db_Mongo($cfg->get('db_mongo'));
	$dao = new Shep_Dao_Queue($cfg->get('dao_queue'), $db);
	generateOutput($dao->getQueue(), 200);
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
