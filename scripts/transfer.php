<?php
//Transfer Cli Script

require '../classes/autoloader.php';
$cfg = new Shep_Config();
$config = $cfg->get('transfer');

$lock = fopen(SHEP_BASE_PATH . $config['pid_path'], 'c+');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
	die('already running');
}

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
//$stdErr = fopen('php://stdout', 'w'); // a hack to duplicate fd/1 to 2
$stdErr = fopen(SHEP_BASE_PATH . $config['error_log_path'], 'a');

pcntl_signal(SIGTSTP, SIG_IGN);
pcntl_signal(SIGTTOU, SIG_IGN);
pcntl_signal(SIGTTIN, SIG_IGN);
pcntl_signal(SIGHUP, SIG_IGN);

// do some long running work
fwrite($stdErr, "stderr\n")
//sleep(300);
?>
