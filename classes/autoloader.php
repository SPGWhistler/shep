<?php
const SHEP_BASE_PATH = '/home/tpetty/shep/';

spl_autoload_register(function ($class) {
	include SHEP_BASE_PATH . 'classes/' . $class . '.php';
});
?>
