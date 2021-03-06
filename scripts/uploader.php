<?php
/*
 * Uploader Cli Script
 * This script uploads media files to a third party service via http.
 *
 * Usage:
 * php uploader.php [-D]
 *
 * -D Do not daemonize.
 */

require '../classes/autoloader.php';

//Make sure we can find the Zend stuff
$path = '../classes/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once 'Zend/Loader.php';

//Get configuration
$cfg = new Shep_Config();
$config = $cfg->get('uploader');

//Make sure no other uploader script is already running.
$lock = fopen(SHEP_BASE_PATH . $config['pid_path'], 'c+');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
	die('already running');
}

//Initialize objects
$logger = new Shep_Txt_Logger($cfg->get('txt_logger'), SHEP_BASE_PATH . $config['error_log_path']);
$queue = new Shep_Queue($cfg->get('queue'));
$list = new Shep_Service_List($cfg->get('service_list'), $cfg, $queue);

//Set timezone
if ($config['timezone'] !== "PHP")
{
	date_default_timezone_set($config['timezone']);
}

//Get command line options
$options = getopt('D');

$logger->logMessage('starting');

//Daemonize
if (!isset($options['D']))
{
	switch ($pid = pcntl_fork()) {
		case -1:
			$logger->logMessage('unable to fork');
			die('unable to fork');
			break;
		case 0: // this is the child process
			break;
		default: // otherwise this is the parent process
			$logger->logMessage('forking to ' . $pid);
			fseek($lock, 0);
			ftruncate($lock, 0);
			fwrite($lock, $pid);
			fflush($lock);
			exit;
			break;
	}
	if (posix_setsid() === -1) {
		$logger->logMessage('could not setsid');
		die('could not setsid');
	}
	fclose(STDIN);
	fclose(STDOUT);
	fclose(STDERR);
	$stdIn = fopen('/dev/null', 'r'); // set fd/0
	$stdOut = fopen('/dev/null', 'w'); // set fd/1
	pcntl_signal(SIGTSTP, SIG_IGN);
	pcntl_signal(SIGTTOU, SIG_IGN);
	pcntl_signal(SIGTTIN, SIG_IGN);
	pcntl_signal(SIGHUP, SIG_IGN);
}

//Loop through queue now and upload files to each service.
$items = $queue->getItems();
$total_to_upload = 0;
foreach ($items as $file)
{
	if (file_exists($file->path) && $file->uploaded !== "1")
	{
		$total_to_upload++;
	}
}
$logger->logMessage('found ' . $total_to_upload . ' items to upload.');
foreach ($items as $file)
{
	if (file_exists($file->path) && $file->uploaded !== "1")
	{
		$logger->logMessage('starting upload of ' . $file->name . ' (' . $file->id . ')');
		$service = $list->getService($file->service);
		if ($service->uploadFile($file))
		{
			$logger->logMessage('finished upload of ' . $file->name . ' (' . $file->id . ')');
		}
		else
		{
			$logger->logMessage('error uploading ' . $file->name . ' (' . $file->id . ') Message: ' . $service->getLastError());
		}
	}
}

$logger->logMessage('exiting');
?>
