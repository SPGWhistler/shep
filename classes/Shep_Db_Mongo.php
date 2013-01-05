<?php
class Shep_Db_Mongo
{
	private $connection = NULL;
	private $database = NULL;
	private $collections = array();

	public function __construct($config)
	{
		$this->config = $config;
	}

	protected function _getConnection()
	{
		if ($this->connection === NULL)
		{
			$this->connection = new MongoClient();
		}
		return $this->connection;
	}

	protected function _getDatabase()
	{
		if ($this->database === NULL)
		{
			$this->database = $this->_getConnection()->selectDB($this->config['database_name']);
		}
		return $this->database;
	}

	public function getCollection($name)
	{
		if (!isset($this->collections[$name]))
		{
			$this->collections[$name] = $this->_getDatabase()->selectCollection($name);
		}
		return $this->collections[$name];
	}
}
?>
