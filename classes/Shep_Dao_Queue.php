<?php
class Shep_Dao_Queue
{
	private $collection = NULL;

	public function __construct($config, $db)
	{
		$this->config = $config;
		$this->db = $db;
	}

	public function getCollection()
	{
		if (!$this->collection)
		{
			$this->collection = $this->db->getCollection($this->config['collection_name']);
		}
		return $this->collection;
	}

	public function addToQueue($file_document)
	{
		return $this->getCollection()->insert($file_document);
	}

	public function getQueue()
	{
		$cursor = $this->getCollection()->find();
		return iterator_to_array($cursor);
	}
}
?>
