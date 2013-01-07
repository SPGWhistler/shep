<?php
class Shep_Queue
{
	protected $items = NULL;

	protected function load()
	{
		if (!isset($this->items))
		{
		}
	}

	public function addItem($properties = array(), $save = TRUE)
	{
		$item = new Shep_Item_Queue($properties);
		if ($item->isValid())
		{
			$this->items[] = $item;
			if ($save)
			{
				$this->save();
			}
			return TRUE;
		}
		return FALSE;
	}

	public function getItems()
	{
		$this->load();
		return $this->items;
	}

	public function getItem()
	{
		$this->load();
	}

	public function save()
	{
	}
}
?>
