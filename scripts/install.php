<?php
//@TODO Add code in here to setup the composer dependencies: php composer.phar install
//@TODO Add cron setup to this script? (tranfer, upload)
//@TODO Also create the symlnk in public_html to the shep/public_html directory.
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
$options = getopt('i:a');

fwrite(STDOUT, "This script will create a new config.json file and other supporting files.\n\n");

$import = array();
if (isset($options['i']))
{
	$import = file_get_contents($options['i']);
	if ($import === FALSE)
	{
		die("Could not read import file.\n");
	}
	$import = json_decode($import, TRUE);
	if (!is_array($import))
	{
		die("Could not parse import file.\n");
	}
}

$config = array(
	'global' => array(
		'timezone' => array(
			'value' => "America/New_York",
			'desc' => "Timezone (enter 'PHP' to use the php default value)",
		),
	),
	'db_mongo' => array(
		'database_name' => array(
			'value' => "shep",
			'desc' => "What is the name for the mongo database",
		),
	),
	'db_file' => array(
		'file_path' => array(
			'value' => "logs/db_file",
			'desc' => "Path to the file database",
			'supporting' => TRUE,
		),
	),
	'txt_logger' => array(
		'seperator' => array(
			'value' => ",",
			'desc' => "Seperator character",
		),
		'date_format' => array(
			'value' => "YmdHis",
			'desc' => "Date format to use",
		),
	),
	'queue' => array(
		'database_path' => array(
			'value' => 'logs/queue.sqlite3',
			'desc' => 'Path to sqlite queue database',
			'supporting' => TRUE,
		),
	),
	'dao_queue' => array(
		'collection_name' => array(
			'value' => "queue",
			'desc' => "Collection name",
		),
	),
	'service_list' => array(
		'services' => array(
			'value' => array(
				"Shep_Service_Dao_Flickr",
				"Shep_Service_Dao_Youtube",
			),
			'desc' => "List of Shep_Service_Dao classes you want to use",
		),
	),
	'service_dao_flickr' => array(
		'desc' => "You can generate a flickr api key and secret at: http://www.flickr.com/services/apps/create/apply/",
		'api_key' => array(
			'value' => "",
			'desc' => "Flickr Api Key",
		),
		'secret' => array(
			'value' => "",
			'desc' => "Flickr Secret",
		),
		'auth_token' => array(
			'value' => "",
			'desc' => "Flickr Auth Token",
		),
		'public_allowed' => array(
			'value' => 0,
			'desc' => "Images should be visible to the public by default (0 or 1)",
		),
		'friend_allowed' => array(
			'value' => 1,
			'desc' => "Images should be visible to friends by default (0 or 1)",
		),
		'family_allowed' => array(
			'value' => 1,
			'desc' => "Images should be visible to family by default (0 or 1)",
		),
	),
	'service_dao_youtube' => array(
		'desc' => "You can generate a YouTube developer key and client id at: https://code.google.com/apis/youtube/dashboard/\nTo get your YouTube session token, please go to: http://127.0.0.1/~tpetty/shep/ytAuthStart/",
		'application_id' => array(
			'value' => "shep-shepUploader-0.1",
			'desc' => "GData Application Id",
		),
		'client_id' => array(
			'value' => "",
			'desc' => "GData Client Id",
		),
		'developer_key' => array(
			'value' => "",
			'desc' => "GData Developer Key",
		),
		'yt_session_token' => array(
			'value' => "",
			'desc' => "YouTube Session Token",
		),
	),
	'add' => array(
		'upload_path' => array(
			'value' => "/home/tpetty/media_queue/",
			'desc' => "Path for media files when they are uploaded.",
		),
	),
	'transfer' => array(
		'pid_path' => array(
			'value' => "logs/transfer.pid",
			'desc' => "Process id path",
			'supporting' => TRUE,
		),
		'error_log_path' => array(
			'value' => "logs/transfer_error_log",
			'desc' => "Error log path",
			'supporting' => TRUE,
		),
		'endpoint' => array(
			'value' => "http://127.0.0.1/~tpetty/shep/index.php/add",
			'desc' => "Endpoint Url",
		),
		'connect_timeout' => array(
			'value' => 2,
			'desc' => "Connect Timeout",
		),
		'new_media_paths' => array(
			'value' => array(
				"/Users/tpetty/Pictures/Eye-Fi/",
				"/Users/tpetty/Movies/Eye-Fi/",
			),
			'desc' => "New Media Paths",
		),
	),
	'uploader' => array(
		'pid_path' => array(
			'value' => "logs/uploader.pid",
			'desc' => "Process id path",
			'supporting' => TRUE,
		),
		'error_log_path' => array(
			'value' => "logs/uploader_error_log",
			'desc' => "Error log path",
			'supporting' => TRUE,
		),
	),
);

$config = getConfig($config, $import);

//Review New Config File
$answer = getValue("Do you want to review the new configuration before it's written", "No");
if (strtolower(substr($answer, 0, 1)) === "y")
{
	fwrite(STDOUT, print_r($config, TRUE));
	$answer = getValue("Does this look good", "Yes");
	if (strtolower(substr($answer, 0, 1)) === "n")
	{
		fwrite(STDOUT, "Not writing configuration file.\n");
		exit(1);
	}
}

//Write New Config File
fwrite(STDOUT, "\nWriting configuration file.\n");
$config_json = json_encode($config);
$res = file_put_contents($config_file_path, $config_json);
if ($res === FALSE)
{
	fwrite(STDOUT, "Error - Could not write configuration file.\n");
	exit(1);
}
fwrite(STDOUT, "Done.\n");

//Create other supporting files
fwrite(STDOUT, "\nCreating supporting files...\n");
createSupportingFiles($config);
fwrite(STDOUT, "Done.\n");


fwrite(STDOUT, "\nAll Done.\n");
exit(0);


function getValue($description, $default)
{
	global $options;
	if (!is_array($default))
	{
		fwrite(STDOUT, $description . " [" . $default . "]: ");
		if (!isset($options['a']))
		{
			$line = trim(fgets(STDIN));
		}
		else
		{
			$line = "";
		}
		if ($line !== "")
		{
			settype($line, gettype($default));
			return $line;
		}
	}
	else
	{
		$description .= " [";
		for ($i = 0; $i < count($default); $i++)
		{
			$description .= $default[$i];
			$description .= ($i < count($default) - 1) ? ', ' : '';
		}
		$description .= "]: ";
		fwrite(STDOUT, $description);
		$new = array();
		do {
			if (!isset($options['a']))
			{
				$line = trim(fgets(STDIN));
			}
			else
			{
				$line = "";
			}
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
	return $default;
}

function getConfig($config, $import)
{
	foreach ($config as $group=>&$options)
	{
		fwrite(STDOUT, "Configuring '" . $group . "' Options...\n");
		if (isset($options['desc']))
		{
			fwrite(STDOUT, $options['desc'] . "\n");
		}
		foreach ($options as $option=>&$data)
		{
			if ($option === 'desc')
			{
				continue;
			}
			$data['value'] = (isset($import[$group][$option]['value'])) ? $import[$group][$option]['value'] : $data['value'];
			$data['value'] = getValue($data['desc'], $data['value']);
		}
		fwrite(STDOUT, "Done.\n\n");
	}
	return $config;
}

function createSupportingFiles($config)
{
	foreach ($config as $group=>&$options)
	{
		foreach ($options as $option=>&$data)
		{
			if ($option === 'desc')
			{
				continue;
			}
			if (isset($data['supporting']))
			{
				fwrite(STDOUT, $data['value'] . "\n");
				file_put_contents($data['value'], '', FILE_APPEND);
			}
		}
	}
}
?>
