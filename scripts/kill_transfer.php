<?php
$lock = fopen('my.pid', 'c+');
if (flock($lock, LOCK_EX | LOCK_NB)) {
	die('process not running');
}
$pid = fgets($lock);

posix_kill($pid, SIGTERM);
?>
