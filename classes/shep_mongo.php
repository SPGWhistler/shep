<?php
class shep_mongo extends shep_base
{
	private $config = NULL;
	private $connection = NULL;
	private $database = NULL;
	private $collections = array();

	public function __construct()
	{
		parent::__construct();
		$this->config = $this->getConfig()->get('db');
	}

	protected function _getConnection()
	{
		if (!$this->connection)
		{
			$this->connection = new MongoClient();
		}
		return $this->connection;
	}

	protected function _getDatabase()
	{
		if (!$this->database)
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
