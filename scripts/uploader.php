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

//Don't forget that this must work with videos too.

/*
$phpFlickr_config = $cfg->get('php_flickr');
$f = new phpFlickr($phpFlickr_config['api_key'], $phpFlickr_config['secret']);
$f->setToken($phpFlickr_config['auth_token']);
*/

//$result = $f->async_upload('/home/tpetty/uploads/IMG_4024.JPG', 'test_async_upload', 'test async description', NULL, 0, 1, 1);
/*
$status = $f->photos_upload_checkTickets($result);
if (isset($status[0]['photoid']))
*/

//function sync_upload ($photo, $title = null, $description = null, $tags = null, $is_public = null, $is_friend = null, $is_family = null) {

$zg_config = $cfg->get('zend_gdata');
Zend_Loader::loadClass('Zend_Gdata_YouTube');
$httpClient = Zend_Gdata_AuthSub::getHttpClient($zg_config['yt_session_token']);
$yt = new Zend_Gdata_YouTube($httpClient, $zg_config['application_id'], $zg_config['client_id'], $zg_config['developer_key']);

$logger->logMessage('exiting');
?>
