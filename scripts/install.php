<?php
/*
 * Install Cli Script
 * This generates a json config file so the application can run.
 */

function getValue($description, &$default)
{
	fwrite(STDOUT, $description . " [" . $default . "]: ");
	$line = trim(fgets(STDIN));
	if ($line !== "")
	{
		$default = $line;
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
fwrite(STDOUT, "Done.\n");

$config = json_encode($config);
print_r($config);
?>
