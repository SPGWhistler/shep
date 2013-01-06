<?php
/*
 * Install Cli Script
 * This generates a json config file so the application can run.
 *
 * Usage:
 * php install.php [-i /path/to/file]
 *
 * -i [file] Specifies a file to import the default configuration from. Must be json.
 */

$config_file_path = 'config/config.json';

//Get command line options
$options = getopt('i:');

function getValue($description, &$default)
{
	fwrite(STDOUT, $description . " [" . $default . "]: ");
	$line = trim(fgets(STDIN));
	if ($line !== "")
	{
		settype($line, gettype($default));
		$default = $line;
	}
}
function getArray($description, &$default)
{
	fwrite(STDOUT, $description . " [" . print_r($default, TRUE) . "]: ");
	$new = array();
	do {
		$line = trim(fgets(STDIN));
		if ($line !== "")
		{
			$new[] = $line;
		}
	} while ($line !== "");
	if (count($new))
	{
		$default = $new;
	}
}

$out = <<<OUT
This will create a configuration file.

OUT;
fwrite(STDOUT, $out);

if (isset($options['i']))
{
	$import = file_get_contents($options['i']);
	if ($import === FALSE)
	{
		die("Could not read import file.\n");
	}
	$config = json_decode($import, TRUE);
	if (!is_array($config))
	{
		die("Could not parse import file.\n");
	}
}
else
{
	$config = array();
}

//global
fwrite(STDOUT, "Configuring Global Options...\n");
if (!isset($config['global']))
{
	$config['global'] = array(
		'timezone' => 'America/New_York'
	);
}
getValue("Timezone (enter 'PHP' to use the php default value)", $config['transfer']['timezone']);
fwrite(STDOUT, "Done.\n");

//db_mongo
fwrite(STDOUT, "Configuring Mongo Database...\n");
if (!isset($config['db_mongo']))
{
	$config['db_mongo'] = array(
		'database_name' => 'shep'
	);
}
getValue("What is the name for the mongo database", $config['db_mongo']['database_name']);
fwrite(STDOUT, "Done.\n");

//db_file
fwrite(STDOUT, "Configuring File Database...\n");
if (!isset($config['db_file']))
{
	$config['db_file'] = array(
		'file_path' => 'logs/db_file'
	);
}
getValue("Path to the file database", $config['db_file']['file_path']);
fwrite(STDOUT, "Done.\n");

//txt_logger
fwrite(STDOUT, "Configuring Text Logger Class...\n");
if (!isset($config['txt_logger']))
{
	$config['txt_logger'] = array(
		'seperator' => ',',
		'date_format' => 'YmdHis'
	);
}
getValue("Seperator character", $config['txt_logger']['seperator']);
getValue("Date format to use", $config['txt_logger']['date_format']);
fwrite(STDOUT, "Done.\n");

//dao_queue
fwrite(STDOUT, "Configuring Dao Queue Class...\n");
if (!isset($config['dao_queue']))
{
	$config['dao_queue'] = array(
		'collection_name' => 'queue'
	);
}
getValue("Collection name", $config['dao_queue']['collection_name']);
fwrite(STDOUT, "Done.\n");

//php_flickr
fwrite(STDOUT, "Configuring Php Flickr Class...\n");
if (!isset($config['php_flickr']))
{
	$config['php_flickr'] = array(
		'api_key' => '',
		'secret' => '',
		'auth_token' => ''
	);
}
fwrite(STDOUT, "You can generate a flickr api key and secret at: http://www.flickr.com/services/apps/create/apply/\n");
getValue("Flickr Api Key", $config['php_flickr']['api_key']);
getValue("Flickr Secret", $config['php_flickr']['secret']);
fwrite(STDOUT, "TODO: Instructions on how to get this token.\n");
getValue("Flickr Auth Token", $config['php_flickr']['auth_token']);

//add
fwrite(STDOUT, "Configuring Add Endpoint...\n");
if (!isset($config['add']))
{
	$config['add'] = array(
		'upload_path' => '/home/tpetty/uploads/'
	);
}
getValue("Path for new uploads", $config['add']['upload_path']);
fwrite(STDOUT, "Done.\n");

//transfer
fwrite(STDOUT, "Configuring Transfer Script...\n");
if (!isset($config['transfer']))
{
	$config['transfer'] = array(
		'pid_path' => 'logs/transfer.pid',
		'error_log_path' => 'logs/transfer_error_log',
		'endpoint' => 'http://127.0.0.1/~tpetty/shep/index.php/add',
		'connect_timeout' => 2,
		'new_media_paths' => array(
			"/Users/tpetty/Pictures/Eye-Fi/"
		)
	);
}
getValue("Process id path", $config['transfer']['pid_path']);
getValue("Error log path", $config['transfer']['error_log_path']);
getValue("Endpoint Url", $config['transfer']['endpoint']);
getValue("Connect Timeout", $config['transfer']['connect_timeout']);
getArray("New Media Paths", $config['transfer']['new_media_paths']);
fwrite(STDOUT, "Done.\n");

//uploader
fwrite(STDOUT, "Configuring Uploader Script...\n");
if (!isset($config['uploader']))
{
	$config['uploader'] = array(
		'pid_path' => 'logs/uploader.pid',
		'error_log_path' => 'logs/uploader_error_log'
	);
}
getValue("Process id path", $config['uploader']['pid_path']);
getValue("Error log path", $config['uploader']['error_log_path']);
fwrite(STDOUT, "Done.\n");

//Review New Config File
$answer = "No";
getValue("Do you want to review the new configuration before it's written", $answer);
if (strtolower(substr($answer, 0, 1)) === "y")
{
	fwrite(STDOUT, print_r($config, TRUE));
	$answer = "Yes";
	getValue("Does this look good", $answer);
	if (strtolower(substr($answer, 0, 1)) === "n")
	{
		fwrite(STDOUT, "Not writing configuration file.\n");
		exit(1);
	}
}

//Write New Config File
fwrite(STDOUT, "Writing configuration file.\n");
$config = json_encode($config);
$res = file_put_contents($config_file_path, $config);
if ($res === FALSE)
{
	fwrite(STDOUT, "Error - Could not write configuration file.\n");
	exit(1);
}
fwrite(STDOUT, "Done.\n");
exit(0);
?>
