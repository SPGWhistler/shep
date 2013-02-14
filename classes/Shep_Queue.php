<?php
class Shep_Queue
{
	protected $items = NULL;
	protected $db = NULL;

	public function __construct($config)
	{
		$this->config = $config;
	}

	protected function getDb()
	{
		if (!isset($this->db))
		{
			$this->db = new PDO('sqlite:' . SHEP_BASE_PATH . $this->config['database_path']);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $this->db;
	}

	protected function load($force = FALSE)
	{
		if ($force || !isset($this->items))
		{
			$this->items = array();
			$this->getDb()->exec("CREATE TABLE IF NOT EXISTS queue (
				id INTEGER PRIMARY KEY,
				path TEXT,
				name TEXT,
				size INTEGER,
				uploaded INTEGER,
				service TEXT,
				type TEXT,
				title TEXT,
				description TEXT,
				tags TEXT,
				category TEXT,
				public_allowed INTEGER,
				friend_allowed INTEGER,
				family_allowed INTEGER,
				upload_token TEXT
			)");
			$result = $this->getDb()->query('SELECT * FROM queue', PDO::FETCH_CLASS, 'Shep_Item_Queue');
			foreach ($result as $item)
			{
				$this->items[] = $item;
			}
		}
	}

	public function addItem($properties)
	{
		$this->load();
		$item = (is_array($properties)) ? new Shep_Item_Queue($properties) : $properties;
		if ($item->isValid())
		{
			$insert_sql = "INSERT OR REPLACE INTO queue (
				id,
				path,
				name,
				size,
				uploaded,
				service,
				type,
				title,
				description,
				tags,
				category,
				public_allowed,
				friend_allowed,
				family_allowed,
				upload_token
			) VALUES (
				:id,
				:path,
				:name,
				:size,
				:uploaded,
				:service,
				:type,
				:title,
				:description,
				:tags,
				:category,
				:public_allowed,
				:friend_allowed,
				:family_allowed,
				:upload_token
			)";
			$insert_stmt = $this->getDb()->prepare($insert_sql);
			$insert_stmt->bindValue(':id', $item->id);
			$insert_stmt->bindValue(':path', $item->path);
			$insert_stmt->bindValue(':name', $item->name);
			$insert_stmt->bindValue(':size', $item->size);
			$insert_stmt->bindValue(':uploaded', $item->uploaded);
			$insert_stmt->bindValue(':service', $item->service);
			$insert_stmt->bindValue(':type', $item->type);
			$insert_stmt->bindValue(':title', $item->title);
			$insert_stmt->bindValue(':description', $item->description);
			$insert_stmt->bindValue(':tags', $item->tags);
			$insert_stmt->bindValue(':category', $item->category);
			$insert_stmt->bindValue(':public_allowed', $item->public_allowed);
			$insert_stmt->bindValue(':friend_allowed', $item->friend_allowed);
			$insert_stmt->bindValue(':family_allowed', $item->family_allowed);
			$insert_stmt->bindValue(':upload_token', $item->upload_token);
			$insert_stmt->execute();
			$item->id = $this->getDb()->lastInsertId();
			$this->items[] = $item;
			return TRUE;
		}
		return FALSE;
	}

	public function getItems()
	{
		$this->load();
		return $this->items;
	}

	public function getItemByProperty($property, $value = NULL, $return_index = FALSE)
	{
		$this->load();
		$output = FALSE;
		if (isset($property) && $property !== '')
		{
			foreach ($this->items as $index=>$item)
			{
				if (isset($item->$property) && $item->$property === $value)
				{
					if ($return_index === TRUE)
					{
						$output = $index;
					}
					else
					{
						$output = $item;
					}
					break;
				}
			}
		}
		return $output;
	}

	public function removeItem($id)
	{
		$this->load();
		if (isset($id))
		{
			if ($found = $this->getItemByProperty('id', $id, TRUE) !== FALSE)
			{
				$this->getDb()->exec("DELETE FROM queue WHERE id = '" . $id . "'");
				unset($this->items[$found]);
				return TRUE;
			}
		}
		return FALSE;
	}

	public function removeItems()
	{
		$this->getDb()->exec("DROP TABLE queue");
		$this->load(TRUE);
		return TRUE;
	}
}
?>
