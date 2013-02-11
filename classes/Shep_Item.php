<?php
/*
 * This is an interface that is used below.
 * This extends Iterator so that objects that
 * extend it can be accessed via foreach loops.
 * It also extends JsonSerializable so that the
 * properties serialize to json correctly.
 */
interface Shep_Interface_Item extends Iterator, JsonSerializable
{
	public function __construct($properties = array());

	public function __set($name, $value);

	public function __get($name);

	public function __isset($name);

	public function isValid();

	public function rewind();

	public function valid();

	public function current();

	public function key();

	public function next();

	public function jsonSerialize();
}

/**
 * A Shep Item Class.
 * This is an abstract class that gives a lot of
 * built in functionality to objects that extend this class.
 * It allows each object to have a required array and an
 * optional array. Anything in required must not be NULL for
 * the isValid method to return TRUE.
 */
abstract class Shep_Item implements Shep_Interface_Item
{
	protected $required = array();

	protected $optional = array();

	protected $_all = array();

	protected $_modified = FALSE;

	public function __construct($properties = array())
	{
		foreach ($properties as $key=>$value)
		{
			$this->__set($key, $value);
		}
	}

	public function __set($name, $value)
	{
		if (array_key_exists($name, $this->required)) {
			$this->required[$name] = $value;
			$this->_modified = TRUE;
		} else if (array_key_exists($name, $this->optional)) {
			$this->optional[$name] = $value;
			$this->_modified = TRUE;
		}
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->required)) {
			return $this->required[$name];
		} else if (array_key_exists($name, $this->optional)) {
			return $this->optional[$name];
		}
		return NULL;
	}

	public function __isset($name)
	{
		return isset($this->required[$name]) || isset($this->optional[$name]);
	}

	public function isValid()
	{
		return (in_array(NULL, $this->required, TRUE)) ? FALSE : TRUE;
	}

	//Iterator Fuctions Below Here
	public function rewind()
	{
		if ($this->_modified)
		{
			$this->_all = array_merge($this->required, $this->optional);
			$this->_modified = FALSE;
		}
		reset($this->_all);
	}

	public function valid()
	{
		$key = key($this->_all);
		return (!$this->_modified && $key !== NULL && $key !== FALSE);
	}

	public function current()
	{
		return current($this->_all);
	}

	public function key()
	{
		return key($this->_all);
	}

	public function next()
	{
		return next($this->_all);
	}

	public function jsonSerialize()
	{
		$this->rewind();
		return $this->_all;
	}
}
?>
