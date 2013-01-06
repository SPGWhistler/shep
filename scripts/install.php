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

fwrite(STDOUT, "This will create a new config.json file.\n\n");

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
	'dao_queue' => array(
		'collection_name' => array(
			'value' => "queue",
			'desc' => "Collection name",
		),
	),
	'php_flickr' => array(
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
	'zend_gdata' => array(
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
		'media_path' => array(
			'value' => "/home/tpetty/media_queue/",
			'desc' => "Path for media files when they are uploaded.",
		),
	),
	'transfer' => array(
		'pid_path' => array(
			'value' => "logs/transfer.pid",
			'desc' => "Process id path",
		),
		'error_log_path' => array(
			'value' => "logs/transfer_error_log",
			'desc' => "Error log path",
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
		),
		'error_log_path' => array(
			'value' => "logs/uploader_error_log",
			'desc' => "Error log path",
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


function getValue($description, $default)
{
	if (!is_array($default))
	{
		fwrite(STDOUT, $description . " [" . $default . "]: ");
		$line = trim(fgets(STDIN));
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
?>
