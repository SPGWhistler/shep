<?php
class Shep_Queue
{
	protected $items = array();
	protected $_modified = TRUE;

	public function add($properties = array())
	{
		$item = new Shep_Item_Queue($properties);
		if ($item->isValid())
		{
			$this->items[] = $item;
			return TRUE;
		}
		return FALSE;
	}

	public function get()
	{
		$this->load();
		return $this->items;
	}

	public function save()
	{
	}

	public function load()
	{
	}
}
?>
