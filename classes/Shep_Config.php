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
		if ($this->config === NULL)
		{
			$config = json_decode(file_get_contents($this->config_file_path), TRUE);
			$last_json_error = json_last_error();
			if ($last_json_error !== JSON_ERROR_NONE)
			{
				die('Error decoding json config file: ' . $last_json_error . "\n");
			}
			if (is_array($config))
			{
				$newconfig = array();
				foreach ($config as $group=>$options)
				{
					foreach ($options as $option=>$data)
					{
						if ($option === 'desc')
						{
							continue;
						}
						$newconfig[$group][$option] = $data['value'];
					}
				}
				$this->config = $newconfig;
			}
			else
			{
				$this->config = array();
			}
			if (!isset($this->config['global']))
			{
				$this->config['global'] = array();
			}
		}
		return ($key !== '' && isset($this->config[$key])) ? array_merge($this->config['global'], $this->config[$key]) : $this->config['global'];
	}
}
?>
