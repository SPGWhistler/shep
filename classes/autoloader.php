<?php
const SHEP_BASE_PATH = '../';

spl_autoload_register(function ($class) {
	include SHEP_BASE_PATH . 'classes/' . $class . '.php';
});
?>
