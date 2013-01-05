<?php
const SHEP_BASE_DIR = '/home/tpetty/shep/';

spl_autoload_register(function ($class) {
	include SHEP_BASE_DIR . 'classes/' . $class . '.php';
});
?>
