<?php
/*
 * Transfer Cli Script
 * This script transfers media files to the server via http.
 */

require '../classes/autoloader.php';

//Get configuration
$cfg = new Shep_Config();
$config = $cfg->get('transfer');

//Make sure no other transfer script is already running.
$lock = fopen(SHEP_BASE_PATH . $config['pid_path'], 'c+');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
	die('already running');
}

//Initialize objects
$logger = new Shep_Txt_Logger($cfg->get('logger'), SHEP_BASE_PATH . $config['error_log_path']);
$filedb = new Shep_Db_File($cfg->get('db_file'));

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

function glob_recursive($pattern, $flags = 0) {
	$files = glob($pattern, $flags);
	if (is_array($files))
	{
		$dirs = glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT);
		if (is_array($dirs))
		{
			foreach ($dirs as $dir)
			{
				$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
			}
		}
	}
	else
	{
		$files = array();
	}
	return $files;
}
function removeDirectories($files)
{
	$new_files = array();
	foreach ($files as $file)
	{
		if (!is_dir($file))
		{
			$new_files[] = $file;
		}
	}
	return $new_files;
}

//Get list of files in directories
$files = array();
foreach ($config['new_media_paths'] as $path)
{
	if (!is_dir($path))
	{
		$logger->logMessage($path . " is not a directory.");
		die($path . " is not a directory.");
	}
	$files = array_merge($files, glob_recursive($path . "*"));
}
$files = removeDirectories($files);

//If no files found, exit.
if (!count($files))
{
	$logger->logMessage('no files found');
	exit(0);
}

//Get length of db.
$old_db_length = $filedb->length();

//Add files to db, making sure db only contains unique entries.
foreach ($files as $file)
{
	$filedb->add($file, FALSE, TRUE);
}
$filedb->save();

//Get length of db.
$new_db_length = $filedb->length();

//If the db length has changed, exit so we can wait for all files to be added.
if ($old_db_length !== $new_db_length)
{
	$logger->logMessage("found new files - exiting");
	exit(0);
}

//Start transfer now
$logger->logMessage("starting transfer of " . $new_db_length . " new files");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $config['endpoint']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
foreach ($filedb->getAll() as $file)
{
	$logger->logMessage('uploading: ' . $file);
	//@TODO
	//- Check to make sure file exists
	//- Get file name
	curl_setopt($ch, CURLOPT_POSTFIELDS, array('name' => 'file', 'file' => '@' . $file));
	$result = curl_exec($ch);
	if ($result)
	{
		$logger->logMessage('success: ' . $file);
		$filedb->remove($file);
		unlink($file);
	}
	else
	{
		$logger->logMessage('error: ' . $file);
	}
}
curl_close($ch);
$logger->logMessage('exiting');
?>
