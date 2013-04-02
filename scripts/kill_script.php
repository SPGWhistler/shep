<?php
//Kill Cli Script

$options = getopt('s:');
if (!isset($options['s']))
{
	die('Must specify a script to kill with the "s" option.');
}
switch (strtolower($options['s']))
{
	case 'transfer':
		$script = 'transfer';
	break;
	case 'uploader':
		$script = 'uploader';
	break;
	default:
		die('Invalid script type.');
	break;
}

require '../classes/autoloader.php';
$cfg = new Shep_Config();
$config = $cfg->get($script);

$lock = fopen(SHEP_BASE_PATH . $config['pid_path'], 'c+');
if (flock($lock, LOCK_EX | LOCK_NB)) {
	die('process not running');
}
$pid = fgets($lock);

posix_kill($pid, SIGTERM);
?>
