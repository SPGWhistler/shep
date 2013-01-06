<?php
class Shep_Service_List
{
	protected $types = NULL;

	public function __construct($config, $db)
	{
		$this->config = $config;
		$this->db = $db;
	}

	public function isSupportedFileType($fileType = '')
	{
		if (is_null($this->types))
		{
			foreach ($this->config['services'] as $service)
			{
				$this->types[$service] = call_user_func($service .'::getSupportedFileTypes');
			}
		}
		if ($fileType !== '')
		{
			foreach ($this->types as $service=>$types)
			{
				$res = array_search($fileType, $types);
				if ($res !== FALSE)
				{
					return $service;
				}
			}
		}
		return FALSE;
	}

	public function getService($service = '')
	{
	}
}
?>
