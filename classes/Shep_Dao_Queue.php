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
		if ($this->collection === NULL)
		{
			$this->collection = $this->db->getCollection($this->config['collection_name']);
		}
		return $this->collection;
	}

	public function addToQueue($file_document)
	{
		try
		{
			$this->getCollection()->insert($file_document);
		}
		catch (Exception $e)
		{
			return FALSE;
		}
		return TRUE;
	}

	public function removeFromQueue($file_document)
	{
		try
		{
			$this->getCollection()->remove($file_document);
		}
		catch (Exception $e)
		{
			return FALSE;
		}
		return TRUE;
	}

	public function updateQueue($file_document)
	{
		if (isset($file_document['_id']))
		{
			try
			{
				$this->getCollection()->update(array('_id' => $file_document['_id']), $file_document);
				return TRUE;
			}
			catch (Exception $e)
			{
			}
		}
		return FALSE;
	}

	public function getQueue($fields = array())
	{
		$cursor = $this->getCollection()->find($fields);
		return iterator_to_array($cursor);
	}
}
?>
