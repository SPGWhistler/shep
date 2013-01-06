<?php
class Shep_Db_File
{
	protected $contents = NULL;

	public function __construct($config)
	{
		$this->config = $config;
	}

	protected function _loadData()
	{
		if ($this->contents === NULL)
		{
			$this->contents = array();
			$lines = file(SHEP_BASE_PATH . $this->config['file_path']);
			if (is_array($lines))
			{
				foreach ($lines as $line)
				{
					$this->add($line, FALSE);
				}
			}
		}
	}

	public function getAll($reload = FALSE)
	{
		if ($reload === TRUE)
		{
			$this->contents = NULL;
		}
		$this->_loadData();
		return $this->contents;
	}

	public function length()
	{
		$this->_loadData();
		return count($this->contents);
	}

	public function add($data, $save = TRUE, $unique = FALSE)
	{
		if ($this->contents === NULL)
		{
			$this->_loadData();
		}
		$newline = trim($data);
		if ($newline !== "")
		{
			if ($unique && $this->find($data) !== FALSE)
			{
				//The same entry already exists, return true like we added it.
				return TRUE;
			}
			$this->contents[] = $newline;
			if ($save === TRUE)
			{
				return $this->save();
			}
			else
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	public function find($data)
	{
		$this->_loadData();
		return array_search($data, $this->contents);
	}

	public function remove($data, $save = TRUE)
	{
		$this->_loadData();
		$index = $this->find($data);
		if($index !== FALSE)
		{
			array_splice($this->contents, $index, 1);
			if ($save === TRUE)
			{
				return $this->save();
			}
			else
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	public function save()
	{
		$this->_loadData();
		if(file_put_contents(SHEP_BASE_PATH . $this->config['file_path'], implode("\n", $this->contents) . "\n"))
		{
			return TRUE;
		}
		return FALSE;
	}
}
?>
