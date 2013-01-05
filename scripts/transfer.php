<?php
//Transfer Cli Script

require '../classes/autoloader.php';

$cfg = new Shep_Config();
$config = $cfg->get('transfer');

$lock = fopen(SHEP_BASE_PATH . $config['pid_path'], 'c+');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
	die('already running');
}

$logger = new Shep_Txt_Logger($cfg->get('logger'), SHEP_BASE_PATH . $config['error_log_path']);
$filedb = new Shep_Db_File($cfg->get('db_file'));

$options = getopt('D');

if (!isset($options['D']))
{
	switch ($pid = pcntl_fork()) {
		case -1:
			die('unable to fork');
			break;
		case 0: // this is the child process
			break;
		default: // otherwise this is the parent process
			fseek($lock, 0);
			ftruncate($lock, 0);
			fwrite($lock, $pid);
			fflush($lock);
			exit;
			break;
	}

	if (posix_setsid() === -1) {
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

// do some long running work
$logger->logMessage('test message');
$filedb->add('test file data');
//sleep(300);
?>
