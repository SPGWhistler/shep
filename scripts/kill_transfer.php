<?php
//Kill Transfer Cli Script

require '../classes/autoloader.php';
$cfg = new Shep_Config();
$config = $cfg->get('transfer');

$lock = fopen(SHEP_BASE_DIR . $config['pid_path'], 'c+');
if (flock($lock, LOCK_EX | LOCK_NB)) {
	die('process not running');
}
$pid = fgets($lock);

posix_kill($pid, SIGTERM);
?>
