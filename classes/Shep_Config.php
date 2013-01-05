<?php
class Shep_Config
{
	protected $config_file_path = NULL;
	protected $config = NULL;

	public function __construct()
	{
		$this->config_file_path = SHEP_BASE_PATH . 'config/config.json';
	}

	public function get($key = '')
	{
		if (!$this->config)
		{
			$config = json_decode(file_get_contents($this->config_file_path), TRUE);
			if (is_array($config))
			{
				$this->config = $config;
			}
			else
			{
				$this->config = array();
			}
		}
		return ($key !== '' && isset($this->config[$key])) ? $this->config[$key] : array();
	}
}
?>
