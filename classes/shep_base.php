<?php
class shep_base
{
	public static $objects = array();

	public static function getObjects()
	{
		return 
	}

	public function __construct()
	{
	}

	public function __get($class)
	{
		var_dump(self::objects);
		/*
		if (!isset(self::objects[$class]))
		{
			self::objects[$class] = new $class;
		}
		*/
		//return self::objects[$class];
	}
}
?>
