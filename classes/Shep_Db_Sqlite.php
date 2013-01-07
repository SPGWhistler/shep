<?php
class Shep_Db_Sqlite
{
	private $database = NULL;

	public function __construct($config)
	{
		$this->config = $config;
	}

	public function getDatabase()
	{
		if ($this->database === NULL)
		{
			$this->database = new PDO('sqlite:' . $this->config['database_path']);
			$this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $this->database;
	}
}
?>
