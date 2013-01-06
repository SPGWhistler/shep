<?php
const SHEP_BASE_PATH = '../';

spl_autoload_register(function ($class) {
	$file = SHEP_BASE_PATH . 'classes/' . $class . '.php';
	if (file_exists($file))
	{
		include $file;
	}
	else
	{
		$class = str_replace('_', '/', $class);
		$file = SHEP_BASE_PATH . 'classes/' . $class . '.php';
		include($file);
	}
});
?>
