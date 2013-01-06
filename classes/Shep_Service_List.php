<?php
class Shep_Service_List
{
	protected $types = NULL;
	protected $services = array();

	public function __construct($config, $cfg, $db)
	{
		$this->config = $config;
		$this->cfg = $cfg;
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
		if ($service === '')
		{
			return FALSE;
		}
		if (!isset($this->services[$service]))
		{
			$config_key = strtolower(substr($service, 5));
			$this->services[$service] = new $service($this->cfg->get($config_key), $this->db);
		}
		return $this->services[$service];
	}
}
?>
