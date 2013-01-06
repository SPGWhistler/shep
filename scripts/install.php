<?php
/*
 * Install Cli Script
 * This generates a json config file so the application can run.
 */

$config_file_path = 'config/config.json';

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

$config = array();

//db_mongo
fwrite(STDOUT, "Configuring Mongo Database...\n");
$config['db_mongo'] = array(
	'database_name' => 'shep'
);
getValue("What is the name for the mongo database", $config['db_mongo']['database_name']);
fwrite(STDOUT, "Done.\n");

//db_file
fwrite(STDOUT, "Configuring File Database...\n");
$config['db_file'] = array(
	'file_path' => 'logs/db_file',
	'eol' => '\n'
);
getValue("Path to the file database", $config['db_file']['file_path']);
getValue("End of line character", $config['db_file']['eol']);
fwrite(STDOUT, "Done.\n");

//txt_logger
fwrite(STDOUT, "Configuring Text Logger Class...\n");
$config['txt_logger'] = array(
	'seperator' => ',',
	'date_format' => 'YmdHis',
	'eol' => '\n'
);
getValue("Seperator character", $config['txt_logger']['seperator']);
getValue("Date format to use", $config['txt_logger']['date_format']);
getValue("End of line character", $config['txt_logger']['eol']);
fwrite(STDOUT, "Done.\n");

//dao_queue
fwrite(STDOUT, "Configuring Dao Queue Class...\n");
$config['dao_queue'] = array(
	'collection_name' => 'queue'
);
getValue("Collection name", $config['dao_queue']['collection_name']);
fwrite(STDOUT, "Done.\n");

//add
fwrite(STDOUT, "Configuring Add Endpoint...\n");
$config['add'] = array(
	'upload_path' => '/home/tpetty/uploads/'
);
getValue("Path for new uploads", $config['add']['upload_path']);
fwrite(STDOUT, "Done.\n");

//transfer
fwrite(STDOUT, "Configuring Transfer Script...\n");
$config['transfer'] = array(
	'pid_path' => 'logs/transfer.pid',
	'error_log_path' => 'logs/transfer_error_log',
	'endpoint' => 'http://127.0.0.1/~tpetty/shep/index.php/add',
	'connect_timeout' => 2,
	'new_media_paths' => array(
		"/Users/tpetty/Pictures/Eye-Fi/"
	)
);
getValue("Process id path", $config['transfer']['pid_path']);
getValue("Error log path", $config['transfer']['error_log_path']);
getValue("Endpoint Url", $config['transfer']['endpoint']);
getValue("Connect Timeout", $config['transfer']['connect_timeout']);
getArray("New Media Paths", $config['transfer']['new_media_paths']);
fwrite(STDOUT, "Done.\n");

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
