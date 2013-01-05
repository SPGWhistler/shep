<?php
class shep_queue_dao extends shep_base
{
	private $config = NULL;
	private $collection = NULL;

	public function __construct()
	{
		parent::__construct();
		$this->config = $this->shep_config->get('queue');
	}

	protected function _getCollection()
	{
		if (!$this->collection)
		{
			$this->collection = $this->getDb()->getCollection($this->config['collection_name']);
		}
		return $this->collection;
	}
}
?>
