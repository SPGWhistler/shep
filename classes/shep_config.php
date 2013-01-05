<?php
class shep_config extends shep_base
{
	protected $config_file_path = '../config/config.json';
	protected $config = NULL;

	public function get($key = '')
	{
		if (!$this->config)
		{
			$config = json_decode(file_get_contents($this->config_file_path), TRUE);
			if (is_array($config)
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
